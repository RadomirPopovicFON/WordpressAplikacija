<?php 
class UpiCRMLeadsStatus extends WP_Widget {
    var $wpdb;
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function get() { 
        //get all leads status
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."leads_status");
        return $rows;
    }
    
    function get_status_name_by_id($lead_status_id) {
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."leads_status WHERE `lead_status_id`={$lead_status_id}");
        return $rows[0]->lead_status_name;
    }
    
    function get_as_array() { 
        //get all leads status
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."leads_status");
        foreach ($rows as $row) {
            $arr[$row->lead_status_id] = $row->lead_status_name;
        }
        return $arr;
    }
    
    function add($lead_status_name) { 
        //add field
        $this->wpdb->insert(upicrm_db()."leads_status", array("lead_status_name" => $lead_status_name));
    }
    
    function update($updateArr, $lead_status_id) { 
        //update field
        $this->wpdb->update(upicrm_db()."leads_status", $updateArr , array("lead_status_id" => $lead_status_id));
    }
    
    function is_exists($lead_status_name) {
        //checks if the field is already existing 
        $rows = $this->wpdb->get_results("SELECT `lead_status_id` FROM ".upicrm_db()."leads_status WHERE `lead_status_name` = '{$lead_status_name}'");
        return count($rows) > 0 ? true : false;
    }
    
    function add_unique($lead_status_name) { 
        //add unique field name, if name is already exist then add to name 1,2,3 etc
        
        if (!$this->is_exists($lead_status_name)) {
            $this->add($lead_status_name);
        }
        else {
            $i = 1;
            do {
                $i++;
                $lead_status_name_new = $lead_status_name.$i;
            } 
           while ($this->is_exists($lead_status_name_new));
           $this->add($lead_status_name_new);
        }
    }
    
        function select_status_list_no_lead($callback,$name='lead_status_id') {
        $get_status = $this->get();
        $text ='<select name="'.$name.'" data-callback="'.$callback.'">';
        $text.='<option value="0"></option>';
            foreach ($get_status as $status) { 
                //$selected = selected( $status->lead_status_id, $lead->lead_status_id, false);
                $text.='<option value="'.$status->lead_status_id.'" '.$selected.'>'.$status->lead_status_name.'</option>';
            }
        $text.='</select>';
        return $text;                         
    }

    
}
?>