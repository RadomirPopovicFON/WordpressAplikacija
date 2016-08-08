<?php

class WCML_Admin_Menus{

    private static $woocommerce_wpml;
    private static $sitepress;

    public static function set_up_menus( &$woocommerce_wpml, &$sitepress, $check_dependencies ){
        self::$woocommerce_wpml =& $woocommerce_wpml;
        self::$sitepress =& $sitepress;
        
        add_action('admin_menu', array(__CLASS__, 'register_menus'));

        if( self::is_page_without_admin_language_switcher() ){
            self::remove_wpml_admin_language_switcher();
        }

        if( is_admin() && !is_null( $sitepress ) && $check_dependencies ){
            add_action('admin_footer', array(__CLASS__, 'documentation_links'));
            add_action( 'admin_head', array( __CLASS__, 'hide_multilingual_content_setup_box' ) );
            add_action( 'admin_init', array( __CLASS__, 'restrict_admin_with_redirect' ) );
        }

    }

    public static function register_menus(){
        if( self::$woocommerce_wpml->check_dependencies && self::$woocommerce_wpml->check_design_update){
            $top_page = apply_filters('icl_menu_main_page', basename(ICL_PLUGIN_PATH) .'/menu/languages.php');

            if(current_user_can('wpml_manage_woocommerce_multilingual')){
                add_submenu_page($top_page, __('WooCommerce Multilingual', 'woocommerce-multilingual'),
                    __('WooCommerce Multilingual', 'woocommerce-multilingual'), 'wpml_manage_woocommerce_multilingual', 'wpml-wcml', array(__CLASS__, 'render_menus'));

            }else{
                global $wpdb, $sitepress;
                $user_lang_pairs = get_user_meta(get_current_user_id(), $wpdb->prefix.'language_pairs', true);
                if( !empty( $user_lang_pairs ) ){
                    add_menu_page(__('WooCommerce Multilingual', 'woocommerce-multilingual'),
                        __('WooCommerce Multilingual', 'woocommerce-multilingual'), 'translate',
                        'wpml-wcml', array(__CLASS__, 'render_menus'), ICL_PLUGIN_URL . '/res/img/icon16.png');
                }
            }

        }elseif( current_user_can('wpml_manage_woocommerce_multilingual') ){
            if(!defined('ICL_SITEPRESS_VERSION')){
                add_menu_page( __( 'WooCommerce Multilingual', 'woocommerce-multilingual' ), __( 'WooCommerce Multilingual', 'woocommerce-multilingual' ),
                    'wpml_manage_woocommerce_multilingual', 'wpml-wcml', array(__CLASS__, 'render_menus'), WCML_PLUGIN_URL . '/res/images/icon16.png' );
            }else{
                $top_page = apply_filters('icl_menu_main_page', basename(ICL_PLUGIN_PATH) .'/menu/languages.php');
                add_submenu_page($top_page, __('WooCommerce Multilingual', 'woocommerce-multilingual'),
                    __('WooCommerce Multilingual', 'woocommerce-multilingual'), 'wpml_manage_woocommerce_multilingual', 'wpml-wcml', array(__CLASS__, 'render_menus'));
            }

        }
    }

    public static function render_menus(){

        if( self::$woocommerce_wpml->check_dependencies && self::$woocommerce_wpml->check_design_update ){
            $menus_wrap = new WCML_Menus_Wrap( self::$woocommerce_wpml );
            $menus_wrap->show();
        }else{
            global $sitepress;
            $plugins_wrap = new WCML_Plugins_Wrap( self::$woocommerce_wpml, $sitepress );
            $plugins_wrap->show();
        }

    }

