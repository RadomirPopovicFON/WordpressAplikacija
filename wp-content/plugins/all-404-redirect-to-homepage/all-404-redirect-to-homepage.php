<?php
/*
Plugin Name: All 404 Redirect  to Homepage
Plugin URI: http://www.clogica.com
Description: a plugin to redirect 404 pages to home page or any custom page
Author: Fakhri Alsadi
Version: 1.4
Author URI: http://www.clogica.com
*/


define( 'OPTIONS404', 'options-404-redirect-group' );
require_once ('functions.php');

add_action('admin_menu', 'p404_admin_menu');
add_action('admin_head', 'p404_header_code');
add_action('wp', 'p404_redirect');

register_activation_hook( __FILE__ , 'p404_install' );
register_deactivation_hook( __FILE__ , 'p404_uninstall' );


function p404_redirect()
{
	if(is_404()) 
	{
	 	
	 	$options= get_my_options();
	    $link=get_current_URL();
	    if($link == $options['p404_redirect_to'])
	    {
	        echo "<b>All 404 Redirect to Homepage</b> has detected that the target URL is invalid, this will cause an infinite loop redirection, please go to the plugin settings and correct the traget link! ";
	        exit(); 
	    }
	    
	 	if($options['p404_status']=='1' & $options['p404_redirect_to']!=''){
		 	header ('HTTP/1.1 301 Moved Permanently');
			header ("Location: " . $options['p404_redirect_to']);
			exit(); 
		}
	}
}


//---------------------------------------------------------------

   function p404_check_default_permalink() 
    {
       global $util,$wp_rewrite;
       
       $file= get_home_path() . "/.htaccess";
       $filestr ="";
       $begin_marker = "# BEGIN WordPress";
       $end_marker = "# END WordPress";
       $content="ErrorDocument 404 /index.php?error=404";
       $findword = "ErrorDocument 404";
       
       if($wp_rewrite->permalink_structure =='')
       {
        
        if(file_exists($file)){
            
           $f = @fopen( $file, 'r+' );
           $filestr = @fread($f , filesize($file)); 
           
           if (strpos($filestr , $findword) === false)
            {
               if (strpos($filestr , $begin_marker) === false)
                    {
                        $filestr = $begin_marker . PHP_EOL . $content . PHP_EOL . $end_marker . PHP_EOL . $filestr ;
                        fwrite($f ,  $filestr); 
                        fclose($f);
                    }
                    else
                    {
                        fclose($f);
                        $f = fopen($file, "w");
                        $n=strpos($filestr , $begin_marker) + strlen('# BEGIN WordPress');;
                        $div1= substr($filestr,0,$n);
                        $div2= substr($filestr,($n+1),strlen($filestr));
                        $filestr = $div1 . PHP_EOL . $content . PHP_EOL . $div2;
                        fwrite($f ,  $filestr); 
                        fclose($f);
                        
                    }
            }
            
        }else
        {
          
          $filestr = $begin_marker . PHP_EOL . $content . PHP_EOL . $end_marker ;
          if($f = @fopen( $file, 'w' )){
            fwrite($f ,  $filestr); 
            fclose($f);
            }
        }
       
       }
       
    }



//---------------------------------------------------------------

function p404_header_code()
{
	p404_check_default_permalink();
	$css=get_url_path() . "style.css";
	echo '<link type="text/css" rel="stylesheet" href="'. $css .'"/>';
	
}

//---------------------------------------------------------------

function p404_admin_menu() {
	add_options_page('All 404 Redirect to Homepage', 'All 404 Redirect to Homepage', 'manage_options', basename(__FILE__), 'p404_options_menu'  );
}

//---------------------------------------------------------------
function p404_options_menu() {
	
	if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		
	include "option_page.php";
}
//---------------------------------------------------------------

function p404_install(){

}


//---------------------------------------------------------------

function p404_uninstall(){
	delete_option(OPTIONS404);
}

//---------------------------------------------------------------
