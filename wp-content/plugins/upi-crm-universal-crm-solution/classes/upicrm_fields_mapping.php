<?php
class UpiCRMFieldsMapping extends  WP_Widget {
    var $wpdb;
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function is_exists($field_id, $source_id, $source_type) {
        //checks if the field mapping is already existing in the fields mapping
        $rows = $this->wpdb->get_results("SELECT `fm_id` FROM ".upicrm_db()."fields_mapping WHERE `field_id` = {$field_id} AND `source_id` = '{$source_id}' AND `source_type` = {$source_type}");
	return count($rows) > 0 ? true : false;
    }
    
    function is_id_exists($fm_id) {
        $rows = $this->wpdb->get_results("SELECT `fm_id` FROM ".upicrm_db()."fields_mapping WHERE `fm_id` = {$fm_id}");
	return count($rows) > 0 ? true : false;
    }
    
    function add_or_update($fm_id,$field_id, $fm_name, $source_id, $source_type) {
        //add field mapping, if already exist replace it, return new id
         if ($this->is_id_exists($fm_id)) {
            $this->wpdb->update( 
                 upicrm_db()."fields_mapping", 
                 array( 
                         'field_id' => $field_id, 
                         'fm_name' => $fm_name,
                         'source_id' => $source_id,
                         'source_type' => $source_type
                 ),
                array('fm_id' => $fm_id)
            );
             return $fm_id;
         }
         else {
            $this->wpdb->insert( 
                 upicrm_db()."fields_mapping", 
                 array( 
                         'field_id' => $field_id, 
                         'fm_name' => $fm_name,
                         'source_id' => $source_id,
                         'source_type' => $source_type
                 ));
            return $this->wpdb->insert_id;
         }
    }
    
    function get() {
        //get field mapping
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."fields_mapping");
        return $rows;
    }
    
    function get_by($fm_name, $source_id, $source_type) {
        //get field mapping by: fm_name, source_id, source_type
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."fields_mapping WHERE `fm_name` = '{$fm_name}' AND `source_id` = '{$source_id}' AND `source_type` = {$source_type}");
        return $rows[0];
    }
    
    function get_all_by($source_id, $source_type) {
        //get field mapping by: source_id, source_type
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."fields_mapping WHERE `source_id` = '{$source_id}' AND `source_type` = {$source_type}");
        return $rows;
    }
    
    function get_with_names() {
        //get field mapping with names (JOIN fields)
        $query = "SELECT * FROM ".upicrm_db()."fields_mapping";
        $query.= " JOIN ".upicrm_db()."fields";
        $query.= " ON  ".upicrm_db()."fields.field_id = ".upicrm_db()."fields_mapping.field_id";
        $rows = $this->wpdb->get_results($query);
        return $rows;
    }
    
    function empty_all() {
        //delete all fields mapping
        $this->wpdb->query("TRUNCATE TABLE ".upicrm_db()."fields_mapping");  
    }
}
