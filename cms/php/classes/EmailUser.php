<?php
Class EmailUser extends Email {


	public function __construct($exceptions = false, $username="", $password=""){

		parent::__construct($exceptions, $username="", $password="");

		$this->IsHTML(true);
		$this->CharSet = 'utf-8';

		$this->aPostValues = Functions::array_sanitize($_POST);
		Log::loguear('EmailUser', $this->aPostValues, false);
	}

	public function sendEmail(){

		$fullname = $this->aPostValues['name'];
		$emailAddress = strtolower($this->aPostValues['email']);
		$model = $this->aPostValues['model'];
		$features = $this->aPostValues['features'];
		$address = $this->aPostValues['address'];
		$address2 = $this->aPostValues['address2'];
		$address3 = $this->aPostValues['address3'];

		$subject = 'Tu participación en promocionesnokia.com';

		$t = new EmailTemplate();
		//$t->setTemplateFolder(Server::getRelativeRootPath() . '_assets/_emails');
		$t->setTemplateFolder('assets/emails');
		$t->getFile('email_user.html');

		$t->set('fullname', $fullname);
		$t->set('email', $emailAddress);
		$t->set('model', $model);
		$t->set('features', $features);
		$t->set('address', $address);
		$t->set('address2', $address2);
		$t->set('address3', $address3);

		$this->SetFrom($this->fromAddress, utf8_decode($this->fromName));
		$this->AddReplyTo($this->fromAddress, utf8_decode($this->fromName));
		$this->Subject = $subject;

		$this->AltBody = $t->getContent(false);
		$this->MsgHTML($t->getContent());

		// Set BCC
		$this->BCCallowed = true;
		$this->BCCto = 'info@neomedia.com.ar';
		$this->BCCname = $this->fromName;

		//parent::sendEmail('ivano22@gmail.com', $this->fromName);
		parent::sendEmail($emailAddress, $this->fromName);
	}
}
