<?php
class Email extends PHPMailer{

	public $error = '';

	public $plainTextBody = '';
	public $htmlBody = '';

	public $fromAddress = '';
	public $fromName = '';
	//public $emailAddressToName = '';
	public $sendLimit = 50;
	public $BCCallowed = false;
	public $BCCto = '';
	public $BCCname = '';
	public $CCallowed = false;
	public $CCto = '';
	public $CCname = '';

	public function __construct($exceptions = false, $username="", $password=""){

		parent::__construct($exceptions);

		switch(Server::getServerName()){
			case 'promocionesnokia.com':
			case 'web.promocionesnokia.com':
				$u = ($username != '') ? $username : 'noreply@promocionesnokia.com';
				$p = ($password != '') ? $password : 'pr0m0sn0k14';
				$port = 25;
				$mailhost = 'mail.promocionesnokia.com';
			break;

			case 'lumiabts.nmd':
				$u = ($username != '') ? $username : 'test@neomedia.com.ar';
				$p = ($password != '') ? $password : '1234abcd';
				$port = 25;
				$mailhost = 'mail.neomedia.com.ar';
			break;
		}

		$this->fromAddress = $u;
		$this->fromName = 'Promociones Nokia Online';

		// Crear una cuenta en el server local con el mismo nombre y password que la cuenta
		// gmail/apps que va a ser configurada en este script
		$this->IsSMTP();
		//$this->IsSendmail();
		$this->SMTPAuth   = true;							// enable SMTP authentication
		$this->Host       = $mailhost;						// sets the SMTP server
		$this->Port       = $port;								// set the SMTP port for the GMAIL server
		$this->Username   = $u;								// email address
		$this->Password   = $p;								// SMTP account password
	}

	public function sendEmail($emailAddressTo,$emailAddressToName){

		Log::loguear('Email sendEmail', $this->Username, false);
		Log::loguear('Email sendEmail', $this->Password, false);
		Log::loguear('Email sendEmail', $this->Host, false);

		try{
			$this->AddAddress($emailAddressTo, $emailAddressToName);

			if($this->BCCallowed){
				$this->AddBCC($this->BCCto, $this->BCCname);
			}
			if($this->CCallowed){
				$this->AddCC($this->CCto, $this->CCname);
			}

			$this->Send();
			$this->ClearAddresses();

		} catch (phpmailerException $e) {
			$this->error = $e->errorMessage();
		} catch (Exception $e) {
			$this->error = $e->getMessage();
		}

	}

	public function validateEmailAddress($email){
		if(self::isValidEmailAddress($email)){
			return true;
		}
		return false;
	}

	public static function isValidEmailAddress($email){
		if(preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/" , $email)){
			return true;
		}
		return false;
	}

	public function checkEmailStatus($email){
		// checks proper syntax
		if($this->validateEmailAddress($email)){
			// gets domain name
			list($username, $domain) = split('@', $email);
			// checks for if MX records in the DNS
			if(!checkdnsrr($domain, 'MX')) {
				return false;
			}
			// attempts a socket connection to mail server
			if(!fsockopen($domain, 25, $errno, $errstr, 30)) {
				return false;
			}
			return true;
		}
		return false;
	}



}
