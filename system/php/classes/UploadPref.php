<?php
class UploadPref extends ABM{

	public function __construct($id=""){
		$this->read_db_table_fields(Tables::UPLOADPREFS);
		$this->read($id);
	}


	private static function getUploadPref($sql){
		$bd = new DB();
		$bd->set_query($sql);
		$record = new UploadPref($bd->execute_value());
		return $record;
	}

	private static function getUploadPrefs($sql){
		$bd = new DB();
		$bd->set_query($sql);
		$rsAll = $bd->execute();
		$vAll = array();
		foreach($rsAll as $record) {
			 $vAll[] = new UploadPref($record["id"]);
		}
		return $vAll;
	}
	
	/*public static function byTitleURL($urltile){
		$sql = "SELECT " . Tables::ENTRIES . ".entry_id FROM " . Tables::ENTRIES .
					" WHERE url_title = '" . $urltile . "' ";
		return self::getEntry($sql);
	}*/
	
	public static function all(){
		return self::getUploadPrefs("SELECT id FROM " . Tables::UPLOADPREFS .
				" WHERE site_id = 1 AND weblog_id = 1 ");
	}

}