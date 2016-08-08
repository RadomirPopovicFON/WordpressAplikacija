<?php
/********************************************************************
 *
 *  Project: URL TOOL
 *  Version: 1.0.3
 *  
 *  The goal of this library is to provide some URL 
 *  (universal resource locator) manipultion tools
 *
 *  This is free software. You may use it and redistribute it. You 
 *  may change code to suit your needs but you may not distribute 
 *  changed code.
 *
 *  Author: Predrag Supurovic
 *
 *  (c)2004 Copyright by DataVoyage, http://www.datavoyage.com/
 *
/********************************************************************/



//
// function url_add_param ($p_url, $p_param, $p_replace)
// Adds specified list of parameters to specified url. If url already contains parameter this will change it's value.
// $p_url may be in URI format
// $p_param contains list of parameters as string in URI format (param1=value&param2=value2&param3=value3"
//
// Demo:
// echo url_add_param ("index.php?i=s&m=8&lat=po", "cyr=eee&lat=fff&uh=12");
//
function url_add_param ($p_url, $p_param, $p_replace = false) {

  $m_param_list = preg_split ("/&/", $p_param);

  foreach ($m_param_list as $m_param_item) {

    $m_param_list_val = preg_split ("/=/", $m_param_item);

	$m_pattern = "/($m_param_list_val[0])=([^&]+)/";

	if (preg_match ($m_pattern, $p_url)) {
		if ($p_replace == true) {
       			$p_url = preg_replace ($m_pattern, "$1=$m_param_list_val[1]", $p_url);
		}
	} else {
	  if (preg_match ("/\?/", $p_url)) {
	    $p_url .= '&' . $m_param_item;
	  } else {
	    $p_url .= '?' . $m_param_item;
	  }
	}
  }
  return $p_url;
}

//
// function url_current_add_param ($p_param, $p_replace = false)
// Adds specified list of parameters to curent url ([REQUEST_URI])
// It calls url_add_param () 
//
// Demo:
// echo url_current_add_param ("cyr=eee&lat=fff&uh=12"); 
//
function url_current_add_param ($p_param, $p_replace = false) {
  $m_url = url_add_param ("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $p_param, $p_replace);
  return $m_url;
}

function url_clean_param ($p_url, $p_param) {
  $m_url = $p_url;

	$m_new_url = preg_replace('/([?&])' . $p_param. '=[^&]+(&|$)/','$1',$m_url);
	$m_new_url = preg_replace('/\?$/', '', $m_new_url);

  return $m_new_url;
}


function url_current_clean_param ($p_param) {
  $m_url = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];

	$m_new_url = preg_replace('/([?&])' . $p_param. '=[^&]+(&|$)/','$1',$m_url);
	$m_new_url = preg_replace('/\?$/', '', $m_new_url);

  return $m_new_url;
}


function url_get_current () {
  $m_url = "http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI'];
  return $m_url;
}

?>