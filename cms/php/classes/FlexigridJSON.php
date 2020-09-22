<?php

class FlexigridJSON extends ABM {

	//private $objetos;
	public  $json_page =					'';
	public  $json_total =					'';
	public  $json_start = 					0;
	public  $json_extra_where = 			'';
	public  $json_fields = 					array();
	public  $json_table = 					'';
	public  $json_join = 					'';
	public	$json_filters = 				'';

	public  $json_rp = 						'';
	public  $json_sortname = 				'';
	public  $json_sortorder = 				'';
	public  $json_query = 					'';
	public  $json_qtype = 					'';
	public  $blog_id = 						'';

	public  $json_limit = 					'';
	public  $json_sql_query =				'';
	public  $json_sql_query_paginated =		'';
	public  $json_sql_query_total =			'';
	public  $json_field_id =				'';

	protected $language_id = 					'';
	protected $aFields = 					array();
	protected $aSearchInFields =			array();
	protected $rsProcessed = 				null;
	protected $json_data = 					'';


	function __construct(){

	}

	public function set_language($id){
		$this->language_id = $id;
		Log::loguear('$this->language_id', $this->language_id, false);
	}

	public function set_field_id($field){
		$this->json_field_id = $field;
		Log::loguear('$this->json_field_id', $this->json_field_id, false);
	}

	public function set_fields(){
		$this->json_fields = join(',',$this->json_fields);
		Log::loguear('$this->json_fields', $this->json_fields, false);
	}

	public function set_table($t){
		$this->json_table = $t;
		Log::loguear('$this->json_table', $this->json_table, false);
	}

	public function set_join($join){
		$this->json_join = $join;
		Log::loguear('$this->json_table',$this->json_join, false);
	}

	public function set_where($where){
		$this->json_extra_where = $where;
		Log::loguear('$this->json_estra_where', $this->json_extra_where, false);
	}

	public function set_filters($filters){
		$this->json_filters = $filters;
		Log::loguear('$this->$filters', $this->json_filters, false);
	}

	public function set_sort_order($order){
		$this->json_sortorder = $order;
	}

	public function make_query(){

		// Seteo las variables de POST
		$this->json_page = $_POST['page'];
		$this->json_rp = $_POST['rp'];
		$this->json_sortname = $_POST['sortname'];
		$this->json_sortorder = $_POST['sortorder'];
		$this->json_query = $_POST['query'];
		$this->json_qtype = $_POST['qtype'];

		// Si se busca por keywords
		if(isset($_POST['keywords']) && $_POST['keywords'] != ''){

			$this->keywords = ($_POST['keywords'] != '') ? $_POST['keywords'] : '';
			$this->keywordsSearch = (isset($_POST['exact_match']) && $_POST['exact_match'] == 1) ?
									' = "' . $_POST['keywords'] . '"' : ' LIKE "%' . $_POST['keywords'] . '%" ';

			switch($_POST['search_in']){
				case 'body':

				break;

				case 'everywhere':

				break;

				case 'comments':
					//$this->search_in = ' AND ' . Tables::COMMENTS . '.titulo ' . $this->keywordsSearch;
				break;

				default:
					$this->search_in = ' AND ' . Tables::ENTRIESDATA . '.title ' . $this->keywordsSearch;
				break;

			}
		}

		// Asigno variables que pueden no venir en POST
		if (!$this->json_page) $this->json_page = 1;
		if (!$this->json_rp) $this->json_rp = 10;
		if (!$this->json_sortname) $this->json_sortname = $this->aFields[0];
		if (!$this->json_sortorder) $this->json_sortorder = 'DESC';

		$this->json_start = (($this->json_page - 1) * $this->json_rp);

		// Asigno el join
		$join = ($this->json_join) ? $this->json_join : '';

		// Asigno el where
		$where  = ($this->json_query)  ? " WHERE " . $this->json_qtype . " LIKE '%" . $this->json_query . "%' " : "";
		$where .= ($where) ? " AND " . $this->json_extra_where : ($this->json_extra_where) ? " WHERE " . $this->json_extra_where . " " : "";

		// Asigno el group by
		//$this->groupBy = " GROUP BY " . Tables::RELATIONSHIPS . ".father ";

		// Asigno el orden de registros
		$sort = " ORDER BY " . $this->json_sortname  . " " . $this->json_sortorder;

		// Asigno el limite de registros
		$limit = "LIMIT " . $this->json_start . ", " . $this->json_rp;

		$this->json_sql_query = "SELECT " .
								$this->json_fields .
								" FROM " . $this->json_table . " " .
								$join . " " .
								$where . " " .
								// parametros del form externo
								$this->blog_id .
								$this->category_id .

								$this->json_filters .

								$this->search_in .
								$this->groupBy .
								$sort  . " " ;

		//SQL paginado
		$this->json_sql_query_paginated = $this->json_sql_query . $limit;

		$this->json_sql_query_total = "SELECT COUNT(" . $this->json_table . '.' . $this->json_field_id . ")" .
									   " FROM " . $this->json_table . " " .
									   $join . " " .
									   $where . " " .
									   $this->blog_id .
									   $this->category_id .

									   $this->json_filters .

									   $this->search_in .
									   $this->groupBy;

		Log::loguear('Flexigrid::make_query var $json_sql_query_paginated ', $this->json_sql_query_paginated, false);

	}

	protected  function processQuery(){

		try {
			// Me fijo si esta seteado el idioma
			if ($this->language_id == '') {
				throw new NMDException('Idioma no seleccionado');
			}
		}
		catch (NMDException $e) {
			$e->showError();
			exit;
		}

		// Separo los posibles nombres de las tablas en los nombres campos
		// (Ej. entrada_data.id)
		// Armo el array aFields con los nombres de los campos para usarlos en getJSON()
		$f = explode(',', $this->json_fields);
		foreach($f as $field){
			// Filtro el nombre o alias de cada fieldname seteado en json_fields
			$this->aFields[] = end(explode('.', end(explode(' ',$field))));
		}

		$db = new DB();
		$db->set_query($this->json_sql_query_paginated);
		$this->rsProcessed = $db->execute();

		// Seteo el total de registros
		$db->set_query($this->json_sql_query_total);
		$this->json_total = $db->execute_value();

		//var r2 = r1 + p.rp - 1;
		Log::loguear('FlexigridJSON::processQuery var $this->aFields', $this->aFields, false);

	}

	public function getJSON(){

		$rs = $this->rsProcessed;

		$js['page'] = $this->json_page;
		$js['total'] = $this->json_total;
		$js['rows'] = array();
		$js['data'] = array();
		$js['data']['id'] = '';
		$js['data']['cell'] = '';

		// Armo el json para flexigrid
		for ($i = 0; $i < count($rs); $i++){

			$js['data']['id'] = $rs[$i][$this->json_field_id];
			for($j = 0; $j < count($this->aFields); $j++){
				Log::loguear('FlexigridJSON getJSON $this->aFields[$j]', $js['data']['id'], false);
				$js['data']['cell'][] = $rs[$i][$this->aFields[$j]];
			}

			$js['rows'][$i] = $js['data'];
			unset($js['data']);
		}
		Log::loguear('FlexigridJSON::getJSON var $js (JsonEncode)',json_encode($js), false);

		// Guardo el json en cache


		$this->json_data = $js;

	}


	function show_error($msg){
		return $msg;
	}



}