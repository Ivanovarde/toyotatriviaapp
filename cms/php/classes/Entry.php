<?php
class Entry extends ABM{

	public function __construct($id=""){
		$this->read_db_table_fields(Tables::ENTRIES);
		$this->read($id);
		$this->entryData = new EntryData($this->entry_id);
	}

	public function save(){
		if(parent::save()){
			Log::loguear('Entry save', $this->get_insert_id(), false);
			$this->entryData->entry_id = (!$this->get_insert_id()) ? $this->entry_id : $this->get_insert_id();
			return $this->entryData->save();
		}else{
			return false;
		}
	}

	private static function getEntry($sql){
		$bd = new DB();
		$bd->set_query($sql);
		$record = new Entry($bd->execute_value());
		return $record;
	}

	private static function getEntries($sql){
		$bd = new DB();
		$bd->set_query($sql);
		$rsAll = $bd->execute();
		$vAll = array();
		foreach($rsAll as $record) {
			 $vAll[] = new Entry($record["entry_id"]);
		}
		return $vAll;
	}

	public static function byTitleURL($urltile){
		$sql = "SELECT " . Tables::ENTRIES . ".entry_id FROM " . Tables::ENTRIES .
					" WHERE url_title = '" . $urltile . "' ";
		return self::getEntry($sql);
	}

	public static function byEntryDate($t){
		$sql = "SELECT " . Tables::ENTRIES . ".entry_id FROM " . Tables::ENTRIES .
					" WHERE entry_date = '" . $t . "' ";
		Log::loguear('Entry byEntryDate', $sql, false);
		return self::getEntry($sql);
	}

	public static function all(){
		return self::getEntries("SELECT entry_id FROM " . Tables::ENTRIES .
				" WHERE site_id = 1 AND weblog_id = 1 AND status = 'open' ");
	}

	public static function checkEntryStatus($uid){
		$db = new DB();

		$sql = "SELECT " . Tables::ENTRIES . ".entry_id
		FROM " . Tables::ENTRIES .
		" WHERE " . Tables::ENTRIES . ".weblog_id = 1 AND " .
		Tables::ENTRIES . ".site_id = 1 AND status = 'open' AND author_id = " . $uid . " ";
		//Log::loguear('Entry checkEntryStatus',$sql);
		$value = $db->execute_value($sql);
		//Log::loguear('Entry checkEntryStatus',$value);
		return (is_null($value))?'':$value;
	}
}
