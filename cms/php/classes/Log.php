<?php
class Log {

	static $output = false;

	public static function l($lbl, $var, $view=false) {
		if(self::$output || $view){
			//$log = FirePHP::getInstance(true);
			//$log->log($var, $lbl);

			ChromePhpWSE::setEnabled(true) ;
			$log = ChromePhpWSE::getInstance();
			$log->log( ($var === '' ? '(empty)' : $var), $lbl) ;
		}
	}

	public static function debugVars(){
		$vars = ' | siteId: ' . Site::$siteId .
		' | LanguageFile: ' . Frontend::getLanguageFile() .
		' | front: ' . (isset($_GET['f']) ? $_GET['f'] : '') .
		' | blog: ' . (isset($_GET['b']) ? $_GET['b'] : '') .
		' | query: ' . (isset($_GET['q']) ? $_GET['q'] : '') ;

		self::l('Log debugVars', $vars, true);

	}

}
?>
