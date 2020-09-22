<?php

include("config.php");

Log::loguear('frontend_actions', $_POST, false);

$action = (isset($_POST['action']) && $_POST['action'] != '') ? addslashes($_POST['action']) : (isset($_GET['action']) && $_GET['action'] != '' ? $_GET['action'] : '') ;

Log::loguear('frontend actions no action',Session::check_user_session(), false);

/*if(!Session::check_user_session()){
	 header('HTTP/1.0 401 Unauthorized');
}*/

$_POST = Functions::array_sanitize($_POST);
$datetime = date('Y-m-d H:i:s');
$hour = date('H');

if(isset($_POST['imei']) && $_POST['imei'] != ''){
	$user = User::byImei($_POST['imei']);
}

switch($action){

	case 'time':
		//echo date('H:i:s');
	break;

	case 'start':
		$registered_users = User::all('WHERE answers_done = 1 AND is_admin = 0');
		$total_registered_users = count($registered_users);
		// 210

		$r['status'] = true;
		$r['total'] = $total_registered_users;
		$r['error'] = '';
		$r['h'] = $datetime . ' ' . $_SERVER['SERVER_NAME'];
		$r['load'] = '';

		if($total_registered_users == 210){
			echo json_encode($r);
			exit;
		}

		if(($hour > 11 && $hour < 17) || $_SERVER['SERVER_NAME'] == 'lumiabts.nmd'  || $_SERVER['SERVER_NAME'] == 'web.promocionesnokia.com'){
			$r['load'] = 'promo';
		}

		echo json_encode($r);
	break;

	case 'signin':

		if($user->imei != '' && $user->answers_done){
			$template = 'repeated';
		}else{
			$template = 'legals';
		}

		if($user->imei == ''){
			$user->name = $_POST['name'];
			$user->email = $_POST['email'];
			$user->model = String::camelize($_POST['model']);
			$user->imei = $_POST['imei'];
			$user->number = $_POST['number'];
			$user->street = String::camelize($_POST['street']);
			$user->noext = $_POST['noext'];
			$user->noint = $_POST['noint'];
			$user->neighborhood = String::camelize($_POST['neighborhood']);
			$user->state = String::Camelize($_POST['state']);
			$user->town = String::camelize($_POST['town']);
			$user->code = $_POST['code'];
			$user->city = String::camelize($_POST['city']);
			$user->signin_date = $datetime;
		}

		if($user->save()){
			$r['status'] = true;
			$r['error'] = '';
			$r['load'] = $template;
			$r['imei'] = $_POST['imei'];
		}else{
			$r['status'] = false;
			$r['title'] = 'Atención';
			$r['msg'] = 'Ha ocurrido un problema en el servidor.<br>Intenta nuevamente';
			$r['error'] = '';
			$r['load'] = 'error';
		}

		echo json_encode($r);
	break;

	case 'legals':

		$bases = (isset($_POST['bases']) && $_POST['bases'] != '' && $_POST['bases'] != 0) ? $_POST['bases'] : false;
		$privacy = (isset($_POST['privacy']) && $_POST['privacy'] != '' && $_POST['privacy'] != 0) ? $_POST['privacy'] : false;

		if(!$bases || !$privacy){
			$r['status'] = false;
			$r['title'] = 'Atención';
			$r['msg'] = 'Debes aceptar las Bases y Condiciones y el Aviso de Privacidad para poder continuar';
			$r['error'] = '';

			echo json_encode($r);
			exit;
		}

		if($user->imei != ''){
			$user->accept_leglas = 1;
			$user->legals_date = $datetime;
			$user->save();

			$r['status'] = true;
			$r['error'] = '';
			$r['load'] = 'pregunta-1';
			$r['imei'] = $_POST['imei'];
		}else{
			$r['status'] = true;
			$r['error'] = '';
			$r['load'] = 'promo';
		}

		echo json_encode($r);
	break;

	case 'question-1':
		if(!isset($_POST['question-1'])){
			$r['status'] = false;
			$r['title'] = 'Atención';
			$r['msg'] = 'Debes seleccionar una opción: ' . $_POST['question-1'];
			$r['error'] = '';

			echo json_encode($r);
			exit;
		}
		$r['status'] = true;
		$r['answers'] = $_POST['question-1'] . ',';
		$r['error'] = '';
		$r['load'] = 'pregunta-2';

		echo json_encode($r);
	break;

	case 'question-2':
	case 'timeout':

		if($action == 'question-2'){

			if(!isset($_POST['question-2'])){
				$r['status'] = false;
				$r['title'] = 'Atención';
				$r['msg'] = 'Debes seleccionar una opción dale';
				$r['error'] = '';

				echo json_encode($r);
				exit;
			}

			 $template = 'failed';
			$r['answers'] = $_POST['answers'] . $_POST['question-2'] . ',';

			$aAnswers = explode(',', rtrim($r['answers'], ','));

			if(in_array('felix', $aAnswers) && in_array('NaCl', $aAnswers)){
				$template = 'status';
				$user->answers_result = 1;
			}
		}

		$user->answers_done = 1;
		$user->answers_date = $datetime;
		$user->save();

		if($action == 'timeout'){
			echo getContent('timeout');
		}else{
			$r['status'] = true;
			$r['error'] = '';
			$r['load'] = $template;

			echo json_encode($r);
		}
	break;

	case 'status':
		$r['status'] = true;
		$r['error'] = '';
		$r['load'] = 'mailing';

		echo json_encode($r);
	break;

	case 'mailing':

		$f1 = (isset($_POST['feature-1']) && $_POST['feature-1'] != '' && $_POST['feature-1'] != 0) ? $_POST['feature-1'] : false;
		$f2 = (isset($_POST['feature-2']) && $_POST['feature-2'] != '' && $_POST['feature-2'] != 0) ? $_POST['feature-2'] : false;
		$f3 = (isset($_POST['feature-3']) && $_POST['feature-3'] != '' && $_POST['feature-3'] != 0) ? $_POST['feature-3'] : false;
		$f4 = (isset($_POST['feature-4']) && $_POST['feature-4'] != '' && $_POST['feature-4'] != 0) ? $_POST['feature-4'] : false;

		$aFeatures = array(1=>$f1, 2=>$f2, 3=>$f3, 4=>$f4);
		$aOptions = array(1=>'Tiene el mejor diseño',
							2=>'Toma fotos increíbles',
							3=>'Está sincronizado con mis demás dispositivos',
							4=>'Es moderno y diferente');
		$aSelected = array();

		$_POST['name'] = $user->name;
		$_POST['email'] = $user->email;
		$_POST['model'] = $user->model;
		$_POST['address'] = $user->street . ' ' . $user->noext . ' ' . $user->noint;
		$_POST['address2'] = $user->neighborhood . ' ' . $user->town;
		$_POST['address3'] = '(' . $user->code . ') ' . $user->city;
		$_POST['model'] = $user->model;


		for($i = 1; $i < count($aOptions) + 1; $i++){
			if($aFeatures[$i]){
				array_push($aSelected, $aOptions[$i]);
			}
		}

		if(count($aSelected) != 2){
			$r['status'] = false;
			$r['title'] = 'Atención';
			$r['msg'] = 'Por favor, selecciona ' . (count($aSelected) < 2 ? '' : 'sólo') . ' 2 características de tu nuevo Lumia';
			$r['error'] = '';

			echo json_encode($r);
			exit;
		}

		$_POST['features'] = implode(' y ', $aSelected);
		Log::loguear('mailing', $aSelected, false);
		Log::loguear('mailing', $_POST, false);

		$user->features = $_POST['features'];
		$user->save();

		$e = new EmailContact(true);

		$_POST['friend_email'] = strtolower($_POST['friend_email']);

		if(!$e->validateEmailAddress($_POST['friend_email'])){
			$r['status'] = false;
			$r['title'] = 'Error';
			$r['msg'] = 'Por favor, introduce un email válido';
			$r['error'] = '';

			echo json_encode($r);
			exit;
		}
		$e->sendEmail();

		if($e->error == ''){
			$user->email_sent = 1;
			$user->email_sent_date = $datetime;
			$user->save();

			$e = new EmailUser(true);
			$e->sendEmail();

			$r['status'] = true;
			$r['title'] = 'Atención';
			$r['msg'] = 'El email ha sido enviado';
			$r['error'] = '';
			$r['load'] = 'ending';
		}else{
			$r['status'] = false;
			$r['title'] = 'Error';
			//$r['msg'] = 'Ha ocurrido un error y no hemos podido enviar el email.<br>Por favor intenta nuevamente';
			$r['msg'] = $e->error . ' | EmailUser';
			$r['error'] = '';
		}
		Log::loguear('mailing', $r, false);
		echo json_encode($r);

	break;

	case 'get_page':
		echo getContent($_POST['page']);
		/*$filename = $config['templates_path'] . 'section/' . $_POST['page'] . '.php';
		$html = file_get_contents('../assets/templates/section/' . $_POST['page'] . '.php');
		Log::loguear('frontend actions get page', $filename);
		echo $html;*/
	break;

}

function getContent($page){
	$filename = $config['templates_path'] . 'section/' . $page . '.php';
	$html = file_get_contents('../assets/templates/section/' . $page . '.php');
	Log::loguear('frontend actions get page', $filename);
	return $html;
}

unset($_POST);
