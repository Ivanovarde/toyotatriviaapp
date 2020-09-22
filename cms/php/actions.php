<?PHP

//header('Access-Control-Allow-Origin: *'); //for all

include('config.php');

/*************************************
 * LOCALIZATION START
 /************************************/
if(!file_exists(Settings::get_globals('localization_filename_path'))){
	Settings::set_globals('localization_filename_path', Settings::get_globals('site_path') . '/' . Settings::get_config('localization_path') .  Settings::get_globals('localization_filename_default'));

	if(Settings::get_config('debug')){
		echo 'Renderizer: Loading Localization lang file - File: ' . Settings::get_globals('localization_filename_path') . ' <strong>NOT found</strong><br>';
	}
}

if(file_exists(Settings::get_globals('localization_filename_path'))){

	if(Settings::get_config('debug')){
		echo 'Renderizer: Loading Localization lang file <strong>FOUND</strong> - File: ' . Settings::get_globals('localization_filename_path') . ' <br>';
	}
	include(Settings::get_globals('localization_filename_path'));
}
/*************************************
 * LOCALIZATION END
 /************************************/

$_GET = Functions::array_sanitize($_GET);
$_POST = Functions::array_sanitize($_POST);

foreach($_POST as $k => $v){
	$_POST[$k] = strip_tags($v);
	${$k} = isset($_POST[$k]) ? $_POST[$k] : $_GET[$k];
}

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';
$action = (($action == '') && (isset($_POST['action']) && $_POST['action'] != '')) ? $_POST['action'] : $action;
$action = strtolower($action);

Log::l('actions.php var $action', $action, false);


//$datetime = date('Y-m-d H:i:s');
//$hour = date('H');
//$relative_path = Server::getRelativeRootPath();


$allowed_no_session_actions = array('signin', 'signup', 'forgot', 'edit', 'add', 'store');

// Si no hay sesion corto el flujo
if(!Session::check_user_session() && !in_array($action, $allowed_no_session_actions)){
	$r['status'] = false;
	$r['url'] = Settings::get_globals('site_url') . '/';
	$r['msg'] = 'La sesión expiró';
	$r['expired'] = true;
	$r['error'] = '';

	echo json_encode($r);
	exit;
}


// Si hay sesion traigo los datos del usuario en sesion
if(Session::check_user_session()){
	$user = new Member($_SESSION['u']->member_id);
	Log::l('actions.php $_SESSION["u"]', $_SESSION['u'], false);
}

//Log::l('actions Session::check_user_session()', Session::check_user_session(), false);


