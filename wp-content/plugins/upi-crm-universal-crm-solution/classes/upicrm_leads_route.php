<?php 
class UpiCRMLeadsRoute extends WP_Widget {
    var $wpdb;
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function get() { 
        //get all leads route
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."leads_route ORDER BY `lead_route_id` DESC");
        return $rows;
    }
    
    function get_type_options() { 
        //get all leads route
        $option[1] = __('contains','upicrm');
        //$option[2] = __('does not contain','upicrm');
        $option[3] = __('equals','upicrm');
        $option[4] = __('begins with','upicrm');
        $option[5] = __('smaller than','upicrm');
        $option[6] = __('bigger than','upicrm');
        return $option;
    }
    
    function add($insertArr) { 
        //add leads route
        $this->wpdb->insert(upicrm_db()."leads_route", $insertArr);
    }
    
    function remove($lead_route_id) {
        //delete lead route
        $this->wpdb->delete(upicrm_db()."leads_route", array("lead_route_id" => $lead_route_id));
    }
    
    function get_by_id($lead_route_id) {
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."leads_route WHERE `lead_route_id`={$lead_route_id}");
        return $rows[0];
    }
    
    function update($updateArr, $lead_route_id) { 
        //update lead route
        $this->wpdb->update(upicrm_db()."leads_route", $updateArr , array("lead_route_id" => $lead_route_id));
    }
    
    function do_route($lead_id) {
        //run the route
       global $SourceTypeID;
       $UpiCRMLeads = new UpiCRMLeads();
       $UpiCRMUIBuilder = new UpiCRMUIBuilder();
       $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
       $UpiCRMFields = new UpiCRMFields();
       $getLeads = $UpiCRMLeads->get_by_id($lead_id); //get lead data
       $listOption = $UpiCRMUIBuilder->get_list_option(); //get UI options & existing fields
       $getNamesMap = $UpiCRMFieldsMapping->get_all_by($getLeads->source_id, $getLeads->source_type); //get lead fields mapping
       
       $getFields = $UpiCRMFields->get_as_array();
       $getFields = array_flip($getFields);
       $is_route = false;
       $is_and_route_ok = false;

       foreach ($this->get() as $route) {
            $loop = 1;
            $is_and_route = false;
            if ($route->lead_route_and) {
                $loop = 2;
                $is_and_route = true;
                $route_count = 0;
            }
            foreach ($listOption as $key => $list_option) {
                foreach ($list_option as $key2 => $field_name) {
                    for ($t=1; $t<=$loop; $t++) {
                        /*echo "----\n";
                        echo "$t\n";
                        echo $is_and_route;
                        echo "\n";*/
                        
                        $value = $UpiCRMUIBuilder->lead_routing($getLeads, $key, $key2, $getNamesMap, true);
                        $count = null;
                        if ($t==2) {
                            $count = 2;
                        }
                        $lead_route_option = 'lead_route_option'.$count;
                        $field_id = 'field_id'.$count;
                        $lead_route_type = 'lead_route_type'.$count;
                        $lead_route_value = 'lead_route_value'.$count;
                        //echo "\n";
                        switch ($route->$lead_route_option) {
                            case 'content':
                                $run_route = $route->$field_id == $getFields[$field_name] ? true : false;
                            break;
                            case 'leads_campaign' || 'leads_integration':
                                $run_route = $route->$field_id == $key2 ? true : false;
                            break;
                        }
                        /*echo $is_route ? 1 : 0;
                        echo '..';
                        echo $run_route ? 1 : 0;
                        echo '..';
                        echo $value;
                        echo '..';
                        echo $key;
                        echo '..';
                        echo $key2;

                        echo "\n";*/
                        if (!$is_route && $run_route && $value != "") {
                            switch ($route->$lead_route_type) {
                                case 1:
                                    //contains
                                    $value_arr = explode(",", $route->$lead_route_value);
                                    foreach ($value_arr as $lead_route_value) {
                                        if (strpos(upicrm_string_cleaner($value), upicrm_string_cleaner($lead_route_value)) !== false ) {
                                            
                                            if (($route_count == 1 && $is_and_route) || (!$is_and_route)) {
                                                $is_route = true;
                                                $get_route = $route;
                                            }
                                            $route_count++;
                                        }
                                    }

                                break;
                                /*case 2:
                                    //does not contain
                                    $value_arr = explode(",", $route->lead_route_value);
                                    foreach ($value_arr as $lead_route_value) {
                                        if (strpos(upicrm_string_cleaner($value), upicrm_string_cleaner($lead_route_value)) === false ) {
                                            $is_route = true;
                                            $get_route = $route;
                                        }
                                    }
                                break;*/
                                case 3:
                                    //equals
                                    $value_arr = explode(",", $route->$lead_route_value);
                                        foreach ($value_arr as $lead_route_value) {
                                        if (upicrm_string_cleaner($value) == upicrm_string_cleaner($lead_route_value)) {
                                            if (($route_count == 1 && $is_and_route) || (!$is_and_route)) {
                                                $is_route = true;
                                                $get_route = $route;
                                            }
                                            $route_count++;
                                        }
                                    }
                                break;
                                case 4:
                                    //begins with
                                    $value_arr = explode(",", $route->$lead_route_value);
                                    foreach ($value_arr as $lead_route_value) {
                                        if (strpos(upicrm_string_cleaner($value), upicrm_string_cleaner($lead_route_value)) === 0) {
                                            if (($route_count == 1 && $is_and_route) || (!$is_and_route)) {
                                                $is_route = true;
                                                $get_route = $route;
                                            }
                                            $route_count++;
                                        }
                                    }
                                break;
                                case 5:
                                    //smaller than
                                    $value_arr = explode(",", $route->$lead_route_value);
                                    foreach ($value_arr as $lead_route_value) {
                                        if (upicrm_string_cleaner($lead_route_value) > upicrm_string_cleaner($value)) {
                                            if (($route_count == 1 && $is_and_route) || (!$is_and_route)) {
                                                $is_route = true;
                                                $get_route = $route;
                                            }
                                            $route_count++;
                                        }
                                    }
                                break;
                                case 6:
                                    //bigger than
                                    $value_arr = explode(",", $route->$lead_route_value);
                                    foreach ($value_arr as $lead_route_value) {
                                        if (upicrm_string_cleaner($lead_route_value) < upicrm_string_cleaner($value)) {
                                            if (($route_count == 1 && $is_and_route) || (!$is_and_route)) {
                                                $is_route = true;
                                                $get_route = $route;
                                            }
                                            $route_count++;
                                        }
                                    }
                                break;

                            } 
                        }
                    }
                }
            }
       }
       

       if ($is_route) {
           $updateArr = array();
           if ($get_route->user_id > 0) {
               $updateArr['user_id'] = $get_route->user_id;
           }
           if ($get_route->lead_status_id > 0) {
               $updateArr['lead_status_id'] = $get_route->lead_status_id;
           }
           if ($get_route->change_field_id > 0) {
                $getFieldsNoFlip = array_flip($getFields);
                $save_key = $getFieldsNoFlip[$get_route->change_field_id];
                $save_value = $get_route->change_field_value;
                
                $lead_content = json_decode($getLeads->lead_content,true);
                $lead_content[$save_key] = $save_value;
                $updateArr['lead_content'] = json_encode($lead_content);
           }
           $UpiCRMLeads->update_by_id($lead_id,$updateArr);
       }
    }
   
    
}
?>