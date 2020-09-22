<?php
class Upload{

	var $is_image       	= 1;
    var $width				= '';
    var $height				= '';
    var $imgtype			= '';
    var $size_str			= '';
    var $mime				= '';
    var $max_size			= 0;
    var $max_width			= 0;
    var $max_height			= 0;
	var $max_filename		= 0;
    var $remove_spaces		= 1;
    var $allowed_types		= "img";  // img or all
    var $file_temp			= "";
    var $file_name			= "";
    var $file_type			= "";
    var $file_size			= "";
    var $new_name			= "";
    var $allowed_mimes		= array();
    var $img_mimes			= array();
    var $upload_path		= "../uploads/";
    var $temp_prefix		= "temp_file_";
    var $message			= '';
    var $file_exists		= FALSE;
	//var $xss_clean		= TRUE;
    var $error_msg			= '';
    var $agent				= '';

    var $new_created_image 	= '';
	var $file;
	var $extension			= '';

	// AUTORESIZE VARS
	var $auto_resize		= FALSE;
	var $auto_resize_ratio	= FALSE;
	var $auto_height		= '';
	var $auto_width			= '';

	//THUMBS VARS
	var $auto_thumb 		= FALSE;
	var $thumb_name			= '';
	var $thumb_width 		= '';
	var $thumb_height 		= 50;
	var $thumb_sufix		= '_thumb';


	public function __construct($f=''){
		$this->agent = ( ! isset($_SERVER['HTTP_USER_AGENT'])) 	? '' :
						$_SERVER['HTTP_USER_AGENT'];

		$this->allowed_mimes = $this->get_mimes();

		$this->img_mimes = array(
								'image/gif',
								'image/jpg',
								'image/jpe',
								'image/jpeg',
								'image/pjpeg',
								'image/png',
								'image/x-png', // shakes fist at IE
								'image/bmp',
								// Added by Ivan
								'application/octet-stream'
								);

		//$this->file = $f;
		$c = 0;
		foreach($_FILES as $f){
			if($c < 1){
				$this->file = $f;
			}
			$c++;
		}
		$this->extension = end(explode('.',$this->file['name']));
	}

	protected function get_mimes(){
		$mimes = array(
						'psd'	=>	'application/octet-stream',
						'pdf'	=>	'application/pdf',
						'swf'	=>	'application/x-shockwave-flash',
						'sit'	=>	'application/x-stuffit',
						'tar'	=>	'application/x-tar',
						'tgz'	=>	'application/x-tar',
						'zip'	=>	'application/zip',
						'gzip'	=>	'application/x-gzip',
						'bmp'	=>	'image/bmp',
						'gif'	=>	'image/gif',
						'jpeg'	=>	'image/jpeg',
						'jpg'	=>	'image/jpeg',
						'jpe'	=>	'image/jpeg',
						'png'	=>	'image/png',
						'txt'	=>	'text/plain',
						'html'	=>	'text/html',
						'doc'	=>	'application/msword',
						'docx'	=>	'application/msword',
						'xl'	=>	'application/excel',
						'xls'	=>	'application/excel',
						'flv'	=>	'video/x-flv',
						'mov'	=>	'video/quicktime',
						'qt'	=>	'video/quicktime',
						'mpg'	=>	'video/mpeg',
						'mpeg'	=>	'video/mpeg',
						'mp3'	=>	'audio/mpeg',
						'aiff'	=>	'audio/x-aiff',
						'aif'	=>	'audio/x-aiff',
						'aac'	=>	'audio/aac'
					);// shakes fist at IE

		if (isset($this->agent) && stristr($this->agent, 'MSIE') !== FALSE)	{
			$mimes['png'] = 'image/x-png';
		}

		return $mimes;
	}

	function get_upload_path(){
		return $this->upload_path;
	}

