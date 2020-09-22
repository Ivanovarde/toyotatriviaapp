<?php
// php/renderizer.php
// Ivano 03/2017


global $config, $global_vars;

include('php/page_metatags.php');
require_once('functions.php');
//include('minify.php');


// Load Main Template
/*************************************************/
if(!$html = getContent($gloabl_vars['master_structure_site_template'], 'Renderizer 1')){
	if($config['debug']){
		echo 'Renderizer: Loading Master template - File: ' . $gloabl_vars['master_structure_site_template'] . ' <strong>NOT found</strong><br>';
	}
}
/*************************************************/


// Process Early includes
/*************************************************/
if($config['debug']){echo '<br>fn renderizer pre-early includes HTML [[' . ($html ? 'full' : 'empty') . ']]<br><br>';}

$html = processIncludes($html);

if($config['debug']){echo '<br>fn renderizer post-early includes HTML [[' . ($html ? 'full' : 'empty') . ']]<br><br>';}
/*************************************************/


// Section process
/*************************************************/
include('section-process.php');
/*************************************************/


// Load Section Content
/*************************************************/
if( !$global_vars['section_content'] = getContent(removeFolder($global_vars['site_url'], 'l') . '/' . $global_vars['section_filename'], 'Config 1') ){

	if($global_vars['is_404']){
		$global_vars['section_content'] = getContent($global_vars['404_template'], 'Config 1');
	}

	if($config['debug']){
		echo 'Renderizer: Loading Section content - File: ' . removeFolder($global_vars['site_url'], 'l') . '/' . $global_vars['section_filename'] . ' <strong>NOT found</strong><br>';

		if($global_vars['is_404']){
			echo 'Renderizer: Loading Content template - File: ' . $gloabl_vars['master_structure_site_template'] . ' <strong>NOT found</strong><br>(404 error)<br><br>';
		}
	}
}else{
	$html = set('section_content', $global_vars['section_content'], $html);
}
/*************************************************/


// Process Late includes
/*************************************************/
if($config['debug']){echo '<br><br>fn renderizer late includes HTML [[' . ($html ? 'full' : 'empty') . ']]<br><br>';}
$html = processIncludes($html);
/*************************************************/


// Contact Form Process
/*************************************************/
/*if(isset($_POST['send']) && $_POST['send'] == 1){
	include('send.php');
}*/
/*************************************************/


// Set page metatags
/*************************************************/
setPageMetatags();
/*************************************************/


// Process replace zones
/*************************************************/
$html = swap_all_vars($html);
/*************************************************/


/*************************************
 * LOCALIZATION
 /************************************/
if(!file_exists($global_vars['localization_filename_path'])){
	$global_vars['localization_filename_path'] = $global_vars['site_path'] . '/' . $config['localization_path'] .  $global_vars['localization_filename_default'];

	if($config['debug']){
		echo 'Renderizer: Loading Localization lang file - File: ' . $global_vars['localization_filename_path'] . ' <strong>NOT found</strong><br>';

	}
}

if(file_exists($global_vars['localization_filename_path'])){

	if($config['debug']){
		echo 'Renderizer: Loading Localization lang file <strong>FOUND</strong> - File: ' . $global_vars['localization_filename_path'] . ' <br>';

	}

	include($global_vars['localization_filename_path']);

	$html = swap_lang_vars($html);
}
/*************************************************/


// Process replace zones last call
/*************************************************/
$html = swap_all_vars($html);
/*************************************************/


// Process empty tags
/*************************************************/
$html = cleanEmptyTags($html);
/*************************************************/


if($config['debug']){

	echo 'config: selfname: ' . $global_vars['selfname'] . '<br>';
	echo 'config: section: ' . $global_vars['section'] . '<br>';
	echo 'config: templates_path + selfname + extension: ' .
		$config['templates_path'] . 'section/' . $global_vars['selfname'] . '.' . $global_vars['extension'] . '<br>';
	echo 'config: section_filename: ' .$global_vars['section_filename'] . '<br>';
	echo 'config: section_content (site_url + section_filename): ' .
		$global_vars['site_url'] . '/' . $global_vars['section_filename'] . '<br>';
	echo 'Content Filename: ' . $global_vars['section_filename'] . '<br>Section Content: ' .
		$global_vars['section_content'] . '<br>404: ' . $global_vars['is_404'] . '<br>';

	foreach($global_vars as $k => $v){
		echo $k . ': ' . $v . '<br>';
	}

	exit;

}


