<?php
/*
 *
 * Copyright (c) 2008-2016 Predrag Supurović
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer
 *    in the documentation and/or other materials provided with the
 *    distribution.
 * 3. The name of the author may not be used to endorse or promote
 *    products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

/*
Plugin Name: Serbian Transliteration of Cyrillic to Latin Script
Plugin URI: http://pedja.supurovic.net/srbtranslatin/
Description: Allows users to choose if they want to see site in Serbian Cyrillic or Serbian Latin script. After installation, check <a href="options-general.php?page=srbtranslatoptions">Settings</a>
Author: Predrag Supurović
Version: 1.37
Author URI: http://pedja.supurovic.net
Text Domain: srbtranslatin
Domain Path: /lang
*/

/***********************************************************/
/***********************************************************/
/***********************************************************/


DEFINE ('STL_DELIMITER_START', '\[');
DEFINE ('STL_DELIMITER_END', '\]');

//test for WMPL language
//DEFINE ('ICL_LANGUAGE_CODE', 'sr');

$stl_priority =  99;

$m_config_file = dirname( plugin_dir_path( __FILE__ ) ) . '/' . dirname( plugin_basename( __FILE__ )) . '/config.php';


if (file_exists ($m_config_file)) {
	include ($m_config_file);
	$stl_priority = $stl_config['priority'];
}

load_plugin_textdomain( 'srbtranslatin', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );


$m_lang_cookie_name = 'stl_default_lang';

$stl_use_cookie_name = 'stl_use_cookie';
$stl_use_cookie_data_field_name = 'stl_use_cookie';

$stl_use_cookie = get_option ($stl_use_cookie_name);

//echo "stl_use_cookie=$stl_use_cookie<br>";

$stl_lang_identificator_opt_name = 'lang_identificator';
$stl_lang_identificator_data_field_name = 'lang_identificator';
$stl_lang_identificator = get_option ($stl_lang_identificator_opt_name);

$stl_lang_cir_id_opt_name = 'cir_id';
$stl_lang_cir_id_data_field_name = 'cir_id';
$stl_lang_cir_id = get_option ($stl_lang_cir_id_opt_name);

$stl_lang_lat_id_opt_name = 'lat_id';
$stl_lang_lat_id_data_field_name = 'lat_id';
$stl_lang_lat_id = get_option ($stl_lang_lat_id_opt_name);

$m_wpml_plugin_file = dirname( plugin_dir_path( __FILE__ ) ) . '/' .'sitepress-multilingual-cms/sitepress.php';
//echo "m_wpml_plugin_file=$m_wpml_plugin_file!";

//$stl_wpml_plugin_active = is_plugin_active('wpml/wpml.php');
$stl_wpml_plugin_active = file_exists($m_wpml_plugin_file);
//echo "stl_wpml_plugin_active=$stl_wpml_plugin_active";


if (empty ($stl_lang_identificator)) {

	if ( $stl_wpml_plugin_active ) {
		$m_lang_identificator = 'script';
	} else {
		$m_lang_identificator = 'lang';
	}
} else {
	$m_lang_identificator = $stl_lang_identificator;
}

$stl_lang_cir_id = 'cir';
$stl_lang_lat_id = 'lat';

$m_lang_lat_id = $stl_lang_lat_id;


$m_cookie_language = '';

if ($stl_use_cookie) {
	if (isset($_COOKIE[$m_lang_cookie_name])) {
		$m_cookie_language = $_COOKIE[$m_lang_cookie_name];
	}
} 

$stl_default_language_opt_name = 'stl_default_language';
$stl_default_language_data_field_name = 'stl_default_language';

$m_default_language = get_option( $stl_default_language_opt_name );