    function upload_file(){
		if ( ! is_uploaded_file($this->file['tmp_name'])){
            $error = ( ! isset($this->file['error'])) ? 4 : $this->file['error'];

            switch($error){
                case 1  :   $this->error_msg = 'file_exceeds_ini_limit';
                    break;
                case 3  :   $this->error_msg = 'file_partially_uploaded';
                    break;
                case 4  :   $this->error_msg = 'no_file_selected';
                    break;
                default :   $this->error_msg = 'file_upload_error';
                    break;
            }

            return FALSE;
		}

		$this->file_temp = $this->file['tmp_name'];
		$this->file_name = ($this->file_name != '') ? $this->_prep_filename($this->file_name) : $this->_prep_filename($this->file['name']);
		$this->file_size = $this->file['size'];

		// mime-type is always application/octet-stream when using flash for uploading
		/*if($this->file['type'] == 'application/octet-stream') {
			if (function_exists('finfo_open')) {
				$f = finfo_open(FILEINFO_MIME);
				$mime = finfo_file($f, $this->file['tmp_name']);
				finfo_close($f);
				$this->file_type = $mime;
		    }elseif (class_exists('finfo')) {
		    	$f = new finfo(FILEINFO_MIME);
		    	$this->file_type = $f->file($this->file['tmp_name']);
		    }elseif (strlen($mime=@shell_exec("file -bi ".escapeshellarg($this->file['tmp_name'])))!=0) {
		    	//Using shell if unix an authorized
		    	$this->file_type = trim($mime);
		    }elseif (function_exists('mime_content_type')) {
		    	//Double check the mime-type with magic-mime if avaible
		    	$this->file_type = mime_content_type($this->file['tmp_name']);
		    }
		}*/

		$this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $this->file['type']);


        /** -------------------------------------
        /**  Determine if the file is an image
        /** -------------------------------------*/

        $this->validate_image();

        /** -------------------------------------
        /**  Is the filetype allowed?
        /** -------------------------------------*/

        if ( ! $this->allowed_filetype()){
			// Commented by Ivan
        	$this->error_msg = 'invalid_filetype_1';
			return FALSE;
        }

        /** -------------------------------------
        /**  Is the file size allowed?
        /** -------------------------------------*/

        if ( ! $this->allowed_filesize()){
			$this->error_msg = 'File size is too large!';
			return FALSE;
        }

        /** -------------------------------------
        /**  Are the dimensions allowed?
        /** -------------------------------------*/

        // Note:  If the server has a very restrictive open_basedir this
        // function will fail and thus not set the width/height properties.

        if ( ! $this->allowed_dimensions()){
			$this->error_msg = 'invalid_dimensions';
			return FALSE;
        }

        /** -------------------------------------
        /**  Set image properties
        /** -------------------------------------*/

		$this->set_properties();

        /** -------------------------------------
        /**  Remove white space in file name
        /** -------------------------------------*/

        if ($this->remove_spaces == 1){
            $this->file_name = preg_replace("/\s+/", "_", $this->file_name);
        }

        // sanitize file name
        $this->file_name = File::filename_security($this->file_name);

		// Truncate the file name if it's too long
		if ($this->max_filename > 0){
			$this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
		}

        /** -------------------------------------
        /**  Does file already exist?
        /** -------------------------------------*/

        // If so we'll give the file a temporary name so we can upload it.
        // The file will be renamed in a different step depending on whether
        // the user wants to overwrite the existing file or use a different name.

        // Note:  If $this->new_name is already set it overrides the normal test

        if($this->new_name != ''){
        	$this->new_name = File::filename_security($this->new_name);
        	// Added by Ivan
        	//$this->extension = end(explode('.', $this->new_name));
        	$this->new_name = str_replace($this->extension,'',$this->new_name) .  strtolower($this->extension);
        	// Added by Ivan

			// Truncate the file name if it's too long
			if ($this->max_filename > 0){
				$this->new_name = $this->limit_filename_length($this->new_name, $this->max_filename);
			}

        	if (sizeof(explode('.', $this->new_name)) == 1 || (array_pop(explode('.', strtolower($this->file_name))) != array_pop(explode('.', $this->new_name)))){
        		$this->error_msg = 'invalid_filetype_2';
				return FALSE;
        	}

			$this->new_name = $this->upload_path . $this->new_name;
        }else{
			if (file_exists($this->upload_path . $this->file_name)){
				$this->new_name = $this->upload_path . $this->temp_prefix . $this->file_name;
				$this->file_exists = TRUE;
			}else{
				$this->new_name = $this->upload_path . $this->file_name;
				$this->file_exists = FALSE;
			}
        }

