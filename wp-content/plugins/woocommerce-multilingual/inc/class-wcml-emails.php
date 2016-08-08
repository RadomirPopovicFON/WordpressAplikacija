<?php
class WCML_Emails{

    private $order_id = false;

    private $locale = false;

    function __construct(  ) {
        add_action( 'init', array( $this, 'init' ) );
    }

    function init(){
        global $pagenow;
        //wrappers for email's header
        if(is_admin() && !defined( 'DOING_AJAX' )){
            add_action('woocommerce_order_status_completed_notification', array($this, 'email_heading_completed'),9);
            add_action('woocommerce_order_status_changed', array($this, 'comments_language'),10);
        }

        add_action('woocommerce_new_customer_note_notification', array($this, 'email_heading_note'),9);
        add_action('wp_ajax_woocommerce_mark_order_complete',array($this,'email_refresh_in_ajax'),9);

        add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'email_heading_processing' ) );
        add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'email_heading_processing' ) );

        //wrappers for email's body
        add_action('woocommerce_before_resend_order_emails', array($this, 'email_header'));
        add_action('woocommerce_after_resend_order_email', array($this, 'email_footer'));

        //filter string language before for emails
        add_filter('icl_current_string_language',array($this,'icl_current_string_language'),10 ,2);

        //change order status
        add_action('woocommerce_order_status_completed',array($this,'refresh_email_lang_complete'),9);
        add_action('woocommerce_order_status_pending_to_processing_notification',array($this,'refresh_email_lang'),9);
        add_action('woocommerce_order_status_pending_to_on-hold_notification',array($this,'refresh_email_lang'),9);
        add_action('woocommerce_new_customer_note',array($this,'refresh_email_lang'),9);


        add_action('woocommerce_order_partially_refunded_notification', array($this,'email_heading_refund'), 9);
        add_action('woocommerce_order_partially_refunded_notification', array($this,'refresh_email_lang'), 9);


        //admin emails
        add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'admin_email' ), 9 );
        add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'admin_email' ), 9 );
        add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'admin_email' ), 9 );
        add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'admin_email' ), 9 );
        add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'admin_email' ), 9 );
        add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $this, 'admin_email' ), 9 );

        add_filter( 'icl_st_admin_string_return_cached', array( $this, 'admin_string_return_cached' ), 10, 2 );

        add_filter( 'plugin_locale', array( $this, 'set_locale_for_emails' ), 10, 2 );

        if( is_admin() && $pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'wc-settings' && isset($_GET['tab']) && $_GET['tab'] == 'email' ){
            add_action('admin_footer', array($this, 'show_language_links_for_wc_emails'));
            $this->set_emails_string_lamguage();
        }
    }

    function email_refresh_in_ajax(){
        if(isset($_GET['order_id'])){
            $this->refresh_email_lang($_GET['order_id']);
            $this->email_heading_completed($_GET['order_id'],true);
        }
    }

    function refresh_email_lang_complete( $order_id ){

        $this->order_id = $order_id;
        $this->refresh_email_lang($order_id);
        $this->email_heading_completed($order_id,true);

    }

    /**
     * Translate WooCommerce emails.
     *
     * @global type $sitepress
     * @global type $order_id
     * @return type
     */
    function email_header($order) {

        
        if (is_array($order)) {
            $order = $order['order_id'];
        } elseif (is_object($order)) {
            $order = $order->id;
        }

        $this->refresh_email_lang($order);

    }


    function refresh_email_lang($order_id){

        if ( is_array( $order_id ) ) {
            if ( isset($order_id['order_id']) ) {
                $order_id = $order_id['order_id'];
            } else {
                return;
            }

        }

        $lang = get_post_meta($order_id, 'wpml_language', TRUE);
        if(!empty($lang)){
            $this->change_email_language($lang);
        }
    }

    /**
     * After email translation switch language to default.
     */
    function email_footer() {
        global $sitepress;
        $sitepress->switch_lang($sitepress->get_default_language());
    }    

    function comments_language(){
        global $woocommerce_wpml;

        $this->change_email_language( $woocommerce_wpml->strings->get_domain_language( 'woocommerce' ) );

    }

    function email_heading_completed( $order_id, $no_checking = false ){
        global $woocommerce;
        if(class_exists('WC_Email_Customer_Completed_Order') || $no_checking){

            $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->heading = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_completed_order_settings', '[woocommerce_customer_completed_order_settings]heading' );

            $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->subject = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_completed_order_settings', '[woocommerce_customer_completed_order_settings]subject' );

            $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->heading_downloadable = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_completed_order_settings', '[woocommerce_customer_completed_order_settings]heading_downloadable' );

            $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->subject_downloadable = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_completed_order_settings', '[woocommerce_customer_completed_order_settings]subject_downloadable' );

            $enabled = $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->enabled;
            $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->enabled = false;
            $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->trigger($order_id);
            $woocommerce->mailer()->emails['WC_Email_Customer_Completed_Order']->enabled = $enabled;
        }
    }

    function email_heading_processing($order_id){
        global $woocommerce;
        if(class_exists('WC_Email_Customer_Processing_Order')){

            $woocommerce->mailer()->emails['WC_Email_Customer_Processing_Order']->heading = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_processing_order_settings', '[woocommerce_customer_processing_order_settings]heading' );

            $woocommerce->mailer()->emails['WC_Email_Customer_Processing_Order']->subject = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_processing_order_settings', '[woocommerce_customer_processing_order_settings]subject' );

            $enabled = $woocommerce->mailer()->emails['WC_Email_Customer_Processing_Order']->enabled;
            $woocommerce->mailer()->emails['WC_Email_Customer_Processing_Order']->enabled = false;
            $woocommerce->mailer()->emails['WC_Email_Customer_Processing_Order']->trigger($order_id);
            $woocommerce->mailer()->emails['WC_Email_Customer_Processing_Order']->enabled = $enabled;
        }
    }

    function email_heading_note($args){
        global $woocommerce;

        if(class_exists('WC_Email_Customer_Note')){

            $woocommerce->mailer()->emails['WC_Email_Customer_Note']->heading = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_note_settings', '[woocommerce_customer_note_settings]heading' );

            $woocommerce->mailer()->emails['WC_Email_Customer_Note']->subject = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_note_settings', '[woocommerce_customer_note_settings]subject' );

            $enabled = $woocommerce->mailer()->emails['WC_Email_Customer_Note']->enabled;
            $woocommerce->mailer()->emails['WC_Email_Customer_Note']->enabled = false;
            $woocommerce->mailer()->emails['WC_Email_Customer_Note']->trigger($args);
            $woocommerce->mailer()->emails['WC_Email_Customer_Note']->enabled = $enabled;
        }
    }

    function email_heading_refund( $order_id, $refund_id = null ){
        global $woocommerce;
        if(class_exists('WC_Email_Customer_Refunded_Order')){

            $woocommerce->mailer()->emails['WC_Email_Customer_Refunded_Order']->heading =
                $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_refunded_order_settings',
                    '[woocommerce_customer_refunded_order_settings]heading_partial' );
            $woocommerce->mailer()->emails['WC_Email_Customer_Refunded_Order']->subject =
                $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_customer_refunded_order_settings',
                    '[woocommerce_customer_refunded_order_settings]subject_partial' );

            $enabled = $woocommerce->mailer()->emails['WC_Email_Customer_Refunded_Order']->enabled;
            $woocommerce->mailer()->emails['WC_Email_Customer_Refunded_Order']->enabled = false;
            $woocommerce->mailer()->emails['WC_Email_Customer_Refunded_Order']->trigger($order_id, true, $refund_id);
            $woocommerce->mailer()->emails['WC_Email_Customer_Refunded_Order']->enabled = $enabled;

        }
    }


    function admin_email($order_id){
        global $woocommerce,$sitepress;
        if(class_exists('WC_Email_New_Order')){
            $recipients = explode(',',$woocommerce->mailer()->emails['WC_Email_New_Order']->get_recipient());
            foreach($recipients as $recipient){
                $user = get_user_by('email',$recipient);
                if($user){
                    $user_lang = $sitepress->get_user_admin_language($user->ID);
                }else{
                    $user_lang = get_post_meta($order_id, 'wpml_language', TRUE);
                }
                icl_get_string_translations_by_id(1);
                $this->change_email_language($user_lang);

                $woocommerce->mailer()->emails['WC_Email_New_Order']->heading = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_new_order_settings', '[woocommerce_new_order_settings]heading' );

                $woocommerce->mailer()->emails['WC_Email_New_Order']->subject = $this->wcml_get_translated_email_string( 'admin_texts_woocommerce_new_order_settings', '[woocommerce_new_order_settings]subject' );

                $woocommerce->mailer()->emails['WC_Email_New_Order']->recipient = $recipient;

                $woocommerce->mailer()->emails['WC_Email_New_Order']->trigger($order_id);
            }
            $woocommerce->mailer()->emails['WC_Email_New_Order']->enabled = false;
            $this->refresh_email_lang($order_id);
        }
    }

    function change_email_language($lang){
        global $sitepress,$woocommerce;
        $sitepress->switch_lang($lang,true);
        $this->locale = $sitepress->get_locale( $lang );
        unload_textdomain('woocommerce');
        unload_textdomain('default');
        $woocommerce->load_plugin_textdomain();
        load_default_textdomain();
        global $wp_locale;
        $wp_locale = new WP_Locale();
    }    
    
    function admin_string_return_cached( $value, $option ){
        if( in_array( $option, array ( 'woocommerce_email_from_address', 'woocommerce_email_from_name' ) ) )
            return false;

        return $value;
    }

    function wcml_get_translated_email_string( $context, $name ){

        if( version_compare(WPML_ST_VERSION, '2.2.6', '<=' ) ){
            global $wpdb;

            $result = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", $context, $name ) );

            return apply_filters( 'wpml_translate_single_string', $result, $context, $name );
        }else{

            return apply_filters( 'wpml_translate_single_string', false, $context, $name );

        }

    }

    function icl_current_string_language(  $current_language, $name ){
        $order_id = false;

        if( isset($_POST['action']) && $_POST['action'] == 'editpost' && isset($_POST['post_type']) && $_POST['post_type'] == 'shop_order' ){
            $order_id = filter_input( INPUT_POST, 'post_ID', FILTER_SANITIZE_NUMBER_INT );
        }elseif( isset($_POST['action']) && $_POST['action'] == 'woocommerce_add_order_note' && isset($_POST['note_type']) && $_POST['note_type'] == 'customer' ) {
            $order_id = filter_input( INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT );
        }elseif( isset($_GET['action']) && isset($_GET['order_id']) && ( $_GET['action'] == 'woocommerce_mark_order_complete' || $_GET['action'] == 'woocommerce_mark_order_status') ){
            $order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        }elseif(isset($_GET['action']) && $_GET['action'] == 'mark_completed' && $this->order_id){
            $order_id = $this->order_id;
        }elseif(isset($_POST['action']) && $_POST['action'] == 'woocommerce_refund_line_items'){
            $order_id = filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
        }

        if( $order_id ){
            $order_language = get_post_meta( $order_id, 'wpml_language', true );
            if( $order_language ){
                $current_language = $order_language;
            }else{
                global $sitepress;
                $current_language = $sitepress->get_current_language();
            }
        }

        return apply_filters( 'wcml_email_language', $current_language, $order_id );
    }

    // set correct locale code for emails
    function set_locale_for_emails(  $locale, $domain ){

        if( $domain == 'woocommerce' && $this->locale ){
            $locale = $this->locale;
        }

        return $locale;
    }

    function show_language_links_for_wc_emails(){
        global $sitepress, $woocommerce_wpml;

        $emails_options = array(
            'woocommerce_new_order_settings',
            'woocommerce_cancelled_order_settings',
            'woocommerce_failed_order_settings',
            'woocommerce_customer_on_hold_order_settings',
            'woocommerce_customer_processing_order_settings',
            'woocommerce_customer_completed_order_settings',
            'woocommerce_customer_refunded_order_settings',
            'woocommerce_customer_invoice_settings',
            'woocommerce_customer_note_settings',
            'woocommerce_customer_reset_password_settings',
            'woocommerce_customer_new_account_settings'
        );

        $text_keys = array(
            'subject',
            'heading',
            'subject_downloadable',
            'heading_downloadable',
            'subject_full',
            'subject_partial',
            'heading_full',
            'heading_partial',
            'subject_paid',
            'heading_paid'
        );


        foreach( $emails_options as $emails_option ) {

            $section_name = str_replace( 'woocommerce_', 'wc_email_', $emails_option );
            $section_name = str_replace( '_settings', '', $section_name );
            if( isset( $_GET['section'] ) && $_GET['section'] == $section_name ){

                $option_settings = get_option($emails_option);
                foreach ($option_settings as $setting_key => $setting_value) {
                    if ( in_array( $setting_key, $text_keys ) ) {
                        $input_name = str_replace( '_settings', '', $emails_option ).'_'.$setting_key;

                        $lang_selector = new WPML_Simple_Language_Selector($sitepress);
                        $language = $woocommerce_wpml->strings->get_string_language( $setting_value, 'admin_texts_'.$emails_option, '['.$emails_option.']'.$setting_key );
                        if( is_null( $language ) ) {
                            $language = $sitepress->get_default_language();
                        }

                        $lang_selector->render( array(
                                                    'id' => $emails_option.'_'.$setting_key.'_language_selector',
                                                    'name' => 'wcml_lang-'.$emails_option.'-'.$setting_key,
                                                    'selected' => $language,
                                                    'show_please_select' => false,
                                                    'echo' => true,
                                                    'style' => 'width: 18%;float: left'
                                                )
                                            );

                        $st_page = admin_url( 'admin.php?page=' . WPML_ST_FOLDER . '/menu/string-translation.php&context=admin_texts_'.$emails_option.'&search='.$setting_value );
                        ?>
                        <script>
                            var input = jQuery('input[name="<?php echo $input_name  ?>"]');
                            if (input.length) {
                                input.parent().append('<div class="translation_controls"></div>');
                                input.parent().find('.translation_controls').append('<a href="<?php echo $st_page ?>" style="margin-left: 10px"><?php _e('translations', 'woocommerce-multilingual') ?></a>');
                                jQuery('#<?php echo $emails_option.'_'.$setting_key.'_language_selector' ?>').appendTo(input.parent().find('.translation_controls'));
                            }
                        </script>
                    <?php }
                }
            }
        }
    }

    function set_emails_string_lamguage(){
        global $woocommerce_wpml;

        foreach( $_POST as $key => $post_value ){
            if( substr( $key, 0, 9 ) == 'wcml_lang' ){

                $email_string = explode( '-', $key );
                $email_settings = get_option( $email_string[1], true );

                if( isset( $email_string[2] ) ){
                    $woocommerce_wpml->strings->set_string_language( $email_settings[ $email_string[2] ], 'admin_texts_'.$email_string[1] ,  '['.$email_string[1].']'.$email_string[2], $post_value );
                }
            }
        }
    }

}