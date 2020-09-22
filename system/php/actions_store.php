<?PHP

$m->nombre = ucfirst(strtolower($a_record_data[10]));;
$m->apellido = ucfirst(strtolower($a_record_data[11]));;
$m->telefono = $a_record_data[13];
$m->email = strtolower($a_record_data[12]);;
$m->provincia = $a_record_data[14];
$m->ciudad = ucfirst(strtolower($a_record_data[15]));
$m->modelo = $a_record_data[8];
$m->plan = $a_record_data[3];
$m->cuotas = $a_record_data[0];
$m->precio_publico = str_replace("|","", $a_record_data[2]);
$m->cuota_pura = str_replace("|","", $a_record_data[4]);
$m->carga_admin_suscription = str_replace("|","", $a_record_data[5]);
$m->iva_21 = str_replace("|","", $a_record_data[6]);
$m->cuota_mensual = str_replace("|","", $a_record_data[1]);
$m->pago_adjudicacion_30 =  empty($a_record_data[7]) ? '0.00' : str_replace("|","", $a_record_data[7]);

$m->tipo_vehiculo = ;
$m->linea = ;
$m->categoria_nombre =  '';
$m->categoria_titulo =  '';

/*
$line_tmp = $a_record_data[16];
$tipo_vehiculo_tmp = $a_record_data[17];

$record->tipo_vehiculo = $a_record_data[17];

$record->created = date('Y-m-d H:i:s');
//$record->email_sent = 0;
//$record->email_sent_date = null;

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

$record->linea = $line_name;
$record->tipo_vehiculo = $vehicle_type_title;
*/
