<?php
class Entry extends ABM{

	public $db_table_name;
	protected static $db_static_table_name;

	public function __construct($db_table, $id=""){
		$this->db_table_name = $db_table;

		Log::l($this->db_table_name, 'Entry constructor $this->db_table_name', false);
		Log::l(self::$db_static_table_name, 'Entry constructor self::$db_static_table_name', false);

		$this->read_db_table_fields($this->db_table_name);
		$this->read($id);
	}

	/*
	public function save(){
		if(parent::save()){
			Log::loguear('Entry save', $this->get_insert_id(), false);
			$this->entryData->entry_id = (!$this->get_insert_id()) ? $this->entry_id : $this->get_insert_id();
			return $this->entryData->save();
		}else{
			return false;
		}
	}
	*/

	private static function get_entry($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new Entry(self::$db_static_table_name, $db->execute_value());
		return $record;
	}

	private static function get_entries($sql){
		$db = new DB();
		$db->set_query($sql);
		$record_set = $db->execute();
		$a_elements = array();
		foreach($record_set as $record) {
			 array_push($a_elements, new Entry(self::$db_static_table_name, $record->id));
		}
		return $a_elements;
	}

	public static function set_db_table_name($table){
		self::$db_static_table_name = $table;
	}

	public static function all($db_table_name, $filter=''){
		self::$db_static_table_name = $db_table_name;
		$sql = "SELECT id FROM " . $db_table_name . ' ' . ($filter ? ' WHERE ' . $filter : '') . '; ';
		Log::l($db_table_name, 'Entry all', false);
		Log::l('Entry all', $sql, false);
		return self::get_entries($sql);
	}

	public static function check_entry_status($uid){
		$db = new DB();

		$sql = "SELECT " . $this->table_name . ".entry_id
		FROM " . $this->table_name .
		" WHERE " . $this->table_name . ".weblog_id = 1 AND " .
		$this->table_name . ".site_id = 1 AND status = 'open' AND author_id = " . $uid . " ";
		//Log::loguear('Entry check_entry_status',$sql);
		$value = $db->execute_value($sql);
		//Log::loguear('Entry check_entry_status',$value);
		return (is_null($value))?'':$value;
	}

	public static function by_name($firstname){
		$sql = "SELECT id FROM " . Tables::PROPSPECTS . " WHERE firstname = '" . $firstname . "'; ";
		Log::l('Entry by_name', $sql, false);
		return self::get_entry($sql);
	}

	public static function by_entry_date($date){
		$sql = "SELECT " . $this->table_name . ".entry_id FROM " . $this->table_name .
					" WHERE entry_date = '" . $date . "' ";

		Log::loguear('Entry by_entry_date', $sql, false);

		return self::get_entry($sql);
	}

	public function delete($path=''){
		return self::delete_entry($this->id, $path='');
	}

	public static function delete_entry($id, $path=''){

		if(!$id){
			Log::l('Entry delete_entry: no Entry id received', '', false);
			return;
		}

		$Entry = new Entry($id);

		if($path){

			$imagefile = $Entry->image;
			$ext = end(explode('.', $imagefile));
			$pattern = str_replace('.' . $ext, '', $imagefile);

			Log::l('Entry delete_entry ',$path . $pattern, false);

			if(is_dir($path)){
				$list = glob($path . $pattern . "*.*");
			}

			foreach ($list as $l) {

				if(file_exists($l)){
					Log::l('Entry delete_entry: file ', $l, false);
					unlink($l);
				}

			}

		}

		$db = new DB();
		$sql = "DELETE FROM " . Tables::PROSPECTS . " WHERE id = " . $id . '; ';
		$db->set_query($sql);
		Log::l('Entry delete_entry', $sql, false);

		return $db->execute_non_query();
	}



	/*
	public static function by_url_title($urltile){
		$sql = "SELECT " . $this->table_name . ".entry_id FROM " . $this->table_name .
					" WHERE url_title = '" . $urltile . "' ";
		return self::get_entry($sql);
	}
	*/


}
