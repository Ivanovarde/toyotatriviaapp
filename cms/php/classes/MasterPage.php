<?php
class MasterPage {

	public $contentTemplateName;
	protected $masterHTML;
	protected $contentHTML;
	protected $mergedHTML;
	protected $pageTitle;
	protected $frontend_id;
	protected $masterHTMLTemplateName = 	"master";
	protected $templatesFolder = 			"templates";

	public $useMainAssetsFile = 			false;
	protected $remakeMainAssetsFiles = 		false;
	protected $useVersionedAssetsFiles = 	false;

	protected static $aReplaceZones = 		Array();
	protected static $aSegments = 			Array();

	/**
	 * @param $idF (int) id del frontend
	 * @param $template (string) nombre del template de contenido
	 * @param $pageTitle (string) titulo de la pagina
	 * @param $masterTemplate (string) opcional. nombre de archivo para usar como pagina maestra
	 * @desc Genera un html, combinando el html de seccion y el html maestro, reemplazando todas las
	 * variables del sistema.
	 */
	function __construct($idF, $template, $pageTitle, $masterTemplate=""){

		$this->pageTitle = $pageTitle;

		$this->frontend_id = $idF; // revisar si es necesaria esta variable o se puede tomar de frontend seteado en confing

		$this->setTemplatesFolder();

		$this->setMasterHTMLTemplateName($masterTemplate);

		if($template != ''){
			//$this->contentTemplateName = dirname(__FILE__) . $this->templatesFolder . "/" . $template . ".html";
			$this->contentTemplateName = $this->templatesFolder . "/" . $template . ".html";

			$this->contentHTML = file_get_contents($this->contentTemplateName);
			Log::loguear('MasterPage __construct', $this->contentHTML, true);
		}

		// selecciono el html maestro
		//$this->masterHTML = file_get_contents(dirname(__FILE__) . $this->templatesFolder . "/" . $this->masterHTMLTemplateName . ".html");
		$this->masterHTML = file_get_contents($this->templatesFolder . "/" . $this->masterHTMLTemplateName . ".html");

		// Adjunto los archivos externos
		$this->attachFiles();

		Log::loguear('MasterPage __construct', $this->masterHTML, true);

		$this->mergeHTML();
	}

	/**
	 * Inserta el html de la plantilla actual dentro del html de la pagina maestra.
	 * El contenido de la plantilla actual es obligatorio, de lo contrario dara un error.
	 */
	public function mergeHTML(){

		try {
			if($this->contentHTML == ''){
				throw new NMDException('No hay contenido para mezclar');
			}
		}
		catch (NMDException $e) {
			$e->showError();
			exit;
		}
		// Reemplazo el contenido ($this->contentHTML) variable de cada seccion,
		// lo mezclo con masterHTML y se lo asigno a la variable mergedHTML
		$this->mergedHTML = str_replace("{blog_content}", $this->contentHTML, $this->masterHTML);
	}

	public function setTemplatesFolder($f=''){
		$p = Server::getDocumentRoot() . '/';
		$this->templatesFolder = ($f == '') ? $p . $this->templatesFolder : $p . $f;
		Log::loguear('setTemplatesFolder', $this->templatesFolder, true);
	}

	public function getTemplatesFolder(){
		return $this->templatesFolder ;
	}

	public function getContentTemplateName(){
		return $this->contentTemplateName;
	}

	public function setMasterHTMLTemplateName($f=''){
		$this->masterHTMLTemplateName = ($f == '') ? $this->masterHTMLTemplateName : $f;
	}

	public function getMasterHTMLTemplateName(){
		return $this->masterHTMLTemplateName;
	}

	public function getTitle(){
		return $this->pageTitle;
	}

	protected function set($key, $value) {
		$this->mergedHTML = str_replace("{" .$key . "}", $value, $this->mergedHTML);
	}

