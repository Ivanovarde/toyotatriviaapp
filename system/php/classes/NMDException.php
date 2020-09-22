<?php
class NMDException extends Exception{

	protected $class = __CLASS__;

	public function __construct($message, $code = 0) {

		// asegúrese de que todo está asignado apropiadamente
		parent::__construct($message);
		$this->code = $code;

		//parent::__construct($message, $code);
		//parent::__construct($e->getMessage());
		 //$this->code = $e->getCode();

		//var_dump('NMDException');
	}

	public function showError(){
		echo $this->getClassName() . $this->proccessError();
	}

	protected function getClassName(){
		return __CLASS__;
	}

	protected function proccessError(){
		return "<br />FILE: " . $this->getFile() . ": [Line: " . $this->getLine() . "] " .
		"<br />ERROR: [" . $this->code . '] ' . $this->getMessage() .
		"<br /><br />DETAILS:<br />" . $this->__toString() .
		//"<br />" . $this->getTraceAsString() .
		"<br />" . $this->formatError();
		"<br /><br />" ;
	}

	 // representación de cadena personalizada del objeto
	/* public function __toString() {
		  return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	 }*/

	 /*public function funciónPersonalizada() {
		  echo "Una función personalizada para este tipo de excepción\n";
	 }*/

	protected function formatError(){
		$c = 1;
		$r = '';

		//$c = count($this->getTrace());
		foreach($this->getTrace() as $step){
			//$r .= 'STEP ' . $c . ' | ';
			$r .= $c . ' | ';

			$file = $step['file'];
			$line = $step['line'];
			$function = $step['function'];
			$class = $step['class'];
			$type = $step['type'];

			$params = false;
			$argCounter = 1;

			foreach($step['args'] as $arg){
				if($arg != ''){
					$params = true;
					$args .= '&nbsp;&nbsp;' . $argCounter . '&nbsp;&rarr;' . $arg . '<br />';
				}
				$argCounter++;
			}

			/*foreach($step as $k=>$v){
				$r .= strtoupper($k) . ': ' . $v . '<br />';;
			}*/
			$r .= 'FILE: ' . $file . ' | LINE: ' . $line . '<br />';
			$r .= 'CLASS: ' . $class . $type . $function . '<br />';
			$r .= ($params) ? 'PARAMS:<br />' . $args : '';

			$r .= '<br />';
			$c++;

		}

		return $r;
	}
}