if ($m_default_language == 'ifcir') {
	$m_accept_languages = split(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	$m_accepts_cyrillic = false;
	foreach ($m_accept_languages as $m_item) {
		$m_accepts_cyrillic = $m_accepts_cyrillic || ereg("^(sr|ru|mk|bg|be|bs|kk|ky|mn|tg|uk)", $m_item);
	}

	if ($m_accepts_cyrillic) {
		$m_default_language = $stl_lang_cir_id;
	} else {
		$m_default_language = $stl_lang_lat_id;
	}
}

//echo "m_default_language=$m_default_language<br>";
//echo "stl_lang_cir_id=$stl_lang_cir_id<br>";
//echo "stl_lang_lat_id=$stl_lang_lat_id<br>";

if ( ($m_default_language != $stl_lang_cir_id) and ($m_default_language != $stl_lang_lat_id) ) {
	$m_default_language = $stl_lang_cir_id;
}

$stl_default_language = $m_default_language;

$stl_default_language = $m_default_language;

//echo "m_default_language=$m_default_language<br>";

$stl_transliterate_title_opt_name = 'stl_transliterate_title';
$stl_transliterate_title_data_field_name = 'stl_transliterate_title';
$stl_transliterate_title = get_option( $stl_transliterate_title_opt_name ) == '1';

$stl_widget_title_opt_name = 'stl_widget_title';
$stl_widget_title_data_field_name = 'stl_widget_title';
$stl_widget_title = get_option( $stl_widget_title_opt_name );
if (empty ($stl_widget_title)) $stl_widget_title  = __("Script selection", 'srbtranslatin');

$stl_show_widget_title_opt_name = 'stl_show_widget_title';
$stl_show_widget_title_data_field_name = 'stl_show_widget_title';
$stl_show_widget_title = get_option ($stl_show_widget_title_opt_name) == 'on';

$stl_widget_type_opt_name = 'stl_widget_type';
$stl_widget_type_data_field_name = 'stl_widget_type';
$stl_widget_type = get_option ($stl_widget_type_opt_name);
if ( ($stl_widget_type != 'links') and ($stl_widget_type != 'list') ) {
	$stl_widget_type = 'links';
}

if ( isset($_REQUEST[$m_lang_identificator]) ) {
	$stl_current_language  = $_REQUEST[$m_lang_identificator];
} else {
	$stl_current_language = $m_cookie_language;
}

if ( ($stl_current_language  != $stl_lang_cir_id) and ($stl_current_language != $stl_lang_lat_id) ) {
	$stl_current_language = $stl_default_language;
}

//echo "m_cookie_language=$m_cookie_language<br>";	
//echo "stl_current_language=$stl_current_language<br>";

if ($stl_use_cookie) {
	setcookie($m_lang_cookie_name, $stl_current_language, strtotime("+1 year"), "/");
} else {
	setcookie($m_lang_cookie_name, $stl_current_language, time()-100, "/");
}

$stl_global['init'] = true;


// Hook for adding admin menus
add_action('admin_menu', 'stl_add_page');

// Hook for adding widget
add_action( 'widgets_init', create_function( '', 'register_widget( "srbtranslatin_widget" );' ) );

//add_action('wp_footer', 'show_in_footer', 100);

include ('srbtranslatin_widget.php');
include ('urlt.php');

class SrbTransLatin {
  var $replace = array(
    "А" => "A",
		"Б" => "B",
		"В" => "V",
		"Г" => "G",
		"Д" => "D",
		"Ђ" => "Đ",
		"Е" => "E",
		"Ж" => "Ž",
		"З" => "Z",
		"И" => "I",
		"Ј" => "J",
		"К" => "K",
		"Л" => "L",
		"Љ" => "LJ",
		"М" => "M",
		"Н" => "N",
		"Њ" => "NJ",
		"О" => "O",
		"П" => "P",
		"Р" => "R",
		"С" => "S",
		"Ш" => "Š",
		"Т" => "T",
		"Ћ" => "Ć",
		"У" => "U",
		"Ф" => "F",
		"Х" => "H",
		"Ц" => "C",
		"Ч" => "Č",
		"Џ" => "DŽ",
		"Ш" => "Š",
    "а" => "a",
		"б" => "b",
		"в" => "v",
		"г" => "g",
		"д" => "d",
		"ђ" => "đ",
		"е" => "e",
		"ж" => "ž",
		"з" => "z",
		"и" => "i",
		"ј" => "j",
		"к" => "k",
		"л" => "l",
		"љ" => "lj",
		"м" => "m",
		"н" => "n",
		"њ" => "nj",
		"о" => "o",
		"п" => "p",
		"р" => "r",
		"с" => "s",
		"ш" => "š",
		"т" => "t",
		"ћ" => "ć",
		"у" => "u",
		"ф" => "f",
		"х" => "h",
		"ц" => "c",
		"ч" => "č",
		"џ" => "dž",
		"ш" => "š",
		"Ња" => "Nja",
		"Ње" => "Nje",
		"Њи" => "Nji",
		"Њо" => "Njo",
		"Њу" => "Nju",
		"Ља" => "Lja",
		"Ље" => "Lje",
		"Љи" => "Lji",
		"Љо" => "Ljo",
		"Љу" => "Lju",
		"Џа" => "Dža",
		"Џе" => "Dže",
		"Џи" => "Dži",
		"Џо" => "Džo",
		"Џу" => "Džu",
    ".срб" => ".срб",
    "иѕ.срб" => "иѕ.срб",
    "њњњ.из.срб" => "њњњ.из.срб",
    ".СРБ" => ".СРБ",
    "ИЗ.СРБ" => "ИЗ.СРБ",
    "ЊЊЊ.ИЗ.СРБ" => "ЊЊЊ.ИЗ.СРБ"    
   );


  var $replace_sanitize = array(
    "А" => "A",
		"Б" => "B",
		"В" => "V",
		"Г" => "G",
		"Д" => "D",
		"Ђ" => "DJ",
		"Е" => "E",
		"Ж" => "Z",
		"З" => "Z",
		"И" => "I",
		"Ј" => "J",
		"К" => "K",
		"Л" => "L",
		"Љ" => "LJ",
		"М" => "M",
		"Н" => "N",
		"Њ" => "NJ",
		"О" => "O",
		"П" => "P",
		"Р" => "R",
		"С" => "S",
		"Ш" => "S",
		"Т" => "T",
		"Ћ" => "C",
		"У" => "U",
		"Ф" => "F",
		"Х" => "H",
		"Ц" => "C",
		"Ч" => "C",
		"Џ" => "DZ",
		"Ш" => "S",
    "а" => "a",
		"б" => "b",
		"в" => "v",
		"г" => "g",
		"д" => "d",
		"ђ" => "dj",
		"е" => "e",
		"ж" => "z",
		"з" => "z",
		"и" => "i",
		"ј" => "j",
		"к" => "k",
		"л" => "l",
		"љ" => "lj",
		"м" => "m",
		"н" => "n",
		"њ" => "nj",
		"о" => "o",
		"п" => "p",
		"р" => "r",
		"с" => "s",
		"ш" => "s",
		"т" => "t",
		"ћ" => "c",
		"у" => "u",
		"ф" => "f",
		"х" => "h",
		"ц" => "c",
		"ч" => "c",
		"џ" => "dz",
		"ш" => "s",
		"Ња" => "Nja",
		"Ње" => "Nje",
		"Њи" => "Nji",
		"Њо" => "Njo",
		"Њу" => "Nju",
		"Ља" => "Lja",
		"Ље" => "Lje",
		"Љи" => "Lji",
		"Љо" => "Ljo",
		"Љу" => "Lju",
		"Џа" => "Dza",
		"Џе" => "Dze",
		"Џи" => "Dzi",
		"Џо" => "Dzo",
		"Џу" => "Dzu"
   );


    function SrbTransLatin() {
			global $stl_default_language;
			global $stl_current_language;
			global $stl_transliterate_title;
			global $stl_priority;
			
			add_action("plugins_loaded",array(&$this,"init_lang"));
      
      if (! is_admin()) {
	
        $m_plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$m_plugin", array(&$this, 'plugin_settings_link') );
    
    
        if ($stl_transliterate_title) {
          add_action('sanitize_title', array(&$this, 'change_permalink'), 0);
          //add_action('sanitize_title', array(&$this, 'convert_title'), $stl_priority);
          add_filter('the_title', array(&$this,'convert_title'), $stl_priority);
        }
        
        add_filter('wp_title', array(&$this,'convert_title'), $stl_priority);      

        //add_filter('the_content', array(&$this,'convert_content'), $stl_priority);            
        //add_filter('the_content_feed', array(&$this,'convert_content'), $stl_priority);
        //add_filter('the_excerpt', array(&$this,'convert_content'), $stl_priority);
        //add_filter('the_excerpt_rss', array(&$this,'convert_content'), $stl_priority);
       

        add_action('wp_head', array(&$this,'buffer_start'), $stl_priority);
        add_action('wp_footer', array(&$this,'buffer_end'), $stl_priority);
        
        add_action('rss_head', array(&$this,'buffer_start'), $stl_priority);		
        add_action('rss_footer', array(&$this,'buffer_end'), $stl_priority);		
    
        add_action('atom_head', array(&$this,'buffer_start'), $stl_priority);		
        add_action('atom_footer', array(&$this,'buffer_end'), $stl_priority);		
        
        add_action('rdf_head', array(&$this,'buffer_start'), $stl_priority);		
        add_action('rdf_footer', array(&$this,'buffer_end'), $stl_priority);		
        
        add_action('rss2_head', array(&$this,'buffer_start'), $stl_priority);		
        add_action('rss2_footer', array(&$this,'buffer_end'), $stl_priority);		
        
        add_filter('option_blogname', array(&$this,'callback'), $stl_priority);
        add_filter('option_blogdescription', array(&$this,'callback'), $stl_priority);
      } // if ! is_admin()

		} // function 
	
	
	function init_lang() {
/*
		register_sidebar_widget("Serbian Scripts", array (&$this,"stl_scripts_widget"));
		register_sidebar_widget("Serbian Transliteration (links)", array (&$this,"stl_links_widget"));
		register_sidebar_widget("Serbian Transliteration (list)",  array (&$this,"stl_list_widget"));
*/
	}

	public function plugin_settings_link($p_links) {
		$m_settings_link = '<a href="options-general.php?page=srbtranslatoptions">' . __('Settings', 'srbtranslatin') . '</a>';
		array_unshift($p_links, $m_settings_link);
		return $p_links;
	}	



	function convert_script($text) {
		global $stl_default_language;
		global $stl_current_language ;
		global $stl_global;
    
		$m_text = $text;
		$m_chunks = $this->parse_lang ($text, '');
	
		$m_text = $this->join_lang($m_chunks);
		if ( $stl_current_language != $stl_default_language ) {
			$m_text = alter_url_batch ($m_text);		
		}
		return $m_text;
	}
	
	
	function convert_title ($title) {
	    $title = $this->convert_script($title);
	    return $title;
	}
  
	function convert_content ($content) {
	    $content = $this->convert_script($content);
	    return $content;
	}  

	function callback($buffer) {
//error_log ('callback in:' . $buffer);  
		$m_buffer = $this->convert_script($buffer);
//error_log ('callback out:' . $m_buffer);      
		return $m_buffer;
	}
	
	function buffer_start() { 
		ob_start(array(&$this,"callback")); 
	}
	 
	function buffer_end() {
		global $stl_global;	 
		ob_end_flush(); 
		
//print_r ($stl_global);
		
	}

	function change_permalink($title) {
		global $wpdb;
		if ($term = $wpdb->get_var("SELECT slug FROM $wpdb->terms WHERE name='$title'")) 
			return $term; 
		else 
			return strtr($title,$this->replace_sanitize);

	}
	
	
    //
    // parse_lang ($p_input, $p_def_lang)
    //
    // Parse input string int chunks delimited by [lang][/lang] tabs. That allows us to process them with 
	  // different languages (each chunk may be set to other language provided in [lang id="nn"] tag where 
	  //	nn is language id). If tag nn is "skip" then language transformation will not occur for that chunk.
	  // You may set default language which is used if chunk does not heave it's own 
	  // language set. If chunks are nested, language of containeer will be used for contained chunk, except
	  // if contained chunk does not have its own language set.
	function parse_lang ($p_input, $p_def_lang) {
		$regex = '#\[lang.*?\]((?:[^[]|\](?!/?lang.*?\])|(?R))+)\[/lang\]#';
		
		if (preg_match ($regex, $p_input)) {
			$split = preg_split ($regex, $p_input,2);
	
			//tekuci nivo, prefiks
			unset ($m_out);
			$m_out['lang'] = $p_def_lang;
			$m_out['value'] = $split[0];
			$out[0] = $m_out;
	
			// sledeci nivo po dubini
			$m_input = $p_input;
			
			if (strlen ($split[0]) > 0) $m_input = substr ($m_input, strlen ($split[0]));
			if (strlen ($split[1]) > 0) $m_input = substr ($m_input, 0, - strlen ($split[1]));
			
			preg_match ('/\[lang(( +[^\]]*)?id=(")?(([a-z]-?)*)"?)?\]/', $p_input, $m_matches);
			
			if (! empty ($m_matches[4])) {
				$m_cur_lang = $m_matches[4]; 
			} else {
				$m_cur_lang = $p_def_lang; 
			}
			
			$m_input = preg_replace ($regex, '$1', $m_input);		
	
			unset ($m_out);
			$m_out[0] = $this->parse_lang ($m_input, $m_cur_lang);
			$out[1] = $m_out;
			
			// tekuci nivo, sufiks
			unset ($m_out);
			$m_out['lang'] = $p_def_lang;
			$m_out = $this->parse_lang ($split[1], $m_out['lang']);
			$out[2] = $m_out;
				
			$m_result = $out;
		} else {
			$m_result[0]['lang'] = $p_def_lang;
			$m_result[0]['value'] = $p_input;
		}
		return ($m_result);
	}
	
	
    //
    // function joinLang ($p_input)
    //
    // Parse input string int chunks delimited by <lang></lang> tabs. That allows us to process them with 
	// different languages (each chunk may be set to other language provided in <lang id="nn"> tag where 
	//	nn is language id)
	function join_lang ($p_input) {
		global $stl_global;
		global $stl_current_language;
		
		$m_result = '';
		foreach ($p_input as $m_item) {
			if (isset ($m_item['value'])) {
				if (! empty ($m_item['value'])) {
					if ($m_item['lang'] === 'skip') {
						$m_result .= $m_item['value'];
					} else {
						if ( $stl_current_language == "lat" ) {
							$m_result .= strtr($m_item['value'], $this->replace);
						} else {
							$m_result .= $m_item['value'];
						}
					}
				}
			} else {
				$m_result .= $this->join_lang ($m_item);
			}
		}
		return ($m_result);
	}	
	
	
	
	function test ($m_string) {
		return "###$m_string###";
	}
	


} // class

	function alter_url_batch ($p_url) {
  
    $m_result1 = preg_replace_callback("/(src=\"|href=\"|background=\"|\<link\>)(.*?)(\"|\<\/link\>)/is", 'alter_url', $p_url);
    
    $m_result = preg_replace_callback("/(src=\'|href=\'|background=\'|\<link\>)(.*?)(\'|\<\/link\>)/is", 'alter_url', $m_result1);    
    
    return $m_result;
	}

	function alter_url($p_urls){
		global $stl_current_language;
		global $stl_default_language;
		global $g_buffer;
		global $m_lang_identificator; 

		$m_site_url = get_option('home');
    
$m_tmp_urls = $p_urls[0];

		if ( $m_site_url == substr ($p_urls[2], 0, strlen ($m_site_url)) ) {

			if ($stl_current_language <> $stl_default_language) {

				$m_result = $p_urls[1] . url_add_param ($p_urls[2], $m_lang_identificator . '=' .  $stl_current_language, false) . $p_urls[3];


				$m_result = preg_replace("/\=$stl_default_language\=/", "=$stl_current_language=", $m_result); 

			}

		} else {

			//$m_result = $p_urls[1] . url_clean_param ($p_urls[2], $m_lang_identificator) . $p_urls[3];
      $m_result = $p_urls[1] . $p_urls[2] . $p_urls[3];

		}

    return $m_result;
	}
	
	
	
function stl_add_page() {

    // Add a new submenu under Options:
    add_options_page('SrbTransLat', 'SrbTransLat', 'update_core', 'srbtranslatoptions', 'stl_options_page');
	
}	

// mt_options_page() displays the page content for the Test Options submenu
function stl_options_page() {

	global $stl_lang_identificator_opt_name;
	global $stl_lang_identificator_data_field_name;
	global $stl_lang_identificator;
	
	global $stl_lang_cir_id;
	global $stl_lang_lat_id;
	
	global $stl_lang_cir_id_opt_name;
	global $stl_lang_lat_id_opt_name;	
	
	global $stl_lang_cir_id_data_field_name;
	global $stl_lang_lat_id_data_field_name;

	global $stl_default_language_opt_name;
	global $stl_default_language_data_field_name;
	global $stl_widget_title_opt_name;
	global $stl_widget_title_data_field_name;
	global $stl_transliterate_title_opt_name;
	global $stl_transliterate_title_data_field_name;
	global $stl_show_widget_title_opt_name;
	global $stl_show_widget_title_data_field_name;
	global $stl_widget_type_opt_name;
	global $stl_widget_type_data_field_name;
	global $stl_use_cookie_name;
	global $stl_use_cookie_data_field_name;
	global $stl_wpml_plugin_active;
	
	
   // Read in existing option value from database


  $stl_lang_identificator_opt_val = get_option( $stl_lang_identificator_opt_name );
	if (empty ($stl_lang_identificator_opt_val)) $stl_lang_identificator_opt_val = 'lang';

  $stl_lang_cir_id_opt_val = get_option( $stl_lang_cir_id_opt_name );
	if (empty ($stl_lang_cir_id_opt_val)) $stl_lang_cir_id_opt_val = $stl_lang_cir_id;

  $stl_lang_lat_id_opt_val = get_option( $stl_lang_lat_id_opt_name );
	if (empty ($stl_lang_lat_id_opt_val)) $stl_lang_lat_id_opt_val = $stl_lang_lat_id;

  $stl_default_language_opt_val = get_option( $stl_default_language_opt_name );
	if (empty ($stl_default_language_opt_val)) $stl_default_language_opt_val = $stl_lang_cir_id;
	
	$stl_widget_title_opt_val = get_option($stl_widget_title_opt_name);
	if (empty ($stl_widget_title_opt_val)) $stl_widget_title_opt_val = "Избор писма";


	$stl_transliterate_title_opt_val = get_option($stl_transliterate_title_opt_name);
	if (empty ($stl_transliterate_title_opt_val)) $stl_transliterate_title_opt_val = 'off';

	$stl_show_widget_title_opt_val = get_option($stl_show_widget_title_opt_name);	
	if (empty ($stl_show_widget_title_opt_val)) $stl_show_widget_title_opt_val = 'off';	

	$stl_widget_type_opt_val = get_option($stl_widget_type_opt_name);	

	if ( ($stl_widget_type_opt_val != 'links') and ($stl_widget_type_opt_val != 'list') ) {
		$stl_widget_type_opt_val = 'links';
	}
	
	$stl_use_cookie_val = get_option($stl_use_cookie_name);	
	
//echo "1stl_use_cookie_val=$stl_use_cookie_val<br>";	

//echo "post<pre>";
//print_r ($_POST);
//echo "</pre>#";

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( ! empty ($_POST['Submit']) ) {
        // Read their posted value

				if (isset ($_POST[ $stl_lang_identificator_data_field_name ])) {
	        $stl_lang_identificator_opt_val = $_POST[ $stl_lang_identificator_data_field_name ];
				} else {
					$stl_lang_identificator_opt_val = 'lang';
				}
        update_option( $stl_lang_identificator_opt_name, $stl_lang_identificator_opt_val );

        //$stl_lang_cir_id_opt_val = $_POST[ $stl_lang_cir_id_data_field_name ];
        //update_option( $stl_lang_cir_id_opt_name, $stl_lang_cir_id_opt_val );

        //$stl_lang_lat_id_opt_val = $_POST[ $stl_lang_lat_id_data_field_name ];
        //update_option( $stl_lang_lat_id_opt_name, $stl_lang_lat_id_opt_val );

        $stl_default_language_opt_val = $_POST[ $stl_default_language_data_field_name ];
        update_option( $stl_default_language_opt_name, $stl_default_language_opt_val );
				
				$stl_transliterate_title_opt_val = isset ($_POST[$stl_transliterate_title_data_field_name]);
        update_option( $stl_transliterate_title_opt_name, $stl_transliterate_title_opt_val );
		

				$stl_use_cookie_val = isset ($_POST[$stl_use_cookie_data_field_name]);

//echo "2stl_use_cookie_val=$stl_use_cookie_val<br>";	

				update_option( $stl_use_cookie_name, $stl_use_cookie_val );

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Options saved.', 'srbtranslatin'); ?></strong></p></div>
<?php

    }
		
		if (empty ($stl_use_cookie_val)) $stl_use_cookie_val = 0;			


//echo "stl_use_cookie_val=$stl_use_cookie_val<br>";	

    // Now display the options editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'SrbTransLat Plugin Options', 'srbtranslatin') . "</h2>";

    // options form
    
    ?>

<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<table class="form-table">

<tr>
<th scope="row"><?php _e("Script identificator:", 'srbtranslatin'); ?></th>
<td><input name="<?php echo $stl_lang_identificator_data_field_name; ?>" type="text" value="<?php echo $stl_lang_identificator_opt_val; ?>"?><br />
<?php echo __('Set what identificator for script selection will be used in urls.', 'srbtranslatin'); ?>
<?php
	if ( $stl_wpml_plugin_active && ($stl_lang_identificator_opt_val == 'lang') ) {
?>
<br /><span style="color:red"><?php echo __("WARNING! You have WPML installed! Using 'lang' for url identificator for SrbTranslating will create conflicts with WMPL! Change identificator!", 'srbtranslatin'); ?></span>
<?php
	}
?>
</td>
</tr>

<!--
<tr>
<th scope="row"><?php _e("Cyrillic Identificator Value:", 'srbtranslatin'); ?></th>
<td><input name="<?php echo $stl_lang_cir_id_data_field_name; ?>" type="text" value="<?php echo $stl_lang_cir_id_opt_val; ?>"?><br />
<?php echo __('Set what value should be used for Cyrillic script identificator in urls.', 'srbtranslatin'); ?>
</td>
</tr>

<tr>
<th scope="row"><?php _e("Latin Identificator Value:", 'srbtranslatin'); ?></th>
<td><input name="<?php echo $stl_lang_lat_id_data_field_name; ?>" type="text" value="<?php echo $stl_lang_lat_id_opt_val; ?>"?><br />
<?php echo __('Set what value should be used for Latin script identificator in urls.', 'srbtranslatin'); ?>
</td>
</tr>
-->

<tr>
<th scope="row"><?php _e("Default script:", 'srbtranslatin'); ?></th>
<td>
<select name="<?php echo $stl_default_language_data_field_name; ?>">
<option value="<?php echo $stl_lang_cir_id_opt_val; ?>" <?php echo $stl_default_language_opt_val==$stl_lang_cir_id_opt_val ? 'selected="selected"' : '' ?>><?php echo __('Cyrillic', 'srbtranslatin'); ?></option>
<option value="<?php echo $stl_lang_lat_id_opt_val; ?>" <?php echo $stl_default_language_opt_val==$stl_lang_lat_id_opt_val ? 'selected="selected"' : '' ?>><?php echo __('Latin', 'srbtranslatin'); ?></option>
<option value="ifcir" <?php echo $stl_default_language_opt_val=='ifcir' ? 'selected="selected"' : '' ?>><?php echo __("Cyrillic, if visitor's browser accepts it", 'srbtranslatin') ?></option>

</select>
<br /><?php _e("Set script that would be used as default, if user do not make script choice", 'srbtranslatin'); ?></td>
</tr>

<tr>
<th scope="row"><?php _e("Use cookie:", 'srbtranslatin'); ?></th>
<td><input name="<?php echo $stl_use_cookie_data_field_name; ?>" type="checkbox" <?php echo $stl_use_cookie_val==1 ? 'checked="checked"' : '' ?>> <?php _e("use cookie", 'srbtranslatin'); ?><br />
<?php echo __('Check to make blog remember users last script selection to cookie.', 'srbtranslatin'); ?>
</td>
</tr>

<tr>
<th scope="row"><?php _e("Permalink options:", 'srbtranslatin'); ?></th>
<td><input name="<?php echo $stl_transliterate_title_data_field_name; ?>" type="checkbox" <?php echo $stl_transliterate_title_opt_val==1 ? 'checked="checked"' : '' ?>> <?php _e("transliterate title to permalink", 'srbtranslatin'); ?><br />
<?php echo __('Check to make permalinks in Latin alphabet regarding Serbian language.', 'srbtranslatin'); ?>
</td>
</tr>
</table>
<hr />

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'srbtranslatin') ?>" />
</p>

</form>
</div>

<?php
 
}

