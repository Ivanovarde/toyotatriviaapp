<?php
Class EmailContact extends Email {


	public function __construct($exceptions = false, $username="", $password=""){

		parent::__construct($exceptions, $username="", $password="");

		$this->IsHTML(true);
		$this->CharSet = 'utf-8';

		$this->aPostValues = Functions::array_sanitize($_POST);
		Log::loguear('EmailContact', $this->aPostValues, false);
	}

	public function sendEmail(){

		$fullname = $this->aPostValues['name'];
		$emailAddress = strtolower($this->aPostValues['email']);
		$model = $this->aPostValues['model'];
		$features = $this->aPostValues['features'];

		$subject = 'Acabo de comprar el mejor teléfono';

		$t = new EmailTemplate();
		//$t->setTemplateFolder(Server::getRelativeRootPath() . '_assets/_emails');
		$t->setTemplateFolder('assets/emails');
		$t->getFile('email_contact.html');

		$t->set('fullname', $fullname);
		$t->set('email', $emailAddress);
		$t->set('model', $model);
		$t->set('features', $features);

		$this->SetFrom($this->fromAddress, utf8_decode($this->fromName));
		$this->AddReplyTo($this->fromAddress, utf8_decode($this->fromName));
		$this->Subject = $subject;

		$this->AltBody = $t->getContent(false);
		$this->MsgHTML($t->getContent());

		// Set BCC
		$this->BCCallowed = true;
		$this->BCCto = 'info@neomedia.com.ar';
		$this->BCCname = $this->fromName;

		// Set CC
		$this->CCallowed = true;
		$this->CCto = $emailAddress;
		$this->CCname = $this->fromName;

		//parent::sendEmail('ivano22@gmail.com', $this->fromName);
		parent::sendEmail($this->aPostValues['friend_email'], $fullname);
	}
}
