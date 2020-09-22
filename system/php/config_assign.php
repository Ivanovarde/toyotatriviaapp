<?php
//Neomedia Comunicacion

/*
 * --------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * --------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class. This allows you to set custom config items or override
 * any default config values found in the config.php file.  This can
 * be handy as it permits you to share one application between more then
 * one front controller file, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 *
 * NOTE: This feature can be used to run multiple EE "sites" using
 * the old style method.  Instead of individual variables you'll
 * set array indexes corresponding to them.
 *
 */

/*************************************
 * ASSIGN TO CONFIG
 /************************************/
// This array must be associative

$assign_to_config['global_vars'] = array(
	'multilang_site'				=> $config_vars['multilang_site']
	,'country_code'                 => $local_vars['country_code']
	, 'country_language'            => $local_vars['country_language']
	, 'country_code_iso'            => $local_vars['country_code_iso']
	, 'decimal_sep'                 => $local_vars['decimal_sep']
	, 'thousend_sep'                => $local_vars['thousend_sep']
	, 'date_time'                   => date('YmdHis')
	, 'current_year'                => date('Y')
	, 'ee_readable_date'			=> $local_vars['ee_readable_date']
	, 'ee_date'						=> $local_vars['ee_date']
	, 'ee_readable_date_time'		=> $local_vars['ee_readable_date_time']
	, 'ee_time'						=> $local_vars['ee_time']
	, 'ee_date_time'				=> $local_vars['ee_date_time']
	, 'user_agent'                  => $config_vars['user_agent']
	, 'server_protocol'             => $config_vars['server_protocol']
	, 'site_url_front'              => $config_vars['site_url_front']
	, 'active_url'                  => $config_vars['active_url']
	, 'a_url'                       => $config_vars['a_url']
	, 'page'                        => $config_vars['page']
	, 'current_page'                => $config_vars['current_page']
	, 'main_class'                  => $config_vars['main_class']
	, 'static_url'					=> $config_vars['static_url']
	, 'staticimg_url'				=> $config_vars['staticimg_url']
	, 'is_live_site'				=> $config_vars['is_live_site']
	, 'enable_online_user_tracking'	=> 'n'
	, 'enable_hit_tracking'			=> 'y'
	, 'enable_entry_view_tracking'	=> 'y'

);


// This array must be associative

?>
