<?php
add_action("ninja_forms_post_process", array(new UpiCRMninja,'save_lead'), 11);


class UpiCRMninja {
   
    function save_lead(){
        //save lead 
        global $SourceTypeID,$ninja_forms_processing;

        $UpiCRMLeads = new UpiCRMLeads();
        
        $form_id = $ninja_forms_processing->get_form_ID();
        $all_fields = $ninja_forms_processing->get_all_fields();

        if( is_array( $all_fields ) ){ 
          foreach( $all_fields as $key => $value ){
              $content_arr[$key] = $value;
          }
        }
       $UpiCRMLeads->add($content_arr,$SourceTypeID['ninja'],$form_id);
    }
    
    function get_all_form() {
        //get all ninja form as array
        $all_forms = ninja_forms_get_all_forms();
        foreach( $all_forms as $form ):
            $arr[$form['id']] = $form['name'];
        endforeach;
        return $arr;
    }
    
    function get_all_form_fields($form_id){
        //get all ninja form fields by form id
        $getInputs = ninja_forms_get_fields_by_form_id($form_id);
        foreach ($getInputs as $arr) {
            if ($arr['type'] != "_submit") {
                $inputs[$arr['id']] = $arr['data']['label'];
            }
        }
          
        return $inputs;
    }
    
    function form_name($source_id) {
        //get ninja form name
        $arr = ninja_forms_get_form_by_id($source_id);
        return $arr['data']['form_title'];
    }
    
    function is_active() {
        //is ninja form active
        return is_plugin_active('ninja-forms/ninja-forms.php');
    }
    
    function import_all() {
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
    }
}

?>