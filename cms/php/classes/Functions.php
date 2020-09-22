<?php
Class Functions {

	public function hash_password($password, $salt = FALSE, $h_byte_size = FALSE){

		// Even for md5, collisions usually happen above 1024 bits, so
		// we artifically limit their password to reasonable size.
		if ( ! $password OR strlen($password) > 250)
		{
			return FALSE;
		}

		// No hash function specified? Use the best one
		// we have access to in this environment.
		if ($h_byte_size === FALSE)
		{
			reset($this->hash_algos);
			$h_byte_size = key($this->hash_algos);
		}
		elseif ( ! isset($this->hash_algos[$h_byte_size]))
		{
			// What are they feeding us? This can happen if
			// they move servers and the new environment is
			// less secure. Nothing we can do but fail. Hard.

			die('Fatal Error: No matching hash algorithm.');
		}

		// No salt? (not even blank), we'll regenerate
		if ($salt === FALSE)
		{
			$salt = '';

			// The salt should never be displayed, so any
			// visible ascii character is fair game.
			for ($i = 0; $i < $h_byte_size; $i++)
			{
				$salt .= chr(mt_rand(33, 126));
			}
		}
		elseif (strlen($salt) !== $h_byte_size)
		{
			// they passed us a salt that isn't the right length,
			// this can happen if old code resets a new password
			// ignore it
			$salt = '';
		}

		return array(
			'salt'		=> $salt,
			'password'	=> hash($this->hash_algos[$h_byte_size], $salt.$password)
		);
	}

	public static function random_string($type = 'alnum', $len = 8){
		switch($type){
			case 'basic'	: return mt_rand();
				break;
			case 'alnum'	:
			case 'numeric'	:
			case 'nozero'	:
			case 'alpha'	:

				switch ($type){
					case 'alpha'	:	$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'alnum'	:	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric'	:	$pool = '0123456789';
						break;
					case 'nozero'	:	$pool = '123456789';
						break;
				}

				$str = '';
				for ($i=0; $i < $len; $i++){
					$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
				return $str;
				break;
			case 'unique'	:
			case 'md5'		:
				return md5(uniqid(mt_rand()));
				break;
			case 'encrypt'	:
			case 'sha1'	:
				//$CI =& get_instance();
				//$CI->load->helper('security');

				return sha1(uniqid(mt_rand(), TRUE));
				break;
		}
	}

	public static function url_slash($string, $location='l'){
		$slash = '/';
		$result = '';

		if(empty($string)){
			return '';
		}

		switch($location){
			case 'l':
				$result = $slash . trim($string, '\/');
			break;

			case 'r':
				$result = trim($string, '\/') . $slash;
			break;

			case 'b':
				$result = $slash . trim($string, '\/') . $slash;
			break;

		}

		return $result;
	}

	public static function filename_security($str){
		$bad = array(
						"../",
						"./",
						"<!--",
						"-->",
						"<",
						">",
						"'",
						'"',
						'&',
						'$',
						'#',
						'{',
						'}',
						'[',
						']',
						'=',
						';',
						'?',
						'/',
						"%20",
						"%22",
						"%3c",		// <
						"%253c", 	// <
						"%3e", 		// >
						"%0e", 		// >
						"%28", 		// (
						"%29", 		// )
						"%2528", 	// (
						"%26", 		// &
						"%24", 		// $
						"%3f", 		// ?
						"%3b", 		// ;
						"%3d"		// =
					  );


		$str =  stripslashes(str_replace($bad, '', $str));

		return $str;
	}

	public static function normalize($string){
	$table = array(
		'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'s',
		'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y',
		'þ'=>'b', 'ÿ'=>'y', 'R'=>'R', 'ñ'=>'n', 'Ñ'=>'n', '©'=>'c', '®'=>'r'
	);

	return strtr(utf8_decode($string), $table);
}

	public static function safe_url($url, $limit='') {

		$url = self::normalize($url);

		// Tranformamos todo a minusculas
		$url = strtolower($url);

		//Rememplazamos caracteres especiales latinos
		//$find = array('á', 'é', 'í', 'ó', 'ú', 'ñ');
		//$repl = array('a', 'e', 'i', 'o', 'u', 'n');
		//$url = str_replace ($find, $repl, $url);

		// Añadimos los guiones
		$find = array(' ', '&', '\r\n', '\n', '+');
		$url = str_replace ($find, '-', $url);

		// Eliminamos y Reemplazamos demás caracteres especiales
		$find = array('/[^a-z0-9\:\.\/\-<>]/', '/[\-]+/', '/<[^>]*>/');
		$repl = array('', '-', '');
		$url = preg_replace ($find, $repl, $url);

		return substr($url, 0, ($limit != '' ? $limit : strlen($url)));
	}

	/**
	 * Toma el valor de cada posicion del array y le aplica las funciones trim y strip_tags
	 *
	 * @desc Toma el valor de cada posicion del array y le aplica las funciones trim y strip_tags
	 * @param Array $array
	 * @return Array
	 */
	public static function array_sanitize($array){

			$a = self::arrayStripTags($array);
			$a = self::arrayTrim($a);
			return $a;

	}

	/**
	 * Toma el valor de cada posicion del array y le aplica la funcion utf8_encode
	 *
	 * @desc Toma el valor de cada posicion del array y le aplica la funcion utf8_encode
	 * @param Array $array
	 * @return Array
	 */
	public static function arrayEncodeUTF8($array){
		return is_array($array) ?
			array_map('self::arrayEncodeUTF8', $array) :
			utf8_encode($array);
	}

	/**
	 * Toma el valor de cada posicion del array y le aplica la funcion utf8_decode
	 *
	 * @desc Toma el valor de cada posicion del array y le aplica la funcion utf8_decode
	 * @param Array $array
	 * @return Array
	 */
	public static function arrayDecodeUTF8($array){
		return is_array($array) ?
			array_map('self::arrayDecodeUTF8', $array) :
			utf8_decode($array);
	}

	/**
	 * Toma el valor de cada posicion del array y le aplica la funcion trim
	 *
	 * @desc Toma el valor de cada posicion del array y le aplica la funcion trim
	 * @param Array $array
	 * @return Array
	 */
	public static function arrayTrim($array){
		return is_array($array) ?
			array_map('self::arrayTrim', $array) :
			trim($array);
	}

	/**
	 * Toma el valor de cada posicion del array y le aplica la funcion strip_tags
	 *
	 * @desc Toma el valor de cada posicion del array y le aplica la funcion strip_tags
	 * @param Array $array
	 * @return Array
	 */
	public static function arrayStripTags($array){
		return is_array($array) ?
			array_map('self::arrayStripTags', $array) :
			strip_tags($array);
	}

	/**
	 * @desc Detect if an integer is even or odd
	 * @param int $num
	 *
	 * @return true if even, false if odd
	 */
	public static function isEven($num){
		if(($num % 2) == 0){
			return true;
		}
		return false;
	}

	/**
	 * Busca un valor en un array sin necesidad de que sea exactamente igual.
	 * El concepto es parecido al LIKE de mysql
	 * @param string $reference
	 * @param array $array
	 * @return Devuelve true si el valor es encontrado, sino devuelve false
	 */
	public static function in_array_like($reference,$array){
		foreach($array as $ref){
			if (strstr($ref,$reference)){
				return true;
			}
		}
		return false;
	}

	/**
	 * Toma el valor de cada posicion del array y le aplica la funcion strip_tags
	 *
	 * @desc Toma el valor de cada posicion del array y le aplica la funcion strip_tags
	 * @param Array $array
	 * @return Array
	 */
	public static function arrayHTMLEntities($array){
		return is_array($array) ?
			array_map('self::arrayHTMLEntities', $array) :
			htmlentities($array);
	}

	public static function JSshowMessageFromPHP($msg){
		$script = "<script type=\"text/javascript\">\n" .
					" window.parent.showMessage('" . $msg . "', 'error');\n" .
					"</script>";

		return $script;
	}

	public static function JSredirectFromPHP($url){
		$script = "<script type=\"text/javascript\">\n" .
					" window.parent.location = '" . $url . "';\n" .
					"</script>";

		return $script;
	}
}
