<?php
class Prospect extends ABM {


	function __construct($id=""){
		$this->read_db_table_fields(Tables::PROSPECTS);
		$this->read($id);
	}

	private static function get_prospect($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new Prospect($db->execute_value());
		return $record;
	}

	private static function get_prospects($sql) {
		$db = new DB();
		$db->set_query($sql);
		$rsAll = $db->execute();
		$vAll = array();
		foreach($rsAll as $record) {
			 array_push($vAll, new Prospect($record->member_id));
		}
		return $vAll;
	}


	public static function all($filter=''){
		$sql = "SELECT member_id FROM " . Tables::PROSPECTS . ' ' . ($filter ? ' WHERE ' . $filter : '') . '; ';
		Log::l('Prospect all', $sql, false);
		return self::get_prospects($sql);
	}

	public static function by_name($firstname){
		$sql = "SELECT member_id FROM " . Tables::PROPSPECTS . " WHERE firstname = '" . $firstname . "'; ";
		Log::l('Prospect by_name', $sql, false);
		return self::get_prospect($sql);
	}

	//public static function byLanguage($lang, $showInactive=false){
	//	$status = (!$showInactive) ? ' AND status = 1 ' : '';
	//	$sql = "SELECT " . Tables::PROPSPECTS . ".id FROM " . Tables::PROPSPECTS .
	//	" LEFT JOIN " . Tables::LANGUAGES . " ON " . Tables::LANGUAGES . ".id = " . Tables::PROPSPECTS . ".lang_id " .
	//	" WHERE lang_code = '" . $lang . "' " . $status . ' ' .
	//	" ORDER BY creation DESC LIMIT 1; ";
	//	Log::l('Prospect byLanguage', $sql, false);
	//	return self::get_prospect($sql);
	//}


	public function delete($path=''){
		return self::delete_prospect($this->id, $path='');
	}

	public static function delete_prospect($id, $path=''){

		if(!$id){
			Log::l('Prospect delete_prospect: no Prospect id received', '', false);
			return;
		}

		$Prospect = new Prospect($id);

		if($path){

			$imagefile = $Prospect->image;
			$ext = end(explode('.', $imagefile));
			$pattern = str_replace('.' . $ext, '', $imagefile);

			Log::l('Prospect delete_prospect ',$path . $pattern, false);

			if(is_dir($path)){
				$list = glob($path . $pattern . "*.*");
			}

			foreach ($list as $l) {

				if(file_exists($l)){
					Log::l('Prospect delete_prospect: file ', $l, false);
					unlink($l);
				}

			}

		}

		$db = new DB();
		$sql = "DELETE FROM " . Tables::PROSPECTS . " WHERE member_id = " . $id . '; ';
		$db->set_query($sql);
		Log::l('Prospect delete_prospect', $sql, false);

		return $db->execute_non_query();
	}


}
