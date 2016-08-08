<?php 
class UpiCRMIntegrations extends WP_Widget {
    var $wpdb;
    
    public function __construct() {
	global $wpdb;
	$this->wpdb = &$wpdb;
    }
    
    function get() { 
        //get all integrations
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."integrations ORDER BY `integration_id` DESC");
        return $rows;
    }
    
    function get_master() { 
        //get all integrations
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."integrations WHERE `integration_is_slave` = 0 ORDER BY `integration_id` DESC");
        return $rows;
    }
    
    function add($insertArr) { 
        //add integration
        //$insertArr['external_key'] = sha1($insertArr['external_domain']."/".$_SERVER['HTTP_HOST']);
        $this->wpdb->insert(upicrm_db()."integrations", $insertArr);
    }
    
    function remove($integration_id) {
        //delete integration
        $this->wpdb->delete(upicrm_db()."integrations", array("integration_id" => $integration_id));
    }
    
    function get_by_id($integration_id) {
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."integrations WHERE `integration_id`={$integration_id}");
        return $rows[0];
    }
    
    function get_by_key($integration_key) {
        $integration_key = esc_sql($integration_key);
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."integrations WHERE `integration_key`='{$integration_key}'");
        return $rows[0];
    }
    
    function get_by_clean_domain($integration_clean_domain) {
        $integration_clean_domain = esc_sql($integration_clean_domain);
        $rows = $this->wpdb->get_results("SELECT * FROM ".upicrm_db()."integrations WHERE `integration_clean_domain`='{$integration_clean_domain}'");
        return $rows[0];
    }
    
    function update($updateArr, $integration_id) { 
        //update integration
        $this->wpdb->update(upicrm_db()."integrations", $updateArr , array("integration_id" => $integration_id));
    }
    
    function add_lead($insertArr) { 
        //add integration
        $this->wpdb->insert(upicrm_db()."leads_integration", $insertArr);
        //print_r( $this->wpdb->last_query );
    }
    
    function get_value_by_lead_and_key($lead_id,$key) {
        $query = "SELECT lead_content FROM ".upicrm_db()."leads";
        $query.= " WHERE ".upicrm_db()."leads.lead_id = {$lead_id}";
        
        $row = $this->wpdb->get_results($query);
        $arr = json_decode($row[0]->lead_content,true);
	return $arr[$key];
        
    }
    
}
?>