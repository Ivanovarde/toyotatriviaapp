<?php
class FlexigridJSONPromo extends FlexigridJSON {

	private $ids_list = '';
	private $rs = '';

	function __construct($idL){
		// Seteo el idioma
		if($idL != ''){
			$this->set_language($idL);
		}

		// Armo un array con los campos de la consulta. En caso de que 2 tablas tengan el mismo nombre
		// hay que agregarle un alias (AS xxxxx) para distinguirlo.
		// El alias será filtrado en FlexigriJSON->processQuery();
		$this->json_fields = array(
									Tables::PROMOS . '.id',
									'lang_code',
									'title',
									'datefrom',
									'dateto',
									'cost',
									Tables::PROMOS . '.status',
									'creation'
									);

		// Seteo los campos de la consulta
		$this->set_fields();

		// Seteo el campo id para poner en las rows del flexigrid
		$this->set_field_id('id');

		// Seteo las tablas de la consulta
		$this->set_table(Tables::PROMOS);

		// Seteo los joins
		$this->set_join(' LEFT JOIN ' . Tables::LANGUAGES .  ' ON ' . Tables::LANGUAGES .  '.id = ' . Tables::PROMOS .  '.lang_id  ');

		// Seteo el where de la consulta
		$this->set_where(' 1 = 1 '/*' ' . Tables::ENTRIES . '.id = ' . Tables::ENTRIESDATA . '.entry_id'*/);

		// Seteo los filtros para usuarios
		$this->set_filters();

		// Armo la consulta
		$this->make_query();

		// Proceso la consulta
		$this->processQuery();
	}

	public function set_filters(){
		$this->status = ($_POST['entryStatus'] != '') ? ' AND ' . $this->json_table . '.status=' . $_POST['entryStatus'] : '';
		$this->date_range = ($_POST['date_range'] != '') ? ' AND DATE_SUB(CURDATE(), INTERVAL ' . $_POST['date_range'] . ' DAY) < ' . $this->json_table . '.creation ' : '';

		$filters = 	$this->status . $this->date_range;

		parent::set_filters($filters);
	}

	public function getJSON(){

		// Armo el JSON llamando a la funcion padre
		parent::getJSON();

		$j = $this->json_data;

		Log::loguear('FlexigridJSONPromo getJSON var $j', $j,false);

		$total_rows = $j['total'];
		Log::loguear('FlexigridJSONPromo getJSON var $total_rows',$total_rows, false);

		for($r=0; $r < $total_rows; $r++){

			$row = $j['rows'][$r];
			$cells = $row['cell'];
			Log::loguear('FlexigridJSONPromo getJSON var $row', $row, false);
			Log::loguear('FlexigridJSONPromo getJSON var $cell', $cells, false);
			Log::loguear('FlexigridJSONPromo getJSON var $row->id', $row['id'], false);

			$promo = new Promo($row['id']);

			for($i=0; $i < count($cells); $i++){
				switch($i){
					case 3: // From
					case 4: // To
					case 7: // Creation
						$j['rows'][$r]['cell'][$i] = Date::format($j['rows'][$r]['cell'][$i],'Y-m-d');
					break;

					case 6:
						$j['rows'][$r]['cell'][$i] = ($promo->status == 0 ? 'Inactive' : 'Active');
					break;

					default:
						$j['rows'][$r]['cell'][$i] = $j['rows'][$r]['cell'][$i];
					break;
				}

				$j['rows'][$r]['cell'][$i] = $j['rows'][$r]['cell'][$i];

				Log::loguear('FlexigridJSONPromo getJSON var $row', $j['rows'][$r], false);
				Log::loguear('FlexigridJSONPromo getJSON var $cell', $j['rows'][$r]['cell'], false);
			}

		}

		$json = json_encode($j);

		Log::loguear('FlexigridJSONPromo::getJSON var $j', $j, false);

		// Devuelvo el json
		return $json;
	}


}