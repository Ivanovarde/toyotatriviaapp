<?php

//error_reporting(0);

$a_servers = array('thestileshotel.nmd', 'thestileshotel.com', 'www.thestileshotel.com', 'beta.thestileshotel.com');

if(!in_array($_SERVER['SERVER_NAME'], $a_servers)){
	exit('Error: Domain not allowed');
}

include('config.php');

//$_GET = array_sanitize($_GET);
$_POST = array_sanitize($_POST);

foreach($_POST as $k => $v){
	$_POST[$k] = strip_tags($v);
	${$k} = $_POST[$k];
}

$config['country_code'] = (isset($_GET['l']) && $_GET['l'] != '' && strlen($_GET['l']) == 2) ? $_GET['l'] : $config['country_code_default'];

$localization_filename = $global_vars['site_path'] . '/' . $global_vars['localization_filename'];


if(file_exists($localization_filename )){
	include($localization_filename );
}else{
	echo '<br>Contact PHP: ' . $localization_filename  . ' <strong>NOT Found</strong><br><br>';
	exit;
}

$headers = array();

$debug = 	false;
$bc	= 		true;
$bcc = 		true;

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';
$action = (($action == '') && (isset($_POST['action']) && $_POST['action'] != '')) ? $_POST['action'] : $action;
$action = strtolower($action);

$fullname = ( !isset($fullname) && (isset($firstname) && isset($lastname) ) ) ? $firstname . ' ' . $lastname : ( isset($fullname) ? $fullname : '' );

$email_templates_path = $global_vars['site_path'] . '/' . $global_vars['templates_path'] . 'emails';
$email_template_filename = $email_templates_path . '/' . 'email-' . $action . '.html';
$email_template_replaces = $email_template_html = '';

$email = isset($email) ? strtolower($email) : '';
$reply_email = $email;
$reply_name = $fullname;

$subject_text = $action == 'contact' ? 'Message from Website' : 'New Subscription';
$email_subject = $global_vars['site_name'] . " - " . $subject_text . ": " . $reply_name;

// Email Server data
$from_server_email = $global_vars['company_email'];
$from_server_username = $global_vars['site_name'];

// Email Receivers data
$to_email = $global_vars['company_email'];
$to_name = $global_vars['site_name'];;
$bc_email = '';
$bc_name	= '';
$bcc_email = 'accounts@ulmarketing.com';
$bcc_name ='Unlimited Marketing';

if($debug === true){
	$to_email = "info@neomedia.com.ar";
	$bc_email = 'pagos@neomedia.com.ar';
	$bcc_email = 'info@novecentoweb.com';
}

// Template
if(file_exists($email_template_filename)){
	$email_template_html = file_get_contents($email_template_filename);
}else{
	echo '<br>Contact PHP: ' . $email_template_filename . ' <strong>NOT Found</strong><br><br>';
}

$a_merge = array_merge ( $global_vars, $_POST);

foreach($a_merge as $k => $v){
	//$email_template_replaces .= 'esto {' . $k . '} x esto: ' . strip_tags($v) . '<br>';
	$email_template_html = str_replace('{' . $k . '}', strip_tags($v), $email_template_html);
}

$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/html; charset=UTF-8";
$headers[] = "From: " . $from_server_username . " <" . $from_server_email . ">";
if($bc){
	$headers[] = "Bc: " . $bc_name . " <" . $bc_email . ">";
}
if($bcc){
	$headers[] = "Bcc: " . $bcc_name . " <" . $bcc_email . ">";
}
$headers[] = "Reply-To: " . $reply_name . " <" . $reply_email . ">";
$headers[] = "Subject: " . $email_subject . "";
$headers[] = "X-Priority: 1";
$headers[] = "X-Mailer: PHP/" . phpversion();

$mail = mail($to_email, $email_subject, $email_template_html, implode("\r\n", $headers));

if($mail !== false){
	//Success Message
	$r['status'] = true;
	$r['msg'] = $lang['email-contact-sent-success'];
	$r['error'] = false;
	//echo 1;
}else{
	//Fail Message
	$r['status'] = false;
	$r['msg'] = $lang['email-contact-sent-fail'];
	$r['error'] = false;
	//echo 0;
}

echo json_encode($r);

?>