        /** ---------------------------------------------------
        /**  Move the uploaded file to the final destination
        /** ---------------------------------------------------*/

		if ( ! @copy($this->file_temp, $this->new_name)){
			if ( ! @move_uploaded_file($this->file_temp, $this->new_name)){
				 $this->error_msg = 'upload_error';
				 return FALSE;
			}
		}

		/** -------------------------------------
        /**  Set Image Properties
        /** -------------------------------------*/

        // Note: We called this function earlier but it might have
        // failed if the server is running an open_basedir restriction
        // since we can't access the "tmp" directory above the root.
        // For that reason we will run this function again on the
        // uploaded image in its final location.

        /*
        	If this is an image and getimagesize() returns FALSE, then PHP does
        	not think this file is a valid image, so we delete the image and
        	throw an error.
        */

        if ($this->is_image === 1 && $this->set_properties($this->new_name) === FALSE){
			$this->error_msg = 'invalid_filecontent_1';

			unlink($this->new_name);

			return FALSE;
		}

		// The $this->mime variable will always return image/jpeg for a jpeg and image/png for a png,
		// but IE 6/7 will sometimes send a different JPEG or PNG MIME during upload so we have to
		// do a quick conversion before testing. - Paul

		$png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

		if (in_array($this->file_type, $png_mimes))	{
			$this->file_type = 'image/png';
		}

		if (in_array($this->file_type, $jpeg_mimes)){
			$this->file_type = 'image/jpeg';
		}


        /** -------------------------------------
        /**  XSS Clean the file
        /** -------------------------------------*/

 		/*if ($this->do_xss_clean() === FALSE){
 			$this->error_msg = 'invalid_filecontent';

			unlink($this->new_name);

			return FALSE;
 		}*/


    	/** -------------------------------------
        /**  Auto resize Added By Ivan
        /** -------------------------------------*/

		if($this->auto_resize){
			// Keeping Ratio aspect
			if($this->auto_resize_ratio){
				if($this->auto_width > $this->auto_height){
					$this->auto_height = 0;
				}else{
					$this->auto_width = 0;
				}
			}
			$this->resize($this->auto_width, $this->auto_height);
		}

    	// Legacy fix required to allow FTP users access to uploaded files in certain
		// server environments removed 6/5/08 - D'Jones
		@chmod($this->new_name, 0777);

		if($this->auto_thumb){
			if($this->thumb_name == ''){
				$ext = end(explode('.',$this->new_name));
				$name = str_replace($this->upload_path, '', $this->new_name);
				$name = str_replace($ext,'',$name);
				$name = substr($name,0,strlen($name)-1);
			}else{
				$name = $this->thumb_name;
			}
			$name = File::filename_security($name);
			$this->set_thumbnail_name($name);
			$this->create_thumbnail();
			$this->set_thumbnail_size($this->thumb_width, $this->thumb_height);
		}


		/** -------------------------------------
        /**  MySQL Timeout Check?
        /** -------------------------------------*/

        // If MySQL has a low timeout value, then the connection might have been lost
        // So, we make sure we are still connected and proceed.

		//$DB->reconnect();

