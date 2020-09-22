<?php
// assets/folder/current/php/config.php
// Ivano 03/2017

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Content-Type, X-Requested-With, Accept, Origin");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
//error_reporting(0);
//error_reporting(E_ALL);
@ini_set('display_errors', 1);

date_default_timezone_set('America/New_York');

// GLOBAL VARS
global $config, $global_vars, $global_content, $lang, $pages, $page_metatags;

$config					= array();
$global_vars			= array();
$company_vars			= array();
$social_vars			= array();
$geolocation_vars		= array();
$global_content		= array();
$lang						= array();
$pages					= array();
$page_metatags 		= array();
$a_merged_vars 		= array();
$social_image_props 	= array();


include_once('functions.php');


// SITE VARS
$config['debug'] = false;
$config['site_enabled'] = true;
$config['site_name'] = 'Mercedes-Benz Expo';
$config['domain_name'] = '';
$config['charset'] = 'utf-8';


//COMPANY DATA VARS
$company_vars['company_street'] = '';
$company_vars['company_city'] = '';
$company_vars['company_state'] = '';
$company_vars['company_zip'] = '';
$company_vars['company_phone'] = '';
$company_vars['company_email'] = 'noreply@mercedesappcms.com.ar';
$company_vars['booking-url-desktop'] = '';
$company_vars['booking-url-mobile'] = '';


// EMAIL VARS
$config['smtp_host'] = 'mercedesappcms.neomedia.com.ar';
$config['smtp_port'] = '465';
$config['smtp_username'] = 'noreply@mercedesappcms.neomedia.com.ar';
$config['smtp_password'] = 'm3rc3d3sb3nz#1';
$config['email_allow_bc'] = false;
$config['email_allow_bcc'] = false;
$config['company_email'] = $company_vars['company_email'];
$config['from_address'] = $company_vars['company_email'];
$config['from_name'] = $config['site_name'];
$config['to_address'] = '';
$config['to_name']	= '';
$config['bc_address'] = '';
$config['bc_name']	= '';
$config['bcc_address'] = '';
$config['bcc_name'] ='';

// EMAIL DEBUG VARS
$config['email_debug'] = false; // There is a local lever debug var in the EmailSimple Class
$config['from_address'] = $company_vars['company_email'];
$config['from_name'] = $config['site_name'];
$config['to_address_debug'] = "iv@neomedia.com.ar";
$config['to_name_debug'] = "Ivano NMD";
$config['bc_address_debug'] = '';
$config['bc_name_debug'] = '';
$config['bcc_address_debug'] = '';
$config['bcc_name_debug'] = '';


//SOCIAL VARS
$social_vars['application_type'] = 'website';
$social_vars['url_facebook'] = '';
$social_vars['url_twitter'] = '';
$social_vars['twitter_user'] = '';
$social_vars['url_instagram'] = '';
$social_vars['url_gplus'] = '';
$social_vars['url_social_image'] = '';


// GEOLOCATION VARS
$geolocation_vars['geo_lat'] = '';
$geolocation_vars['geo_lng'] = '';
$geolocation_vars['url_google_map'] = '';
$geolocation_vars['google_maps_api'] = '';
$geolocation_vars['google_maps_div'] = '';


// TEMPLATES VARS
$config['folder'] = 'cms';
$config['remove_folder'] = false;
$config['templates_path'] = 'assets/templates/';
$config['master_site_folder'] = 'master/';
$config['master_site_template'] = 'index';
$config['template_extension'] = 'html';


// COMPRESSION VARS
$config['html_compression_enabled'] = false;
$config['minify_enabled'] = false;
$config['minify_css_enabled'] = false;
$config['minify_js_enabled'] = false;
$config['minify_cache_folder'] = 'cache'; // relative to root. No slashes


// LOCALIZATION VARS
$config['languages'] = array('es'=>'Español'); // Allowed languages
$config['multilanguage'] = 	false;
$config['country_code_url_explicit'] = false;
$config['country_code_default'] = 'es';
$config['country_lang_default'] = 'Español';
$config['localization_filename_prefix'] = 'lang_';
$config['localization_path'] = 'assets/localization/';


// ASSETS FILES
$a_external_js_files = array(

);

