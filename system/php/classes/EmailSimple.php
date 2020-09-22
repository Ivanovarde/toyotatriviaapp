<?php
class EmailSimple{

	//public $debug = 	false	;
	public $allow_bc	= 		false;
	public $allow_bcc = 		false;
	public $is_html = 	true;
	public $use_template = false;
	public $reply = 		true;

	public $error_msg = '';

	public $to_address = '';
	public $email_subject = '';
	public $email_template_html = '';

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
		$headers[] = "MIME-Version: 1.0";

		if($this->is_html){
			$headers[] = "Content-type: text/html; charset=UTF-8";
		}else{
			$headers[] = "Content-type: text/plain; charset=UTF-8";
		}

		if($this->from_name && $this->from_address){
			$headers[] = "From: " . $this->from_name . " <" . $this->from_address . ">";
		}

		if( (Settings::get_config('email_allow_bc') == true || $this->allow_bc == true) && $this->bc_address){
			$headers[] = "Bc: " . $this->bc_name . " <" . $this->bc_address . ">";
		}

		if( (Settings::get_config('email_allow_bcc') == true || $this->allow_bcc == true) && $this->bcc_address){
			$headers[] = "Bcc: " . $this->bcc_name . " <" . $this->bcc_address . ">";
		}

		if($this->reply && $this->reply_address){
			$headers[] = "Reply-To: " . $this->reply_name . " <" . $this->reply_address . ">";
		}

		$headers[] = "Subject: " . $this->email_subject . "";
		$headers[] = "X-Priority: 1";
		$headers[] = "X-Mailer: PHP/" . phpversion();

		$this->headers = $headers;
	}

	public function send(){

		if(Settings::get_config('email_debug') === true){
			$this->to_address = $this->config['to_address_debug'];
			$this->to_name = $this->config['to_name_debug'];
			$this->bc_address = $this->config['bc_address_debug'];
			$this->bc_name = $this->config['bc_name_debug'];
			$this->bcc_address = $this->config['bcc_address_debug'];
			$this->bcc_name = $this->config['bcc_name_debug'];
		}

		if(!$this->validate_email_address($this->to_address)){
			Log:l('EmailSimple send:', 'Invalid email address: ' . $this->to_address, $this->debug);
			$error = 'Invalid email address: ' . $this->to_address;
			$this->error_msg = $error;
			return false;
		}

		$this->set_headers();
		$this->swap_system_vars();

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

	public function set_subject($subject){
		$this->subject = $subject;
	}

	public function add_from_address($address, $name=''){
		$this->from_name = $address;
		$this->from_address = (!$name ? $address : $name);
	}

	public function add_reply_address($address, $name=''){
		$this->reply_name = $address;
		$this->reply_address = (!$name ? $address : $name);
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
			Log::l('EmailSimple load_email_template:', $error, $this->config['email_debug']);

			$this->error_msg = $error;
			return false;
		}
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

	public function send_status($m){
		echo $this->error_msg;
	}


}

?>
