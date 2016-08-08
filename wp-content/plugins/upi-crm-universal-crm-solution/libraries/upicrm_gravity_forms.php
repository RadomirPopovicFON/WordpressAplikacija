<?php
add_action("gform_after_submission", array(new UpiCRMgform,'save_lead'), 10, 2);

class UpiCRMgform {
    
    function save_lead($entry, $form,$group=true){
        //save lead 
        global $SourceTypeID;
        $groupMode = true;

        $UpiCRMLeads = new UpiCRMLeads();
        $gform = GFFormsModel::get_form_meta($entry['form_id']);
   
        $oldGroup = "";
        foreach ($entry as $key => $value) {
            if (is_numeric($key) && $value != "")   {
                if (!$group)
                    $content_arr[$key] = $value;
                else {
                    //group is always true for now
                    $expGroup = explode(".",$key);
                    if ($expGroup[0] != $oldGroup) {
                        $arr = array();
                    }
                    
                    if (isset($expGroup[1])) {
                        $arr[] = $value;
                        $content_arr[$expGroup[0]] = $arr;
                    }
                    else {
                        $content_arr[$expGroup[0]] = $value;
                    }    
                    $oldGroup = $expGroup[0];
                               
                }
            }
        }
       
        $UpiCRMLeads->add($content_arr,$SourceTypeID['gform'],$entry['form_id']);
    }
    
    function get_all_form() {
        //get all gforms as array
        $forms = RGFormsModel::get_forms( null, 'title' );
        foreach( $forms as $form ):
            $arr[$form->id] = $form->title;
        endforeach;
        return $arr;
    }
    
    function get_all_form_fields($form_id,$group=true){
        /* get all gform fields by form id
         * parameter group: group by similar (use for checkbox, radio etc')
         */
        $form = RGFormsModel::get_form_meta($form_id);
        $fields = array();

        if(is_array($form["fields"])){
            foreach($form["fields"] as $field){
                if(isset($field["inputs"]) && is_array($field["inputs"])){

                    foreach($field["inputs"] as $input)
                        $fields[] =  array($input["id"], GFCommon::get_label($field, $input["id"]));
                }
                else if(!rgar($field, 'displayOnly')){
                    $fields[] =  array($field["id"], GFCommon::get_label($field));
                }
            }
        }
        if ($group == true) {
            $oldGroup = "";
            foreach ($fields as $group) {
                $expGroup = explode(".",$group[0]);
                if ($expGroup[0] == $oldGroup) {
                   $GroupFields[$expGroup[0]] = $GroupFields[$expGroup[0]].", ".$group[1];
                }
                else {
                    $GroupFields[$expGroup[0]] = $group[1];
                }    
                $oldGroup = $expGroup[0];
            }
            return $GroupFields;
        }
        else 
            return $fields;
    }
    
    function form_name($source_id) {
        //get gform name
        $gform = GFFormsModel::get_form_meta($source_id);
        return $gform['title'];
    }
    
    function is_active() {
        //is gform active
        return is_plugin_active('gravityforms/gravityforms.php');
    }
    
    function import_all() {
        //get all gform leads and save it to UpiCRM leads
        global $SourceTypeID;
        $groupMode = true;
        
        $UpiCRMLeads = new UpiCRMLeads();
        
        $forms = RGFormsModel::get_forms();
        foreach( $forms as $form ) {
            $getAll[] = GFFormsModel::get_leads($form->id);
        }
        
        $oldGroup = "";
        foreach ($getAll as $fullEntry) {
            foreach ($fullEntry as $entry) {
                unset($content_arr);
                foreach ($entry as $key => $value) {
                    if (is_numeric($key) && $value != "")   {
                        if (!$group)
                            $content_arr[$key] = $value;
                        else {
                            //group is always true for now
                            $expGroup = explode(".",$key);
                            if ($expGroup[0] != $oldGroup) {
                                $arr = array();
                            }

                            if (isset($expGroup[1])) {
                                $arr[] = $value;
                                $content_arr[$expGroup[0]] = $arr;
                            }
                            else {
                                $content_arr[$expGroup[0]] = $value;
                            }    
                            $oldGroup = $expGroup[0];

                        }
                    }
                    if ($key == "form_id")
                        $source_id = $value;
                }
                $UpiCRMLeads->add($content_arr,$SourceTypeID['gform'],$source_id,false);
            }
        }
    }
}

?>