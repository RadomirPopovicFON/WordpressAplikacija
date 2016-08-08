<?php
if ( !class_exists('UpiCRMAdminLeadRoute') ):
    class UpiCRMAdminLeadRoute{
        public function Render() {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMLeadsRoute = new UpiCRMLeadsRoute();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMUsers = new UpiCRMUsers();
            $UpiCRMUIBuilder = new UpiCRMUIBuilder();
            
            $list_option = $UpiCRMUIBuilder->get_list_option_minimum();
            $id = (int)$_GET['id'];
            if ($id > 0) {
                $GetLeadsRouteOBJ = $UpiCRMLeadsRoute->get_by_id($id);
            }
            
            switch ($_POST['action']) {
                case 'save_route':
                    $this->saveRoute();
                    $msg = __('changes saved successfully','upicrm');
                    break;
                
                case 'update_route':
                    $this->updateRoute();
                    $msg = __('update saved successfully','upicrm');
                    break;
            }
?>
<script type="text/javascript">
    $j(document).ready(function () {
        pageSetUp();
    })
</script>

    <?php
            if (isset($msg)) {
    ?>
    <div class="updated">
        <p><?php echo $msg; ?></p>
    </div>
    <?php
            }
    ?>
<div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <h2><?php _e('Add New lead routing rule','upicrm'); ?></h2>
            <?php _e('Here you can define rules and conditions to automatically assign leads to designated UpiCRM users or change leads status fields. 
For example you can define that If field "country" equals "India" , then assign this lead to the regional sales manager of India. 
Another example: if subject contains "buy free", then change lead status to "Spam".','upicrm'); ?>
            <br /><br />
            
            <strong><?php _e('Important notes:','upicrm'); ?></strong>
            <ul class="num_list">
                <li><?php _e("You can add as many rules as you'd like, we'll process them in the order they're written, while the last rule will always override the previous rules – so it's highly recommended to plan your logic before you actually define the rules.",'upicrm'); ?></li>
                <li><?php _e('You can add multiple values separated by commas (,). 
Example: if subject contains buy, free, shop, cheap, new, deals – then change lead status to "Spam"','upicrm'); ?></li> 
                <li><?php _e('Value are by definition NOT case sensitive, meaning – if you write "india", as content, the rule will be processed also in cases where field contains value "INDIA".','upicrm'); ?></li>
                <li><?php _e('You can always delete or edit rules in the future… so start slow and add more rules as you go.','upicrm'); ?></li>
                <li><?php _e('Checkboxes on forms: if you\'re using check box fields on you forms, we recommend using "contains" operator only.','upicrm'); ?></li>
                
            </ul>
            <br /><br />
            <form method="post" action="admin.php?page=upicrm_lead_route">
                <?php if($id > 0) { ?>
                    <input type="hidden" name="action" value="update_route" />
                    <input type="hidden" name="upicrm_lead_id" value="<?php echo $id; ?>" />
                <?php } else { ?>
                    <input type="hidden" name="action" value="save_route" />
                <?php } ?>
                <strong><?php _e('IF','upicrm'); ?></strong>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <select name="field_id">
                                <?php
                                $i = 1;
                                foreach ($list_option as $key => $arr) {
                                    foreach ($arr as $key2 => $value) {
                                        $selected = "";
                                        if ($id > 0)
                                            $selected = selected($GetLeadsRouteOBJ->lead_route_option.'||exp||'.$GetLeadsRouteOBJ->field_id,$key.'||exp||'.$key2, false);
                                        ?>
                                        <option value="<?php echo $key.'||exp||'.$key2; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                                        <?php
                                    }
                                }
                                ?>
                </select>
                <select name="lead_route_type">
                    <?php
                    $OperatorArr = $UpiCRMLeadsRoute->get_type_options();
                    foreach ($OperatorArr as $key => $value) { 
                        $selected = "";
                        if ($id > 0)
                            $selected = selected( $GetLeadsRouteOBJ->lead_route_type, $key, false);
                        ?>
                        <option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                   <?php } ?>
                </select>
                : <input type="text" name="lead_route_value" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->lead_route_value : ""?>" style="height: 28px; position: relative; top: 2px;" />
                
                <input type="checkbox" value="1" name="lead_route_and" id="lead_route_and" style="position: relative; top: -4px; margin-left: 15px;" />
                <label for="lead_route_and"><?php _e('Add and option','upicrm'); ?></label>
                
                <div id="add_more" style="display: none;">
                <strong><?php _e('AND','upicrm'); ?></strong>
                               &nbsp;
                               <select name="field_id2">
                                               <?php
                                               $i = 1;
                                               foreach ($list_option as $key => $arr) {
                                                   foreach ($arr as $key2 => $value) {
                                                       $selected = "";
                                                       if ($id > 0)
                                                           $selected = selected($GetLeadsRouteOBJ->lead_route_option2.'||exp||'.$GetLeadsRouteOBJ->field_id2,$key.'||exp||'.$key2, false);
                                                       ?>
                                                       <option value="<?php echo $key.'||exp||'.$key2; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                                                       <?php
                                                   }
                                               }
                                               ?>
                               </select>
                               <select name="lead_route_type2">
                                   <?php
                                   $OperatorArr = $UpiCRMLeadsRoute->get_type_options();
                                   foreach ($OperatorArr as $key => $value) { 
                                       $selected = "";
                                       if ($id > 0)
                                           $selected = selected( $GetLeadsRouteOBJ->lead_route_type2, $key, false);
                                       ?>
                                       <option value="<?php echo $key; ?>" <?php echo $selected; ?> ><?php echo $value; ?></option>
                                  <?php } ?>
                               </select>
                               : <input type="text" name="lead_route_value2" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->lead_route_value2 : ""?>" style="height: 28px; position: relative; top: 2px;" />
                </div>
                
                <br /><br />
                <strong><?php _e('THEN','upicrm'); ?></strong>
                &nbsp;&nbsp;
                <ul style="list-style-type: decimal; margin: 0 35px;">
                    <li>
                        <?php _e('Assign lead to','upicrm'); ?>
                        <select name="user_id">
                            <option value="0"></option>
                            <?php
                            foreach ($UpiCRMUsers->get() as $user) { 
                                if ($id > 0)
                                    $selected = selected( $GetLeadsRouteOBJ->user_id, $user->ID, false);
                                ?>
                                <option value="<?php echo $user->ID; ?>" <?php echo $selected; ?> ><?php echo $user->display_name; ?></option>
                           <?php } ?>
                        </select>
                    </li>
                    <li>
                        <?php _e('Change lead status to','upicrm'); ?>
                        <select name="lead_status_id">
                            <option value="0"></option>
                            <?php
                            foreach ($UpiCRMLeadsStatus->get() as $status) { 
                                if ($id > 0)
                                    $selected = selected( $GetLeadsRouteOBJ->lead_status_id, $status->lead_status_id, false);
                                ?>
                                <option value="<?php echo $status->lead_status_id; ?>" <?php echo $selected; ?> ><?php echo $status->lead_status_name; ?></option>
                           <?php } ?>
                        </select>
                    </li>
 <li>
                        <?php _e('Change','upicrm'); ?>
                        <select name="change_field_id">
                            <option value="0"></option>
                            <?php
                            foreach ($UpiCRMFields->get() as $field) { 
                                $selected = "";
                                if ($id > 0)
                                    $selected = selected( $GetLeadsRouteOBJ->change_field_id, $field->field_id, false);
                                ?>
                                <option value="<?php echo $field->field_id; ?>" <?php echo $selected; ?> ><?php echo $field->field_name; ?></option>
                           <?php } ?>
                        </select>
                        <?php _e('value to:','upicrm'); ?>
                        <input type="text" name="change_field_value" value="<?php echo $id > 0 ?  $GetLeadsRouteOBJ->change_field_value : ""?>" style="height: 28px; position: relative; top: 2px;">
                    </li>
                </ul>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e($id > 0 ? 'Update Rule' : 'Save Rule','upicrm'); ?>"></p>            </form>
        </div>
    </div>
    
    <section id="widget-grid" class="">
    
    <!-- row -->
    <div id="LeadsRouteTable" class="row">
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">
             <header>
                        <span class="widget-icon">
                          
                          <i class="fa fa-table">
                          </i>
                          
                        </span>
                        <h2>
                          <?php _e('Lead Routing Table','upicrm'); ?>
                        </h2>
                        
                      </header>
                      
                      <!-- widget div-->
                      <div>
                        
                        <!-- widget edit box -->
                        <div class="jarviswidget-editbox">
                          <!-- This area used as dropdown edit box -->
                          
                        </div>
                        <!-- end widget edit box -->
                        
                        <!-- widget content -->
                        <div class="widget-body no-padding">
                          
                          <table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
                            
                            <thead>
                              <tr>
                                <th data-class="expand">
                                     <?php _e('Field','upicrm'); ?>
                                </th>
                                <th data-class="expand">
                                     <?php _e('Operator','upicrm'); ?>
                                </th>
                                <th data-class="expand">
                                     <?php _e('Value','upicrm'); ?>
                                </th>
                                <th data-class="expand">
                                     <?php _e('Assign lead to','','upicrm'); ?>
                                </th>
                                <th data-class="expand">
                                     <?php _e('Change lead status to','upicrm'); ?>
                                </th>
                                <th data-class="expand">
                                     <?php _e('Add field value','upicrm'); ?>
                                </th>
                                <th data-class="expand">
                                     <?php _e('Actions','upicrm'); ?>
                                </th>
                              </tr>
                            </thead>
                            
                            <tbody>
                                <?php 
                                $GetUserArr = $UpiCRMUsers->get_as_array();
                                $FieldsArr = $UpiCRMFields->get_as_array();
                                $LeadsStatusArr = $UpiCRMLeadsStatus->get_as_array();
                                foreach ($UpiCRMLeadsRoute->get() as $obj) { ?>
                                    <tr>
                                        <td data-belongs="">
                                            <?php
                                            echo $list_option[$obj->lead_route_option][$obj->field_id];
                                            if ($obj->lead_route_and) { 
                                                echo "<br />";
                                                echo $list_option[$obj->lead_route_option2][$obj->field_id2];
                                            }
                                            ?>
                                            
                                        </td>
                                        <td data-belongs="">
                                            <?php 
                                            echo $OperatorArr[$obj->lead_route_type]; 
                                            if ($obj->lead_route_and) { 
                                                echo "<br />";
                                                echo $OperatorArr[$obj->lead_route_type2]; 
                                            }
                                            ?>
                                        </td>
                                        <td data-belongs="">
                                            <?php 
                                            echo $obj->lead_route_value;
                                            if ($obj->lead_route_and) { 
                                                echo "<br />";
                                                echo $obj->lead_route_value2;
                                            }
                                            ?>
                                        </td>
                                        <td data-belongs=""><?php echo $GetUserArr[$obj->user_id]; ?></td>
                                        <td data-belongs=""><?php echo $LeadsStatusArr[$obj->lead_status_id]; ?></td>
                                        <td data-belongs="">
                                            <?php if ($obj->change_field_id > 0) { ?>
                                                <?php echo $FieldsArr[$obj->change_field_id]; ?>
                                                &gt;&gt; 
                                                <?php echo $obj->change_field_value; ?>
                                            <?php } ?>
                                        </td>
                                        <td data-belongs="" class="upicrm_lead_actions">
                                            <span class="glyphicon glyphicon-edit" data-callback="edit" data-lead_route_id="<?php echo $obj->lead_route_id; ?>" title="<?php _e('Edit','upicrm'); ?>"></span>
                                            <span class="glyphicon glyphicon-remove" data-callback="remove" data-lead_route_id="<?php echo $obj->lead_route_id; ?>" title="<?php _e('Remove','upicrm'); ?>"></span>
                                        </td>
                                    </tr>   
                               <?php } ?> 
                            </tbody>
							
                          </table>
                          
                        </div>
                        <!-- end widget content -->
                        
                      </div>
                      <!-- end widget div -->
                      
                  </div>
                  <!-- end widget -->
                  
              </article>    
    </div>
    
    
          
          <!-- end row -->
          
          <!-- end row -->
          
          
   </section>
</div>
<script type="text/javascript">
    $j(document).ready(function($) {
        $j("*[data-callback='remove']").click(function() {
                if (confirm("<?php _e('Remove this lead?','upicrm'); ?>")) {
                    GetSelect = $j(this);
                    var data = {
                        'action': 'remove_lead_route',
                        'lead_route_id': $j(this).attr("data-lead_route_id"),
                    };
                    $j.post(ajaxurl, data , function(response) {
                        GetSelect.closest("tr").fadeOut();
                        console.log(response);
                    });
                }
            });
            
        $j("*[data-callback='edit']").click(function() {
            var lead_route_id = $j(this).attr("data-lead_route_id");
            window.location = "admin.php?page=upicrm_lead_route&id="+lead_route_id;
        });
        
        $("#lead_route_and").click(function() {
            if ($(this).prop("checked")) {
                $("#add_more").fadeIn();
            }
            else {
                $("#add_more").fadeOut();
            }
        });
        
        <?php if($GetLeadsRouteOBJ->lead_route_and == 1) { ?>
            $("#lead_route_and").prop("checked",true);
            $("#add_more").show();
        <?php } ?>
    });
</script>
<?php
        }
        
        function saveRoute() {
            $UpiCRMLeadsRoute = new UpiCRMLeadsRoute();
            $field_id = explode('||exp||',$_POST['field_id']);
            $insertArr['lead_route_option'] = $field_id[0];
            $insertArr['field_id'] = $field_id[1];
            
            if ($_POST['field_id2']) {
                $field_id2 = explode('||exp||',$_POST['field_id2']);
                $insertArr['lead_route_option2'] = $field_id2[0] ? $field_id2[0] : 0;
                $insertArr['field_id2'] = $field_id2[1] ? $field_id2[1] : 0;
            }
            
            $insertArr['lead_route_type'] = $_POST['lead_route_type'];
            $insertArr['lead_route_value'] = $_POST['lead_route_value'];
            $insertArr['lead_route_type2'] = $_POST['lead_route_type2'];
            $insertArr['lead_route_value2'] = $_POST['lead_route_value2'];
            $insertArr['lead_route_and'] = $_POST['lead_route_and'] ? 1 : 0;
            $insertArr['user_id'] = $_POST['user_id'];
            $insertArr['lead_status_id'] = $_POST['lead_status_id'];
            $insertArr['change_field_id'] = $_POST['change_field_id'];
            $insertArr['change_field_value'] = $_POST['change_field_value'];
            $UpiCRMLeadsRoute->add($insertArr);
        }
        
        function updateRoute() {
            $UpiCRMLeadsRoute = new UpiCRMLeadsRoute();
            
            $field_id = explode('||exp||',$_POST['field_id']);

            if ($_POST['field_id2']) {
                $field_id2 = explode('||exp||',$_POST['field_id2']);
                $updateArr['lead_route_option2'] = $field_id2[0] ? $field_id2[0] : 0;
                $updateArr['field_id2'] = $field_id2[1] ? $field_id2[1] : 0;
            }
            
            $updateArr['lead_route_option'] = $field_id[0];
            $updateArr['field_id'] = $field_id[1];
            $updateArr['lead_route_option2'] = $field_id2[0];
            $updateArr['field_id2'] = $field_id2[1];
            $updateArr['lead_route_type'] = $_POST['lead_route_type'];
            $updateArr['lead_route_value'] = $_POST['lead_route_value'];
            $updateArr['lead_route_type2'] = $_POST['lead_route_type2'];
            $updateArr['lead_route_value2'] = $_POST['lead_route_value2'];
            $updateArr['lead_route_and'] = $_POST['lead_route_and'] ? 1 : 0;
            $updateArr['lead_route_type'] = $_POST['lead_route_type'];
            $updateArr['lead_route_value'] = $_POST['lead_route_value'];
            $updateArr['user_id'] = $_POST['user_id'];
            $updateArr['lead_status_id'] = $_POST['lead_status_id'];
            $updateArr['change_field_id'] = $_POST['change_field_id'];
            $updateArr['change_field_value'] = $_POST['change_field_value'];
            $UpiCRMLeadsRoute->update($updateArr,$_POST['upicrm_lead_id']);
        }
        
        
        function wp_ajax_remove_lead_route_callback() {
            $UpiCRMLeadsRoute = new UpiCRMLeadsRoute();
            $UpiCRMLeadsRoute->remove($_POST['lead_route_id']);
            die();
        }
    }
    
    add_action( 'wp_ajax_remove_lead_route', array(new UpiCRMAdminLeadRoute,'wp_ajax_remove_lead_route_callback'));
endif;
?>