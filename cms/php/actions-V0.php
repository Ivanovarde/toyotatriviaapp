<?PHP

header('Access-Control-Allow-Origin: *'); //for all


include('config.php');

/*************************************
 * LOCALIZATION
 /************************************/
if(!file_exists($global_vars['localization_filename_path'])){
	$global_vars['localization_filename_path'] = $global_vars['site_path'] . '/' . $config['localization_path'] .  $global_vars['localization_filename_default'];

	if($config['debug']){
		echo 'Renderizer: Loading Localization lang file - File: ' . $global_vars['localization_filename_path'] . ' <strong>NOT found</strong><br>';
	}
}
if(file_exists($global_vars['localization_filename_path'])){

	if($config['debug']){
		echo 'Renderizer: Loading Localization lang file <strong>FOUND</strong> - File: ' . $global_vars['localization_filename_path'] . ' <br>';
	}
	include($global_vars['localization_filename_path']);
}


//$_GET = array_sanitize($_GET);
$_POST = array_sanitize($_POST);

foreach($_POST as $k => $v){
	$_POST[$k] = strip_tags($v);
	${$k} = $_POST[$k];
}

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';
$action = (($action == '') && (isset($_POST['action']) && $_POST['action'] != '')) ? $_POST['action'] : $action;
$action = strtolower($action);

Log::l('actions.php var $action', $action, false);

$p = Functions::array_sanitize($_POST);
$g = Functions::array_sanitize($_GET);
$datetime = date('Y-m-d H:i:s');
$hour = date('H');
$relative_path = Server::getRelativeRootPath();

if(isset($g['logout']) && $g['logout'] != false){

	 //Session::end();

	 //echo get_section($_GET['section']);
	 //exit;
}


$allowed_no_session_actions = array('signin', 'signup', 'forgot', 'edit', 'add', 'store');


if(!Session::check_user_session() && !in_array($action, $allowed_no_session_actions)){
	$r['status'] = false;
	$r['url'] = $global_vars['site_url'] . '/';
	$r['msg'] = 'La sesión expiró';
	$r['error'] = '';

	echo json_encode($r);
	exit;
}


if(Session::check_user_session()){
	$user = new User($_SESSION['u']->id);
	Log::l('actions.php $_SESSION["u"]', $_SESSION['u'], false);
}


Log::l('actions Session::check_user_session()', Session::check_user_session(), false);

