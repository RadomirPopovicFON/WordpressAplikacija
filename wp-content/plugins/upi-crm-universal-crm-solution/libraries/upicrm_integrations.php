<?php
class UpiCRMIntegrationsLib {
    
    function route($action,$key) {
        //$return_arr['is_upicrm'] = true;
        switch ($action) {
            case "check_status":
                //$return_arr['status'] = $this->get_check_status($key);
                $this->get_check_status($key);
            break;
            case "save_lead":
                $this->save_lead($key);
            break;
        }
        //echo json_encode($return_arr);
        die();
    }
    
    function build_url($IntegrationOBJ,$action) {
        $url = $IntegrationOBJ->integration_domain."?upicrm_integration_action=".$action."&upicrm_integration_key=".$IntegrationOBJ->integration_key;
        return $url;
    }
    
    function check_key($key) {
        $UpiCRMIntegrations = new UpiCRMIntegrations();
        $IntegrationOBJ = $UpiCRMIntegrations->get_by_key($key);
        //print_r($IntegrationOBJ);
        if (isset($IntegrationOBJ->integration_id)) {
            if ($IntegrationOBJ->integration_clean_domain == upicrm_parse_url(upicrm_get_referer()) 
                    && ($this->is_token(get_site_url(), upicrm_get_referer(),$key) || $this->is_token(upicrm_get_referer(), get_site_url(),$key))) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
    
    function get_check_status($key) {
        if ($this->check_key($key)) {
            $status = __('Connected + Last checked','upicrm');
            $status.= date(" d/m/Y H:i:s"); 
            $status.= '; ';
            $status.= __('Remote UpiCRM installed: V','upicrm');
            $status.= UPICRM_VERSION;
        } else {
            $status = __('Incorrect API Key','upicrm');
        }
        
        //update localDB
        $UpiCRMIntegrations = new UpiCRMIntegrations();
        $IntegrationOBJ = $UpiCRMIntegrations->get_by_clean_domain(upicrm_parse_url(upicrm_get_referer()));
        $UpiCRMIntegrations->update(array("integration_status" => $status), $IntegrationOBJ->integration_id);
        
        echo $status;
    }
    
    function send_check_status($IntegrationOBJ) {
        $content = $this->get_url($this->build_url($IntegrationOBJ,"check_status"));
        if ($content) {
            $status = $content;
        } else {
            $status = __('http 404 – can\'t find resource','upicrm');
        }
        return $status;
    }
    
    function get_url($url,$post=false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($post) {
            //$post = array_map('urlencode', $post);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_REFERER, get_site_url());
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    function save_lead($key) {
        if (!$this->check_key($key)) {
            echo  __('Incorrect API Key','upicrm');
        }
        else {
            $this->get_input_master($key);
        }
    }
    
    function send_slave($id) {
        $UpiCRMIntegrations = new UpiCRMIntegrations();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        
        $MasterOBJ = $UpiCRMIntegrations->get_master();
        if ($MasterOBJ) {
            $UpiCRMIntegrationsLib = new UpiCRMIntegrationsLib();
            $getLeads = $UpiCRMLeads->get_by_id($id);
            
            $getLeads->lead_status_name = $UpiCRMLeadsStatus->get_status_name_by_id($getLeads->lead_status_id);
            
            $listOption = $UpiCRMUIBuilder->get_list_option(); //get UI options & existing fields
            $getNamesMap = $UpiCRMFieldsMapping->get_all_by($getLeads->source_id, $getLeads->source_type); //get lead fields mapping
            foreach ($listOption as $key => $list_option) {
               foreach ($list_option as $key2 => $field_name) {
                    $value = $UpiCRMUIBuilder->lead_routing($getLeads, $key, $key2, $getNamesMap, true);
                    $lead_content_arr[$field_name] = $value;
               }
            }
           $getLeads->lead_content_arr = json_encode($lead_content_arr,true);
           $getLeads_arr = (array)$getLeads;
           
            foreach($MasterOBJ as $master) {
                $content = $this->get_url($this->build_url($master,"save_lead"),$getLeads_arr);
                if ($content) {
                    if(strlen($content) > 200) {
                       $status = __('UpiCRM is not installed / not upgraded on remote server.','upicrm');
                       $insIL['lead_integration_error'] = 1;
                    }
                    else {
                        $content_exp = explode(";", $content);
                        $insIL['lead_id_external'] = $content_exp[0];
                        $status = $content_exp[1];
                    }
                } else {
                    $status = __('http 404 – can\'t find resource','upicrm');
                    $insIL['lead_integration_error'] = 1;
                }
                
                $insIL['lead_id'] = $id;
                $insIL['integration_id'] = $master->integration_id;
                $insIL['integration_is_slave'] = 1;
                $insIL['lead_integration_status'] = $status;
                $UpiCRMIntegrations->add_lead($insIL);

            }
        }
    }
    
    function get_input_master($key) {
        global $SourceTypeID;
        $UpiCRMIntegrations = new UpiCRMIntegrations();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMLeadsRoute = new UpiCRMLeadsRoute();
        $UpiCRMMails = new UpiCRMMails();
        
        $last_id = $UpiCRMLeads->add($_POST['lead_content_arr'], $SourceTypeID['upi_integration'], 0, true, true, $_POST);
        $status = __('Connected + Last checked','upicrm').date(" d/m/Y H:i:s");
        echo $last_id.';'.$status;
        
        $IntegrationOBJ = $UpiCRMIntegrations->get_by_key($key);
        
        $insIL['lead_id'] = $last_id;
        $insIL['integration_id'] = $IntegrationOBJ->integration_id;
        $insIL['lead_id_external'] = $_POST['lead_id'];
        $insIL['lead_integration_status'] = $status;
        $UpiCRMIntegrations->add_lead($insIL);
        
        $UpiCRMLeadsRoute->do_route($last_id);
        $UpiCRMMails->send($last_id, "new_lead");
        
    }
    
    function is_token($master, $slave,$key) {
        $token_base = '';

        $parse_m = parse_url($master);
        $parse_s = parse_url($slave);
        if ($parse_m !== FALSE && $parse_s !== FALSE || !isset($parse_m['host']) || !isset($parse_s['host']) || empty($parse_m['host']) || empty($parse_s['host']) ) {
            $token = sha1($parse_m['host'].'###&#bbb'.$parse_s['host']);
            if ($token == $key) {
                return true;
            }
        }

        return false;
    }


   
}

?>