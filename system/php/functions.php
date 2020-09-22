<?php
// []/php/config_actions.php
// Ivano 06/2019

//echo dirname(__FILE__) . "/classes/";

// FUNCTIONS
/*************************************************/
function __autoload($class) {
	include(dirname(__FILE__) . "/classes/" . $class . ".php");
}

function getAssets($type, $cssglue=''){

	global $global_vars, $config, $minify_files;

	$request = array();
	$request_version = '?v=' . $global_vars['minify_version'] ;
	$request_filename = $global_vars['minify_' . $type . '_filename'];
	$request_filename_min = $global_vars['minify_' . $type . '_filename'];

	$a_all_files = array_merge($minify_files['external_' . $type . '_files'], $minify_files[ $type . '_files']);

	$a_type = array(
		'css' => array(
			'open' => '<link rel="stylesheet" href="',
			'close' => $request_version . '">',
			'glue' => ($cssglue == '' ? "\n" : $cssglue)
		),
		'js' => array(
			'open' =>  '<script src="',
			'close' => $request_version . '"></script>',
			'glue' => "\n"
		)
	);


	// Si minify no esta activo, agrego scripts externos
	if(!$global_vars['minify_' . $type . '_enabled'] || !$config['minify_enabled']){

		foreach($a_all_files as $src){

			array_push($request, $a_type[$type]['open'] . sep($config['folder'], 'l') . $src . $a_type[$type]['close']);

		}

	}else{
		$e = '';
		$counter = 1;
		$total_external = count($minify_files['external_' . $type . '_files']);
		$total_internal = count($minify_files[$type . '_files']);
		$total = $total_external > 0 ? ($total_external + $total_internal) : $total_internal;
		$min_started = false;

		//echo '<br>Iteracion en un total de ' . count($a_all_files) . '<br>';

		foreach($a_all_files as $src){

			if($counter <= $total_external){
				//echo $counter . ' de ' . $total . ' (<= total ext). - e = file_get_contents(' . $src . ')<br>';
				$e .= file_get_contents($src);
			}
			if( ($counter == $total_external) || ($counter > $total_external && $min_started === false) ){
				if($type == 'js'){
					//echo 'Nuevo minify js<br>';
					$r = new Minify\JS($e);
				}elseif($type == 'css'){
					//echo 'Nuevo minify css<br>';
					$r = new Minify\CSS($e);
				}
				$min_started = true;
				//echo $counter . ' de ' . $total . ' (== total ext). - r = new  Minify\\' . $type . '($e)<br>';
			}
			if($counter > $total_external){
				//echo $counter . ' de ' . $total . ' (> total ext). r->add(' . $global_vars['site_path'] . $src . ')<br>';
				$r->add($global_vars['site_path'] . $src);
			}

			$counter++;

		}

		//echo 'Minify cache path: ' . $global_vars['minify_cache_path'] . '/' . $request_filename_min  . '<br>';
		//echo 'Minify cache url: ' . $global_vars['minify_cache_url'] . $request_filename_min  . '<br>';

		$request = $a_type[$type]['open'] . $global_vars['minify_cache_url'] . $request_filename_min . $a_type[$type]['close'];

		//echo '<br>request: ==========<br>';
		//var_dump($request);
		//echo '<br>============<br>';

		$r->minify($global_vars['minify_cache_path'] . '/' . $request_filename_min);

	}

	if($config['debug']){
		echo '<br><br>fn: getAssets: =========================<br>';
		echo implode("\r\n", $request);
		echo '<br>=======================================<br>';
	}

	//echo '<br>Final assets request: ' . $request . '<br>';

	return ((!$global_vars['minify_' . $type . '_enabled'] || !$config['minify_enabled'])) ? implode($a_type[$type]['glue'], $request) : $request;

}

