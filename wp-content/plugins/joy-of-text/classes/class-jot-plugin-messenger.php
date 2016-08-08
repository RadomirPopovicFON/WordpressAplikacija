<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Joy_Of_Text_Plugin_Messenger Class
*
*/


final class Joy_Of_Text_Plugin_Messenger {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    function __construct() {
                 add_action( 'wp_ajax_send_message', array( &$this, 'send_message_callback' ) );
                 add_action( 'wp_ajax_nopriv_send_message', array( &$this, 'send_message_callback' ) );
                 add_action( 'wp_ajax_queue_message', array( &$this, 'queue_message' ) );
    } // end constructor
 
    private static $_instance = null;
       
    public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
    } // End instance()
 
     /**
     * Queue messages in table before being processed
     */
    public function queue_message() {
    
             global $wpdb;
             $error = 0;
             $scheduled = false;
            
             $formdata = $_POST['formdata'];             
             
             parse_str($formdata['jot-allform'], $output);            
             $message              = isset($output['jot-plugin-messages']['jot-message'])           ? $output['jot-plugin-messages']['jot-message']           : "";
             $mess_type            = isset($output['jot-plugin-messages']['jot-message-type'])      ? $output['jot-plugin-messages']['jot-message-type']      : "";
             $mess_suffix          = isset($output['jot-plugin-messages']['jot-message-suffix'])    ? $output['jot-plugin-messages']['jot-message-suffix']    : "";
             $mess_audioid         = isset($output['jot-plugin-messages']['jot-message-audioid'])   ? $output['jot-plugin-messages']['jot-message-audioid']   : "";
             $mess_mmsimageid      = isset($output['jot-plugin-messages']['jot-message-mms-image']) ? $output['jot-plugin-messages']['jot-message-mms-image'] : "";
             $mess_senderid        = isset($output['jot-plugin-messages']['jot-message-senderid'])  ? $output['jot-plugin-messages']['jot-message-senderid']  : "";
             $schedule_description = isset($output['jot-plugin-messages']['jot-scheddesc'])         ? $output['jot-plugin-messages']['jot-scheddesc']         : "";
             //echo ">>>" . $message . " >>" . $mess_type . " >>" . $mess_suffix. " >>" . $mess_audioid . " >>" . $mess_mmsimageid . " >>" . $mess_senderid;
             
                          
             if ($mess_type=='jot-sms' && empty($message)) {
                // Empty message
                $error = 3;       
             }
             //$message = $output['jot-plugin-messages']['jot-message'];
                        
            if ($mess_type=='jot-call' && empty($message) && (empty($mess_audioid) || $mess_audioid == 'default' )) {
                // Empty audio message
                $error = 6;       
            }
                      
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;            
                        
            if ($selected_provider == 'default' || empty($selected_provider)) {
                 $error = 1;
            }
            
            
            if ($error == 0) {
             
                          // Save message type
                          $smsmessage =  get_option('jot-plugin-messages');
                          $smsmessage['jot-message-type'] = $mess_type;
                             
                          // Save message suffix
                          $smsmessage['jot-message-suffix'] = $mess_suffix;
                            
                          // Save message content
                          $smsmessage['jot-message'] = $message;
                          
                          // Save audio file ID
                          $smsmessage['jot-message-audioid'] = $mess_audioid;
                                                 
                          update_option('jot-plugin-messages',$smsmessage);
                            
                          // Append Message suffix
                          if (!empty($mess_suffix)) {
                                $fullmessage = $message . " " . $mess_suffix ;                     
                          } else {
                                $fullmessage = $message;    
                          }
                             
                          // Save message content for call type
                          $messageid = uniqid(rand(), false);                          
                                             
                          // Batch id for this set of messages
                          $batchid = uniqid(rand(), false);                    
                         
                          $mess_memsel_json = stripslashes($formdata['jot-message-grouplist']);
                          $mess_memsel = json_decode($mess_memsel_json,true);
            
                          //print_r($mess_memsel);
                          
                          // Set schedule timestamp
                          $temp_schedule_timestamp = '2000-01-01 00:00:01';
                          $temp_schedule_timestamp = apply_filters('jot_queue_message_schedule',$temp_schedule_timestamp,$output);
                             
                          if ($temp_schedule_timestamp == '2000-01-01 00:00:01') {
                                       // Not scheduled
                                       $schedule_timestamp = $temp_schedule_timestamp;
                                       $message_status = "P";
                                       $scheduled = false;
                          } else {
                                       // Scheduled
                                       $schedule_timestamp = $temp_schedule_timestamp;
                                       $message_status = "S";
                                       $scheduled = true;                                       
                          }
                          
                          foreach ($mess_memsel as $jotmemid ) {
                                       
                                       //list($jotgrpid,$jotmemid) = explode("-", $memsel, 2);
                                       
                                       $member = $this->get_member($jotmemid);

                                        // Replace tags in message
                                       $finalmessage = "";
                                       $finalmessage = $this->get_replace_tags($fullmessage,$member);
                                       $finalmessage = apply_filters('jot-queue-message',$finalmessage);
                                       
                                       switch ( $mess_type ) {
                                                    case 'jot-sms';
                                                          $message_type = "S";
                                                          $media_id = "";
                                                    break;
                                                    case 'jot-call';
                                                          $message_type = "c";
                                                          $media_id = $mess_audioid;
                                                    break;                                         
                                       }
                                      
                                       $data = array(
                                                  'jot_messqbatchid' => $batchid,
                                                  'jot_messqgrpid'   => 0,
                                                  'jot_messqmemid'   => (int) $jotmemid,
                                                  'jot_messqcontent' => sanitize_text_field ($finalmessage),
                                                  'jot_messqtype'    => $message_type,
                                                  'jot_messqaudio'   => $media_id,
                                                  'jot_messqstatus'  => $message_status,
                                                  'jot_messsenderid' => $mess_senderid,
                                                  'jot_messqschedts' => $schedule_timestamp,
                                                  'jot_messqts'      => current_time('mysql', 1)
                                                  
                                       );
                                       
                                       //echo "==" . print_r($data,true);
                                                                              
                                       $table = $wpdb->prefix."jot_messagequeue";
                                       $success=$wpdb->insert( $table, $data );              
                          }
                          
                          // if this is a scheduled queue then add record to schedule history.
                          if ($scheduled == true) {                            
                              do_action("jot_sched_add_schedule",$batchid, $schedule_timestamp, $message_status, $message, count($mess_memsel), $schedule_description);
                          }
             }
            
            
             switch ( $error ) {
                case 0; // All fine
                       $msg = "";
                break;
                case 1; // No SMS provider set
                       $msg = __("Please select and configure an SMS provider.", "jot-plugin");         
                break;
                case 3; // Message is empty
                       $msg = __("Please enter a message.", "jot-plugin");         
                break;
                case 6; // No audio file selected.
                       $msg = __("Please enter a message or select an audio file for a call message", "jot-plugin");
                break;
                default;
                       $msg = "";
                break;
             }
     
             
            $response = array('errormsg'=> $msg, 'errorcode' => $error, 'batchid' => $batchid, 'fullbatchsize' => count($mess_memsel), 'scheduled' => $scheduled);
            echo json_encode($response);        
            
            die(); // this is required to terminate immediately and return a proper response            
             
    }

 
    /**
     * JavaScript callback used to send the message entered by the admin user via Twilio
     */
    public function send_message_callback() {
    
          
            $error = 0;
                         
            
            $formdata = $_POST['formdata'];
            parse_str($formdata, $output);
            $message = $output['jot-plugin-messages']['jot-message'];
            $mess_type = $output['jot-plugin-messages']['jot-message-type'];
            $mess_suffix = $output['jot-plugin-messages']['jot-message-suffix'];
            $jotmemid = $_POST['jotmemid'];
            $member = $this->get_member($jotmemid);
               
            if (empty($message)) {
                // Empty message
                $error = 3;       
            }                             
            
            if ($error == 0) {
                          if (Joy_Of_Text_Plugin()->currentsmsprovider) {
                    
                                // Save message type
                                $smsmessage =  get_option('jot-plugin-messages');
                                $smsmessage['jot-message-type'] = $mess_type;
                                   
                                // Save message suffix
                                $smsmessage['jot-message-suffix'] = $mess_suffix;
                                  
                                // Save message content
                                $smsmessage['jot-message'] = $message;
                                
                                update_option('jot-plugin-messages',$smsmessage);
                                  
                                // Replace tags in message
                                $message = $this->get_replace_tags($message,$member);
                                   
                                // Append Message suffix
                                if (!empty($mess_suffix)) {
                                      $fullmessage = $message . " " . $mess_suffix ;                     
                                } else {
                                      $fullmessage = $message;    
                                }
                                   
                                $fullmessage = apply_filters('jot-send-message-messagetext',$fullmessage);
                    
                                if (!empty($member)) {                    
                                       switch ( $output['jot-plugin-messages']['jot-message-type'] ) {
                                          case 'jot-sms';
                                             $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($member['jot_grpmemnum'],$fullmessage);
                                          break;
                                          case 'jot-call';
                                             $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_callmessage($member['jot_grpmemnum'],$fullmessage);
                                          break;
                                       }
                                }
                                if ($message_error['send_message_errorcode'] != 0) {
                                       //An error occurred sending the message
                                       $error = 999;
                                }
                                $all_send_errors[] = $message_error;
                       
                                   
                          } else {
                                    $error = 1;
                          }
            }
            switch ( $error ) {
                case 0; // All fine
                       $msg = "";
                break;
                case 1; // No SMS provider set
                       $msg = __("Please select and configure an SMS provider.", "jot-plugin");         
                break;
                case 2; // No from number selected
                       $msg = __("Please select a 'from' number on the SMS provider tab.", "jot-plugin");         
                break;
                case 3; // Message is empty
                       $msg = __("Please enter a message.", "jot-plugin");         
                break;
                case 4; // No message recipients selectedMessage is empty
                       $msg = __("Please select your message recipients.", "jot-plugin");         
                break;
                case 5; // Error inserting message into database.
                       $msg = __("Error inserting call message into database", "jot-plugin");
                break;
                default;
                       $msg = "";
                break;
             }
     
             //if ($error != 0 ) {
             //    // Cleanup saved messages
             //    $this->delete_saved_message($messageid);
             //}
      
            $response = array('errormsg'=> $msg, 'errorcode' => $error, 'send_errors'=>$all_send_errors );
            echo json_encode($response);
        
            
            die(); // this is required to terminate immediately and return a proper response
        
 
      
 
    } // end send_message_callback
    
    function get_replace_tags($message,$member) {
                
          $message = str_replace('%name%',$member['jot_grpmemname'], $message);
          $message = str_replace('%number%',$member['jot_grpmemnum'], $message);
          $message = str_replace('%lastpost%',$this->get_last_post(), $message);
              
          return apply_filters('jot_get_replace_tags',$message);   
    }
    
    function get_last_post() {
           $args = array( 'numberposts' => '1' );
           $recent_posts = wp_get_recent_posts( $args );
           foreach( $recent_posts as $recent ){
               return get_permalink($recent["ID"]);
           }     
    }
    
    public function get_member($jotmemid) {
        
            //Get member details for given memberid
            global $wpdb;
            
            $table_members = $wpdb->prefix."jot_groupmembers";
            $sql = " SELECT jot_grpmemid, jot_grpmemname, jot_grpmemnum " .
                   " FROM " . $table_members  .
                   " WHERE jot_grpmemid =" . $jotmemid;
                       
            $member = $wpdb->get_row( $sql );
            $memarr = array("jot_grpmemid" => $jotmemid, "jot_grpmemname" => $member->jot_grpmemname, "jot_grpmemnum" => $member->jot_grpmemnum );
                          
            return apply_filters('jot_get_member',$memarr);
    }
    
    public function save_call_message($messageid, $fullmessage) {
          
             global $wpdb;          
             
             $table = $wpdb->prefix."jot_messages";
             $data = array(
                    'jot_messageid'   => sanitize_text_field ($messageid),
                    'jot_messagecontent' =>sanitize_text_field ($fullmessage)
             );
             $success=$wpdb->insert( $table, $data ); 
             if ($wpdb->last_error !=null) {
                 $this->log_to_file("*** In save_call_message *** " . $messageid . " SQL error : " . $wpdb->last_error);   
                 return 5;
             } else {
                 return 0;     
             }
                                  
    }
    
    public function get_saved_message($messageid) {
        
            //Get message which will be played as a voice call
            global $wpdb;
            
            $table = $wpdb->prefix."jot_messages";
            $sql = " SELECT  jot_messagecontent " .
                   " FROM " . $table  .
                   " WHERE jot_messageid = '" . $messageid . "'";
        
            $message = $wpdb->get_row( $sql );
            $messagecontent = $message->jot_messagecontent;
                          
            return apply_filters('jot_saved_message',$messagecontent);
    }
    
    /*
     *
     * Get groups for display in drop downs
     *
     */
    public function get_display_groups() {
        
			//Get all groups that have been set as auto subscribe
			global $wpdb;
							    
			$table = $wpdb->prefix."jot_groups";
			$sql = " SELECT  jot_groupid, jot_groupname" .
			       " FROM " . $table  .
			       " ORDER BY 2" ;   
		       
			$grplist = $wpdb->get_results( $sql );         
			
			return apply_filters('jot_get_jot_groups',$grplist);
		
    }
    
    public function delete_saved_message($messageid) {
        
            //Delete saved message after voice call
            global $wpdb;
            $table = $wpdb->prefix."jot_messages";
            $success=$wpdb->delete( $table, array( 'jot_messageid' => $messageid ) );
            if ($wpdb->last_error != 0) {
               $this->log_to_file("Error deleting saved messageid:" . $messageid . " " . $wpdb->last_error);
            }
    }
    
    public function log_to_file($text) {
        
        $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
        $file = WP_PLUGIN_DIR. "/joy-of-text/log/jot-" . $selected_provider . "-calls.log";
               
        if(!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);            
        } else {
            file_put_contents($file, "==" . date('m/d/Y h:i:s a', time()) . " " . $text . "\r\n"  ,FILE_APPEND);
        }        
    }
    
   
    
    function call_curl($url,$data, $request_type) {
            if ($this->is_curl_installed()) {
                   
                $TwilioAuth = get_option('jot-plugin-smsprovider');
                $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            
                $sid = $TwilioAuth['jot-accountsid-' . $selected_provider]; 
                $token = $TwilioAuth['jot-authsid-' . $selected_provider];               
                
                $jot_curl = curl_init($url );
               
                // Send data for post requests
                if (strcasecmp($request_type,"post") == 0) {
                    $post = http_build_query($data);
                    curl_setopt($jot_curl, CURLOPT_POST, true);
                    curl_setopt($jot_curl, CURLOPT_POSTFIELDS, $post);
                }
                
                curl_setopt($jot_curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($jot_curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($jot_curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($jot_curl, CURLOPT_USERPWD, "$sid:$token");
               
                $jot_curl_output = curl_exec($jot_curl);
                curl_close($jot_curl);
           } else {
                echo "cURL is NOT installed on this server";
                wp_die();
           }
           return $jot_curl_output;
    }
    
    
    public function get_jot_groupname($jot_groupid) {
			
			//Get group name
			global $wpdb;
							    
			$table = $wpdb->prefix."jot_groups";
			$sql = " SELECT  jot_groupname" .
			       " FROM " . $table  .
			       " WHERE jot_groupid = %d";
			        
		       
		        $sqlprep = $wpdb->prepare($sql,$jot_groupid);
			$grp = $wpdb->get_row( $sqlprep );         
			
			if (!empty($grp->jot_groupname)) {
			    $grpname = $grp->jot_groupname;
			} else {
			    $grpname = "";
			}
			
			return apply_filters('jot_get_jot_groupname',$grpname);
			
    }

             // Check if curl is installed
             function is_curl_installed() {
                 if  (in_array  ('curl', get_loaded_extensions())) {
                    return true;
                 } else {
                    return false;
                 }
             }
            

 
} // end class
 