switch($action){

	case 'signin':

		$u = User::by_username($p['username']);

		Log::l('actions.php signin', $u, false);

		if($u->id == ''){
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'El usuario ' . $p['username'] . ' no existe.<br>Por favor chequea los datos.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		if($u->password != $p['password']) {
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'La contraseña ingresada no es correcta.<br>Por favor chequea los datos.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		if(!$u->validate_user()) {
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'El usuario ingresado aún no se encuentra activo.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		if($u->login()){

			$r['status'] = true;
			$r['title'] = 'Atención';
			$r['msg'] = 'Aguarda unos instantes.<br>Ingresando al sistema.';
			$r['url'] = sep($config['folder'], 'l') . '/prospectos/';
			$r['error'] = '';
			$r['class'] = 'success';
		}else{

			Session::end();

			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'Ha ocurrido un error, prueba nuevamente en unos minutos.';
			$r['error'] = '';
			$r['class'] = 'danger';
		}

		echo json_encode($r);
		exit;

	break;

	case 'edit_form':

		if(!isset($_POST['id'])){
			$r['status'] = false;
			$r['title'] = $lang['text_error'];
			$r['html'] = '';
			$r['msg'] = $lang['edit_form_no_id'];
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		if(!isset($_POST['mode'])){
			$r['status'] = false;
			$r['title'] = $lang['text_error'];
			$r['html'] = '';
			$r['msg'] = $lang['edit_form_no_mode'];
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		$id = $_POST['id'];
		$mode = $_POST['mode'];
		$template_edit_form_path = $config['templates_path'] . 'includes/' . $mode . '-form.html';
		$template_edit_form = getContent( $template_edit_form_path, 'actions.php (' . $action . ')');

		$prospect = new Prospect($id);

		if($prospect->id){
			$template_edit_form = set('id', $prospect->id, $template_edit_form);
			$template_edit_form = set('firstname', $prospect->firstname, $template_edit_form);
			$template_edit_form = set('lastname', $prospect->lastname, $template_edit_form);
			$template_edit_form = set('email', $prospect->email, $template_edit_form);
			$template_edit_form = set('phone', $prospect->phone, $template_edit_form);
			//$template_edit_form = set('state', $prospect->state, $template_edit_form);
			$template_edit_form = set('city', $prospect->city, $template_edit_form);
			$template_edit_form = set('checked', ($prospect->email_sent ? 'checked' : ''), $template_edit_form);

			$r['status'] = true;
			$r['title'] = $lang['edit_form_title'] . '(' . $prospect->id . ') ' . $prospect->firstname . ' ' . $prospect->lastname;
			$r['html'] = $template_edit_form;
			$r['msg'] = '';
			$r['error'] = '';
			$r['class'] = '';
		}else{
			$r['status'] = false;
			$r['title'] = $lang['text_error'];
			$r['html'] = $lang['edit_form_no_results'];
			$r['msg'] = '';
			$r['error'] = '';
			$r['class'] = '';
		}

		echo json_encode($r);
		exit;

	break;

	case 'store':

		// Proceso el Stream recibido
		$caracteresEliminar = array("\\", "[", "]","\"");
		$string_resultado = str_replace($caracteresEliminar, "", $_GET['storedRecords']);

		$registrosProcesados = explode(",", $string_resultado) ;

		$prospect = new Prospect();

		$prospect->firstname = ucfirst(strtolower($registrosProcesados[10]));
		$prospect->lastname = ucfirst(strtolower($registrosProcesados[11]));
		$prospect->email = strtolower($registrosProcesados[12]);
		$prospect->phone = $registrosProcesados[13];
		$prospect->state = $registrosProcesados[14];
		$prospect->city = ucfirst(strtolower($registrosProcesados[15]));

		//Valido que vengan los datos del formulario
		if( !isset($prospect->firstname) || !isset($prospect->lastname) || !isset($prospect->email) || !isset($prospect->phone) || !isset($prospect->state) || !isset($prospect->city) ){

			$r['status'] = false;
			$r['html'] = '';
			$r['msg'] = 'Todos los datos son requeridos';
			$r['modal'] = '#modal-edit';
			$r['error'] = '';
			$r['class'] = '';

			echo json_encode($r);
			exit;
		}

		$prospect->cuotas = $registrosProcesados[0];
		$prospect->cuota_mensual = str_replace("|","", $registrosProcesados[1]);
		$prospect->precio_publico = str_replace("|","", $registrosProcesados[2]);
		$prospect->plan = $registrosProcesados[3];
		$prospect->cuota_pura = str_replace("|","", $registrosProcesados[4]);
		$prospect->carga_admin_suscripcion = str_replace("|","", $registrosProcesados[5]);
		$prospect->iva_21 = str_replace("|","", $registrosProcesados[6]);
		$prospect->pago_adjudicacion_30 = empty($registrosProcesados[7]) ? '0.00' : str_replace("|","", $registrosProcesados[7]);
		$prospect->modelo = $registrosProcesados[8];
		//$prospect->plan = $registrosProcesados[9];

		$line_tmp = $registrosProcesados[16];
		$tipo_vehiculo_tmp = $registrosProcesados[17];


		$prospect->tipo_vehiculo = $registrosProcesados[17];

		$prospect->created = date('Y-m-d H:i:s');
		$prospect->email_sent = 0;
		$prospect->email_sent_date = null;

		// Determino la Linea del Vehiculo
		switch ($tipo_vehiculo_tmp) {

			case 'bus':
				$vehicle_type_title = "Bus";
				$img_newsletter = "bus.jpg";

				if($line_tmp == "1"){ $line_name = "Interurbanos";}
				if($line_tmp == "2"){ $line_name = "Midibus";}
				if($line_tmp == "3"){ $line_name = "Plataforma con motor eléctrico";}
				if($line_tmp == "4"){ $line_name = "Urbanos";}
				break;

			case 'truck':
				$vehicle_type_title = "Camión";
				$img_newsletter = "truck.jpg";

				if($line_tmp == "1"){ $line_name = "Livianos";}
				if($line_tmp == "2"){ $line_name = "Medianos";}
				if($line_tmp == "3"){ $line_name = "Pesados Off Road";}
				if($line_tmp == "4"){ $line_name = "Pesados On Road";}
				if($line_tmp == "5"){ $line_name = "Semipesados";}
				break;

			case 'van':
				$vehicle_type_title = "Vans";
				$img_newsletter = "van.jpg";

				if($line_tmp == "1"){ $line_name = "Chasis Cabina";}
				if($line_tmp == "2"){ $line_name = "Combi";}
				if($line_tmp == "3"){ $line_name = "Furgón";}
				if($line_tmp == "4"){ $line_name = "Pasajeros";}
				break;

			case 'pickup':
				$vehicle_type_title = "Pickup";
				$img_newsletter = "pickup.jpg";

				if($line_tmp == "1"){ $line_name = "Furgón";}
				if($line_tmp == "2"){ $line_name = "Pasajeros";}
				break;
		}

		$prospect->linea = $line_name;
		$prospect->tipo_vehiculo = $vehicle_type_title;

		// Comienzo la TRANSACTION
		mysql_query("BEGIN");

		if(!$prospect->save()){
			mysql_query("ROLLBACK");

			$r['status'] = false;
			$r['html'] = '';
			$r['msg'] = 'Fallo de Sincronización.';
			$r['modal'] = '#modal-edit';
			$r['error'] = '';
			$r['class'] = 'danger';
		}else{
			mysql_query("COMMIT");

			$r['status'] = true;
			$r['html'] = '';
			$r['msg'] = 'Sincronización finalizada con éxito.';
			$r['modal'] = '#modal-edit';
			$r['error'] = '';
			$r['class'] = 'success';

		}

		echo json_encode($r);
		exit;

	break;

	case 'edit':
	case 'add':

		$id = isset($_POST['id']) ? $_POST['id'] : '';
		$is_new = $id ? false : true;

		$prospect = new Prospect($id);
		$updated_list_record_tr = '';

		if($is_new){



		}else{

			$_POST['email_sent'] = !isset($_POST['email_sent']) ? 0 : $_POST['email_sent'];

			foreach($_POST as $k => $v){
				switch($k){

					default:
						$prospect->$k = $v;
				}

			}

			$template_records_path = $config['templates_path'] . 'includes/prospect-table-record.html';
			$template_record = Template::get_content( $template_records_path);
			$updated_list_record_tr = Template::replace_list_record($prospect, $template_record);

		}

		if($prospect->save()){
			$r['status'] = true;
			$r['html'] = $updated_list_record_tr;
			$r['record_id'] = 'prospect-' . $prospect->id;
			$r['msg'] = $lang['record_saved'];
			$r['modal'] = '#modal-edit';
			$r['error'] = '';
			$r['class'] = 'success';
		}else{
			$r['status'] = false;
			$r['html'] = '';
			$r['msg'] = $lang['record_not_saved'];
			$r['error'] = '';
			$r['class'] = 'danger';
		}

		echo json_encode($r);
		exit;
	break;

	case 'search':

		unset($_POST['action']);

		$filters = array();
		$template_results_path = $config['templates_path'] . 'includes/prospect-table-results.html';
		$template_record_path = $config['templates_path'] . 'includes/prospect-table-record.html';

		$template_record = Template::get_content( $template_record_path);
		$template_results = Template::get_content( $template_results_path);
		$html = '';

		foreach($_POST as $k => $v){
			if(!empty($v)){

				switch($k){
					default:
						$value = ' ' . $k . ' LIKE "%' . $v . '%" ';
						array_push($filters, $value);
				}

			}
		}

		$prospects = Prospect::all(implode(' AND ', $filters));
		$prospects_length = count($prospects);

		if($prospects_length < 1){
			$r['status'] = true;
			$r['html'] = '<p class="text-center">' . $lang['no_results'] . '</p.>';
			$r['error'] = '';
			$r['class'] = '';

			echo json_encode($r);
			exit;
		}

		$updated_list_record_tr = Template::replace_list_record($prospects, $template_record);

		$template_results = Functions::swap_var('search_results', $updated_list_record_tr, $template_results);
		$template_results = Functions::swap_var('records_found', $prospects_length, $template_results);

		$r['status'] = true;
		$r['html'] = swap_all_vars(swap_lang_vars($template_results));
		$r['error'] = '';
		$r['class'] = '';

		echo json_encode($r);
		exit;

	break;


	case 'signup':

		$code = String::randomText(30);
		//$user = User::by_username($p['email']);

		//if($user->id != ''){
			//$r['status'] = false;
			//$r['title'] = 'Error';
			//$r['msg'] = 'El usuario ' . $p['email'] . ' ya existe en nuestra base de datos.';
			//$r['error'] = '';

			//echo json_encode($r);
			//exit;
		//}

		if(!Email::isValidEmailAddress($p['email'])){
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'El email ingresado no es un email válido.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		$user = User::byEmail($p['email']);

		if($user->id != ''){
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'El email ' . $p['email'] . ' pertenece a un usuario registrado<br>Por favor elige otro email.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		$user = new User();
		$user->firstname = String::Camelize($p['firstname']);
		$user->lastname = String::Camelize($p['lastname']);
		$user->fullname = String::Camelize($p['firstname'] . ' ' . $p['lastname']);
		$user->email = $p['email'];
		$user->username = $p['email'];
		$user->password = $p['password'];
		$user->status = 0;
		$user->city = String::Camelize($p['city']);
		$user->state = $p['state'];
		$user->occupation = String::Camelize($p['occupation']);
		$user->organization = String::Camelize($p['organization']);
		$user->caller = $p['caller'];
		$user->signup_date = $datetime;
		$user->auth_code = $code;

		$_POST['fullname'] = $user->fullname;
		$_POST['authcode'] = $code;
		$_POST['site_url'] = $global_vars['site_url'];

		if($user->save()){

			$email_templates_path = $global_vars['site_path'] . '/' . $global_vars['templates_path'] . 'emails';
			$email_template_filename = $email_templates_path . '/' . 'email-signup.html';

			$e = new EmailSimple($user->email, 'Programa Siria: Verificación de usuario');
			$e->loadTemplate($email_template_filename);

			if($e->send()){
				$r['status'] = true;
				$r['title'] = 'Atención';
				$r['msg'] = 'El usuario ha sido creado.<br>Se ha enviado un email de activación a su cuenta de email.<br>Asegúrese de revisar en la carpeta spam';
				$r['error'] = '';
				$r['class'] = 'success';
			}else{
				$r['status'] = false;
				$r['title'] = 'Error';
				$r['msg'] = $e->result();
				$r['error'] = '';
				$r['class'] = 'danger';
			}

		}else{

			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'No se ha podido crear un usuario.<br>Vuelva a intentarlo en unos minutos';
			$r['error'] = '';
			$r['class'] = 'danger';
		}

		echo json_encode($r);
		exit;

	break;

	case 'logout':

		Session::end();

		$r['status'] = true;
		$r['url'] = $global_vars['site_url'] . '/';
		$r['error'] = '';
		$r['class'] = 'success';

		echo json_encode($r);
		exit;

		//$module = explode('-', $segment_1);
//
		//Log::l('actions.php Logout $module', $module, false);
//
		//if($module[0] == 'modulo'){
		//	$module_number = str_replace('0', '', $module[1]);
		//}
//
		//Log::l('actions.php Logout $module_number', $module_number, false);
		//Log::l('actions.php Logout $user', $user, true);
		//Log::l('actions.php Logout $user->id', $user->id, false);
//
		//if($user->id){
		//	$user->module = ($user->module >= $module_number ? $user->module : $module_number);
		//	$user->module_date = $datetime;
//
		//	Log::l('actions.php Logout $module_number', $user, false);
		//	Log::l('actions.php Logout $datetime', $datetime, false);
//
		//	if($user->save()){
//
		//		Session::end();
//
		//		$r['status'] = true;
		//		$r['url'] = $global_vars['site_url'] . '/';
		//		$r['error'] = '';
//
		//	}else{
		//		$r['status'] = false;
		//		$r['msg'] = 'Ha ocurrido una problema.<br>No se ha podido guardar el estado del curso.';
		//		$r['error'] = '';
		//	}
		//}else{
		//	$r['status'] = false;
		//	$r['msg'] = 'La sesión expiró.<br>No se ha podido guardar el estado del curso.';
		//	$r['error'] = '';
		//}
//
		//echo json_encode($r);
		//exit;

	break;

	case 'next':

		$current_module = $user->module;
		$next_module = $p['next'];

		//$module_difference = $next_module - $current_module;

		Log::l('actions.php next', 'Current module: ' . $current_module . ', Next module:  ' . $next_module, true);

		Log::l('actions.php next', '/modulo-0' . $next_module . '/', true);

		if($next_module <= $current_module + 1){
			$user->module = $next_module;
			$user->module_date = $datetime;

			if($user->save()){
				$r['status'] = true;
				$r['url'] = $global_vars['site_url'] . ($next_module <= 4 ? '/modulo-0' : '/') . $next_module . '/';
				$r['error'] = '';
				$r['class'] = 'success';
			}else{
				$r['status'] = false;
				$r['error'] = '';
				$r['class'] = 'danger';
			}

			echo json_encode($r);
			exit;
		}


	break;

	case 'forgot':
		$user = User::byEmail($p['email']);

		if(!$user->id){
				$r['status'] = false;
				$r['msg'] = 'No existe un usuario con el email ingresado.';
				$r['error'] = '';
				$r['class'] = 'danger';
			}else{

				$email_templates_path = $global_vars['site_path'] . '/' . $global_vars['templates_path'] . 'emails';
				$email_template_filename = $email_templates_path . '/' . 'email-forgot.html';

				$e = new EmailSimple($user->email, 'Información de cuenta');
				$e->loadTemplate($email_template_filename);

				if($e->send()){
					$r['status'] = true;
					$r['msg'] = 'El email ha sido enviado. Recuerde buscar en la carpeta spam';
					$r['error'] = '';
					$r['class'] = 'success';
				}else{
					$r['status'] = false;
					$r['msg'] = $e->result();
					$r['error'] = '';
					$r['class'] = 'danger';
				}


			}

			echo json_encode($r);
			exit;
	break;

}
