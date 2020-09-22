<?PHP

include('config.php');

if(!isset($_SESSION['id_usuario'])){
	//echo 'go to: ' . $global_vars['cms_url'];
	header("location:" . $global_vars['cms_url'] . "/");
	exit;
}
