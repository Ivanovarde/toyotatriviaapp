<?PHP
class Settings{

	public static $vars;

	public function __construct(){
		global $config, $global_vars;

		$this->config = $config;
		$this->global_vars = $global_vars;

	}

	public static function get_config($var='', $location=''){
		global $config;

		if(!$var){
			return $config;
		}
		//echo $location . ' - ' . $var . '<br>';
		return $config[$var];
	}

	public static function set_config($var, $value){
		global $config;

		if(!$var){
			return;
		}

		$config[$var] = $value;
	}

	public static function get_globals($var=''){
		global $global_vars;

		if(!$var){
			return $global_vars;
		}

		return $global_vars[$var];
	}

	public static function set_globals($var, $value){
		global $global_vars;

		if(!$var){
			return;
		}

		$global_vars[$var] = $value;
	}
}
