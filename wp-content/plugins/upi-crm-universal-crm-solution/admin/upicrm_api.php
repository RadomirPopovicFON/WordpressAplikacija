<?php
if ( !class_exists('UpiCRMAdminAPI') ):
    class UpiCRMAdminAPI{
        public function Render() {
            global $SourceTypeID;
            
            switch ($_GET['action']) {
                case 'change_lead_status':
                   //admin.php?page=upicrm_api&action=change_lead_status&lead_id=38&lead_status_id=2
                   $this->change_lead_status($_GET['lead_id'],$_GET['lead_status_id']);
                break;
                case 'change_lead_user_id':
                   //admin.php?page=upicrm_api&action=change_lead_user_id&lead_id=38&user_id=1
                   $this->change_user_id($_GET['lead_id'],$_GET['user_id']);
                break;
                case 'save_comment':
                   //admin.php?page=upicrm_api&action=save_comment
                   $this->save_comment($_POST['lead_id'],$_POST['lead_management_comment']);
                break;
                default:
                    echo "Error!";
            }
        }
        
        public function change_lead_status($lead_id,$lead_status_id) {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();

            if ($UpiCRMUsers->is_have_permission_to_lead(get_current_user_id(),$lead_id)) {
                $UpiCRMLeads->change_status($lead_status_id, $lead_id);
                
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                $user = $user = get_user_by('id', $leadObj->user_id);
                $UpiCRMMails->send($lead_id, "change_lead_status", $user->user_email);
                $status_name = $UpiCRMLeadsStatus->get_status_name_by_id($leadObj->lead_status_id);
                
                $this->show_comment($lead_id, __('You have successfully changed lead status to:','upicrm')." ".$status_name, true);
            }
            else {
                echo "Error! No permissions!";
            }
        }
        
        public function change_user_id($lead_id,$user_id) {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMMails = new UpiCRMMails();

            if ($UpiCRMUsers->is_have_permission_to_lead(get_current_user_id(),$lead_id)) {
                $UpiCRMLeads->change_user($user_id,$lead_id);
                
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                $user = $user = get_user_by('id', $leadObj->user_id);
                $UpiCRMMails->send($lead_id, "change_user", $user->user_email);
                
                $this->show_comment($lead_id, __('You Have successfully assigned the lead to:','upicrm')." ".$user->display_name, true);
            }
            else {
                echo "Error! No permissions!";
            }
        }
        
        public function show_comment($lead_id,$msg,$msg2=false) {
            $UpiCRMLeads = new UpiCRMLeads();
            $leadObj = $UpiCRMLeads->get_by_id($lead_id);
            if ($msg != "") {
            ?>
                <div class="updated">
                    <p><?php echo $msg; ?></p>
                    <?php if ($msg2) { ?>
                        <p><?php _e('Email notification was sent!','upicrm'); ?></p>
                    <?php } ?>
                </div>
            <?php
            }
            ?>
            <br /><br />
            <form action="admin.php?page=upicrm_api&action=save_comment" method="post">
                <p><?php _e('You can also add some comment or important information for this lead if you wish:','upicrm'); ?></p>
                <textarea rows="11" cols="50" name="lead_management_comment"><?php echo $leadObj->lead_management_comment;?></textarea>
                <input type="hidden" value="<?php echo $lead_id;?>" name="lead_id" />
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Comment">
                </p>
                <p><a href="admin.php?page=upicrm_allitems"><?php _e('Manage all leads','upicrm'); ?></a></p>
            </form>
            <?php
        }
        
        function save_comment($lead_id,$lead_management_comment) {
            $UpiCRMLeads = new UpiCRMLeads();
            $UpiCRMUsers = new UpiCRMUsers();

            if ($UpiCRMUsers->is_have_permission_to_lead(get_current_user_id(),$lead_id)) {
                $updateArr['lead_management_comment'] = $lead_management_comment;
                $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                $this->show_comment($lead_id, __('comment saved successfully','upicrm'));
            }
            else {
                echo "Error! No permissions!";
            }
        }

    }    
endif;

