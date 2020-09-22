<?php
class Language extends ABM {


	function __construct($id=""){
		$this->read_db_table_fields(Tables::LANGUAGES);
		$this->read($id);
	}


	private static function getLanguage($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new Language($db->execute_value());
		return $record;
	}

	private static function getLanguages($sql) {
		$db = new DB();
		$db->set_query($sql);
		$rsAll = $db->execute();
		$vAll = array();
		foreach($rsAll as $record) {
			 $vAll[] = new Language($record["id"]);
		}
		Log::loguear('Language getLanguages', $vAll, false);
		return $vAll;
	}

	public static function all(){
		$sql = "SELECT id FROM " . Tables::LANGUAGES . " ORDER BY name ASC ";

		Log::loguear('Language all', $sql, false);
		return self::getLanguages($sql);
	}

	public static function byID($id){
		$sql = "SELECT id FROM " . Tables::LANGUAGES . " WHERE id = $id ";

		Log::loguear('Language byID', $sql, false);
		return self::getLanguage($sql);
	}

	public static function byName($name){
		$sql = "SELECT id FROM " . Tables::LANGUAGES . " WHERE name = $name ";

		Log::loguear('Language byName', $sql, false);
		return self::getLanguage($sql);
	}

}

?>
