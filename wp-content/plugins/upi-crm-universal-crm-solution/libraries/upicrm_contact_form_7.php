<?php

add_action('wpcf7_before_send_mail', array(new UpiCRMwpcf7,'save_lead'));
 
class UpiCRMwpcf7 {
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function save_lead($cf7) {
       //save lead 
        global $SourceTypeID;
        $UpiCRMLeads = new UpiCRMLeads();

        $submission = WPCF7_Submission::get_instance();
        $get_data = $submission->get_posted_data();

        foreach ($get_data as $key => $value) {
            if (!preg_match('/^\_wpcf7|^\_wpnonce$/',$key)) {
                $content_arr[$key] = $value;
            }
        }
        $UpiCRMLeads->add($content_arr,$SourceTypeID['wpcf7'],$get_data['_wpcf7']);
    }
    
    function get_all_form() {
        //get all wpcf7 as array
        $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1);
        $posts_array = get_posts( $args );
        foreach( $posts_array as $form ):
            $arr[$form->ID] = $form->post_title;
        endforeach;
        return $arr;
    }
    
    function get_all_form_fields($form_id){
        //get all wpcf7 fields by form id
        $getInputs = get_post_meta($form_id,'_form');
        preg_match_all('/(?<=\[)([^\]]+)/',$getInputs[0],$match); 
        foreach ($match[0] as $fields) {
            $inputInfo = explode(" ",$fields); 
            if ($inputInfo[0] != "submit")
                $inputs[] = $inputInfo[1];  
        }
          
        return $inputs;
    }
    
    function form_name($source_id) {
        //get wpcf7 name
        return get_post($source_id)->post_title;
    }

    function is_active() {
        //is wpcf7 active
        return is_plugin_active('contact-form-7/wp-contact-form-7.php');
    }
    
    function is_db_active() {
        //is wpcf7 DB extension active
        if ($this->wpdb->get_var("SHOW TABLES LIKE '{$this->wpdb->prefix}cf7dbplugin_submits'") == $this->wpdb->prefix."cf7dbplugin_submits")
            return true;
        else
            return false;
    }
    
    function import_all() {
        //get all wpcf7 DB extension leads and save it to UpiCRM leads
        
        global $SourceTypeID;
        $UpiCRMLeads = new UpiCRMLeads();
        
        //get from wpcf7 DB
        $table = $this->wpdb->prefix."cf7dbplugin_submits";
        $query = "SELECT `submit_time`,`form_name`,`field_name`,`field_value` FROM {$table} WHERE `field_name` != 'Submitted Login' AND `field_name` != 'Submitted From'";
        $rows = $this->wpdb->get_results($query);
        foreach ($rows as $row) {
            $form[$row->submit_time]['c7_form_name'] = $row->form_name;
            $form[$row->submit_time][$row->field_name] = $row->field_value;
        }
        
       
        foreach ($form as $importArr) {
            $source_id = 0;
            unset($content_arr);
            
             //add wpcf7 id
            foreach ($this->get_all_form() as $formID => $formName) {
                if ($importArr['c7_form_name'] == $formName) {
                    $source_id = $formID;
                }
            }
            
            //add content
            foreach ($importArr as $key => $value) {
                if ($key != 'c7_form_name')
                    $content_arr[$key] = $value; 
            }
            
            //save
            if ($source_id != 0) {
                $UpiCRMLeads->add($content_arr,$SourceTypeID['wpcf7'],$source_id,false);
            }
        }
    }
}
?>