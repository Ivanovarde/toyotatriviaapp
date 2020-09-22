<?php
abstract class ABM {
	protected $table_name;
	protected $primary_key_field = 								'';
	protected $query_type = 									'';
	protected $table_fields = 								array();
	protected $field_values = 								array();
	protected $null_allowed_fields = 						array();
	protected $auto_increment_fields = 					array();
	protected $field_default_values = 						array();
	protected $insert_id;

	protected static $searchByDataParameters = 		'';
	protected static $searchByDataJoinParameters = 	'';
	protected static $searchByDataField = 				'';
	protected static $searchByDataIDF = 				'';
	protected static $searchByDataIDL = 				'';
	protected static $cache = 								array();

	public function __get($field) {
		//Log::l('ABM __get ($field)', $field, false);
		//Log::l('ABM __get (this->fieldVaues)', $this->field_values, false);

		if (isset($this->$field)) {

			$f = $this->$field;

			if (is_null($f)) {
				return NULL;
			} else {
				return $this->$field;
			}
		}
		//if (isset($this->field_values[$field])) {
		//	if (is_null($this->field_values[$field])) {
		//		return NULL;
		//	} else {
		//		return $this->field_values[$field];
		//	}
		//}
	}

	public function __set($field, $value) {
		//$this->field_values[$field] = $value;
		////Log::l('ABM __set ($field) | $value', $field . ' | ' . $value, false);
		if(!empty($field)){
			$this->{$field} = $value;
			////Log::l('ABM __set $this->{$field}', $this->{$field}. ' | ' . $value, false);
		}
	}

	/**
	 * Reads the fields from the table and assigns each field as
	 * a var with its value
	 *
	 * @param integer $id The id of current record
	 * @param integer $idB The blog id
	 * @param integer $idF The frontend id
	 * @param integer $idL The language id
	 */
	public function read($id, $idB='', $idF='', $idL='') {

		//Log::l('ABM read var $id', $id, false);

		if ((!is_null($id)) && ($id != null) && ($id != "") && !is_object($id)) {
			$this->__set($this->primary_key_field, $id);
			$db = new DB();

			$b = ($idB != '') ? " AND blog_id = " . $idB : "";
			$f = ($idF != '') ? " AND frontend_id = " . $idF : "";
			$i = ($idL != '') ? " AND language_id = " . $idL : "";

			//Log::l('ABM read var $this->primary_key_field', $this->primary_key_field, false);

			$sql = "SELECT * FROM " . $this->table_name . " WHERE " .
					$this->primary_key_field . " = " . $this->get_db_value($this->primary_key_field) .
					$b . $f . $i;

			//Log::l('ABM read var $sql', $sql, false);

			$db->set_query($sql);

			$record = $db->execute_record();

			for($i = 0 ; $i < count($this->table_fields); $i++){
				if(isset($this->table_fields[$i])){
					$this->__set($this->table_fields[$i], $record->{$this->table_fields[$i]});
					//Log::l('ABM read ','i = ' . $i . ', campo = ' . $this->table_fields[$i] . ', value = ' . $record->{$this->table_fields[$i]}, false);
				}
			}
		}
	}