    private static function is_page_without_admin_language_switcher(){
        global $pagenow;

        $get_post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : false;
        $get_post      = isset( $_GET['post'] ) ? $_GET['post'] : false;
        $get_page      = isset( $_GET['page'] ) ? $_GET['page'] : false;

        $is_page_wpml_wcml          = isset($_GET['page']) && $_GET['page'] == 'wpml-wcml';
        $is_new_order_or_coupon     = in_array( $pagenow, array( 'edit.php', 'post-new.php' ) ) &&
                                        $get_post_type &&
                                        in_array( $get_post_type, array( 'shop_coupon', 'shop_order' ) );
        $is_edit_order_or_coupon    = $pagenow == 'post.php' && $get_post &&
                                        in_array( get_post_type( $get_post ), array( 'shop_coupon', 'shop_order' ) );
        $is_shipping_zones          = $get_page == 'shipping_zones';
        $is_attributes_page          =  apply_filters( 'wcml_is_attributes_page', $get_page == 'product_attributes' );


        return is_admin() && (
                $is_page_wpml_wcml ||
                $is_new_order_or_coupon ||
                $is_edit_order_or_coupon ||
                $is_shipping_zones ||
                $is_attributes_page
              );

    }

    public static function remove_wpml_admin_language_switcher(){

        remove_action( 'wp_before_admin_bar_render', array(self::$sitepress, 'admin_language_switcher') );

    }

    public static function documentation_links() {
        global $post, $pagenow;

        if ( is_null( $post ) )
            return;

        $get_post_type = get_post_type( $post->ID );

        if ( $get_post_type == 'product' && $pagenow == 'edit.php' ) {
            $prot_link = '<span class="button"><img align="baseline" src="' . ICL_PLUGIN_URL . '/res/img/icon.png" width="16" height="16" style="margin-bottom:-4px" /> <a href="' . WCML_Links::generate_tracking_link( 'https://wpml.org/documentation/related-projects/woocommerce-multilingual/', 'woocommerce-multilingual', 'documentation', '#4' ) . '" target="_blank">' .
                __( 'How to translate products', 'sitepress' ) . '<\/a>' . '<\/span>';
            $quick_edit_notice = '<div id="quick_edit_notice" style="display:none;"><p>' .
                sprintf( __( "Quick edit is disabled for product translations. It\'s recommended to use the %s for editing products translations. %s",
                    'woocommerce-multilingual' ), '<a href="' . admin_url( 'admin.php?page=wpml-wcml&tab=products' ) . '" >' .
                    __( 'WooCommerce Multilingual products editor', 'woocommerce-multilingual' ) . '</a>',
                    '<a href="" class="quick_product_trnsl_link" >' . __( 'Edit this product translation', 'woocommerce-multilingual' ) . '</a>'
                ) . '</p></div>';
            $quick_edit_notice_prod_link = '<input type="hidden" id="wcml_product_trnsl_link" value="' . admin_url( 'admin.php?page=wpml-wcml&tab=products&prid=' ) . '">';
            ?>
            <script type="text/javascript">
                jQuery(".subsubsub").append('<?php echo $prot_link ?>');
                jQuery(".subsubsub").append('<?php echo $quick_edit_notice ?>');
                jQuery(".subsubsub").append('<?php echo $quick_edit_notice_prod_link ?>');
                jQuery(".quick_hide a").on('click', function () {
                    jQuery(".quick_product_trnsl_link").attr('href', jQuery("#wcml_product_trnsl_link").val() + jQuery(this).closest('tr').attr('id').replace(/post-/, ''));
                });

                //lock feautured for translations
                jQuery(document).on('click', '.featured a', function () {

                    if (jQuery(this).closest('tr').find('.quick_hide').size() > 0) {

                        return false;

                    }

                });
            </script>
            <?php
        }

        if ( isset($_GET['taxonomy']) ) {
            $pos = strpos( $_GET['taxonomy'], 'pa_' );

            if ( $pos !== false && $pagenow == 'edit-tags.php' ) {
                $prot_link = '<span class="button" style="padding:4px;margin-top:0px; float: left;"><img align="baseline" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" width="16" height="16" style="margin-bottom:-4px" /> <a href="' . WCML_Links::generate_tracking_link( 'https://wpml.org/documentation/related-projects/woocommerce-multilingual/', 'woocommerce-multilingual', 'documentation', '#3' ) . '" target="_blank" style="text-decoration: none;">' .
                    __( 'How to translate attributes', 'sitepress' ) . '<\/a>' . '<\/span><br \/><br \/>';
                ?>
                <script type="text/javascript">
                    jQuery("table.widefat").before('<?php echo $prot_link ?>');
                </script>
                <?php
            }
        }

        if ( isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'product_cat' ) {

            $prot_link = '<span class="button" style="padding:4px;margin-top:0px; float: left;"><img align="baseline" src="' . ICL_PLUGIN_URL . '/res/img/icon16.png" width="16" height="16" style="margin-bottom:-4px" /> <a href="' . WCML_Links::generate_tracking_link( 'https://wpml.org/documentation/related-projects/woocommerce-multilingual/', 'woocommerce-multilingual', 'documentation', '#3' ) . '" target="_blank" style="text-decoration: none;">' .
                __( 'How to translate product categories', 'sitepress' ) . '<\/a>' . '<\/span><br \/><br \/>';
            ?>
            <script type="text/javascript">
                jQuery("table.widefat").before('<?php echo $prot_link ?>');
            </script>
            <?php
        }
    }