function checkLanguageSegment(){
	global $config;

	$lang_segment = getSegments(1);

	if( ($config['multilanguage'] || $config['country_code_url_explicit']) && strlen($lang_segment) == 2 && array_key_exists($lang_segment, $config['languages']) ){

		if($config['debug']){
			echo 'fn: checkLanguageSegment: ' . $lang_segment . ' (segment_1 is a language segment)<br><br>';
		}

		return true;
	}
	if($config['debug']){
		echo 'fn: checkLanguageSegment: ' . $lang_segment . ' (segment_1 is NOT a language segment)';
		echo '<br>multilanguage: ' . $config['multilanguage'];
		echo '<br>country_code_url_explicit: ' . $config['country_code_url_explicit'];
		echo '<br>strlen: ' . strlen($lang_segment);
		echo '<br>array_key_exists: ' . array_key_exists($lang_segment, $config['languages']) . '<br><br>';

	}
	return false;
}

function getSelfname(){
	global $config;

	//$config['debug'] = true;

	$selfname = getLastSegment();
	if(
		($config['multilanguage'] && strlen(getLastSegment()) == 2)
		|| (!$config['multilanguage'] && getLastSegment() == '')
		//|| (getLastSegment() == getSegments(1) && getSegments(1) == 'index')
		|| ( (getLastSegment() == getSegments(1) ) && (getLastSegment() == '' || getLastSegment() == 'index') )
		|| (getLastSegment() == '' && getSegments(1) == '')
		){
		$selfname = 'index';
	}

	if($config['debug']){
		echo '<br><br>fn getSelfname: getSegments(1): ' . getSegments(1) . '<br>';
		echo 'fn getSelfname: getLastSegment: ' . getLastSegment() . '<br>';
		echo 'fn getSelfname: selfname: ' . $selfname . '<br><br>';
	}

	return $selfname;
}

function removeFolder($string, $location=''){
	global $config;

	if($config['remove_folder'] != true){
		return;
	}

	if(empty($string) || empty($config['folder'])){
		return '';
	}

	//$config['debug'] = false;

	if($location == 'l'){
		$remove = '/' . $config['folder'];
	}elseif($location == 'r'){
		$remove =  $config['folder'] . '/';
	}elseif($location == 'b'){
		$remove = '/' . $config['folder'] . '/';
	}else{
		$remove = $config['folder'];
	}

	$result = str_replace($remove, '', $string);

	if($config['debug']){
		echo '<br>removeFolder: folder: ' . $config['folder'] . '<br>';
		echo 'removeFolder: remove: ' . $remove . '<br>';
		echo 'removeFolder: string: ' . $string . '<br>';
		echo 'removeFolder: result: ' . $result . '<br><br>';
	}

	return $result;
}

function sep($string, $location='l'){
	$sep = '/';
	$result = '';

	if(empty($string)){
		return '';
	}

	switch($location){
		case 'l':
			$result = $sep . trim($string, '\/');
		break;

		case 'r':
			$result = trim($string, '\/') . $sep;
		break;

		case 'b':
			$result = $sep . trim($string, '\/') . $sep;
		break;

	}

	return $result;
}

function swap_all_vars($html, $last_call=false){
	global $config, $global_vars;

	$replaces = '';

	if($config['debug']){echo 'fn swap_all_vars: Start global vars swap<br>=========================<br><br>';}

	foreach($global_vars as $tag => $content){

		$tag = strip_tags(trim( str_replace(' ', '', $tag) ));
		$content = trim($content);
		$stripped_content = strip_tags($content);

		if($config['debug'] && is_array($content)){
			echo '<br>============<br>';
			echo '<br>' . var_dump($content) . '<br>';
			echo '<br>============<br>';
		}
		$replaces .= 'Replace: ' . $tag . ' with: ' . ( $stripped_content == '' ? '(empty)' : $stripped_content ) . "<br>";

		if($last_call){
			$html = set($tag, (isset($global_vars[$tag]) && $global_vars[$tag] != '' ? $content : ''), $html);
		}else{
			$html = set($tag, $content, $html);
		}
	}

	if($config['debug']){
		//echo $replaces . "<br>";
	}

	return $html;
}

