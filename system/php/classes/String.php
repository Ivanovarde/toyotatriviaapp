<?php
class String {

	public static function limitWords($str, $stop, $enc='utf8'){
		switch($enc){
			case 'latin':
				$chars = '������������������ܿ?.()';
			break;

			default:
				$chars = utf8_encode('������������������ܿ?.()');
		}

		$c = (int)$stop;

		$str = strip_tags($str);
		if (strlen($str) > $c){

			$short_str = substr($str, 0, $c); // obtener la frase cortada.
			$words = str_word_count($short_str, 1, $chars); // obtener array con las palabras.
			//$words = explode(' ',$short_str);
			$total_words = count($words) - 1; // contar total array elementos y restar 1 elementos
			$words = array_splice($words, 0, $total_words); // le quitamos la ultima palabra.
			$str_output = implode(' ', $words); //  y concatenamos con el espacio hacia una cadena.
			$str_output .= "..."; // se a�aden los puntos suspensivos a la cadena obtenida..

		}else{

			$str_output = $str;
		}

		return $str_output;
	}

	public static function random_text($length) {

		$key = '';

		$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
		for($i=0;$i<$length;$i++) {
			$key .= $pattern{rand(0,35)};
		}
		return $key;
	}

	public static function makeParagraphs($text, $p=2){

		$t = explode(' ', $text);

		$total = count($t);
		$breaklines = round($total/$p);

		$r = array(); // Variable de retorno
		$c = 0; // Contador de palabras en el array $t

		for($j=0; $j < $p; $j++){

			$breaklines = $breaklines * ($j+1); // Multiplico los cortes de linea en cada parrafo
			Log::loguear('String::breaklines Comienzo', $breaklines, false);

			for($i=$c; $i < $breaklines; $i++){
				$r[$j] .= $t[$i] . ' ';
			}

			$c = $i; // Reseteo el contador para que comience en la priper posicion del segundo arrary
			Log::loguear('String::breaklines Final', $breaklines, false);
		}
		Log::loguear('String::makeParagrpah', $r, false);

		return $r;
	}

	public static function camelize($str, $utf8=true){
		$str = ($utf8) ? utf8_decode($str) : $str;
		$t = explode(' ', $str);
		foreach($t as $word){
			if(strlen($word) > 2){
				$s = strtolower($word);
				$s = ucfirst($s);
				$string[] = $s;
			}else{
				$string[] = strtolower($word);
			}
		}
		if($utf8){
			$return = utf8_encode(join(' ', $string));
		}else{
			$return = join(' ', $string);
		}
		return $return;
	}
}
