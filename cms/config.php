<?PHP

session_start();

$global_vars['protocol'] = 'http://';
$global_vars['site_url'] = $global_vars['protocol'] . $_SERVER['SERVER_NAME'];
$global_vars['cms_url'] = $global_vars['site_url'] . '/cms';


/*----------------------------------------------------------------------*/
/* Extracción de los datos del archivo de configuración                 */
/*----------------------------------------------------------------------*/
include("expo-db.php");

include('expo-funciones.php');
