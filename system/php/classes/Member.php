<?php
class Member extends ABM{

	public $table = Tables::MEMBERS;
	protected static $db_static_table_name;

	public function __construct($id=""){
		$this->db_table_name = $this->table;

		//Log::l($this->db_table_name, 'Member constructor $this->db_table_name', false);
		//Log::l(self::$db_static_table_name, 'Member constructor self::$db_static_table_name', false);

		$this->read_db_table_fields($this->db_table_name);
		$this->read($id);
		$this->member_fields = MemberField::all();
		$this->member_data = new MemberData($id);

	}

	/*
	public function save(){
		if(parent::save()){
			Log::loguear('Member save', $this->get_insert_id(), false);
			$this->memberData->member_id = (!$this->get_insert_id()) ? $this->member_id : $this->get_insert_id();
			return $this->memberData->save();
		}else{
			return false;
		}
	}
	*/

	private static function get_member($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new Member($db->execute_value());
		return $record;
	}

	private static function get_members($sql){
		$db = new DB();
		$db->set_query($sql);
		$record_set = $db->execute();
		$a_elements = array();
		foreach($record_set as $record) {
			 array_push($a_elements, new Member($record->member_id));
		}
		return $a_elements;
	}

	public function save(){
		$result = parent::save();

		if($result){
			$this->member_data->member_id = !$this->get_insert_id() ? $this->member_id : $this->get_insert_id();

			$member_data_result = $this->member_data->save();
			return $member_data_result;
		}else{
			return false;
		}
	}

	public function delete($path=''){
		return self::delete_member($this->id, $path='');
	}

	public static function set_db_table_name($table){
		self::$db_static_table_name = $table;
	}

	public static function all($filter=''){
		self::$db_static_table_name = Tables::MEMBERS;
		$sql = "SELECT member_id FROM " . Tables::MEMBERS . ' ' . ($filter ? ' WHERE ' . $filter : '') . '; ';
		Log::l(Tables::MEMBERS, 'Member all', false);
		Log::l('Member all', $sql, false);
		return self::get_members($sql);
	}

	public static function check_member_status($id){
		$db = new DB();

		$sql = "SELECT " . $this->table_name . ".member_id " .
		" FROM " . $this->table_name .
		" WHERE " .
		$this->table_name .".member_id = " . $id .
		" AND " . $this->table_name . ".authcode IS NULL " .
		" OR " . $this->table_name . ".authcode = ''; ";

		//Log::loguear('Member check_member_status',$sql);
		$value = $db->execute_value($sql);
		//Log::loguear('Member check_member_status',$value);
		return (is_null($value) || $value == '' || $value == false) ? false : true;
	}

	public static function by_name($firstname){
		$sql = "SELECT member_id FROM " . Tables::MEMBERS . " WHERE firstname = '" . $firstname . "'; ";
		Log::l('Member by_name', $sql, false);
		return self::get_member($sql);
	}

	public static function delete_member($id, $path=''){

		if(!$id){
			Log::l('Member delete_member: no Member id received', '', false);
			return;
		}

		$Member = new Member($id);

		if($path){

			$imagefile = $Member->image;
			$ext = end(explode('.', $imagefile));
			$pattern = str_replace('.' . $ext, '', $imagefile);

			Log::l('Member delete_member ',$path . $pattern, false);

			if(is_dir($path)){
				$list = glob($path . $pattern . "*.*");
			}

			foreach ($list as $l) {

				if(file_exists($l)){
					Log::l('Member delete_member: file ', $l, false);
					unlink($l);
				}

			}

		}

		$db = new DB();
		$sql = "DELETE FROM " . Tables::MEMBERS . " WHERE member_id = " . $id . '; ';
		$db->set_query($sql);
		Log::l('Member delete_member', $sql, false);

		return $db->execute_non_query();
	}

}
