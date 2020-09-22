<?php

class HTMLSelectLanguages extends HTMLSelect{

	/**
	 *
	 * @param $id String. El id para la etuiteta html
	 * @param $value. El valor seleccionado que debe tener el select
	 * @param $name String. El name para la etiqueta html
	 */
	function __construct($id='', $value=0, $name=''){

		$this->value = $value;

		if($id){
			$this->set_attrId($id);
		}

		if($name){
			$this->set_attrName($name);
		}elseif($id){
			$this->set_attrName($id);
		}

		$this->languages = Language::all();

		foreach($this->languages as $lang){
			$this->rsOptions[$lang->id] = $lang->name;
		}
		$this->options = '<option value="" >Select</option>' . "\n";

		$counter = 0;
		foreach($this->rsOptions as $id => $lbl){
			$this->attrSelected = ($id == $this->value) ? 'selected="selected"' : '';
			$this->options .= '<option value="' . $id . '" ' . $this->attrSelected . ' >' . $lbl . '</option>' . "\n";
			$counter++;
		}
	}
}