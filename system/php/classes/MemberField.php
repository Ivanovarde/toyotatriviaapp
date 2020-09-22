<?php
class MemberField extends ABM {

	function __construct($id=""){
		$this->read_db_table_fields(Tables::MEMBERFIELDS);
		$this->read($id);

	}

	private static function get_member_field($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new MemberField($db->execute_value());
		return $record;
	}

	private static function get_member_fields($sql) {
		$db = new DB();
		$db->set_query($sql);
		$record_set = $db->execute();
		$a_elements = array();
		//Log::l('get_member_field', $record_set, true);
		foreach($record_set as $record) {
			//Log::l('get_member_field', $record, false);
			array_push($a_elements, new MemberField($record->m_field_id));
		}
		return $a_elements;
	}

	public static function all($filter=''){
		$sql = "SELECT * FROM " . Tables::MEMBERFIELDS . ' ' . ($filter ? ' WHERE ' . $filter : '') . '; ';
		//Log::l(Tables::MEMBERFIELDS, 'MemberField all', false);
		//Log::l('MemberField all', $sql, false);
		return self::get_member_fields($sql);
	}

	/*
	public static function byTitle($title){
		$sql = "SELECT id FROM " . Tables::PROMOS . " WHERE title = '" . $title . "'; ";
		//Log::loguear('MemberField byTitle', $sql, false);
		return self::get_member_field($sql);
	}
	*/

	/*
	public static function byLanguage($lang, $showInactive=false){
		$status = (!$showInactive) ? ' AND status = 1 ' : '';
		$sql = "SELECT " . Tables::PROMOS . ".id FROM " . Tables::PROMOS .
		" LEFT JOIN " . Tables::LANGUAGES . " ON " . Tables::LANGUAGES . ".id = " . Tables::PROMOS . ".lang_id " .
		" WHERE lang_code = '" . $lang . "' " . $status . ' ' .
		" ORDER BY creation DESC LIMIT 1; ";
		//Log::loguear('MemberField byLanguage', $sql, false);
		return self::get_member_field($sql);
	}
	*/


}

