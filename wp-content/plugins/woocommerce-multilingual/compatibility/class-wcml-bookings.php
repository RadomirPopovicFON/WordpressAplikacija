<?php

class WCML_Bookings{

    public $tp;

    function __construct(){

        add_action( 'woocommerce_bookings_after_booking_base_cost' , array( $this, 'wcml_price_field_after_booking_base_cost' ) );
        add_action( 'woocommerce_bookings_after_booking_block_cost' , array( $this, 'wcml_price_field_after_booking_block_cost' ) );
        add_action( 'woocommerce_bookings_after_display_cost' , array( $this, 'wcml_price_field_after_display_cost' ) );
        add_action( 'woocommerce_bookings_after_booking_pricing_base_cost' , array( $this, 'wcml_price_field_after_booking_pricing_base_cost' ), 10, 2 );
        add_action( 'woocommerce_bookings_after_booking_pricing_cost' , array( $this, 'wcml_price_field_after_booking_pricing_cost' ), 10, 2 );
        add_action( 'woocommerce_bookings_after_person_cost' , array( $this, 'wcml_price_field_after_person_cost' ) );
        add_action( 'woocommerce_bookings_after_person_block_cost' , array( $this, 'wcml_price_field_after_person_block_cost' ) );
        add_action( 'woocommerce_bookings_after_resource_cost' , array( $this, 'wcml_price_field_after_resource_cost' ), 10, 2 );
        add_action( 'woocommerce_bookings_after_resource_block_cost' , array( $this, 'wcml_price_field_after_resource_block_cost' ), 10, 2 );
        add_action( 'woocommerce_bookings_after_bookings_pricing' , array( $this, 'after_bookings_pricing' ) );

        add_action( 'init', array( $this, 'load_assets' ) );

        add_action( 'save_post', array( $this, 'save_custom_costs' ), 110, 2 );
        add_action( 'wcml_before_sync_product_data', array( $this, 'sync_bookings' ), 10, 3 );
        add_action( 'wcml_before_sync_product', array( $this, 'sync_booking_data' ), 10, 2 );

        add_filter( 'woocommerce_bookings_process_cost_rules_cost', array( $this, 'wc_bookings_process_cost_rules_cost' ), 10, 3 );
        add_filter( 'woocommerce_bookings_process_cost_rules_base_cost', array( $this, 'wc_bookings_process_cost_rules_base_cost' ), 10, 3 );
        add_filter( 'woocommerce_bookings_process_cost_rules_override_block', array( $this, 'wc_bookings_process_cost_rules_override_block_cost' ), 10, 3 );

        add_filter( 'wcml_multi_currency_is_ajax', array( $this, 'wcml_multi_currency_is_ajax' ) );

        add_filter( 'wcml_cart_contents_not_changed', array( $this, 'filter_bundled_product_in_cart_contents' ), 10, 3 );

        add_action( 'woocommerce_bookings_after_create_booking_page', array( $this, 'booking_currency_dropdown' ) );
        add_action( 'init', array( $this, 'set_booking_currency') );

        add_action( 'wp_ajax_wcml_booking_set_currency', array( $this, 'set_booking_currency_ajax' ) );
        add_action( 'woocommerce_bookings_create_booking_page_add_order_item', array( $this, 'set_order_currency_on_create_booking_page' ) );
        add_filter( 'woocommerce_currency_symbol', array( $this, 'filter_booking_currency_symbol' ) );
        add_filter( 'get_booking_products_args', array( $this, 'filter_get_booking_products_args' ) );
        add_filter( 'wcml_filter_currency_position', array( $this, 'create_booking_page_client_currency' ) );

        add_filter( 'wcml_client_currency', array( $this, 'create_booking_page_client_currency' ) );

        add_action( 'wcml_gui_additional_box_html', array( $this, 'custom_box_html'), 10, 3 );
        add_filter( 'wcml_gui_additional_box_data', array( $this, 'custom_box_html_data'), 10, 4 );
        add_filter( 'wcml_check_is_single', array( $this, 'show_custom_blocks_for_resources_and_persons'), 10, 3 );
        add_filter( 'wcml_product_content_exception', array( $this, 'remove_custom_fields_to_translate' ), 10, 3 );
        add_filter( 'wcml_not_display_single_fields_to_translate', array( $this, 'remove_single_custom_fields_to_translate' ) );
        add_filter( 'wcml_product_content_label', array( $this, 'product_content_resource_label' ), 10, 2 );
        add_action( 'wcml_update_extra_fields', array( $this, 'wcml_products_tab_sync_resources_and_persons'), 10, 4 );

        add_action( 'woocommerce_new_booking', array( $this, 'duplicate_booking_for_translations') );

        $bookings_statuses = array( 'unpaid', 'pending-confirmation', 'confirmed', 'paid', 'cancelled', 'complete', 'in-cart', 'was-in-cart' );
        foreach( $bookings_statuses as $status ){
            add_action('woocommerce_booking_' . $status, array( $this, 'update_status_for_translations' ) );
        }

        add_filter( 'parse_query', array( $this, 'booking_filters_query' ) );
        add_filter( 'woocommerce_bookings_in_date_range_query', array( $this, 'bookings_in_date_range_query') );
        add_action( 'before_delete_post', array( $this, 'delete_bookings' ) );
        add_action( 'wp_trash_post', array( $this, 'trash_bookings' ) );

        if( is_admin() ){

            $this->tp = new WPML_Element_Translation_Package;

            add_filter( 'wpml_tm_translation_job_data', array( $this, 'append_persons_to_translation_package' ), 10, 2 );
            add_action( 'wpml_translation_job_saved',   array( $this, 'save_person_translation' ), 10, 3 );

            add_filter( 'wpml_tm_translation_job_data', array( $this, 'append_resources_to_translation_package' ), 10, 2 );
            add_action( 'wpml_translation_job_saved',   array( $this, 'save_resource_translation' ), 10, 3 );

            //lock fields on translations pages
            add_filter( 'wcml_js_lock_fields_ids', array( $this, 'wcml_js_lock_fields_ids' ) );
            add_filter( 'wcml_after_load_lock_fields_js', array( $this, 'localize_lock_fields_js' ) );
        }

        if( !is_admin() || isset( $_POST['action'] ) && $_POST['action'] == 'wc_bookings_calculate_costs' ){
            add_filter( 'get_post_metadata', array( $this, 'filter_wc_booking_cost' ), 10, 4 );
        }


        $this->clear_transient_fields();

    }

    function wcml_price_field_after_booking_base_cost( $post_id ){

        $this->echo_wcml_price_field( $post_id, 'wcml_wc_booking_cost' );

    }

    function wcml_price_field_after_booking_block_cost( $post_id ){

        $this->echo_wcml_price_field( $post_id, 'wcml_wc_booking_base_cost' );

    }

    function wcml_price_field_after_display_cost( $post_id ){

        $this->echo_wcml_price_field( $post_id, 'wcml_wc_display_cost' );

    }

    function wcml_price_field_after_booking_pricing_base_cost( $pricing, $post_id ){

        $this->echo_wcml_price_field( $post_id, 'wcml_wc_booking_pricing_base_cost', $pricing );

    }

    function wcml_price_field_after_booking_pricing_cost( $pricing, $post_id ){

        $this->echo_wcml_price_field( $post_id, 'wcml_wc_booking_pricing_cost', $pricing );

    }

    function wcml_price_field_after_person_cost( $person_type_id ){

        $this->echo_wcml_price_field( $person_type_id, 'wcml_wc_booking_person_cost', false, false );

    }

    function wcml_price_field_after_person_block_cost( $person_type_id ){

        $this->echo_wcml_price_field( $person_type_id, 'wcml_wc_booking_person_block_cost', false, false );

    }

    function wcml_price_field_after_resource_cost( $resource_id, $post_id ){

        $this->echo_wcml_price_field( $post_id, 'wcml_wc_booking_resource_cost', false, true, $resource_id );

    }

    function wcml_price_field_after_resource_block_cost( $resource_id, $post_id ){

        $this->echo_wcml_price_field( $post_id, 'wcml_wc_booking_resource_block_cost', false, true, $resource_id );

    }

