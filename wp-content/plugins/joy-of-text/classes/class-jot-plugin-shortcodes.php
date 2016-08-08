<?php
/**
* Joy_Of_Text shortcodes. Processes shortcode requests
*
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Shortcodes {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    function __construct() {
         add_shortcode('jotform',array($this, 'process_jotform_shortcode'));
         add_action( 'wp_ajax_group_subscribe', array( &$this, 'group_subscribe' ) );
         add_action( 'wp_ajax_nopriv_group_subscribe', array( &$this, 'group_subscribe' ) );
    } // end constructor
 
    private static $_instance = null;
        
        public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
        } // End instance()

      public function process_jotform_shortcode ($atts) {
                
                $error = 0;
                
                extract( shortcode_atts( array(
                                'id' => '',
                                'name' => 'yes'
                                ), $atts ));
                
                         
                $group_id =(int) $id;
                if ( !$group_id ) {
                    // Id is not an integer          
                    $error = 1;
                }
                
                //Get group invite details from database.
                global $wpdb;
                $table = $wpdb->prefix."jot_groupinvites";
                $sql = " SELECT jot_grpid, jot_grpinvdesc, jot_grpinvnametxt, jot_grpinvnumtxt, jot_grpinvretchk, jot_grpinvrettxt" .
                   " FROM " . $table .
                   " WHERE jot_grpid = " . $id;
                
                
                $groupinvite = $wpdb->get_row( $sql );
                if (!$groupinvite) {
                    //group is not found
                    $error=2;            
                }
                
                switch ( $error ) {
                         case 0; 
                              $subhtml = '<div>';
                              $subhtml .= '<form id="jot-subscriber-form-' . $id . '" action="" method="post">';
                              $subhtml .= '<input type="hidden"  name="jot-group-id" value="' . $id . '">';
                              $subhtml .= '<input type="hidden"  name="jot_form_id" value="jot-subscriber-form">';
                              $subhtml .= '<table>';
                              $subhtml .= '<tr><th colspan=2 class="jot-td-c">' . $groupinvite->jot_grpinvdesc . '</th></tr>';
                                
                              if (strtolower($name) == 'no') {
                                $subhtml .= '<tr><th></th><td><input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="hidden" value="No name given"/></td></tr>';  
                              } else {
                                $subhtml .= '<tr><th>' . $groupinvite->jot_grpinvnametxt . '</th><td><input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="text"/></td></tr>';
                              }  
                              
                              $subhtml .= '<tr><th>' . $groupinvite->jot_grpinvnumtxt . '</th><td><input id="jot-subscribe-num" name="jot-subscribe-num" maxlength="40" size="40" type="text"/></td></tr>';
                              $subhtml .= '<tr><td><input type="button" id="jot-subscribegroup-' . $id . '" class="button" value="Subscribe"/></td>';
                              $subhtml .= '<td><div id="jot-subscribemessage"></div></td></tr>';
                              $subhtml .= '</table>';
                              $subhtml .= '</form>';
                              $subhtml .= '</div>';  
                         break;
                         case 1;
                              //Group ID is not an integer
                              $subhtml = "<div>";
                              $subhtml .= '<p>jotform shortcode error. ID field in shortcode is not valid.<p>';
                              $subhtml .= '</div>';
                         break;
                         case 2:
                              // ID not found
                              $subhtml = "<div>";
                              $subhtml .= '<p>jotform shortcode error. Group ID field in shortcode "' . $id . '" is not found.<p>';
                              $subhtml .= '</div>';
                         break;
                         default:
                         # code...
                         break;
                }         
                                     
                
               return $subhtml;
                
                }
        
    
    
} // end class
 