switch($action){

	case 'signin':
		$u = Member::by_username($_POST['username']);

		//Log::l('actions.php signin', $u, false);

		if($u->member_id == ''){
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'El usuario ' . $_POST['username'] . ' no existe.<br>Por favor chequea los datos.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		//var_dump($u->password);
		//var_dump(md5($_POST['password']));
		//var_dump(sha1(md5($_POST['password'])));
		//var_dump(sha1($_POST['password'], $u->salt));
		//var_dump($u->salt);

		//if($u->password != md5($_POST['password'])) {
		//	$r['status'] = false;
		//	$r['title'] = 'Error';
		//	$r['msg'] = 'La contraseña ingresada no es correcta.<br>Por favor chequea los datos.';
		//	$r['error'] = '';
		//	$r['class'] = 'danger';
//
		//	echo json_encode($r);
		//	exit;
		//}
//
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
			$r['url'] = sep(Settings::get_config('folder'), 'l') . '/members/';
			$r['error'] = '';
			$r['class'] = 'success';
		}else{

			Session::end();

			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'Ha ocurrido un error, prueba nuevamente en unos minutos.';
			$r['error'] = '';
			$r['class'] = 'danger';
			$r['url'] = Settings::get_globals('site_url') . '/';
			$r['expired'] = true;
		}

		echo json_encode($r);
		exit;

	break;

	case 'logout':

		Session::end();

		Settings::set_globals('username', '');
		Settings::set_globals('no_session_class', 'session-hidden');

		$r['status'] = true;
		$r['url'] = Settings::get_globals('site_url') . '/';
		$r['error'] = '';
		$r['class'] = 'success';

		echo json_encode($r);
		exit;

	break;

	case 'search':

		$element_type = $_POST['element_type'];
		$filters = array();
		$template_results_path = Settings::get_config('templates_path') . 'includes/' . $element_type . '-table-results.html';
		$template_record_path = Settings::get_config('templates_path') . 'includes/' . $element_type . '-table-record.html';

		$template_record = Template::get_content( $template_record_path);
		$template_results = Template::get_content( $template_results_path);
		$html = '';


		// Remove var from global var POST for iteration
		unset($_POST['action'], $_POST['element_type']);

		$record = ucfirst($element_type);
		$record = new $record();

		foreach($_POST as $k => $v){
			if(!empty($v)){

				if(isset($record->member_fields_relationships[$k])){

					$data_field_id = 'md.' . $record->member_fields_relationships[$k];

				}else{

					$data_field_id = 'm.' . $k;

				}

				switch($k){
					case 'envio':
						$value = ' ' . $data_field_id . ' = "' . ($v == 'SI' ? 1 : 0) . '" ';
					break;

					default:
						$value = " " . $data_field_id . " LIKE \"%%" . $v . "%\" ";
				}

				array_push($filters, $value);

			}
		}

		array_push($filters, ' group_id = 7 ');

		$filters_string = implode(' AND ', $filters);
		$filters_string .= ' ORDER BY m.join_date DESC';

		Log::l('actions.php search $filters_string', $filters_string, false);

		//var_dump($filters_string);

		$records = $record::all($filters_string);
		$records_length = count($records);

		Log::l('actions.php search', $records, false);

		if($records_length < 1){
			$r['status'] = true;
			$r['html'] = '<p class="text-center">' . $lang['text_no_results'] . '</p.>';
			$r['records_found'] = 0;
			$r['error'] = '';
			$r['class'] = '';

			echo json_encode($r);
			exit;
		}

		$updated_list_record_tr = Template::swap_member_values($records, $template_record);

		$template_results = Template::swap_var('search_results', $updated_list_record_tr, $template_results);
		$template_results = Template::swap_var('records_found', $records_length, $template_results);
		$template_results = Template::swap_lang_vars($template_results);
		$template_results = Template::swap_system_vars($template_results);

		$r['status'] = true;
		$r['html'] = $template_results;
		$r['records_found'] = $records_length;
		$r['error'] = '';
		$r['class'] = '';

		echo json_encode($r);
		exit;

	break;

	case 'send_email':

		$a_id = explode(',', $_POST['aId']);
		$a_sent_id = array();
		$a_sent_addresses = array();
		$a_not_sent_id = array();
		$img_newsletter = '';

		if(count($a_id) > 0){

			$element_type = $_POST['element_type'];
			$record = ucfirst($element_type);

			$email_templates_path = Settings::get_globals('site_path') . '/' . Settings::get_config('templates_path', 'send_email 2') . 'emails';
			$email_template_filename = $email_templates_path . '/' . $_POST['template'] . '.html';

			foreach($a_id as $id){

				$record = new $record($id);

				$target_email = isset($record->email) ? strtolower($record->email) : '';

				$email_subject = $record->member_data->{$record->member_fields_relationships['tipo_vehiculo']} . " Mercedes-Benz: Envío de tu cotización";

				$record_firstname = $record->member_data->{$record->member_fields_relationships['nombre']};
				$record_lastname = $record->member_data->{$record->member_fields_relationships['apellido']};
				$record_fullname = $record_firstname . ' ' . $record_lastname;

				Log::l('actions.php', $email_subject, false);

				$e = new EmailSimple($target_email, $email_subject);
				$e->debug = false;
				$e->use_phpmailer = false;
				$e->set_to_name($record_fullname);
				//$e->set_email_subject($email_subject);
				$e->load_email_template($email_template_filename);
				$e->add_reply_address(Settings::get_config('company_email'), Settings::get_config('site_name'));
				$e->allow_bcc = true;
				$e->add_bcc_address('iv@neomedia.com.ar', 'Ivano');

				$e->email_template_html = Template::swap_member_values($record, $e->get_email_template());

				Log::l('actions.php $e->email_template_html', $e->email_template_html, false);

				//var_dump($e);
				//exit;

				if($e->send()){
				//if(1 == 1){

					$record->member_data->{$record->member_fields_relationships['envio']} = '1';
					$record->member_data->{$record->member_fields_relationships['envio_fecha']} = time();

					Log::l('actions.php send_email', $record, false);

					$record->save();

					array_push($a_sent_id , $record->member_id);
					array_push($a_sent_addresses , '[' . $target_email . '] ' . $record_fullname);

				}else{

					$record->member_data->{$record->member_fields_relationships['envio']} = '0';

					array_push($a_not_sent_id, array($record->member_id, $target_email, $e->get_status() ) );
				}

			}

			if(count($a_sent_id) > 0){

				$msg = $lang['email_records_sent'];
				$result_class = 'success';

				if(count($a_not_sent_id) > 0){
					$msg =  $lang['email_some_records_not_sent'];
					$result_class = 'warning';
				}

				$r['status'] = true;
				$r['title'] = $lang['text_success'];
				$r['msg'] = $msg;
				$r['ids'] = $a_sent_id;
				$r['sent'] = $a_sent_addresses;
				$r['ids_not_sent'] = $a_not_sent_id;
				$r['error'] = '';
				$r['class'] = $result_class;

			}else{
				$r['status'] = false;
				$r['title'] = $lang['text_error'];
				$r['msg'] = $e->get_status();
				$r['ids'] = $a_not_sent_id;
				$r['error'] = '';
				$r['class'] = 'danger';
			}
		}else{

			$r['status'] = false;
			$r['title'] = $lang['text_error'];
			$r['html'] = '';
			$r['msg'] = $lang['email_records_not_received'] ;
			$r['ids'] = $a_sent_id;
			$r['error'] = '';
			$r['class'] = 'danger';

		}

		echo json_encode($r);
		exit;

	break;


//case 'edit_form':
/*
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

		if(!isset($_POST['element_type'])){
			$r['status'] = false;
			$r['title'] = $lang['text_error'];
			$r['html'] = '';
			$r['msg'] = $lang['edit_form_no_element_type'];
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		$id = $_POST['id'];
		$element_type = $_POST['element_type'];
		$template_edit_form_path = Settings::get_config('templates_path') . 'includes/' . $element_type . '-form.html';
		$template_edit_form = getContent( $template_edit_form_path, 'actions.php (' . $action . ')');

		$record = ucfirst($element_type);
		$record = new $record($id);

		if($record->id){

			include('actions/' . $element_type . '_' . $action . '.php');

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
*/


//case 'store':
/*
	case 'store':

		$chars_to_clean = array("\\", "[", "]","\"");
		$record_data = str_replace($chars_to_clean, "", $_GET['storedRecords']);
		$a_record_data = explode(",", $record_data) ;

		$element_type = $_POST['element_type'];
		$record = ucfirst($element_type);
		$record = new $record();

		include('actions/' . $element_type . '_' . $action . '.php');

		// Comienzo la TRANSACTION
		mysql_query("BEGIN");

		if(!$record->save()){
			mysql_query("ROLLBACK");

			$r['status'] = false;
			$r['html'] = '';
			$r['msg'] = $lang=['text_sinc_fail'];
			$r['modal'] = '#modal-edit';
			$r['error'] = '';
			$r['class'] = 'danger';
		}else{
			mysql_query("COMMIT");

			$r['status'] = true;
			$r['html'] = '';
			$r['msg'] = $lang=['text_sinc_success'];
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
		$element_type = $_POST['element_type'];
		$record = ucfirst($element_type);

		$record = new $record($id);
		$updated_list_record_tr = '';

		if($is_new){


		}else{

			$_POST['email_sent'] = !isset($_POST['email_sent']) ? 0 : $_POST['email_sent'];

			foreach($_POST as $k => $v){
				switch($k){

					default:
						$record->$k = $v;
				}

			}

			$template_records_path = Settings::get_config('templates_path') . 'includes/member-table-record.html';
			$template_record = Template::get_content( $template_records_path);
			$updated_list_record_tr = Template::replace_list_record($record, $template_record);
		}

		if($record->save()){
			$r['status'] = true;
			$r['html'] = $updated_list_record_tr;
			$r['record_id'] = 'record-' . $record->id;
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
*/


//case 'delete':
/*
	case 'delete':

		$a_id = explode(',', $_POST['aId']);
		$a_deleted_id = array();
		$a_not_deleted_id = array();

		if(count($a_id) > 0){

			$element_type = $_POST['element_type'];
			$record = ucfirst($element_type);

			foreach($a_id as $id){

				$record = new $record($id);
				if($record->delete()){
					array_push($a_deleted_id, $record->id);
				}else{
					array_push($a_not_deleted, $record->id);
				}

			}

			if(count($a_deleted_id) > 0){

				$msg = $lang['delete_records_deleted'];
				$result_class = 'success';

				if(count($a_not_deleted_id) > 0){
					$msg = $lang['delete_some_records_not_deleted'];
					$result_class = 'warning';
				}

				$r['status'] = true;
				$r['title'] = $lang['text_warning'];
				$r['html'] = '';
				$r['msg'] = $msg;
				$r['ids'] = $a_deleted_id;
				$r['ids_not_deleted'] = $a_not_deleted;
				$r['error'] = '';
				$r['class'] = $result_class;

			}else{

				$r['status'] = false;
				$r['title'] = $lang['text_error'];
				$r['html'] = '';
				$r['msg'] = $lang['delete_records_not_deleted'];
				$r['ids'] = '';
				$r['error'] = '';
				$r['class'] = 'danger';

			}

		}else{

			$r['status'] = false;
			$r['title'] = $lang['text_error'];
			$r['html'] = '';
			$r['msg'] = $lang['delete_records_not_received'] ;
			$r['ids'] = $a_deleted_id;
			$r['error'] = '';
			$r['class'] = 'danger';

		}

		echo json_encode($r);
		exit;

	break;
*/


//case 'export':
	case 'export':

		$element_type = $_GET['element_type'];
		$filters = array();
		$sql = "SELECT
				m.member_id AS id,
				md.m_field_id_1 AS 'Nombre',
				md.m_field_id_2 AS 'Apellido',
				m.email AS 'Email',
				md.m_field_id_3 AS 'Telefono',
				md.m_field_id_5 AS 'Ciudad',
				md.m_field_id_19 AS 'Codigo Postal',
				md.m_field_id_4 AS 'Provincia',
				md.m_field_id_9 AS 'Categoria',
				md.m_field_id_10 AS 'Linea',
				md.m_field_id_6 AS 'Modelo',
				md.m_field_id_7 AS 'Plan',
				md.m_field_id_8 AS 'Cuotas',
				md.m_field_id_11 AS 'Precio Publico',
				md.m_field_id_12 AS 'Cuota Pura',
				md.m_field_id_13 AS 'Carga Amin Susc',
				md.m_field_id_14 AS 'IVA 21',
				md.m_field_id_15 AS 'Cuota Mensual',
				md.m_field_id_16 AS 'Pago Adj 30',
				DATE_FORMAT(FROM_UNIXTIME(m.join_date), '%e-%m-%Y') AS 'Reg Fecha',
				(CASE md.m_field_id_20 WHEN 1 THEN 'SI' ELSE 'NO' END) AS 'Envio',
				DATE_FORMAT(FROM_UNIXTIME(md.m_field_id_21), '%e-%m-%Y') AS 'Envio Fecha'
				FROM exp_members m
				LEFT JOIN exp_member_data md ON md.member_id = m.member_id
				WHERE
				{filters}
				ORDER BY join_date DESC;";

		$record = ucfirst($element_type);
		$record = new $record();

		//Remove action and element_type for making the $_GET iteration
		unset($_GET['action'], $_GET['element_type']);

		//Make filters from GET values
		foreach($_GET as $k => $v){

			if(!empty($v)){

				if(isset($record->member_fields_relationships[$k])){

					$data_field_id = 'md.' . $record->member_fields_relationships[$k];

				}else{

					$data_field_id = 'm.' . $k;

				}

				switch($k){
					case 'envio':
						$value = ' ' . $data_field_id . ' = "' . ($v == 'SI' ? 1 : 0) . '" ';
					break;

					default:
						$value = " " . $data_field_id . " LIKE \"%%" . $v . "%\" ";
				}

				array_push($filters, $value);

			}
		}

		array_push($filters, ' group_id = 7 ');

		$filters_string = implode(' AND ', $filters);
		//$filters_string .= ' ORDER BY m.join_date DESC';

		$sql_statement = str_replace('{filters}', ($filters_string ? $filters_string : ''), $sql);

		$db = new DB();
		$db->set_query($sql_statement);
		$records = $db->execute();
		$records_length = count($records);

		//var_dump($_GET);
		//echo '<br>===============<br>';
		//echo '<br>Filters: ' . $filters_string;
		//echo '<br>===============<br>';
		//echo '<br>SQL: ' . $sql_statement . '<br>';
		//echo '<br><br>records length: ' . $records_length;
		//echo '<br>===============<br>';
		//echo '<br>Recordset<br>';
		//var_dump( $records );


		$download_filename = strtolower(Functions::normalize(Functions::safe_url(Settings::get_config('site_name'))));

		$sep = "\n";
		$output = '';
		$tableo = $sep . '<table>' . $sep;
		$tablec = $sep . '</table>' . $sep;
		$theado = $sep . '<thead>' . $sep;
		$theadc = $sep . '</thead>' . $sep;
		$tbodyo = $sep . '<tbody>' . $sep;
		$tbodyc = $sep . '</tbody>' . $sep;
		$tro = $sep . '<tr>' . $sep;
		$trc = $sep . '</tr>' . $sep;
		$tho = $sep . '<th>' . $sep;
		$thc = $sep . '</th>' . $sep;
		$tdo = $sep . '<td>' . $sep;
		$tdc = $sep . '</td>' . $sep;

		//All records from the table
		//$record = ucfirst($element_type);
		//$records = $record::all();
		//$records_length = count($records);

		// No results - Exit
		if($records_length < 1){
			$r['status'] = true;
			$r['html'] = '<p class="text-center">' . $lang['text_no_results'] . '</p.>';
			$r['error'] = '';
			$r['class'] = '';

			echo json_encode($r);
			exit;
		}


		$output = $tableo . $theado . $tro;

		Log::l('actions.php export $record', $records[0], false);

		// Table headers
		foreach($records[0] as $field => $value){
			$output .= $tho;
			$output .= $sep . $field . $sep;
			$output .= $thc;
		}

		$output .= $trc . $theadc . $tbodyo;

		Log::l('actions.php export $record', $record, false);
		Log::l('actions.php export $output', $output, false);

		// Table data
		foreach($records as $row){

			$output .= $tro;

			foreach($row as $field => $value){
				$output .= $tdo;

				$output .= $sep . $value . $sep;

				$output .= $tdc;
			}

			$output .= $trc;

		}

		$output .= $tbodyc . $tablec;

		header("Content-Type: application/xls");
		header("Content-Disposition: attachment; filename=" . $download_filename . '-' . date('YmdHis') . '.xls');

		echo $output;

		exit;
	break;


//case 'signup':
/*
	case 'signup':

		$code = String::randomText(30);
		//$user = User::by_username($_POST['email']);

		//if($user->id != ''){
			//$r['status'] = false;
			//$r['title'] = 'Error';
			//$r['msg'] = 'El usuario ' . $_POST['email'] . ' ya existe en nuestra base de datos.';
			//$r['error'] = '';

			//echo json_encode($r);
			//exit;
		//}

		if(!Email::is_valid_email_address($_POST['email'])){
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'El email ingresado no es un email válido.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		$user = User::byEmail($_POST['email']);

		if($user->id != ''){
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'El email ' . $_POST['email'] . ' pertenece a un usuario registrado<br>Por favor elige otro email.';
			$r['error'] = '';
			$r['class'] = 'danger';

			echo json_encode($r);
			exit;
		}

		$user = new User();
		$user->firstname = String::Camelize($_POST['firstname']);
		$user->lastname = String::Camelize($_POST['lastname']);
		$user->fullname = String::Camelize($_POST['firstname'] . ' ' . $_POST['lastname']);
		$user->email = $_POST['email'];
		$user->username = $_POST['email'];
		$user->password = $_POST['password'];
		$user->status = 0;
		$user->city = String::Camelize($_POST['city']);
		$user->state = $_POST['state'];
		$user->occupation = String::Camelize($_POST['occupation']);
		$user->organization = String::Camelize($_POST['organization']);
		$user->caller = $_POST['caller'];
		$user->signup_date = $datetime;
		$user->auth_code = $code;

		$_POST['fullname'] = $user->fullname;
		$_POST['authcode'] = $code;
		$_POST['site_url'] = Settings::get_globals('site_url');

		if($user->save()){

			$email_templates_path = Settings::get_globals('site_path') . '/' . Settings::get_globals('templates_path') . 'emails';
			$email_template_filename = $email_templates_path . '/' . 'email-signup.html';

			$e = new EmailSimple($user->email, 'Programa Siria: Verificación de usuario');
			$e->load_email_template($email_template_filename);

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
*/

//case 'next':
/*
	case 'next':

		$current_module = $user->module;
		$next_module = $_POST['next'];

		//$module_difference = $next_module - $current_module;

		//Log::l('actions.php next', 'Current module: ' . $current_module . ', Next module:  ' . $next_module, true);

		//Log::l('actions.php next', '/modulo-0' . $next_module . '/', true);

		if($next_module <= $current_module + 1){
			$user->module = $next_module;
			$user->module_date = $datetime;

			if($user->save()){
				$r['status'] = true;
				$r['url'] = Settings::get_globals('site_url') . ($next_module <= 4 ? '/modulo-0' : '/') . $next_module . '/';
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
*/

//case 'forgot':
/*
	case 'forgot':
		$user = User::byEmail($_POST['email']);

		if(!$user->id){
				$r['status'] = false;
				$r['msg'] = 'No existe un usuario con el email ingresado.';
				$r['error'] = '';
				$r['class'] = 'danger';
			}else{

				$email_templates_path = Settings::get_globals('site_path') . '/' . Settings::get_globals('templates_path') . 'emails';
				$email_template_filename = $email_templates_path . '/' . 'email-forgot.html';

				$e = new EmailSimple($user->email, 'Información de cuenta');
				$e->load_email_template($email_template_filename);

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
*/

}
