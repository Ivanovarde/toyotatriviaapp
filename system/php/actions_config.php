<?php
// []/php/config_actions.php
// Ivano 06/2019

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, X-Requested-With, Accept, Origin");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
//error_reporting(0);
//error_reporting(E_ALL);
@ini_set('display_errors', 1);


function __autoload($class) {
	include(dirname(__FILE__) . "/classes/" . $class . ".php");
}


date_default_timezone_set('America/New_York');


//setlocale(LC_ALL, "es_ES");
//$string = date('d/m/Y');
//$date = DateTime::createFromFormat("d/m/Y", $string);

//// GLOBAL VARS
//global $config, $global_vars, $global_content, $lang, $pages, $page_metatags;
//
//$config					= array();
//$global_vars			= array();
//$company_vars			= array();
//$social_vars			= array();
//$geolocation_vars		= array();
//$global_content		= array();
//$lang						= array();
//$pages					= array();
//$page_metatags 		= array();
//$a_merged_vars 		= array();
//$social_image_props 	= array();


// SITE VARS
//$config['debug'] = false;




