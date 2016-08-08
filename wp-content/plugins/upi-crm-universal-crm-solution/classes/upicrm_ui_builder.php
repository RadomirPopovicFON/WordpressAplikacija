<?php
class UpiCRMUIBuilder {
    
    function get_list_option() {
        $UpiCRMFields = new UpiCRMFields();
        
        foreach ($UpiCRMFields->get() as $field) { 
           $arr['content'][$field->field_id] = $field->field_name;
        }
        
        $arr['leads']['lead_id'] = __('ID','upicrm');
        $arr['leads']['time'] = __('Time','upicrm');
        $arr['leads']['user_agent'] = __('User Agent','upicrm');
        $arr['leads']['user_referer'] = __('Referer','upicrm');
        $arr['leads']['user_ip'] = __('IP','upicrm');
        
        $arr['special']['actions'] = __('Actions','upicrm');
        $arr['special']['source_id'] = __('Form Name','upicrm');
        $arr['special']['user_id'] = __('Assigned To','upicrm');
        $arr['special']['lead_status_id'] = __('Lead Status','upicrm');
        $arr['special']['lead_management_comment'] = __('Lead Management Comment','upicrm');
        

        $arr['leads_campaign']['utm_source'] = "UTM Source";
        $arr['leads_campaign']['utm_medium'] = "UTM Medium";
        $arr['leads_campaign']['utm_term'] = "UTM Term";
        $arr['leads_campaign']['utm_content'] = "UTM Content";
        $arr['leads_campaign']['utm_campaign'] = "UTM Campaign";
        
        $arr['leads_integration']['lead_id_external'] =  __('Lead ID on remote server','upicrm');
        $arr['leads_integration']['lead_integration_status'] =  __('Transmission Status','upicrm');
        $arr['leads_integration']['integration_domain'] =  __('Remote server domain','upicrm');
        

        return $arr;
    }
    
    function get_list_option_minimum() {
        $arr = $this->get_list_option();
        unset($arr['special']);
        return $arr;
    }
    
    function lead_routing($lead,$route,$value,$map,$noHtml=false) {
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $UpiCRMUsers = new UpiCRMUsers();
               
        switch ($route) {
            case "leads":
                $text = $lead->$value;
            break;
            case "leads_campaign":
                $text = $lead->$value;
            break;
            case "leads_integration":
                $text = $lead->$value;
            break;
            case "content":
                $text = $this->return_lead_content($lead,$value,$map);
            break;
            case "special":
                switch ($value) {
                    case "source_id":
                        $text = $UpiCRMLeads->get_source_form_name($lead->source_id,$lead->source_type);
                    break;
                    case "user_id":
                        if (!$noHtml)
                            $text = $UpiCRMUsers->select_list($lead,"change_user");
                        else 
                            $text = $UpiCRMUsers->get_by_id($lead->$value);
                    break;
                    case "lead_status_id":
                        if (!$noHtml)
                            $text = $this->select_status_list($lead,"change_lead_status");
                        else 
                            $text = $UpiCRMLeadsStatus->get_status_name_by_id($lead->$value);
                    break;
                    case "lead_management_comment":
                        if (!$noHtml)
                            $text = $this->remarks_textarea($lead,"change_lead_remarks");
                        else 
                            $text = $lead->lead_management_comment;
                    break;
                    case "actions":
                        if (!$noHtml) {
                            $text= '<div class="upicrm_lead_actions">';
                                if ($lead->is_slave == 1) {
                                    $text.= '<span class="glyphicon glyphicon-repeat" data-callback="send_master_again" data-lead_id="'.$lead->lead_id.'" title="'.__('Send lead again to UpiCRM master','upicrm').'"></span>';
                                }
                                $text.= '<span class="glyphicon glyphicon-question-sign" data-callback="request_status" data-lead_id="'.$lead->lead_id.'" title="'.__('Request status update from lead owner','upicrm').'"></span>'; 
                                $text.= '<span class="glyphicon glyphicon-floppy-save" data-callback="save" data-lead_id="'.$lead->lead_id.'" title="'.__('Save','upicrm').'"></span>';
                                $text.= '<span class="glyphicon glyphicon-edit" data-callback="edit" data-lead_id="'.$lead->lead_id.'" title="'.__('Edit','upicrm').'"></span>'; 
                                $text.= '<span class="glyphicon glyphicon-remove" data-callback="remove" data-lead_id="'.$lead->lead_id.'" title="'.__('Remove','upicrm').'"></span>'; 
                            $text.= '</div>';
                        }
                    break;
                    
                }
            break;
        }
        return $text;
        
    }
    
