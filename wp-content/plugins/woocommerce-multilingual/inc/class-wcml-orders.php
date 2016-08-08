<?php
class WCML_Orders{
    
    private $standart_order_notes = array('Order status changed from %s to %s.',
        'Order item stock reduced successfully.','Item #%s stock reduced from %s to %s.','Item #%s stock increased from %s to %s.','Awaiting BACS payment','Awaiting cheque payment','Payment to be made upon delivery.',
        'Validation error: PayPal amounts do not match (gross %s).','Validation error: PayPal IPN response from a different email address (%s).','Payment pending: %s',
        'Payment %s via IPN.','Validation error: PayPal amounts do not match (amt %s).','IPN payment completed','PDT payment completed'
    );
    
    function __construct(){
        
        add_action('init', array($this, 'init'));
        
        //checkout page
        add_action( 'wp_ajax_woocommerce_checkout',array($this,'switch_to_current'),9);
        add_action( 'wp_ajax_nopriv_woocommerce_checkout',array($this,'switch_to_current'),9);

        add_action( 'wp_ajax_wcml_order_delete_items', array( $this, 'order_delete_items' ) );
    }
    
    function init(){
        
        add_action('woocommerce_shipping_update_ajax', array($this, 'fix_shipping_update'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'set_order_language'));
        
        add_filter('icl_lang_sel_copy_parameters', array($this, 'append_query_parameters'));

        add_filter('the_comments', array($this, 'get_filtered_comments'));
        add_filter('gettext',array($this, 'filtered_woocommerce_new_order_note_data'),10,3);

        add_filter('woocommerce_order_get_items',array($this,'woocommerce_order_get_items'),10);

        add_action( 'woocommerce_process_shop_order_meta', array( $this, 'set_order_language_backend'), 10, 2 );
        add_action( 'woocommerce_order_actions_start', array( $this, 'order_language_dropdown' ), 11 ); //after order currency drop-down

        //special case for wcml-741
        add_action('updated_post_meta', array($this,'update_order_currency'), 100,4);

    }

    function filtered_woocommerce_new_order_note_data($translations, $text, $domain ){
        if(in_array($text,$this->standart_order_notes)){
            global $sitepress_settings, $wpdb, $woocommerce_wpml;

            $language = $woocommerce_wpml->strings->get_string_language( $text, 'woocommerce' );

            if( $sitepress_settings['admin_default_language'] != $language ){

                $string_id = icl_get_string_id( $text, 'woocommerce');
                $strings = icl_get_string_translations_by_id( $string_id );
                if($strings){
                    $translations = $strings[ $sitepress_settings['admin_default_language'] ]['value'];
                }

            }else{
                return $text;
            }
        }

        return $translations;
    }

    function get_filtered_comments($comments){

        $user_id = get_current_user_id();

        if( $user_id ){

            global $wpdb, $woocommerce_wpml;
            
            $user_language    = get_user_meta( $user_id, 'icl_admin_language', true );

            foreach($comments as $key=>$comment){

                $comment_string_id = icl_get_string_id( $comment->comment_content, 'woocommerce');

                if($comment_string_id){
                    $comment_strings = icl_get_string_translations_by_id( $comment_string_id );
                    if($comment_strings){
                        $comments[$key]->comment_content = $comment_strings[$user_language]['value'];
                    }
                }
            }        
            
        }

        return $comments;

    }
    