$a_js_files = array(
	"/assets/scripts/library/jquery-3.3.1.min.js"
	,"/assets/scripts/library/popper.min.js"
	,"/assets/scripts/library/bootstrap.js"
	,"/assets/scripts/library/jquery-livequery-1.0.2.js"
	,"/assets/scripts/library/jquery.form.js"
	,"/assets/scripts/library/iv-common.6.5.js"
	,"/assets/scripts/library/iv-validator.3.2.js"
	,"/assets/scripts/library/iv-validator-regional-es.js"
	,"/assets/scripts/ivano.js"
);


$a_external_css_files = array(
);

$a_css_files = array(
	"/assets/stylesheets/library/font-awesome.css"
	,"/assets/stylesheets/library/bootstrap.css"
	,"/assets/stylesheets/library/awesome-bootstrap-checkbox.css"
	//,"/assets/stylesheets/library/jquery.autocomplete.css"
	//,"/assets/stylesheets/library/jquery.modal.css"
	,"/assets/stylesheets/ivano.css"
);


/***********************************************************************************/
/***********************************************************************************/
/* DONT EDIT BEFORE THIS LINE */
/***********************************************************************************/
/***********************************************************************************/
/***********************************************************************************/

// FirePHP Console
require_once('classes/ChromePhpWSE.php');

// START SESSION
Session::start(60*60);
$logged_user_fields = isset($_SESSION['logged_user_fields']) ? $_SESSION['logged_user_fields'] : array();
Log::l('config.php $_SESSION["u"]', $logged_user_fields, false);

// ADD VARS INTO GLOBALS
Log::l('config: $logged_user_fields ', $logged_user_fields, false);
$a_merged_vars = array_merge($company_vars, $social_vars, $geolocation_vars, $logged_user_fields);

foreach($a_merged_vars as $merged_var => $merged_value){
	$global_vars[$merged_var] = $merged_value;
}
Log::l('config $global_vars', $global_vars, false);


// SYSTEM VARS
$global_vars['server_protocol'] = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$global_vars['site_path'] = $_SERVER['DOCUMENT_ROOT'] . sep($config['folder'], 'l');
$global_vars['site_url'] =  $global_vars['server_protocol'] . $_SERVER['SERVER_NAME'] . sep($config['folder'], 'l');
$global_vars['templates_path'] = sep($config['folder'], 'r') . $config['templates_path'];

$global_vars['localization_path'] =  sep($config['folder'], 'r') . $config['localization_path'];
$global_vars['country_code_in_url'] = checkLanguageSegment();
$global_vars['localization_filename_default'] = $config['localization_filename_prefix'] . $config['country_code_default'] . '.php';
$global_vars['country_code'] = checkLanguageSegment() ? getSegments(1) : $config['country_code_default'];
$global_vars['localization_file'] = $config['localization_filename_prefix'] . $global_vars['country_code'] . '.php';
$global_vars['localization_filename'] = $global_vars['localization_path'] . $global_vars['localization_file'];
$global_vars['localization_filename_path'] = $global_vars['site_path'] . '/' . $global_vars['localization_filename'];

$global_vars['site_url_front'] = $global_vars['site_url'] . ($global_vars['country_code_in_url'] || $config['country_code_url_explicit'] ? '/' . $global_vars['country_code'] . '/' : '/');
$global_vars['site_url_encoded'] = urlencode($global_vars['site_url']);
$global_vars['site_url_front_encoded'] = urlencode($global_vars['site_url_front']);
$global_vars['last_segment'] = getLastSegment();
$global_vars['selfname'] = getSelfname();

$global_vars['section'] = $global_vars['selfname'];

//$global_vars['section'] = !getSegments(1, true) || ( getSegments(1, true) == $config['folder'] && $global_vars['last_segment'] == getSegments(1, true)) ? 'index' : getSegments(1, true);


$global_vars['extension'] = $config['template_extension'];
$global_vars['request_uri'] = trim($_SERVER['REQUEST_URI'], '\/') == $config['folder'] ? '' : $_SERVER['REQUEST_URI'];
$global_vars['current_url'] = $global_vars['site_url'] . $global_vars['request_uri'] . ($global_vars['request_uri'] ? '' : '/');
$global_vars['folder'] = $config['folder'];
$global_vars['is_404'] = false;
$global_vars['is_live'] = ($_SERVER['SERVER_NAME'] == $config['domain_name']) ? true : false;

