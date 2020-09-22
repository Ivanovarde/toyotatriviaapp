<?PHP
include("config.php");

/*----------------------------------------------------------------------*/
/* Limpio las variables													*/
/*----------------------------------------------------------------------*/
$_SESSION['id_usuario'] = '';
$_SESSION['u_permisos'] = '';
$id_usuario = '';
$u_permisos = '';
unset($_SESSION['id_usuario']);
unset($_SESSION['u_permisos']);
unset ($id_usuario);
unset ($u_permisos);

/*----------------------------------------------------------------------*/
/* Validando acceso														*/
/*----------------------------------------------------------------------*/

$redirect = '';

$sql = "SELECT t3.p_acceso, t1.u_id FROM usuarios AS t1, usuario_permiso AS t2, permisos AS t3 WHERE t1.u_id = t2.up_u_id AND t2.up_p_id = t3.p_id AND t1.u_usuario = '" . addslashes(strip_tags($_POST['user_login'])) . "' AND t1.u_password = '" . addslashes(strip_tags($_POST['password_login'])) . "';";

$result = mysqli_query($link, $sql);

if ($myrow = mysqli_fetch_array($result,MYSQLI_ASSOC)) { // Si existe
	$id_usuario = $myrow["u_id"];
	$permisos = $myrow["p_acceso"];

	$_SESSION['id_usuario'] = $id_usuario;
	$_SESSION['u_permisos'] = $permisos;

	$redirect = "expo-contacto.php";
}
/*----------------------------------------------------------------------*/
echo "go to: " . $global_vars['cms_url'] . '/' . $redirect;
//header("location:" . $global_vars['cms_url'] . "/" . $redirect);
exit;