	protected function read_db_table_fields($table) {
		$this->table_name = $table;

		//Log::l('ABM read_db_table_fields this->table_name', $this->table_name, false);

		if(!isset(self::$cache[$table])){

			$db = new DB();
			$db->set_query("SHOW FULL COLUMNS FROM " . $table);

			$fields = $db->execute();
			//Log::l('ABM read_db_table_fields', $fields, false);

			foreach ($fields as $field) {

				if($field->Key == "PRI"){
					$this->primary_key_field = $field->Field;
				}

				// Set NULL fields
				if ($field->Null == "NO" || $field->Null == "") {
					$this->null_allowed_fields[$field->Field] = false;
				}else{
					$this->null_allowed_fields[$field->Field] = true;
				}

				// Set Auto Increment fields
				if ($field->Extra == "auto_increment") {
					$this->auto_increment_fields[$field->Field] = true;
				}else{
					$this->auto_increment_fields[$field->Field] = false;
				}

				// Set Default field value
				if ($field->Default != "") {
					$this->field_default_values[$field->Field] = $field->Default;
				}else{
					$this->field_default_values[$field->Field] = '';
				}

				// Set an array with the table fields
				$this->table_fields[] = $field->Field;
			}

			self::$cache[$table] = array();
			self::$cache[$table]["primary_key_field"] = $this->primary_key_field;
			self::$cache[$table]["table_fields"] = $this->table_fields;
			self::$cache[$table]["null_allowed_fields"] = $this->null_allowed_fields;
			self::$cache[$table]["auto_increment_fields"] = $this->auto_increment_fields;
			self::$cache[$table]["field_default_values"] = $this->field_default_values;

		}else{
			$this->primary_key_field = self::$cache[$table]["primary_key_field"];
			$this->table_fields = self::$cache[$table]["table_fields"];
			$this->null_allowed_fields = self::$cache[$table]["null_allowed_fields"];
			$this->auto_increment_fields = self::$cache[$table]["auto_increment_fields"];
			$this->field_default_values = self::$cache[$table]["field_default_values"];
		}

		//Log::l('ABM read_db_table_fields $this->primary_key_field', $this->primary_key_field, false);
		//Log::l('ABM read_db_table_fields $this->table_fields', $this->table_fields, false);
		//Log::l('ABM read_db_table_fields $this->null_allowed_fields', $this->null_allowed_fields, false);
		//Log::l('ABM read_db_table_fields $this->auto_increment_fields', $this->auto_increment_fields, false);
		//Log::l('ABM read_db_table_fields $this->field_default_values', $this->field_default_values, false);
	}

	protected function generate_insert_sql() {
		$sql = "INSERT INTO " . $this->table_name . " (";

		//Log::l('ABM generate_insert_sql ', $this->table_fields, false);

		// inser query: fields section
		foreach($this->table_fields as $field) {
			$sql .= $field . ", ";
		}

		$sql = substr($sql, 0, strlen($sql)-2);
		$sql .= ") VALUES (";

		// inser query: values section
		foreach($this->table_fields as $field) {
			if($field == $this->primary_key_field || $this->auto_increment_fields[$field]){
				$sql .= "NULL, ";
			}else{
				$sql .= $this->get_db_value($field) . ", ";
			}
		}

		$sql = substr($sql, 0, strlen($sql)-2);
		$sql .= ");";

		$this->query_type = 'INSERT';

		return $sql;
	}

	protected function generate_update_sql() {
		$sql = "UPDATE " . $this->table_name . " SET ";

		foreach($this->table_fields as $field) {
			$sql .= $field . " = " . $this->get_db_value($field) . ", ";
		}

		$sql = substr($sql, 0, strlen($sql)-2);

		$sql .= " WHERE " . $this->primary_key_field . ' = ' . $this->get_db_value($this->primary_key_field) . ';';

		$this->query_type = '';

		return $sql;
	}

	public function save() {
		$db = new DB();

		//Log::l("ABM->save", $this->get_primary_key_value(), false);

		if (is_null($this->get_primary_key_value()) || $this->get_primary_key_value() == "") {
			$sql = $this->generate_insert_sql();
		} else {
			$sql = $this->generate_update_sql();
		}
		//Log::l('ABM->save', $sql, false);

		$db->set_query($sql);

		$result = $db->execute_non_query($this->query_type);
		$this->query_type = '';

		$this->insert_id = $db->get_insert_id();
		return ($result === false ? false : true);
	}

	public function is_new() {
		return (is_null($this->__get($this->primary_key_field)) == true);
	}

	public function get_db_value($field) {

		$value = $this->__get($field);

		//Log::l('ABM get_db_value var $value', $value, false);
		//Log::l('ABM get_db_value var $field', $field, false);
		//Log::l('ABM get_db_value $this->primary_key_field', $this->primary_key_field, false);

		//if($field == $this->primary_key_field || $this->auto_increment_fields[$field]){
		//	//Log::l('ABM get_db_value: field is pryKey or autoincrement', true, false);
		//	return 'NULL';
		//}

		if (is_string($value)) {
			return "'" . addslashes($value) . "'";
		} else if (is_numeric($value)) {
			return $value;
		} else if (is_null($value)) {

			if ($this->null_allowed_fields[$field]){
				return "NULL";
			}else{
				if($this->field_default_values[$field] != ''){
					return "'" . $this->field_default_values[$field]. "'";
				}else{
					return "''";
				}
			}

		} else if (is_object($value)) {
			//Log::l('ABM get_db_value var $value', $value, false);
			return $value->get_value($field);
		}
	}