function stl_show_selector($p_selection_type = 'oneline', $p_oneline_separator = ' | ', $p_cirilica_title = 'ћирилица', $p_latinica_title = 'латиница', 
													  $p_inactive_script_only = false, $p_show_only_on_wpml_languages = '') {
	global $m_lang_identificator;
	global $m_default_language;
	global $stl_lang_cir_id;
	global $stl_lang_lat_id;

    if (isset ($_REQUEST[$m_lang_identificator])) {
			$m_current_language = $_REQUEST[$m_lang_identificator];
		} else {
  		$m_current_language = $m_default_language;
		}
		
		$m_show_only_on_wpml_languages = trim ($p_show_only_on_wpml_languages);
		$m_wpml_languages = explode(',', $m_show_only_on_wpml_languages);
		
		if (defined('ICL_LANGUAGE_CODE')) {
		  $m_ICL_LANGUAGE_CODE = ICL_LANGUAGE_CODE;
		} else {
			$m_ICL_LANGUAGE_CODE = '';
		}
		
//echo "m_ICL_LANGUAGE_CODE	= $m_ICL_LANGUAGE_CODE";
//print_r ($m_wpml_languages);
//echo in_array ($m_ICL_LANGUAGE_CODE, $m_wpml_languages);


		if ( empty ($m_show_only_on_wpml_languages)  || in_array ($m_ICL_LANGUAGE_CODE, $m_wpml_languages) ) {
		
			$m_cir_url = url_current_add_param ($m_lang_identificator. '=' . $stl_lang_cir_id, true);
			$m_lat_url = url_current_add_param ($m_lang_identificator . '=' . $stl_lang_lat_id, true);
		
			$m_show_cir = !$p_inactive_script_only || ($m_current_language != $stl_lang_cir_id);
			$m_show_lat = !$p_inactive_script_only || ($m_current_language != $stl_lang_lat_id);		

			switch ($p_selection_type) {
				case 'list':


?>
<form action="" method="post">
<select name="<?php echo $m_lang_identificator; ?>" id="lang" onchange="this.form.submit()">
<option value="<?php echo $stl_lang_cir_id; ?>" <?php echo $m_current_language==$stl_lang_cir_id ? 'selected="selected"' : '' ?>>[lang id="skip"]<?php echo $p_cirilica_title; ?>[/lang]</option>
<option value="<?php echo $stl_lang_lat_id; ?>" <?php echo $m_current_language==$stl_lang_lat_id ? 'selected="selected"' : '' ?>><?php echo $p_latinica_title; ?></option>
</select>
</form>
<?php
				break;

			case 'oneline':

?>
<p>
<?php
  if ($m_show_cir) {
?>
<a href="<?php echo $m_cir_url; ?>">[lang id="skip"]<?php echo $p_cirilica_title; ?>[/lang]</a>
<?php
  }
?>
<?php
  if (!$p_inactive_script_only) {
?>
<?php echo $p_oneline_separator; ?>
<?php
  }
?>
<?php
  if ($m_show_lat) {
?>
<a href="<?php echo $m_lat_url; ?>"><?php echo $p_latinica_title; ?></a>
<?php
  }
?>

</p>
<?php

				break;



			default:


?>
<ul>
<?php
  if ($m_show_cir) {
?>
<li><a href="<?php echo $m_cir_url; ?>">[lang id="skip"]<?php echo $p_cirilica_title; ?>[/lang]</a></li>
<?php
  }
?>
<?php
  if ($m_show_lat) {
?>
<li><a href="<?php echo $m_lat_url; ?>"><?php echo $p_latinica_title; ?></a></li>
<?php
  }
?>
</ul>

<?php
	
	  	} // switch
		} // if $m_show_only_on_wpml_languages


}

function show_in_footer() {
    echo '<div id="SrbTransLatinFooter" style="font-size: 0.8em; text-align:center;">Cyrilic to Latin transliteration provided by <a href="http://pedja.supurovic.net/projekti/srbtranslatin?lnkrel=plugin" target="_blank">SrbTransLatin</a></div>';
}

$wppSrbTransLatin = new SrbTransLatin;

?>