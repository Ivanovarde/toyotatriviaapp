<?php
class Session extends ABM{

	public static $inactiveTime;
	public static $session_exists = 				false;

	public function __construct($id=''){
		$this->read_db_table_fields(Tables::SESSIONS);
		$this->read($id);

	}

	public static function start($timeOut = 20){

		self::$inactiveTime = $timeOut;

		if(isset($_SESSION['start']) ) {
			$session_life = time() - $_SESSION['start'];
			if($session_life > self::$inactiveTime){
				self::end();
			}
		}else{
			session_start();
			self::$session_exists = true;
		}
		$_SESSION['start'] = time();
	}

	public static function end(){
		self::$session_exists = false;
		unset($_SESSION['u']);
		unset($_SESSION);
		session_unset();
		session_destroy();
	}

	public static function check_user_session($id=''){
		//Log::l('Session::check_user_session', $id, false);

		$idU = $id;

		if($idU == '' && isset($_SESSION['u'])){
			$idU = $_SESSION['u']->member_id;
		}

		////Log::l('',ini_get("session.gc_maxlifetime"));
		//Log::l('Session check_user_session $idU', $idU, false);
		//Log::l('Session check_user_session $_SESSION["u"]', (isset($_SESSION['u']) ? $_SESSION['u'] : ''), false);

		if($idU != ''){

			Settings::set_globals('session_username', $_SESSION['u']->username);
			Settings::set_globals('no_session_class', '');

			return true;
		} else{

			Settings::set_globals('session_username', '');
			Settings::set_globals('no_session_class', 'session-hidden');

			return false;
		}

	}

	public static function check_session_active(){

		//Log::l('Session::check_session_active', self::$session_exists, false);

		if(self::$session_exists){
			return true;
		}else{
			return false;
		}
	}

}
?>
