<?php
class MasterPageAdmin extends MasterPage{

	protected $masterHTMLTemplateName = 		"adm_master";

	public static $panelMessageText = 			'';
	public static $panelMessageClass = 			'';
	public static $rsMenuIzquierdo = 			"";


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

		Log::loguear('MasterPageAdmin __construct', $masterTemplate, true);

		$this->frontend_id = $idF; // revisar si es necesaria esta variable o se puede tomar de frontend seteado en confing

		$this->setTemplatesFolder("assets/templates");

		$this->setMasterHTMLTemplateName($masterTemplate);

		if($template != ''){
			$this->contentTemplateName = $this->templatesFolder . "/" . $template . ".html";

			$this->contentHTML = file_get_contents($this->contentTemplateName);
		}

		// selecciono el html maestro
		$this->masterHTML = file_get_contents($this->templatesFolder . "/" . $this->masterHTMLTemplateName . ".html");

		// Adjunto los archivos externos
		$this->attachFiles();

		$this->mergeHTML();
	}

	function getHTML() {
		// LLamo al metodo de la clase padre
		parent::getHTML();

		$this->set("mainmenu_main", mainmenu_main);
		$this->set("mainmenu_publish", mainmenu_publish);
		$this->set("mainmenu_edit", mainmenu_edit);
		$this->set("mainmenu_users", mainmenu_users);
		$this->set("mainmenu_files", mainmenu_files);
		$this->set("mainmenu_galleries", mainmenu_galleries);
		$this->set("mainmenu_admin", mainmenu_admin);
		$this->set("mainmenu_new_gallery_lnk_txt", mainmenu_new_gallery_lnk_txt);
		$this->set("mainmenu_new_gallery_lnk_url", URL::admGalleryMenu());

		$this->set("message_panel_status", $message_panel_status);
		$this->set("results", results);
		$this->set("order", order);
		$this->set('anyDate', anyDate);
		$this->set('today', today);
		$this->set('yesterday', yesterday);
		$this->set('lastWeek', lastWeek);
		$this->set('lastMonth', lastMonth);
		$this->set('lastSemester', lastSemester);
		$this->set('lastYear', lastYear);
		$this->set('category', category);
		$this->set('open', open);
		$this->set('closed', closed);
		$this->set('status', status);
		$this->set('anyStatus', anyStatus);
		$this->set('search', search);
		$this->set('keyWords', keyWords);
		$this->set('exactMatch', exactMatch);
		$this->set('titles', titles);
		$this->set('titlesEntries', titlesEntries);
		$this->set('titlesEntriesComments', titlesEntriesComments);
		$this->set('comments', comments);
		$this->set('filterByBlog', filterByBlog);
		$this->set('filterBoxTitle', filterBoxTitle);
		$this->set("year", date('Y'));
		$this->set('user_group', user_group);
		$this->set('name', name);
		$this->set('lastname', lastname);
		$this->set('username', username);
		$this->set('email', email);
		$this->set('gender', gender);
		$this->set('male', male);
		$this->set('female', female);
		$this->set('news', news);
		$this->set('asc', asc);
		$this->set('desc', desc);

		// Login
		$this->set("vouchersManager", 'Vouchers Manager');
		$this->set("lbl_user", lbl_user);
		$this->set("lbl_password", lbl_password);
		$this->set("lbl_submit", lbl_submit);

		// Si no estoy en la pagina de login, creo el menu de navegacion
		if($this->getMasterHTMLTemplateName() == "adm_master"){
			$this->makeMainMenu(Frontend::$frontendId, Frontend::$frontendLangId);
		}

		if(isset($_GET['panelMsg']) && $_GET['panelMsg'] != ''){
			$this->setPanelMessage();
		}

		$this->showPanelMessage();

		return $this->mergedHTML;
	}

	private function makeMainMenu($idF, $idL=1) {

		// Traigo los elementos para los menues Publicar y Editar
		$rsMainMenuItems = MainMenu::menuByFrontend($idF, $idL);

		// Menu Publicar
		$publishMenuRepeater = $this->getRepeat("publish_menu");
		Log::loguear("MasterPageAdmin->makeMainMenu publishMenuRepeater: ",$publishMenuRepeater, false);
		for ($m = 0; $m <count($rsMainMenuItems); $m++) {
			Log::loguear("MasterPageAdmin->makeMainMenu: ",$rsMainMenuItems[$m], false);
			$publishMenuRepeater->newElement();
			$publishMenuRepeater->set("publish_menu_lnk_txt", $rsMainMenuItems[$m]->mainMenuData->title);
			$publish_menu_lnk_url = URL::admPublishMenu($rsMainMenuItems[$m]);
			$publishMenuRepeater->set("publish_menu_lnk_url", $publish_menu_lnk_url);
			$publishMenuRepeater->closeElement();
		}
		$this->replaceRepeat($publishMenuRepeater);

		// Menu Editar
		$editMenuRepeater = $this->getRepeat("edit_menu");
		Log::loguear("MasterPageAdmin->makeMainMenu: ",$editMenuRepeater, false);
		for ($m = 0; $m <count($rsMainMenuItems); $m++) {
			Log::loguear("MasterPageAdmin->makeMainMenu: ",$rsMainMenuItems[$m], false);
			$editMenuRepeater->newElement();
			$editMenuRepeater->set("edit_menu_lnk_txt", $rsMainMenuItems[$m]->mainMenuData->title);
			$edit_menu_lnk_url = URL::admEditMenu($rsMainMenuItems[$m]);
			$editMenuRepeater->set("edit_menu_lnk_url", $edit_menu_lnk_url);
			$editMenuRepeater->closeElement();
		}
		$this->replaceRepeat($editMenuRepeater);

		// Menu Blog
		$rsBlogMenuItems = Blog::byFrontend(Frontend::$frontendId, Frontend::$frontendLangId);
		$blogMenuRepeater = $this->getRepeat("blog_menu");
		Log::loguear("MasterPageAdmin->makeMainMenu: ",$blogMenuRepeater, false);
		for ($m = 0; $m <count($rsBlogMenuItems); $m++) {
			Log::loguear("MasterPageAdmin->makeMainMenu: ",$rsBlogMenuItems[$m], false);
			$blogMenuRepeater->newElement();
			$blogMenuRepeater->set("blog_menu_lnk_txt", $rsBlogMenuItems[$m]->blogData->title);
			$blog_menu_lnk_url = URL::admBlogMenu($rsBlogMenuItems[$m]);
			$blogMenuRepeater->set("blog_menu_lnk_url", $blog_menu_lnk_url);
			$blogMenuRepeater->closeElement();
		}
		$this->replaceRepeat($blogMenuRepeater);


		// Menu Galeria
		$galleryMenuRepeater = $this->getRepeat("gallery_menu");
		Log::loguear("MasterPageAdmin->makeMainMenu: ",$galleryMenuRepeater, false);

		$rsgalleryMenu = MainMenu::galleryMenu($idF, $idL);

		for ($m = 0; $m <count($rsgalleryMenu); $m++) {
			Log::loguear("MasterPageAdmin->makeMainMenu: ",$rsgalleryMenu[$m], false);
			$galleryMenuRepeater->newElement();
			$galleryMenuRepeater->set("gallery_menu_lnk_txt", $rsgalleryMenu[$m]->title);

			$gallery_menu_lnk_url = URL::admGalleryMenu($rsgalleryMenu[$m]);
			$galleryMenuRepeater->set("gallery_menu_lnk_url", $gallery_menu_lnk_url);

			$galleryMenuRepeater->closeElement();
		}

		$this->replaceRepeat($galleryMenuRepeater);
	}

	/**
	 * @desc Setea el mensaje para mostrar en el panel de mensajes del administrador cuando se lleva
	 * a cabo algun tipo de accion que tenga que mostrar un resultado
	 * @param string $text El texto del mensaje para mostrar
	 * @param string $class la clase para el mensaje (entry_success_class, entry_error_class, entry_warning_class
	 */
	public function setPanelMessage($text='', $class=entry_success_class){

		$t = $text;
		$c = $class;

		if($_GET['panelMsg'] != ''){
			switch($_GET['panelMsg']){
				case entry_success_url:
					$t = entry_success_msg;
					$c = entry_success_class;
				break;
				case entry_warning_url:
					$t = entry_warning_msg;
					$c = entry_warning_class;
				break;
				case entry_error_url:
					$t = entry_error_msg;
					$c = entry_error_class;
				break;

				default;
					$t = '';
					$c = '';
			}
		}

		self::$panelMessageText = $t;
		self::$panelMessageClass = $c;
	}

	private function showPanelMessage(){
		if(self::$panelMessageText != ''){
			$this->set("panel_message_text", self::$panelMessageText);
			$this->set("panel_message_class", self::$panelMessageClass);
			$this->set("panel_message_display", styleDisplayBlock);
		}else{
			$this->set("panel_message_text", '');
			$this->set("panel_message_class", self::$panelMessageClass);
			$this->set("panel_message_display", styleDisplayNone);
		}
	}

}
?>