    public static function hide_multilingual_content_setup_box(){
        remove_meta_box('icl_div_config', convert_to_screen('shop_order'), 'normal');
        remove_meta_box('icl_div_config', convert_to_screen('shop_coupon'), 'normal');
    }

    public static function restrict_admin_with_redirect() {
        global $pagenow;

        $default_lang = self::$sitepress->get_default_language();
        $current_lang = self::$sitepress->get_current_language();

        if(
            ( $pagenow == 'post.php' && isset( $_GET[ 'post' ] ) ) ||
            ( $pagenow == 'admin.php' &&
                isset( $_GET[ 'action' ] ) &&
                $_GET[ 'action'] == 'duplicate_product' &&
                isset( $_GET[ 'post' ] )
            )
        ){
            $prod_lang = self::$sitepress->get_language_for_element( $_GET[ 'post' ], 'post_product' );
        }

        if(
            !self::$woocommerce_wpml->settings[ 'trnsl_interface' ] &&
            $pagenow == 'post.php' &&
            isset( $_GET[ 'post' ] )&&
            get_post_type( $_GET[ 'post' ] ) == 'product' &&
            !self::$woocommerce_wpml->products->is_original_product(  $_GET[ 'post' ] ) )
        {
            add_action( 'admin_notices', array( __CLASS__, 'inf_editing_product_in_non_default_lang' ) );
        }

        if(
            self::$woocommerce_wpml->settings[ 'trnsl_interface' ] &&
            $pagenow == 'post.php' &&
            !is_ajax() &&
            isset( $_GET[ 'post' ] ) &&
            !self::$woocommerce_wpml->products->is_original_product( $_GET[ 'post' ] ) &&
            get_post_type( $_GET[ 'post' ] ) == 'product'
        ) {
            if(
                !isset( $_GET[ 'action' ] ) ||
                ( isset( $_GET[ 'action' ] ) && !in_array( $_GET[ 'action' ], array( 'trash', 'delete' ) ) )
            ) {
                wp_redirect( admin_url( 'admin.php?page=wpml-wcml&tab=products' ) );
                exit;
            }
        }

        if(
            self::$woocommerce_wpml->settings[ 'trnsl_interface' ] &&
            $pagenow == 'admin.php' &&
            isset( $_GET[ 'action' ] ) &&
            $_GET[ 'action' ] == 'duplicate_product' &&
            $default_lang != $prod_lang )
        {
            wp_redirect( admin_url( 'admin.php?page=wpml-wcml&tab=products' ) );
            exit;
        }
    }

    public static function inf_editing_product_in_non_default_lang(){
        $message = '<div class="message error"><p>';
        $message .= sprintf(
                        __( 'The recommended way to translate WooCommerce products is using the
                             %sWooCommerce Multilingual products translation%s page.
                             Please use this page only for translating elements that are not available in the WooCommerce Multilingual products translation table.',
                            'woocommerce-multilingual' ),
                    '<strong><a href="' .admin_url( 'admin.php?page=wpml-wcml&tab=products' ) . '">', '</a></strong>' );
        $message .= '</p></div>';

        echo $message;
    }

}