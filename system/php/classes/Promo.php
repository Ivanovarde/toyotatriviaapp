<?php
class Promo extends ABM {


	function __construct($id=""){
		$this->read_db_table_fields(Tables::PROMOS);
		$this->read($id);

		if($this->lang_id != ''){
			$this->lang = new Language($this->lang_id);
		}

	}

	private static function getPromo($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new Promo($db->execute_value());
		return $record;
	}

	private static function getPromos($sql) {
		$db = new DB();
		$db->set_query($sql);
		$rsAll = $db->execute();
		$vAll = array();
		foreach($rsAll as $record) {
			 $vAll[] = new Promo($record["id"]);
		}
		Log::loguear('Section getPromos', $vAll, false);
		return $vAll;
	}

	public static function byTitle($title){
		$sql = "SELECT id FROM " . Tables::PROMOS . " WHERE title = '" . $title . "'; ";
		Log::loguear('Promo byTitle', $sql, false);
		return self::getPromo($sql);
	}

	public static function byLanguage($lang, $showInactive=false){
		$status = (!$showInactive) ? ' AND status = 1 ' : '';
		$sql = "SELECT " . Tables::PROMOS . ".id FROM " . Tables::PROMOS .
		" LEFT JOIN " . Tables::LANGUAGES . " ON " . Tables::LANGUAGES . ".id = " . Tables::PROMOS . ".lang_id " .
		" WHERE lang_code = '" . $lang . "' " . $status . ' ' .
		" ORDER BY creation DESC LIMIT 1; ";
		Log::loguear('Promo byLanguage', $sql, false);
		return self::getPromo($sql);
	}


	public function delete($path){
		return self::delete_promo($this->id, $path);
	}

	public static function delete_promo($id, $path){
		if(!$id || !$path){
			return;
		}
		$promo = new Promo($id);
		$imagefile = $promo->image;
		$ext = end(explode('.', $imagefile));
		$pattern = str_replace('.' . $ext, '', $imagefile);
		Log::loguear('Promo delete_promo ',$path . $pattern, false);
		if(is_dir($path)){
			$list = glob($path . $pattern . "*.*");
		}
		foreach ($list as $l) {
			if(file_exists($l)){
				Log::loguear('Promo delete_promo: ', $l, false);
				unlink($l);
			}
		}

		$db = new DB();
		$sql = "DELETE FROM " . Tables::PROMOS . " WHERE id = " . $id;
		$db->set_query($sql);
		Log::loguear('Promo delete_promo', $sql, false);

		return $db->execute_non_query();
	}


}

?>
