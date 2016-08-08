<?php
if ( !class_exists('UpiCRMAdminSettings') ):
    class UpiCRMAdminSettings{
        public function Render() {
            global $SourceTypeID;
            $UpiCRMgform = new UpiCRMgform();
            $UpiCRMwpcf7 = new UpiCRMwpcf7(); 
            $UpiCRMninja = new UpiCRMninja(); 
            $UpiCRMcaldera = new UpiCRMcaldera();
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            
            switch ($_POST['action']) {
                case 'save_field':
                    $this->saveField();
                    $msg = __('changes saved successfully','upicrm');
                break;
                case 'save_status':
                    $this->saveStatus();
                    $msg = __('changes saved successfully','upicrm');
                break;
            }
            $tabs_html = '';
            $content_html = '';
            if($UpiCRMgform->is_active()) {                
                foreach ($UpiCRMgform->get_all_form() as $key => $value) {                    
                    $tabs_html .= '<li><a href="#g'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="g'.$key.'"><div class="table-responsive"><table class="table"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMgform->get_all_form_fields($key,true) as $inputName => $inputValue) {
                        $arr = array();
                        $arr["name"] = $inputName;
                        $arr["value"] = $inputValue;
                        $arr["source_id"] = $key;
                        $arr["item_id"] = 'g'.$key;
                        $arr["source_type"] = $SourceTypeID['gform'];
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= '</tbody></table></div></div>';
                }
            }
            if ($UpiCRMwpcf7->is_active()) {
                foreach ($UpiCRMwpcf7->get_all_form() as $key => $value) {                    
                    $tabs_html .= '<li><a href="#f7'.strval($key).'">'.$value.'</a></li>';
                    $content_html .= '<div id="f7'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMwpcf7->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputName;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['wpcf7'];
                        $arr["item_id"] = 'f7'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= '</tbody></table></div></div>';
                }
            }
            
            if ($UpiCRMninja->is_active()) {
                foreach ($UpiCRMninja->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#ninja'.strval($key).'">'.$value.'</a></li>'; 
                    $content_html .= '<div id="ninja'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMninja->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputValue;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['ninja'];
                        $arr["item_id"] = 'ninja'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= '</tbody></table></div></div>';
                }
            }
            
            if ($UpiCRMcaldera->is_active()) {
                  foreach ($UpiCRMcaldera->get_all_form() as $key => $value) {
                    $tabs_html .= '<li><a href="#caldera'.strval($key).'">'.$value.'</a></li>'; 
                    $content_html .= '<div id="caldera'.$key.'"><div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Form Field</th><th>UPiCRM Field</th></tr></thead><tbody>';
                    foreach ($UpiCRMcaldera->get_all_form_fields($key)  as $inputValue => $inputName) {
                        $arr = array();
                        $arr["name"] = $inputValue;
                        $arr["value"] = $inputName;
                        $arr["source_id"] = $key;
                        $arr["source_type"] = $SourceTypeID['caldera'];
                        $arr["item_id"] = 'caldera'.$key;
                        $content_html .= $this->TabContentTemplate($arr);
                    }
                    $content_html .= '</tbody></table></div></div>';
                } 
            }
            
?>

    <div class="row">
        <div>
            <?php
                if (isset($msg)) {
            ?>
            <div class="updated">
                <p><?php echo $msg; ?></p>
            </div>
            <?php
                }
            ?>
        </div>
    
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <?php _e('In this screen you can perform the following actions:','upicrm'); ?> <br />
        <?php _e('1. Map all your site\'s forms and fields into a single, central and manageable data structure. Please use the forms fields mapping wizard below in order to map all you site\'s forms into UpiCRM leads table.','upicrm'); ?> <br />
        <?php _e('2. Add / Edit additional data fields for UpiCRM database: UpiCRM default set of fields can be easily extended to support your unique and specific needs. Just add ANY fields you may need – and map your forms into the new field/s you have defined. ','upicrm'); ?> <br />
        <?php _e('3. Add / edit additional status fields : UpiCRM default set of status fields can be easily extended. Just add ANY status  you may need – and it will become available for you to manage.','upicrm'); ?>       
            
    </div>
        <div class="clearfix"></div>
        <br /><br />
        <a href="admin.php?page=upicrm_allitems&action=reset" class="btn button-primary" onclick="return confirm('<?php _e('are you sure?','upicrm'); ?>');">
            <i class="glyphicon glyphicon-repeat"></i> 
            <?php _e('Reset configuration','upicrm'); ?>
        </a> 
        <?php _e('this option will reset all configuration, but will not delete data from UpiCRM.','upicrm'); ?>
        <br /><br />
        <a href="admin.php?page=upicrm_allitems&action=delete_all" class="btn button-primary" onclick="return confirm('<?php _e('are you sure?','upicrm'); ?>');">
            <i class="glyphicon glyphicon-trash"></i> 
            <?php _e('Delete All data','upicrm'); ?>
        </a>
        <?php _e('this option will delete all data from UpiCRM, but will not delete the configuration.','upicrm'); ?>
        <br /><br />
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h2><?php _e('Map existing forms fields to UpiCRM structured database field:','upicrm'); ?></h2>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
            <div class="table-responsive">
                <div id="tabs">
                    <table id="table-tabs" class="table table-bordered">
                        <thead style="display: none;">
                            <tr>
                                <th><?php _e('Form Name','upicrm'); ?></th>
                                <th><?php _e('Fields','upicrm'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="width: 25%;">
                                    <ul>
                                        <?php                     
            echo $tabs_html;
                                        ?>
                                    </ul>
                                </td>
                                <td class="fields-container"><?php                     
            echo $content_html;
                                                             ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
<div class="clearfix"></div>
<br /><br />
    <div class="row">
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h2><?php _e('Existing Fields','upicrm'); ?></h2>
            <form method="post" action="admin.php?page=upicrm_settings">
                <?php _e('Add additional fields and datatypes to UpiCRM:','upicrm'); ?>
                <input type="hidden" name="action" value="save_field" />
                <input type="text" name="field_name" value="" /><br />
                
                <?php submit_button(__('Add New Field','upicrm')); ?>
            </form>
            <br />
            <?php 
            foreach ($UpiCRMFields->get_as_array() as $key => $value) { ?>
            <?php echo $value; ?><br />
            <?php } ?>
        </div>
        
        
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <h2><?php _e('Existing Statuses','upicrm'); ?></h2>
            <form method="post" action="admin.php?page=upicrm_settings">
                <?php _e('Add additional status to UpiCRM:','upicrm'); ?>
                <input type="text" name="status_name" value="" /><br />
                <input type="hidden" name="action" value="save_status" />
                
                <?php submit_button(__('Add New Status','upicrm')); ?>
            </form>
            <br />
            <?php 
            foreach ($UpiCRMLeadsStatus->get_as_array() as $key => $value) { ?>
                <div class="status_edit" style="display: none;" data-status_id="<?php echo $key; ?>">
                    <input type="text" value="<?php echo $value; ?>" data-callback="edit_input" data-status_id="<?php echo $key; ?>" />
                    <span class="glyphicon glyphicon-floppy-save pointer-click" data-callback="save" data-status_id="<?php echo $key; ?>" title="save"></span>
                    <span class="glyphicon glyphicon-floppy-remove pointer-click" data-callback="cancel" data-status_id="<?php echo $key; ?>" title="cancel"></span>

                </div>
                <div class="status_show" data-status_id="<?php echo $key; ?>">
                    <span class="text"><?php echo $value; ?></span> 
                    <span class="glyphicon glyphicon-edit pointer-click" data-callback="edit" data-status_id="<?php echo $key; ?>" title="edit"></span>
                </div>
            <?php } ?>
        </div>
        
    </div>
    <script type="text/javascript">
        $j(document).ready(function () {
            $j("select[data-callback='save_field']").change(function () {
                var _this = $j(this);
                if (_this.val() != 0) {
                    var data = {
                        'action': 'save_field_mapping_ajax',
                        'fm_name': $j(this).attr("data-name"),
                        'source_id': $j(this).attr("data-source_id"),
                        'source_type': $j(this).attr("data-source_type"),
                        'field_id': $j(this).val(),
                        'fm_id': $j(this).attr("data-fm_id"),

                    };
                    $j.post(ajaxurl, data, function (response) {
                        if (response != 0) {
                            _this.attr("data-fm_id", response);
                            $j.bigBox({
                                title: "Field has been mapped succesfully!",
                                content: "Your new mapping pair is:<br/><h4 style='font-size: 1.3em;'>" + _this.attr("data-value") + " >> " + _this[0].options[_this[0].selectedIndex].text + "</h4>",
                                color: "#739E73",
                                timeout: 5000,
                                icon: "fa fa-check-square-o",
                                number: ""
                            }, function () {
                                closedthis();
                            });
                        }
                        else {
                            _this.val(0);
                            $j.bigBox({
                                title: "Field mapping attempt failed!",
                                content: "Please, select another UPiCRM field.",
                                color: "#C46A69",
                                icon: "fa fa-warning shake animated",
                                number: "",
                                timeout: 4500
                            });
                        }
                    });
                }
            });
            
            $j("*[data-callback='edit']").click(function() {
                var id = $j(this).attr("data-status_id");
                $j(".status_show[data-status_id="+id+"]").hide();
                $j(".status_edit[data-status_id="+id+"]").show();
            });

            $j("*[data-callback='save']").click(function() {
                var id = $j(this).attr("data-status_id");
                var val = $j("input[data-callback='edit_input'][data-status_id="+id+"]").val();
                        var data = {
                            'action': 'change_status_name',
                            'lead_status_id': id,
                            'lead_status_name': val,
                        };
                        $j.post(ajaxurl, data , function(response) {
                            if (response == 1) {
                                $j(".status_edit[data-status_id="+id+"]").hide();
                                $j(".status_show[data-status_id="+id+"]").show();
                                $j(".status_show[data-status_id="+id+"] .text").text(val);
                            }
                            else {
                                alert("Oh no! Error!");
                                console.log(response);
                            }
                        });

            });

            $j("*[data-callback='cancel']").click(function() {
                var id = $j(this).attr("data-status_id");
                $j(".status_edit[data-status_id="+id+"]").hide();
                $j(".status_show[data-status_id="+id+"]").show();
            });
        });
    </script>

    <?php
        }        
   
        function TabContentTemplate($arr) {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $fm_obj = $UpiCRMFieldsMapping->get_by($arr["name"],  $arr["source_id"], $arr["source_type"]);
            $content_html = '<tr><td><label class="control-label">'.$arr["value"].'</label></td><td>';
            
            $content_html .= '<fieldset><section><select data-callback="save_field" data-value="'.$arr["value"].'" data-name="';
            $content_html .= $arr["name"].'" data-source_id="'.$arr["source_id"].'" ';
            $content_html .= 'data-source_type="'.$arr["source_type"].'" data-fm_id="'.$fm_obj->fm_id.'"><option value="0"></option>';
            foreach ($UpiCRMFields->get() as $field) {
                $content_html .= '<option value="'.$field->field_id.'" '.selected( $field->field_id, $fm_obj->field_id ,false).'>'.$field->field_name.'</option>';
            }
            $content_html .= '</select></section></fieldset></td></tr>';
            
            //$content_html .= print_r($fm_obj,true);
            
            return $content_html;
        }
        
        function InputsTemplate($arr) {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            $fm_obj = $UpiCRMFieldsMapping->get_by($arr["name"],  $arr["source_id"], $arr["source_type"]);
            echo $arr["value"];
    ?>
    >>>
            <select data-callback="save_field" data-name="<?php echo $arr["name"];?>" data-source_id="<?php echo $arr["source_id"];?>" data-source_type="<?php echo $arr["source_type"];?>"  data-fm_id="<?php echo $fm_obj->fm_id;?>">
                <option value="0"></option>
                <?php 
            foreach ($UpiCRMFields->get() as $field) { 
                ?>
                <option value="<?php echo $field->field_id; ?>" <?php selected( $field->field_id, $fm_obj->field_id ); ?>><?php echo $field->field_name; ?></option>
                <?php } ?>
            </select>
    <br />
<?php
        }        
        function wp_ajax_save_field_mapping_ajax_callback() {
            $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
            if (!$UpiCRMFieldsMapping->is_exists($_POST['field_id'], $_POST['source_id'], $_POST['source_type'])) {
                echo $UpiCRMFieldsMapping->add_or_update($_POST['fm_id'],$_POST['field_id'], $_POST['fm_name'], $_POST['source_id'], $_POST['source_type']);
            }
            else {
                echo 0;
            }
            die();
        }

        function saveField() {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMFields->add_unique($_POST['field_name']);
        }

        function saveStatus() {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeadsStatus->add_unique($_POST['status_name']);
        }
        
        function wp_ajax_change_status_name_callback() {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $updateArr = array("lead_status_name" => $_POST['lead_status_name']);
            $UpiCRMLeadsStatus->update($updateArr,$_POST['lead_status_id']);
            echo 1;
            die();
        }        
    }    
endif;
add_action( 'wp_ajax_save_field_mapping_ajax', array(new UpiCRMAdminSettings,'wp_ajax_save_field_mapping_ajax_callback'));
add_action( 'wp_ajax_change_status_name', array(new UpiCRMAdminSettings,'wp_ajax_change_status_name_callback'));

