<?php
class Server {

	const FOLDER_ASSETS = 								'assets';
	const FOLDER_SCRIPTS = 								'scripts';
	const FOLDER_STYLESHEETS = 						'stylesheets';
	const FOLDER_ASSETS_IMAGES = 						'';
	const FOLDER_IMAGES = 								'images';
	const FOLDER_SWF = 									'';
	const FOLDER_FILES = 								'';
	const FOLDER_ADM = 									'';
	const FOLDER_ASSETS_IMAGESADM = 					'';
	const FOLDER_CACHE = 								'';

	public static  $protocol;
	public static  $site_url;
	public static  $site_url_assets;
	public static  $site_url_scripts;
	public static  $site_url_stylesheets;
	public static  $site_url_assets_images;
	public static  $site_url_images;
	public static  $site_url_files;
	public static  $site_urlFront;
	public static  $site_url_adm;
	public static  $site_url_admFront;
	public static  $site_url_adm_images;
	public static  $site_url_cache;
	public static  $self;
	public static  $current_url;

	public static  $allowStaticDNS =			false;
	public static  $dnsScripts;
	public static  $dnsImages;


	public static function setServerUrls(){

		// URL vars
		self::$protocol = self::getServerProtocol();
		self::$site_url =  self::getServerProtocol() . self::getServerName() . "/";
		self::$site_url_assets = self::$site_url . self::FOLDER_ASSETS . '/';
		self::$site_url_scripts = self::$site_url_assets . self::FOLDER_SCRIPTS . '/';
		self::$site_url_stylesheets = self::$site_url_assets . self::FOLDER_STYLESHEETS . '/';
		self::$site_url_assets_images = self::$site_url_assets . self::FOLDER_ASSETS_IMAGES . '/';
		self::$site_url_images = self::$site_url_assets . self::FOLDER_IMAGES . '/';
		self::$site_url_files = self::$site_url . self::FOLDER_FILES . '/';
		self::$site_url_cache = self::$site_url . self::FOLDER_CACHE . '/';

		self::$site_url_adm = self::$site_url . self::FOLDER_ADM . '/';
		self::$site_url_adm_images = self::$site_url_assets . self::FOLDER_ASSETS_IMAGESADM . '/';

		self::$selfname = end(explode('/', substr(self::getScriptName(), 1, -4)));
		self::$current_url = substr(self::$site_url,0, strlen(self::$site_url)-1) . self::getRequestUri();

		// DNS vars
		if(self::$allowStaticDNS){
			self::$dnsScripts = self::getServerProtocol() . 's.' . self::getServerName() . '/';// . self::FOLDER_ASSETS . '/';
			self::$dnsImages = self::getServerProtocol() . 'i.' . self::getServerName() . '/';// . self::FOLDER_ASSETS . '/';
		}else{
			self::$dnsScripts = self::getServerProtocol() . '' . self::getServerName() . '/';// . self::FOLDER_ASSETS . '/';
			self::$dnsImages = self::getServerProtocol() . '' . self::getServerName() . '/';// . self::FOLDER_ASSETS . '/';
		}
	}

	public static function showServerVars(){
		foreach($_SERVER as $k=>$v){
			echo 'Server | k: '.$k.' | v: '.$v.'<br/>';
		}
	}

	public static function getServerVar($var){
		return $_SERVER[$var];
	}

	public static function redirect($url){
		header("Location: " . $url);
		exit;
	}

	public static function getIP(){
		return $_SERVER['REMOTE_ADDR'];
	}

	public static function getCurrentURL(){
		return self::$current_url;
	}

	public static function getServerProtocol(){
		return (!empty($_SERVER['HTTPS'])) ? "https://" : "http://";
	}

	public static function getServerName(){
		return $_SERVER['SERVER_NAME'];
	}

	public static function getRequestUri(){
		return $_SERVER['REQUEST_URI'];
	}

	public static function getScriptName(){
		return $_SERVER['SCRIPT_NAME'];
	}

	public static function getQueryString(){
		return $_SERVER['QUERY_STRING'];
	}

	public static function getDocumentRoot(){
		return $_SERVER['DOCUMENT_ROOT'];
	}

	public static function getRelativeRootPath(){
		$relpath = "";
		$tempvar_relpathdir = explode("/", dirname($_SERVER['PHP_SELF']));
		for($i = count($tempvar_relpathdir); $i > 0; $i--){
			if(isset($tempvar_relpathdir[$i])){
				if($tempvar_relpathdir[$i] != ''){
					$relpath .= "../";
				}
			}
		}
		return $relpath;
	}
}
?>
