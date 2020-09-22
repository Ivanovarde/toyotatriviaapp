<?php
// []/php/actions.php
// Ivano 06/2019

header('Access-Control-Allow-Origin: *'); //for all

include('actions_config.php');


$_GET = Functions::array_sanitize($_GET);
$_POST = Functions::array_sanitize($_POST);
$datetime = date('Y-m-d H:i:s');
$hour = date('H');
$relative_path = Server::getRelativeRootPath();

//Log::l('actions.php $_POST', $_POST, false);
//Log::l('actions.php $_GET', $_GET, false);

foreach($_POST as $k => $v){
	if(isset($_POST[$k])){
		$_POST[$k] = Functions::arrayStriptags($v);
		//${$k} = $_POST[$k];
	}
}

foreach($_GET as $k => $v){
	if(isset($_GET[$k])){
		$_GET[$k] = Functions::arrayStriptags($v);
		//${$k} = $_GET[$k];
	}
}

$p = $_POST;
$g = $_GET;

$action = (isset($_GET['action']) && $_GET['action'] != '') ? $_GET['action'] : '';
$action = (($action == '') && (isset($_POST['action']) && $_POST['action'] != '')) ? $_POST['action'] : $action;
$action = strtolower($action);

$allowed_no_session_actions = array('store');

if(!in_array($action, $allowed_no_session_actions)){
	$r['status'] = false;
	$r['url'] = '';
	$r['msg'] = 'La sesión expiró';
	$r['expired'] = true;
	$r['error'] = '';

	echo json_encode($r);
	exit;
}


switch($action){

	case 'store':

		//$received_record = json_decode($_POST['stored_leads']);
		//$record_data = $received_record[0];

		$total_records = count($_POST['stored_leads']);
		$a_failed_records = array();
		$errors = false;
		$c = 0;


		//Log::l('actions.php $record_data ', $_POST['stored_leads'], false);

		foreach($_POST['stored_leads'] as $lead){
			//Log::l('actions.php $lead[0] ', $lead[0]['nombre'], false);
			//Log::l('actions.php json_decode($lead[0]) ', json_encode($lead[0]), false);


			$record_data = json_decode(json_encode($lead));
			//Log::l('actions.php r->nombre ', $record_data->nombre, false);

			//echo '<br>***********<br>';
			//echo '$lead ***********<br>';
			//var_dump($lead);
			//echo '<br>***********<br><br>';
			//echo '$record_data ***********<br>';
			//var_dump($record_data->firstname);
			//echo '<br>***********<br><br>';


			//$m = new Member();
			//$m = new stdClass();
			$u = new User();

			foreach($lead as $key => $value){

				//echo 'key: ' . $key . ' - value: ' . $value . '<br>';

				switch($key){
					case 'firstname':
					case 'lastname':
					case 'city':

						$u->{$key} = String::camelize($value);

					break;

					default:
						$u->{$key} = $value;
				}

			}

			//echo '<br>***********<br>';
			//echo '$lead ***********<br>';
			//var_dump($u);

			// Comienzo la TRANSACTION
			//mysql_query("BEGIN");

			if(!$u->save()){
			//if(1 == 1){

				//mysql_query("ROLLBACK");

				array_push($a_failed_records, $record_data);
				$errors = true;

				$r['status'] = false;
				$r['html'] = '';
				$r['msg'] = 'Fallo en la Sincronización.<br>Registros exportados correctamente: <strong>{e}</strong><br>Registros remanentes en memoria: <strong>{m}</strong><br>Recuerde volver a exportar en otro momento.';
				$r['error'] = '';
				$r['class'] = 'danger';
				$r['failed_records'] = $a_failed_records;

			}else{

				//Log::l('actions.php', $u, false);
				//mysql_query("COMMIT");

				$r['status'] = true;
				$r['html'] = '';
				$r['msg'] = 'Sincronización finalizada con éxito.';
				//$r['modal'] = '#modal-edit';
				$r['error'] = '';
				$r['class'] = 'success';
			}

		}

		if($errors){
			$r['msg'] = str_replace('{e}', ($total_records - count($a_failed_records)), $r['msg']);
			$r['msg'] = str_replace('{m}', count($a_failed_records), $r['msg']);
		}

		echo json_encode($r);
		exit;

	break;

}

exit;
//////////////////////////
