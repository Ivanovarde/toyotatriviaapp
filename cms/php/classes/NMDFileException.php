<?php
class NMDFileException extends NMDException{

	public function __construct ($message, $code=0){
		parent::__construct($message, $code);
	}

	public function showError(){
		echo __CLASS__ .
		$this->proccessError();
	}


}

?>