	function getRepeat($tag) {
		try {
			$tagInitPos = strpos($this->mergedHTML, '{' . $tag . '}');
			$tagEndPos = strpos($this->mergedHTML, '{/' . $tag . '}');
			$tagLength = strlen($tag) + 2;

			if(!$tagInitPos || !$tagEndPos){
				throw new NMDException('No se pudo encontrar la etiqueta a repetir: ' . $tag);
			}

			$repeat = substr($this->mergedHTML, $tagInitPos + $tagLength, $tagEndPos - $tagInitPos - $tagLength);
			return new RepeatZone($tag, $repeat);

		} catch (NMDException $e) {
			$e->showError();
		}
	}

	function replaceRepeat($repeatZone) {
		$tag = $repeatZone->tag;
		$tagInitPos = strpos($this->mergedHTML, '{' . $tag . '}');
		$tagEndPos = strpos($this->mergedHTML, '{/' . $tag . '}');

		$clonHTML = $this->mergedHTML;
		$this->mergedHTML = substr($clonHTML, 0, $tagInitPos);
		$this->mergedHTML .= $repeatZone->outputHTML;
		$this->mergedHTML .= substr($clonHTML, $tagEndPos + strlen($tag) + 3);
	}

	function getHTML(){

		$this->replaceCodeData();

		$this->replacePermissions();

		$this->setSegments();

		$this->replaceSegments();

		//$this->replaceConditionals();

		$this->repeatData();

		$this->replacedata();

		$this->replace();

		// Asigno todas la variables de servidor para ser reemplazadas
		$this->replaceSystemVars();

		// Autoversion
		$this->setAutover();

		return $this->mergedHTML;
	}

	protected function setSegments(){

		$str = trim(Server::getRequestUri(),'/');
		$tempSegments = explode('/',$str);

		for($i = 1; $i < count($tempSegments) + 1; $i++){
			self::$aSegments[$i] = $tempSegments[$i - 1];
		}
	}

	protected function replaceSegments(){
		if(self::$aSegments != ''){
			for($i = 1; $i < count(self::$aSegments) + 1; $i++){
				$this->set('segment_' . $i, self::$aSegments[$i]);
				Log::loguear('MasterPage replaceSegments', 'segment_' . $i . " | " . self::$aSegments[$i], false);
			}
		}
	}

	public static function getSegments($pos=''){

		self::setSegments();

		Log::loguear('MasterPage getSegments', self::$aSegments, false);

		if(self::$aSegments == ''){
			return;
		}

		if($pos == ''){
			return self::$aSegments;
		}

		if($pos != '' && is_numeric($pos)){
			return self::$aSegments[$pos];
		}
	}

