<?php
class Site extends ABM {
	
	public static $siteId =						0;
	public static $charset =					'';
	public static $siteRootURL =				'';
	public static $siteName =					'';
	
	public static $multifront =					false; // Static
	
	function __construct($id=""){
		
		if(self::$multifront){
			$this->read_db_table_fields(Tables::SITES);
			$this->read($id);
			
			$this->setCharset();
			$this->setSiteRootURL();
			$this->setSiteName();
			self::$siteId = $this->id;
		}
	}
	
	public function setMultifront($value=false){
		if(!$value){
			self::$multifront = false;
		}else{
			self::$multifront = $value;
		}
	}
	
	public function setCharset($charset=''){
		if(empty($charset)){
			self::$charset = "charset=" . $this->defaultCharset;
		}else{
			self::$charset = "charset=" . $charset;
		}
	}
	
	public function setSiteRootURL($url=''){
		if(empty($url)){
			self::$siteRootURL = $this->siteRootURL;
		}else{
			self::$siteRootURL = $url;
		}
	}
	
	public function setSiteName($name=''){
		if(empty($name)){
			self::$siteName = $this->siteName;
		}else{
			self::$siteName = $name;
		}
	}
	
	private static function getSite($sql){
		$db = new DB();
		$db->set_query($sql);
		$record = new Site($db->execute_value());
		return $record;
	}
	
	private static function getSites($sql) {
		$db = new DB();
		$db->set_query($sql);
		$rsAll = $db->execute();
		$vAll = array(); 
		foreach($rsAll as $record) {
			 $vAll[] = new Site($record["id"]);
		}
		return $vAll;		
	}
		
	public static function byServerName(){	
		$sql = "SELECT id FROM " . Tables::SITES . 
				" WHERE siteRootUrl like '%" . str_replace('www.', '', Server::getServerName()) . "' " ;
		
		Log::loguear("Blog::byServerName var sql",$sql, false);
		return self::getSite($sql);
	}
	
	public static function byNameURL($name, $idL=1){
		$sql = "SELECT blog_id FROM " . Tables::BLOGSDATA . " WHERE name_url = '" . $name . "' ";
		
		Log::loguear("Blog::byNameURL var sql",$sql, false);
		return self::getBlog($sql, $idL);
	}	
	
	
	
}

?>