    function woocommerce_order_get_items($items){
        if(isset($_GET['post']) && get_post_type($_GET['post']) == 'shop_order'){
            global $sitepress_settings;
            foreach($items as $index=>$item){
                foreach($item as $key=>$item_data){
                    if($key == 'product_id'){
                        $tr_product_id = apply_filters( 'translate_object_id',$item_data,'product',false,$sitepress_settings['admin_default_language']);
                        if(!is_null($tr_product_id)){
                            $items[$index][$key] = $tr_product_id;
                            $items[$index]['name'] = get_the_title($tr_product_id);
                        }
                    }
                    if($key == 'variation_id'){
                        $tr_variation_id = apply_filters( 'translate_object_id',$item_data,'product_variation',false,$sitepress_settings['admin_default_language']);
                        if(!is_null($tr_variation_id)){
                            $items[$index][$key] = $tr_variation_id;
                        }
                    }

                    if (substr($key, 0, 3) == 'pa_') {
                        global $wpdb, $woocommerce_wpml;
                        //attr is taxonomy

                        $term_id = $woocommerce_wpml->terms->wcml_get_term_id_by_slug( $key, $item_data );
                        $tr_id = apply_filters( 'translate_object_id', $term_id, $key, false, $sitepress_settings['admin_default_language']);

                        if(!is_null($tr_id)){
                            $translated_slug = $wpdb->get_var($wpdb->prepare("
                                    SELECT t.slug FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} x ON x.term_id = t.term_id WHERE t.term_id = %d AND x.taxonomy = %s", $tr_id, $key));
                            $items[$index][$key] = $translated_slug;
                        }
                    }
                }
            }
        }
        return $items;
    }
    
    // Fix for shipping update on the checkout page.
    function fix_shipping_update($amount){
        global $sitepress, $post;
        
        if($sitepress->get_current_language() !== $sitepress->get_default_language() && $post->ID == $this->checkout_page_id()){
        
            $_SESSION['icl_checkout_shipping_amount'] = $amount;
            
            $amount = $_SESSION['icl_checkout_shipping_amount'];
        
        }
    
        return $amount;
    }


    /**
     * Adds language to order post type.
     * 
     * Language was stored in the session created on checkout page.
     * See params().
     * 
     * @param type $order_id
     */ 
    function set_order_language($order_id) { 
        if(!get_post_meta($order_id, 'wpml_language')){
            $language = isset($_SESSION['wpml_globalcart_language']) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
            update_post_meta($order_id, 'wpml_language', $language);
        }
    }
    
    function append_query_parameters($parameters){
        
        if(is_order_received_page() || is_checkout()){
            if(!in_array('order', $parameters)) $parameters[] = 'order';
            if(!in_array('key', $parameters)) $parameters[] = 'key';
        }

        return $parameters;
    }

    function switch_to_current(){
        global $sitepress,$woocommerce_wpml;
        $woocommerce_wpml->emails->change_email_language($sitepress->get_current_language());
    }

    function order_language_dropdown( $order_id ){
        global $woocommerce_wpml, $sitepress;
        if( !get_post_meta( $order_id, '_order_currency') ) {
            $languages = icl_get_languages('skip_missing=0&orderby=code');
            $selected_lang =  isset( $_COOKIE [ '_wcml_dashboard_order_language' ] ) ?  $_COOKIE [ '_wcml_dashboard_order_language' ] : $sitepress->get_default_language();
            ?>
            <li class="wide">
                <label><?php _e('Order language:'); ?></label>
                <select id="dropdown_shop_order_language" name="wcml_shop_order_language">
                    <?php if (!empty($languages)): ?>

                        <?php foreach ($languages as $l): ?>

                            <option
                                value="<?php echo $l['language_code'] ?>" <?php echo $selected_lang == $l['language_code'] ? 'selected="selected"' : ''; ?>><?php echo $l['translated_name']; ?></option>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </select>
            </li>
            <?php
            $wcml_set_dashboard_order_language_nonce = wp_create_nonce( 'set_dashboard_order_language' );
            wc_enqueue_js( "
                 var order_lang_current_value = jQuery('#dropdown_shop_order_language option:selected').val();

                 jQuery('#dropdown_shop_order_language').on('change', function(){
                    if(confirm('" . esc_js(__("All the products will be removed from the current order in order to change the language", 'woocommerce-multilingual')). "')){
                        var lang = jQuery(this).val();

                        jQuery.ajax({
                            url: ajaxurl,
                            type: 'post',
                            dataType: 'json',
                            data: {action: 'wcml_order_delete_items', order_id: woocommerce_admin_meta_boxes.post_id, lang: lang , wcml_nonce: '".$wcml_set_dashboard_order_language_nonce."' },
                            success: function( response ){
                                if(typeof response.error !== 'undefined'){
                                    alert(response.error);
                                }else{
                                    window.location = window.location.href;
                                }
                            }
                        });
                    }else{
                        jQuery(this).val( order_lang_current_value );
                        return false;
                    }
                });

            ");
        }
    }

    function order_delete_items(){
        $nonce = filter_input( INPUT_POST, 'wcml_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if(!$nonce || !wp_verify_nonce($nonce, 'set_dashboard_order_language')){
            echo json_encode(array('error' => __('Invalid nonce', 'woocommerce-multilingual')));
            die();
        }

        setcookie('_wcml_dashboard_order_language', filter_input( INPUT_POST, 'lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS ), time() + 86400, COOKIEPATH, COOKIE_DOMAIN);

    }

    function set_order_language_backend( $post_id, $post ){

        if( isset( $_POST['wcml_shop_order_language'] ) ){
            update_post_meta( $post_id, 'wpml_language', filter_input( INPUT_POST, 'wcml_shop_order_language', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
        }

    }

    function update_order_currency( $meta_id, $object_id, $meta_key, $meta_value ){
        global $woocommerce_wpml;

        if( $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT && get_post_type($object_id) == 'shop_order' && isset( $_GET['wc-ajax'] ) && $_GET['wc-ajax'] == 'checkout' ){
            update_post_meta( $object_id, '_order_currency', get_woocommerce_currency() );
        }

    }


}
