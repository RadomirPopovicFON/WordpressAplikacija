<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Joy_Of_Text_Plugin_Admin Class
*

*/


final class Joy_Of_Text_Plugin_Admin {
        /**
        * Joy_Of_Text_Plugin_Admin The single instance of Joy_Of_Text_Plugin_Admin.
        * @var object
        * @access private
        * @since 1.0.0
        */
        
        private static $_instance = null;
        
        
        /**
        * Constructor function.
        */
        public function __construct () {
            // Register the settings with WordPress.
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            // Register the settings screen within WordPress.
            add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );
        } // End __construct()
        
        /**
        * Main Joy_Of_Text_Plugin_Admin Instance
        *
        * Ensures only one instance of Joy_Of_Text_Plugin_Admin is loaded or can be loaded.
        *
        */
        public static function instance () {
            if ( is_null( self::$_instance ) )
            self::$_instance = new self();
            return self::$_instance;
        } // End instance()
        
        /**
        * Register the admin screen.
        */
        public function register_settings_screen () {
            //$this->_hook = add_submenu_page( 'options-general.php', __( 'Joy Of Text Plugin Settings', 'jot-plugin' ), __( 'JOT Settings', 'jot-plugin' ), 'manage_options', 'jot-plugin', array( $this, 'settings_screen' ) );
            add_menu_page(__('Messaging', 'jot-plugin'), __('Messaging', 'jot-plugin'), 'manage_options', 'jot-plugin', array( $this, 'settings_screen' ),'dashicons-phone');
        } // End register_settings_screen()
        
        /**
        * Output the markup for the settings screen.
        */
        public function settings_screen () {
            global $title;
            $sections = Joy_Of_Text_Plugin()->settings->get_settings_sections();
            $tab = $this->_get_current_tab( $sections );
            
            
            $subform = $this->get_subform();
            $tabform = $tab . "-" . $subform;
            
            echo $this->get_admin_header_html( $sections, $title );
            switch ( $tabform ) {
                case 'smsprovider-main'; 
                    $this->write_smsprovider_fields($sections, $tab);          
                break;
                case 'messages-main':
                    $this->write_message_fields($sections, $tab);
                break;
                case 'group-list-main':
                    $this->write_group_list_fields($sections, $tab);
                break;
                case 'group-list-add':
                    $this->write_group_add_fields($sections, $tab);
                break;
                //case 'scheduler-manager-main':                
                //    $this->write_scheduler_fields($sections, $tab);                
                //break;
                case 'extensions-main':                
                    $this->write_extensions_fields($sections, $tab);                
                break;
                default:
                   do_action("jot_render_extension_tab",$tabform);
                break;
            }
                    
        } // End settings_screen()
            
            
        /**
        * Write out message_fields tab screen
        */    
        public function write_smsprovider_fields($sections,$tab) {
            
            echo "<form id=\"smsprovider-fields-form\" action=\"options.php\" method=\"post\">";
            settings_fields( 'jot-plugin-settings-' . $tab );
            //do_settings_sections( 'jot-plugin-' . $tab );
            
            $pagehtml = Joy_Of_Text_Plugin()->settings->render_smsprovider_settings($sections,$tab);
            echo $pagehtml['html'];
            
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            if ($selected_provider != 'default') {
               submit_button( __(  $sections[$tab]['buttontext'], 'jot-plugin' ) );
            }
            echo "</form>";
            echo "<br>";
            
            // Display a guidance messages
            $auth = get_option('jot-plugin-smsprovider');
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            if (isset($auth['jot-accountsid-' . $selected_provider])) {
                $sid = $auth['jot-accountsid-' . $selected_provider];
            } else {
                $sid = null;
            }
            $guidance = "";
            
            if (!is_null($sid)) {               
                if ($pagehtml['message_code'] !=0 ) {
                    $guidance = $pagehtml['message_text'];               
                } elseif ( Joy_Of_Text_Plugin()->settings->get_current_smsprovider_number() == 'default') {
                    $guidance = __( 'Please select your "from" number and save.', 'jot-plugin' );                   
                } 
            }
            
            
            echo "<div id=\"jot-messagestatus\" class=\"jot-messagered\">$guidance</div>";
            echo "<br>";
            echo "<br>";
            echo "<br>";
            //echo "<div class=''><a href='http://www.getcloudsms.com/?p=33&upgrade' target='_blank' class='button button-primary'>" .  __("Upgrade to the Joy Of Text Pro Version","jot-plugin") . "</a><br><br>" . __("For feedback and support, please send an email to", "jot-plugin") . " " . "<a href=\"mailto:jotplugin@gmail.com\">jotplugin@gmail.com</a></div>";
            echo "<div class=''>" . __(" Joy Of Text Lite Version : ","jot-plugin") . Joy_Of_Text_Plugin()->version . " (". Joy_Of_Text_Plugin()->product .")" . "<br>" . _("For feedback and support, please send an email to") . " " . "<a href=\"mailto:jotplugin@gmail.com\">jotplugin@gmail.com</a></div>";
            
           
        }
        
        /**
        * Write out message_fields tab screen
        */
        public function write_message_fields($sections,$tab) {
            
            echo "<form id=\"jot-message-field-form\" action=\"\" method=\"post\">";
            settings_fields( 'jot-plugin-settings-' . $tab );
            //do_settings_sections( 'jot-plugin-' . $tab );
            echo Joy_Of_Text_Plugin()->settings->render_message_panel($sections,$tab);
            echo "<a href=\"#\" class=\"button button-primary\" id=\"jot-sendmessage\">Send your message</a>"; 
            echo "</form>";
            echo "<br>";
            echo "<div id=\"jot-messagestatus\"></div>";
            echo "<div id=\"jot-sendstatus-div\">";
            echo "</div>";
            //echo "</div><!--/.wrap-->";
        }
        
        public function write_group_list_fields($sections,$tab) {
            
            
            echo "<form id=\"group-list-fields-form\" action=\"\" method=\"post\">";
            echo "<input type=\"hidden\"  name=\"jot-form-id\" value=\"jot-group-add\">";
            settings_fields( 'jot-plugin-settings-' . $tab );            
            echo "</form>";
            //echo "</div><!--/.wrap-->";
            echo "<br>";
            echo "<br>";
            echo "<br>";
            
            
            $lastid = Joy_Of_Text_Plugin()->lastgrpid;
            wp_localize_script( 'jot-js', 'jot_lastgroup',
		       array( 'id' => $lastid ) );
            echo Joy_Of_Text_Plugin()->settings->render_grouplisttabs();
            echo Joy_Of_Text_Plugin()->settings->render_groupdetails($sections, $tab, $lastid);
            echo Joy_Of_Text_Plugin()->settings->render_groupmembers($sections, $tab, $lastid);
            echo Joy_Of_Text_Plugin()->settings->render_groupinvites($sections, $tab, $lastid);             
 
            do_action("jot_render_extension_subtab",$sections, $tab, $lastid);
                     
        }
        
        public function write_group_add_fields($sections,$tab) {
            
            if( isset($_GET['settings-updated']) ) { 
                echo "<div id=\"message\" class=\"updated\">";
                echo "<p><strong>" . _e('Settings saved.') . "</strong></p>";
                echo "</div>";
            }
            //echo "<form id=\"group-add-fields-form\" action=\"" . plugins_url( 'jot-options.php\"', __FILE__ ) . " method=\"post\">";
            echo "<form id=\"jot-group-add-fields-form\" action=\"\" method=\"post\">";
            echo "<input type=\"hidden\"  name=\"jot_form_id\" value=\"jot-group-add\">";
            echo "<input type=\"hidden\"  name=\"jot_form_target\" value=\"main\">";
            settings_fields( 'jot-plugin-settings-' . $tab );
            //do_settings_sections( 'jot-plugin-' . $tab );
            echo Joy_Of_Text_Plugin()->settings->render_groupadd($sections, $tab);
            echo "<div class='jot-group-add-buttons'>";
            echo "<a href=\"#\" class=\"button button-primary\" id=\"jot-addgroup\">Add new group</a>";
            echo "<div class=\"divider\"></div>";
            echo "<a href=\"#\" class=\"button button-primary\" id=\"jot-addgroupcancel\">Cancel</a>"; 
            echo "</div>";
            echo "</form>";
            echo "<br>";
            echo "<div id=\"jot-messagestatus\"></div>";
            echo "</div><!--/.wrap-->";
        }
        
        public function write_extensions_fields($sections,$tab) {
            
             
             //if (is_plugin_active('jot-scheduler/jot-scheduler.php')) {
             //   do_action("jot_render_extension_tab",$tabform);
             //} else {
                echo "<span style='font-size:28px;color:black'>";
                echo "A number of extensions are available for the Joy Of Text Lite plugin, these include:";
                echo "</span>";
                echo "<br><br><p>";
                
                echo "<div style='display: block;'>";
                echo "<div style='margin:0px 0px 10px 0px;float:left;'>";
                echo "<a href='http://www.getcloudsms.com/downloads/joy-text-message-scheduler-extension/'>";
                echo  "<img src='" . plugins_url('images/jotsched.png', dirname(__FILE__)) . "' width='320' height='200' alt='" . __("JOT Scheduler Extension","jot-plugin") . "' style='margin:0px 10px 0px 0px'>";
                echo "</a>";
                echo "</div>";
                echo "<p style='font-size:20px;color:black'>";
                echo __("The JOT Scheduler extension allows you to schedule a batch of messages to be sent at a future date and","jot-plugin");
                echo "<br>" . __("time and seemlessly integrates into the existing JOT Pro and Lite screens.","jot-plugin");
                echo "</div>";
                
                               
                echo "<div style='display: block;clear:left;'>";
                echo "<div style='margin:0px 0px 10px 0px;float:left;'>";
                echo "<a href='http://www.getcloudsms.com/downloads/joy-of-text-post-and-comment-wordpress-sms-notifier'>";
                echo  "<img src='" . plugins_url('images/jotnotify.png', dirname(__FILE__)) . "' width='320' height='200' alt='" . __("JOT Post Notifier Extension","jot-plugin") . "' style='margin:0px 10px 0px 0px'>";
                echo "</a>";
                echo "</div>";
                echo "<p style='font-size:20px;color:black;'>";
                echo __("This extension will send SMS messages to group members when a new post is added, when a post is updated,","jot-plugin");
                echo "<br>" . __("or when a new comment is added.","jot-plugin");
                echo "</div>";
                                
                echo "<div style='display: block;clear:left;'>";
                echo "<div style='margin:0px 0px 10px 0px;float:left;'>";
                echo "<a href='http://www.getcloudsms.com/downloads/joy-of-text-woocommerce-integration-extension//'>";
                echo  "<img src='" . plugins_url('images/jotwoo.png', dirname(__FILE__)) . "' width='320' height='200' alt='" . __("JOT Woocommerce Extension","jot-plugin") . "' style='margin:0px 10px 0px 0px'>";
                echo "</a>";
                echo "</div>";
                echo "<p style='font-size:20px;color:black;'>";
                echo __("This plugin lets you to synchronise your Woocommerce customers into the Joy Of Text,","jot-plugin");
                echo "<br>" . __("allowing you to send messsages to your valued Woo customers.","jot-plugin");
                echo "</div>";
                echo "<br><br><p><p>";
                
                
                echo "<span style='display: block;clear:left;font-size:28px;color:black'>";
                echo __("Also checkout the Joy Of Text Pro","jot-plugin");
                echo " and its fab features including : ";                
                echo "</span>";
                echo "<p>";
                echo "<div style='display: block;'>";
                echo "<div style='margin:0px 0px 10px 0px;float:left;'>";
                echo "<a href='http://www.getcloudsms.com/downloads/joy-of-text-pro-version-3/'>";
                echo  "<img src='" . plugins_url('images/jotpro.png', dirname(__FILE__)) . "' width='320' height='200' alt='" . __("Joy Of Text Pro","jot-plugin") . "' style='margin:0px 10px 0px 0px'>";
                echo "</a>";
                echo "</div>";
                echo "<p style='font-size:20px;color:black;'>";                         
                echo "<span style='margin-left:20px;font-size:20px;color:black'>" . "- Multiple member groups." . "</span>";
                echo "<br><span style='margin-left:20px;font-size:20px;color:black'>" . "- Receiving SMS messages." . "</span>";
                echo "<br><span style='margin-left:20px;font-size:20px;color:black'>" . "- Subscriber opt-in and opt-out supported." . "</span>";
                echo "<br><span style='margin-left:20px;font-size:20px;color:black'>" . "- Sending MMS messages (USA and Canada only)." . "</span>";
                echo "<br><span style='margin-left:20px;font-size:20px;color:black'>" . "- Subscribing to groups using a text message." . "</span>";
                echo "<br><span style='margin-left:20px;font-size:20px;color:black'>" . "- Woocommerce integration." . "</span>";
                echo "<br><span style='margin-left:20px;font-size:20px;color:black'>" . "- Administrative commands available from your mobile phone....and many more!" . "</span>";
                echo "</div>";
             
                                
                echo "<br><br><p><span style='font-size:28px;color:black'>";
                echo "If you have comments, feedback or suggestions, please send to them using the form below. Thank you.";
                echo "</span>";
                
                if(isset($_POST['feedbacksub']))  {
                  $fields = $_POST['jot-plugin-extensions'];                 
                  
                  
                  // Send email
                  $to = 'jotplugin@gmail.com';
                  $subject = "JOT Lite feedback form.";
                  $message = "Name : "  . $fields['jot-extensions-name'] . "\r\n";
                  $message .= "Email : " . $fields['jot-extensions-email'] . "\r\n";
                  $message .= "Message : " . $fields['jot-extensions-message'] . "\r\n";
                  $message .= "Newsletter : " . $fields['jot-extensions-mail'] . "\r\n";
                                  
                  
                  if (!empty($fields['jot-extensions-message'])) {
                    echo "<br><br><p><span style='font-size:28px;color:green'>";
                    echo "Thank you for your comments.";
                    echo "</span>";
                    $send_email = wp_mail( $to, $subject, $message );
                  }
                }
                
                
                $html = "";
                //$html .= Joy_Of_Text_Plugin()->settings->render_section_header(__("Comments, suggestions, feedback","jot-plugin")); 
                $html .= "<form action='' method='post'>";
                $html .= "<table class=\"jot-formtab form-table\">\n";
                $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-extensions-name','','',$tab);
                $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-extensions-email','','',$tab);
                $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-extensions-message','','',$tab);
                 $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-extensions-mail','','',$tab);
                $html .= "</table>";                
                $html .= "<input class='button button-primary' type='submit' name='feedbacksub' value='Send'>";                
                $html .= "</form>";
                
                
                echo $html;
                
           
              
                
            
        }    
        
        /**
        * Register the settings within the Settings API.
        */
        public function register_settings () {
                           
                    register_setting( 'jot-plugin-settings-smsprovider', 'jot-plugin-smsprovider',array($this,'sanitise_settings'));
                    register_setting( 'jot-plugin-settings-messages', 'jot-plugin-messages');
                    register_setting( 'jot-plugin-settings-group-list', 'jot-plugin-group-list');
                    register_setting( 'jot-plugin-settings-woocommerce', 'jot-plugin-woo-manager');
                    
        } // End register_settings()
        
        public function sanitise_settings($input) {
                        
            if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
            }
            parse_str( $_POST['_wp_http_referer'], $referrer );
            
            if (isset($referrer['tab'])) {                 
                   $tab = $referrer['tab'];
            } else {
                   return $input;
            }
            
                        
            if (isset($referrer['section'])) {                 
                   $sectiontab = $referrer['section'];
            } else {
                   return $input;
            }
                        
            $input = $input ? $input : array();
            
            // Get existing settings array
            $smsdetails = get_option('jot-plugin-smsprovider') ? get_option('jot-plugin-smsprovider') : array() ;
            
            // If there are fields of type checkbox for this tab, that are not in input then set them to false            
            $fields = Joy_Of_Text_Plugin()->settings->get_settings_fields($tab);
         
            foreach ($fields as $key => $value) {
                if (isset($value['sectiontab'])) {                    
                    if ($value['type'] == 'checkbox' && $value['sectiontab'] == $sectiontab) {
                        if (array_key_exists($key, $input)) {//
                            // Key found in input array
                        } else {
                            // Key not found so add it into the input array
                            $input[$key] = false;
                        }
                    }
                }
            }
                        
            // Merge new settings with existing settings (priority goes to left hand array)
            $smsdetails_merge = $input + $smsdetails;
            
            return $smsdetails_merge;
        }
        
        /**
        * Validate the settings.
        */
        public function validate_settings ( $input ) {
            $sections = Joy_Of_Text_Plugin()->settings->get_settings_sections();
            $tab = $this->_get_current_tab( $sections );
            return Joy_Of_Text_Plugin()->settings->validate_settings( $input, $tab );
        } // End validate_settings()

        /**
        * Return marked up HTML for the header tag on the settings screen.
        */
        public function get_admin_header_html ( $sections, $title ) {
            $response = '';
            $defaults = array(
            'tag' => 'h2',
            'atts' => array( 'class' => 'jot-plugin-wrapper' ),
            'content' => $title
            );
            $args = $this->_get_admin_header_data( $sections, $title );
            $args = wp_parse_args( $args, $defaults );
            $atts = '';
            if ( 0 < count ( $args['atts'] ) ) {
                foreach ( $args['atts'] as $k => $v ) {
                    $atts .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
                }
            }
            $response = '<' . esc_attr( $args['tag'] ) . $atts . '>' . $args['content'] . '</' . esc_attr( $args['tag'] ) . '>' . "\n";
            return $response;
        } // End get_admin_header_html()
       
        /**
        * Return the current tab key.
        */
        private function _get_current_tab ( $sections = array() ) {
            if ( isset ( $_GET['tab'] ) ) {
                $response = sanitize_title_with_dashes( $_GET['tab'] );
            } else {
                if ( is_array( $sections ) && ! empty( $sections ) ) {
                list( $first_section ) = array_keys( $sections );
                $response = $first_section;
                } else {
                $response = '';
                }
            }
            return $response;
        } // End _get_current_tab()
        
        /**
        * Return the current subform key.
        */
        
        private function get_subform () {
            if ( isset ( $_GET['subform'] ) ) {
                $response = sanitize_title_with_dashes( $_GET['subform'] );
            } else {
                $response = 'main';               
            }
            return $response;
        } // End _get_current_tab()
       
        
        /**
        * Return an array of data, used to construct the header tag.
        */
        private function _get_admin_header_data ( $sections, $title ) {
            $response = array( 'tag' => 'h2', 'atts' => array( 'class' => 'jot-plugin-wrapper' ), 'content' => $title );
                if ( is_array( $sections ) && 1 < count( $sections ) ) {
                $response['content'] = '';
                $response['content'] = '<a href="http://www.getcloudsms.com/lite-documentation/" target="_blank" class="nav-tab" id="jot-help" title="Help"><img src="' . plugins_url( 'images/help.png', dirname(__FILE__) ) .  '" title="Help" id="jot-help-image"></a>';
                $response['atts']['class'] = 'nav-tab-wrapper';
                $tab = $this->_get_current_tab( $sections );
                foreach ( $sections as $key => $value ) {
                    $class = 'nav-tab';
                    if ( $tab == $key ) {
                    $class .= ' nav-tab-active';
                    }
                    $response['content'] .= '<a href="' . admin_url( 'admin.php?page=jot-plugin&tab=' . sanitize_title_with_dashes( $key ) ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $value['tabname']) . '</a>';
                }
            }
            return (array)apply_filters( 'jot-plugin-get-admin-header-data', $response );
        } // End _get_admin_header_data()

} // End Class