for($i = 1; $i < count(getSegments()) + 1; $i++){
	$global_vars['segment_' . $i] = getSegments($i);
}

// TEMPLATE VARS
$gloabl_vars['html'] = '';
$gloabl_vars['master_structure_site_template'] = removeFolder($global_vars['site_url'], 'l') . '/' . $config['templates_path'] . $config['master_site_folder'] . $config['master_site_template'] . '.' . $global_vars['extension'];
$global_vars['section_filename'] = sep($config['folder'], 'r') . $config['templates_path'] . 'section/' . ( ($global_vars['last_segment'] != $global_vars['section'] ) && ($global_vars['section'] != 'index' && $global_vars['section'] != 'usuario')  ? $global_vars['last_segment'] : $global_vars['section']) . '.' . $global_vars['extension'];
$global_vars['404_template'] = 'section/404.' . $global_vars['extension'];

// MINIFY VARS
$minify_files['external_js_files'] = $a_external_js_files;
$minify_files['js_files'] = $a_js_files;
$minify_files['external_css_files'] = $a_external_css_files;
$minify_files['css_files'] = $a_css_files;

$global_vars['minify_enabled'] = $config['minify_enabled'];
$global_vars['minify_css_enabled'] = $config['minify_css_enabled'];
$global_vars['minify_js_enabled'] = $config['minify_js_enabled'];
$global_vars['minify_path'] = $global_vars['site_path'] . '/php/ivmin';
$global_vars['minify_cache_path'] = $global_vars['site_path'] . '/' . $config['minify_cache_folder'];
$global_vars['minify_cache_url'] = $global_vars['site_url'] . '/' . $config['minify_cache_folder'] . '/';
$global_vars['minify_js_filename'] = 'minify.js';
$global_vars['minify_css_filename'] = 'minify.css';
$global_vars['minify_version'] = 1;
$global_vars['minify_js_request'] = getAssets('js');
$global_vars['minify_css_request'] = getAssets('css');

$global_vars['device_type'] = 'desktop';

// Add language selector (in case the site has more than one)
/*************************************************/
$global_vars['lang_selector'] = makeLanguageSelector();


// DATE VARS
$global_vars['time_stamp'] = date('YmdHis');
$global_vars['year'] = date('Y');
$global_vars['current_year'] = date('Y');
$global_vars['time'] = date('H:i s');
$global_vars['date_time'] = 'd-m-Y, H:i s';
$global_vars['date_today'] = date('Y-m-d');
$tomorrow = new DateTime('tomorrow');
$global_vars['date_tomorrow'] = $tomorrow->format('Y-m-d');

setlocale(LC_ALL,"es_ES");
$string = date('d/m/Y');
$date = DateTime::createFromFormat("d/m/Y", $string);
$global_vars['date_today_es'] = strftime("%e de %B de %Y", $date->getTimestamp());


// SITE VARS
$global_vars['site_name'] = $config['site_name'];
$global_vars['charset'] = $config['charset'];
$global_vars['main_class'] = ($global_vars['section'] == 'index') ? 'home' : $global_vars['section'];


//SOCIAL TAGS
$global_vars['social_image_width'] = '';
$global_vars['social_image_height'] = '';
$global_vars['social_image_type'] = '';
$global_vars['social_image'] = $global_vars['site_url']  . $global_vars['url_social_image'];

if(file_exists($global_vars['social_image'])){
	$social_image_props = getimagesize($global_vars['social_image']);
	$global_vars['social_image_width'] = $social_image_props[0];
	$global_vars['social_image_height'] = $social_image_props[1];
	$global_vars['social_image_type'] = $social_image_props['mime'];
}


// GEOLOCATION
$global_vars['geo_latlng'] = $global_vars['geo_lat'] . ',' . $global_vars['geo_lng'];

$global_vars['study_test'] = 'examen-' . rand(1, 2);

//Log::l($global_vars, 'ver', true);