    function return_lead_content($lead,$value,$map) {
        global $SourceTypeID;
        $content = json_decode($lead->lead_content,true);
        
        if ($lead->source_type != $SourceTypeID['upi_integration']) {
            foreach ($map as $arr) {
                if ($lead->source_id == $arr->source_id && $lead->source_type == $arr->source_type && $value == $arr->field_id) {
                    if ($content[$arr->fm_name]) {
                        if (!is_array($content[$arr->fm_name])) {
                            $text = $content[$arr->fm_name];
                        }
                        else {
                            $text="";
                            foreach ($content[$arr->fm_name] as $val) {
                               $text.=$val.", "; 
                            }
                        }
                        $is_dynamic_field = true;
                        break;
                    } /*else {
                        //static field
                        $text=$this->return_lead_content_static($content,$value);
                    }*/
                    
                }
            }
            if (!$is_dynamic_field) {
                //static field
                $getFields = unserialize(UPICRM_FIELDS_ARR);
                foreach ($content as $content_key => $content_value) {;
                    if ($getFields[$value] == $content_key) {
                        $text = $this->return_lead_content_static($content, $value);
                        break;
                    }
                }
            }
            
        }
        else {
            //integration
            return $this->return_lead_content_static($content,$value);
        }
        return $text;
    }
    
    function return_lead_content_static($content,$value) {
        $UpiCRMFields = new UpiCRMFields();
        $getFields = unserialize(UPICRM_FIELDS_ARR); 
        if (!$getFields) {
            $getFields = $UpiCRMFields->get_as_array();
        }
        //echo $content[$getFields[$value]];
        //print_r($getFields);
        return isset($content[$getFields[$value]]) ? $content[$getFields[$value]] : '';
    }
    
    function return_lead_content_arr($lead,$value,$map) {
        $content = json_decode($lead->lead_content,true);
        
        foreach ($map as $arr) {
            if ($lead->source_id == $arr->source_id && $lead->source_type == $arr->source_type && $value == $arr->field_id) {
                if (!is_array($content[$arr->fm_name])) {
                    $text = $content[$arr->fm_name];
                }
                $lead_content_arr['text'] = $text;
                $lead_content_arr['fm_name'] = $arr->fm_name;
                $lead_content_arr['field_id'] = $arr->field_id;
                $lead_content_arr['source_id'] = $arr->source_id;
                $lead_content_arr['source_type'] = $arr->source_type;
                break;
            }
        }
        
        return $lead_content_arr;
    }
    
    function select_status_list($lead, $callback) {
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $get_status = $UpiCRMLeadsStatus->get();
        $text ='<select name="lead_status_id" data-lead_id="'.$lead->lead_id.'" data-callback="'.$callback.'">';
            foreach ($get_status as $status) { 
                $selected = selected( $status->lead_status_id, $lead->lead_status_id, false);
                $text.='<option value="'.$status->lead_status_id.'" '.$selected.'>'.$status->lead_status_name.'</option>';
            }
        $text.='</select>';
        return $text;                         
    }

    
    function remarks_textarea($lead, $callback) {
        $text ='<label class="textarea textarea-expandable">';
            $text.='<textarea class="custom-scroll" name="lead_remarks" data-lead_id="'.$lead->lead_id.'" data-callback="'.$callback.'">';
              $text.=$lead->lead_management_comment;
            $text.='</textarea>';
        $text.='</label>';
        return $text;                         
    }
    
}
?>
