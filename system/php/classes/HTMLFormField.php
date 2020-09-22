<?php
class HTMLFormField {

	private $html =								'';

	//protected $value =							'';

	public $is_multiple;

	public $fieldname;
	public $fieldtype;
	public $value;
	public $style;
	public $class;
	public $rel;
	public $minlength;
	public $maxlength;
	public $size;
	public $cols;
	public $multiple;

	public $attr_id;
	public $attr_name;
	public $attr_disabled;
	public $attr_readonly;
	public $attr_multiple;
	public $attr_size;
	public $attr_class;
	public $attr_style;
	public $attr_event;
	public $attr_tabindex;
	public $attr_rows;
	public $attr_checked;
	public $attr_rel;


	public static $a_img_extensions = 			array('jpg','gif','png');


	function __construct() {
		//Log::loguear('htmlformfield __construct', 'Field: ' . $field->fieldname . ' | Value: ' . $value, true);

	}

	public function get_html(){
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

	/*
	public function set_req($req=false){
		$this->field->required = false;
		if($req){
			$this->field->class = ($this->field->class == '') ? ' required ' : $this->field->class . ' required ';
			$this->field->required = ' <span class="req"> * </span> ';
		}
		Log::loguear('FormFieldRenderer set_req',$this->field->required,false);
	}
	*/

	public function set_is_email($is_email=false){
		if($is_email){
			$this->field->class = ($this->field->class == '') ? '  email ' : $this->field->class . ' email ';
		}
		Log::loguear('FormFieldRenderer set_is_uRL',$this->field->fieldname .  ' ' .  $this->field->class .  ' ' . $this->field->is_uRL, false);
	}

	public function set_is_uRL($is_url=false){
		if($is_url){
			$this->field->class = ($this->field->class == '') ? '  url ' : $this->field->class . ' url ';
		}
		Log::loguear('FormFieldRenderer set_is_uRL',$this->field->fieldname .  ' ' .  $this->field->class .  ' ' . $this->field->is_uRL,false);
	}

	public function set_rel($rel){
		$this->rel = $rel;
	}

	//public function set_label($label=''){
	//	$this->label = ($label == '') ? $this->fieldname : $label;
	//}

	//public function set_description($desc){
	//	if(!$desc) return;
	//	$this->description = $desc;
	//}

	public function set_attr_id($id){
		$this->attr_id = ($id != '') ? ' id="' . $id . '" ' : '';
	}

	public function set_attr_name($name){
		$this->attr_name = ($name != '') ? ' name="' . $name . '" ' : '';
		$name_multiple = substr($this->attr_name, 0, strlen($this->attr_name)-2) . '[]"';
		$this->attr_name = ($this->attr_multiple != '') ? $name_multiple : $this->attr_name;
	}

	public function set_attr_type($type){
		$this->type = $type;
		$this->attr_type = ($type != '') ? ' type="' . $type . '" ' : '';
	}

	public function set_attr_class($class=''){
		$c = ($this->class != '') ? $this->class : '';
		$this->attr_class = ' class="' . $class . ' ' . $c . '" ';
	}

	public function set_attr_style($style){
		$this->attr_style = ($style != '') ? ' style="' . $style . '" ' : '';
	}

	public function set_attr_value($value){
		$this->attr_value = ' value="' . $value . '" ';
	}

	public function set_activevalue($active_value){
		$this->active_value = $active_value;
	}

	public function set_attr_tab_index($tabindex){
		$this->attr_tabi_ndex = ($tabindex != '') ? ' tabindex="' . $tabindex .'" ' : '';
	}

	public function set_attr_event($event){
		$this->attr_event = ($event != '') ? $event : '';
	}

	public function set_attr_minlength($minlength){
		$this->attr_min_length = ($minlength != '' && $minlength > 0) ? ' minlength="' . $minlength . '" ' : '';
	}

	public function set_attr_maxlength($maxlength){
		$this->attr_max_length = ($maxlength != '' && $maxlength > 0) ? ' maxlength="' . $maxlength . '" ' : '';
	}

	public function set_attr_size($size){
		$this->attr_size = ($size != '' && (int)$size != 0) ? ' size="' . $size . '" ' : '';
	}

	public function set_attr_cols($cols){
		$this->attr_cols = ($cols != '') ? ' cols="' . $cols . '" ' : '';
	}

	public function set_attr_rows($rows){
		$this->attr_r_rows = ($rows != '') ? ' rows="' . $rows . '" ' : '';
	}

	public function set_attr_multiple($multiple){
		$this->is_multiple = $multiple;
		$this->attr_multiple = ($multiple) ? ' multiple="multiple" ' : '';
	}

	public function set_attr_checked($val){
		$this->attr_checked = ($this->value == $this->active_value) ? ' checked="checked" ' : "";
	}

	public function set_attr_readonly($readonly){
		$this->attr_readonly = ($readonly) ? ' readonly="readonly" ' : '';
	}

	public function set_attr_disabled($disabled){
		$this->attr_disabled = ($disabled) ? ' disabled="disabled" ' : '';
	}

	public function set_attr_rel($rel){
		$r = $rel . ' ' . $this->rel;
		$this->attr_rel = ($r) ? ' rel="' . $r . '" ' : '';
	}

	public function set_options($a_options){
		$this->rs_options = $a_options;
	}

}
?>
