<?php

class HTMLSelect extends HTMLFormField{

	private $make_db_request = 			true;
	private $sql_query = 				'';
	private $options =					'';
	private $html =						'';

	public $rs_options =					'';



	function __construct(){

	}

	private function bind_data(){
		if($this->make_db_request){
			Log::l("FormFieldSelect __construct var sql", $this->sqlQuery,  false);
			$db->set_query($this->sqlQuery);
			$this->rs_options = $db->execute();

			$this->options .= '<option value="" >' . lbl_select . '</option>';

			foreach($this->rs_options as $option){
				$this->options .= '<option value="' . $option['id'] . '"' . $this->attr_selected . ' >' . $option['lbl'] . '</option>' . "\n";
			}
		}
	}

	private function set_sql_query(){
		$db = new DB();

		Log::l('FormFieldSelect __construct param $this->field', $this->field, false);

		switch(strtolower($this->field->fieldname)){

			case "status":
				$this->rs_options = Entry::getEntriesStatus();

				foreach($this->rs_options as $k => $v){
					$this->field->attr_selected = ($k == $this->field->value && !is_null($this->field->value)) ? 'selected="selected"' : '';
					$this->field->options .= '<option value="' . $k . '"' . $this->field->attr_selected . ' >' . $v . '</option>' . "\n";
				}
				$this->make_db_request = false;
			break;

			case "frontend_id":
				$this->sqlQuery = "SELECT " . Tables::FRONTENDS . ".id AS id, " . Tables::COUNTRIESDATA . ".name AS lbl FROM " .
						Tables::FRONTENDS . ", " . Tables::COUNTRIESDATA .
						" WHERE " . Tables::COUNTRIESDATA . ".language_id = " . $this->field->formFieldData->language_id .
						" AND " . Tables::FRONTENDS . ".country_id = " . Tables::COUNTRIESDATA . ".country_id " .
						" ORDER BY lbl ASC";
			break;

			case "language_id":
				$this->sqlQuery = "SELECT father_id AS id, name AS lbl FROM languages " .
						" WHERE language_id=" . $this->field->formFieldData->language_id . " ORDER BY lbl ASC";
			break;

			case "country_id":
				$this->sqlQuery = "SELECT " . Tables::COUNTRIES . ".id AS id, " . Tables::COUNTRIESDATA . ".name AS lbl FROM " .
						Tables::COUNTRIES . ", " . Tables::COUNTRIESDATA .
						" WHERE " . Tables::COUNTRIESDATA . ".language_id = " . $this->field->formFieldData->language_id .
						" AND " . Tables::COUNTRIES . ".id = " . Tables::COUNTRIESDATA . ".country_id " .
						" ORDER BY lbl ASC";
			break;

			case "state_id":
				$this->sqlQuery = "SELECT country_id FROM " . Tables::STATES . " WHERE id = " . $this->field->value;
				$db->set_query($sql);
				$idC = ($this->field->value != '') ? $db->execute_value() : '';
				$where = ($idC != '') ? " WHERE country_id = " . $idC : '';

				$this->sqlQuery = "SELECT " . Tables::STATES . ".id AS id, " . Tables::STATES . ".name AS lbl FROM " .
						Tables::STATES .
						$where .
						" ORDER BY lbl ASC";
			break;

			case "category_id":
				$this->sqlQuery = "SELECT " . Tables::CATEGORIES . ".id AS id, " . Tables::CATEGORIESDATA . ".title AS lbl FROM " .
						Tables::CATEGORIES . ", " . Tables::CATEGORIESDATA .
						" WHERE " . Tables::CATEGORIESDATA . ".language_id = " . $this->field->formFieldData->language_id .
						" AND " . Tables::CATEGORIES . ".id = " . Tables::CATEGORIESDATA . ".category_id " .
						" AND frontend_id = " . Frontend::$frontendId . " AND blog_id = " . $this->field->blog_id .
						" ORDER BY lbl ASC";
			break;

			default:
				$this->sqlQuery = "SELECT " . $this->field->fk . " AS id, " . $this->field->fk_lbl . " AS lbl FROM " . $this->field->fk_table .
						" LEFT JOIN " . Tables::ENTRIES . " ON " . Tables::ENTRIES . ".id = " . Tables::ENTRIESDATA . ".entry_id " .
						" WHERE " .
						" language_id=" . $this->field->formFieldData->language_id .
						" AND blog_id = " . $this->field->fk_blog_id .
						" ORDER BY lbl ASC";
			break;
		}
	}

	public function fill_options($a_options='', $selected_value='', $default_value='', $default_label=''){

		if($a_options){
			if(!is_array($a_options)){
				$options = array($a_options);
			}else{
				$options = $a_options;
			}
		}else{
			$options = $this->rs_options;
		}

		if(count($options) < 1){
			return;
		}

		if(count($options) > 0){

			if($default_label){
				$this->options .= '<option value="' . $default_value . '" >' . $default_label . '</option>';
			}

			foreach($options as $option){
				$attr_selected = ($selected_value == $option['value'] ? 'selected="selected"' : '');
				$this->options .= '<option value="' . $option['value'] . '"' . $attr_selected . ' >' . $option['label'] . '</option>' . "\n";
			}

		}
	}

	public function get_html(){

		if(count($this->rs_options > 0)){

			$this->html = '<select ' .
							$this->attr_id .
							$this->attr_name .
							$this->attr_disabled .
							$this->attr_readonly .
							$this->attr_multiple .
							$this->attr_size .
							$this->attr_class .
							$this->attr_style .
							$this->attr_event .
							$this->attr_tabindex .
							'>' . "\n";

			$this->html .= $this->options;

			$this->html .= '</select>' . "\n";
		}else{
			$this->html .= '<span>{lblSelectNoItems}</span>';
		}
		return $this->html;
	}
}
