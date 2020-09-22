<?php

//echo '<br>****************************<br>config_path<br>****************************<br>';


// Novecentoweb
// ------------------------------------------------------
// DO NOT ALTER THIS FILE UNLESS YOU HAVE A REASON TO

// ------------------------------------------------------
// Path to the directory containing your backend files

$system_path = $_SERVER['DOCUMENT_ROOT'] . '/system/admin/ee-2101';

// ------------------------------------------------------
// MANUALLY CONFIGURABLE VARIABLES
// See user guide for more information
// ------------------------------------------------------

//include_once('../config.php');

require('config_site.php');

//$debug = false;


// Paths para el server
$config_vars['multilang_site'] = 	true;
$config_vars['user_agent'] =        $_SERVER['HTTP_USER_AGENT'];
$config_vars['server_protocol'] =   (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$config_vars['site_url'] =          $config_vars['server_protocol'] . $_SERVER['SERVER_NAME'];
//$config_vars['site_url_front'] =    $config_vars['site_url'] . ($config_vars['multilang_site'] ? '/' . $local_vars['country_code'] : '');
$config_vars['site_url_front'] =    $config_vars['site_url'];
$config_vars['active_url'] =       rtrim($config_vars['site_url'] . $_SERVER['REQUEST_URI'], '\/') . '/';
$config_vars['a_url'] =             explode('/', trim($_SERVER['REQUEST_URI'], '\/'));
$config_vars['page'] =              (strlen($config_vars['a_url'][0]) > 2 ? $config_vars['a_url'][0] : (isset($config_vars['a_url'][1]) ? $config_vars['a_url'][1] : ''));
$config_vars['current_page'] =      (empty($config_vars['page'])) ? 'index' : $config_vars['page'];
$config_vars['main_class'] =        end($config_vars['a_url']);

$config_vars['static_url'] = $config_vars['server_protocol'] . $config_site['static_subdomain'] . '/assets';
$config_vars['staticimg_url'] = $config_vars['server_protocol'] . $config_site['staticimg_subdomain'];
$config_vars['is_live_site'] = $config_site['is_live_site'];


//echo '<div style="display: none;">' . $config_vars['facebook_comments_url'] . '</div>';


//$detect = new Mobile_Detect;
//$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'desktop');

/*INCLUDE EXTERNAL PROCCESS*/
/**/


/*************************************
 * GLOBAL VARS
 /************************************/
include('config_assign.php');
/*


/*************************************
 * LOCALIZATION
 /************************************/
//if($country_code != ''){
//
//	if(file_exists($localization_path . $localization_filename)){
//		$localization_file = $localization_path . $localization_filename;
//
//		include($localization_file);
//
//		foreach($lang as $var => $val){
//			foreach($global_vars as $k => $v){
//				$val = str_replace('{' . $k . '}', $v, $val);
//			}
//			$global_vars[$var] = $val;
//		}
//	}
//}
/************************************/




/*************************************
 * DEBUG
 /************************************/
//if($debug){
//	foreach($global_vars as $variableName => $variableVal){
//		echo 'Nombre: <strong>' . $variableName . '</strong><br>Contenido: ' . $variableVal . '<br><br>-------------------------------------------------------<br>';
//	}
//	exit;
//}
/************************************/

?>