	protected function replaceConditionals(){
		$searchRegex = "|\{if\:(.*)\}(.*)\{/if\}|msU";
		$conditionals = preg_match_all($searchRegex, $this->mergedHTML,	$match, PREG_PATTERN_ORDER);
		Log::loguear('MasterPage replaceConditionals', $match, true);

		$evaluation = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[1][0]));
		$content = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[2][0]));

		// Busco dentro del contenido si hay else
		$searches = array("/\{if\:else\}/");
		/*$replaces = array("?>")*/
		//$searchRegex = "/\{((if:else)*if)\s+(.*?)\}/msU";
		preg_match_all($searchRegex, $content,	$blocks, PREG_PATTERN_ORDER);
		//$blocks = preg_split($searchRegex, $content);
		Log::loguear('MasterPage replaceConditionals $blocks', $blocks, true);


		$r = eval("return " . $evaluation . ";");
		Log::loguear('MasterPage replaceConditionals', $r, true);


		/*
		$str = "if (5 == 5): ?>A is equal to 5<? endif; ";
		eval("\$str = \"$str\";");
		Log::loguear('MasterPage replaceConditionals r', $str, true);
		*/

		if($r){
			echo 'anda';
		}else{
			echo 'no anda';
		}

	}

	/**
	 * @desc Reemplaza porciones de codigo desde / hasta
	 * @param $tag string la etiqueta a reemplazar en el markup
	 */
	protected function replaceCodeData(){
		$searchRegEx = "|\{replacecodedata:usergroup\=\"(.*)\"(.*)\}(.*)\{\/replacecodedata\}|msU";
		$matches = preg_match_all($searchRegEx, $this->mergedHTML, $match, PREG_PATTERN_ORDER);

		for($i = 0; $i < $matches; $i++){

			$data['user_group'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[1][$i]));
			$data['parameters'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[2][$i]));
			$data['htmlTargetCode'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[3][$i]));
			$data['htmlSourceCode'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[0][$i]));

			$allowedGroups = explode('|', $data['user_group']);

			if(in_array(User::$logged_user_id, $allowedGroups)){
				$this->mergedHTML = preg_replace($searchRegEx, $data['htmlTargetCode'], $this->mergedHTML, 1);
			}else{
				$this->mergedHTML = preg_replace($searchRegEx, '', $this->mergedHTML, 1);
			}
		}
	}

	/**
	 * @desc Reemplaza porciones de codigo desde / hasta segun permisos de usuario en user_groups
	 */
	protected function replacePermissions(){
		$searchRegEx = "|\{replacepermission:action\=\"(.*)\"(.*)\}(.*)\{\/replacepermission\}|msU";
		$matches = preg_match_all($searchRegEx, $this->mergedHTML, $match, PREG_PATTERN_ORDER);

		for($i = 0; $i < $matches; $i++){

			$data['action'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[1][$i]));
			$data['parameters'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[2][$i]));
			$data['htmlTargetCode'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[3][$i]));
			$data['htmlSourceCode'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[0][$i]));

			$currentUserGroup = $_SESSION['u']->user_group; //$logged_user;
			$currentAction = $data['action'];

			Log::loguear('Masterpage replacePermissions', $_SESSION['u']->user_group->group_title . ' | ' . $currentAction . ' | ' . $currentUserGroup->$currentAction, false);

			if($currentUserGroup->id == 1 || $currentUserGroup->$currentAction){
				$this->mergedHTML = preg_replace($searchRegEx, $data['htmlTargetCode'], $this->mergedHTML, 1);
			}else{
				$this->mergedHTML = preg_replace($searchRegEx, '', $this->mergedHTML, 1);
			}
		}
	}

	/**
	 * @desc Reemplaza el markup del html por los valores correspondientes tomados desde la BD
	 * la sintaxis que se debe usar es:
	 * {replace:blog|blogData->title} donde replace:blog indica que se reemplazara del blog actual y
	 * blogData->title indica el campo que se debe tomar como valor para el reemplazo
	 */
	protected function replaceData(){
		$searchRegEx = "|\{replacedata\:data\=\"(.*)\".*blog\=\"(.*)\"(.*)\}|U";
		$matches = preg_match_all($searchRegEx, $this->mergedHTML,	$match, PREG_PATTERN_ORDER);

		Log::loguear('MasterPage replaceData', $match, false);

		for($i = 0; $i < $matches; $i++){

			$data['dataTable'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[1][$i]));
			$data['blogTitleSeo'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[2][$i]));
			$data['parameters'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[3][$i]));
			$data['textToReplace'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[0][$i]));

			switch($data['dataTable']){
				case 'entries':
					$d = Entry::byData($data, false);
					Log::loguear('MasterPage ReplaceData',$d[0]->id, false);
				break;
				case 'blogs':
					$d = Blog::byData($data);
				break;
			}

			for($j = 0; $j < count($d); $j++){
				$out[$i] .= $data['textToReplace'];

				$tags = preg_match_all("|field=\"(.*)\"|U", $out[$i], $t, PREG_PATTERN_ORDER);
				Log::loguear('MasterPage replaceData', $t, false);

				$finalVariable = '';
				$counter = 0;

				for($k = 0; $k < count($t[1]); $k ++){

					// Separo la variable a reemplazar tomada en el html (ej entryData->title)
					$var = explode('->',$t[1][$k]);

					// Itero el camino hacia la variable final tomada desde el html
					// ej entryData (->) title
					foreach($var as $variable){

						// Me fijo si el contador esta en 0 para seguir iterando hacia la variable final.
						// Asigno la variable actual a una variable nueva para mantener el valor en la iteracion
						$v = ($counter == 0) ? $d[$j]->$variable : $finalVariable->$variable;
						$finalVariable = $v;

						// Me fijo si estoy procesando entradas
						switch(strtolower($data['dataTable'])){
							case 'entries':
								// Proceso la entrada para darle el formato seteado en cada FormField de la entrada
								// ejemplo nl2br()
								foreach($d[$j]->aBlogFields as $field){
									Log::loguear('MasterPage replacedata', $field->fieldname . ' ' . $variable, false);
									if($field->fieldname == $variable){

										// Traigo los valores de los formatos (seteado en adm_lang)
										$aFormats = FormField::getFormfieldFormats();

										switch($field->format){
											case $aFormats[0][0]:
												$finalVariable = strip_tags($finalVariable);
											break;
											case $aFormats[1][0]:
												$finalVariable = nl2br($finalVariable);

											break;
											case $aFormats[2][0]: //'html

											break;
										}
									}
								}
							break;
						}

						Log::loguear('MasterPage replacedata',$finalVariable, false);

						if($counter == count($var)-1){$counter = 0;}else{$counter++;}
					}

					Log::loguear('MasterPage replacedata ',$out[$i].' '.$finalVariable, false);

					$out[$i] = str_replace($out[$i], $finalVariable, $out[$i]);
				}
			}

			$this->mergedHTML = preg_replace($searchRegEx, $out[$i], $this->mergedHTML, 1);

		}
	}

	/**
	 * Busca partes que se iteran en el html para reemplazarlo con los valores tomados desde la BD.
	 * La sintaxis que se debe usar es:
	 * {repeat:data="entries" blog="clientes" orderby="date" sort="desc" limit=3}
	 * <a href="{replace:entryURL}">{replace:entryData->title}</a>
	 * {/repeat}
	 * data="entries" indica la procedencia de los datos.
	 * blog="clientes" indica el blog en el cual buscar.
	 * Los demas parametros son los filtros de busqueda para los registros.
	 * Entre las etiquetas de apertura y cierre {repeat} {/repeat} se puede usar html regular
	 * con markup para reemplazar los fragmentos necesarios como {replace:entryURL}, este fragmento ser� reemplazado por
	 * la variable entryURL de cada entrada en la iteraci�n.
	 */
	protected function repeatData(){
		$searchRegEx = "|\{repeat:data\=\"(.*)\".*blog\=\"(.*)\"(.*)\}(.*)\{\/repeat\}|msU";
		$matches = preg_match_all($searchRegEx, $this->mergedHTML, $match, PREG_PATTERN_ORDER);

		Log::loguear('MasterPage repeatData', $match, false);
		for($i = 0; $i < $matches; $i++){

			$data['dataTable'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[1][$i]));
			$data['blogTitleSeo'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[2][$i]));
			$data['parameters'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[3][$i]));
			$data['textToReplace'] = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', trim($match[4][$i]));

			switch($data['dataTable']){
				case 'entries':
					$d = Entry::byData($data);
					Log::loguear('MasterPage repeatData', $d[0]->aBlogFields[0], false);
				break;
				case 'blogs':
					$d = Blog::byData($data);
				break;
			}

			for($j = 0; $j < count($d); $j++){
				$out[$i] .= $data['textToReplace'];

				$tags = preg_match_all("|\{replace:(.*)\}|U", $out[$i], $t, PREG_PATTERN_ORDER);
				Log::loguear('MasterPage repeatData', $t, false);
				Log::loguear('MasterPage repeatData', $d[$j]->entryCountry->countryData->name, false);

				$finalVariable = '';
				$counter = 0;

				for($k = 0; $k < count($t[1]); $k ++){

					// Separo la variable a reemplazar tomada en el html (ej entryData->title)
					$var = explode('->',$t[1][$k]);

					// Itero el camino hacia la variable final tomada desde el html
					// ej entryData (->) title
					foreach($var as $variable){

						// Me fijo si el contador esta en 0 para seguir iterando hacia la variable final.
						// Asigno la variable actual a una variable nueva para mantener el valor en la iteracion
						$v = ($counter == 0) ? $d[$j]->$variable : $finalVariable->$variable;
						$finalVariable = $v;

						// Me fijo si estoy procesando entradas
						switch(strtolower($data['dataTable'])){
							case 'entries':
								// Proceso la entrada para darle el formato seteado en cada FormField de la entrada
								// ejemplo nl2br()
								foreach($d[$j]->aBlogFields as $field){
									Log::loguear('MasterPage repeatData', $field->fieldname . ' ' . $variable, false);
									if($field->fieldname == $variable){

										// Traigo los valores de los formatos (seteado en adm_lang)
										$aFormats = FormField::getFormfieldFormats();

										switch($field->format){
											case $aFormats[0][0]:
												$finalVariable = strip_tags($finalVariable);
											break;
											case $aFormats[1][0]:
												$finalVariable = nl2br($finalVariable);

											break;
											case $aFormats[2][0]: //'html

											break;
										}
									}
								}
							break;
						}

						Log::loguear('MasterPage repeatData',$finalVariable, false);

						if($counter == count($var)-1){$counter = 0;}else{$counter++;}
					}

					Log::loguear('MasterPage repeatData ',$t[0][$k].' '.$finalVariable, false);

					$out[$i] = str_replace($t[0][$k], $finalVariable, $out[$i]);
				}
			}

			$this->mergedHTML = preg_replace($searchRegEx, $out[$i], $this->mergedHTML, 1);

		}
		//{repeat:entries blog="clientes" orderby="date" limit="15" }

		//$f = Entry::
		/*
		author_id="1"

		category="1"
		language="1"
		frontend="1"
		display_by="month"
		entry_id="147"
		limit="10"
		orderby="date"
		sort="asc"
		status="open"
		featured="1"
		title_seo="my_wedding"
		username="petunia"
		blog="news"
		year="2003"
		month="12"
		day="23"
		//cache="yes" refresh="60"
		//entry_id_from="20"
		//entry_id_to="40"
		//fixed_order="3|7|1"
		//group_id="4"
		//paginate="top"
		//paginate_base="site/index"
		//paginate_type="field"
		*/
	}

	protected function replaceSystemVars($context=''){

		$systemVars = array(
			// Server Vars
			"protocol" => Server::$protocol,
			"currentURL" => Server::$current_url,
			"siteBase" => Server::$site_url,
			"siteBaseFront" => Server::$site_urlFront,
			"siteBaseAssets" => Server::$site_url_assets,
			"siteBaseJs" => Server::$site_url_scripts,
			"siteBaseCss" => Server::$site_url_stylesheets,
			"siteBaseBullets" => Server::$site_url_assets_images,
			"siteBaseImg" => Server::$site_url_images,
			"siteBaseFiles" => Server::$site_url_files,

			"siteBaseAdm" => Server::$site_url_adm,
			"siteBaseAdmFront" => Server::$site_url_admFront,
			"siteBaseAdmBullets" => Server::$site_url_adm_images,
			"siteBaseCupones" => SITEBASECUPONES,

			"selfname" => Server::$selfname,
			"querystring" => Server::getQueryString(),

			// Site Vars
			"charset" => Site::$charset,
			"siteRootURL" => Site::$siteRootURL,
			"siteName" => Site::$siteName,

			// Frontend Vars
			"frontEndCode" => Frontend::$frontendCode,
			"frontEndID" => Frontend::$frontendId,

			// System Vars
			"date" => date,
			"dateTime" => dateTime,
			"styleDisplayNone" => styleDisplayNone,
			"styleDisplayBlock" => styleDisplayBlock,

			// User Vars
			"logged_user" => (class_exists(User)) ? User::$logged_user : '',
			"logged_user_group" => (class_exists(User)) ? User::$logged_user_group : '',

			// HTML Vars
			"pageTitle" => $this->pageTitle
		);

		foreach($systemVars as $var => $value){
			if(!$context){
				$this->set($var, $value);
			}else{
				$context = str_replace('{' . $var . '}', $value, $context);
			}
		}

		if($context){
			return $context;
		}
	}

	public function setAutover(){
		$qtty = preg_match_all("|\{autoVer\:(.*)\}|U", $this->mergedHTML,	$matches, PREG_PATTERN_ORDER);

		for($i=0; $i < $qtty; $i++){
			$this->set( substr($matches[0][$i], 1, strlen($matches[0][$i])-2), $this->getAutoVer($matches[1][$i]) );
			Log::loguear('MasterPage setAutoVer', $matches[0][$i] .' => '. $matches[1][$i],false);
		}
	}

	public function getAutoVer($url){

		Log::loguear('MasterPage getAutoVer','busco : '.
					Server::$site_url_assets." en: ".$url.' : '.
					preg_match("/".preg_quote(Server::$site_url_assets,'/')."/i", $url),false);

		if(preg_match("/".preg_quote(Server::$site_url_assets,'/')."/i", $url)){

			preg_match( "|".preg_quote(Server::$site_url_assets,'/')."(.*)\/|U", $url, $matches);

			Log::loguear('MasterPage getAutoVer', $matches, false);

			switch($matches[1]){
				case Server::FOLDER_IMAGES:
				case Server::FOLDER_ASSETS_IMAGES:
				case Server::FOLDER_SWF:
				case Server::FOLDER_FILES:
				case Server::FOLDER_ASSETS_IMAGESADM:
					$dnsName = Server::$dnsImages;
				break;

				case Server::FOLDER_STYLESHEETS;
				case Server::FOLDER_SCRIPTS;
					$dnsName = Server::$dnsScripts;
				break;
			}

			$folderType = $matches[1];
			$fileName = end(explode($folderType, $url));

			Log::loguear('MasterPage getAutoVer $fileName', $fileName, false);

			$fullFileName = Server::FOLDER_ASSETS . '/' . $folderType . $fileName;

			Log::loguear('MasterPage getAutoVer $fullFileName', $fullFileName, false);

			return $this->getFileVersion($dnsName,$fullFileName);
		}
	}

	public function getFileVersion($dnsName,$fullFileName){
		Log::loguear('MasterPage getFileVersion document root',$_SERVER['DOCUMENT_ROOT'],false);
		try{
			$searchedFile = Server::getDocumentRoot() . '/' . $fullFileName;
			if(!file_exists($searchedFile)){
				throw new NMDFileException($searchedFile . ' | El archivo no existe');
			}
		}
		catch (NMDFileException $e){
			$e->showError();
			exit;
		}

		$pathinfo = pathinfo($fullFileName);

		$fileVersion = filemtime(Server::getDocumentRoot() . '/' . $fullFileName);

		$outputFileName =   $dnsName .
							$pathinfo['dirname'] . '/' .
							$pathinfo['filename'] .
							'.' .
							$pathinfo['extension'];

		$outputVersionedFileName =   $dnsName .
							$pathinfo['dirname'] . '/' .
							$pathinfo['filename'] .
							'.v_' . $fileVersion . '.' .
							$pathinfo['extension'];

		Log::loguear('MasterPage getAutoVer',$outputFileName, false);

		return ($this->useVersionedAssetsFiles) ? $outputVersionedFileName : $outputFileName;
	}

	protected function attachFiles(){
		$searchRegEx = "|\<attachfiles\>.*tag\{(.*)\} attr\{(.*)\} browser\{(.*)\}.*sources\[(.*)].*\<\/attachfiles\>|msU";
		preg_match_all($searchRegEx, $this->masterHTML, $match, PREG_PATTERN_ORDER);

		Log::loguear('MasterPage attachFile', $match, false);

		$f['tag'] = $match[1];
		$f['attr'] = $match[2];
		$f['browser'] = $match[3];
		$f['sources'] = $match[4];

		for($i = 0; $i < count($f['tag']); $i++){

			$src = explode(',',$f['sources'][$i]);

			foreach($src as $file){

				$fileSource = preg_replace("/(?:(?:\r\n|\r|\n)\s*)/s", '', $file);
				$fileSource = $this->replaceSystemVars($fileSource);
				$fileSourceCropped = end(explode('/', $fileSource));

				$folderCache = dirname(__FILE__) . '/../' . Server::FOLDER_CACHE . '/';

				Log::loguear('File Exists? ',$folderCache . $fileSourceCropped, false);
				$cachedFileSize = file_exists($folderCache . $fileSourceCropped) ? filesize($folderCache . $fileSourceCropped) : 0;

				switch($f['tag'][$i]){
					case 'script':
						$folderSource = dirname(__FILE__) . '/../' . Server::FOLDER_ASSETS . '/' . Server::FOLDER_SCRIPTS . '/';
						$sourceFileSize = filesize($folderSource . $fileSource);
						$filename = 'mainScript_js.js';
						$autoverOutput[$f['tag'][$i]][$i] .= '<script ' . $f['attr'][$i] . ' src="{autoVer:{siteBaseJs}' . $fileSource . '}"></script>' . "\n";
						$externalFile[$f['tag'][$i]][$i] = '<script ' . $f['attr'][$i] . ' src="' . Server::$site_url_cache . $filename . '" ></script>' . "\n";
					break;

					case 'link':
						$folderSource = dirname(__FILE__) . '/../' . Server::FOLDER_ASSETS . '/' . Server::FOLDER_STYLESHEETS . '/';
						$sourceFileSize = filesize($folderSource . $fileSource);
						$filename = 'mainScript_css.css';
						$autoverOutput[$f['tag'][$i]][$i] .= '<link href="{autoVer:{siteBaseCss}' . $fileSource . '}" ' . $f['attr'][$i] . ' />' . "\n";
						$externalFile[$f['tag'][$i]][$i] = '<link href="' . Server::$site_url_cache . $filename . '" ' . $f['attr'][$i] . ' />' . "\n";
					break;
				}

				if($cachedFileSize != $sourceFileSize){
					$this->remakeMainAssetsFiles = true;
					$file = new FileWriter(false);
					$fileConents = $this->replaceSystemVars(file_get_contents($folderSource . $fileSource));
					$file->write($folderCache . $fileSourceCropped, $fileConents);
				}

				Log::loguear('cahcedFilseSize | sourceFileSize', $fileSource . ' ' . $cachedFileSize . ' | ' . $sourceFileSize, false);

				$externalContent[$f['tag'][$i]] .= file_get_contents($folderCache . $fileSource);

				if($f['browser'][$i] != 'ie'){
					if($this->remakeMainAssetsFiles){
						$mainAssetsfile = new FileWriter(false);
						$mainAssetsfile->write(Server::FOLDER_CACHE . '/' . $filename, $externalContent[$f['tag'][$i]]);
					}
				}
				$this->remakeMainAssetsFiles = false;
			} // End foreach src as file

			$outputFile = $autoverOutput[$f['tag'][$i]][$i];
			$external = $externalFile[$f['tag'][$i]][$i];

			$replaceRegEx = "|\<attachfiles\>(.*tag\{" . $f['tag'][$i] . "\}.*)\<\/attachfiles\>|msU";

			if($this->useMainAssetsFile && $f['browser'][$i] != 'ie'){
				$this->masterHTML = preg_replace($replaceRegEx, $external, $this->masterHTML, 1);
			}else{
				$this->masterHTML = preg_replace($replaceRegEx, $outputFile, $this->masterHTML, 1);
			}
		}// End for tags
	}

	/**
	 * Agrega un nuevo reemplazo al array $aReplaceZones. Luego cuando se ejecuta el metodo getHTML()
	 * este a su vez ejecuta el metodo replace() que hace el reemplazo masivo de todos los valores del
	 * array $aReplaceZones en el html final
	 * @param string $tag
	 * @param string $value
	 */
	public function replaceZone($tag, $value){
		self::$aReplaceZones[$tag] = $value;
	}

	/**
	 * Se activa en el getHTML y reemplaza todas los valores del array $aReplaceZones en el html final
	 */
	protected function replace(){
		foreach(self::$aReplaceZones as $key => $value){
			$this->set($key, $value);
		}
	}

}
?>