		return TRUE;
    }
    /* END */


    /** -------------------------------------
    /**  Validate image
    /** -------------------------------------*/

    function validate_image(){
        $this->is_image = (in_array($this->file_type, $this->img_mimes)) ? 1 : 0;

        ///////Ivan
        if($this->is_image == 0){
        	$this->error_msg = 'Image not valid' . $this->file_type. ' '. $this->img_mimes;
        	return false;
        }
    }
    /* END */


    /** -------------------------------------
    /**  Verify filetype
    /** -------------------------------------*/

    function allowed_filetype(){
    	if ( ! strpos($this->file_name, '.')){
    		$this->error_msg = 'There is no . in the name of the file';
    		return FALSE;
    	}

        if ($this->allowed_types == 'img'){
            if ($this->is_image == 1){
                return TRUE;
            }else{
            	$this->error_msg = 'File is not an image '.$this->file_type;
            	return FALSE;
            }
        }else{
        	$ext = $this->fetch_extension();

        	if ( ! isset($this->allowed_mimes[$ext])){
        		$this->error_msg = 'File extension is not in mime types';
        		return FALSE;
        	}else{
        		return TRUE;
        	}
        }
    }
    /* END */


    /** -------------------------------------
    /**  Set upload directory path
    /** -------------------------------------*/

    function set_upload_path($path) {
        if ( ! @is_dir($path)){
			$this->error_msg = 'path_does_not_exist';
			return FALSE;
        }

        $this->upload_path = rtrim($path, '/').'/';
		return TRUE;
    }
    /* END */


    /** -------------------------------------
    /**  Set maximum filesize
    /** -------------------------------------*/

    function set_max_filesize($n, $kb = FALSE){
    	if ($kb == TRUE){
    		$n = $n * 1024;
    	}
        $this->max_size = ( ! preg_match("#^[0-9]+$#", $n)) ? 0 : $n;
    }
    /* END */


    /** -------------------------------------
    /**  Set file name
    /** -------------------------------------*/
	function set_filename($n){
		$this->file_name = $n;
	}
    /* END */


    /** -------------------------------------
    /**  Set maximum file name length
    /** -------------------------------------*/
	function set_max_filename($n){
		$this->max_filename = ((int) $n < 0) ? 0: (int) $n;
	}
    /* END */


	/** -------------------------------------
    /**  Set maximum width
    /** -------------------------------------*/

    function set_max_width($n){
        $this->max_width = ( ! preg_match("#^[0-9]+$#", $n)) ? 0 : $n;
    }
    /* END */


    /** -------------------------------------
    /**  Set maximum height
    /** -------------------------------------*/

    function set_max_height($n){
        $this->max_height = ( ! preg_match("#^[0-9]+$#", $n)) ? 0 : $n;
    }
    /* END */


    /** -------------------------------------
    /**  Set allowed filetypes
    /** -------------------------------------*/

    function set_allowed_types($types){
        $options = array('img', 'all');

        if ($types == '' || ! in_array($types, $options)){
        	$types = 'img';
        }

        $this->allowed_types = $types;
    }
    /* END */


    /** -------------------------------------
    /**  Fetch file extension
    /** -------------------------------------*/

	function fetch_extension(){
		//$x = explode('.', $this->file_name);
		//return strtolower(end($x));
		return $this->extension;
	}
	/* END */

	/** -------------------------------------
    /**  Verify filesize
    /** -------------------------------------*/

    function allowed_filesize(){
        if ($this->max_size != 0  &&  $this->file_size > $this->max_size){
            return FALSE;
        }else{
            return TRUE;
        }
    }
    /* END */


    /** -------------------------------------
    /**  Verify image dimensions
    /** -------------------------------------*/

    function allowed_dimensions(){
        if ($this->is_image != 1){
            return TRUE;
        }

        if (function_exists('getimagesize')){
            $D = @getimagesize($this->file_temp);

            if ($this->max_width > 0 && $D['0'] > $this->max_width){
                //return FALSE;
                // Added by Ivan
            	if($this->auto_resize){
                	$this->auto_width = $this->max_width;
                }else{
                	return FALSE;
                }
                // Added by Ivan
            }

            if ($this->max_height > 0 && $D['1'] > $this->max_height){
                //return FLASE;
            	// Added by Ivan
            	if($this->auto_resize){
            		$this->auto_height = $this->max_height;
                }else{
                	return FALSE;
                }
                // Added by Ivan
            }

            return TRUE;
        }

        return TRUE;
    }
    /* END */


    /** -------------------------------------
    /**  Set image properties
    /** -------------------------------------*/

    function set_properties($path = ''){
        if ($this->is_image != 1){
            return;
        }

        if ($path == ''){
        	$path = $this->file_temp;
        }

        if (function_exists('getimagesize')) {
            $D = @getimagesize($path);

            // Invalid image!
            if ($D === FALSE || ($D['0'] == 1 && $D['1'] == 1)){
            	//return FALSE;
            }

			$this->width    = $D['0'];
			$this->height   = $D['1'];
			$this->imgtype  = $D['2'];
			$this->size_str = $D['3'];  // string containing height and width
			$this->mime		= (isset($D['mime'])) ? $D['mime'] : '';

            return TRUE;
        }

        return TRUE;
    }
    /* END */


	/**
	 * Limit the File Name Length
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function limit_filename_length($filename, $length){
		if (strlen($filename) < $length){
			return $filename;
		}

		$ext = '';
		if (strpos($filename, '.') !== FALSE){
			$parts		= explode('.', $filename);
			$ext		= '.'.array_pop($parts);
			$filename	= implode('.', $parts);
		}

		return substr($filename, 0, ($length - strlen($ext))).$ext;
	}
	/* END */


	/** -------------------------------------
    /**  Show Error Message
    /** -------------------------------------*/

	function show_error($msg = ''){
		if ($this->error_msg == '')	{
			$this->error_msg = 'file_upload_error';
		}

		if ($msg != '')	{
			$this->error_msg = $msg;
		}

		return $this->error_msg;
	}
	/* END */


	/** -------------------------------------
    /**  Prep Filename
    /**  Prevents possible script execution from Apache's handling of files multiple extensions
    /**  http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
    /** -------------------------------------*/

	function _prep_filename($filename){
		if (strpos($filename, '.') === FALSE){
			return $filename;
		}

		$parts		= explode('.', $filename);
		$ext		= array_pop($parts);
		$filename	= array_shift($parts);

		foreach ($parts as $part){
			if (! in_array(strtolower($part), $this->allowed_mimes)){
				$filename .= '.'.$part.'_';
			}else{
				$filename .= '.'.$part;
			}
		}

		$filename .= '.'.strtolower($ext);

		return $filename;
	}
	/* END */


	/** -------------------------------------
    /**  File overwrite
    /** -------------------------------------*/

    function file_overwrite($orig = '', $new = '', $type_match=TRUE) {

        /*$original_file = ($orig != '') ? $orig : $IN->GBL('original_file');
        $this->file_name = ($new != '') ? $new : $IN->GBL('file_name');*/
    	$original_file = ($orig != '') ? $orig : $this->file_name;
        $this->file_name = ($new != '') ? $new : $this->file_name;

        // If renaming a file, it should have same file type suffix as the original

        if ($type_match === TRUE){
        	if (sizeof(explode('.', $this->file_name)) == 1 || (array_pop(explode('.', $this->file_name)) != array_pop(explode('.', $original_file)))){
        		$this->error_msg = 'invalid_filetype_3';
				return FALSE;
        	}
		}

		if ($this->remove_spaces == 1){
            $this->file_name = preg_replace("/\s+/", "_", $this->file_name);
            $original_file = preg_replace("/\s+/", "_", $original_file);
        }

        if ( ! @copy($this->upload_path . $this->temp_prefix . $original_file, $this->upload_path . $this->file_name)){
			$this->error_msg = 'copy_error';
			return FALSE;
        }

		unlink ($this->upload_path . $this->temp_prefix.$original_file);
		return TRUE;
    }
    /* END */


    /** -------------------------------------
    /**  Image Resize
    /** -------------------------------------*/

    function resize($max_width = 0, $max_height = 0 ){
		if(eregi("\.png$",$this->new_name)){
			$img = ImageCreateFromPNG ($this->new_name);
		}

		if(eregi("\.(jpg|jpeg)$",$this->new_name)){
			$img = ImageCreateFromJPEG ($this->new_name);
		}

		if(eregi("\.gif$",$this->new_name)){
			$img = ImageCreateFromGif ($this->new_name);
		}

		$FullImage_width = imagesx ($img);
    	$FullImage_height = imagesy ($img);

		if(isset($max_width) && isset($max_height) && $max_width != 0 && $max_height != 0){
			$new_width = $max_width;
			$new_height = $max_height;
		}else if(isset($max_width) && $max_width != 0){
			$new_width = $max_width;
			$new_height = ((int)($new_width * $FullImage_height) / $FullImage_width);
		}else if(isset($max_height) && $max_height != 0){
			$new_height = $max_height;
			$new_width = ((int)($new_height * $FullImage_width) / $FullImage_height);
		}else{
			$new_height = $FullImage_height;
			$new_width = $FullImage_width;
		}
/*
    	$ratio =  ( $FullImage_width > $max_width ) ? (real)($max_width / $FullImage_width) : 1 ;
    	$new_width = ((int)($FullImage_width * $ratio));    //full size width
    	$new_height = ((int)($FullImage_height * $ratio));    //full size height

    	$ratio =  ( $new_height > $max_height ) ? (real)($max_height / $new_height) : 1 ;
    	$new_width = ((int)($new_width * $ratio));    //mid size width
    	$new_height = ((int)($new_height * $ratio));    //mid size height
*/

    	$this->new_created_image =  ImageCreateTrueColor ( $new_width , $new_height );

    	$this->preserveAlpha();

		ImageCopyResampled ( $this->new_created_image, $img, 0,0,0,0, $new_width, $new_height, $FullImage_width, $FullImage_height );


		if(eregi("\.(jpg|jpeg)$",$this->new_name)){
			$full = ImageJPEG( $this->new_created_image, $this->new_name,100);
		}

		if(eregi("\.png$",$this->new_name)){
			$full = ImagePNG( $this->new_created_image, $this->new_name);
		}

		if(eregi("\.gif$",$this->new_name)){
			$full = ImageGIF($this->new_created_image, $this->new_name);
		}
		ImageDestroy( $this->new_created_image );
		unset($max_width);
		unset($max_height);
	}
    /* END */

    /** -------------------------------------
    /**  preserveAlpha
    /** -------------------------------------*/
	protected function preserveAlpha ()	{
		if ($this->extension == 'png'){
			imagealphablending($this->new_created_image, false);

			$colorTransparent = imagecolorallocatealpha
			(
				$this->new_created_image,
				0,//$this->options['alphaMaskColor'][0],
				0,//$this->options['alphaMaskColor'][1],
				0,//$this->options['alphaMaskColor'][2],
				127//0
			);

			imagefill($this->new_created_image, 0, 0, $colorTransparent);
			imagesavealpha($this->new_created_image, true);

		}

		// preserve transparency in GIFs... this is usually pretty rough tho
		if ($this->extension == 'gif'){
			$colorTransparent = imagecolorallocate
			(
				$this->new_created_image,
				255,//$this->options['transparencyMaskColor'][0],
				255,//$this->options['transparencyMaskColor'][1],
				255 //$this->options['transparencyMaskColor'][2]
			);

			imagecolortransparent($this->new_created_image, $colorTransparent);
			imagetruecolortopalette($this->new_created_image, true, 256);
		}
	}
	/* END */


	/** -------------------------------------
    /**  Set Thumbnail Name
    /** -------------------------------------*/

	function set_thumbnail_name($thumbname){
		if(eregi("\.png$",$this->new_name)){
			$this->thumb_name = $this->upload_path . "/" . $thumbname . $this->thumb_sufix . ".png";
		}

		if(eregi("\.(jpg|jpeg)$",$this->new_name)){
			$this->thumb_name = $this->upload_path . "/" . $thumbname . $this->thumb_sufix . ".jpg";
		}

		if(eregi("\.gif$",$this->new_name)){
			$this->thumb_name = $this->upload_path . "/" . $thumbname . $this->thumb_sufix . ".gif";
		}
	}
	/* END */


	/** -------------------------------------
    /**  Create Thumbnail
    /** -------------------------------------*/

	function create_thumbnail(){
		if (!copy($this->new_name, $this->thumb_name)){
			$this->error_msg = 'failed to copy';
			return FALSE;
		}
	}
	/* END */


	/** -------------------------------------
    /**  Set Thumbnail Size
    /** -------------------------------------*/

	function set_thumbnail_size($max_width = 0, $max_height = 0 ){
		if(eregi("\.png$",$this->thumb_name)){
			$img = ImageCreateFromPNG ($this->thumb_name);
		}

		if(eregi("\.(jpg|jpeg)$",$this->thumb_name)){
			$img = ImageCreateFromJPEG ($this->thumb_name);
		}

		if(eregi("\.gif$",$this->thumb_name)){
			$img = ImageCreateFromGif ($this->thumb_name);
		}

    	$FullImage_width = imagesx ($img);
    	$FullImage_height = imagesy ($img);

		if(isset($max_width) && isset($max_height) && $max_width != 0 && $max_height != 0){
			$new_width = $max_width;
			$new_height = $max_height;
		}else if(isset($max_width) && $max_width != 0){
			$new_width = $max_width;
			$new_height = ((int)($new_width * $FullImage_height) / $FullImage_width);
		}else if(isset($max_height) && $max_height != 0){
			$new_height = $max_height;
			$new_width = ((int)($new_height * $FullImage_width) / $FullImage_height);
		}else{
			$new_height = $FullImage_height;
			$new_width = $FullImage_width;
		}
/*
    	$ratio =  ( $FullImage_width > $max_width ) ? (real)($max_width / $FullImage_width) : 1 ;
    	$new_width = ((int)($FullImage_width * $ratio));    //full size width
    	$new_height = ((int)($FullImage_height * $ratio));    //full size height

    	$ratio =  ( $new_height > $max_height ) ? (real)($max_height / $new_height) : 1 ;
    	$new_width = ((int)($new_width * $ratio));    //mid size width
    	$new_height = ((int)($new_height * $ratio));    //mid size height
*/

		$this->new_created_image =  ImageCreateTrueColor ( $new_width , $new_height );

    	$this->preserveAlpha();

    	ImageCopyResampled ( $this->new_created_image, $img, 0,0,0,0, $new_width, $new_height, $FullImage_width, $FullImage_height );

		if(eregi("\.(jpg|jpeg)$",$this->thumb_name)){
			$full = ImageJPEG( $this->new_created_image, $this->thumb_name,100);
		}

		if(eregi("\.png$",$this->thumb_name)){
			$full = ImagePNG( $this->new_created_image, $this->thumb_name);
		}

		if(eregi("\.gif$",$this->thumb_name)){
			$full = ImageGIF($this->new_created_image, $this->thumb_name);
		}
		ImageDestroy( $this->new_created_image );
		unset($max_width);
		unset($max_height);
	}
	/* END */

	function set_thumbnail($tname = '',$thumbwitdh=50,$thumbheight=50){
		$this->auto_thumb = TRUE;
		$this->thumb_name = $tname;
		$this->thumb_width = $thumbwitdh;
		$this->thumb_height = $thumbheight;
	}

	public function entryFileUpload($uploadPrefID){

		$upload_pref = new UploadPref($uploadPrefID);
	 	if ($this->set_upload_path($upload_pref->server_path) !== true){
	        return $this->show_error();
	 	}

	 	$this->auto_resize = true;
	 	$this->auto_resize_ratio = $upload_pref->thumb_ratio;
	 	$this->auto_width = $upload_pref->width;
	 	$this->auto_height = $upload_pref->height;
	 	$this->set_max_width($upload_pref->max_width);
	 	$this->set_max_height($upload_pref->max_height);
	 	$this->set_max_filesize($upload_pref->max_size);

	 	$this->set_allowed_types($upload_pref->tipo);

	 	if ( !$this->upload_file()){
	 		return $this->show_error();
	 	}else{
	 		return true;
	 	}

	}


}
?>