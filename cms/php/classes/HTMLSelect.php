<?php

class HTMLSelect extends HTMLFormField{

	private $html =						'';
	private $sqlQuery = 				'';

	//public $field = 					'';
	public $rsOptions =					'';


	private $makeDBRequest = 			true;

	function __construct(){

		//$this->field = $field;

	}

	private function bindData(){
		if($this->makeDBRequest){
			Log::loguear("FormFieldSelect __construct var sql", $this->sqlQuery,  false);
			$db->set_query($this->sqlQuery);
			$this->rsOptions = $db->execute();

			$this->options .= '<option value="0" >' . lbl_select . '</option>';

			foreach($this->rsOptions as $option){
				$this->options .= '<option value="' . $option['id'] . '"' . $this->attrSelected . ' >' . $option['lbl'] . '</option>' . "\n";
			}
		}
	}

	private function setSqlQuery(){
		$db = new DB();

		Log::loguear('FormFieldSelect __construct param $this->field', $this->field, false);

		switch(strtolower($this->field->fieldname)){

			case "status":
				$this->rsOptions = Entry::getEntriesStatus();

				foreach($this->rsOptions as $k => $v){
					$this->field->attrSelected = ($k == $this->field->value && !is_null($this->field->value)) ? 'selected="selected"' : '';
					$this->field->options .= '<option value="' . $k . '"' . $this->field->attrSelected . ' >' . $v . '</option>' . "\n";
				}
				$this->makeDBRequest = false;
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

	public function makeStaticOptions($keyFieldName, $valueFieldName, $defaultLbl=true){
		if(count($this->rsOptions > 0)){

			if($defaultLbl){
				$this->options .= '<option value="" >' . lbl_select . '</option>';
			}

			foreach($this->rsOptions as $option){
				$this->options .= '<option value="' . $option->$keyFieldName . '"' . $this->attrSelected . ' >' . $option->$valueFieldName . '</option>' . "\n";
			}
		}
	}

	public function getHTML(){

		if(count($this->rsOptions > 0)){

			$this->html = '<select ' .
							$this->attrId .
							$this->attrName .
							$this->attrDisabled .
							$this->attrReadonly .
							$this->attrMultiple .
							$this->attrSize .
							$this->attrClass .
							$this->attrStyle .
							$this->attrEvent .
							$this->attrTabIndex .
							'>' . "\n";

			$this->html .= $this->options;

			$this->html .= '</select>' . "\n";
		}else{
			$this->html .= '<span>{lblSelectNoItems}</span>';
		}
		return $this->html;
	}
}