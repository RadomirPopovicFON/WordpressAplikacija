<?php
if ( !class_exists('UpiCRMAdminUsers') ):
    class UpiCRMAdminUsers{
        public function Render() {
            $UpiCRMUsers = new UpiCRMUsers();
            $id = (int)$_GET['id'];
            if ($id > 0) {
                $GetUserOBJ = $UpiCRMUsers->get_inside_by_id($id);
            }
            
            switch ($_POST['action']) {
                case 'save_user':
                    $this->saveUser();
                break;
                case 'update_user':
                    $this->updateUser();
                    $msg = __('update saved successfully','upicrm');
                break;
            
            }

            if (isset($msg)) {
                ?>
                <br />
                <div class="updated">
                    <p><?php echo $msg; ?></p>
                </div>
                <br /><br />
                <?php
            }
           _e('UpiCRM User hierarchy: <br />
UpiCRM is designed to work in a multi-user / global organization environment, with up to 5 tiers of users, per your choice / definition.
<br />
For Example: you can define a single "Global Sales Manager" , three "Regional Sales Managers", under each of them - local "Sales representatives", that are working with "External distributors".
<br />
Per every user you can define:<br />
1.      Permission to re-assign leads back to the manager in charge.<br />
2.      Will the manager receive status updates per every status changes by email. <br />

All users, when connected to the UpiCRM dashboard, will see not only the leads assigned to them, but also the aggregation of leads assigned to the users that are reporting to them.
<br /><br />
In order add users to UpiCRM: <br />','upicrm');
             _e('1. Add a new WordPress "subscriber" users<br />','upicrm');
             _e('2. Add the new user to UpiCRM:<br />','upicrm');
             _e('&nbsp;&nbsp;&nbsp;&nbsp; a.  Choose the new user (drop down list)<br />','upicrm');
             _e('&nbsp;&nbsp;&nbsp;&nbsp; b.  Choose the manager (drop down list)<br />','upicrm');
             _e('&nbsp;&nbsp;&nbsp;&nbsp; c.  Add role (free text / label)<br />','upicrm');
             _e('&nbsp;&nbsp;&nbsp;&nbsp; d.  Choose UpiUser/UpiAdmin <br />','upicrm');
             _e('&nbsp;&nbsp;&nbsp;&nbsp; e.  Choose – if a user can re-assign eads back to the manager ? yes/no. <br />','upicrm');
            _e('&nbsp;&nbsp;&nbsp;&nbsp; f.  Choose: will a manager receive notifications on the user status updates  ? yes/no. <br /> <br />','upicrm');
             _e(' Note: you can always click the "edit" icon and change the user\'s role/position/permissions.','upicrm');
           
            ?>
            <br /><br />
            <form method="post" class="form-inline" action="admin.php?page=upicrm_users_center">
               <h2><?php _e(!$GetUserOBJ ? "Add User" : "Update User", 'upicrm'); ?></h2>
               <input type="hidden" name="action" value="<?php echo !$GetUserOBJ ? "save_user" : "update_user"; ?>">
               <input type="hidden" name="inside_id" value="<?php echo $GetUserOBJ->inside_id; ?>">
               <div class="form-group pad_form">
                   <label><?php _e('User','upicrm'); ?>:</label>
                   <?php echo $UpiCRMUsers->select_list_user("user_id", $GetUserOBJ->user_id); ?>
               </div>
               <div class="form-group pad_form">
                   <label><?php _e('Reports to','upicrm'); ?>:</label>
                   <?php echo $UpiCRMUsers->select_list_user("user_parent_id", $GetUserOBJ->user_parent_id); ?>
               </div>
               <div class="form-group pad_form">
                   <label><?php _e('Role','upicrm'); ?>:</label>
                   <input type="text" name="user_label" value="<?php echo $GetUserOBJ->user_label; ?>" /> 
               </div>
               <div class="form-group pad_form">
                    <label><?php _e('Permission','upicrm'); ?>:</label>
                    <select name="upicrm_user_permission">
                        <option value="1" <?php selected(get_the_author_meta('upicrm_user_permission', $GetUserOBJ->user_id), 1);?>><?php _e('UpiCRM User','upicrm'); ?></option>
                        <option value="2" <?php selected(get_the_author_meta('upicrm_user_permission', $GetUserOBJ->user_id), 2);?>><?php _e('UpiCRM Admin','upicrm'); ?></option>
                    </select>
               </div>
               <div class="clearfix"></div>
               <br />
               
               <div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_user_reassign_manager" <?php checked( get_the_author_meta('upicrm_user_reassign_manager', $GetUserOBJ->user_id), 1, 1 ); ?>  /> 
                     <?php _e('Can Re-assign leads back to manager','upicrm'); ?></label>
               </div>
               <div class="clearfix"></div>
               <div class="checkbox">
                    <label><input type="checkbox" value="1" name="upicrm_user_manager_status_change_note" <?php checked( get_the_author_meta('upicrm_user_manager_status_change_note', $GetUserOBJ->user_id), 1, 1 ); ?>  /> 
                     <?php _e('Update manager on status changes','upicrm'); ?></label>
               </div>
               
               <div class="clearfix"></div>
               <br /><br />
               <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e(!$GetUserOBJ ? "Add UpiCRM User" : "Update", 'upicrm'); ?>">  
            </form>
            <br /><br />
            <div class="tree smart-form">
                <strong><p><?php _e('Global User Hierarchy', 'upicrm'); ?>:</p></strong>
                <?php
                $users = $UpiCRMUsers->get_inside_by_parent_id(0);
                $this->view_user_tree($users);
                ?>
            </div>
            <script type="text/javascript">
            $j(document).ready(function($) {
                $j("*[data-callback='remove']").click(function() {
                        if (confirm("<?php _e('Delete this user from UpiCRM?','upicrm'); ?>")) {
                            GetSelect = $j(this);
                            var data = {
                                'action': 'remove_user_hierarchy',
                                'inside_id': $(this).attr("data-inside_id"),
                            };
                            $j.post(ajaxurl, data , function(response) {
                                GetSelect.closest("li").fadeOut();
                                console.log(response);
                            });
                        }
                    });
                    
                $j("*[data-callback='edit']").click(function() {
                    var inside_id = $j(this).attr("data-inside_id");
                    window.location = "admin.php?page=upicrm_users_center&id="+inside_id;
                });
                });
                </script>
            
            <?php
        }
        
        function view_user_tree($users) {
            $UpiCRMUsers = new UpiCRMUsers();
            ?>
            <ul>
                <?php foreach ($users as $user) { ?>
                <li>
                    <span class="glyphicon glyphicon-remove hand" data-callback="remove" data-inside_id="<?php echo $user->inside_id; ?>" title="<?php _e('Remove', 'upicrm'); ?>"></span>
                    <span class="glyphicon glyphicon-edit hand" data-callback="edit" data-inside_id="<?php echo $user->inside_id; ?>" title="<?php _e('Edit', 'upicrm'); ?>"></span>
                    <span class="glyphicon glyphicon-refresh <?php echo get_the_author_meta('upicrm_user_reassign_manager', $user->user_id) ? "" : "opacity"; ?>" title="<?php _e('Can Re-assign leads back to manager', 'upicrm'); ?>"></span>
                    <span class="glyphicon glyphicon-headphones <?php echo get_the_author_meta('upicrm_user_manager_status_change_note', $user->user_id) ? "" : "opacity"; ?>" title="<?php _e('Update manager on status changes', 'upicrm'); ?>"></span>
                    &nbsp;&nbsp;
                    <strong><?php echo $UpiCRMUsers->get_by_id($user->user_id); ?></strong> 
                    &nbsp;&nbsp;<?php echo $user->user_label; ?>

                    <?php
                    $child_users = $UpiCRMUsers->get_inside_by_parent_id($user->user_id);
                    if($child_users) {
                        $this->view_user_tree($child_users);
                    }
                    ?>
                </li>
                <?php } ?>
                
            </ul>

            <?php
        }
        
        function saveUser() {
            $UpiCRMUsers = new UpiCRMUsers();
            if (!$UpiCRMUsers->get_inside_by_user_id($_POST['user_id']) && $_POST['user_id'] > 0) {
                $insertArr['user_id'] = $_POST['user_id'];
                $insertArr['user_parent_id'] = $_POST['user_parent_id'];
                $insertArr['user_label'] = $_POST['user_label'];
                update_user_meta( $_POST['user_id'],'upicrm_user_permission', sanitize_text_field( $_POST['upicrm_user_permission']));
                update_user_meta( $_POST['user_id'],'upicrm_user_reassign_manager', sanitize_text_field( $_POST['upicrm_user_reassign_manager']));
                update_user_meta( $_POST['user_id'],'upicrm_user_manager_status_change_note', sanitize_text_field( $_POST['upicrm_user_manager_status_change_note']));
                $UpiCRMUsers->add_inside($insertArr);
                ?>
                <div class="updated">
                    <p>
                        <?php _e('User Added Successfully', 'upicrm'); ?>
                    </p>
               </div>
               <br /><br />
                <?php
            } else {
                ?>
                <div class="error">
                    <p>
                        <?php _e('Error: User already defined, please edit existing user', 'upicrm'); ?>
                    </p>
               </div>
               <br /><br />
                <?php
            }
        }
        
        function updateUser() {
            $UpiCRMUsers = new UpiCRMUsers();
                $updateArr['user_id'] = $_POST['user_id'];
                $updateArr['user_parent_id'] = $_POST['user_parent_id'];
                $updateArr['user_label'] = $_POST['user_label'];
                update_user_meta( $_POST['user_id'],'upicrm_user_permission', sanitize_text_field( $_POST['upicrm_user_permission']));
                update_user_meta( $_POST['user_id'],'upicrm_user_reassign_manager', sanitize_text_field( $_POST['upicrm_user_reassign_manager']));
                update_user_meta( $_POST['user_id'],'upicrm_user_manager_status_change_note', sanitize_text_field( $_POST['upicrm_user_manager_status_change_note']));
                
                $UpiCRMUsers->update_inside($updateArr,$_POST['inside_id']);
        }
        
        function wp_ajax_remove_user_hierarchy_callback() {
            $UpiCRMUsers = new UpiCRMUsers();
            $userOBJ = $UpiCRMUsers->get_inside_by_id($_POST['inside_id']);
            $UpiCRMUsers->remove_inside($_POST['inside_id']);
            $users = $UpiCRMUsers->get_childrens_by_parent_id($userOBJ->user_id);
            foreach ($users as $user) {
                 $UpiCRMUsers->remove_inside($user->inside_id);
                
            }
            echo 1;
            die();
        }
        
    } 
add_action( 'wp_ajax_remove_user_hierarchy', array(new UpiCRMAdminUsers,'wp_ajax_remove_user_hierarchy_callback'));
endif;

