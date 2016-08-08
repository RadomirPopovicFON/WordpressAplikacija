<?php

function get_current_URL()
{
	$prt = $_SERVER['SERVER_PORT'];
	$sname = $_SERVER['SERVER_NAME'];
	
	if (array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS'] != 'off' && $_SERVER['HTTPS'] != '')
	$sname = "https://" . $sname; 
	else
	$sname = "http://" . $sname; 
	
	if($prt !=80)
	{
	$sname = $sname . ":" . $prt;
	} 
	
	$path = $sname . $_SERVER["REQUEST_URI"];
	
	return $path ;

}

//-----------------------------------------------------

function get_abs_path()
{
	return WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/';
}
	
//-----------------------------------------------------

function get_url_path()
{
	return WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) . '/';
}

//----------------------------------------------------

function get_current_parameters($remove_parameter="")
{	
	
	if($_SERVER['QUERY_STRING']!='')
	{
		$qry = '?' . $_SERVER['QUERY_STRING']; 
		if($remove_parameter!='')
		{
			$string_remove = '&' . $remove_parameter . "=" . $_GET[$remove_parameter];
			$qry=str_replace($string_remove,"",$qry);
			$string_remove = '?' . $remove_parameter . "=" . $_GET[$remove_parameter];
			$qry=str_replace($string_remove,"",$qry);
		}
		
		return $qry;
	}else
	{
		return "";
	}
} 

//----------------------------------------------------

function get_visitor_IP()
{
	$ipaddress = $_SERVER['REMOTE_ADDR'];
	
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	
	return $ipaddress ;
}

//----------------------------------------------------

function get_visitor_OS()
{

$userAgent= $_SERVER['HTTP_USER_AGENT'];
		$oses = array (
		'iPhone' => '(iPhone)',
		'Windows 3.11' => 'Win16',
		'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)', 
		'Windows 98' => '(Windows 98)|(Win98)',
		'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
		'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
		'Windows 2003' => '(Windows NT 5.2)',
		'Windows Vista' => '(Windows NT 6.0)|(Windows Vista)',
		'Windows 7' => '(Windows NT 6.1)|(Windows 7)',
		'Windows 8' => '(Windows NT 6.2)|(Windows 8)',
		'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
		'Windows ME' => 'Windows ME',
		'Open BSD'=>'OpenBSD',
		'Sun OS'=>'SunOS',
		'Linux'=>'(Linux)|(X11)',
		'Safari' => '(Safari)',
		'Macintosh'=>'(Mac_PowerPC)|(Macintosh)',
		'QNX'=>'QNX',
		'BeOS'=>'BeOS',
		'OS/2'=>'OS/2',
		'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)'
	);

	foreach($oses as $os=>$pattern){ 

		if(eregi($pattern, $userAgent)) { 
			return $os; 
		}
	}
	return 'Unknown';
}

//-----------------------------------------------------------------

function get_visitor_Browser()
{

$userAgent= $_SERVER['HTTP_USER_AGENT'];
		$browsers = array(
		'Opera' => 'Opera',
		'Firefox'=> '(Firebird)|(Firefox)', 
		'Galeon' => 'Galeon',
		'Chrome'=>'Chrome',
		'MyIE'=>'MyIE',
		'Lynx' => 'Lynx',
		'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',
		'Konqueror'=>'Konqueror',
		'SearchBot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp/cat)|(msnbot)|(ia_archiver)',
		'Internet Explorer 8' => '(MSIE 8\.[0-9]+)',
		'Internet Explorer 9' => '(MSIE 9\.[0-9]+)',
        'Internet Explorer 7' => '(MSIE 7\.[0-9]+)',
		'Internet Explorer 6' => '(MSIE 6\.[0-9]+)',
		'Internet Explorer 5' => '(MSIE 5\.[0-9]+)',
		'Internet Explorer 4' => '(MSIE 4\.[0-9]+)',
	);

	foreach($browsers as $browser=>$pattern) { 

		if(eregi($pattern, $userAgent)) {
			return $browser; 
		}
	}
	return 'Unknown'; 

}



//---------------------------------------------------- 

function init_my_options()
{	
	add_option(OPTIONS404);
	$options = array();
	$options['p404_redirect_to']= site_url();
	$options['p404_status']= '1';	
	update_option(OPTIONS404,$options);
} 

//---------------------------------------------------- 

function update_my_options($options)
{	
	update_option(OPTIONS404,$options);
} 

//---------------------------------------------------- 

function get_my_options()
{	
	$options=get_option(OPTIONS404);
	if(!$options)
	{
		init_my_options();
		$options=get_option(OPTIONS404);
	}
	return $options;			
}

//---------------------------------------------------- 

function option_msg($msg)
{	
	echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';		
}

//---------------------------------------------------- 

function info_option_msg($msg)
{	
	echo '<div id="message" class="updated"><p><div class="info_icon"></div> ' . $msg . '</p></div>';		
}

//---------------------------------------------------- 

function warning_option_msg($msg) 
{	
	echo '<div id="message" class="error"><p><div class="warning_icon"></div> ' . $msg . '</p></div>';		
}

//---------------------------------------------------- 

function success_option_msg($msg)
{	
	echo '<div id="message" class="updated"><p><div class="success_icon"></div> ' . $msg . '</p></div>';		
}

//---------------------------------------------------- 

function failure_option_msg($msg)
{	
	echo '<div id="message" class="error"><p><div class="failure_icon"></div> ' . $msg . '</p></div>';		
}


//---------------------------------------------------- 
function there_is_cache()
{	

$plugins=get_option( 'active_plugins' );

		    for($i=0;$i<count($plugins);$i++)
		    {   
		       if (stripos($plugins[$i],'cache')!==false)
		       {
		       	  return $plugins[$i];
		       }
		    }


	return '';				
}

   