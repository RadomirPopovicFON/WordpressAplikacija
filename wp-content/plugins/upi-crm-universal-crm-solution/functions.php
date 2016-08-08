<?php
function upicrm_get_referer() {
	$ref = '';
	if ( ! empty( $_REQUEST['_wp_http_referer'] ) )
		$ref = $_REQUEST['_wp_http_referer'];
	else if ( ! empty( $_SERVER['HTTP_REFERER'] ) )
		$ref = $_SERVER['HTTP_REFERER'];

	if ( $ref !== $_SERVER['REQUEST_URI'] )
		return $ref;
	return false;
}

function upicrm_get_user_lead_id() {
    return isset($_COOKIE['old_lead_id']) ? $_COOKIE['old_lead_id'] : 0;
}

function upicrm_set_new_user($id) {
    @setcookie("old_lead_id", $id);
}

function upicrm_load($load) {
    switch ($load) {
        case 'excel':
            $path = 'resources/includes/PHPExcel.php';
        break;
    }
    require_once( UPICRM_PATH . $path ); 
}

function upicrm_string_cleaner($str) {
    $str = strtolower($str);
    $str = trim($str);
    return $str;
}

function upicrm_parse_url($url) {
    $url_arr = parse_url($url);
    return isset($url_arr['host']) ? preg_replace('#^(http(s)?://)?w{3}\.#', '$1', $url_arr['host']) : false;
}


