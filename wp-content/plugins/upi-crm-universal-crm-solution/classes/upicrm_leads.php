<?php 
class UpiCRMLeads extends WP_Widget {
    var $wpdb;
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function add($lead_content_arr, $source_type, $source_id, $sendEmail=true, $isIntegration=false,$integrationArr=false) {
        //add lead (content as array)
        
        $UpiCRMMails = new UpiCRMMails();
        $UpiCRMLeadsRoute = new UpiCRMLeadsRoute();
        $UpiCRMIntegrations = new UpiCRMIntegrations();
        
        $user = get_users( array( 'role' => 'Administrator' ));
        $user_id = get_option('upicrm_default_lead');

        if (!$isIntegration) {
            $ins['lead_content'] = json_encode($lead_content_arr); //save this in JSON format
        }
        else {
            /*foreach (json_decode($lead_content_arr,true) as $key => $value) {
                echo $key."\n";
            }*/
            $ins['lead_content'] = $lead_content_arr;
        }
        
        $ins['source_type'] = $source_type;
        $ins['source_id'] = $source_id;
	$ins['user_ip'] = !$isIntegration ? $_SERVER['REMOTE_ADDR'] : $integrationArr['user_ip'];
        $ins['user_agent'] = !$isIntegration ? $_SERVER['HTTP_USER_AGENT'] : $integrationArr['user_agent'];
        $ins['user_referer'] =  !$isIntegration ? $_SESSION['upicrm_referer'] : $integrationArr['user_referer']; 
        $ins['lead_status_id'] =  1; 
        $ins['user_id'] =  $user_id; 
        
        
        $user_lead_id  = upicrm_get_user_lead_id();
        if ($user_lead_id != 0) {
            $ins['old_user_lead_id'] = $user_lead_id;
        }
        
        $this->wpdb->insert(upicrm_db()."leads", $ins);
        $last_id = $this->wpdb->insert_id;
        
        if ($user_lead_id == 0 && !$isIntegration) 
            upicrm_set_new_user($last_id);
            
        if (!$isIntegration) {
            if (isset($_SESSION['utm_source']) || isset($_SESSION['utm_medium']) || isset($_SESSION['utm_term']) || isset($_SESSION['utm_content']) || isset($_SESSION['utm_campaign'])) {
                $ins_campaign['lead_id'] = $last_id;
                $ins_campaign['utm_source'] = $_SESSION['utm_source'];
                $ins_campaign['utm_medium'] = $_SESSION['utm_medium'];
                $ins_campaign['utm_term'] = $_SESSION['utm_term'];
                $ins_campaign['utm_content'] = $_SESSION['utm_content'];
                $ins_campaign['utm_campaign'] = $_SESSION['utm_campaign'];
                $this->wpdb->insert(upicrm_db()."leads_campaign", $ins_campaign);
            }
        } else {
            if (isset($integrationArr['utm_source']) || isset($integrationArr['utm_medium']) || isset($integrationArr['utm_term']) || isset($integrationArr['utm_content']) || isset($integrationArr['utm_campaign'])) {
                $ins_campaign['lead_id'] = $last_id;
                $ins_campaign['utm_source'] = $integrationArr['utm_source'];
                $ins_campaign['utm_medium'] = $integrationArr['utm_medium'];
                $ins_campaign['utm_term'] = $integrationArr['utm_term'];
                $ins_campaign['utm_content'] = $integrationArr['utm_content'];
                $ins_campaign['utm_campaign'] = $integrationArr['utm_campaign'];
                $this->wpdb->insert(upicrm_db()."leads_campaign", $ins_campaign);
            }
        }
        
        $UpiCRMLeadsRoute->do_route($last_id);

        //$leadObj = $this->get_by_id($last_id);
        do_action('upicrm_after_new_lead', $last_id);

        if ($sendEmail && !$isIntegration) {
            $UpiCRMMails->send($last_id, "new_lead");
        }        

        //send integrations
        $UpiCRMIntegrationsLib = new UpiCRMIntegrationsLib();
        $UpiCRMIntegrationsLib->send_slave($last_id);
        
        return $last_id;
    }
    
    function update_by_id($lead_id,$updateArr) {
        //update lead by id 
        $this->wpdb->update(upicrm_db()."leads", $updateArr, array("lead_id" => $lead_id));
    }
    
