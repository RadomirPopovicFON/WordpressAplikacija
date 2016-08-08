<?php
/**
 * The plugin bootstrap file
 *
 * 
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             1.0.0
 * @package           Joy_Of_Text
 *
 * @wordpress-plugin
 * Plugin Name:       Joy Of Text Lite - SMS messaging for Wordpress.
 * Plugin URI:        http://www.getcloudsms.com
 * Description:       Send SMS and text-to-voice messages to your customers, subscribers, followers, members and friends.
 * Version:           1.6
 * Author:            Stuart Wilson
 * Author URI:        http://www.getcloudsms.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jot-plugin
 * Domain Path:       /languages
 *
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	define( 'USING_SQLITE', 'false' );
		
	/**
	* Returns the main instance of Joy_Of_Text_Plugin to prevent the need to use globals.
	*/
	function Joy_Of_Text_Plugin() {
		return Joy_Of_Text_Plugin::instance();
	} // End Joy_Of_Text_Plugin()

	Joy_Of_Text_Plugin();


	/**
	* Main Joy_Of_Text_Plugin Class
	* @author SW
	*/
	final class Joy_Of_Text_Plugin {
		
		private static $_instance = null;
		public $token;
		public $debug;
		public $version;
		public $product;
		public $admin;
		public $settings;
		public $messenger;
		public $options;
		public $shortcodes;
		public $smsproviders;
		public $currentsmsprovider;     
		public $currentsmsprovidername;
		public $current_site;
		public $lastgrpid;
	
	
		public function __construct () {
			
			$this->product = "JOT Lite";						
			$this->token = 'jot-plugin';
			$this->version = '1.6';
			$this->debug = false;
			
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
						
			$installed_version = get_option($this->token . '-version');
			if ($installed_version < $this->version) {
			    $this->apply_updates();	
			}
			
			$this->plugin_url = plugin_dir_url( __FILE__ );
			$this->plugin_path = plugin_dir_path( __FILE__ );
						
			require_once( 'classes/class-jot-plugin-settings.php' );
			$this->settings = Joy_Of_Text_Plugin_Settings::instance();
						
			require_once( 'classes/class-jot-plugin-messenger.php' );
			$this->messenger = Joy_Of_Text_Plugin_Messenger::instance();
			
			require_once( 'classes/class-jot-plugin-admin.php' );
			$this->admin = Joy_Of_Text_Plugin_Admin::instance();
				
			require_once( 'classes/class-jot-plugin-options.php' );
			$this->options = Joy_Of_Text_Plugin_Options::instance();
				
			require_once( 'classes/class-jot-plugin-shortcodes.php' );
			$this->shortcodes = Joy_Of_Text_Plugin_Shortcodes::instance();
			
			
			add_action('init', array($this, 'load_plugin_textdomain'));
			register_activation_hook( __FILE__, array( $this, 'install' ) );
			add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
			add_action('wp_enqueue_scripts', array( $this, 'initialise_plugin' ));
			add_action('admin_enqueue_scripts', array( $this, 'initialise_plugin' ));
			add_filter('query_vars', array($this,'messageid_query_vars'));
			add_action('parse_request', array($this,'parse_voicecall_request'));
			add_action( 'plugins_loaded', array($this,'check_classes') );
						
			$this->smsproviders = $this->get_smsproviders();
			$this->currentsmsprovidername = 'twilio';
			//$this->currentsmsprovidername = $this->settings->get_current_smsprovider();
			
			if ($this->currentsmsprovidername != 'default' && !empty($this->currentsmsprovidername)) {
				require_once( 'classes/smsproviders/class-jot-provider-' . $this->currentsmsprovidername . '.php' );
				$this->currentsmsprovider = Joy_Of_Text_Plugin_Smsprovider::instance();
			} else {
				// Set the SMS provider to 'default'
				$smsprov =  get_option('jot-plugin-smsprovider');
				$smsprov['jot-smsproviders'] = 'default' ;   
				update_option('jot-plugin-smsprovider',$smsprov);
			}
		
			$this->lastgrpid = $this->jot_get_groupid();
			$this->_log_version_number();
			   
		} // End __construct()
		
		
		/**
		* Main Joy_Of_Text_Plugin Instance
		*
		* Ensures only one instance of Joy_Of_Text_Plugin is loaded or can be loaded.
		*
		*/
		public static function instance () {
			if ( is_null( self::$_instance ) )
			self::$_instance = new self();
			return self::$_instance;
		} // End instance()
		
		/**
		* Load the localisation file.
		*/
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'jot-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // End load_plugin_textdomain()
	
	    
		
	
		/**
		* Add settings link
		*/
		function plugin_action_links($links, $file) {
			
			static $this_plugin;
		     
			if (!$this_plugin) {
			    $this_plugin = plugin_basename(__FILE__);
			}
		     
			// check to make sure we are on the correct plugin
			if ($file == $this_plugin) {
			    // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page
			    $settings_link = "<a href='admin.php?page=jot-plugin&tab=smsprovider'>Settings</a>";
			    // add the link to the list
			    array_unshift($links, $settings_link);
			}
		     
			return $links;
		}
		
		/**
		* Cloning is forbidden.
		*/
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?','jot-plugin' ), '1.0.0' );
		} // End __clone()
		
		/**
		* Unserializing instances of this class is forbidden.
		*/
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'jot-plugin' ), '1.0.0' );
		} // End __wakeup()
		
		/**
		* Installation. Runs on activation.
		*/
		public function install () {
			global $wpdb;			
			
			// Create groups table
			$table = $wpdb->prefix."jot_groups";
			$structure = "CREATE TABLE IF NOT EXISTS $table (
			    jot_groupid        INT(9) NOT NULL AUTO_INCREMENT,
			    jot_groupname      VARCHAR(40) NOT NULL,
			    jot_groupdesc      VARCHAR(60) NOT NULL,
			    jot_ts             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			    UNIQUE KEY jot_groupid (jot_groupid)
			);";
			$wpdb->query($structure);
			
			// Create group members table
			$table = $wpdb->prefix."jot_groupmembers";
			$structure = "CREATE TABLE IF NOT EXISTS $table (
			    jot_grpmemid      INT(9) NOT NULL AUTO_INCREMENT,
			    jot_grpid         INT(9) NOT NULL,
			    jot_grpmemname    VARCHAR(40) NOT NULL,
			    jot_grpmemnum     VARCHAR(40) NOT NULL,
			    jot_grpmemts      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			    UNIQUE KEY jot_grpmemid (jot_grpmemid )
			);";
			$wpdb->query($structure);
			
			// Create group invite table
			$table = $wpdb->prefix."jot_groupinvites";
			$structure = "CREATE TABLE IF NOT EXISTS $table (
			    jot_grpid         INT(9) NOT NULL,
			    jot_grpinvdesc    VARCHAR(60) NOT NULL,
			    jot_grpinvnametxt VARCHAR(40) NOT NULL,
			    jot_grpinvnumtxt  VARCHAR(40) NOT NULL,
			    jot_grpinvretchk  BOOLEAN DEFAULT 1,
			    jot_grpinvrettxt  VARCHAR(160) NOT NULL,
			    jot_grpinvts      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			    UNIQUE KEY jot_grpinvid (jot_grpid)
			);";
			$wpdb->query($structure);
			
			// Messages table added in V1.05
			$table = $wpdb->prefix."jot_messages";
			$structure = "CREATE TABLE IF NOT EXISTS $table (
			    jot_messautoid     INT(9) NOT NULL AUTO_INCREMENT,
			    jot_messageid      VARCHAR(30) NOT NULL,
			    jot_messagecontent VARCHAR(640) NOT NULL,
			    UNIQUE KEY jot_messautoid (jot_messautoid )
			);";
			$wpdb->query($structure);
			
			// Message queue table 
			$table = $wpdb->prefix."jot_messagequeue";
			$structure = "CREATE TABLE $table (
			    jot_messqid         INT(9) NOT NULL AUTO_INCREMENT,
			    jot_messqbatchid    VARCHAR(30) NOT NULL,
			    jot_messqgrpid      INT(9) NOT NULL,
			    jot_messqmemid      INT(9) NOT NULL,
			    jot_messqcontent    VARCHAR(640) NOT NULL,
			    jot_messqtype       CHAR(1) NOT NULL,
			    jot_messqstatus     CHAR(1) NOT NULL,
			    jot_messqaudio      VARCHAR(20) NOT NULL,
			    jot_messsenderid    VARCHAR(11) NOT NULL,
			    jot_messqschedts    TIMESTAMP NOT NULL,
			    jot_messqts         TIMESTAMP NOT NULL,
			    UNIQUE KEY jot_messqid (jot_messqid)
			)   ENGINE=InnoDB
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			//$wpdb->query($structure);
			dbDelta($structure);			

			// Create group meta table
			$table = $wpdb->prefix."jot_groupmeta";
			$structure = "CREATE TABLE $table (
			    jot_groupmetaid    INT(9)       NOT NULL AUTO_INCREMENT,
			    jot_groupid        VARCHAR(40)  NOT NULL,
			    jot_groupmetakey   VARCHAR(255) NOT NULL,
			    jot_groupmetaval   LONGTEXT     NOT NULL,
			    UNIQUE KEY jot_groupmetaid (jot_groupmetaid)
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			
			
			// If jot_groups table is empty then insert the default group
			$lastgrpid = $this->jot_get_groupid(); 
			if ($lastgrpid == 0) {
				$data = array(
				'jot_groupname' => __("My customer group","jot-plugin"),
				'jot_groupdesc' => __("My customer group","jot-plugin")            
				);
				$table = $wpdb->prefix."jot_groups";
				$sqlerr=$wpdb->insert( $table, $data );
				      
			} 		
			
					
			
		} // End install()
		
		/*
		 *
		 * Check if certain classes exist
		 *
		 */
		public function check_classes(){
			
			
			
		}
		
		public function apply_updates() {
			
			global $wpdb;
			
			// Create group meta table
			$table = $wpdb->prefix."jot_groupmeta";
			$structure = "CREATE TABLE $table (
			    jot_groupmetaid    INT(9)       NOT NULL AUTO_INCREMENT,
			    jot_groupid        VARCHAR(40)  NOT NULL,
			    jot_groupmetakey   VARCHAR(255) NOT NULL,
			    jot_groupmetaval   LONGTEXT     NOT NULL,
			    UNIQUE KEY jot_groupmetaid (jot_groupmetaid)
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);			
			
		}
		
		/**
		* Log the plugin version number.
		*/
		private function _log_version_number () {
			// Log the version number.
			update_option( $this->token . '-version', $this->version );
		} // End _log_version_number()
		
						
		/**
		* Registers and enqueues admin-specific minified JavaScript.
		*/
	       public function initialise_plugin() {
				    
			//load_plugin_textdomain( 'jot-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
						
			if (!is_admin()) {
			    wp_enqueue_script('jquery');
			}
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_register_style('jot-css', plugins_url('css/jot.css',__FILE__ ));
			wp_enqueue_style('jot-css');			
			
			// Load Javascript
			$version_suffix = str_replace('.','-',$this->version);
			wp_register_script( 'jot-js', plugins_url( 'joy-of-text/js/jot-messenger-' . $version_suffix . '.js'),false,false,true );			
			
			wp_enqueue_script( 'jot-js' );
			
			// Enqueue CSS and script for Multiselect plugin  
			wp_register_style('jot-uitheme-css', plugins_url('css/jquery-ui-fresh.css',__FILE__ ));
			wp_enqueue_style('jot-uitheme-css');
			wp_register_style('jot-multiselect-css', plugins_url('css/jquery.multiselect.css',__FILE__ ) );
			wp_enqueue_style('jot-multiselect-css');
			wp_register_script( 'jot-multiselect-js', plugins_url( 'joy-of-text/js/jquery-ui-multiselect-widget-master/src/jquery.multiselect.js' ),false,false,true );
			wp_enqueue_script( 'jot-multiselect-js' );
			
			wp_localize_script( 'jot-js', 'ajax_object',
			       array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
			
			wp_localize_script( 'jot-js', 'wp_vars',
			       array( 'wp_admin_url' => admin_url() ) );
			
			wp_localize_script( 'jot-js', 'jot_plugin',
			       array( 'referrer' => strval(isset($_SERVER['HTTP_REFERER'])) )  );
			
			wp_localize_script ( 'jot-js', 'jot_images',
				array( 'saveimg' => plugins_url( 'joy-of-text/images/save.png', dirname(__FILE__) ),
				       'addimg'  => plugins_url( 'joy-of-text/images/add.png', dirname(__FILE__) ),
				       'delimg'  => plugins_url( 'joy-of-text/images/delete.png', dirname(__FILE__) ),
				       'spinner' => plugins_url( 'joy-of-text/images/ajax-loader.gif', dirname(__FILE__) )
				      ) );
			
			wp_localize_script( 'jot-js', 'jot_woo',
			       array( 'logfile' => plugins_url("/joy-of-text/log/jotwoosync.log") ) );
			
			$strings = $this->get_frontend_strings();
			wp_localize_script ( 'jot-js', 'jot_strings',$strings);
			
			
			if ( isset($_GET['lastid'])) {
				$id = $_GET['lastid'];
			} else {
				$id = 1;
			}
			
			wp_localize_script( 'jot-js', 'jot_lastgroup', array( 'id' => $id ));
	    
	       } // end register_admin_scripts

	       function get_frontend_strings() {
		
		$strings =  array(     'saveinv' => __("Saving invite details....","jot-plugin"),
				       'savegrp' => __("Saving group details","jot-plugin"),
				       'grpsub' => __("Subscribing you to the group....","jot-plugin"),
				       'sendmsg' => __("Sending messages....","jot-plugin"),
				       'sentmsg' => __("Message Sent at","jot-plugin"),
				       'addgrp' => __("Adding group....","jot-plugin"),
				       'selectrecip' => __("Select message recipients","jot-plugin"),
				       'number' => __("Number","jot-plugin"),
				       'status' => __("Status Message","jot-plugin"),
				       'proccomplete' => __("Processing complete.","jot-plugin"),
				       'scheduled' => __("Messages have been scheduled","jot-plugin"),
				       'queuemsg' => __("Queuing messages....","jot-plugin")
		);
		 
		return apply_filters('jot_get_frontend_strings',$strings);
	       }
	       
	       function messageid_query_vars($vars) {
		   $vars[] = 'messageid';
		   return $vars;
		}
	 
	       function parse_voicecall_request($wp) {
			// only process requests with "messageid"
			if (array_key_exists('messageid', $wp->query_vars)) {
		    
			    // process the request.
			    $this->currentsmsprovider->get_callmessage();
			}
		}
	    		
	       /**
		* Reads SMS provider details from an ini file
		*/
	       public function get_smsproviders() {
	           
		   return parse_ini_file( 'jot.ini',true);
		   
	       }
	       
	       function jot_get_groupid() {
		     global $wpdb;
		     
		     
		     $table = $wpdb->prefix."jot_groups";
		     if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
			$result = $wpdb->get_row("select max(jot_groupid) as jot_groupid from ". $table );
			if(count($result) == 0) {
			   return 0;
			} else {
			   return $result->jot_groupid;
			}
		     } else {
			return 0;
		     }
	       }
	       
	       
		
	} // End Class

?>