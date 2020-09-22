<?php
class DB {
	public $host;
	public $user;
	public $password;
	public $databaseName;
	private $connection;
	private $insert_id;
	private $affectedRows;
	private $serverName;

	public $errorInfo = 						'';
	public $errorNum = 							'';
	public $error_Msg = 						'';
	public $query = 							'';

	public function __construct($db="", $h="", $u="", $p="") {

		$this->serverName = (Server::getServerName() != '') ? Server::getServerName() : $_SERVER['SERVER_NAME'];
		$this->host = 'localhost';

		switch($this->serverName){
			case 'expoagroapp2019.nmd':
				$this->user = 'root';
				$this->password = 'root';
				$this->databaseName = 'expoagroapp2019';
			break;

			case 'expoagroapp2019.neomedia.com.ar':
				$this->host = 'zeus.servidoraweb.net';
				//$this->user = 'ivano_expoagrocms';
				//$this->password = 'expoagrocms#';
				$this->user = 'ivano_admin';
				$this->password = 'ivano22';
				$this->databaseName = 'ivano_expoagroapp2019';
			break;

			case 'mercedesbenzappcms.nmd':
				$this->host = 'localhost';
				$this->user = 'root';
				$this->password = 'root';
				$this->databaseName = 'mercedescms2019';
			break;

			default:
				$this->host = 'zeus.servidoraweb.net';
				$this->user = 'ivano_admin';
				$this->password = 'ivano22';
				$this->databaseName = 'ivano_mercedesappcms';
			break;
		}

		if(!empty($db) && !empty($h) && !empty($u) && !empty($p)){
			$this->host = $h;
			$this->user = $u;
			$this->password = $p;
			$this->databaseName = $db;
		}
	}

	private function openDB() {

		try {
			$this->connection = new PDO(
				'mysql:host=' . $this->host . ';dbname=' . $this->databaseName . ';charset=utf8',
				$this->user,
				$this->password
			);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			$this->errorInfo = $this->connection->errorInfo();
			if (reset($this->errorInfo) != 0) {
				$this->errorNum = $this->errorInfo[0] . ' | ' . $this->errorInfo[1];
				$this->error_Msg = $this->errorInfo[2];
				throw new NMDDBException($this->databaseName, $this->error_Msg, $this->errorNum, $this->query);
			}

		}
		catch (NMDDBException /*PDOException*/  $e) {
			//$e->getMessage();
			$e->showError();
			exit;
		}
	}

	private function closeDB() {
		//mysql_close($this->connection);
	}

	public function set_query($sql){
		$this->query = $sql;
		//Log::l('DB set_query', $this->query, false);
	}

	public function execute() {
		$this->openDB();

		try{

			//Log::l('DB execute', $this->query, false);
			//Log::l('DB execute', $this->connection->query($this->query), false);

			if(!$rs = $this->connection->query($this->query)){
				$this->errorInfo = $this->connection->errorInfo();
				$this->errorNum = $this->errorInfo[0] . ' | ' . $this->errorInfo[1];
				$this->error_Msg = $this->errorInfo[2];
				throw new NMDDBException($this->databaseName, $this->error_Msg, $this->errorNum, $this->query);
			}
		}
		catch (NMDDBException  $e) {
			$e->getMessage();
			$e->showError();
			exit;
		}

		$aRecords = array();

		//while($record = $rs->fetch(PDO::FETCH_ASSOC)) {
		  while($record = $rs->fetch(PDO::FETCH_OBJ)) {
			array_push($aRecords, $record);
		}
		$this->closeDB();
		//Log::l('DB execute', $this->query, false);
		return $aRecords;
	}

	/**
	 * Ejecuta una consulta a la base de datos sin seleccionar ningun registro
	 * Se usa mayormente para consultas tipo UPDATE, INSERT, DELETE	 *
	 * @param $sql Sql Sentence
	 * @return true / false
	 */
	public function execute_non_query() {
		$this->openDB();

		//Log::l('DB::execute_non_query query', $this->query, false);

		try{
			$result = $this->connection->exec($this->query);
			if($result === false){
				////Log::l('DB->execute_non_query errorInfo: ', $this->connection->errorInfo(), false);
				throw new NMDDBException($this->databaseName, $this->connection->errorInfo(), $this->query);
			}
		}
		catch (NMDDBException  $e) {
			//$e->getMessage();
			$e->showError();
			exit;
		}

		$this->affectedRows = $result;
		$this->insert_id = $this->connection->lastInsertId();

		//Log::l('DB::execute_non_query result', $result, false);

		return $result;
	}

	public function execute_value() {
		$records = $this->execute($this->query);
		//Log::l('DB::execute_value SQL',$this->query, false);
		//Log::l('DB::execute_value $records', $records, false);
		//Log::l('DB::execute_value $records count', count($records), false);
		if(count($records) > 0){
			//Log::l('DB::execute_value $records[0]',$records[0], false);
			//Log::l('DB::execute_value $records[0][0]',reset($records[0]), false);
			return reset($records[0]);
		}
		return;
	}

	public function executeCount($table, $field='*', $where='') {
		$sql = "SELECT COUNT(" . $field . ") FROM " . $table . " " . $where;
		$this->set_query($sql);
		//Log::l('DB->executeCount: ', $this->query, false);

		return $this->execute_value();
	}

	public function executeTable($table) {
		$this->query = "SELECT * FROM " . $table;
		return $this->execute();
	}

	public function execute_record() {
		//Log::l('DB execute_record query', $this->query, false);
		$records = $this->execute($this->query);
		//Log::l('DB execute_record', $records, false);
		return $records[0];
	}

	public function get_insert_id(){
		return $this->insert_id;
	}

	public function getAffectedRows(){
		return $this->affectedRows;
	}

}

?>