    function get($user_id=0,$page=0,$limit=0,$orderBy="DESC",$check_date=0) {
        
        //get leads
        $UpiCRMUsers = new UpiCRMUsers();
        
        $query = "SELECT *,  ".upicrm_db()."leads.lead_id AS `lead_id`, ".upicrm_db()."leads_integration.integration_is_slave AS `is_slave`, ".upicrm_db()."leads.user_id AS `user_id` FROM ".upicrm_db()."leads";
        $query.= " LEFT JOIN ".upicrm_db()."leads_campaign";
        $query.= " ON ".upicrm_db()."leads_campaign.lead_id = ".upicrm_db()."leads.lead_id";
        $query.= " LEFT JOIN ".upicrm_db()."leads_integration";
        $query.= " ON ".upicrm_db()."leads_integration.lead_id = ".upicrm_db()."leads.lead_id";
        $query.= " LEFT JOIN ".upicrm_db()."integrations";
        $query.= " ON ".upicrm_db()."integrations.integration_id = ".upicrm_db()."leads_integration.integration_id";
        $query.= " LEFT JOIN ".upicrm_db()."users";
        $query.= " ON ".upicrm_db()."users.user_id = ".upicrm_db()."leads.user_id";
        
        if ($check_date > 0) {
            $query.= " WHERE ".upicrm_db()."leads.time > DATE_SUB(CURDATE(), INTERVAL {$check_date} DAY)";
        }
        if ($user_id != 0) {
            if ($check_date > 0) {
                $SQLopretor = "AND";
            }
            else {
                $SQLopretor = "WHERE";
            }
            $users = $UpiCRMUsers->get_childrens_by_parent_id($user_id);
            $child_user_query ="";
            foreach ($users as $user) {
                $child_user_query.= " OR ".upicrm_db()."leads.user_id = {$user->user_id}";
                
            }
            $query.= " {$SQLopretor} (".upicrm_db()."leads.user_id = {$user_id} {$child_user_query})";
        }
        $query.= " ORDER BY ".upicrm_db()."leads.`lead_id` {$orderBy}";
        
        if ($limit > 0) {
            $lim1 = ($page - 1) * $limit;
            $query.= " LIMIT {$lim1},{$limit}";
        }    
        
        //echo $query."<br /><br />";
        $rows = $this->wpdb->get_results($query);
	return $rows;
    }
    
    function get_total($user_id=0) {
        $query = "SELECT `lead_id` FROM ".upicrm_db()."leads";
        if ($user_id > 0) {
            $UpiCRMUsers = new UpiCRMUsers();
            $query.= " LEFT JOIN ".upicrm_db()."users";
            $query.= " ON ".upicrm_db()."users.user_id = ".upicrm_db()."leads.user_id";
            $users = $UpiCRMUsers->get_childrens_by_parent_id($user_id);
            $child_user_query = "";
            foreach ($users as $user) {
                $child_user_query.= " OR " . upicrm_db() . "leads.user_id = {$user->user_id}";
            }
            $query.= " WHERE ".upicrm_db()."leads.user_id = {$user_id} {$child_user_query}";
            //$query.= " WHERE `user_id` = {$user_id}"; 
        }
        $rows = $this->wpdb->get_results($query);
        return $this->wpdb->num_rows;
    }
    
    function get_source_form_name($source_id,$source_type) {
        //get the name of the form by source_id & source_type
        global $SourceTypeID;
        
        switch ($source_type) {
            case $SourceTypeID['gform']:
                $form_name = UpiCRMgform::form_name($source_id);
            break;
            case $SourceTypeID['wpcf7']:
                $form_name = UpiCRMwpcf7::form_name($source_id);
            break;
            case $SourceTypeID['ninja']:
                $form_name = UpiCRMninja::form_name($source_id);
            break;
            case $SourceTypeID['caldera']:
                $form_name = UpiCRMcaldera::form_name($source_id);
            break;
            default:
                $form_name = '';
        }
        
	return $form_name;
    }
    
    function change_user($user_id,$lead_id) {
        //change lead user id
        $this->wpdb->update(upicrm_db()."leads", array("user_id" => $user_id), array("lead_id" => $lead_id));
        do_action('upicrm_after_lead_change_user', $lead_id);
    }
    
    function change_status($lead_status_id,$lead_id) {
        //change lead status id
        $this->wpdb->update(upicrm_db()."leads", array("lead_status_id" => $lead_status_id), array("lead_id" => $lead_id));
        do_action('upicrm_after_lead_change_status', $lead_id);
    }
    
    function remove_lead($lead_id) {
        //delete lead
        $this->wpdb->delete(upicrm_db()."leads", array("lead_id" => $lead_id));
        $this->wpdb->delete(upicrm_db()."leads_campaign", array("lead_id" => $lead_id));
    }
    
    function remove_leads($lead_ids) {
        //delete leads
        foreach ($lead_ids as $lead_id) {
            $this->wpdb->delete(upicrm_db()."leads", array("lead_id" => $lead_id));
            $this->wpdb->delete(upicrm_db()."leads_campaign", array("lead_id" => $lead_id));
        }
    }
    
    function empty_all() {
        //delete all leads mapping
        $this->wpdb->query("TRUNCATE TABLE ".upicrm_db()."leads");  
        $this->wpdb->query("TRUNCATE TABLE ".upicrm_db()."leads_campaign");  
    }
    
    function get_by_id($lead_id) {
        //get lead by id
        
        $query = "SELECT *,  ".upicrm_db()."leads.lead_id AS `lead_id` FROM ".upicrm_db()."leads";
        $query.= " LEFT JOIN ".upicrm_db()."leads_campaign";
        $query.= " ON ".upicrm_db()."leads_campaign.lead_id = ".upicrm_db()."leads.lead_id";
        $query.= " LEFT JOIN ".upicrm_db()."leads_integration";
        $query.= " ON ".upicrm_db()."leads_integration.lead_id = ".upicrm_db()."leads.lead_id";
        $query.= " LEFT JOIN ".upicrm_db()."integrations";
        $query.= " ON ".upicrm_db()."integrations.integration_id = ".upicrm_db()."leads_integration.integration_id";
        $query.= " WHERE ".upicrm_db()."leads.lead_id = {$lead_id}";
        
        $rows = $this->wpdb->get_results($query);
	return $rows[0];
    }
    
}
?>