<?php
if ( !class_exists('UpiCRMAdminAdminLists') ):
class UpiCRMAdminAdminLists{
public function RenderLists() {
    global $SourceTypeName;
    
    $pageNum = (int)$_GET['page_num'];
    if($pageNum == 0)
        $pageNum = 1;
    if($perPage == NULL)
        $perPage = 30;

    $UpiCRMLeads = new UpiCRMLeads();
    $UpiCRMFields = new UpiCRMFields();
    $UpiCRMUIBuilder = new UpiCRMUIBuilder();
    $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
    $UpiCRMUsers = new UpiCRMUsers();
    $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
    
             
    switch ($_GET['action']) {
        case 'reset':
            $msg = __('Reset all settings successfully','upicrm');
            $UpiCRMFieldsMapping->empty_all();
            $UpiCRMUsers->empty_all();
        break;
        case 'delete_all':
            $msg = __('Delete all leads successfully','upicrm');
            $UpiCRMLeads->empty_all();
        break;
        case 'save_leads':
            $msg = __('Changes saved successfully','upicrm');
            $this->save_leads_arr();
        break;
        case 'change_time':
            //$msg = __('Changes saved successfully','upicrm');
            $this->change_time();
        break;
    
    
    }  
    

    $list_option = $UpiCRMUIBuilder->get_list_option();
    $getNamesMap = $UpiCRMFieldsMapping->get(); 
    
    $check_date = isset($_COOKIE['upicrm_lead_table_days']) ? $_COOKIE['upicrm_lead_table_days'] : 7;
    
    if ($UpiCRMUsers->get_permission() == 1) {
        $userID = get_current_user_id();
        $getLeads = $UpiCRMLeads->get($userID,0,0,'DESC',$check_date);
    }
    if ($UpiCRMUsers->get_permission() == 2) {
        $upicrm_is_admin = true;
        $getLeads = $UpiCRMLeads->get(0,0,0,'DESC',$check_date);
    }
    
    //var_dump($UpiCRMLeads->get_by_id(265));
?>
 
                <?php
                if (isset($msg)) {
                ?>
                    <div class="updated">
                        <p><?php echo $msg; ?></p>
                    </div>
                    <div class="clearfix"></div>
                <?php
                }
                ?>   
                    

<div class="ajax_load2"></div>
<div id="finish_load" style="display: none;">
   <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 nopad">
   <div id="ChooseDate">     
        <?php _e('Default date range:','upicrm'); ?> &nbsp;&nbsp;
        <a href="admin.php?page=upicrm_allitems&action=change_time&days=1" data-id="1" class="btn btn-default">
            <?php _e('1 Day','upicrm'); ?> 
        </a>
        <a href="admin.php?page=upicrm_allitems&action=change_time&days=7" data-id="7" class="btn btn-default">
            <?php _e('7 Days','upicrm'); ?> 
        </a>
        <a href="admin.php?page=upicrm_allitems&action=change_time&days=30" data-id="30" class="btn btn-default">
            <?php _e('1 Month','upicrm'); ?> 
        </a>
        <a href="admin.php?page=upicrm_allitems&action=change_time&days=90" data-id="90" class="btn btn-default">
            <?php _e('3 Months','upicrm'); ?> 
        </a>
        <a href="admin.php?page=upicrm_allitems&action=change_time&days=0" data-id="0" class="btn btn-default">
            <?php _e('All Time','upicrm'); ?> 
        </a>   
   </div>
   <br />
    <?php _e('Choose Fields to display:','upicrm'); ?> 
        <select id="ChooseInputs" multiple="multiple">
            <?php  
            $i=1;
            foreach ($list_option as $key => $arr) { 
                 foreach ($arr as $key2 => $value) { 
                ?>
                <option  data-count="<?php echo $i; ?>" value="<?php echo $key; ?>[<?php echo $key2; ?>]"><?php echo $value; ?></option>
            <?php 
                $i++;
                }
            } ?>
        </select>
    </div>
      <!--<style type="text/css">
          #LeadTable td, #LeadTable th{
              display: none;
          }
          #LeadTable td.checklead, #LeadTable th.checklead{
              display: table-cell;
          }
      </style> -->


  <!-- widget grid -->
  <section id="widget-grid" class="">
    
    <!-- row -->
    <div id="LeadTable" class="">
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12 nopad">
        <form method="post" action="admin.php?page=upicrm_allitems&action=save_leads">
            <br />
            <a href="javascript:void(0);" class="btn btn-default delete_all">
                <i class="glyphicon glyphicon-remove"></i> 
                <?php _e('Delete Selected leads','upicrm'); ?>
            </a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?php _e('Assign selected leads to','upicrm'); ?>:
            <?php echo $UpiCRMUsers->select_list_no_lead('assigned_to_all','user_id_all'); ?>
            &nbsp;&nbsp;&nbsp;
            <?php _e('Change selected leads status to','upicrm'); ?>:
            <?php echo $UpiCRMLeadsStatus->select_status_list_no_lead('status_to_all','lead_status_id_all'); ?>
            <input type="submit" class="btn btn-primary" value=" <?php _e('Apply','upicrm'); ?>" />
            <br /><br />
        
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">
             <header>
                        <span class="widget-icon">
                          
                          <i class="fa fa-table">
                          </i>
                          
                        </span>
                        <h2>
                          <?php _e('Leads Table','upicrm'); ?>
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
                          
                          <table id="upicrm_datatable" class="table table-striped table-bordered" width="100%">
                            
                            <thead>
                              <tr>
                                  <th class="checklead hasinput" data-class="expand">
                                  </th>
                                <?php 
                                foreach ($list_option as $key => $arr) { 
                                    foreach ($arr as $key2 => $value) { 
                                    ?>
                                    <th class="hasinput" data-belongs="<?php echo $key;?>[<?php echo $key2; ?>]">
                                      <input type="text" class="form-control" placeholder="<?php _e('Filter','upicrm'); ?> <?php echo $value; ?>" class="filter" />
                                  </th>
                                <?php 
                                    }
                                } 
                                ?>
                              </tr>
                              <tr>
                                  <th class="checklead" data-class="expand">
                                      <input type="checkbox" name="checklead" class="checklead_checkall" />
                                    <?php _e('Select all','upicrm'); ?> 
                                  </th>
                                <?php 
                                 $i=0;
                                 foreach ($list_option as $key => $arr) {
                                    foreach ($arr as $key2 => $value) {  ?>
                                    <th data-class="expand" data-belongs="<?php echo $key;?>[<?php echo $key2; ?>]">
                                      <?php 
                                      echo $value;
                                      if ($key == "leads" && $key2 == "lead_id") {
                                        $count_id = $i;
                                      }
                                      $i++;
                                      ?>
                                    </th>
                                <?php 
                                    }
                                } ?>
                            </thead>
                            
                            <tbody>
                                <?php 
                                foreach ($getLeads as $leadObj) { 
                                    ?>
                                    <tr>
                                        <td class="checklead">
                                            <input type="checkbox" value="<?php echo $leadObj->lead_id; ?>" name="checklead_check[]" class="checklead_check" data-lead_id="<?php echo $leadObj->lead_id; ?>" id="checklead_check<?php echo $leadObj->lead_id; ?>" />
                                        </td>
                                    <?php
                                    foreach ($list_option as $key => $arr) { 
                                        foreach ($arr as $key2 => $value) {  ?>   
                                            <td data-belongs="<?php echo $key;?>[<?php echo $key2; ?>]">
                                              <?php echo $UpiCRMUIBuilder->lead_routing($leadObj,$key,$key2,$getNamesMap); ?>
                                            </td>
                                    <?php 
                                        }
                                   }
                                   ?>
                                   </tr>             
                               <?php    
                               } 
                               ?>
                            </tbody>
							
                          </table>
                          
                        </div>
                        <!-- end widget content -->
                        
                      </div>
                      <!-- end widget div -->
                  </div>
                  <!-- end widget -->
               </form>
              </article>    
    </div>
    
    
          
          <!-- end row -->
          
          <!-- end row -->
          
          
   </section>

              
