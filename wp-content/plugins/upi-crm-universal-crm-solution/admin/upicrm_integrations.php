<?php
if ( !class_exists('UpiCRMAdminIntegrations') ):
    class UpiCRMAdminIntegrations{
        public function Render() {
            $UpiCRMIntegrations = new UpiCRMIntegrations();
            $id = (int)$_GET['id'];
            if ($id > 0) {
                $GetIntegrationsOBJ = $UpiCRMIntegrations->get_by_id($id);
            }
            
            switch ($_POST['action']) {
                case 'save_integration':
                    $this->saveIntegration();
                    $msg = __('changes saved successfully','upicrm');
                    break;
                case 'update_integration':
                    $this->updateIntegration();
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

        <?php _e('UpiCRM can act as an aggregator of leads from multiple / remote web sites.
This means that a UpiCRM master can receive leads from any other UpiCRM "slave" , as long as the connection (pairing) is performed.
UpiCRM can work in complete orchestration, receive / send leads from and to multiple servers at the same time.
In order to get your required API key, please visit <a href="http://www.upicrm.com/apikey" target="_blank">http://www.upicrm.com/apikey</a> <br />
More documentation can be found on: <a href="http://www.upicrm.com/docs" target="_blank">http://www.upicrm.com/docs</a>','upicrm'); ?>

            <br /><br />
            <img src="<?php echo plugins_url( 'images/integrations_tree.png', dirname(__FILE__) ); ?>" /> 
            <br /><br /><br />
    <?php   
    $info[0] = array(
        'checkbox_text' => __('Send Leads to Remote UpiCRM MASTER: This UpiCRM is a SLAVE, and is sending new leads to the below listed and confirmed UpiCRM MASTERS:','upicrm'),
        'table_title' => __('UpiMaster Table','upicrm'),
        'is_checked' => get_option('use_master'),
    );
    $info[1] = array(
        'checkbox_text' => __('Receive leads from remote UpiCRM SLAVE: this UpiCRM is a MASTER, and is receiving new leads from the below listed and confirmed UpiCRM SLAVES:','upicrm'),
        'table_title' => __('UpiSlave Table','upicrm'),
        'is_checked' => get_option('use_slave'),
    );
    
    for($i=0; $i<=1; $i++) {
        ?> 
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                <!--<input type="checkbox" id="use_integration_<?php echo $i; ?>" data-integration_id="<?php echo $i; ?>" data-callback="use_integration" <?php checked($info[$i]['is_checked']); ?> /> 
                <label for="use_integration_<?php echo $i; ?>" style="margin-top: 8px; margin-left: 5px;">
                    <?php _e($info[$i]['checkbox_text'],'upicrm'); ?>
                </label>-->
                <?php _e($info[$i]['checkbox_text'],'upicrm'); ?>

                <br /><br />
            </div>
        </div>
           <?php 
           $action = "save_integration";
           $button_text =  __('Add', 'upicrm');
           $integration_domain = "";
           $integration_key = "";
           if ($id > 0 && $GetIntegrationsOBJ->integration_is_slave == $i) { 
               $action = "update_integration";
               $button_text =  __('Update', 'upicrm');
               $integration_domain = $GetIntegrationsOBJ->integration_domain;
               $integration_key = $GetIntegrationsOBJ->integration_key;
               
           }
           ?> 
        <form method="post" class="form-inline" action="admin.php?page=upicrm_integrations">
           <input type="hidden" name="action" value="<?php echo $action; ?>" />
           <input type="hidden" name="integration_is_slave" value="<?php echo $i; ?>" />
           <input type="hidden" name="integration_id" value="<?php echo $id; ?>" />
           <div class="form-group">
               <label><?php _e('URL:'); ?></label>
               <input type="text" name="integration_domain" value="<?php echo $integration_domain; ?>" placeholder="http://" style="margin-right: 10px; height: 29px;" />
           </div>
           <div class="form-group">
               <label><?php _e('API Key:'); ?></label>
               <input type="text" name="integration_key" value="<?php echo $integration_key; ?>" placeholder="" style="height: 29px;" /> 
               <a href="http://www.upicrm.com/apikey" target="_blank">(?)</a>
           </div>
           <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $button_text; ?>" style="margin-left: 10px;">  
        </form>
        <br /><br />
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
                              <?php _e($info[$i]['table_title'],'upicrm'); ?>
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
                                         <?php _e('Domain','upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                         <?php _e('Secrete shared API key','upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                         <?php _e('Status','upicrm'); ?>
                                    </th>
                                    <th data-class="expand">
                                         <?php _e('Actions','upicrm'); ?>
                                    </th>
                                  </tr>
                                </thead>

                                <tbody>
                                    <?php 
                                    foreach ($UpiCRMIntegrations->get() as $obj) {
                                        if ($obj->integration_is_slave == $i) { 
                                        ?>
                                        <tr>
                                            <td data-belongs=""><?php echo $obj->integration_domain; ?></td>
                                            <td data-belongs=""><?php echo $obj->integration_key ?></td>
                                            <td data-belongs="" data-type="status"><?php echo $obj->integration_status ?></td>
                                            <td data-belongs="" class="upicrm_lead_actions">
                                                <span class="glyphicon glyphicon-question-sign" data-callback="request_status" data-integration_id="<?php echo $obj->integration_id; ?>" title="<?php _e('verify connection with remote server','upicrm'); ?>"></span>
                                                <span class="glyphicon glyphicon-edit" data-callback="edit" data-integration_id="<?php echo $obj->integration_id; ?>" title="<?php _e('Edit','upicrm'); ?>"></span>
                                                <span class="glyphicon glyphicon-remove" data-callback="remove" data-integration_id="<?php echo $obj->integration_id; ?>" title="<?php _e('Remove','upicrm'); ?>"></span>
                                            </td>
                                        </tr>   
                                   <?php 
                                        }
                                    } ?> 
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
        <br />
    <?php } ?>
</div>
<script type="text/javascript">
    $j(document).ready(function($) {
        $j("*[data-callback='remove']").click(function() {
                if (confirm("<?php _e('Delete this domain?','upicrm'); ?>")) {
                    GetSelect = $j(this);
                    var data = {
                        'action': 'remove_integration',
                        'integration_id': $j(this).attr("data-integration_id"),
                    };
                    $j.post(ajaxurl, data , function(response) {
                        GetSelect.closest("tr").fadeOut();
                        console.log(response);
                    });
                }
            });
       /*$("*[data-callback='use_integration']").change(function() {
           if ($(this).attr("checked")) {
               use_integration = 1;
           }
           else {
               use_integration = 0;
           }
            var data = {
                'action': 'use_integration',
                'integration_id': $(this).attr("data-integration_id"),
                'use_integration': use_integration,
            };
            $.post(ajaxurl, data , function(response) {
                //console.log(response);
            });
            //console.log(data);
        });*/
        
        $j("*[data-callback='edit']").click(function() {
            var integration_id = $j(this).attr("data-integration_id");
            window.location = "admin.php?page=upicrm_integrations&id="+integration_id;
        });
        
        $j("*[data-callback='request_status']").click(function() {
            var element = $(this).closest("tr").find('*[data-type="status"]');
            element.html('<div class="ajax_load"></div>');
            var data = {
                'action': 'check_integration_status',
                'integration_id': $(this).attr("data-integration_id"),
            };
            $.post(ajaxurl, data , function(response) {
                /*console.log(response);
                obj = JSON.parse(response)
                element.html(obj.status);*/
                console.log(response);
                element.html(response);
            });
        });
            
    });
</script>
<?php
        }
        
        function saveIntegration() {
            $UpiCRMIntegrations = new UpiCRMIntegrations();
            $insertArr['integration_domain'] = $_POST['integration_domain'];
            $insertArr['integration_clean_domain'] = upicrm_parse_url($_POST['integration_domain']);
            $insertArr['integration_key'] = $_POST['integration_key'];
            $insertArr['integration_status'] = __('Unchecked','upicrm');
            $insertArr['integration_is_slave'] = $_POST['integration_is_slave'];
            $UpiCRMIntegrations->add($insertArr);
        }
        
        function updateIntegration() {
            $UpiCRMIntegrations = new UpiCRMIntegrations();
            $updateArr['integration_domain'] = $_POST['integration_domain'];
            $updateArr['integration_clean_domain'] = upicrm_parse_url($_POST['integration_domain']);
            $updateArr['integration_key'] = $_POST['integration_key'];
            $UpiCRMIntegrations->update($updateArr,$_POST['integration_id']);
        }
        
        function wp_ajax_remove_integration_callback() {
            $UpiCRMIntegrations = new UpiCRMIntegrations();
            $UpiCRMIntegrations->remove($_POST['integration_id']);
            die();
        }
        
        /*function wp_ajax_use_integration_callback() {
            if ($_POST['integration_id'] == 0) {
                update_option('use_master',$_POST['use_integration']);
            }
            if ($_POST['integration_id'] == 1) {
                update_option('use_slave',$_POST['use_integration']);
            }
            echo 1;
            die();
        }*/
        
        function wp_ajax_check_integration_status_callback() {
            $UpiCRMIntegrations = new UpiCRMIntegrations();
            $UpiCRMIntegrationsLib = new UpiCRMIntegrationsLib();
            $IntegrationOBJ = $UpiCRMIntegrations->get_by_id($_POST['integration_id']);
            $ret = $UpiCRMIntegrationsLib->send_check_status($IntegrationOBJ);
            if (strlen($ret) > 200) {
                $ret = __('UpiCRM is not installed / not upgraded on remote server.','upicrm');  //there is no upi on this server!
            }
            $UpiCRMIntegrations->update(array("integration_status" => $ret), $IntegrationOBJ->integration_id);
            echo $ret;
            die();
        }
        
        
        
       
    }
    
    add_action( 'wp_ajax_remove_integration', array(new UpiCRMAdminIntegrations,'wp_ajax_remove_integration_callback'));
    //add_action( 'wp_ajax_use_integration', array(new UpiCRMAdminIntegrations,'wp_ajax_use_integration_callback'));
    add_action( 'wp_ajax_check_integration_status', array(new UpiCRMAdminIntegrations,'wp_ajax_check_integration_status_callback'));
    
    
endif;
?>