    function echo_wcml_price_field( $post_id, $field, $pricing = false, $check = true, $resource_id = false ){
        global $woocommerce_wpml;

        if( ( !$check || $woocommerce_wpml->products->is_original_product( $post_id ) ) && $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT ){

            $currencies = $woocommerce_wpml->multi_currency->get_currencies();

            $wc_currencies = get_woocommerce_currencies();

            echo '<div class="wcml_custom_cost_field" >';

            foreach($currencies as $currency_code => $currency){

                switch( $field ){
                    case 'wcml_wc_booking_cost':
                        woocommerce_wp_text_input( array( 'id' => 'wcml_wc_booking_cost', 'class'=>'wcml_bookings_custom_price', 'name' => 'wcml_wc_booking_cost['.$currency_code.']', 'label' => get_woocommerce_currency_symbol($currency_code), 'description' => __( 'One-off cost for the booking as a whole.', 'woocommerce-bookings' ), 'value' => get_post_meta( $post_id, '_wc_booking_cost_'.$currency_code, true ), 'type' => 'number', 'desc_tip' => true, 'custom_attributes' => array(
                            'min'   => '',
                            'step' 	=> '0.01'
                        ) ) );
                        break;
                    case 'wcml_wc_booking_base_cost':
                        woocommerce_wp_text_input( array( 'id' => 'wcml_wc_booking_base_cost', 'class'=>'wcml_bookings_custom_price', 'name' => 'wcml_wc_booking_base_cost['.$currency_code.']', 'label' => get_woocommerce_currency_symbol($currency_code), 'description' => __( 'This is the cost per block booked. All other costs (for resources and persons) are added to this.', 'woocommerce-bookings' ), 'value' => get_post_meta( $post_id, '_wc_booking_base_cost_'.$currency_code, true ), 'type' => 'number', 'desc_tip' => true, 'custom_attributes' => array(
                            'min'   => '',
                            'step' 	=> '0.01'
                        ) ) );
                        break;
                    case 'wcml_wc_display_cost':
                        woocommerce_wp_text_input( array( 'id' => 'wcml_wc_display_cost', 'class'=>'wcml_bookings_custom_price', 'name' => 'wcml_wc_display_cost['.$currency_code.']', 'label' => get_woocommerce_currency_symbol($currency_code), 'description' => __( 'The cost is displayed to the user on the frontend. Leave blank to have it calculated for you. If a booking has varying costs, this will be prefixed with the word "from:".', 'woocommerce-bookings' ), 'value' => get_post_meta( $post_id, '_wc_display_cost_'.$currency_code, true ), 'type' => 'number', 'desc_tip' => true, 'custom_attributes' => array(
                            'min'   => '',
                            'step' 	=> '0.01'
                        ) ) );
                        break;

                    case 'wcml_wc_booking_pricing_base_cost':

                        if( isset( $pricing[ 'base_cost_'.$currency_code ] ) ){
                            $value = $pricing[ 'base_cost_'.$currency_code ];
                        }else{
                            $value = '';
                        }

                        echo '<div class="wcml_bookings_range_block" >';
                        echo '<label>'. get_woocommerce_currency_symbol($currency_code) .'</label>';
                        echo '<input type="number" step="0.01" name="wcml_wc_booking_pricing_base_cost['.$currency_code.'][]" class="wcml_bookings_custom_price" value="'. $value .'" placeholder="0" />';
                        echo '</div>';
                        break;

                    case 'wcml_wc_booking_pricing_cost':

                        if( isset( $pricing[ 'cost_'.$currency_code ] ) ){
                            $value = $pricing[ 'cost_'.$currency_code ];
                        }else{
                            $value = '';
                        }

                        echo '<div class="wcml_bookings_range_block" >';
                        echo '<label>'. get_woocommerce_currency_symbol($currency_code) .'</label>';
                        echo '<input type="number" step="0.01" name="wcml_wc_booking_pricing_cost['.$currency_code.'][]" class="wcml_bookings_custom_price" value="'. $value .'" placeholder="0" />';
                        echo '</div>';
                        break;

                    case 'wcml_wc_booking_person_cost':

                        $value = get_post_meta( $post_id, 'cost_'.$currency_code, true );

                        echo '<div class="wcml_bookings_person_block" >';
                        echo '<label>'. get_woocommerce_currency_symbol($currency_code) .'</label>';
                        echo '<input type="number" step="0.01" name="wcml_wc_booking_person_cost['.$post_id.']['.$currency_code.']" class="wcml_bookings_custom_price" value="'. $value .'" placeholder="0" />';
                        echo '</div>';
                        break;

                    case 'wcml_wc_booking_person_block_cost':

                        $value = get_post_meta( $post_id, 'block_cost_'.$currency_code, true );

                        echo '<div class="wcml_bookings_person_block" >';
                        echo '<label>'. get_woocommerce_currency_symbol($currency_code) .'</label>';
                        echo '<input type="number" step="0.01" name="wcml_wc_booking_person_block_cost['.$post_id.']['.$currency_code.']" class="wcml_bookings_custom_price" value="'. $value .'" placeholder="0" />';
                        echo '</div>';
                        break;

                    case 'wcml_wc_booking_resource_cost':

                        $resource_base_costs = maybe_unserialize( get_post_meta( $post_id, '_resource_base_costs', true ) );

                        if( isset( $resource_base_costs[ 'custom_costs' ][ $currency_code ][ $resource_id ] ) ){
                            $value = $resource_base_costs[ 'custom_costs' ][ $currency_code ][ $resource_id ];
                        }else{
                            $value = '';
                        }

                        echo '<div class="wcml_bookings_resource_block" >';
                        echo '<label>'. get_woocommerce_currency_symbol($currency_code) .'</label>';
                        echo '<input type="number" step="0.01" name="wcml_wc_booking_resource_cost['.$resource_id.']['.$currency_code.']" class="wcml_bookings_custom_price" value="'. $value .'" placeholder="0" />';
                        echo '</div>';
                        break;

                    case 'wcml_wc_booking_resource_block_cost':

                        $resource_block_costs = maybe_unserialize( get_post_meta( $post_id, '_resource_block_costs', true ) );

                        if( isset( $resource_block_costs[ 'custom_costs' ][ $currency_code ][ $resource_id ] ) ){
                            $value = $resource_block_costs[ 'custom_costs' ][ $currency_code ][ $resource_id ];
                        }else{
                            $value = '';
                        }

                        echo '<div class="wcml_bookings_resource_block" >';
                        echo '<label>'. get_woocommerce_currency_symbol($currency_code) .'</label>';
                        echo '<input type="number" step="0.01" name="wcml_wc_booking_resource_block_cost['.$resource_id.']['.$currency_code.']" class="wcml_bookings_custom_price" value="'. $value .'" placeholder="0" />';
                        echo '</div>';
                        break;

                    default:
                        break;

                }

            }

            echo '</div>';

        }
    }

    function after_bookings_pricing( $post_id ){
        global $woocommerce_wpml;

        if( in_array( 'booking', wp_get_post_terms( $post_id, 'product_type', array( "fields" => "names" ) ) ) && $woocommerce_wpml->products->is_original_product( $post_id ) && $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT ){

            $custom_costs_status = get_post_meta( $post_id, '_wcml_custom_costs_status', true );

            $checked = !$custom_costs_status ? 'checked="checked"' : ' ';

            echo '<div class="wcml_custom_costs">';

            echo '<input type="radio" name="_wcml_custom_costs" id="wcml_custom_costs_auto" value="0" class="wcml_custom_costs_input" '. $checked .' />';
            echo '<label for="wcml_custom_costs_auto">'. __('Calculate costs in other currencies automatically', 'woocommerce-multilingual') .'</label>';

            $checked = $custom_costs_status == 1 ? 'checked="checked"' : ' ';

            echo '<input type="radio" name="_wcml_custom_costs" value="1" id="wcml_custom_costs_manually" class="wcml_custom_costs_input" '. $checked .' />';
            echo '<label for="wcml_custom_costs_manually">'. __('Set costs in other currencies manually', 'woocommerce-multilingual') .'</label>';

            wp_nonce_field( 'wcml_save_custom_costs', '_wcml_custom_costs_nonce' );

            echo '</div>';
        }

    }