</div>
<!--<script src="https://code.jquery.com/jquery-2.2.3.min.js" integrity="sha256-a23g1Nt4dtEYOj7bR+vTu7+T8VP13humZFBJNIYoEJo=" crossorigin="anonymous"></script>
<script src="<?php echo UPICRM_URL; ?>resources/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo UPICRM_URL; ?>resources/js/app.js"></script> 
<script src="<?php echo UPICRM_URL; ?>resources/js/plugin/datatables/dataTables.tableTools.min.js"></script> 
<script src="<?php echo UPICRM_URL; ?>resources/js/plugin/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo UPICRM_URL; ?>resources/js/plugin/datatables/dataTables.colVis.min.js"></script>
<script src="<?php echo UPICRM_URL; ?>resources/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo UPICRM_URL; ?>resources/js/plugin/datatables/dataTables.tableTools.min.js"></script> -->

 
<script type="text/javascript">
     
        jQuery(document).ready(function($) {
            //alert(jQuery.fn.jquery);
            //show default options
            var count_lead = <?php echo $count_id+1; ?>;
            
            <?php if (isset($_COOKIE['upicrm_lead_table_days'])) { ?>
                var cda = $("#ChooseDate a[data-id='<?php echo $_COOKIE['upicrm_lead_table_days']; ?>']");
            <?php } else { ?>
                var cda = $("#ChooseDate a[data-id='7']");
            <?php } ?>
                
            cda.removeClass('btn-default');
            cda.addClass('btn-primary');
            
            
            /*$.fn.dataTable.ext.search.push(
                function( settings, data, dataIndex ) {
                    if ($("*[data-belongs='special[user_id]'] input[type='text']").val() != "") {
                        console.log(data[37]);
                    }
                    return true;
                    //return false;
                  }
            );*/
    
    
            
            var otable = $('#upicrm_datatable').DataTable({
               "order": [[ count_lead, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                  ]
            });
           

            $("#upicrm_datatable thead th input[type=text]").on( 'keyup change', function () {
                otable
                    .column( $(this).parent().index()+':visible' )
                    .search( this.value )
                    .draw();     		            
            });
            
            var column = otable.columns();
            column.visible(false);
            var column = otable.column(0);
            column.visible(true);
            
            if (upicrm_get_cookie('upicrm_lead_table_fields') != "") {
                var show_option = JSON.parse(upicrm_get_cookie('upicrm_lead_table_fields'));
            } else {
                var show_option = [
                    "content[1]", 
                    "content[2]",
                    "content[8]",
                    "special[user_id]", 
                    "leads[time]", 
                    "leads[lead_id]",
                    "special[actions]",
                    "special[lead_management_comment]",
                    "special[lead_status_id]"
                ]; 
            }
            show_option.forEach(function(entry) {
                 $j("#ChooseInputs option[value='"+entry+"']").prop('selected', true);
                 var i = $("#ChooseInputs option[value='"+entry+"']").index() + 1;
                 //$j("#LeadTable *[data-belongs='"+entry+"']").show();
                var column = otable.column(i);
                column.visible(true);
            });
            
            $j('#ChooseInputs').multiselect({
                onChange: function(options) {
                    var column = otable.columns();
                    column.visible(false);
                    var column = otable.column(0);
                    column.visible(true);
                    
                    var brands = $j('#ChooseInputs option:selected');
                    var selected = [];
                    //$j("#LeadTable td, #LeadTable th").hide();
                    
                    var remember_me = new Array();
                    $j(brands).each(function(index, brand){
                        val = $j(this).val();
                        var column = otable.column($(this).attr("data-count"));
                        column.visible(true);
                        //$j("#LeadTable *[data-belongs='"+val+"']").show();

                        remember_me[index] = val;
                    });
                    //$j("#LeadTable .checklead").show();
                    upicrm_set_cookie('upicrm_lead_table_fields', JSON.stringify(remember_me),30);
                }
            });
            
            $(".checklead_checkall").click(function() {
                if ($(this).prop("checked")) {
                    $('.checklead_check').prop("checked",true);
                    $('.checklead_check').closest("tr").find("td").css("background","#ECF3F8");
                }
                else {
                    $('.checklead_check').prop("checked",false);
                    $('.checklead_check').closest("tr").find("td").css("background","");
                }
            });
            
            /*$("*[data-belongs='special[user_id]'] input[type='text']").keyup(function() {
                //alert(1);
            });*/
            
            /*$j("a[data-callback='excel_output']").click(function() {
                var data = {
                    'action': 'excel_output',
                }
                $j.post(ajaxurl, data , function(response) {
                    if (response == 1)
                        location = "<?php echo home_url();?>/wp-content/uploads/upicrm/leads.xlsx";
                    else {
                        alert("Oh no! Error!");
                        console.log(response);
                    }
                });
            });*/
            $j(document).on("click", "*[data-callback='remove']", function() {
                if (confirm("<?php _e('Remove this lead?','upicrm'); ?>")) {
                    GetSelect = $j(this);
                    var data = {
                        'action': 'remove_lead',
                        'lead_id': $j(this).attr("data-lead_id"),
                    };
                    $j.post(ajaxurl, data , function(response) {
                        GetSelect.closest("tr").fadeOut();
                        //console.log(response);
                    });
                }
            });
            
            $(".checklead_check").click(function() {
                if ($(this).prop("checked")) {
                    $(this).closest("tr").find("td").css("background","#ECF3F8");
                }
                else {
                    $(this).closest("tr").find("td").css("background","");
                }
                
            });
            
            $j(document).on("click", "*[data-callback='save']", function() {
                lead_id = $j(this).attr("data-lead_id");
                user_id = $j("select[name='user_id'][data-lead_id='"+lead_id+"']").val();
                lead_status_id = $j("select[name='lead_status_id'][data-lead_id='"+lead_id+"']").val();
                remarks = $j("textarea[name='lead_remarks'][data-lead_id='"+lead_id+"']").val();
                var data = {
                    'action': 'save_lead',
                    'lead_id': lead_id,
                    'user_id': user_id,
                    'lead_status_id': lead_status_id,
                    'remarks': remarks,
                };
                
                $j.post(ajaxurl, data , function(response) {
                   //console.log(response);
                   if (response == 1)
                       alert("<?php _e('Saved successfully!','upicrm'); ?>");
                   else 
                        alert("Oh no! Error!");
                });
            });
            
            $j(document).on("click", "*[data-callback='edit']", function() {
                window.location = "admin.php?page=upicrm_edit_lead&id="+$j(this).attr("data-lead_id");
            });
            
            $j(document).on("click", "*[data-callback='request_status']", function() {
                lead_id = $j(this).attr("data-lead_id");
                var data = {
                    'action': 'request_status',
                    'lead_id': lead_id,
                };
                
                $j.post(ajaxurl, data , function(response) {
                   //console.log(response);
                   if (response == 1)
                       alert("<?php _e('Status update request was sent successfully!','upicrm'); ?>");
                   else 
                        alert("Oh no! Error!");
                });
            });
        
        $j(document).on("click", "*[data-callback='send_master_again']", function() {
                lead_id = $j(this).attr("data-lead_id");
                var data = {
                    'action': 'send_lead_again_to_master',
                    'lead_id': lead_id,
                };
                
                $j.post(ajaxurl, data , function(response) {
                   if (response == 1)
                       alert("<?php _e('Lead retransmission success!','upicrm'); ?>");
                    else
                        console.log(response);
                });
            });
            
            $(".delete_all").click(function() {
                if (confirm("<?php _e('Remove all selected leads?','upicrm'); ?>")) {
                    var ids = new Array();
                    $(".checklead_check:checked").each(function( index ) {
                        ids[index] = $(this).attr("data-lead_id");
                        $(this).closest("tr").fadeOut();
                    });
                    var data = {
                        'action': 'remove_lead_arr',
                        'lead_id': ids,
                    };
                    $j.post(ajaxurl, data , function(response) {
                       // GetSelect.closest("tr").fadeOut();
                        console.log(response);
                    });
                }
                
                //console.log(ids);
            });
            
            /*$("*[data-callback='assigned_to_all']").change(function() {
                    var ids = new Array();
                    var user_id = $(this).val();
                    $(".checklead_check:checked").each(function( index ) {
                        ids[index] = $(this).attr("data-lead_id");
                        $(this).closest("tr").fadeOut();
                    });
                    var data = {
                        'action': 'save_lead_user_arr',
                        'lead_id': ids,
                        'user_id': user_id,
                    };
                    $j.post(ajaxurl, data , function(response) {
                       // GetSelect.closest("tr").fadeOut();
                        if (response == 1)
                            alert("<?php _e('Saved successfully!','upicrm'); ?>");
                        else 
                             alert("Oh no! Error!");
                    });
            });
            
            $("*[data-callback='status_to_all']").change(function() {
                    var ids = new Array();
                    var lead_status_id = $(this).val();
                    $(".checklead_check:checked").each(function( index ) {
                        ids[index] = $(this).attr("data-lead_id");
                        $(this).closest("tr").fadeOut();
                    });
                    var data = {
                        'action': 'save_lead_status_arr',
                        'lead_id': ids,
                        'lead_status_id': lead_status_id,
                    };
                    $j.post(ajaxurl, data , function(response) {
                       // GetSelect.closest("tr").fadeOut();
                        if (response == 1)
                            alert("<?php _e('Saved successfully!','upicrm'); ?>");
                        else {
                            alert("Oh no! Error!");
                            console.log(response);
                         }
                    });
            });*/
            
            
            $(".ajax_load2").hide();
            $("#finish_load").fadeIn();
             
        });
    </script>
<?php
    }
    
    function bulk_actions() {
        $UpiCRMUsers  = new UpiCRMUsers();
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        ?>
        <br />
        <form method="post" action="admin.php?page=upicrm_allitems&action=save_leads">
            <a href="javascript:void(0);" class="btn btn-default delete_all">
                <i class="glyphicon glyphicon-remove"></i> 
                <?php _e('Delete all','upicrm'); ?>
            </a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?php _e('Assigned all leads to','upicrm'); ?>:
            <?php echo $UpiCRMUsers->select_list_no_lead('assigned_to_all'); ?>
            &nbsp;&nbsp;&nbsp;
            <?php _e('Change all leads statuses to','upicrm'); ?>:
            <?php echo $UpiCRMLeadsStatus->select_status_list_no_lead('status_to_all'); ?>
            <input type="submit" class="btn btn-primary" value=" <?php _e('Send','upicrm'); ?>" />
            <br /><br />
        </form>
        <?php
    }
    
    function change_time() {
        $_COOKIE['upicrm_lead_table_days'] = (int)$_GET['days'];
    }
    
    
    function wp_ajax_remove_lead_callback() {
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMLeads->remove_lead($_POST['lead_id']);
        die();
    }
    
    function wp_ajax_remove_lead_arr_callback() {
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMLeads->remove_leads($_POST['lead_id']);
        die();
    }
    
    function wp_ajax_save_lead_callback() {
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMLeads = new UpiCRMLeads();
        $leadObj = $UpiCRMLeads->get_by_id($_POST['lead_id']);
        $updateArr = array();
        
        $updateArr['lead_management_comment'] = $_POST['remarks'];
        if ($leadObj->user_id != $_POST['user_id']) {
            $updateArr['user_id'] = $_POST['user_id'];
        }
        if ($leadObj->lead_status_id != $_POST['lead_status_id']) {
            $updateArr['lead_status_id'] = $_POST['lead_status_id'];
        }
        
        $UpiCRMLeads->update_by_id($_POST['lead_id'], $updateArr);
        $user = get_user_by('id', $_POST['user_id']);
        
        if ($leadObj->user_id != $_POST['user_id']) {
            $UpiCRMMails->send($_POST['lead_id'], "change_user", $user->user_email);
        }
        
         if ($leadObj->lead_status_id != $_POST['lead_status_id']) {
            $UpiCRMMails->send($_POST['lead_id'], "change_lead_status", $user->user_email);
        }
        
        
        echo 1;
        die();
    }
    
   function wp_ajax_save_lead_user_arr_callback() {
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMLeads = new UpiCRMLeads();
        $user_id = $_POST['user_id'];
        
        
        foreach ($_POST['lead_id'] as $lead_id) {
            $updateArr = array();
            $leadObj = $UpiCRMLeads->get_by_id($lead_id);
            if ($leadObj->user_id != $user_id) {
                $updateArr['user_id'] = $user_id;
                $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                $user = get_user_by('id', $user_id);
                $UpiCRMMails->send($lead_id, "change_user", $user->user_email);
            }
        }
        echo 1;
        die();
    }
    
    function wp_ajax_save_lead_status_arr_callback() {
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMLeads = new UpiCRMLeads();
        $lead_status_id = $_POST['lead_status_id'];
        foreach ($_POST['lead_id'] as $lead_id) {
            $updateArr = array();
            $leadObj = $UpiCRMLeads->get_by_id($lead_id);
            if ($leadObj->lead_status_id != $lead_status_id) {
                $updateArr['lead_status_id'] = $lead_status_id;
                $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                $user = get_user_by('id', $leadObj->user_id);
                $UpiCRMMails->send($lead_id, "change_lead_status", $user->user_email);
            }
        }
        echo 1;
        die();
    }
    
    function save_leads_arr() {
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMLeads = new UpiCRMLeads();
        $lead_status_id = $_POST['lead_status_id_all'];
        $user_id = $_POST['user_id_all'];
        
        if ($lead_status_id > 0) {
            foreach ($_POST['checklead_check'] as $lead_id) {
                $updateArr = array();
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                //print_r($leadObj);
                if ($leadObj->lead_status_id != $lead_status_id) {
                    $updateArr['lead_status_id'] = $lead_status_id;
                    $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                    $user = get_user_by('id', $leadObj->user_id);
                    $UpiCRMMails->send($lead_id, "change_lead_status", $user->user_email);
                }
            }
        }
        
        if ($user_id > 0) {
            foreach ($_POST['checklead_check'] as $lead_id) {
                $updateArr = array();
                $leadObj = $UpiCRMLeads->get_by_id($lead_id);
                if ($leadObj->user_id != $user_id) {
                    $updateArr['user_id'] = $user_id;
                    $UpiCRMLeads->update_by_id($lead_id, $updateArr);
                    $user = get_user_by('id', $user_id);
                    $UpiCRMMails->send($lead_id, "change_user", $user->user_email);
                }
            }
        }
        
    }
    
    function wp_ajax_request_status_callback() {
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMLeads = new UpiCRMLeads();
        
        $leadObj = $UpiCRMLeads->get_by_id($_POST['lead_id']);
        $user = get_user_by('id', $leadObj->user_id);
        $UpiCRMMails->send($_POST['lead_id'], "request_status", $user->user_email);
        echo 1;
        die();
    }
    
    function wp_ajax_send_lead_again_to_master_callback() {
        $UpiCRMIntegrationsLib = new UpiCRMIntegrationsLib();
        $UpiCRMIntegrationsLib->send_slave($_POST['lead_id']);
        echo 1;
        die();
    }
}

//add_action( 'wp_ajax_excel_output', array(new UpiCRMAdminAdminLists,'wp_ajax_excel_output_callback'));
add_action( 'wp_ajax_remove_lead', array(new UpiCRMAdminAdminLists,'wp_ajax_remove_lead_callback'));
add_action( 'wp_ajax_remove_lead_arr', array(new UpiCRMAdminAdminLists,'wp_ajax_remove_lead_arr_callback'));
add_action( 'wp_ajax_save_lead', array(new UpiCRMAdminAdminLists,'wp_ajax_save_lead_callback'));
add_action( 'wp_ajax_save_lead_user_arr', array(new UpiCRMAdminAdminLists,'wp_ajax_save_lead_user_arr_callback'));
add_action( 'wp_ajax_save_lead_status_arr', array(new UpiCRMAdminAdminLists,'wp_ajax_save_lead_status_arr_callback'));
add_action( 'wp_ajax_request_status', array(new UpiCRMAdminAdminLists,'wp_ajax_request_status_callback'));
add_action( 'wp_ajax_send_lead_again_to_master', array(new UpiCRMAdminAdminLists,'wp_ajax_send_lead_again_to_master_callback'));


endif;