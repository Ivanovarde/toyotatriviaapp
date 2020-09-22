<?php
class Frontend extends ABM {
	public static $frontendCode;
	public static $frontendId;
	public static $frontendDefaultLangId;
	public static $frontendLangId;
	public static $dateFormat;

	public static $dynamicFront =					true;

	public function __construct($id="") {
		if(self::$dynamicFront){
			$this->read_db_table_fields(Tables::FRONTENDS);
			$this->read($id);
			$this->frontendCountry = new Country($this->country_id, $this->default_language_id);
			$this->frontendLanguage = new Language($this->default_language_id);
		}
	}

	private static function getFrontend($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new Frontend($db->execute_value());
		return $record;
	}

	public static function byCountryID($id){
		$sql ="SELECT " . Tables::FRONTENDS . ".id " .
			" FROM " . Tables::FRONTENDS .
			" INNER JOIN " . Tables::COUNTRIES . " ON " .
			Tables::COUNTRIES . ".id = " . Tables::FRONTENDS . ".country_id " .
			" WHERE " . Tables::COUNTRIES . ".id " . " = " . $id;

		Log::loguear('Frontend byCountryID',$sql,false);
		return self::getFrontend($sql);
	}

	public static function byCountryCode($code){
		if($code == ''){
			return;
		}
		$sql = "SELECT " . Tables::FRONTENDS . ".id " .
			" FROM " . Tables::FRONTENDS .
			" INNER JOIN " . Tables::COUNTRIES . " ON " .
			Tables::COUNTRIES . ".code = " . Tables::FRONTENDS . ".country_code " .
			" WHERE " . Tables::COUNTRIES . ".code " . " = '" . $code . "' ";

		Log::loguear('Frontend byCountryCode', $sql, false);
		return self::getFrontend($sql);
	}

	public function setFrontendVars(){
		if(self::$dynamicFront){
			self::$frontendCode = $this->frontendCountry->code;
			self::$frontendId = $this->id;
			self::$frontendDefaultLangId = $this->default_language_id;
			self::$frontendLangId = (!isset($_POST['new_language_id']) || $_POST['new_language_id'] == '') ?
										$this->default_language_id : $_POST['new_language_id'];
			self::$dateFormat = Language::getDateFormat(self::$frontendLangId);
		}else{
			self::$frontendCode = (isset($_GET['f']) && $_GET['f'] != '') ? $_GET['f'] : 'es';
			self::$frontendId = 1;
			self::$frontendDefaultLangId = 1;
			self::$frontendLangId = 1;
		}

			Server::$site_urlFront = Server::$site_url . self::$frontendCode . "/";
			Server::$site_url_admFront = Server::$site_url_adm . self::$frontendCode . "/";
	}

	public static function getLanguageFile(){
		if(self::$frontendDefaultLangId == self::$frontendLangId){
			return self::$frontendCode;
		}else{
			$l = new Language(self::$frontendLangId);
			return $l->country_code;
		}
	}

	public static function getFirstFront(){
		$sql = "SELECT id FROM " . Tables::FRONTENDS . " WHERE site_id = " . Site::$siteId;

		return self::getFrontend($sql);
	}

	public static function staticFrontend(){
		self::$dynamicFront = false;

		return new Frontend();

	}

	/*public static function setSiteName(){
		self::$SITENAME = 'AFS-Arquitectos';
	}*/

}
