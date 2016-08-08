<?php

if ( !class_exists('UpiCRMAdmin') ):
    class UpiCRMAdmin{
        public function __construct() {   
            add_action( 'admin_menu', array( $this, 'onWpAdminMenu' ) );
        } 
        public function onWpAdminMenu() {
            wp_register_style( 'upicrm_css_bootstrap', UPICRM_URL.'resources/css/bootstrap.css', FALSE, '0.1' );
            if (is_rtl()) {
                wp_register_style( 'upicrm_css', UPICRM_URL.'resources/css/smartadmin-production-rtl.css', FALSE, '0.1' );
            } else {
                wp_register_style( 'upicrm_css', UPICRM_URL.'resources/css/smartadmin-production.css', FALSE, '0.1' );
            }
            
            wp_register_style( 'upicrm_css_smart_admin_skins', UPICRM_URL.'resources/css/smartadmin-skins.css', FALSE, '0.1' );
            wp_register_style( 'upicrm_css_font','https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css', FALSE, '0.1' ); 
            wp_register_style( 'upicrm_css_bootstrap_multiselect', UPICRM_URL.'resources/css/bootstrap-multiselect.css', FALSE, '0.1' ); 
            wp_register_style( 'upicrm_main_style', UPICRM_URL.'css/style.css', FALSE, '0.3' ); 
            //wp_register_style( 'upicrm_data_table_style', 'https://cdn.datatables.net/t/dt/dt-1.10.11/datatables.min.css', FALSE, '0.2' ); 
           
            wp_register_script('upicrm_jquery', 'https://code.jquery.com/jquery-1.12.0.min.js', array('jquery'), '2.0');

            wp_register_script('upicrm_js_sparkline',  UPICRM_URL.'resources/js/plugin/sparkline/jquery.sparkline.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_jarvis',  UPICRM_URL.'resources/js/plugin/smartwidgets/jarvis.widget.min.js', array('jquery'), '1.0');

            wp_register_script('upicrm_js_dataTable',  UPICRM_URL.'resources/js/plugin/datatables/jquery.dataTables.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_colVis',  UPICRM_URL.'resources/js/plugin/datatables/dataTables.colVis.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_tableTools',  UPICRM_URL.'resources/js/plugin/datatables/dataTables.tableTools.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_tablebootstrap',  UPICRM_URL.'resources/js/plugin/datatables/dataTables.bootstrap.min.js', array('jquery'), '1.0');
            wp_register_script('upicrm_js_responsive',  UPICRM_URL.'resources/js/plugin/datatable-responsive/datatables.responsive.min.js', array('jquery'), '1.0');

            wp_register_script('upicrm_js_app',  UPICRM_URL.'resources/js/app.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_bootstrap',  UPICRM_URL.'resources/js/bootstrap.min.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_bootstrap_multiselect',  UPICRM_URL.'resources/js/bootstrap-multiselect.js', array('jquery'), '1.1');
            wp_register_script('upicrm_js_main',  UPICRM_URL.'js/main.js', array('jquery'), '1.1');
            
            
            if (substr($_GET['page'],0,6) == "upicrm") {
                wp_enqueue_style( 'upicrm_css_bootstrap' );
                wp_enqueue_style( 'upicrm_css' );
                wp_enqueue_style( 'upicrm_css_smart_admin_skins' );
                wp_enqueue_style( 'upicrm_css_font' ); 
                wp_enqueue_style( 'upicrm_css_bootstrap_multiselect' );
                wp_enqueue_style( 'upicrm_main_style' );
                //wp_enqueue_style( 'upicrm_data_table_style' );

                wp_enqueue_script('upicrm_jquery');
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('jquery-ui-widget');
                wp_enqueue_script('jquery-ui-tabs');
                wp_enqueue_script('upicrm_js_sparkline'); 
                wp_enqueue_script('upicrm_js_app');
                wp_enqueue_script('upicrm_js_bootstrap');
                wp_enqueue_script('upicrm_js_bootstrap_multiselect');
                wp_enqueue_script('upicrm_js_jarvis');  
                wp_enqueue_script('upicrm_js_dataTable'); 
                wp_enqueue_script('upicrm_js_colVis');
                wp_enqueue_script('upicrm_js_tableTools'); 
                wp_enqueue_script('upicrm_js_tablebootstrap');
                wp_enqueue_script('upicrm_js_dataTable_cdn');
                wp_enqueue_script('upicrm_js_responsive');
         
                wp_enqueue_script('upicrm_js_main');
                //ICL_AdminNotifier::add_script();
                //wp_deregister_script('icl-admin-notifier');
                

            }
            
            $UpiCRMUsers = new UpiCRMUsers();
            if (1 <= $UpiCRMUsers->get_permission()) {
                add_object_page('UpiCRM', 'UpiCRM', 'read', 'upicrm_index', array( $this, 'onDisplayDashboard' ), UPICRM_URL . 'resources/images/icon_crm.gif');
                
                add_submenu_page( 'upicrm_index', __('Lead Management','upicrm'), __('Lead Management','upicrm'), 'read', 'upicrm_allitems', array( $this, 'onDisplayMainMenu' ) );
                add_submenu_page( 'upicrm_dont_show', '', '', 'read', 'upicrm_edit_lead', array( $this, 'onDisplayAdminEditLead' ) );
                add_submenu_page( 'upicrm_dont_show', '', '', 'read', 'upicrm_api', array( $this, 'onDisplayAdminAPI' ) );
    
            }
            if (1 < $UpiCRMUsers->get_permission()) {
                add_submenu_page( 'upicrm_index', __('Data Types & Fields','upicrm'), __('Data Types & Fields','upicrm'), 'read', 'upicrm_settings', array( $this, 'onDisplayCommonSettings' ) );
                //add_submenu_page( 'upicrm_index', __('Existing Fields','upicrm'), __('Existing Fields','upicrm'), 'read', 'upicrm_existing_fields', array( $this, 'onDisplayExistingFields' ) );
                //add_submenu_page( 'upicrm_index', __('Existing Statuses','upicrm'), __('Existing Statuses','upicrm'), 'read', 'upicrm_existing_statuses', array( $this, 'onDisplayExistingStatuses' ) );
                add_submenu_page( 'upicrm_index', __('Email Notifications','upicrm'), __('Email Notifications','upicrm'), 'read', 'upicrm_email_notifications', array( $this, 'onDisplayEmailNotifications' ) );
                add_submenu_page( 'upicrm_index', __('Auto Lead Management','upicrm'), __('Auto Lead Management','upicrm'), 'read', 'upicrm_lead_route', array( $this, 'onDisplayLeadRouting' ) );
                add_submenu_page( 'upicrm_index', __('Lead Aggregation','upicrm'), __('Lead Aggregation','upicrm'), 'read', 'upicrm_integrations', array( $this, 'onDisplayIntegrations' ) );
                add_submenu_page( 'upicrm_index', __('Import & Export','upicrm'), __('Import & Export','upicrm'), 'read', 'upicrm_import_export', array( $this, 'onDisplayImportExport' ) );
                add_submenu_page( 'upicrm_index', __('Users Center','upicrm'), __('Users Center','upicrm'), 'read', 'upicrm_users_center', array( $this, 'onDisplayUser' ) );
            }
            
        }
                private function beforeAllAdminPages() {
            global $wp_version;
            if ($wp_version >= 5) {
                ?>
                <div class="alert alert-warning fade in">
                    <i class="fa-fw fa fa-warning"></i>
                    <strong>Warning</strong> Your UpiCRM version is not compatible with WordPress 5.X . <a href="http://www.upicrm.com/?utm_source=upicrmvf">Please upgrade your UpiCRM WordPress CRM solution here</a>.
                </div>
                <?php
            }
            
        }
        public function onDisplayDashboard(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Dashboard','upicrm');
            $upi_setting['logo'] = 'home';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminIndex = new UpiCRMAdminIndex();
            $UpiCRMAdminIndex->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        public function onDisplayCommonSettings(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Data Types & Fields','upicrm');
            $upi_setting['logo'] = 'cogs';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminSettings = new UpiCRMAdminSettings();
            $UpiCRMAdminSettings->Render(); 
            
            $UpiCRMAdminWarp->footer();
        }
	    public function onDisplayMainMenu() {	
            global $title;           
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Leads Management','upicrm');
            $upi_setting['logo'] = 'table';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminAdminLists = new UpiCRMAdminAdminLists();
            $UpiCRMAdminAdminLists->RenderLists(); 
            
            $UpiCRMAdminWarp->footer();
	    } 
        
        public function onDisplayExistingFields(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Existing Fields','upicrm');
            $upi_setting['logo'] = 'bolt';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminExistingFields = new UpiCRMAdminExistingFields();
            $UpiCRMAdminExistingFields->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayExistingStatuses(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Existing Statuses','upicrm');
            $upi_setting['logo'] = 'bolt';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminExistingStatuses = new UpiCRMAdminExistingStatuses();
            $UpiCRMAdminExistingStatuses->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayEmailNotifications(){
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Email Notifications','upicrm');
            $upi_setting['logo'] = 'envelope-o';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminEmailNotifications = new UpiCRMAdminEmailNotifications();
            $UpiCRMAdminEmailNotifications->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayAdminEditLead() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Edit Lead','upicrm');
            $upi_setting['logo'] = 'pencil-square-o';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminEditLead = new UpiCRMAdminEditLead();
            $UpiCRMAdminEditLead->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayAdminAPI() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['logo'] = 'magic';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminAPI = new UpiCRMAdminAPI();
            $UpiCRMAdminAPI->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayLeadRouting() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Auto Lead Management','upicrm');
            $upi_setting['logo'] = 'cogs';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminLeadRoute = new UpiCRMAdminLeadRoute();
            $UpiCRMAdminLeadRoute->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayIntegrations() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Lead Aggregation','upicrm');
            $upi_setting['logo'] = 'exchange';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminIntegrations = new UpiCRMAdminIntegrations();
            $UpiCRMAdminIntegrations->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayUser() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Users Center','upicrm');
            $upi_setting['logo'] = 'user';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminUsers = new UpiCRMAdminUsers();
            $UpiCRMAdminUsers->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        public function onDisplayImportExport() {
            $UpiCRMAdminWarp = new UpiCRMAdminWarp();
            $upi_setting['title'] =  __('Import & Export','upicrm');
            $upi_setting['logo'] = 'sign-in';
            $UpiCRMAdminWarp->header($upi_setting);
            
            $UpiCRMAdminUsers = new UpiCRMAdminImportExport();
            $UpiCRMAdminUsers->Render();
            
            $UpiCRMAdminWarp->footer();
        }
        
        
        
    }
endif;