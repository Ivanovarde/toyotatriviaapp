<?php
class EmailTemplate extends ABM{

	private $template;
	private $templateTxt;
	private $folder;
	public $isHTML = true;

	function __construct($id="") {
		if($id != ""){
			$this->read_db_table_fields(Tables::EMAILTEMPLATES);
			$this->read($id);
			$this->template = $this->template_data;
		}
	}

	public function getFile($filename){
		$this->template = file_get_contents($this->folder . $filename);
		$extension = end(explode('.', $filename));
		$txtFile = str_replace($extension, 'txt', $filename);
		if(file_exists($this->folder . $txtFile)){
			$this->templateTxt = file_get_contents($this->folder . $txtFile);
		}
		//Log::loguear('EmailTemplate getFile', $this->folder . $filename, true);
	}

	/*public function getFileTxt($filename){
		$this->templateTxt = file_get_contents($this->folder . $filename);
	}*/

	public function setTemplateFolder($folder){
		$f = dirname(__FILE__) . "/../../" . $folder . "/";
		if(is_dir($f)){
			$this->folder = $f;
		}
		//Log::loguear('EmailTemplate setTemplateFolder', $f, true);
	}

	public function set($key, $value) {
		$this->template = str_replace("{" .$key . "}", $value, $this->template);
		if($this->templateTxt != ''){
			$this->templateTxt = str_replace("{" .$key . "}", $value, $this->templateTxt);
		}
	}

	public function getContent($html=true){
		$this->set('url_youtube', url_youtube);
		$this->set('url_facebook', url_facebook);
		$this->set('url_twitter', url_twitter);

		$this->set('siteBaseFiles', Server::$site_url_files);
		$this->set('siteBaseAssets', Server::$site_url_assets);
		$this->set('siteBaseFront', Server::$site_urlFront);
		$this->set('siteBase', Server::$site_url);

		if($html){
			return $this->template;
		}else{
			return $this->templateTxt;
		}
	}

}
?>
