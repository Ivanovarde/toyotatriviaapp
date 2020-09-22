<?php
class Template {

	function __construct(){

	}


	public static function swap_member_values($members, $raw){

		$elements = $members;
		$html = '';

		if(!is_array($elements)){
			$elements = array($elements);
		}

		Log::l('Template replace_list_record: ', $elements[0]->member_fields[0]->m_field_name, false);
		Log::l('Template replace_list_record: ', $elements[0], false);

		foreach($elements as $member){

			$tmp = $raw;

			Log::l('Template replace_list_record: ', $member, false);

			foreach($member as $key => $value){

				if(!is_array($value) && !is_object($value)){
					Log::l('Template replace_list_record: ', 'Campo: ' . $key . ' = ' . $value, false);

					if($key == 'join_date'){
						$value = date('d-m-Y', $value);
					}

					$tmp = self::swap_var($key, $value, $tmp);
				}

			}

			// Member fields / Data Mapping
			foreach($member->member_fields_relationships as $field_name => $field_id){

				$current_value = $member->member_data->{$field_id};

				if($field_name == 'envio'){
					$current_value = $current_value != 1 ? 'NO' : 'SI';
				}
				if($field_name == 'envio_fecha'){
					$current_value = $current_value != '' ? date('d-m-Y', $current_value) : '-';
				}


				$tmp = self::swap_var($field_name, $current_value, $tmp);

			}

			$html .= $tmp;

		}

		return $html;
	}

	public static function replace_list_record($a_elements, $template_record){

		$elements = $a_elements;
		$html = '';

		if(!is_array($elements)){
			$elements = array($elements);
		}

		Log::l('Template replace_list_record: ', $elements[0]->member_fields[0]->m_field_name, false);
		Log::l('Template replace_list_record: ', $elements[0], false);

		foreach($elements as $element){

			$tmp = $template_record;

			Log::l('Template replace_list_record: ', $element, false);

			foreach($element as $key => $value){

				if(!is_array($value) && !is_object($value)){
					Log::l('Template replace_list_record: ', 'Campo: ' . $key . ' = ' . $value, false);

					if($key == 'join_date'){
						$value = date('d-m-Y', $value);
					}
					$tmp = self::swap_var($key, $value, $tmp);
				}

			}

			// Member fields / Data
			foreach($element->member_fields as $field){
				$fid = $field->m_field_id;
				$fname = $field->m_field_name;
				$fdata_key = 'm_field_id_' . $fid;

				Log::l('Template replace_list_record: ', 'Campo: ' . $fname . ' = ' . $element->member_data->{$fdata_key}, false);
				Log::l('Template replace_list_record: ',$element->member_data, false);

				//if($element->member_data->{$fdata_key} != ''){
				$current_value = $element->member_data->{$fdata_key};

				if($fname == 'envio'){
					$current_value = $current_value != 'SI' ? 'NO' : 'SI';
				}
				if($fname == 'envio_fecha'){
					$current_value = $current_value != '' ? date($current_value, 'd-m-Y') : '-';
				}

				$tmp = self::swap_var($fname, $current_value, $tmp);
				//}

			}

			$html .= $tmp;

		}

		return $html;
	}

	// $template_filename, $pos
	public static function get_content($template_path){

		$template_file = str_replace(Functions::url_slash(Settings::get_config('folder'), 'l'), '', $template_path);
		$template_file = str_replace(Settings::get_config('templates_path'), '', str_replace(Functions::url_slash(Settings::get_config('folder'), 'b'), '', $template_path) );

		$url_filename = Settings::get_globals('site_url') . '/' . Settings::get_config('templates_path') . $template_file;
		$filename = Settings::get_globals('site_path') . '/' . Settings::get_config('templates_path') . $template_file;

		if(Settings::get_config('debug')){
			echo '<br>getContent: pos: ' . $pos . '<br>';
			echo 'getContent: template_path: ' . Settings::get_config('templates_path') . '<br>';
			echo 'getContent: template_filename: ' . $template_path . '<br>';
			echo 'getContent: template_file: ' . $template_file . '<br>';
			echo 'getContent: url_filename: ' . $url_filename . '<br>';
			echo 'getContent: filename: ' . $filename . '<br><br>';
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
				echo 'getContent file: ' . $template_file . ' <strong>NOT Found</strong><br><br>';
			}
		}
	}

	public static function swap_var($tag, $str, $source){

		if(!is_array($str)){
			return str_replace('{' . $tag . '}', $str, $source);
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

	public static function swap_lang_vars($html, $last_call=false){
		global $lang, $config;

		$a_lang_vars = Settings::get_lang();

		$replaces = '';

		if(Settings::get_config('debug')){
			if(count($a_lang_vars) > 0){
				echo 'fn swap_lang_vars: Start lang vars swap<br>=========================<br>';
			}else{
				echo 'fn swap_lang_vars: lang is empty<br>=========================<br>';
			}
		}


		foreach($lang as $var => $val){

			$replaces .= 'Replace: ' . $var . ' with: ' . (strip_tags($val) == '' ? '(empty)' : strip_tags($val)) . "<br>";

			$html = self::swap_var($var, $val, $html);
		}

		if($config['debug']){
			echo $replaces . "<br>";
		}

		return $html;
	}

}

?>
