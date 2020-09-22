<?php

global $global_vars;

function unifyFiles($a_files){
	if(!is_array($a_files)){
		return $a_files. "\n";
	}
	foreach($a_files){
		$r .= $a_files . "\n";
	}
	return $r;
}

//if($global_vars['minify_enabled']){

	//echo '<br><br>minify_path: ' . $global_vars['minify_path'] . '<br>Minify vars: ' . $global_vars['minify_path'] . '/minify/src/Minify.php<br><br>';
	//exit;

	require_once $global_vars['minify_path'] . '/minify/src/Minify.php';
	require_once $global_vars['minify_path'] . '/minify/src/CSS.php';
	require_once $global_vars['minify_path'] . '/minify/src/JS.php';
	require_once $global_vars['minify_path'] . '/minify/src/Exception.php';
	require_once $global_vars['minify_path'] . '/minify/src/Exceptions/BasicException.php';
	require_once $global_vars['minify_path'] . '/minify/src/Exceptions/FileImportException.php';
	require_once $global_vars['minify_path'] . '/minify/src/Exceptions/IOException.php';
	require_once $global_vars['minify_path'] . '/minify-path-converter/src/ConverterInterface.php';
	require_once $global_vars['minify_path'] . '/minify-path-converter/src/Converter.php';


	$a_external_js_files = array(
		//'https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'
		,'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js'
		,'http://malsup.github.com/jquery.cycle2.js')
		,"https://cdnjs.cloudflare.com/ajax/libs/jquery.simpleWeather/3.1.0/jquery.simpleWeather.min.js"
		,"https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.js"
	);

	$a_js_files = array(
		"/assets/scripts/library/iv-isMobile.js",
		"/assets/scripts/library/modernizr-2.6.2.min.js",
		"/assets/scripts/library/jquery-livequery-1.0.2.js",
		"/assets/scripts/library/jquery.cycle2.swipe.min.js",
		"/assets/scripts/library/jquery.easing.min.js",
		"/assets/scripts/library/plugins.js",
		"/assets/scripts/library/jquery-afterresize.js",
		"/assets/scripts/script.js"
	);


	$a_external_css_files = array(
		'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.5/jquery.fancybox.min.css'
		,'http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css'
		//,"https://fonts.googleapis.com/css?family=Lato:300,700"
	);

	$a_css_files = array(
		"/assets/stylesheets/library/font-awesome.min.css",
		"/assets/stylesheets/library/weather.css",
		"/assets/stylesheets/style.css",
		"/assets/stylesheets/ivano.css"
	);


	$external_js = unifyFiles($a_external_js_files);
	$external_css = unifyFiles($a_external_css_files);

	//==============================================================
	// CSS Files
	//==============================================================





	//==============================================================
	//==============================================================


	//==============================================================
	// JS Files
	//==============================================================




	$analytics_ga = file_get_contents('https://www.google-analytics.com/analytics.js');


	//==============================================================
	//==============================================================


	use MatthiasMullie\Minify;

	//==============================================================
	// CSS Process
	//==============================================================
	$min_css = new Minify\CSS($external_css);

	foreach($a_css_files as $css_file){
		$min_css->add($global_vars['site_path'] . $css_file);
		$global_vars['minify_css_request'] .= '"' . $css_file . '?v=' . $global_vars['minify_version'] . '">';
	}

	if($global_vars['minify_css_enabled']){
		$min_css->minify($global_vars['minify_cache_path'] . '/' . $global_vars['minify_css_filename']);
		$global_vars['minify_css_request'] = '"' . $global_vars['minify_cache_url'] . $global_vars['minify_css_filename']  . '?v=' . $global_vars['minify_version'] . '">';
	}else{
		$global_vars['minify_css_links'] = $global_vars['minify_css_request'];

		if(count($a_external_css) > 0){
			$global_vars['minify_css_links'] .= '<style>';
			foreach($a_external_css as $css){
				$global_vars['minify_css_links'] .= $css ;
			}
			$global_vars['minify_css_links'] .= '</style>';
		}
	}
	//==============================================================
	//==============================================================


	//==============================================================
	//JS Process
	//==============================================================
	$min_js = new Minify\JS($external_js);

	// Si minify no esta activo, agrego scripts externos
	if(!$global_vars['minify_js_enabled']){

		$a_all_files = $a_external_js_files + $a_js_files;

		foreach($a_all_files as $src){

			$global_vars['minify_js_request'] .= '<script  src="' .  $src . '?v=' . $global_vars['minify_version'] . '",' . "\n\r";

		}

	}else{

		$counter = 1;
		$total_external = count($a_external_js_files);
		$total_internal = count($a_js_files);
		$total = $total_external + $total_internal;

		foreach($a_js_files as $src){

			if($counter <= $total_external){
				$e .= file_get_contents($src) . "\n";
				$r = new Minify\CSS($e);
			}

			if($counter > $total_external){
				$r->add($global_vars['site_path'] . $src);
			}

		}

		$request_version = '?v=' . $global_vars['minify_version'] ;
		$request_filename = $global_vars['minify_' . $type . '_filename'] . $request_version;


		$a_type = array(
			'css' => '<link rel="stylesheet" href="' . $request_filename . '">',
			'js' => '<script" src="' . $request_filename . '"></script>'
		)


		$request = $a_type[$type];

		$r->minify($global_vars['minify_cache_path'] . '/' . $request_filename);
		$global_vars['minify_js_request'] = $request;

	}

	// Si minify no esta activo, agrego scripts externos
	if(!$global_vars['minify_js_enabled']){
		$global_vars['minify_js_request'] .= '<script  src="' .  $jquery_src . '?v=' . $global_vars['minify_version'] . '",' . "\n\r";
		$global_vars['minify_js_request'] .= '<script  src="' .  $jquery_ui_src . '?v=' . $global_vars['minify_version'] . '",' . "\n\r";
		$global_vars['minify_js_request'] .= '<script  src="' .  $jquery_simple_weather_src . '?v=' . $global_vars['minify_version'] . '",' . "\n\r";
	}

	foreach($a_js_files as $js_file){
		$min_js->add($global_vars['site_path'] . $js_file);
		$global_vars['minify_js_request'] .= '<script  src="' .  $js_file . '?v=' . $global_vars['minify_version'] . '",' . "\n\r";
	}

	if($global_vars['minify_js_enabled']){
		$min_js->minify($global_vars['minify_cache_path'] . '/' . $global_vars['minify_js_filename']);
		$global_vars['minify_js_request'] = '<script async src="' .  $global_vars['minify_cache_url'] . $global_vars['minify_js_filename'] . '?v=' . $global_vars['minify_version'] . '",';
	}
	//==============================================================
	//==============================================================

	// ANALYTICS
	//$minGA = new Minify\JS($analytics_ga);
	//$minGA->minify($global_vars['minify_cache_path'] . '/analytics_ga.js');
	//$global_vars['analytics_ga'] = '"' .  $global_vars['minify_cache_url'] . 'analytics_ga.js",';

	//echo $min_js->minify();
	//exit;

//}
