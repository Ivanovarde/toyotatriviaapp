<?php

switch($_SERVER['SERVER_NAME']){
	case 'expoagroapp2019.nmd':
		define("HOST","127.0.0.1");
		define("USUARIO","root");
		define("PASSWORD","root");
		define("DATABASE","expoagroapp2019");
	break;

	default:
		define("HOST","127.0.0.1");
		define("USUARIO","ivano_expoagrocms");
		define("PASSWORD","expoagrocms#");
		define("DATABASE","ivano_expoagroapp2019");
	break;

	case 'mercedesbenzappcms.nmd':
		define("HOST","127.0.0.1");
		define("USUARIO","root");
		define("PASSWORD","root#");
		define("DATABASE","mercedescms2019");
	break;

	default:
		define("HOST","zeus.servidoraweb.net");
		define("USUARIO","ivano_admin");
		define("PASSWORD","ivano22");
		define("DATABASE","ivano_mercedesappcms");
	break;
}

$link = mysqli_connect(HOST, USUARIO, PASSWORD, DATABASE);

if (!$link) {
	 echo "Error: Unable to connect to MySQL." . PHP_EOL;
	 echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	 echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	 exit;
}
//
//$db = mysqli_select_db(DATABASE, $link);
//if (!$db) {
//	die ('Can\'t use foo : ' . mysqli_error());
//}

?>
