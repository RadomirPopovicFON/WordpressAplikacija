<?php
class UpiCRMFields extends WP_Widget {
    var $wpdb;
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function get() { 
        //get all fields
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."fields");
        return $rows;
    }
    
    function get_name_by_id($field_id) { 
        //get all fields
        $rows = $this->wpdb->get_results("SELECT `field_name` FROM ".upicrm_db()."fields WHERE `field_id`={$field_id}");
        return $rows[0]->field_name;
    }
    
    function get_id_by_name($field_name) { 
        //get all fields
        $rows = $this->wpdb->get_results("SELECT `field_id` FROM ".upicrm_db()."fields WHERE `field_name`='{$field_name}'");
        return $rows[0]->field_id;
    }
    
    function get_as_array() { 
        //get all fields as array (array[id] => name)
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."fields");
        
        foreach ($rows as $row) 
            $arr[$row->field_id] = $row->field_name;  
        
        return $arr;
    }
    
    function add($field_name) { 
        //add field
        $this->wpdb->insert(upicrm_db()."fields", array("field_name" => $field_name));
    }
    
    function is_exists($field_name) {
        //checks if the field is already existing 
        $rows = $this->wpdb->get_results("SELECT `field_id` FROM ".upicrm_db()."fields WHERE `field_name` = '{$field_name}'");
        return count($rows) > 0 ? true : false;
    }
    
    function add_unique($field_name) { 
        //add unique field name, if name is already exist then add to name 1,2,3 etc
        
        if (!$this->is_exists($field_name)) {
            $this->add($field_name);
        }
        else {
            $i = 1;
            do {
                $i++;
                $field_name_new = $field_name.$i;
            } 
           while ($this->is_exists($field_name_new));
           $this->add($field_name_new);
        }
        
        //$this->add($field_name);
    }
}
