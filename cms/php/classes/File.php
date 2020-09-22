<?php
class File {
	
	/** -------------------------------------
	/**  File name security
	/** -------------------------------------*/
	
	public static function filename_security($str){
		$bad = array(
						"../",
						"./",
						"<!--",
						"-->",
						"<",
						">",
						"'",
						'"',
						'&',
						'$',
						'#',
						'{',
						'}',
						'[',
						']',
						'=',
						';',
						'?',
						'/',
						"%20",
						"%22",
						"%3c",		// <
						"%253c", 	// <
						"%3e", 		// >
						"%0e", 		// >
						"%28", 		// (  
						"%29", 		// ) 
						"%2528", 	// (
						"%26", 		// &
						"%24", 		// $
						"%3f", 		// ?
						"%3b", 		// ;
						"%3d"		// =
        			);
        			

		$str =  stripslashes(str_replace($bad, '', $str));
		
		return $str;
	}
	/* END */
	
	public static function get_extension($f){
		$ext = end(explode('.', $f));
		Log::loguear('File::get_extension: ', $ext, false);
		
		return strtolower($ext);
	}
}