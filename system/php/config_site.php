<?PHP

$config_site = array();

switch ( $_SERVER['SERVER_NAME'] ) {

	// local
	case 'mercedesappcms.nmd' :
	case 'mercedesbenzappcms.nmd' :
		$config_site['site_short_name'] = 'Mercedes Benz Expo App NMD';
		$config_site['is_live_site'] = false;
		$config_site['static_subdomain'] = '' . $_SERVER['SERVER_NAME'];
		$config_site['staticimg_subdomain'] = '' . $_SERVER['SERVER_NAME'];

		$config_site['hostname'] = "localhost";
		$config_site['username'] = "root";
		$config_site['password'] = "root";
		$config_site['database'] = "mercedescms2019";

	break;


	// live
	default :
		$config_site['site_short_name'] = 'Mercedes Benz Expo App';
		$config_site['is_live_site'] = true;
		$config_site['static_subdomain'] = 'static.' . $_SERVER['SERVER_NAME'];
		$config_site['staticimg_subdomain'] = 'static.' . $_SERVER['SERVER_NAME'];
		//$config_site['static_subdomain'] = 'static.' . str_replace('dev.', '', $_SERVER['SERVER_NAME']);
		//$config_site['staticimg_subdomain'] = 'images.' . str_replace('dev.', '', $_SERVER['SERVER_NAME']);

		$config_site['hostname'] = "localhost";
		$config_site['username'] = "ivano_admin";
		$config_site['password'] = "ivano22";
		$config_site['database'] = "ivano_mercedesappcms";

	break;

}


