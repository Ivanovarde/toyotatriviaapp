<?php
class RepeatZone {
	
	public $tag;
	public $content;
	public $sourceHTML;
	public $outputHTML;
	public $first = true;
	
	public function __construct($tag, $html) {
		$this->tag = $tag;
		$this->content = $html;
		$this->sourceHTML = $html;
		$this->outputHTML = "";
	}
	
	public function newElement() {
		if (!$this->first) {
			// No es la primera vez		
			$this->content = $this->sourceHTML;			
		} else {
			$this->first = false;
			$this->content = $this->sourceHTML;
		}
	}
	
	public function closeElement() {
		$this->outputHTML .= $this->content;		
	}
	
	public function set($key, $value) {
		Log::loguear('RepeatZone set key | value', $key . ' ' . $value, false);
		$this->content = str_replace("{" . $key . "}", $value, $this->content);
	}
	/*
	public function getHTML() {
		return $this->outputHTML . $this->content;
	}
	*/
}
?>