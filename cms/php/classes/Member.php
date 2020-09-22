<?php
class Member extends ABM{

	private $valid_userName = 						false;
	private static $valid_userEmail = 			false;
	private static $valid_userPassword = 		false;
	private $valid_user = 							false;

	public static $logged_user =					'';
	public static $logged_user_group = 			'';
	public static $logged_user_id	= 				false;
	public $current_language_id =					0;
	public $aUserGroups =							array();
	public $aUserStatus =							array();
	public $member_fields_relationships =		array();
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

		// Member fields / Data Mapping
		if(count($this->member_fields) > 0){

			foreach($this->member_fields as $field){
				$fid = $field->m_field_id;
				$fname = $field->m_field_name;
				$fdata_key = 'm_field_id_' . $fid;

				//$current_value = $record->member_data->{$fdata_key};

				$this->member_fields_relationships[$fname] = $fdata_key;

				//$record->member_data->{$fdata_key} = $current_value;

			}

		}

	}

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
		$sql = "SELECT m.member_id FROM " . Tables::MEMBERS . ' m ' .
		'LEFT JOIN ' . Tables::MEMBERDATA . ' md ON md.member_id = m.member_id ' .
		($filter ? ' WHERE ' . $filter : '') . '; ';

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

	public static function by_username($username){
		$sql = "SELECT member_id FROM " . Tables::MEMBERS . " WHERE username = '" . $username . "'; ";
		Log::l('Member by_username', $sql, false);
		return self::get_member($sql);
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

	public function validate_user(){

		$db = new DB();
		$sql = "SELECT member_id FROM " .
				Tables::USERS .
				" WHERE username = '" . $this->username . "' " ;
				//" AND password = '" . $this->password . "' " .
				//" AND status = 1 LIMIT 1";

		//Log::l('User validate_user', $sql, false);

		$this->valid_user = self::get_member($sql);

		//Log::l('User validate_user', $this->valid_user, false);

		if($this->valid_user->member_id != ''){
			return true;
		}else{
			return false;
		}
	}

	public function login(){

		if(!$this->valid_user){
			Log::l('User login valid_user', $this->valid_user, false);
			Session::end();
			return false;
		}else{
			Session::start();
			self::$logged_user = $this->valid_user;
			self::$logged_user_id = $this->valid_user->id;
			self::$logged_user_group = isset($this->valid_user->user_group->group_title) ? $this->valid_user->user_group->group_title : '';

			$_SESSION['u'] = self::$logged_user;
			$_SESSION['logged_user_fields'] = $this->get_logged_user_fields_array();

			Log::l('User login SESSION["u"]', $_SESSION['u'], false);

			//$this->addSessionLog();

			return true;
		}
	}

	public function logout($url){
		Server::redirect($url);
		exit;
	}

	private function get_logged_user_fields_array(){
		$fields = array();
		$table_fields = self::$logged_user->table_fields;

		foreach($table_fields as $field){
			Log::l('User get_logged_user_fields_array table_fields', $field, false);
			$fields['user_' . $field] = $this->$field;
		}
		//$fields['user_initial'] = strtoupper($fields['user_firstname'][0]);

		return $fields;
	}

}
