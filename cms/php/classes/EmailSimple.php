<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailSimple{

	public $debug = 					false;
	public $allow_bc	= 				false;
	public $allow_bcc = 				false;
	public $is_html = 				true;
	public $use_template = 			false;
	public $allow_reply = 			true;
	public $use_phpmailer = 		false;

	public $error_msg = '';

	public $email_subject = '';
	public $email_template_html = '';

	public $to_address = '';
	public $to_name = '';

	public $from_name = '';
	public $from_address = '';

	public $bc_name = '';
	public $bc_address = '';

	public $bcc_name = '';
	public $bcc_address = '';

	public $reply_name = '';
	public $reply_address = '';

	public $headers = array();
	public $config = array();
	public $globals = array();


	function __construct($to_address, $subject='', $content=''){

		$this->config = Settings::get_config();
		$this->globals = Settings::get_globals();

		$this->to_address = $to_address;

		$this->email_subject = ($subject ? $subject : '');
		$this->email_template_html = ($content ? $content : '');

		$this->from_name = $this->config['site_name'] ? $this->globals['site_name'] : 'Web app email';
		$this->from_address = $this->config['company_email'] ? $this->config['company_email'] : 'noreply@webappsite.com';

	}

	private function set_headers(){
		$organization = $this->config['site_name'];

		$headers[] = "MIME-Version: 1.0";

		if($this->is_html){
			$headers[] = "Content-type: text/html; charset=UTF-8";
		}else{
			$headers[] = "Content-type: text/plain; charset=UTF-8";
		}

		// EL dominio del header From deberia coincidir con el dominio del servidor
		if($this->from_name && $this->from_address){
			$headers[] = "From: " . $this->from_name . " <" . $this->from_address . ">";
		}

		if($this->from_name && $this->from_address){
			$headers[] = "To: " . $this->to_name . " <" . $this->to_address . ">";
		}

		if( ($this->config['email_allow_bc'] == true || $this->allow_bc == true) && $this->bc_address){
			$headers[] = "Bc: " . $this->bc_name . " <" . $this->bc_address . ">";
		}

		if( ($this->config['email_allow_bcc'] == true || $this->allow_bcc == true) && $this->bcc_address){
			$headers[] = "Bcc: " . $this->bcc_name . " <" . $this->bcc_address . ">";
		}

		if($this->allow_reply && $this->reply_address){
			$headers[] = "Reply-To: " . $this->reply_name . " <" . $this->reply_address . ">";
		}

		$headers[] = "Return-Path: " . $this->from_name . " <" . $this->from_address . ">";

		if( isset($organization)){
			$headers[] = "Organization: " . $organization ;
		}

		$headers[] = "Subject: " . $this->email_subject . "";
		$headers[] = "X-Priority: 3";
		$headers[] = "X-Mailer: PHP" . phpversion();

		$this->headers = $headers;
	}

	public function send(){

		// EL dominio del header From deberia coincidir con el dominio del servidor
		if($this->config['email_debug'] === true || $this->debug === true){

			$this->from_address = $this->config['from_address'];
			$this->from_name = $this->config['from_name'];
			$this->reply_address = $this->from_address;
			$this->reply_name = $this->from_name;

			$this->to_address = $this->config['to_address_debug']; //'iv@neomedia.com.ar';
			$this->to_name = $this->config['to_name_debug']; //'Ivano Nmd';
			$this->bc_address = $this->config['bc_address_debug'];
			$this->bc_name = $this->config['bc_name_debug'];
			$this->bcc_address = $this->config['bcc_address_debug'];
			$this->bcc_name = $this->config['bcc_name_debug'];

		}

		if(!$this->to_name){
			 $this->to_name = 'Contact';
		}

		if(!$this->validate_email_address($this->to_address)){
			Log::l('EmailSimple send:', 'Invalid email address: ' . $this->to_address, ($this->debug || $this->config['email_debug']) );
			$error = 'Invalid email address: ' . $this->to_address;
			return $this->error_msg = $error;
			//return false;
		}

		$this->swap_system_vars();

		if($this->use_phpmailer){
			Log::l('EmailSimple send()', $this, ($this->debug || $this->config['email_debug']) );
			return $this->send_with_phpmailer();
		}else{
			return $this->send_with_mail();
		}

	}

	protected function send_with_phpmailer(){

		require $this->globals['site_path'] . '/php/phpmailer/Exception.php';
		require $this->globals['site_path'] . '/php/phpmailer/PHPMailer.php';
		require $this->globals['site_path'] . '/php/phpmailer/SMTP.php';


		// Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer(true);

		try {

			//Server settings
			// SMTPDebug: 0 none, 1: client errors, 2client and server errros
			$mail->SMTPDebug = 0;
			$mail->Username = $this->config['smtp_username'];			// SMTP username
			$mail->Password = $this->config['smtp_port'];				// SMTP password

			$mail->isSMTP();																// Set mailer to use SMTP
			$mail->Host = $this->config['smtp_host'];					// Specify main and backup SMTP servers
			$mail->Port = $this->config['smtp_password'];				// TCP port to connect to
			$mail->SMTPAuth = true;														// Enable SMTP authentication
			$mail->CharSet = 'UTF-8';													// Set encode to utf-8
			$mail->isHTML(true);															// Set email format to HTML

			//Recipients

			// EL dominio del header From deberia coincidir con el dominio del servidor
			if($this->from_address){
				$mail->setFrom($this->from_address, ($this->from_name ? $this->from_name : $this->from_address) );
				$mail->addAddress($this->from_address, ($this->from_name ? $this->from_name : $this->from_address) );
			}

			if($this->allow_reply && $this->reply_address){
				$mail->addReplyTo($this->reply_address, ($this->reply_name ? $this->reply_name : $this->reply_address) );
			}

			if($this->allow_bc && $this->bc_address){
				$mail->addCC($this->bc_address, ($this->bc_name ? $this->bc_name : $this->bc_address) );
			}

			if($this->allow_bcc && $this->bcc_address){
				$mail->addBCC($this->bcc_address, ($this->bcc_name ? $this->bcc_name : $this->bcc_address) );
			}

			// Content
			$mail->Subject = $this->email_subject;
			$mail->Body    = $this->email_template_html;
			//$mail->AltBody = 'This is a html email. Please turn html on to see';


			$this->clear_address();

			if($mail != false){
				$this->error_msg = '';
			}

			// FALTA HCER EL ENVIO

			return $mail;

		} catch (Exception $e) {
			$this->error_msg = "EmailSimple->send_with_phpmailer() - Error: " . $mail->ErrorInfo;
			return false;
		}


		echo json_encode($r);
	}

	protected function send_with_mail(){

		$this->set_headers();

		$mail = mail($this->to_address, $this->email_subject, $this->email_template_html, implode("\r\n", $this->headers));

		$this->clear_address();

		if($mail != false){
			$this->error_msg = '';
		}

		return $mail;
	}

	private function swap_system_vars(){

		$a_merge = array_merge ( $this->config, $this->globals, $_POST);

		foreach($a_merge as $k => $v){
			if(!is_array($v)){
				$this->email_template_html = str_replace('{' . $k . '}', strip_tags($v), $this->email_template_html);
				//echo 'Replace {' . $k . '} x this: ' . strip_tags($v) . '<br>';
				Log::l('EmailSimple swap_system_vars:', 'Replace {' . $k . '} x this: ' . strip_tags($v), false);
			}
		}

	}

	public function set_to_name($name){
		$this->to_name = $name;
	}

	public function set_email_subject($subject){
		$this->email_subject = $subject;
	}

	public function add_from_address($address, $name=''){
		$this->from_name = $address;
		$this->from_address = (!$name ? $address : $name);
	}

	public function add_reply_address($address, $name=''){
		$this->reply_address = $address;
		$this->reply_name = (!$name ? $address : $name);
	}

	public function add_bc_address($address, $name=''){
		$this->bc_address = $address;
		$this->bc_name = (!$name ? $address : $name);
	}

	public function add_bcc_address($address, $name=''){
		$this->bcc_address = $address;
		$this->bcc_name = (!$name ? $address : $name);
	}

	private function clear_address(){
		$this->to_address = '';
		$this->to_name = '';
		$this->from_address = '';
		$this->from_name = '';
		$this->bc_address = '';
		$this->bc_name = '';
		$this->bcc_address = '';
		$this->bcc_name = '';
	}

	public function load_email_template($path){

		$this->use_template = true;

		$email_template_filename = $path;

		if(file_exists($email_template_filename)){
			$this->email_template_html = file_get_contents($email_template_filename);
		}else{
			$error = $email_template_filename . ' NOT Found';
			Log::l('EmailSimple load_email_template:', $error, ($this->debug || $this->config['email_debug']) );

			$this->error_msg = $error;
			return false;
		}
	}

	public function get_email_template($path=""){
		if(!$path && $this->email_template_html){
			return $this->email_template_html;
		}elseif(!$path && !$this->email_template_html){
			return;
		}
		return $this->load_email_template($path);
	}

	public function is_html($boolean){

		$this->is_hmtl = (bool) $boolean;

	}

	public function allow_bc($mode){
		$this->allow_bc = mode;
	}

	public function allow_bcc($mode){
		$this->allow_bcc = mode;
	}

	public function validate_email_address($email){
		if(self::is_valid_email_address($email)){
			return true;
		}
		return false;
	}

	public static function is_valid_email_address($email){
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/" , $email)){
			return true;
		}
		return false;
	}

	public function get_status($m){
		return $this->error_msg;
	}


}

?>