if(!function_exists('get_user_by')) {
    function get_user_by( $field, $value ) {
        $userdata = WP_User::get_data_by( $field, $value );

        if ( !$userdata )
            return false;

        $user = new WP_User;
        $user->init( $userdata );

        return $user;
    }
}

 function upicrm_excel_output() {
        upicrm_load('excel');
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMUsers = new UpiCRMUsers();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $objPHPExcel = new PHPExcel();
        
        $list_option = $UpiCRMUIBuilder->get_list_option();
        if ($UpiCRMUsers->get_permission() == 1) {
            $userID = get_current_user_id();
            $getLeads = $UpiCRMLeads->get($userID);
        }
        if ($UpiCRMUsers->get_permission() == 2) {
            $getLeads = $UpiCRMLeads->get();
        }
        $getNamesMap = $UpiCRMFieldsMapping->get(); 
        $fileName = '/leads.xlsx';
        $dirName = WP_CONTENT_DIR."/uploads/upicrm"; 
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $t="A";
        foreach ($list_option as $key => $arr) { 
            foreach ($arr as $key2 => $value) { 
                $objPHPExcel->getActiveSheet()->getStyle($t.'1')->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->setCellValue($t.'1', $value);
                $objPHPExcel->getActiveSheet()->getColumnDimension($t)->setWidth(25);
                $t++;
            }
        } 
        
        $i=2;
        foreach ($getLeads as $leadObj) {
            $t="A";
            foreach ($list_option as $key => $arr) { 
                foreach ($arr as $key2 => $value) {
                    $getValue = $UpiCRMUIBuilder->lead_routing($leadObj,$key,$key2,$getNamesMap,true);
                    $objPHPExcel->getActiveSheet()->setCellValue($t.$i, $getValue);
                    $t++;
                }
            } 
            $i++;
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($dirName.$fileName);

        echo '<script>window.onload = function (event) { window.location="'.home_url().'/wp-content/uploads/upicrm/leads.xlsx"; };</script>';
}

if(!function_exists('get_the_author_meta')) {
function get_the_author_meta( $field = '', $user_id = false ) {
    $original_user_id = $user_id;
 
    if ( ! $user_id ) {
        global $authordata;
        $user_id = isset( $authordata->ID ) ? $authordata->ID : 0;
    } else {
        $authordata = get_userdata( $user_id );
    }
 
    if ( in_array( $field, array( 'login', 'pass', 'nicename', 'email', 'url', 'registered', 'activation_key', 'status' ) ) )
        $field = 'user_' . $field;
 
    $value = isset( $authordata->$field ) ? $authordata->$field : '';
 
    /**
     * Filter the value of the requested user metadata.
     *
     * The filter name is dynamic and depends on the $field parameter of the function.
     *
     * @since 2.8.0
     * @since 4.3.0 The `$original_user_id` parameter was added.
     *
     * @param string   $value            The value of the metadata.
     * @param int      $user_id          The user ID for the value.
     * @param int|bool $original_user_id The original user ID, as passed to the function.
     */
    return apply_filters( 'get_the_author_' . $field, $value, $user_id, $original_user_id );
}
}

if ( !function_exists('get_userdata') ) :
function get_userdata( $user_id ) {
    return get_user_by( 'id', $user_id );
}
endif;


if ( !function_exists('wp_mail') ) :
function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
    // Compact the input, apply the filters, and extract them back out
 
    /**
     * Filter the wp_mail() arguments.
     *
     * @since 2.2.0
     *
     * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
     *                    subject, message, headers, and attachments values.
     */
    $atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
 
    if ( isset( $atts['to'] ) ) {
        $to = $atts['to'];
    }
 
    if ( isset( $atts['subject'] ) ) {
        $subject = $atts['subject'];
    }
 
    if ( isset( $atts['message'] ) ) {
        $message = $atts['message'];
    }
 
    if ( isset( $atts['headers'] ) ) {
        $headers = $atts['headers'];
    }
 
    if ( isset( $atts['attachments'] ) ) {
        $attachments = $atts['attachments'];
    }
 
    if ( ! is_array( $attachments ) ) {
        $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
    }
    global $phpmailer;
 
    // (Re)create it, if it's gone missing
    if ( ! ( $phpmailer instanceof PHPMailer ) ) {
        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';
        $phpmailer = new PHPMailer( true );
    }
 
    // Headers
    if ( empty( $headers ) ) {
        $headers = array();
    } else {
        if ( !is_array( $headers ) ) {
            // Explode the headers out, so this function can take both
            // string headers and an array of headers.
            $tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
        } else {
            $tempheaders = $headers;
        }
        $headers = array();
        $cc = array();
        $bcc = array();
 
        // If it's actually got contents
        if ( !empty( $tempheaders ) ) {
            // Iterate through the raw headers
            foreach ( (array) $tempheaders as $header ) {
                if ( strpos($header, ':') === false ) {
                    if ( false !== stripos( $header, 'boundary=' ) ) {
                        $parts = preg_split('/boundary=/i', trim( $header ) );
                        $boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
                    }
                    continue;
                }
                // Explode them out
                list( $name, $content ) = explode( ':', trim( $header ), 2 );
 
                // Cleanup crew
                $name    = trim( $name    );
                $content = trim( $content );
 
                switch ( strtolower( $name ) ) {
                    // Mainly for legacy -- process a From: header if it's there
                    case 'from':
                        $bracket_pos = strpos( $content, '<' );
                        if ( $bracket_pos !== false ) {
                            // Text before the bracketed email is the "From" name.
                            if ( $bracket_pos > 0 ) {
                                $from_name = substr( $content, 0, $bracket_pos - 1 );
                                $from_name = str_replace( '"', '', $from_name );
                                $from_name = trim( $from_name );
                            }
 
                            $from_email = substr( $content, $bracket_pos + 1 );
                            $from_email = str_replace( '>', '', $from_email );
                            $from_email = trim( $from_email );
 
                        // Avoid setting an empty $from_email.
                        } elseif ( '' !== trim( $content ) ) {
                            $from_email = trim( $content );
                        }
                        break;
                    case 'content-type':
                        if ( strpos( $content, ';' ) !== false ) {
                            list( $type, $charset_content ) = explode( ';', $content );
                            $content_type = trim( $type );
                            if ( false !== stripos( $charset_content, 'charset=' ) ) {
                                $charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
                            } elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
                                $boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '', $charset_content ) );
                                $charset = '';
                            }
 
                        // Avoid setting an empty $content_type.
                        } elseif ( '' !== trim( $content ) ) {
                            $content_type = trim( $content );
                        }
                        break;
                    case 'cc':
                        $cc = array_merge( (array) $cc, explode( ',', $content ) );
                        break;
                    case 'bcc':
                        $bcc = array_merge( (array) $bcc, explode( ',', $content ) );
                        break;
                    default:
                        // Add it to our grand headers array
                        $headers[trim( $name )] = trim( $content );
                        break;
                }
            }
        }
    }
 
    // Empty out the values that may be set
    $phpmailer->ClearAllRecipients();
    $phpmailer->ClearAttachments();
    $phpmailer->ClearCustomHeaders();
    $phpmailer->ClearReplyTos();
 
    // From email and name
    // If we don't have a name from the input headers
    if ( !isset( $from_name ) )
        $from_name = 'WordPress';
 
    /* If we don't have an email from the input headers default to wordpress@$sitename
     * Some hosts will block outgoing mail from this address if it doesn't exist but
     * there's no easy alternative. Defaulting to admin_email might appear to be another
     * option but some hosts may refuse to relay mail from an unknown domain. See
     * https://core.trac.wordpress.org/ticket/5007.
     */
 
    if ( !isset( $from_email ) ) {
        // Get the site domain and get rid of www.
        $sitename = strtolower( $_SERVER['SERVER_NAME'] );
        if ( substr( $sitename, 0, 4 ) == 'www.' ) {
            $sitename = substr( $sitename, 4 );
        }
 
        $from_email = 'wordpress@' . $sitename;
    }
 
    /**
     * Filter the email address to send from.
     *
     * @since 2.2.0
     *
     * @param string $from_email Email address to send from.
     */
    $phpmailer->From = apply_filters( 'wp_mail_from', $from_email );
 
    /**
     * Filter the name to associate with the "from" email address.
     *
     * @since 2.3.0
     *
     * @param string $from_name Name associated with the "from" email address.
     */
    $phpmailer->FromName = apply_filters( 'wp_mail_from_name', $from_name );
 
    // Set destination addresses
    if ( !is_array( $to ) )
        $to = explode( ',', $to );
 
    foreach ( (array) $to as $recipient ) {
        try {
            // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
            $recipient_name = '';
            if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                if ( count( $matches ) == 3 ) {
                    $recipient_name = $matches[1];
                    $recipient = $matches[2];
                }
            }
            $phpmailer->AddAddress( $recipient, $recipient_name);
        } catch ( phpmailerException $e ) {
            continue;
        }
    }
 
    // Set mail's subject and body
    $phpmailer->Subject = $subject;
    $phpmailer->Body    = $message;
 
    // Add any CC and BCC recipients
    if ( !empty( $cc ) ) {
        foreach ( (array) $cc as $recipient ) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';
                if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $recipient = $matches[2];
                    }
                }
                $phpmailer->AddCc( $recipient, $recipient_name );
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }
 
    if ( !empty( $bcc ) ) {
        foreach ( (array) $bcc as $recipient) {
            try {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                $recipient_name = '';
                if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $recipient_name = $matches[1];
                        $recipient = $matches[2];
                    }
                }
                $phpmailer->AddBcc( $recipient, $recipient_name );
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }
 
    // Set to use PHP's mail()
    $phpmailer->IsMail();
 
    // Set Content-Type and charset
    // If we don't have a content-type from the input headers
    if ( !isset( $content_type ) )
        $content_type = 'text/plain';
 
    /**
     * Filter the wp_mail() content type.
     *
     * @since 2.3.0
     *
     * @param string $content_type Default wp_mail() content type.
     */
    $content_type = apply_filters( 'wp_mail_content_type', $content_type );
 
    $phpmailer->ContentType = $content_type;
 
    // Set whether it's plaintext, depending on $content_type
    if ( 'text/html' == $content_type )
        $phpmailer->IsHTML( true );
 
    // If we don't have a charset from the input headers
    if ( !isset( $charset ) )
        $charset = get_bloginfo( 'charset' );
 
    // Set the content-type and charset
 
    /**
     * Filter the default wp_mail() charset.
     *
     * @since 2.3.0
     *
     * @param string $charset Default email charset.
     */
    $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );
 
    // Set custom headers
    if ( !empty( $headers ) ) {
        foreach ( (array) $headers as $name => $content ) {
            $phpmailer->AddCustomHeader( sprintf( '%1$s: %2$s', $name, $content ) );
        }
 
        if ( false !== stripos( $content_type, 'multipart' ) && ! empty($boundary) )
            $phpmailer->AddCustomHeader( sprintf( "Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary ) );
    }
 
    if ( !empty( $attachments ) ) {
        foreach ( $attachments as $attachment ) {
            try {
                $phpmailer->AddAttachment($attachment);
            } catch ( phpmailerException $e ) {
                continue;
            }
        }
    }
 
    /**
     * Fires after PHPMailer is initialized.
     *
     * @since 2.2.0
     *
     * @param PHPMailer &$phpmailer The PHPMailer instance, passed by reference.
     */
    do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );
 
    // Send!
    try {
        return $phpmailer->Send();
    } catch ( phpmailerException $e ) {
 
        $mail_error_data = compact( $to, $subject, $message, $headers, $attachments );
 
        /**
         * Fires after a phpmailerException is caught.
         *
         * @since 4.4.0
         *
         * @param WP_Error $error A WP_Error object with the phpmailerException code, message, and an array
         *                        containing the mail recipient, subject, message, headers, and attachments.
         */
        do_action( 'wp_mail_failed', new WP_Error( $e->getCode(), $e->getMessage(), $mail_error_data ) );
 
        return false;
    }
}
endif;

if ( !function_exists('get_userdata') ) :
/**
 * Retrieve user info by user ID.
 *
 * @since 0.71
 *
 * @param int $user_id User ID
 * @return WP_User|false WP_User object on success, false on failure.
 */
function get_userdata( $user_id ) {
	return get_user_by( 'id', $user_id );
}
endif;

if ( !function_exists('get_user_by') ) :
/**
 * Retrieve user info by a given field
 *
 * @since 2.8.0
 * @since 4.4.0 Added 'ID' as an alias of 'id' for the `$field` parameter.
 *
 * @param string     $field The field to retrieve the user with. id | ID | slug | email | login.
 * @param int|string $value A value for $field. A user ID, slug, email address, or login name.
 * @return WP_User|false WP_User object on success, false on failure.
 */
function get_user_by( $field, $value ) {
	$userdata = WP_User::get_data_by( $field, $value );

	if ( !$userdata )
		return false;

	$user = new WP_User;
	$user->init( $userdata );

	return $user;
}
endif;





?>