function swap_lang_vars($html){
	global $lang, $config;

	$replaces = '';

	if($config['debug']){
		if(count($lang) > 0){
			echo 'fn swap_lang_vars: Start lang vars swap<br>=========================<br>';
		}else{
			echo 'fn swap_lang_vars: lang is empty<br>=========================<br>';
		}
	}


	foreach($lang as $var => $val){

		$replaces .= 'Replace: ' . $var . ' with: ' . (strip_tags($val) == '' ? '(empty)' : strip_tags($val)) . "<br>";

		$html = set($var, $val, $html);
	}

	if($config['debug']){
		echo $replaces . "<br>";
	}

	return $html;
}

function cleanEmptyTags($html){

	global $config;

	$not_processed_Tags = '';
	$processed_Tags = '';
	$searchRegEx = "|\{.*\}|U";
	$tag_results = preg_match_all($searchRegEx, $html,	$tags, PREG_PATTERN_ORDER);

	foreach($tags[0] as $tag){

		if(strpos($tag, ':') === false){
			$html = str_replace($tag, '', $html);

			$processed_Tags .= $tag . '<br>';
		}else{
			$not_processed_Tags .= $tag . '<br>';
		}
	}

	if($config['debug']){
		echo '<br>fn cleanEmptyTags: processed tags:<br>' . $processed_Tags . '<br><br>';
		echo '<br>fn cleanEmptyTags: Not processed tags:<br>' . $not_processed_Tags . '<br><br>';
	}

	return $html;
}

function extractIncludes($string){

	global $config, $global_vars, $global_content;

	$matches = '';
	$results = '';
	$global_content['extract_includes_results'] = '';
	$global_content['extract_includes_matches'] = '';

	// |(?:\{)?((\w+)=(?:['"])(.*?)(?:['"]))+?(?:\})?|U

	// Estas dos andan
	// \{?\s?([\w]+)=[\"']{1}([^\"']+)[\"']{1}\s?\}?|g
	// \{?\s?(\w+)=['"]{1}((?:[^"\\\\]|\\\\.)*)['"]{1}\s?\}?|g

	$searchRegEx = "|\{include\=[\'\"](.*)\/(.*)[\'\"](.*)\}|U";
	$results = preg_match_all($searchRegEx, $string, $matches, PREG_PATTERN_ORDER);

	$global_content['extract_includes_results'] = $results;
	$global_content['extract_includes_matches'] = $matches;

	if($config['debug']){
		echo '<br><br>fn extractIncludes<br>===========================<br>';
		var_dump($results);
		var_dump($matches);
		echo '<br>===========================<br><br>';
	}

	return $results;
}

function set($tag, $str, $source){
	if(!is_array($str)){
		return str_replace('{' . $tag . '}', $str, $source);
	}
}

function getContent($template_filename, $pos){

	global $global_vars, $config;

	//$config['debug'] = true;

	$template_file = str_replace(sep($config['folder'], 'l'), '', $template_filename);
	$template_file = str_replace($config['templates_path'], '', str_replace(sep($config['folder'], 'b'), '', $template_filename) );

	$url_filename = $global_vars['site_url'] . '/' . $config['templates_path'] . $template_file;
	$filename = $global_vars['site_path'] . '/' . $config['templates_path'] . $template_file;

	if($config['debug']){
		echo '<br>getContent: pos: ' . $pos . '<br>';
		echo 'getContent: template_path: ' . $config['templates_path'] . '<br>';
		echo 'getContent: template_filename: ' . $template_filename . '<br>';
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

		$global_vars['is_404'] = true;

		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

		if($config['debug']){
			echo 'getContent file: ' . $template_file . ' <strong>NOT Found</strong><br><br>';
		}
	}

}

function redirect($url, $type=''){
	if($type == 301){
		header('HTTP/1.1 301 Moved Permanently');
	}
	header ("Location:" . $url);
	exit;
}

