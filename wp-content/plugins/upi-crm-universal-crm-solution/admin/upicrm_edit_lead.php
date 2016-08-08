<?php
if ( !class_exists('UpiCRMAdminEditLead') ):
class UpiCRMAdminEditLead{
    public function Render() {
        global $SourceTypeID;
        $lead_id = (int)$_GET['id'];

        switch ($_GET['action']) {
             case 'save':
                    $this->updateContent($lead_id,$_POST['is_integration']);
                    $msg =  __('changes saved successfully','upicrm');
            break;
        }
        
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMFields = new UpiCRMFields();
        $UpiCRMIntegrations = new UpiCRMIntegrations();

        $lead = $UpiCRMLeads->get_by_id($lead_id);
        if ($lead->source_type == $SourceTypeID['upi_integration']) {
            $is_integration = true;
            $fields = $UpiCRMFields->get(); 
            foreach ($fields as $field) {
                $list_option[$field->field_name] = $field->field_name;
            }
        }
        else {
            $is_integration = false;
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($lead->source_id, $lead->source_type);
            foreach ($UpiCRMFields->get() as $field) { 
                foreach ($getNamesMap as $map) {
                    if ($map->field_id == $field->field_id)
                        $list_option[$field->field_id] = $field->field_name;  
                }
            }
        }
        
        ?>

            
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
              <form method="post" id="edit_lead_form" action="admin.php?page=upicrm_edit_lead&action=save&id=<?php echo $lead_id; ?>">
            <?php 
                foreach ($list_option as $key => $value) {
                   
                    ?>
                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
                    <?php
                        if (!$is_integration) {
                            $LeadContent = $UpiCRMUIBuilder->return_lead_content_arr($lead,$key,$getNamesMap,$is_integration);
                            ?>
                            <label for="label_<?php echo $LeadContent['fm_name']; ?>"><?php echo $value; ?>:</label><br />
                            <textarea name="<?php echo $LeadContent['fm_name']; ?>" id="label_<?php echo $LeadContent['fm_name']; ?>" style="width: 100%;  color: #000;"><?php echo $LeadContent['text']; ?></textarea>
                            <br /><br />
                        <?php } else { 
                            $LeadContent = $UpiCRMIntegrations->get_value_by_lead_and_key($lead->lead_id,$value); 
                            ?>
                            <label><?php echo $value; ?>:</label><br />
                            <textarea name="<?php echo $value; ?>" style="width: 100%;  color: #000;"><?php echo $LeadContent; ?></textarea>
                            <input type="hidden" name="is_integration" value="1" />
                            <br /><br />
                        <?php } ?>
                        <?php
                    ?>
                    </div>
                    <?php
            }  
            
            $show_arr = array(
                "user_ip",
                "user_agent",
                "utm_source",
                "utm_medium",
                "utm_term",
                "utm_content",
                "utm_campaign",
                "user_referer",
                "time",
            );
            foreach ($lead as $key => $value) {
                foreach ($show_arr as $arr) {
                    if ($key == $arr) {
                    ?>
                        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
                            <label><?php echo $key; ?>:</label><br />
                            <textarea disabled="" style="width: 100%; color: #000;"><?php echo $value; ?></textarea>
                            <br /><br />
                        </div>
                    <?php
                    }
                }
            }
            ?>
                  <div class="clearfix"></div>
                  <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4">
                        <?php submit_button(); ?>
                  </div>
            </form>               
          </div>
        </div>
        <?php

    }
    
    function updateContent($lead_id,$is_integration=false) {
        if (count($_POST) > 0) {
            $UpiCRMLeads = new UpiCRMLeads();
            if ($is_integration == false)
                $lead_content_arr = $_POST;
            else {
                foreach ($_POST as $key => $value) {
                    $lead_content_arr[str_replace("_"," ",$key)] = $value;
                }
            }
            $updateArr['lead_content'] = json_encode($lead_content_arr);
            $UpiCRMLeads->update_by_id($lead_id,$updateArr);
        }
    }
}
endif;