<?php
class UpiCRMStatistics extends WP_Widget {
    var $wpdb;
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function get_total_leads() {
        $query = "SELECT `lead_id` FROM ".upicrm_db()."leads";
        $rows = $this->wpdb->get_results($query);
        return $this->wpdb->num_rows;
    }
    
    function get_total_leads_by_user_id($user_id) {
        $UpiCRMUsers = new UpiCRMUsers();
        $query = "SELECT `lead_id` FROM ".upicrm_db()."leads WHERE `user_id` = {$user_id} ";
        foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
            $query.= "OR `user_id` = {$obj->user_id} ";   
        }
        $rows = $this->wpdb->get_results($query);
        return $this->wpdb->num_rows;
    }
    
    function get_total_leads_status_by_user_id($user_id=0) {
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $UpiCRMUsers = new UpiCRMUsers();
        $color = $this->color_array();
        
        if ($user_id > 0)  {
            $query = "SELECT count(*) AS `count` ,`lead_status_id` FROM ".upicrm_db()."leads WHERE `user_id` = {$user_id} ";
            foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
                $query.= "OR `user_id` = {$obj->user_id} ";   
            }
            $query.= "group by `lead_status_id`";
        }
        else 
            $query = "SELECT count(*) AS `count` ,`lead_status_id` FROM ".upicrm_db()."leads group by `lead_status_id`";
        
        $rows = $this->wpdb->get_results($query);
        
        $i=0;
        foreach ($UpiCRMLeadsStatus->get_as_array() as $key => $value) {
            $arr[$i]['lead_status_id'] = $key;
            $arr[$i]['lead_status_name'] = $value;
            $arr[$i]['count'] = 0;
            $arr[$i]['color'] = $color[$i];
            foreach ($rows as $row) {
                if ($row->lead_status_id == $key) {
                    $arr[$i]['count'] = $row->count;  
                }
            }
            $i++;
        }
        return $arr;
    }
    
    function get_total_leads_assigned_by_user_id($user_id=0) {
        $UpiCRMUsers = new UpiCRMUsers();
        $color = $this->color_array();
        
        if ($user_id > 0)  {
            $query = "SELECT count(*) AS `count` ,`user_id` FROM ".upicrm_db()."leads WHERE `user_id` = {$user_id} ";
            foreach ($UpiCRMUsers->get_childrens_by_parent_id($user_id) as $obj) {
                $query.= "OR `user_id` = {$obj->user_id} ";   
            }
            $query.= "group by `user_id`";
        }
        else 
            $query = "SELECT count(*) AS `count` ,`user_id` FROM ".upicrm_db()."leads group by `user_id`";
        
        $rows = $this->wpdb->get_results($query);
        
        $i=0;
        $users_ARR = $UpiCRMUsers->get_as_array();
        
        if ($user_id > 0) {
            foreach ($users_ARR as $key => $value) {
                $delete = true;
                foreach ($rows as $row) {
                    if ($row->user_id == $key) {
                       $delete = false; 
                    }
                }
                if ($delete) {
                    unset($users_ARR[$key]);
                }
            }
        }
        
        foreach ($users_ARR as $key => $value) {
            $arr[$i]['user_id'] = $key;
            $arr[$i]['user_name'] = $value;
            $arr[$i]['count'] = 0;
            $arr[$i]['color'] = $color[$i];
            foreach ($rows as $row) {
                if ($row->user_id == $key) {
                    $arr[$i]['count'] = $row->count;  
                }
            }
            $i++;
        }
        return $arr ? $arr : array();
    }
    
    function get_total_leads_group_field_by_user_id($user_id=0, $field_id) {
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $get_content = array();
        
        $getNamesMap = $UpiCRMFieldsMapping->get(); 
        $list_option = $UpiCRMUIBuilder->get_list_option();
        $getLeads = $UpiCRMLeads->get($user_id);
        
        foreach ($getLeads as $leadObj) {
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if ($key == "content" && $key2 == $field_id) {
                        $get_content[] = $UpiCRMUIBuilder->lead_routing($leadObj,$key,$key2,$getNamesMap,true);
                    }
                }
            }
        }
        $return = array();
        foreach ($get_content as $content) {
            $is_exist = false;
            foreach ($return as $key => $value) {
                if (strtoupper($key) == strtoupper($content)) {
                    $is_exist = true;
                    break;
                }
            }
            if ($is_exist) {
                $return[strtoupper($content)]++;
            }
            else {
                $return[strtoupper($content)] = 1;
            }
        }
        unset($return['']);
        return $return;
    }
    
        function get_total_leads_group_field_name_by_user_id($user_id=0, $field_name) {
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $UpiCRMFields = new UpiCRMFields();
        $get_content = array();
        
        
        $field_id = $UpiCRMFields->get_id_by_name($field_name);
        $getNamesMap = $UpiCRMFieldsMapping->get(); 
        $list_option = $UpiCRMUIBuilder->get_list_option();
        $getLeads = $UpiCRMLeads->get($user_id);

        foreach ($getLeads as $leadObj) {
            foreach ($list_option as $key => $arr) {
                foreach ($arr as $key2 => $value) {
                    if ($key == "content" && $key2 == $field_id) {
                        $get_content[] = $UpiCRMUIBuilder->lead_routing($leadObj,$key,$key2,$getNamesMap,true);
                    }
                }
            }
        }
        $return = array();
        foreach ($get_content as $content) {
            $is_exist = false;
            foreach ($return as $key => $value) {
                if (strtoupper($key) == strtoupper($content)) {
                    $is_exist = true;
                    break;
                }
            }
            if ($is_exist) {
                $return[strtoupper($content)]++;
            }
            else {
                $return[strtoupper($content)] = 1;
            }
        }
        unset($return['']);
        return $return;
    }
 
    
    function get_total_leads_by_weeks($week=0) {
        if ($week == 0) {
            //$saturday = date("Y-m-d",strtotime('last saturday'));
            $weekAgo = date("Y-m-d", strtotime('-7 days'));
            $query = "SELECT count(*) AS `count` FROM ".upicrm_db()."leads 
            WHERE (`time` BETWEEN '{$weekAgo}' AND NOW())";
        }
        else {
            $a = $week * 7;
            $b = $week * 14;
            $weekAgo = date("Y-m-d", strtotime("-{$a} days"));
            $weekAgo2 = date("Y-m-d", strtotime("-{$b} days"));
            
            $query = "SELECT count(*) AS `count` FROM ".upicrm_db()."leads 
            WHERE (`time` BETWEEN '{$weekAgo2}' AND '{$weekAgo}')";
        }
        $rows = $this->wpdb->get_results($query);
        return $rows[0]->count ? $rows[0]->count : 0;
    }
    
    function color_array() {
        return array("blue","red","green","orange","yellow","pink","purple","greenLight","greenDark","orangeDark",'#885886','#578C28','#1A5665','#578C28','#314788','#314788','#4255B3','#5C6B3F','#AD3598');
    }
    
}
