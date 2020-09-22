<?php
class NMDDBException extends NMDException{

	public $dbname = 					'';
	public $query = 					'';
	public $code = 					0;

	public function __construct ($dbname, $info, $query=''){

		/**
		* PDO errorInfo Array
		* 0 SQLSTATE error code (a five characters alphanumeric identifier defined in the ANSI SQL standard).
		* 1 Driver-specific error code.
		* 2 Driver-specific error message.
		*/
		$sql_state_error_code = $info[0];
		$driver_error_code = $info[0];
		$error_message = $info[2];

		parent::__construct($error_message, $sql_state_error_code);
		//$this->code = $sql_state_error_code;
		//parent::__construct($error_message, $sql_state_error_code);

		$this->dbname = $dbname;
		$this->query = $query;
	}

	public function __toString(){
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

	public function showError(){
		echo "<br>Database Error" .
		"<br />FILE: " . $this->getFile() . ": [Line: " . $this->getLine() . "] " .
		"<br />ERROR: [" . $this->code . '] ' . $this->getMessage() .
		($this->query != '' ? "<br />QUERY: " . $this->query : '') .
		"<br /><br />EXCEPTION DETAILS:<br />" . $this->__toString() . "<br /><br />" .
		$this->formatError() .
		//"<br />" . getTraceAsString() .
		"<br /><br />" ;
	}

 }