    function save_custom_costs( $post_id, $post ){
        global $woocommerce_wpml;

        $nonce = filter_input( INPUT_POST, '_wcml_custom_costs_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

        if( isset( $_POST['_wcml_custom_costs'] ) && isset( $nonce ) && wp_verify_nonce( $nonce, 'wcml_save_custom_costs' ) ){
            
            update_post_meta( $post_id, '_wcml_custom_costs_status', $_POST['_wcml_custom_costs'] );

            if( $_POST['_wcml_custom_costs'] == 1 ){

                $currencies = $woocommerce_wpml->multi_currency->get_currencies();

                foreach( $currencies as $code => $currency ){

                    $wc_booking_cost = $_POST[ 'wcml_wc_booking_cost' ][ $code ];
                    update_post_meta( $post_id, '_wc_booking_cost_'.$code, $wc_booking_cost  );

                    $wc_booking_base_cost = $_POST[ 'wcml_wc_booking_base_cost' ][ $code ];
                    update_post_meta( $post_id, '_wc_booking_base_cost_'.$code, $wc_booking_base_cost  );

                    $wc_display_cost = $_POST[ 'wcml_wc_display_cost' ][ $code ];
                    update_post_meta( $post_id, '_wc_display_cost_'.$code, $wc_display_cost  );

                }

                $booking_pricing = get_post_meta( $post_id, '_wc_booking_pricing', true );
                foreach ( $booking_pricing  as $key => $prices) {

                    $updated_meta[$key] = $prices;

                    foreach ($currencies as $code => $currency) {

                        $updated_meta[$key]['base_cost_' . $code] = $_POST['wcml_wc_booking_pricing_base_cost'][$code][$key];
                        $updated_meta[$key]['cost_' . $code] = $_POST['wcml_wc_booking_pricing_cost'][$code][$key];

                    }

                }

                update_post_meta($post_id, '_wc_booking_pricing', $updated_meta);

                //person costs
                if( isset( $_POST[ 'wcml_wc_booking_person_cost' ] ) ) {

                    foreach ($_POST['wcml_wc_booking_person_cost'] as $person_id => $costs) {

                        foreach ($currencies as $code => $currency) {

                            $wc_booking_person_cost = $costs[$code];
                            update_post_meta($person_id, 'cost_' . $code, $wc_booking_person_cost);

                        }

                    }

                }

                if( isset( $_POST[ 'wcml_wc_booking_person_cost' ] ) ){

                    foreach( $_POST[ 'wcml_wc_booking_person_block_cost' ] as $person_id => $costs ){

                        foreach( $currencies as $code => $currency ){

                            $wc_booking_person_block_cost = $costs[ $code ];
                            update_post_meta( $person_id, 'block_cost_'.$code, $wc_booking_person_block_cost  );

                        }

                    }

                }

                if( isset( $_POST[ 'wcml_wc_booking_resource_cost' ] ) ) {

                    $updated_meta = get_post_meta( $post_id, '_resource_base_costs', true );

                    $wc_booking_resource_costs = array();

                    foreach ( $_POST['wcml_wc_booking_resource_cost'] as $resource_id => $costs) {

                        foreach ($currencies as $code => $currency) {

                            $wc_booking_resource_costs[ $code ][ $resource_id ] = $costs[ $code ];

                        }

                    }

                    $updated_meta[ 'custom_costs' ] = $wc_booking_resource_costs;

                    update_post_meta( $post_id, '_resource_base_costs', $updated_meta );

                    $this->sync_resource_costs_with_translations( $post_id, '_resource_base_costs' );

                }

                if( isset( $_POST[ 'wcml_wc_booking_resource_block_cost' ] ) ){

                    $updated_meta = get_post_meta( $post_id, '_resource_block_costs', true );

                    $wc_booking_resource_block_costs = array();

                    foreach( $_POST[ 'wcml_wc_booking_resource_block_cost' ] as $resource_id => $costs ){

                        foreach( $currencies as $code => $currency ){

                            $wc_booking_resource_block_costs[ $code ][ $resource_id ] = $costs[ $code ];

                        }

                    }

                    $updated_meta[ 'custom_costs' ] = $wc_booking_resource_block_costs;

                    update_post_meta( $post_id, '_resource_block_costs', $updated_meta );

                    $this->sync_resource_costs_with_translations( $post_id, '_resource_block_costs' );

                }


            }
        }

    }

    // sync existing product bookings for translations
    function sync_bookings( $original_product_id, $product_id, $lang ){
        global $wpdb;

        $all_bookings_for_product = $wpdb->get_results( $wpdb->prepare( "SELECT post_id as id FROM $wpdb->postmeta WHERE meta_key = '_booking_product_id' AND meta_value = %d", $original_product_id ) );

        foreach($all_bookings_for_product as $booking ){
            $check_if_exists = $wpdb->get_row( $wpdb->prepare( "SELECT pm3.* FROM {$wpdb->postmeta} AS pm1
                                            LEFT JOIN {$wpdb->postmeta} AS pm2 ON pm1.post_id = pm2.post_id
                                            LEFT JOIN {$wpdb->postmeta} AS pm3 ON pm1.post_id = pm3.post_id
                                            WHERE pm1.meta_key = '_booking_duplicate_of' AND pm1.meta_value = %s AND pm2.meta_key = '_language_code' AND pm2.meta_value = %s AND pm3.meta_key = '_booking_product_id'"
                , $booking->id, $lang ) );

            if( is_null( $check_if_exists ) ){
                $this->duplicate_booking_for_translations( $booking->id, $lang );
            }elseif( $check_if_exists->meta_value === '' ){
                update_post_meta( $check_if_exists->post_id, '_booking_product_id', $this->get_translated_booking_product_id( $booking->id, $lang ) );
                update_post_meta( $check_if_exists->post_id, '_booking_resource_id', $this->get_translated_booking_resource_id( $booking->id, $lang ) );
                update_post_meta( $check_if_exists->post_id, '_booking_persons', $this->get_translated_booking_persons_ids( $booking->id, $lang ) );
            }
        }


    }

    function sync_booking_data( $original_product_id, $current_product_id ){

        if( has_term( 'booking', 'product_type', $original_product_id ) ){
            global $wpdb, $sitepress, $pagenow, $iclTranslationManagement;

            // get language code
            $language_details = $sitepress->get_element_language_details( $original_product_id, 'post_product' );
            if ( $pagenow == 'admin.php' && empty( $language_details ) ) {
                //translation editor support: sidestep icl_translations_cache
                $language_details = $wpdb->get_row( $wpdb->prepare( "SELECT element_id, trid, language_code, source_language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type = 'post_product'", $original_product_id ) );
            }
            if ( empty( $language_details ) ) {
                return;
            }

            // pick posts to sync
            $posts = array();
            $translations = $sitepress->get_element_translations( $language_details->trid, 'post_product' );
            foreach ( $translations as $translation ) {

                if ( !$translation->original ) {
                    $posts[ $translation->element_id ] = $translation;
                }
            }

            foreach ( $posts as $post_id => $translation ) {

                $trn_lang = $sitepress->get_language_for_element( $post_id, 'post_product' );

                //sync_resources
                $this->sync_resources( $original_product_id, $post_id, $trn_lang );

                //sync_persons
                $this->sync_persons( $original_product_id, $post_id, $trn_lang );
            }

        }

    }

    function sync_resources( $original_product_id, $trnsl_product_id, $lang_code, $duplicate = true ){
        global $wpdb;

        $orig_resources = $wpdb->get_results( $wpdb->prepare( "SELECT resource_id, sort_order FROM {$wpdb->prefix}wc_booking_relationships WHERE product_id = %d", $original_product_id ) );

        $trnsl_product_resources = $wpdb->get_col( $wpdb->prepare( "SELECT resource_id FROM {$wpdb->prefix}wc_booking_relationships WHERE product_id = %d", $trnsl_product_id ) );

        foreach ($orig_resources as $resource) {

            $trns_resource_id = apply_filters( 'translate_object_id', $resource->resource_id, 'bookable_resource', false, $lang_code );

            if ( !is_null( $trns_resource_id ) && in_array( $trns_resource_id, $trnsl_product_resources ) ) {

                if ( ( $key = array_search( $trns_resource_id, $trnsl_product_resources ) ) !== false ) {

                    unset($trnsl_product_resources[$key]);

                    $wpdb->update(
                        $wpdb->prefix . 'wc_booking_relationships',
                        array(
                            'sort_order' => $resource->sort_order
                        ),
                        array(
                            'product_id' => $trnsl_product_id,
                            'resource_id' => $trns_resource_id
                        )
                    );

                    update_post_meta( $trns_resource_id, 'qty', get_post_meta( $resource->resource_id, 'qty', true ) );
                    update_post_meta( $trns_resource_id, '_wc_booking_availability', get_post_meta( $resource->resource_id, '_wc_booking_availability', true ) );

                }

            } else {

                if( $duplicate ){

                    $trns_resource_id = $this->duplicate_resource( $trnsl_product_id, $resource, $lang_code );

                }else{

                    continue;

                }


            }

        }

        foreach ($trnsl_product_resources as $trnsl_product_resource) {

            $wpdb->delete(
                $wpdb->prefix . 'wc_booking_relationships',
                array(
                    'product_id' => $trnsl_product_id,
                    'resource_id' => $trnsl_product_resource
                )
            );

            wp_delete_post( $trnsl_product_resource );

        }

        $this->sync_resource_costs( $original_product_id, $trnsl_product_id, '_resource_base_costs', $lang_code );
        $this->sync_resource_costs( $original_product_id, $trnsl_product_id, '_resource_block_costs', $lang_code );

    }

    function duplicate_resource( $tr_product_id, $resource, $lang_code){
        global $sitepress, $wpdb, $iclTranslationManagement;

        if( method_exists( $sitepress, 'make_duplicate' ) ){

            $trns_resource_id = $sitepress->make_duplicate( $resource->resource_id, $lang_code );

        }else{

            if ( !isset( $iclTranslationManagement ) ) {
                $iclTranslationManagement = new TranslationManagement;
            }

            $trns_resource_id = $iclTranslationManagement->make_duplicate( $resource->resource_id, $lang_code );

        }

        $wpdb->insert(
            $wpdb->prefix . 'wc_booking_relationships',
            array(
                'product_id' => $tr_product_id,
                'resource_id' => $trns_resource_id,
                'sort_order' => $resource->sort_order
            )
        );

        delete_post_meta( $trns_resource_id, '_icl_lang_duplicate_of' );

        return $trns_resource_id;
    }

    function sync_persons( $original_product_id, $tr_product_id, $lang_code, $duplicate = true ){
        global $wpdb, $woocommerce_wpml;

        $orig_persons = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'bookable_person'", $original_product_id ) );

        $trnsl_persons = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'bookable_person'", $tr_product_id ) );


        foreach ($orig_persons as $person) {

            $trnsl_person_id = apply_filters( 'translate_object_id', $person, 'bookable_person', false, $lang_code );

            if ( !is_null( $trnsl_person_id ) && in_array( $trnsl_person_id, $trnsl_persons ) ) {

                if ( ( $key = array_search( $trnsl_person_id, $trnsl_persons ) ) !== false ) {

                    unset($trnsl_persons[$key]);

                    update_post_meta( $trnsl_person_id, 'block_cost', get_post_meta( $person, 'block_cost', true ) );
                    update_post_meta( $trnsl_person_id, 'cost', get_post_meta( $person, 'cost', true ) );
                    update_post_meta( $trnsl_person_id, 'max', get_post_meta( $person, 'max', true ) );
                    update_post_meta( $trnsl_person_id, 'min', get_post_meta( $person, 'min', true ) );


                    if( get_post_meta( $person, '_wcml_custom_costs_status', true ) && $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT){
                        $currencies = $woocommerce_wpml->multi_currency->get_currencies();

                        foreach( $currencies as $code => $currency ){

                            update_post_meta( $trnsl_person_id, 'block_cost_'.$code, get_post_meta( $person, 'block_cost_'.$code, true ) );
                            update_post_meta( $trnsl_person_id, 'block_cost_'.$code, get_post_meta( $person, 'cost_'.$code, true ) );

                        }
                    }

                }

            }else{

                if( $duplicate ) {

                    $this->duplicate_person($tr_product_id, $person, $lang_code);

                }else{

                    continue;

                }

            }

        }

        foreach ($trnsl_persons as $trnsl_person) {

            wp_delete_post( $trnsl_person );

        }

    }

    function duplicate_person( $tr_product_id, $person_id, $lang_code ){
        global $sitepress, $wpdb, $iclTranslationManagement;

        if( method_exists( $sitepress, 'make_duplicate' ) ){

            $new_person_id = $sitepress->make_duplicate( $person_id, $lang_code );

        }else{

            if ( !isset( $iclTranslationManagement ) ) {
                $iclTranslationManagement = new TranslationManagement;
            }

            $new_person_id = $iclTranslationManagement->make_duplicate( $person_id, $lang_code );

        }

        $wpdb->update(
            $wpdb->posts,
            array(
                'post_parent' => $tr_product_id
            ),
            array(
                'ID' => $new_person_id
            )
        );

        delete_post_meta( $new_person_id, '_icl_lang_duplicate_of' );

        return $new_person_id;
    }

    function filter_wc_booking_cost( $check, $object_id, $meta_key, $single ){

        if( in_array( $meta_key, array( '_wc_booking_cost', '_wc_booking_base_cost', '_wc_display_cost', '_wc_booking_pricing', 'cost', 'block_cost', '_resource_base_costs', '_resource_block_costs' ) ) ){

            global $woocommerce_wpml;

            if( $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT ){

                $original_id = apply_filters( 'translate_object_id', $object_id, 'product', true, $woocommerce_wpml->products->get_original_product_language( $object_id ) );

                $cost_status = get_post_meta( $original_id, '_wcml_custom_costs_status', true );

                $currency = $woocommerce_wpml->multi_currency->get_client_currency();

                if ( $currency == get_option('woocommerce_currency') ){
                    return $check;
                }

                if( in_array( $meta_key, array( 'cost', 'block_cost' ) ) ) {

                    if ( get_post_type($object_id) == 'bookable_person' ) {

                        $value = get_post_meta($object_id, $meta_key . '_' . $currency, true);

                        if ( $cost_status && $value ) {

                            return $value;

                        } else {

                            remove_filter( 'get_post_metadata', array( $this, 'filter_wc_booking_cost' ), 10, 4 );

                            $cost = get_post_meta( $object_id, $meta_key, true);

                            add_filter( 'get_post_metadata', array( $this, 'filter_wc_booking_cost' ), 10, 4 );

                            return $woocommerce_wpml->multi_currency->prices->convert_price_amount( $cost, $currency );
                        }

                    } else {

                        return $check;

                    }

                }

                if( in_array ( $meta_key, array( '_wc_booking_pricing', '_resource_base_costs', '_resource_block_costs' ) ) ){

                    remove_filter( 'get_post_metadata', array( $this, 'filter_wc_booking_cost' ), 10, 4 );

                    if( $meta_key == '_wc_booking_pricing' ){

                        if( $original_id != $object_id ){
                            $value = get_post_meta( $original_id, $meta_key );
                        }else{
                            $value = $check;
                        }

                    }else{

                        $costs = maybe_unserialize( get_post_meta( $object_id, $meta_key, true ) );

                        if( !$costs ){
                            $value = $check;
                        }elseif( $cost_status && isset( $costs[ 'custom_costs' ][ $currency ] ) ){
                            $value = array( 0 => $costs[ 'custom_costs' ][ $currency ] );
                        }elseif( $cost_status && isset( $costs[ 0 ][ 'custom_costs' ][ $currency ] )){
                            $value = array( 0 => $costs[ 0 ][ 'custom_costs' ][ $currency ] );
                        }else{

                            $converted_values = array();

                            foreach( $costs as $resource_id => $cost ){
                                $converted_values[0][ $resource_id ] = $woocommerce_wpml->multi_currency->prices->convert_price_amount( $cost, $currency );
                            }

                            $value = $converted_values;
                        }

                    }

                    add_filter( 'get_post_metadata', array( $this, 'filter_wc_booking_cost' ), 10, 4 );

                    return $value;

                }

                $value = get_post_meta( $original_id, $meta_key.'_'.$currency, true );

                if( $cost_status &&  ( !empty($value) || ( empty($value) && $meta_key == '_wc_display_cost' ) ) ){

                    return $value;

                }else{

                    remove_filter( 'get_post_metadata', array( $this, 'filter_wc_booking_cost' ), 10, 4 );

                    $value = get_post_meta( $original_id, $meta_key, true );

                    $value = $woocommerce_wpml->multi_currency->prices->convert_price_amount( $value, $currency );

                    add_filter( 'get_post_metadata', array( $this, 'filter_wc_booking_cost' ), 10, 4 );

                    return $value;

                }

            }

        }

        return $check;
    }

    function sync_resource_costs_with_translations( $object_id, $meta_key, $check = false ){
        global $sitepress,$woocommerce_wpml;

        $original_product_id = apply_filters( 'translate_object_id', $object_id, 'product', true, $woocommerce_wpml->products->get_original_product_language( $object_id ) );

        if( $object_id == $original_product_id ){

            $trid = $sitepress->get_element_trid( $object_id, 'post_product' );
            $translations = $sitepress->get_element_translations( $trid, 'post_product' );

            foreach ( $translations as $translation ) {

                if ( !$translation->original ) {

                    $this->sync_resource_costs( $original_product_id, $translation->element_id, $meta_key, $translation->language_code );

                }
            }

            return $check;

        }else{

            $language_code = $sitepress->get_language_for_element( $object_id, 'post_product' );

            $this->sync_resource_costs( $original_product_id, $object_id, $meta_key, $language_code );

            return true;

        }

    }

    function sync_resource_costs( $original_product_id, $object_id, $meta_key, $language_code ){

        $original_costs = maybe_unserialize( get_post_meta( $original_product_id, $meta_key, true ) );

        $wc_booking_resource_costs = array();
        if( !empty( $original_costs ) ) {
            foreach ($original_costs as $resource_id => $costs) {

                if ($resource_id == 'custom_costs' && isset($costs['custom_costs'])) {

                    foreach ($costs['custom_costs'] as $code => $currencies) {

                        foreach ($currencies as $custom_costs_resource_id => $custom_cost) {

                            $trns_resource_id = apply_filters('translate_object_id', $custom_costs_resource_id, 'bookable_resource', true, $language_code);

                            $wc_booking_resource_costs['custom_costs'][$code][$trns_resource_id] = $custom_cost;

                        }

                    }

                } else {

                    $trns_resource_id = apply_filters('translate_object_id', $resource_id, 'bookable_resource', true, $language_code);

                    $wc_booking_resource_costs[$trns_resource_id] = $costs;

                }

            }
        }

        update_post_meta( $object_id, $meta_key, $wc_booking_resource_costs );

    }

    function wc_bookings_process_cost_rules_cost( $cost, $fields, $key ){
        return $this->filter_pricing_cost( $cost, $fields, 'cost_', $key );
    }

    function wc_bookings_process_cost_rules_base_cost( $base_cost, $fields, $key ){
        return $this->filter_pricing_cost( $base_cost, $fields, 'base_cost_', $key );
    }

    function wc_bookings_process_cost_rules_override_block_cost( $override_cost, $fields, $key ){
        return $this->filter_pricing_cost( $override_cost, $fields, 'override_block_', $key );
    }

    function filter_pricing_cost( $cost, $fields, $name, $key ){
        global $woocommerce_wpml, $product;

        if( $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT ){

            $currency = $woocommerce_wpml->multi_currency->get_client_currency();

            if ( $currency == get_option('woocommerce_currency') ) {
                return $cost;
            }

            if( isset( $_POST[ 'form' ] ) ){
                parse_str( $_POST[ 'form' ], $posted );

                $booking_id = $posted[ 'add-to-cart' ];

            }elseif( isset( $_POST[ 'add-to-cart' ] ) ){

                $booking_id = $_POST[ 'add-to-cart' ];

            }

            if( isset( $booking_id ) ){
                $original_id = apply_filters( 'translate_object_id', $booking_id, 'product', true, $woocommerce_wpml->products->get_original_product_language( $booking_id ) );

                if( $booking_id != $original_id ){
                    $fields = maybe_unserialize( get_post_meta( $original_id, '_wc_booking_pricing', true ) );
                    $fields = $fields[$key];
                }
            }

            if( isset( $fields[ $name.$currency ] ) ){
                return $fields[ $name.$currency ];
            }else{
                return $woocommerce_wpml->multi_currency->prices->convert_price_amount( $cost, $currency );
            }

        }

        return $cost;

    }

    function load_assets( ){
        global $pagenow, $woocommerce_wpml;

        if( $pagenow == 'post.php' || $pagenow == 'post-new.php' ){

	        wp_register_style( 'wcml-bookings-css', WCML_PLUGIN_URL . '/compatibility/res/css/wcml-bookings.css', array(), WCML_VERSION );
            wp_enqueue_style( 'wcml-bookings-css' );

	        wp_register_script( 'wcml-bookings-js', WCML_PLUGIN_URL . '/compatibility/res/js/wcml-bookings.js', array( 'jquery' ), WCML_VERSION );
            wp_enqueue_script( 'wcml-bookings-js' );

        }

    }

    function localize_lock_fields_js(){
        wp_localize_script( 'wcml-bookings-js', 'lock_settings' , array( 'lock_fields' => 1 ) );
    }

    function wcml_multi_currency_is_ajax( $actions ){

        $actions[] = 'wc_bookings_calculate_costs';

        return $actions;
    }

    function filter_bundled_product_in_cart_contents( $cart_item, $key, $current_language ){

        if( $cart_item[ 'data' ] instanceof WC_Product_Booking && isset( $cart_item[ 'booking' ] ) ){
            global $woocommerce_wpml;

            $current_id = apply_filters( 'translate_object_id', $cart_item[ 'data' ]->id, 'product', true, $current_language );
            $cart_product_id = $cart_item['data']->id;

            if( $current_id != $cart_product_id ) {

                $cart_item['data'] = new WC_Product_Booking( $current_id );

            }

            if( $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT || $current_id != $cart_product_id ){

                $booking_info = array(
                    'wc_bookings_field_start_date_year' => $cart_item[ 'booking' ][ '_year' ],
                    'wc_bookings_field_start_date_month' => $cart_item[ 'booking' ][ '_month' ],
                    'wc_bookings_field_start_date_day' => $cart_item[ 'booking' ][ '_day' ],
                    'add-to-cart' => $current_id
                );

                if( isset( $cart_item[ 'booking' ][ '_persons' ] ) ){
                    foreach( $cart_item[ 'booking' ][ '_persons' ] as $person_id => $value ){
                        $booking_info[  'wc_bookings_field_persons_' . apply_filters( 'translate_object_id', $person_id, 'bookable_person', false, $current_language ) ] = $value;
                    }
                }

                if( isset( $cart_item[ 'booking' ][ '_resource_id' ]  ) ){
                    $booking_info[ 'wc_bookings_field_resource' ] = apply_filters( 'translate_object_id', $cart_item[ 'booking' ][ '_resource_id' ], 'bookable_resource', false, $current_language);
                }

                if( isset( $cart_item[ 'booking' ][ '_duration' ]  ) ){
                    $booking_info[ 'wc_bookings_field_duration' ] = $cart_item[ 'booking' ][ '_duration' ];
                }

                if( isset( $cart_item[ 'booking' ][ '_time' ]  ) ){
                    $booking_info[ 'wc_bookings_field_start_date_time' ] = $cart_item[ 'booking' ][ '_time' ];
                }

                $booking_form = new WC_Booking_Form( wc_get_product( $current_id ) );

                $prod_qty = get_post_meta( $current_id, '_wc_booking_qty', true );
                update_post_meta( $current_id, '_wc_booking_qty', intval( $prod_qty + $cart_item[ 'booking' ][ '_qty' ] ) );
                $cost = $booking_form->calculate_booking_cost( $booking_info );
                update_post_meta( $current_id, '_wc_booking_qty', $prod_qty );

                if( !is_wp_error( $cost ) ){
                    $cart_item[ 'data' ]->set_price( $cost );
                }
            }

        }

        return $cart_item;

    }

    function booking_currency_dropdown(){
        global $woocommerce_wpml, $sitepress;

        if( $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT ){
            $current_booking_currency = $this->get_cookie_booking_currency();

            $wc_currencies = get_woocommerce_currencies();
            $currencies = $woocommerce_wpml->multi_currency->get_currencies( $include_default = true );
            ?>
            <tr valign="top">
                <th scope="row"><?php _e( 'Booking currency', 'woocommerce-multilingual' ); ?></th>
                <td>
                    <select id="dropdown_booking_currency">

                        <?php foreach($currencies as $currency => $count ): ?>

                            <option value="<?php echo $currency ?>" <?php echo $current_booking_currency == $currency ? 'selected="selected"':''; ?>><?php echo $wc_currencies[$currency]; ?></option>

                        <?php endforeach; ?>

                    </select>
                </td>
            </tr>

            <?php

            $wcml_booking_set_currency_nonce = wp_create_nonce( 'booking_set_currency' );

            wc_enqueue_js( "

            jQuery(document).on('change', '#dropdown_booking_currency', function(){
               jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'wcml_booking_set_currency',
                        currency: jQuery('#dropdown_booking_currency').val(),
                        wcml_nonce: '".$wcml_booking_set_currency_nonce."'
                    },
                    success: function( response ){
                        if(typeof response.error !== 'undefined'){
                            alert(response.error);
                        }else{
                           window.location = window.location.href;
                        }
                    }
                })
            });
        ");

        }

    }

    function set_booking_currency_ajax(){

        $nonce = filter_input( INPUT_POST, 'wcml_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        if(!$nonce || !wp_verify_nonce($nonce, 'booking_set_currency')){
            echo json_encode(array('error' => __('Invalid nonce', 'woocommerce-multilingual')));
            die();
        }

        $this->set_booking_currency(filter_input( INPUT_POST, 'currency', FILTER_SANITIZE_FULL_SPECIAL_CHARS ));

        die();
    }

    function set_booking_currency( $currency_code = false ){

        if( !isset( $_COOKIE [ '_wcml_booking_currency' ]) && !headers_sent()) {
            global $woocommerce_wpml;

            $currency_code = get_woocommerce_currency();

            if ( $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT ){
                $order_currencies = $woocommerce_wpml->multi_currency->orders->get_orders_currencies();

                if (!isset($order_currencies[$currency_code])) {
                    foreach ($order_currencies as $currency_code => $count) {
                        $currency_code = $currency_code;
                        break;
                    }
                }
            }
        }

        if( $currency_code ){
            setcookie('_wcml_booking_currency', $currency_code , time() + 86400, COOKIEPATH, COOKIE_DOMAIN);
        }

    }

    function get_cookie_booking_currency(){

        if( isset( $_COOKIE [ '_wcml_booking_currency' ] ) ){
            $currency = $_COOKIE[ '_wcml_booking_currency' ];
        }else{
            $currency = get_woocommerce_currency();
        }

        return $currency;
    }

    function filter_booking_currency_symbol( $currency ){
        global $pagenow;

        remove_filter( 'woocommerce_currency_symbol', array( $this, 'filter_booking_currency_symbol' ) );
        if( isset( $_COOKIE [ '_wcml_booking_currency' ] ) && $pagenow == 'edit.php' && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'create_booking' ){
            $currency = get_woocommerce_currency_symbol( $_COOKIE [ '_wcml_booking_currency' ] );
        }
        add_filter( 'woocommerce_currency_symbol', array( $this, 'filter_booking_currency_symbol' ) );

        return $currency;
    }

    function create_booking_page_client_currency( $currency ){
        global $pagenow;

        if( wpml_is_ajax() && isset( $_POST[ 'form' ] ) ){
            parse_str( $_POST[ 'form' ], $posted );
        }

        if( ( $pagenow == 'edit.php' && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'create_booking' ) || ( isset( $posted[ '_wp_http_referer' ] ) && strpos( $posted[ '_wp_http_referer' ], 'page=create_booking' ) !== false ) ){
            $currency = $this->get_cookie_booking_currency();
        }

        return $currency;
    }

    function set_order_currency_on_create_booking_page( $order_id ){
        global $sitepress;

        update_post_meta( $order_id, '_order_currency', $this->get_cookie_booking_currency() );

        update_post_meta( $order_id, 'wpml_language', $sitepress->get_current_language() );

    }

    function filter_get_booking_products_args( $args ){
        if( isset( $args['suppress_filters'] ) ){
            $args['suppress_filters'] = false;
        }
        return $args;
    }

    function custom_box_html( $obj, $product_id, $data ){
        global $wpdb;

        if( wc_get_product($product_id)->product_type != 'booking' ){
            return;
        }

        $bookings_section = new WPML_Editor_UI_Field_Section( __( 'Bookings', 'woocommerce-multilingual' ) );

        if( get_post_meta( $product_id,'_wc_booking_has_resources',true) == 'yes' ){
            $group = new WPML_Editor_UI_Field_Group( '', true );
            $booking_field = new WPML_Editor_UI_Single_Line_Field( '_wc_booking_resouce_label', __( 'Resources Label', 'woocommerce-multilingual' ), $data, true );
            $group->add_field( $booking_field );
            $bookings_section->add_field( $group );
        }

        $orig_resources = maybe_unserialize( get_post_meta( $product_id, '_resource_base_costs', true ) );

        if( $orig_resources ){
            $group = new WPML_Editor_UI_Field_Group( __( 'Resources', 'woocommerce-multilingual' ) );
            $group_title = __( 'Resources', 'woocommerce-multilingual' );
            foreach ( $orig_resources as $resource_id => $cost) {

                if ($resource_id == 'custom_costs') continue;

                $group = new WPML_Editor_UI_Field_Group( $group_title );
                $group_title = '';

                $resource_field = new WPML_Editor_UI_Single_Line_Field( 'bookings-resource_'.$resource_id.'_title', __( 'Title', 'woocommerce-multilingual' ), $data, true );
                $group->add_field( $resource_field );
                $bookings_section->add_field( $group );
            }

        }

        $original_persons = $this->get_original_persons( $product_id );
        end( $original_persons );
        $last_key = key( $original_persons );
        $divider = true;
        $group_title = __( 'Person Types', 'woocommerce-multilingual' );
        foreach( $original_persons as $person_id ){
            if( $person_id == $last_key ){
                $divider = false;
            }
            $group = new WPML_Editor_UI_Field_Group( $group_title , $divider );
            $group_title = '';

            $person_field = new WPML_Editor_UI_Single_Line_Field( 'bookings-person_'.$person_id.'_title', __( 'Person Type Name', 'woocommerce-multilingual' ), $data, false );
            $group->add_field( $person_field );
            $person_field = new WPML_Editor_UI_Single_Line_Field( 'bookings-person_'.$person_id.'_description', __( 'Description', 'woocommerce-multilingual' ), $data, false );
            $group->add_field( $person_field );
            $bookings_section->add_field( $group );

        }

        if( $orig_resources ||  $original_persons ){
            $obj->add_field( $bookings_section );
        }

    }


    function custom_box_html_data( $data, $product_id, $translation, $lang ){

        if( wc_get_product($product_id)->product_type != 'booking' ){
            return $data;
        }

        if( get_post_meta( $product_id,'_wc_booking_has_resources',true) == 'yes' ){

            $data[ '_wc_booking_resouce_label' ] = array( 'original' => get_post_meta( $product_id,'_wc_booking_resouce_label',true) );
            $data[ '_wc_booking_resouce_label' ][ 'translation' ] = $translation ? get_post_meta( $translation->ID,'_wc_booking_resouce_label',true) : '';
        }

        $orig_resources = $this->get_original_resources( $product_id );

        if( $orig_resources ){

            foreach ( $orig_resources as $resource_id => $cost) {

                if ($resource_id == 'custom_costs') continue;
                $data[ 'bookings-resource_'.$resource_id.'_title' ] = array( 'original' => get_the_title( $resource_id ) );

                $trns_resource_id = apply_filters('translate_object_id', $resource_id, 'bookable_resource', false, $lang);
                $data[ 'bookings-resource_'.$resource_id.'_title' ][ 'translation' ] = $trns_resource_id ? get_the_title( $trns_resource_id ) : '';
            }
        }

        $original_persons = $this->get_original_persons( $product_id );

        foreach( $original_persons as $person_id ){

            $data[ 'bookings-person_'.$person_id.'_title' ] = array( 'original' => get_the_title( $person_id ) );
            $data[ 'bookings-person_'.$person_id.'_description' ] = array( 'original' => get_post( $person_id )->post_excerpt );

            $trnsl_person_id = apply_filters( 'translate_object_id', $person_id, 'bookable_person', false, $lang );
            $data[ 'bookings-person_'.$person_id.'_title' ][ 'translation' ] = $trnsl_person_id ? get_the_title( $trnsl_person_id ) : '';
            $data[ 'bookings-person_'.$person_id.'_description' ][ 'translation' ] = $trnsl_person_id ? get_post( $trnsl_person_id )->post_excerpt : '';

        }

        return $data;
    }


    function get_original_resources( $product_id ){
        $orig_resources = maybe_unserialize( get_post_meta( $product_id, '_resource_base_costs', true ) );
        return $orig_resources;
    }

    function get_original_persons( $product_id ){
        global $wpdb;
        $original_persons = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'bookable_person' AND post_status = 'publish'", $product_id ) );
        return $original_persons;
    }

    function show_custom_blocks_for_resources_and_persons( $check, $product_id, $product_content ){
        if( in_array( $product_content, array( 'wc_booking_resources', 'wc_booking_persons' ) ) ){
            return false;
        }
        return $check;
    }

    function remove_custom_fields_to_translate( $exception, $product_id, $meta_key ){
        if( in_array( $meta_key, array( '_resource_base_costs', '_resource_block_costs' ) ) ){
            $exception = true;
        }
        return $exception;
    }

    function remove_single_custom_fields_to_translate( $fields ){
        $fields[] = '_wc_booking_resouce_label';

        return $fields;
    }

    function product_content_resource_label( $meta_key, $product_id ){
        if ($meta_key == '_wc_booking_resouce_label'){
            return __( 'Resources label', 'woocommerce-multilingual' );
        }
        return $meta_key;
    }

    function wcml_products_tab_sync_resources_and_persons( $original_product_id, $tr_product_id, $data, $language ){
        global $wpdb, $woocommerce_wpml, $wpml_post_translations;

        remove_action ( 'save_post', array( $wpml_post_translations, 'save_post_actions' ), 100, 2 );

        $orig_resources = $orig_resources = $this->get_original_resources( $original_product_id );;

        if( $orig_resources ){

            foreach( $orig_resources as $orig_resource_id => $cost ){

                $resource_id = apply_filters( 'translate_object_id', $orig_resource_id, 'bookable_resource', false, $language );
                $orig_resource = $wpdb->get_row( $wpdb->prepare( "SELECT resource_id, sort_order FROM {$wpdb->prefix}wc_booking_relationships WHERE resource_id = %d AND product_id = %d", $orig_resource_id, $original_product_id ), OBJECT );

                if( is_null( $resource_id ) ){

                    if( $orig_resource ) {
                        $resource_id = $this->duplicate_resource( $tr_product_id, $orig_resource, $language);
                    }else{
                        continue;
                    }

                }else{
                    //update_relationship

                    $exist = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}wc_booking_relationships WHERE resource_id = %d AND product_id = %d", $resource_id, $tr_product_id ) );

                    if( !$exist ){

                        $wpdb->insert(
                            $wpdb->prefix . 'wc_booking_relationships',
                            array(
                                'product_id' => $tr_product_id,
                                'resource_id' => $resource_id,
                                'sort_order' => $orig_resource->sort_order
                            )
                        );

                    }

                }



                $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_title' => $data[ md5( 'bookings-resource_'.$orig_resource_id.'_title')  ]
                    ),
                    array(
                        'ID' => $resource_id
                    )
                );

                update_post_meta( $resource_id, 'wcml_is_translated', true );

            }

            //sync resources data
            $this->sync_resources( $original_product_id, $tr_product_id, $language, false );

        }

        $original_persons = $this->get_original_persons( $original_product_id );

        //sync persons
        if( $original_persons ){

            foreach( $original_persons as $original_person_id ){

                $person_id = apply_filters( 'translate_object_id', $original_person_id, 'bookable_person', false, $language );

                if( is_null( $person_id ) ){

                    $person_id = $this->duplicate_person( $tr_product_id, $original_person_id, $language);

                }else{

                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'post_parent' => $tr_product_id
                        ),
                        array(
                            'ID' => $person_id
                        )
                    );

                }

                $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_title' => $data[ md5 ( 'bookings-person_'.$original_person_id.'_title' ) ],
                        'post_excerpt' => $data[ md5( 'bookings-person_'.$original_person_id.'_description' ) ],
                    ),
                    array(
                        'ID' => $person_id
                    )
                );

                update_post_meta( $person_id, 'wcml_is_translated', true );

            }

            //sync persons data
            $this->sync_persons(  $original_product_id, $tr_product_id, $language, false );

        }

        add_action ( 'save_post', array( $wpml_post_translations, 'save_post_actions' ), 100, 2 );

    }

    function duplicate_booking_for_translations( $booking_id, $lang = false ){
        global $sitepress;

        $booking_object = get_post( $booking_id );

        $booking_data = array(
            'post_type'   => 'wc_booking',
            'post_title'  => $booking_object->post_title,
            'post_status' => $booking_object->post_status,
            'ping_status' => 'closed',
            'post_parent' => $booking_object->post_parent,
        );

        $active_languages = $sitepress->get_active_languages();

        foreach( $active_languages as $language ){

            $booking_product_id = get_post_meta( $booking_id, '_booking_product_id', true );

            if( !$lang ){
                $booking_language = $sitepress->get_element_language_details( $booking_product_id, 'post_product' );
                if ( $booking_language->language_code == $language['code'] ) {
                    continue;
                }
            }elseif( $lang != $language['code'] ){
                continue;
            }

            $booking_persons = maybe_unserialize( get_post_meta( $booking_id, '_booking_persons', true ) );
            $trnsl_booking_persons = array();

            foreach( $booking_persons as $person_id => $person_count ){

                $trnsl_person_id = apply_filters( 'translate_object_id', $person_id, 'bookable_person', false, $language['code']  );

                if( is_null( $trnsl_person_id ) ){
                    $trnsl_booking_persons[] = $person_count;
                }else{
                    $trnsl_booking_persons[ $trnsl_person_id ] = $person_count;
                }

            }

            $trnsl_booking_id = wp_insert_post( $booking_data );

            $meta_args = array(
                '_booking_order_item_id' => get_post_meta( $booking_id, '_booking_order_item_id', true ),
                '_booking_product_id'    => $this->get_translated_booking_product_id( $booking_id, $language['code'] ),
                '_booking_resource_id'   => $this->get_translated_booking_resource_id( $booking_id, $language['code'] ),
                '_booking_persons'       => $this->get_translated_booking_persons_ids( $booking_id, $language['code'] ),
                '_booking_cost'          => get_post_meta( $booking_id, '_booking_cost', true ),
                '_booking_start'         => get_post_meta( $booking_id, '_booking_start', true ),
                '_booking_end'           => get_post_meta( $booking_id, '_booking_end', true ),
                '_booking_all_day'       => intval( get_post_meta( $booking_id, '_booking_all_day', true ) ),
                '_booking_parent_id'     => get_post_meta( $booking_id, '_booking_parent_id', true ),
                '_booking_customer_id'   => get_post_meta( $booking_id, '_booking_customer_id', true ),
                '_booking_duplicate_of'   => $booking_id,
                '_language_code'   => $language['code'],
            );

            foreach ( $meta_args as $key => $value ) {
                update_post_meta( $trnsl_booking_id, $key, $value );
            }

            WC_Cache_Helper::get_transient_version( 'bookings', true );

        }


    }

    function get_translated_booking_product_id( $booking_id, $language ){

        $booking_product_id = get_post_meta( $booking_id, '_booking_product_id', true );

        if( $booking_product_id ){
            $trnsl_booking_product_id = apply_filters( 'translate_object_id', $booking_product_id, 'product', false, $language );
            if( is_null( $trnsl_booking_product_id ) ){
                $trnsl_booking_product_id = '';
            }
        }

        return $trnsl_booking_product_id;

    }

    function get_translated_booking_resource_id( $booking_id, $language ){

        $booking_resource_id = get_post_meta( $booking_id, '_booking_resource_id', true );
        $trnsl_booking_resource_id = '';

        if( $booking_resource_id ){
            $trnsl_booking_resource_id = apply_filters( 'translate_object_id', $booking_resource_id, 'bookable_resource', false, $language );

            if( is_null( $trnsl_booking_resource_id ) ){
                $trnsl_booking_resource_id = '';
            }
        }

        return $trnsl_booking_resource_id;
    }

    function get_translated_booking_persons_ids( $booking_id, $language ){

        $booking_persons = maybe_unserialize( get_post_meta( $booking_id, '_booking_persons', true ) );
        $trnsl_booking_persons = array();

        foreach( $booking_persons as $person_id => $person_count ){

            $trnsl_person_id = apply_filters( 'translate_object_id', $person_id, 'bookable_person', false, $language  );

            if( is_null( $trnsl_person_id ) ){
                $trnsl_booking_persons[] = $person_count;
            }else{
                $trnsl_booking_persons[ $trnsl_person_id ] = $person_count;
            }

        }

        return $trnsl_booking_persons;

    }

    function update_status_for_translations( $booking_id ){
        global $wpdb;

        $translated_bookings = $this->get_translated_bookings( $booking_id );

        foreach( $translated_bookings as $booking ){

            $status = $wpdb->get_var( $wpdb->prepare( "SELECT post_status FROM {$wpdb->posts} WHERE ID = %d", $booking_id ) ); //get_post_status( $booking_id );
            $language = get_post_meta( $booking->post_id, '_language_code', true );

            $wpdb->update(
                $wpdb->posts,
                array(
                    'post_status' => $status,
                    'post_parent' => wp_get_post_parent_id( $booking_id ),
                ),
                array(
                    'ID' => $booking->post_id
                )
            );

            update_post_meta( $booking->post_id, '_booking_product_id', $this->get_translated_booking_product_id( $booking_id, $language ) );
            update_post_meta( $booking->post_id, '_booking_resource_id', $this->get_translated_booking_resource_id( $booking_id, $language ) );
            update_post_meta( $booking->post_id, '_booking_persons', $this->get_translated_booking_persons_ids( $booking_id, $language ) );

        }

    }

    function get_translated_bookings($booking_id){
        global $wpdb;

        $translated_bookings = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_booking_duplicate_of' AND meta_value = %d", $booking_id ) );

        return $translated_bookings;
    }

    public function booking_filters_query( $query ) {
        global $typenow, $sitepress, $wpdb;

        if ( ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == 'wc_booking' ) ) {

            $product_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT element_id
					FROM {$wpdb->prefix}icl_translations
					WHERE language_code = %s AND element_type = 'post_product'", $sitepress->get_current_language() ) );

            $query->query_vars[ 'meta_query' ][] = array(
                array(
                    'key'   => '_booking_product_id',
                    'value' => $product_ids,
                    'compare ' => 'IN'
                )
            );
        }
    }

    function bookings_in_date_range_query($booking_ids){
        global $sitepress;

        foreach ( $booking_ids as $key => $booking_id ) {

            $language_code = $sitepress->get_language_for_element( get_post_meta( $booking_id, '_booking_product_id', true ) , 'post_product' );
            $current_language = $sitepress->get_current_language();

            if( $language_code != $current_language ){
                unset( $booking_ids[$key] );
            }

        }

        return $booking_ids;

    }

    function clear_transient_fields(){

        if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'wc_booking' && isset( $_GET['page'] ) && $_GET['page'] == 'booking_calendar' ) {

            global $wpdb;
            //delete transient fields
            $wpdb->query("
                DELETE FROM $wpdb->options
		        WHERE option_name LIKE '%book_dr_%'
		    ");

        }

    }

    function delete_bookings( $booking_id ){

        if( $booking_id > 0 && get_post_type( $booking_id ) == 'wc_booking' ){

            $translated_bookings = $this->get_translated_bookings( $booking_id );

            remove_action( 'before_delete_post', array( $this, 'delete_bookings' ) );

            foreach( $translated_bookings as $booking ){

                global $wpdb;

                $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_parent' => 0
                    ),
                    array(
                        'ID' => $booking->post_id
                    )
                );

                wp_delete_post( $booking->post_id );

            }

            add_action( 'before_delete_post', array( $this, 'delete_bookings' ) );
        }

    }

    function trash_bookings( $booking_id ){

        if( $booking_id > 0 && get_post_type( $booking_id ) == 'wc_booking' ){

            $translated_bookings = $this->get_translated_bookings( $booking_id );

            foreach( $translated_bookings as $booking ){
                global $wpdb;

                $wpdb->update(
                    $wpdb->posts,
                    array(
                        'post_status' => 'trash'
                    ),
                    array(
                        'ID' => $booking->post_id
                    )
                );

            }

        }

    }

    function append_persons_to_translation_package( $package, $post ){

        if( $post->post_type == 'product' ){
            $product = wc_get_product( $post->ID );

            //WC_Product::get_type() available from WooCommerce 2.4.0
            $product_type = method_exists($product, 'get_type') ? $product->get_type() : $product->product_type;

            if( $product_type == 'booking' ){

                $bookable_product = new WC_Product_Booking( $post->ID );

                $person_types = $bookable_product->get_person_types();

                foreach( $person_types as $person_type ) {

                    $package['contents']['wc_bookings:person:' . $person_type->ID . ':name'] = array(
                        'translate' => 1,
                        'data' => $this->tp->encode_field_data( $person_type->post_title, 'base64' ),
                        'format'    => 'base64'
                    );

                    $package['contents']['wc_bookings:person:' . $person_type->ID . ':description'] = array(
                        'translate' => 1,
                        'data' => $this->tp->encode_field_data( $person_type->post_excerpt, 'base64' ),
                        'format'    => 'base64'
                    );

                }

            }

        }

        return $package;

    }

    function save_person_translation($post_id, $data, $job ){
        global $sitepress;

        $person_translations = array();

        foreach($data as $value){

            if( $value['finished'] && strpos( $value['field_type'], 'wc_bookings:person:' ) === 0 ) {

                $exp = explode( ':', $value['field_type'] );

                $person_id  = $exp[2];
                $field      = $exp[3];

                $person_translations[$person_id][$field] = $value['data'];

            }

        }

        if( $person_translations ){

            foreach( $person_translations as $person_id => $pt ){

                $person_trid = $sitepress->get_element_trid( $person_id, 'post_bookable_person');


                $person_id_translated = apply_filters( 'translate_object_id', $person_id, 'bookable_person', false, $job->language_code );

                if( empty($person_id_translated) ) {

                    $person_post = array(

                        'post_type' => 'bookable_person',
                        'post_status' => 'publish',
                        'post_title' => $pt['name'],
                        'post_parent' => $post_id,
                        'post_excerpt' => isset($pt['description']) ? $pt['description'] : ''

                    );

                    $person_id_translated = wp_insert_post( $person_post );

                    $sitepress->set_element_language_details( $person_id_translated, 'post_bookable_person', $person_trid, $job->language_code );

                } else {

                    $person_post = array(
                        'ID'            => $person_id_translated,
                        'post_title'    => $pt['name'],
                        'post_excerpt'  => isset($pt['description']) ? $pt['description'] : ''
                    );

                    wp_update_post( $person_post );

                }

            }

        }

    }

    function append_resources_to_translation_package( $package, $post ){

        if( $post->post_type == 'product' ){
            $product = wc_get_product( $post->ID );

            //WC_Product::get_type() available from WooCommerce 2.4.0
            $product_type = method_exists($product, 'get_type') ? $product->get_type() : $product->product_type;

            if( $product_type == 'booking' && $product->has_resources() ){

                $resources = $product->get_resources();

                foreach( $resources as $resource ) {

                    $package['contents']['wc_bookings:resource:' . $resource->ID . ':name'] = array(
                        'translate' => 1,
                        'data' => $this->tp->encode_field_data( $resource->post_title, 'base64' ),
                        'format'    => 'base64'
                    );

                }

            }

        }

        return $package;

    }

    function save_resource_translation( $post_id, $data, $job ){
        global $sitepress, $wpdb;

        $resource_translations = array();

        foreach($data as $value){

            if( $value['finished'] && strpos( $value['field_type'], 'wc_bookings:resource:' ) === 0 ) {

                $exp = explode( ':', $value['field_type'] );

                $resource_id  = $exp[2];
                $field        = $exp[3];

                $resource_translations[$resource_id][$field] = $value['data'];

            }

        }

        if( $resource_translations ){

            foreach( $resource_translations as $resource_id => $rt ){

                $resource_trid = $sitepress->get_element_trid( $resource_id, 'post_bookable_resource');

                $resource_id_translated = apply_filters( 'translate_object_id', $resource_id, 'bookable_resource', false, $job->language_code );

                if( empty($resource_id_translated) ) {

                    $resource_post = array(

                        'post_type' => 'bookable_resource',
                        'post_status' => 'publish',
                        'post_title' => $rt['name'],
                        'post_parent' => $post_id
                    );

                    $resource_id_translated = wp_insert_post( $resource_post );

                    $sitepress->set_element_language_details( $resource_id_translated, 'post_bookable_resource', $resource_trid, $job->language_code );

                    $sort_order = $wpdb->get_var( $wpdb->prepare( "SELECT sort_order FROM {$wpdb->prefix}wc_booking_relationships WHERE resource_id=%d", $resource_id ) );
                    $relationship = array(
                        'product_id'    => $post_id,
                        'resource_id'   => $resource_id_translated,
                        'sort_order'    => $sort_order
                    );
                    $wpdb->insert( $wpdb->prefix . 'wc_booking_relationships',  $relationship);

                } else {

                    $resource_post = array(
                        'ID'            => $resource_id_translated,
                        'post_title'    => $rt['name']
                    );

                    wp_update_post( $resource_post );

                    $sort_order = $wpdb->get_var( $wpdb->prepare( "SELECT sort_order FROM {$wpdb->prefix}wc_booking_relationships WHERE resource_id=%d", $resource_id ) );
                    $wpdb->update( $wpdb->prefix . 'wc_booking_relationships', array( 'sort_order' => $sort_order ),
                        array ( 'product_id' => $post_id, 'resource_id' => $resource_id_translated) );


                }


            }

        }

    }

    function wcml_js_lock_fields_ids( $ids ){

        $ids = array_merge( $ids, array(
            '_wc_booking_has_resources',
            '_wc_booking_has_persons',
            '_wc_booking_duration_type',
            '_wc_booking_duration',
            '_wc_booking_duration_unit',
            '_wc_booking_calendar_display_mode',
            '_wc_booking_requires_confirmation',
            '_wc_booking_user_can_cancel',
            '_wc_accommodation_booking_min_duration',
            '_wc_accommodation_booking_max_duration',
            '_wc_accommodation_booking_max_duration',
            '_wc_accommodation_booking_calendar_display_mode',
            '_wc_accommodation_booking_requires_confirmation',
            '_wc_accommodation_booking_user_can_cancel',
            '_wc_accommodation_booking_cancel_limit',
            '_wc_accommodation_booking_cancel_limit_unit',
            '_wc_accommodation_booking_qty',
            '_wc_accommodation_booking_min_date',
            '_wc_accommodation_booking_min_date_unit',
            '_wc_accommodation_booking_max_date',
            '_wc_accommodation_booking_max_date_unit',
            'bookings_pricing select',
            'bookings_resources select',
            'bookings_availability select',
            'bookings_persons input[type="checkbox"]'
        ) );

        return $ids;
    }

}