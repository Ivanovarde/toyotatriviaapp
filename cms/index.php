<?php


include('php/config.php');

if(!Session::check_user_session() && getSegments(1) != '' && getSegments(1) != 'index'  && getSegments(1) != 'usuario'  && getSegments(1) != 'registro'){
	header('HTTP/1.0 401 Unauthorized');
	Server::redirect(Settings::get_globals('site_url') . '/');
}

//Log::l('index.php Session::check_user_session()', $_SESSION['u'], true);
Log::l('index.php Session::check_user_session()', Session::check_user_session(), false);
//exit;


if(getSegments(1) == 'usuario'){

	Settings::set_globals('auth_msg', $lang['user_not_verified']);
	Settings::set_globals('auth_class', 'fail');

	if(getSegments(2) != ''){

		$code = getSegments(2);

		$u = User::byAuthCode($code);

		Log::l('index $u', $u, false);

		if($u->id){

			$u->auth_code = '';
			$u->status = 1;
			$u->auth_date = date('Y-m-d H:i:s');

			if($u->save()){
				Settings::set_globals('auth_msg', $lang['user_verified']);
				Settings::set_globals('auth_class', 'success');
			}

		}else{

			Settings::set_globals('auth_msg', $lang['user_no_verification_code']);

		}

	}
}

// Process data
require('php/renderizer.php');

if (Settings::get_config('debug')){
	//exit;
}


// Display message if site is not enabled
if (!Settings::get_config('site_enabled')){
	die('Site is off');
	exit;
}


// Display content if site is enabled
if(Settings::get_config('html_compression_enabled') && !Settings::get_config('debug')){
	ob_start("ob_html_compress");
}

echo $html;

if(Settings::get_config('html_compression_enabled') && !Settings::get_config('debug')){
	ob_end_flush();
}

die();


