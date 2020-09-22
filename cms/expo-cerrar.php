<?php
include("config.php");

$_SESSION['id_usuario'] = '';
$_SESSION['u_permisos'] = '';
$id_usuario = '';
$u_permisos = '';
unset($_SESSION['id_usuario']);
unset($_SESSION['u_permisos']);
unset ($id_usuario);
unset ($u_permisos);

header("Location:" . $global_vars['cms_url'] . '/'); /* Redirect browser */
exit;
