<?php
class Template {

	function __construct(){

	}


	public static function replace_list_record($a_elements, $template_record){

		$elements = $a_elements;
		$html = '';

		if(!is_array($elements)){
			$elements = array($elements);
		}

		foreach($elements as $element){

			$tmp = $template_record;

			foreach($element->table_fields as $var){

				switch($var){
					case 'status':
						$v = $element->$var ? 'Activo' : 'Inactivo';
						$tmp = set($var, $v, $tmp);
					break;
					case 'caller':
						$v = $element->$var ? 'SI' : 'NO';
						$tmp = set($var, $v, $tmp);
					break;
					case 'module':
						$module_values = array('Intro', 'Curso módulo 1', 'Curso módulo 2', 'Curso módulo 3', 'Curso módulo 4', 'Curso completado', 'Examen', 'Examen aprobado', 'Certificado');
						$tmp = set($var, $module_values[$element->$var], $tmp);
					break;
					default:
						$tmp = set($var, $element->$var, $tmp);
				}

			}

			$html .= $tmp;
		}

		return $html;
	}

	// $template_filename, $pos
	public static function get_content($template_path, $pos=''){

		//$template_file = str_replace(Functions::url_slash(Settings::get_config('folder'), 'l'), '', $template_path);
		//$template_file = str_replace(Settings::get_config('templates_path'), '', str_replace(Functions::url_slash(Settings::get_config('folder'), 'b'), '', $template_path) );

		$template_file_temp = explode('templates/', $template_path);
		$template_file = end($template_file_temp);

		$url_filename = Settings::get_globals('site_url') . '/' . Settings::get_config('templates_path') . $template_file;
		$filename = Settings::get_globals('site_path') . '/' . Settings::get_config('templates_path') . $template_file;

		if(Settings::get_config('debug')){
		//if(1==1){
			echo '<br>Templeate::get_content: pos: ' . $pos . '<br>';
			echo 'Templeate::get_content: template_path: ' . Settings::get_config('templates_path') . '<br>';
			echo 'Templeate::get_content: template_filename: ' . $template_path . '<br>';
			echo 'Templeate::get_content: template_file: ' . $template_file . '<br>';
			echo 'Templeate::get_content: url_filename: ' . $url_filename . '<br>';
			echo 'Templeate::get_content: filename: ' . $filename . '<br><br>';
		}

		if(file_exists($filename)){

			$opts = array('http'=>array('header' => "User-Agent:MyAgent/1.0\r\n"));
			$context = stream_context_create($opts);
			$html = file_get_contents($filename, false, $context);

			return $html;

		}else{

			Settings::set_globals('is_404', true);

			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

			if(Settings::get_config('debug')){
				echo 'Templeate::get_content file: ' . $template_file . ' <strong>NOT Found</strong><br><br>';
			}
		}
	}

	public static function swap_var($tag, $str, $source){

		if(!is_array($str)){
			return str_replace('{' . $tag . '}', $str, $source);
		}else{
			$first = true;
			foreach($str as $val){
				$return .= ($first === true ? ' ' : '') . $val;
				$first = false;
			}
			return str_replace('{' . $tag . '}', $return, $source);;
		}

	}

	public static function swap_system_vars($html, $last_call=false){

		$replaces = '';
		$a_system_vars = array_merge ( Settings::get_config(), Settings::get_globals());

		foreach($a_system_vars as $tag => $content){

			$tag = strip_tags(trim( str_replace(' ', '', $tag) ));
			$content = trim($content);
			$stripped_content = strip_tags($content);

			if(Settings::get_config('debug') && is_array($content)){
				echo '<br>============<br>';
				echo '<br>' . var_dump($content) . '<br>';
				echo '<br>============<br>';
			}

			$replaces .= 'Replace: ' . $tag . ' with: ' . ( $stripped_content == '' ? '(empty)' : $stripped_content ) . "<br>";

			if($last_call){
				$html = self::swap_var($tag, (Settings::get_globals($tag) !== null && Settings::get_globals($tag) != '' ? $content : ''), $html);
			}else{
				$html = self::swap_var($tag, $content, $html);
			}

		}

		if(Settings::get_config('debug')){
			//echo $replaces . "<br>";
		}

		return $html;
	}

}

?>
