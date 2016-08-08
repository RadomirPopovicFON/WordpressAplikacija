<?php
if ( !class_exists('UpiCRMAdminEmailNotifications') ):
    class UpiCRMAdminEmailNotifications{
        public function Render() {

            switch ($_GET['action']) {
                case 'save_field':
                    $this->saveField();
                    $msg = __('changes saved successfully','upicrm');
                break;
                case 'save_settings':
                    $this->saveSettings();
                    $msg = __('changes saved successfully','upicrm');
                break;
            
            }
            
            $UpiCRMMails = new UpiCRMMails();
            $getMails = $UpiCRMMails->get();

            ?>
                <?php
                if (isset($msg)) {
                ?>
                    <div class="updated">
                        <p><?php echo $msg; ?></p>
                    </div>
                <?php
                }
                ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        <h2><?php _e('Lead Management','upicrm'); ?></h2>
                        <form method="post" action="admin.php?page=upicrm_email_notifications&action=save_settings">
                            <?php _e('Send all leads and updates to the following user:','upicrm'); ?> 
                                        <select name="default_email">
                                            <?php 
                        $default_email = get_option('upicrm_default_email');
                        $get_users = get_users( array( 'role' => '' ) ); //Editor, Administrator
                        foreach ($get_users as $user) { 
                            if (get_the_author_meta('upicrm_user_permission', $user->ID) > 0 ) {
                                            ?>
                                            <option value="<?php echo $user->user_email; ?>" <?php selected( $default_email, $user->user_email ); ?>><?php echo $user->display_name; ?></option>
                                            <?php } 

                            }?>
                                        </select>
                            <br />
                            <?php _e('Leads are by default assigned to:','upicrm'); ?>  
                                        <select name="default_lead">
                                            <?php 
                        $default_lead = get_option('upicrm_default_lead');
                        $get_users = get_users( array( 'role' => '' ) ); //Editor, Administrator
                        foreach ($get_users as $user) { 
                            if (get_the_author_meta('upicrm_user_permission', $user->ID) > 0 ) {
                                            ?>
                                            <option value="<?php echo $user->ID; ?>" <?php selected( $default_lead, $user->ID ); ?>><?php echo $user->display_name; ?></option>
                                            <?php } 
                        }
                                            ?>
                                        </select>
                            <br />
                            <?php $email_format =  get_option('upicrm_email_format');?>
                            <?php _e('Email format:','upicrm'); ?>  
                            <select name="email_format">
                                <option value="1" <?php selected( $email_format, 1); ?>>HTML</option>
                                <option value="2" <?php selected( $email_format, 2); ?>>Text</option>
                            </select><br />
                            <?php _e('Distribute all leads and updated to additional email address (or multiple addresses separated by comma (,):','upicrm'); ?>
                            <input type="text" name="extra_email" value="<?php echo get_option('upicrm_extra_email'); ?>" /><br />
                                            <?php _e('Change default "from" field for emails sent from','upicrm'); ?> UpiCRM: <input type="text" name="sender_email" value="<?php echo get_option('upicrm_sender_email'); ?>" /><br />
                            <?php _e('Email will be sent in the following format: &lt;name&gt; no-reply@yourdomain.com','upicrm'); ?>
                            <br />

                            <?php submit_button(); ?>
                        </form>
                    </div>
                </div>

                <form method="post" action="admin.php?page=upicrm_email_notifications&action=save_field">
                    <?php foreach ($getMails as $mail) { ?>                  
                        <div class="row">
                           <h2><?php echo $mail->mail_event_name; ?></h2>
                           <div class="col-xs-12 col-sm-5 col-md-5 col-lg-6">
                               <label><?php _e('Content:','upicrm'); ?> </label><br />
                               <textarea name="<?php echo $mail->mail_event; ?>[mail_content]" rows="12" cols="50"><?php echo $mail->mail_content; ?></textarea>
                           </div>
                           <div class="col-xs-12 col-sm-5 col-md-5 col-lg-6">
                               <label><?php _e('Subject:','upicrm'); ?> </label><br />
                               <input type="text" name="<?php echo $mail->mail_event; ?>[mail_subject]" value="<?php echo $mail->mail_subject; ?>" />
                               <br /><br />
                               <label><?php _e('CC:','upicrm'); ?> </label><br />
                               <input type="text" name="<?php echo $mail->mail_event; ?>[mail_cc]" value="<?php echo $mail->mail_cc; ?>" />
                               <br /><br />
                               <strong><?php _e('Variables:','upicrm'); ?></strong> <br />
                               [lead]<br />
                               [url]<br />
                               [assigned-to]<br />
                               [lead-status]<br />
                               [lead-plaintext]<br />
                               [field-*]
                           </div>
                        </div>
                     <?php } ?>
                    <?php submit_button(); ?>
                </form>
            </div>
        <?php
        }
        
        function saveField() {
            $UpiCRMMails = new UpiCRMMails();
            $UpiCRMMails->update2($_POST);
        }
        
        function saveSettings() {
            update_option('upicrm_default_email', $_POST['default_email']);
            update_option('upicrm_extra_email', $_POST['extra_email']);
            update_option('upicrm_sender_email', $_POST['sender_email']);
            update_option('upicrm_default_lead', $_POST['default_lead']);
            update_option('upicrm_email_format', $_POST['email_format']);
        }
    }
endif;
?>