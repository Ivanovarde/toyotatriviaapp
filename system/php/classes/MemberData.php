<?php
class MemberData extends ABM {

	function __construct($id=""){

		$this->read_db_table_fields(Tables::MEMBERDATA);
		$this->read($id);

		if($id==''){
			$this->primary_key_field = "";
		}else{
			$this->primary_key_field = "member_id";
		}

	}

	private static function get_member_data($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new MemberData($db->execute_value());
		return $record;
	}

	private static function get_member_datas($sql) {
		$db = new DB();
		$db->set_query($sql);
		$record_set = $db->execute();
		$a_elements = array();
		foreach($record_set as $record) {
			 array_push($a_elements, new MemberData($record->id));
		}
		//Log::l('Section get_member_datas', $vAll, false);
		return $a_elements;
	}

	/*
	public static function byTitle($title){
		$sql = "SELECT id FROM " . Tables::PROMOS . " WHERE title = '" . $title . "'; ";
		//Log::loguear('MemberData byTitle', $sql, false);
		return self::get_member_data($sql);
	}
	*/

	/*
	public static function byLanguage($lang, $showInactive=false){
		$status = (!$showInactive) ? ' AND status = 1 ' : '';
		$sql = "SELECT " . Tables::PROMOS . ".id FROM " . Tables::PROMOS .
		" LEFT JOIN " . Tables::LANGUAGES . " ON " . Tables::LANGUAGES . ".id = " . Tables::PROMOS . ".lang_id " .
		" WHERE lang_code = '" . $lang . "' " . $status . ' ' .
		" ORDER BY creation DESC LIMIT 1; ";
		//Log::loguear('MemberData byLanguage', $sql, false);
		return self::get_member_data($sql);
	}
	*/


	public function delete($path){
		return self::delete_member_data($this->id, $path);
	}

	public static function delete_member_data($id, $path){
		if(!$id || !$path){
			return;
		}
		$member_data = new MemberData($id);
		$imagefile = $member_data->image;
		$ext = end(explode('.', $imagefile));
		$pattern = str_replace('.' . $ext, '', $imagefile);
		//Log::loguear('MemberData delete_member_data', $path . $pattern, false);
		if(is_dir($path)){
			$list = glob($path . $pattern . "*.*");
		}
		foreach ($list as $l) {
			if(file_exists($l)){
				//Log::loguear('MemberData delete_member_data: ', $l, false);
				unlink($l);
			}
		}

		$db = new DB();
		$sql = "DELETE FROM " . Tables::MEMBERDATA . " WHERE member_id = " . $id . ";";
		$db->set_query($sql);
		//Log::loguear('MemberData delete_member_data', $sql, false);

		return $db->execute_non_query();
	}


}

