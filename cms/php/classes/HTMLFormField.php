<?php
class HTMLFormField {

	private static $aFormfieldFormats = aFormfieldFormats; //se accede mediante getFormfieldFormat()
	private $html =								'';

	protected	$value =						'';

	public static $aImg_extensions = 			array('jpg','gif','png');


	function __construct() {
		//Log::loguear('htmlformfield __construct', 'Field: ' . $field->fieldname . ' | Value: ' . $value, true);

	}

	public function getHTML(){
		return $this->html;
	}

	public function set_fieldname($f){
		$this->fieldname = $f;
	}

	public function set_fieldtype($type){
		$this->type = $type;
	}

	public function set_value($value){
		$this->field->value = $value;
		$this->value = $this->field->value;
		Log::loguear('FormFieldRenderer set_value this->value', $this->value, false);
	}

	public function set_style($style){
		$this->style = $style;
	}

	public function set_class($class){
		$this->class = $class;
	}

	public function set_size($size){
		$this->size = $size;
	}

	public function set_req($req=false){
		$this->field->required = false;
		if($req){
			$this->field->class = ($this->field->class == '') ? ' required ' : $this->field->class . ' required ';
			$this->field->required = ' <span class="req"> * </span> ';
		}
		Log::loguear('FormFieldRenderer set_req',$this->field->required,false);
	}

	public function set_isEmail($isEmail=false){
		if($isEmail){
			$this->field->class = ($this->field->class == '') ? '  email ' : $this->field->class . ' email ';
		}
		Log::loguear('FormFieldRenderer set_isURL',$this->field->fieldname .  ' ' .  $this->field->class .  ' ' . $this->field->isURL,false);
	}

	public function set_isURL($isUrl=false){
		if($isUrl){
			$this->field->class = ($this->field->class == '') ? '  url ' : $this->field->class . ' url ';
		}
		Log::loguear('FormFieldRenderer set_isURL',$this->field->fieldname .  ' ' .  $this->field->class .  ' ' . $this->field->isURL,false);
	}

	public function set_rel($rel){
		$this->rel = $rel;
	}

	public function set_labeltext($label){
		$this->label = ($label == '') ? $this->fieldname : $label;
	}

	public function set_description($desc){
		if(!$desc) return;
		$this->description = $desc;
	}

	public function set_attrId($id){
		$this->attrId = ($id != '') ? ' id="' . $id . '" ' : '';
	}

	public function set_attrName($name){
		$this->attrName = ($name != '') ? ' name="' . $name . '" ' : '';
		$nameMultiple = substr($this->attrName, 0, strlen($this->attrName)-2) . '[]"';
		$this->attrName = ($this->attrMultiple != '') ? $nameMultiple : $this->attrName;
	}

	public function set_attrType($type){
		$this->type = $type;
		$this->attrType = ($type != '') ? ' type="' . $type . '" ' : '';
	}

	public function set_attrClass($class=''){
		$c = ($this->class != '') ? $this->class : '';
		$this->attrClass = ($class != '' || $c) ? ' class="' . $class . ' ' . $c . '" ' : '';
	}

	public function set_attrStyle($style){
		$this->attrStyle = ($style != '') ? ' style="' . $style . '" ' : '';
	}

	public function set_attrValue($value){
		$this->attrValue = ' value="' . $value . '" ';
	}

	public function set_activevalue($active_value){
		$this->active_value = $active_value;
	}

	public function set_attrTabindex($tabindex){
		$this->attrTabIndex = ($tabindex != '') ? ' tabindex="' . $tabindex .'" ' : '';
	}

	public function set_attrEvent($event){
		$this->attrEvent = ($event != '') ? $event : '';
	}

	public function set_attrMinlength($minlength){
		$this->attrMinlength = ($minlength != '' && $minlength > 0) ? ' minlength="' . $minlength . '" ' : '';
	}

	public function set_attrMaxlength($maxlength){
		$this->attrMaxlength = ($maxlength != '' && $maxlength > 0) ? ' maxlength="' . $maxlength . '" ' : '';
	}

	public function set_attrSize($size){
		$this->attrSize = ($size != '' && (int)$size != 0) ? ' size="' . $size . '" ' : '';
	}

	public function set_attrCols($cols){
		$this->attrCols = ($cols != '') ? ' cols="' . $cols . '" ' : '';
	}

	public function set_attrRows($rows){
		$this->attrRows = ($rows != '') ? ' rows="' . $rows . '" ' : '';
	}

	public function set_attrMultiple($multiple){
		$this->isMultiple = $multiple;
		$this->attrMultiple = ($multiple) ? ' multiple="multiple" ' : '';
	}

	public function set_attrChecked($val){
		$this->attrChecked = ($this->value == $this->active_value) ? ' checked="checked" ' : "";
	}

	public function set_attrReadonly($readonly){
		$this->attrReadonly = ($readonly) ? ' readonly="readonly" ' : '';
	}

	public function set_attrDisabled($disabled){
		$this->attrDisabled = ($disabled) ? ' disabled="disabled" ' : '';
	}

	public function set_attrRel($rel){
		$r = $rel . ' ' . $this->rel;
		$this->attrRel = ($r) ? ' rel="' . $r . '" ' : '';
	}

	public function set_options($aOptions){
		$this->rsOptions = $aOptions;
	}

}
?>