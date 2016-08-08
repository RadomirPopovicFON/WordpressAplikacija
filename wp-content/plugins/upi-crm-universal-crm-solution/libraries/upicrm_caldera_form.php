<?php
add_action("caldera_forms_submit_complete", array(new UpiCRMcaldera,'save_lead'), 55);

class UpiCRMcaldera {
   
    function save_lead($form){
        //save lead 
        global $SourceTypeID;

        $UpiCRMLeads = new UpiCRMLeads();
        
        $content_arr= array();
        foreach( $form[ 'fields' ] as $field_id => $field){
            $content_arr[ $field['slug'] ] = Caldera_Forms::get_field_data( $field_id, $form );
        }
        //$is_mail_sent = wp_mail("roevvvv@gmail.com", "aaa", print_r($_POST,true),$headers);
        $UpiCRMLeads->add($content_arr,$SourceTypeID['caldera'],$_POST['_cf_frm_id']);

    }
    
    function get_all_form() {
        //get all form as array
        $all_forms = Caldera_Forms_Forms::get_forms( true );
        foreach( $all_forms as $form ):
            $arr[$form['ID']] = $form['name'];
        endforeach;
        return $arr;
    }
    
    function get_all_form_fields($source_id){
        //get all form fields by form id
        $getInputs = Caldera_Forms_Forms::get_form($source_id);
        $fields = $getInputs['fields'];
        foreach ($fields as $key => $arr) {
            if ($fields[$key]['type'] != "button" && $fields[$key]['type'] != "html") 
                $inputs[$fields[$key]['slug']] = $fields[$key]['label'];
        }
          
        return $inputs;
    }
    
    function form_name($source_id) {
        //get form name
        $arr = Caldera_Forms_Forms::get_form($source_id);
        return $arr['name'];
    }
    
    function is_active() {
        //is form active
        return is_plugin_active('caldera-forms/caldera-core.php');
    }
    
    /*function import_all() {
        //get all ninja form leads and save it to UpiCRM leads
        global $SourceTypeID;
        $UpiCRMLeads = new UpiCRMLeads();
        
        foreach ($this->get_all_form() as $key => $value) {
            $args = array('form_id'   => $key);
            // This will return an array of sub objects.
            $subs = Ninja_Forms()->subs()->get( $args );
            foreach ($subs as $obj) {
                if (isset($obj->fields)) {
                    $UpiCRMLeads->add($obj->fields,$SourceTypeID['ninja'],$key,false);
                }
            }
        }    
    }*/
}

?>