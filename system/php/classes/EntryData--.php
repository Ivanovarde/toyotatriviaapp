<?php
class EntryData extends ABM {

	function __construct($id="") {
		if($id==''){
			$this->primary_key_field = "";
		}else{
			$this->primary_key_field = "entry_id";
		}
		$this->read_db_table_fields(Tables::ENTRIESDATA);
		$this->read($id);
	}

	private static function getEntryData($sql){
		$bd = new DB();
		$bd->set_query($sql);
		$record = new EntryData($bd->execute_value());
		return $record;
	}

	private static function getEntryDatas($sql){
		$bd = new DB();
		$bd->set_query($sql);
		$rsAll = $bd->execute();
		$vAll = array();
		foreach($rsAll as $record) {
			 $vAll[] = new EntryData($record["entry_id"]);
		}
		return $vAll;
	}

	public static function byDimensions($w, $h){
		if(empty($w) || empty($h)){
			return false;
		}
		$sql = "SELECT " . Tables::ENTRIESDATA . ".entry_id FROM " . Tables::ENTRIESDATA .
		" WHERE " .
		" channel_id = 9 AND " .
		" site_id = 1 AND " .
		" field_id_59 = '" . $w . "' AND " . /*field_id_59 width*/
		" field_id_60 = '" . $h . "' "; /*field_id_60 height*/

		Log::loguear('EntryData byDimensions', $sql, false);
		return self::getEntryData($sql);
	}

}
