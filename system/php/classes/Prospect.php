<?php
class Prospect extends Entry {

	public $db_table_name =					Tables::USERS;

	function __construct($id=""){

		Log::l($id, 'Prospect id', false);
		Log::l($this->db_table_name, 'Prospect Tables::USERS', false);

		parent::__construct($this->db_table_name, $id);

	}

}