	public function get_value($field) {
		return $this->__get($field);
	}

	public function get_insert_id(){
		return $this->insert_id;
	}

	public function get_primary_key_field(){
		return $this->primary_key_field;
	}

	public function get_table_name(){
		return $this->table_name;
	}

	public function get_table_fields() {
		return $this->table_fields;
	}

	public function get_primary_key_value() {
		return $this->__get($this->primary_key_field);
	}

	public function byData($data){

		//Log::l('ABM byData param $data', $data, false);

		$parameters = explode(' ',$data['parameters']);
		//Log::l('ABM byData', $data['dataTable'], false);

		switch($data['dataTable']){
			case 'entries':
				$frontend = ' AND ' . Tables::ENTRIES . '.frontend_id = ' . Frontend::$frontendId;
				$language = ' AND ' . Tables::ENTRIESDATA . '.language_id = ' . Frontend::$frontendLangId ;
			break;
			case 'blogs':
				$frontend = ' AND ' . Tables::BLOGS . '.frontend_id = ' . Frontend::$frontendId;
				$language = ' AND ' . Tables::BLOGSDATA . '.language_id = ' . Frontend::$frontendLangId ;
			break;
		}

		foreach($parameters as $p){

			if($p != ''){

				//Log::l('ABM byData $p', $p, false);

				switch($p){
					case (strpos($p, 'frontend') !== false):
						$tmpFrontend = explode('=',$p);
						$frontend = ' AND ' . Tables::ENTRIES . '.frontend_id = ' . $tmpFrontend[1];
					break;
					case (strpos($p, 'lang') !== false):
						$tmpLang = explode('=',$p);
						$language = ' AND ' . Tables::ENTRIESDATA . '.language_id = ' . $tmpLang[1];
					break;
					case (strpos($p, 'orderby') !== false):
						$tmpOrderBy = explode('=',$p);
						$orderBy = ' ORDER BY ' . $tmpOrderBy[1];
					break;
					case (strpos($p, 'sort') !== false):
						$tmpSort = explode('=',$p);
						$sort = ' ' . str_replace(array('"','\''), '', strtoupper($tmpSort[1])) . ' ';
					break;
					case (strpos($p, 'limit') !== false):
						$tmpLimit = explode('=',$p);
						$limit = ' LIMIT ' . $tmpLimit[1];
					break;
					case (strpos($p, 'categorytitle') !== false):
						$tmpCategorytitle = explode('=',$p);
						$categorytitle = ' AND ' . Tables::CATEGORIESDATA . '.title = \'' . str_replace(array('"','\''), '', strtoupper($tmpCategorytitle[1])) . '\' ';
					break;
					case (strpos($p, 'groupby') !== false):
						$tmpGroupby = explode('=',$p);
						$groupby = ' GROUP BY  ' . str_replace(array('"','\''), '', strtoupper($tmpGroupby[1])) . ' ';
					break;
					/*case (strpos($p, 'join') !== false):
						//$join .= ''
					break;*/
					case (strpos($p, 'field') !== false):
						$tmpField = explode('=',$p);
						self::$searchByDataField = end($tmpField);
					break;

					default:
						$where .= ' AND ' . $p . ' ';
				}
			}
		}

		$idF = ($tmpFrontend[1]) ? $tmpFrontend[1] : Frontend::$frontendId;
		$idL = ($tmpLang[1]) ? $tmpLang[1] : Frontend::$frontendLangId;

		//self::$searchByDataJoinParameters =
		self::$searchByDataParameters = $frontend . $language . $where . $categorytitle . $orderBy . $sort . $groupby .$limit . " ";
		self::$searchByDataIDF = $idF;
		self::$searchByDataIDL = $idL;
	}
}
?>
