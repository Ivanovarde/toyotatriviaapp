<?php
class Date {
	
	/**
	 * @desc Devuelve el día en formato de dos dígitos
	 * @return Fecha (solo del día) actual en formato de dos dígitos
	 */
	public static function getShortDay(){
		return date('d');
	}
	
	/**
	 * @desc Devuelve el mes en formato de dos dígitos
	 * @return Mes actual en formato de dos dígitos
	 */
	public static function getShortMonth(){
		return date('m');
	}
	
	/**
	 * @desc Devuelve el año en formato de cuatro dígitos
	 * @return Año actual en formato de cuatro dígitos
	 */
	public static function getShortYear(){
		return date('Y');
	}
	
	/**
	 * @desc Fecha actual en formato normal o formato mysql, según el parametro pasado
	 * @param opcional string $format (mysqlDate) Es la constante que tiene seteada esl sistema como 
	 * formato de fecha, sacada de campo formato_fecha en tabla idioma de la bd
	 * @return Fecha actual en formato normal o formato mysql, según el parametro pasado 
	 */
	public static function dateNow($format=date){
		return self::format(date(date), $format);
	}
	
	/**
	 * @desc Fecha y hora actual en formato normal o formato mysql, según el parametro pasado
	 * @param opcional string $format (mysqlDateTime) Es la constante que tiene seteada esl sistema como 
	 * formato de fecha y hora , basada en campo formato_fecha en tabla idioma de la bd
	 * @return Fecha actual en formato normal o formato mysql, según el parametro pasado 
	 */
	public static function dateTimeNow($format=dateTime){
		return self::format(date(mysqlDateTime), $format);
	}
	
	public static function format($date ,$f = 'd-m-Y'){
		if( (is_null($date)) || (strpos(reset(explode(' ',$dd)), '00') !== false) ){
			return '0000-00-00 00:00:00';
		}
		
		$d = date_parse($date);
		Log::loguear('Date::format: ',$d , false);
		
		$h = ($d['hour']) ? (int)$d['hour'] : 0;
		$m = ($d['minute']) ? (int)$d['minute'] : 0;
		$s = ($d['second']) ? (int)$d['second'] : 0;
		
		$timestamp =  mktime($h, $m, $s, $d['month'], $d['day'], $d['year']);
		
		$final_date = date($f, $timestamp);
		Log::loguear('Date::format: ', $final_date, false);
		
		return $final_date;		
	}
	
	public static function toMysql($date){ 
		
		$d = date_parse($date);
		Log::loguear('Date::format: ',$d , false);
		
		$h = ($d['hour']) ? (int)$d['hour'] : 0;
		$m = ($d['minute']) ? (int)$d['minute'] : 0;
		$s = ($d['second']) ? (int)$d['second'] : 0;
		
		$timestamp =  mktime($h, $m, $s, $d['month'], $d['day'], $d['year']);
		
		$f = 'Y-m-d : H:i:s a';
		$final_date = date($f, $timestamp);
		Log::loguear('Date::format: ', $final_date, false);
		
		return $final_date;
		
		/*if(!is_null($date)){
			ereg( "([0-9]{1,2})-([0-9]{1,2})-([0-9]{2,4})", $date, $myDate); 
			$theDate = $myDate[3]."-".$myDate[2]."-".$myDate[1]; 
			return $theDate; 
		}*/
	}
	
	public static function toNormal($date){ 
		if(!is_null($date)){
			ereg( "([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $date, $myDate); 
			$theDate = $myDate[3]."-".$myDate[2]."-".$myDate[1]; 
			return $theDate; 
		}
	}
	
	public static function timeStampToDate($date, $format = "normal"){
		if(!is_null($date)){
			$myDate = split(" ",$date);
			if($format == "mysql"){
				return self::toMysql($myDate[0]);
			}else{
				return self::toNormal($myDate[0]);
			}
		}
	}
	
	public static function dateTimeToDate($date, $format = "normal"){
		if(!is_null($date)){
			$myDate = split(" ",$date);
			if($format == "mysql"){
				return self::toMysql($myDate[0]);
			}else{
				return self::toNormal($myDate[0]);
			}			
		}
	}
	
	public static function calculateAge($date){
		$myDate = $date;
		// Me fijo si la fecha viene en formato mysql para convertirla a formato normal
		if(!preg_match("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $date )){
			$mydate = self::toNormal($date);
		}
		
		if(preg_match("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", $mydate, $match )){
			$age = date("Y") - $match[3];
			if((int)$match[2] > date("n") || ((int)$match[2] == date("n") && (int)$match[1] > date("j")) ) { $age--; }
			return $age;
		}else{
			return false;
		} 
	} 

	public static function datePeriod($d, $period){
		$date = split("-",$d);
		$year = $date[0];
		$month = $date[1];
		$day = $date[2];
		
		switch ($period){
			case "yesterday":
				$myDate = date("Y-m-d", mktime(0, 0, 0, $month, $day-1, $year)); //ayer
				break;
			case "week":
				$myDate = date("Y-m-d", mktime(0, 0, 0, $month, $day-6, $year)); //7 Dias
				break;
			case "actualMonth":
				$myDate = date("Y-m-d", mktime(0, 0, 0, $month, 1, $year)); //Principio del mes
				break;
			case "lastMonthBegin":
				$myDate = date("Y-m-d", mktime(0, 0, 0, $month-1, 1, $year)); //Principio del mes pasado
				break;
			case "lastMonthFinal":
				$myDate = date("Y-m-d", mktime(0, 0, 0, $month, 0, $year)); //Final del mes Pasado
				break;
		}
		return $myDate;
	}
	
	public static function todayLastMonth($date){
		$d = explode("-", $date);
		
		$year = $d[0];
		$month = $d[1];
		$day = $d[2];
		
		if($month == "01"){
			$month = "12";
			$year = $year-1;
			return $year."-".$month."-".$day;
		}else{
			$month = $month-1;
			if(strlen($month) == 1){$month = "0".$month;}
			return $year."-".$month."-".$day;
		}
	}
	
	public static function timeDiff($firstTime,$lastTime){
		// convert to unix timestamps
		$firstTime=strtotime($firstTime);
		$lastTime=strtotime($lastTime);
		
		// perform subtraction to get the difference (in seconds) between times
		$timeDiff=$firstTime-$lastTime;
		
		// return the difference in seconds
		return $timeDiff;
	}
	
	public function utime_add($unixtime, $hr=0, $min=0, $sec=0, $mon=0, $day=0, $yr=0) { 
		$dt = localtime($unixtime, true); 
		$unixnewtime = mktime($dt['tm_hour']+$hr, $dt['tm_min']+$min, $dt['tm_sec']+$sec, $dt['tm_mon']+1+$mon, $dt['tm_mday']+$day, $dt['tm_year']+1900+$yr); 
		return $unixnewtime; 
	} 
}