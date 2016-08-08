<?php
function upicrm_setup_plugin() {
    global $wpdb;
    $charset_collate = '';

    if ( ! empty( $wpdb->charset ) ) {
      $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }

    if ( ! empty( $wpdb->collate ) ) {
      $charset_collate .= " COLLATE {$wpdb->collate}";
    }
    $sql = "CREATE TABLE ".upicrm_db()."leads (
            `lead_id` INT NOT NULL AUTO_INCREMENT,
            `source_type` INT NOT NULL,
            `source_id` INT NOT NULL,
            `lead_content` TEXT,
            `user_ip` TEXT,
            `user_agent` TEXT,
            `user_referer` TEXT,
            `old_user_lead_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `lead_status_id` INT NOT NULL,
            `lead_management_comment` TEXT,
            `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`lead_id`)
   ) $charset_collate;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE ".upicrm_db()."leads_campaign (
            `lead_id` INT,
            `utm_source` TEXT,
            `utm_medium` TEXT,
            `utm_term` TEXT,
            `utm_content` TEXT,
            `utm_campaign` TEXT
   ) $charset_collate;";
    $wpdb->query($sql);
    
   $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."fields_mapping (
  `fm_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL,
  `fm_name` text NOT NULL,
  `source_id` int(11) NOT NULL,
  `source_type` int(11) NOT NULL,
  PRIMARY KEY (`fm_id`)
   ) $charset_collate;";
    $wpdb->query($sql);
    
   $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."fields (
  `field_id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` text NOT NULL,
  PRIMARY KEY (`field_id`)
   ) $charset_collate;";
    $wpdb->query($sql);
    
    $sql = "INSERT INTO ".upicrm_db()."fields (`field_id`, `field_name`) VALUES
    (1, 'Name'),
    (2, 'Last name'),
    (3, 'Date'),
    (4, 'Message subject'),
    (5, 'Phone number mobile'),
    (6, 'Phone number work'),
    (7, 'Phone number home'),
    (8, 'Email'),
    (9, 'Role'),
    (10, 'Company'),
    (11, 'Industry'),
    (12, 'Website'),
    (13, 'Product'),
    (14, 'Service'),
    (15, 'City'),
    (16, 'Street'),
    (17, 'Country'),
    (18, 'Zip code'),
    (19, 'Address'),
    (20, 'Fax number'),
    (21, 'Future contact allowed'),
    (22, 'Message details/Remarks')
    ;";
    $wpdb->query($sql);
    
   $sql = "CREATE TABLE IF NOT EXISTS ".upicrm_db()."leads_status (
  `lead_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_status_name` varchar(100) NOT NULL,
  PRIMARY KEY (`lead_status_id`),
  UNIQUE (`lead_status_name`)
   ) $charset_collate;";
    $wpdb->query($sql);
    
    $sql = "INSERT INTO ".upicrm_db()."leads_status (`lead_status_id`, `lead_status_name`) VALUES
    (1, 'Received'),
    (2, 'Qualified'),
    (3, 'Assigned'),
    (4, 'In process'),
    (5, 'Quote'),
    (6, 'Closing'),
    (7, 'Revenue')
    ;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE ".upicrm_db()."mails (
            `mail_id` INT NOT NULL AUTO_INCREMENT,
            `mail_event` TEXT,
            `mail_content` TEXT,
            `mail_subject` TEXT,
            `mail_cc` TEXT,
            `mail_event_name` TEXT,
            PRIMARY KEY (`mail_id`)
   ) $charset_collate;";
    $wpdb->query($sql);

    $sql = "INSERT INTO ".upicrm_db()."mails (`mail_id`, `mail_event`, `mail_content`, `mail_subject`, `mail_cc`, `mail_event_name`) VALUES
    (1, 'new_lead','[lead]','New Lead','','New Lead'),
    (2, 'change_user','[lead]','Change User','','Change User'),
    (3, 'change_lead_status','[lead]','Change Lead Status','','Change Lead Status'),
    (4, 'request_status','[lead]','Request status update','','Request status update from lead owner')
    ;";
    $wpdb->query($sql);
    
    
    //update all admins permissions to UpiCRM Admin
     $users = get_users( array( 'role' => 'Administrator' ));
     foreach ($users as $user) {
         update_user_meta( $user->id,'upicrm_user_permission', 2);
     } 

    
    if (!get_option('upicrm_default_email')) {
        $default_email = get_option( 'admin_email' );
        add_option('upicrm_default_email', $default_email);
    } 

}

/*function upicrm_remove_plugin() {
    global $wpdb;
    $sql = "DROP TABLE ".upicrm_db()."leads";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."leads_campaign";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."fields_mapping";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."fields";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."leads_status";
    $wpdb->query($sql);
    $sql = "DROP TABLE ".upicrm_db()."mails";
    $wpdb->query($sql);
}*/

function upicrm_update_db_check() {
    global $upicrm_db_version, $wpdb;
    if (get_option("upicrm_db_version") <= 3) {
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_status` ADD UNIQUE( `lead_status_name`);";
        $wpdb->query($sql);
        
        $sql = "ALTER TABLE `".upicrm_db()."leads_status` CHANGE `lead_status_name` `lead_status_name` VARCHAR(100);";
        $wpdb->query($sql);
        
        $sql = "INSERT INTO ".upicrm_db()."leads_status (`lead_status_name`) VALUES
        ('Not relevant')
        ;";
        $wpdb->query($sql);
        
        $sql = "UPDATE ".upicrm_db()."fields SET `field_name` = 'Phone number' WHERE `field_name` = 'Phone number home';";
        $wpdb->query($sql);

    }

    if (get_option("upicrm_db_version") != $upicrm_db_version) {
        $sql = "CREATE TABLE ".upicrm_db()."leads_route (
            `lead_route_id` int(11) NOT NULL AUTO_INCREMENT,
            `field_id` int(11) NOT NULL,
            `lead_route_type` int(11) NOT NULL,
            `lead_route_value` text NOT NULL,
            `user_id` int(11) NOT NULL,
            `lead_status_id` int(11) NOT NULL,
            PRIMARY KEY (`lead_route_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
        
    $sql = "CREATE TABLE ".upicrm_db()."integrations (
            `integration_id` INT(11) NOT NULL AUTO_INCREMENT,
            `integration_domain` TEXT,
            `integration_key` TEXT,
            `integration_status` TEXT,
            `integration_is_slave` INT(1),
            PRIMARY KEY (`integration_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
        
    $sql = "ALTER TABLE `".upicrm_db()."integrations` ADD `integration_clean_domain` TEXT NOT NULL AFTER `integration_domain`; $charset_collate;";
    $wpdb->query($sql);
    
    $sql = "CREATE TABLE ".upicrm_db()."leads_integration (
            `lead_integration_id` INT(11) NOT NULL AUTO_INCREMENT,
            `lead_id` INT NOT NULL,
            `lead_id_external` INT NOT NULL,
            `integration_id` INT NOT NULL,
            `lead_integration_status` TEXT,
            `integration_is_slave` INT(1) NOT NULL,
            `lead_integration_error` INT(1) NOT NULL,
            PRIMARY KEY (`lead_integration_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
         
        $sql = "CREATE TABLE ".upicrm_db()."users (
            `inside_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT,
            `user_parent_id` INT,
            `user_label` TEXT,
            `user_permission` INT,
            PRIMARY KEY (`inside_id`)
       ) $charset_collate;";
        $wpdb->query($sql);
        
        
        $sql = "ALTER TABLE `".upicrm_db()."leads` CHANGE `source_id` `source_id` TEXT NOT NULL; $charset_collate;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."fields_mapping` CHANGE `source_id` `source_id` TEXT NOT NULL; $charset_collate;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `change_field_id` INT NOT NULL AFTER `lead_status_id`, ADD `change_field_value` TEXT NOT NULL AFTER `change_field_id`; $charset_collate;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` CHANGE `field_id` `field_id` TEXT NOT NULL; $charset_collate;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` CHANGE `field_id` `field_id` TEXT NOT NULL; $charset_collate;";
        $wpdb->query($sql);

        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_option` VARCHAR(30) NOT NULL DEFAULT 'content' AFTER `lead_route_id`;$charset_collate;";
        $wpdb->query($sql);


        $sql = "ALTER TABLE `".upicrm_db()."leads_route` ADD `lead_route_and` BOOLEAN NOT NULL AFTER `lead_route_value`, ADD `lead_route_option2` VARCHAR(30) NOT NULL DEFAULT 'content' AFTER `lead_route_and`, ADD `field_id2` TEXT NOT NULL AFTER `lead_route_option2`, ADD `lead_route_type2` INT NOT NULL AFTER `field_id2`, ADD `lead_route_value2` TEXT NOT NULL AFTER `lead_route_type2`;";
       $wpdb->query($sql);
        
        update_option( "upicrm_db_version", $upicrm_db_version );

    }
    
    if (!get_option('upicrm_sender_email')) {
        add_option('upicrm_sender_email', 'no-reply');
    } 
    
    if (!get_option('upicrm_default_lead')) {
       $users = get_users( array( 'role' => 'Administrator' ));
        add_option('upicrm_default_lead', $users[0]->ID);
    } 
    if (!get_option('upicrm_email_format')) {
        add_option('upicrm_email_format', 1);
    } 
    if (!get_option('insert_lead_gen')) {
        add_option('insert_lead_gen', 1);
        $sql = "INSERT INTO ".upicrm_db()."fields (`field_name`) VALUES ('Received From');";
        $wpdb->query($sql); 
    }
    
    
   

    

    
}
?>