function setSegments($request_uri){

	global $config;

	$str = $request_uri;
	$str = str_replace($config['folder'], '', $str);
	$str = str_replace('//', '/', $str);
	$pos = strpos($str, '?');

//	echo '<br>1- str: ' . $str . '<br>';
//	echo '2- pos: (?) ' . $pos . ' en: ' . $_SERVER['REQUEST_URI'] . '<br>';

	if($pos !== false){
		$str = str_replace(substr($str, $pos), '', $str);
	}

//	echo '3- str: ' . $str . '<br>';

	$str = trim($str, '\/\?');

//	echo '4- str: ' . $str . '<br>';

	$tempSegments = explode('/', $str);

	$aSegments = array();

	for($i = 1; $i < count($tempSegments) + 1; $i++){
		$aSegments[$i] = $tempSegments[$i - 1];
	}
	return $aSegments;
}

function getSegments($pos='', $skip_lang=false, $location=''){

	global $config;

	$aSegments = setSegments($_SERVER['REQUEST_URI']);
	//$url_check = isset($config['country_code_in_url']) ? true : false;
	$url_check = strlen(reset($aSegments)) == 2 ? true : false;
	$skip_lang_debug = (!$skip_lang ? 'FALSE' : 'TRUE');
	$url_check_debug = (!$url_check ? 'FALSE' : 'TRUE');
	$location_debug = ($location == '' ? '(empty)' : $location);

	if($aSegments == ''){
		return;
	}

	if($pos == ''){
		return $aSegments;
	}
	//if($config['debug'] || $location){
	if($config['debug']){
		echo 'getSegments: Pos inicial: ' . $pos . ' - Skip lang: ' . $skip_lang_debug . ' - location: ' . $location_debug . '<br>';
	}

	$pos = ($skip_lang && $config['multilanguage'] && $url_check && strlen(reset($aSegments)) == 2 ? $pos + 1 : $pos);
	//$pos = ($skip_lang && strlen(reset($aSegments)) == 2 ? $pos + 1 : $pos);

	//if($config['debug'] || $location){
	if($config['debug']){
		echo 'multilanguage: ' . $config['multilanguage'] . '<br>';
		echo 'skip_lang: ' . $skip_lang_debug . '<br>';
		//echo 'country_code_in_url: ' . $config['country_code_in_url'] . '<br>';
		echo 'url_check: ' . $url_check_debug . '<br>';
		echo 'getSegments: Pos: final: ' . $pos . '<br><br>';
	}

	if($pos != '' && is_numeric($pos)){
		return (isset($aSegments[$pos]) ? $aSegments[$pos] : '');
	}
}

function getLastSegment(){
	global $config;

	$segments = getSegments();
	$segments = array_filter($segments, function($value){return !empty($value) || $value === 0;});
	$segments = array_map('trim', $segments);
	$last_value = end($segments);

	if($config['folder'] == $last_value){
		return '';
	}
	return ($last_value ? $last_value : 'index');
}

function getSegmentPos($segment){
	$aSegments = getSegments();

	$pos = array_search($segment, $aSegments);

	return $pos;
}

function getLastSegmentPos(){
	return getSegmentPos(getLastSegment());
}

function normalize($string){
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

function safeURL($url, $limit) {

	$url = normalize($url);

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

function ob_html_compress($buf){
	return preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n","\r","\t"), '', $buf));
}
//
//function array_sanitize($array){
//	if(is_array($array)){
//		$a = arrayStripTags($array);
//		$a = arrayTrim($array);
//		$a = array_map('addslashes',$array);
//		return $a;
//	}
//	return false;
//}
//
//function arrayTrim($array){
//	if(is_array($array)){
//		return array_map('trim',$array);
//	}
//	return false;
//}
//
//function arrayStripTags($array){
//	if(is_array($array)){
//		return array_map('strip_tags',$array);
//	}
//	return false;
//}

?>
