<?php
/**
*
* Joy_Of_Text options. Processess requests from the admin pages
*
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Options {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    public function __construct() {
                                    
                add_action( 'wp_ajax_process_forms',        array( $this, 'process_forms' ) );
                add_action( 'wp_ajax_nopriv_process_forms', array( $this, 'process_forms' ) );
                    
                add_action( 'wp_ajax_process_savemem', array( $this, 'process_save_member' ) );
                add_action( 'wp_ajax_process_deletemem', array( $this, 'process_delete_member' ) );
                add_action( 'wp_ajax_process_addmem', array( $this, 'process_add_member' ) );
                  
        
    } // end constructor
 
    private static $_instance = null;
        
    public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
    } // End instance()

     
    public function process_forms () {
        
        
        if (!empty($_POST)) {
            $formdata =  $_POST['formdata'] ;
            parse_str($formdata, $output);
            $jot_form_id = $output['jot_form_id'];
            
            switch ( $jot_form_id ) {
                case 'jot-group-add':
                   $this->process_group_add_form();
                break;
                case 'jot-group-invite-form':
                   $this->process_group_invite_form();
                break;
                case 'jot-subscriber-form':
                   $this->process_subscriber_form();
                break;
                case 'jot-group-details-form':
                   $this->process_group_details_form();
                break;
                            
                default:
                # code...
                break;
            }
           
        }
    }
        
    public function process_group_add_form() {
                
        if ( !current_user_can('manage_options') ) {
              $error=4;  
        }
        
        global $wpdb;
        $error = 0;
        
        $formdata = $_POST['formdata'];
        parse_str($formdata, $output);
        $groupfields = $output['jot-plugin-group-list'];
        
        if (!isset($groupfields['jot_groupdesc']) || str_replace(' ', '',$groupfields['jot_groupdesc']) == '') {
                $error = 2;
        }
        
        if (!isset($groupfields['jot_groupname']) || str_replace(' ', '',$groupfields['jot_groupname']) == '') {
                $error = 1;
        }
        
        $table = $wpdb->prefix."jot_groups";
        
        $group_exists =$wpdb->get_col( $wpdb->prepare( 
                "
                SELECT    jot_groupid 
                FROM        " . $table . "
                WHERE       jot_groupname = %s                 
                ",
                $groupfields['jot_groupname']                
        ) ); 
        
        if ($group_exists) {
                $error=3;
        }
        
        
        if ($error===0) {                
                $data = array(
                    'jot_groupname' => sanitize_text_field ($groupfields['jot_groupname']),
                    'jot_groupdesc' => sanitize_text_field ($groupfields['jot_groupdesc'])            
                );
                $sqlerr=$wpdb->insert( $table, $data );
                $lastid = $wpdb->insert_id;                
        }
        
                      
        switch ( $error ) {
                case 0; // All fine
                       $msg = "";
                break;
                case 1; // Group name not set
                       $msg = __("No group name was entered. Please try again.", "jot-plugin");         
                break;
                case 2; // Group description not set
                       $msg = __("No group description was entered. Please try again.", "jot-plugin");         
                break;
                case 3; // Group with same name already exists
                       $msg = __("A group with this name already exists. Please try again.", "jot-plugin");         
                break;
                case 4; // Not an admin
                       $msg = __("You are not an Admin user.", "jot-plugin");     
                break;
                default:
                # code...
                break;
        }         
        
        
        
        if ($error===0) {
                // Alter URL to set the correct target form       
                $target = remove_query_arg('subform', wp_get_referer() );
                if (isset($_POST['jot-form-target'])) {
                    $target .= add_query_arg( array( 'subform' => $_POST['jot_form_target'] ),  $target );
                }
                // Alter URL to add status and last row ID
                $url = add_query_arg( array( 'settings-updated' => 'true', 'success' => $success, 'lastid' => $lastid ),  $target );
         }
        
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> $url, 'sqlerr' => $wpdb->last_error, 'lastid' => "" );
        echo json_encode($response);
        
        wp_die();
        
    }
    
    public function process_group_details_form() {
                
        if ( !current_user_can('manage_options') ) {
              $error=4;  
        }
        
        global $wpdb;
        $error = 0;
        
        $formdata = $_POST['formdata'];
        parse_str($formdata, $output);
       
        $groupfields = $output['jot-plugin-group-list'];
        
        if (!isset($groupfields['jot_groupdescupd']) || str_replace(' ', '',$groupfields['jot_groupdescupd']) == '') {
                $error = 2;
        }
        
        if (!isset($groupfields['jot_groupnameupd']) || str_replace(' ', '',$groupfields['jot_groupnameupd']) == '') {
                $error = 1;
        }
        
        $table = $wpdb->prefix."jot_groups";
              
        $group_exists =$wpdb->get_col( $wpdb->prepare( 
                "
                SELECT    jot_groupid 
                FROM      " . $table . "
                WHERE     jot_groupname = %s
                AND       jot_groupdesc = %s  
                ",
                $groupfields['jot_groupnameupd'],
                $groupfields['jot_groupdescupd']                
        ) ); 
        
        if ($group_exists) {
                $error=3;
        }
        
               
        if ($error===0) {                
                $data = array(
                    'jot_groupname' => sanitize_text_field ($groupfields['jot_groupnameupd']),
                    'jot_groupdesc' => sanitize_text_field ($groupfields['jot_groupdescupd'])            
                );                
                $sqlerr=$wpdb->update( $table, $data, array( 'jot_groupid' =>  $output['jot_grpid']));
                
        }        
                      
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Group details saved successfully", "jot-plugin");
                break;
                case 1; // Group name not set
                       $msg = __("No group name was entered. Please try again.", "jot-plugin");         
                break;
                case 2; // Group description not set
                       $msg = __("No group description was entered. Please try again.", "jot-plugin");         
                break;
                case 3; // Group with same name already exists
                       $msg = __("A group with this name already exists. Please try again.", "jot-plugin");         
                break;
                case 4; // Not an admin
                       $msg = __("You are not an Admin user.", "jot-plugin");     
                break;
                default:
                # code...
                break;
        }         
        
        
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> "", 'sqlerr' => $wpdb->last_error, 'lastid' => ""  );
        echo json_encode($response);
        
        wp_die();
        
    }
    
    public function process_group_invite_form() {
        
        $error = 0;
        
        if ( !current_user_can('manage_options') ) {
              $error=4;  
        }
        
        global $wpdb;
        
        if ($error==0) {
                $formdata = $_POST['formdata'];
                parse_str($formdata, $output);
                      
                $groupfields = $output['jot-plugin-group-list'];
                $table = $wpdb->prefix."jot_groupinvites";
                
                $invite_exists =$wpdb->get_col( $wpdb->prepare( 
                        "
                        SELECT    jot_grpid  
                        FROM        " . $table . "
                        WHERE       jot_grpid = %s                 
                        ",
                        $groupfields['jot_grpid']                
                ) ); 
        
                if ( $invite_exists ) {
                        $data = array(
                        'jot_grpinvdesc'   => sanitize_text_field ($groupfields['jot_grpinvdesc']),
                        'jot_grpinvnametxt'=> sanitize_text_field ($groupfields['jot_grpinvnametxt']),
                        'jot_grpinvnumtxt' => sanitize_text_field ($groupfields['jot_grpinvnumtxt']),
                        'jot_grpinvretchk' => sanitize_text_field ($groupfields['jot_grpinvretchk'] === 'true' ? 1:0) ,
                        'jot_grpinvrettxt' => sanitize_text_field ($groupfields['jot_grpinvrettxt'])                
                        );
                        
                        $success=$wpdb->update( $table, $data, array( 'jot_grpid' =>  $groupfields['jot_grpid'] ) );
                        
                } else {
                        $data = array(
                        'jot_grpid' => (int) $groupfields['jot_grpid'],
                        'jot_grpinvdesc'   => sanitize_text_field ($groupfields['jot_grpinvdesc']),
                        'jot_grpinvnametxt' =>sanitize_text_field ($groupfields['jot_grpinvnametxt']),
                        'jot_grpinvnumtxt' => sanitize_text_field ($groupfields['jot_grpinvnumtxt']),
                        'jot_grpinvretchk' => sanitize_text_field ($groupfields['jot_grpinvretchk'] === 'true' ? 1:0) ,
                        'jot_grpinvrettxt' => sanitize_text_field ($groupfields['jot_grpinvrettxt'])          
                        
                        );
                        $success=$wpdb->insert( $table, $data );                            
                }
        }
        if ($wpdb->last_error !=null) {
            $error = 1;
        }
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Group details saved successfully", "jot-plugin");
                break;
                case 1; // All fine
                       $msg = __("A database error occurred", "jot-plugin");
                break;
                case 4; // Not an admin
                       $msg = __("You are not an Admin user.", "jot-plugin");     
                break;
                default:
                # code...
                break;
        }             
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> "", 'sqlerr' => $wpdb->last_error, 'lastid' => ""  );
        echo json_encode($response);
        
        
        wp_die();
        
    }
    
    public function process_subscriber_form() {     
        
        
        $error = 0;
        $url = '';
        $lastid='';
        $msgerr = "";
        global $wpdb;
        
        $table = $wpdb->prefix."jot_groupmembers";
        $formdata = $_POST['formdata'];
        parse_str($formdata, $output);
        
        //Strip spaces out of number
        $phone_num = $this->parse_phone_number($output['jot-subscribe-num']);
        
        // Check name is entered
         if (!isset($output['jot-subscribe-name']) || str_replace(' ', '',$output['jot-subscribe-name']) == '') {
                $error = 4;         
        }
                
        // Check phone number
        $removed_plus = false;
        
        // Does phone number start with a plus
        if (preg_match('/^\+/', $phone_num)) {
            $phone_num = substr($phone_num,1);
            $removed_plus = true;
        }
        
        if (!is_numeric($phone_num)) {
             $error = 2;
        }
        
        if ($removed_plus) {
            $phone_num = "+" . $phone_num;
        }
        
        if ($error == 0) {                
                $verified_number = $this->verify_number($phone_num);
                if ( $verified_number == "") {
                    $error = 5;
                }
        }
        
        if ($this->number_exists($table, $verified_number, $output['jot-group-id'])) {
             $error = 3;
        }
        
        if ( $error===0)  {
                $data = array(
                    'jot_grpid' => $output['jot-group-id'],
                    'jot_grpmemname' => sanitize_text_field ($output['jot-subscribe-name']),
                    'jot_grpmemnum' =>  $verified_number            
                );
                    
                    
                $success=$wpdb->insert( $table, $data );
                $lastmemid = $wpdb->insert_id;
                
                
                if ($success === false) {
                        // Insert failed
                        $error=1;
                } else {
                        // Send welcome message if required
                   $msgerr = $this->send_welcome_message($output['jot-group-id'], $verified_number ,$lastmemid);
                }
        } 
        
        switch ( $error ) {
                case 0; // All fine
                       $msg = __('Thank you for subscribing to the group.', 'jot-plugin');
                break;
                case 1; // insert failed
                       $msg = __('An error occurred subscribing to the group.', 'jot-plugin');         
                break;
                case 2; // None numeric phone number
                       $msg = __('The phone number is not numeric. Please try again', 'jot-plugin');         
                break;
                case 3; // Number already exists in this group
                       $msg = __('This phone number is already subscribed to this group.', 'jot-plugin');         
                break;
                case 4; // Member name not set set
                       $msg = __('Please enter your name.', 'jot-plugin');
                break;
                case 5; // Not a valid number
                        $msg = esc_html($phone_num) . __(" - number is not valid. Try again by adding your country code.","jot-plugin");                       
                break; 
                default:
                # code...
                break;
        }         
              
                             
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> "", 'sqlerr' => $wpdb->last_error, 'lastid' => $lastid, 'message_error' => $msgerr );
        echo json_encode($response);
                                    
        wp_die();
        
    }
    
    public function process_add_member($param_memname = null, $param_memnum = null, $param_grpid = null) {
       
        $url ='';
        $errorfield = '';
        $lastmemid = 0;
        $error=0;
        
        //if ( !current_user_can('manage_options') ) {
        //      $error=4;  
        //}

        global $wpdb;
        $table = $wpdb->prefix."jot_groupmembers";
        
        // From POST or from another class?
        if (is_null($param_grpid) ) {
            $formdata = $_POST['formdata'];
            $jot_grpmemname = $formdata['jot_grpmemname'];
            $jot_grpmemnum = $formdata['jot_grpmemnum'];
            $jot_grpid = $formdata['jot_grpid'];           
        } else {
            $jot_grpmemname = $param_memname;
            $jot_grpmemnum  = $param_memnum;
            $jot_grpid = $param_grpid;           
        }
        
             
        // Check name is entered
         if (!isset($jot_grpmemname) || str_replace(' ', '',$jot_grpmemname) == '') {
                $error = 1;         
        }
        
        // Check phone number
        $removed_plus = false;
        $phone_num = $this->parse_phone_number( $jot_grpmemnum );
        
        // Does phone number start with a plus
        if (preg_match('/^\+/', $phone_num)) {
            $phone_num = substr($phone_num,1);
            $removed_plus = true;
        } 
        if (!is_numeric($phone_num)) {
             $error = 2;
        }
        
        if ($removed_plus) {
            $phone_num = "+" . $phone_num;
        }
        
        if ($error == 0) {
            $verified_number = $this->verify_number($phone_num);
            if ( $verified_number == "") {
                $error = 5;
            }
        }
        
        if ($this->number_exists($table, $verified_number, $jot_grpid)) {
             $error = 3;
        }
            
        if ( $error==0 ) {
                
                $data = array(
                    'jot_grpid' => $jot_grpid,
                    'jot_grpmemname' => sanitize_text_field ($jot_grpmemname),
                    'jot_grpmemnum' =>  $verified_number           
                );
                    
                
                $success=$wpdb->insert( $table, $data );
                $lastmemid = $wpdb->insert_id;
                
        }
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("New member added successfully.", "jot-plugin");
                break;
                case 1; // insert failed
                       $msg = __("Name field is blank. Please enter a name", "jot-plugin");
                       $errorfield = isset($formdata['jot_namefield_id']) ? $formdata['jot_namefield_id'] : "";
                break;
                case 2; // None numeric phone number
                       $msg = __("The phone number is not numeric.", "jot-plugin");
                       $errorfield = isset($formdata['jot_numfield_id']) ? $formdata['jot_numfield_id'] : "";                       
                break;
                case 3; // Number already exists in this group
                       $msg = __("Phone number already exists in this group", "jot-plugin");
                       $errorfield = isset($formdata['jot_numfield_id']) ? $formdata['jot_numfield_id'] : "";
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.","jot-plugin");                       
                break;
                case 5; // Not a valid number
                       $msg = esc_html($phone_num) . __(" - number is not valid. Try again by adding your country code.","jot-plugin");                       
                break; 
                default:
                       $msg= "";
                break;
        }    
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => $errorfield,'url'=> "", 'sqlerr' => $wpdb->last_error, 'lastid'=> $lastmemid  );
               
        // If called from frontend
        if (!isset($param_grpid) ) {
           echo json_encode($response);        
           wp_die();
        } else {
            // If called from bulkadd
           return $response;     
        }
                
    }
    
    
    public function process_save_member() {
                
        if ( !current_user_can('manage_options') ) {
              $error=4;  
        }

        global $wpdb;
        
        $errorfield = "";
        $url = "";
        $error=0;
        
        $formdata = $_POST['formdata'];
        $table = $wpdb->prefix."jot_groupmembers";
        
             
        // Check name is entered
         if (!isset($formdata['jot_grpmemname']) || str_replace(' ', '',$formdata['jot_grpmemname']) == '') {
                $error = 1;         
        }
        
        // Check phone number
        $removed_plus = false;
        $phone_num = $this->parse_phone_number( $formdata['jot_grpmemnum'] );
         
        
        // Does phone number start with a plus
        if (preg_match('/^\+/', $phone_num)) {
            $phone_num = substr($phone_num,1);
            $removed_plus = true;
        } 
        if (!is_numeric($phone_num)) {
             $error = 2;
        }
        
        if ($removed_plus) {
            $phone_num = "+" . $phone_num;
        }
        
        if ($error == 0) {                
                $verified_number = $this->verify_number($phone_num);
                if ( $verified_number == "") {
                    $error = 5;
                }
        }
                
        if ($this->number_exists_for_member($table, $verified_number, $formdata['jot_grpid'], $formdata['jot_grpmemid'] )) {
             $error = 3;
        }
            
        if ( $error==0 ) {
                
                $data = array(
                        'jot_grpmemname' => sanitize_text_field ($formdata['jot_grpmemname']),
                        'jot_grpmemnum' =>  sanitize_text_field ($verified_number)
                );
                    
                
                $success=$wpdb->update( $table, $data, array( 'jot_grpid' =>  $formdata['jot_grpid'],'jot_grpmemid' => $formdata['jot_grpmemid'], ) );
                
        }
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Details updated successfully.", "jot-plugin");
                break;
                case 1; // insert failed
                       $msg = __("Name field is blank. Please enter a name", "jot-plugin");
                       $errorfield = $formdata['jot_namefield_id'];
                break;
                case 2; // None numeric phone number
                       $msg = __("The phone number is not numeric.", "jot-plugin");
                       $errorfield = $formdata['jot_numfield_id'];                       
                break;
                case 3; // Number already exists in this group
                       $msg = __("Phone number already exists in this group", "jot-plugin");
                       $errorfield = $formdata['jot_numfield_id'];
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.", "jot-plugin");                       
                break;
                case 5; // Not a valid number
                       $msg = esc_html($phone_num) . __(" - number is not valid. Try again by adding your country code.","jot-plugin");                       
                break; 
                default:
                       $msg= "";
                break;
        }         
        
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => $errorfield,'url'=> "", 'sqlerr' => $wpdb->last_error  );
        echo json_encode($response);
        
        wp_die();
                
    }
    
    public function process_delete_member() {
                
        if ( !current_user_can('manage_options') ) {
              $error=4;  
        }

        global $wpdb;
        $error=0;
        
        $formdata = $_POST['formdata'];
        $table = $wpdb->prefix."jot_groupmembers";
        
                
            
        if ( $error==0 ) {                
                $success=$wpdb->delete( $table, array( 'jot_grpid' =>  $formdata['jot_grpid'],'jot_grpmemid' => $formdata['jot_grpmemid'] ) );                
        }
        
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Member deleted successfully.", "jot-plugin");
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.", "jot-plugin");                       
                break; 
                default:
                       $msg= "";
                break;
        }         
        
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => "",'url'=> "", 'sqlerr' => $wpdb->last_error  );
        echo json_encode($response);
        
        wp_die();
                
    }
    
    public function parse_phone_number($number) {

        $number = str_replace(' ', '', $number);
        return sanitize_text_field($number);

    }
    
    public function number_exists($table, $number, $id) {

         global $wpdb;
         $sql = " SELECT jot_grpmemnum  " .
                " FROM " . $table .
                " WHERE jot_grpid = " . $id .
                " AND jot_grpmemnum = '" . $number . "'";
               
         $numexists = $wpdb->get_results( $sql );
         return $numexists;

    }
    
    // Check whether this number being added for a different member
    // In which case, that's an error
    public function number_exists_for_member($table, $number, $grpid, $memid) {

                
         global $wpdb;
         $sql = " SELECT jot_grpmemnum  " .
                " FROM " . $table .
                " WHERE jot_grpid = " . $grpid .
                " AND   jot_grpmemid != " . $memid . 
                " AND jot_grpmemnum = '" . $number . "'";
               
         $numexists = $wpdb->get_results( $sql );       
         
         return $numexists;

    }
    
    public function send_welcome_message($id, $number,$jotmemid) {

         global $wpdb;
         $table = $wpdb->prefix."jot_groupinvites";
         $sql = " SELECT jot_grpinvretchk,jot_grpinvrettxt  " .
                " FROM " . $table .
                " WHERE jot_grpid = " . $id;               
         
         $welchkbox = $wpdb->get_row( $sql );
         
         if ($welchkbox->jot_grpinvretchk) {
                $member = Joy_Of_Text_Plugin()->messenger->get_member($jotmemid);
                $detagged_message = Joy_Of_Text_Plugin()->messenger->get_replace_tags($welchkbox->jot_grpinvrettxt,$member);
                $msgerr = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($number, $detagged_message);               
         }
         return $msgerr;

    }
    
    
      /*
    *
    * Confirm that the given number is valid by calling Twilio's lookup function.
    *
    */
    public function verify_number($number) {
            
       $intnumber = "";
       $countrycode = "US";
       
       //jot-plugin-smsprovider[jot-smscountrycode]
       $currcc = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-smscountrycode');
       if (isset($currcc)) {
            $countrycode = $currcc;
       }
            
       $data = array();
       
       $url = "https://lookups.twilio.com/v1/PhoneNumbers/" . $number . "?CountryCode=" . $countrycode;
       
       $twilio_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'get');
       
       $twilio_json = json_decode($twilio_response);
       
       if (!empty($twilio_json->phone_number)) {
            $intnumber = $twilio_json->phone_number;
         
       }
       return $intnumber;
            
    }
    
    public function process_jot_edd_activate_license() {
            
            $formdata   = $_POST['formdata'];    
            $licence    = isset($formdata['jot-eddlicence']) ? $formdata['jot-eddlicence'] : "";
            $product    = isset($formdata['jot-eddproduct']) ? $formdata['jot-eddproduct'] : EDD_SL_ITEM_NAME;
            $statuskey  = 'jot-eddlicencestatus';
            $licencekey = 'jot-eddlicence';
            
            $this->process_edd_activate_license($licence,$product,$statuskey,$licencekey);
            
   }
   
   public function process_edd_activate_license($licence,$product,$statuskey,$licencekey) {
            
            
            
            // data to send in our API request
            $api_params = array( 
                    'edd_action'=> 'activate_license', 
                    'license' 	=> $licence, 
                    'item_name' => urlencode( $product ),
                    'url'       => home_url()
            );
            
            
            // Call the custom API.
            $response = wp_remote_post( EDD_SL_STORE_URL, array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => $api_params
            ) );
            
                    

            // make sure the response came back okay
            if ( is_wp_error( $response ) )
                    return false;

            // decode the licence data
            $licence_data = json_decode( wp_remote_retrieve_body( $response ) );
            
            // $licence_data->license will be either "active" or "inactive"      
            Joy_Of_Text_Plugin()->settings->set_smsprovider_settings($statuskey,$licence_data->license);
            Joy_Of_Text_Plugin()->settings->set_smsprovider_settings($licencekey,$licence);
            
        
            echo json_encode(array("activationstatus" => $licence_data->license, "response" => $licence_data));
            wp_die();
   }
   
    
} // end class
 