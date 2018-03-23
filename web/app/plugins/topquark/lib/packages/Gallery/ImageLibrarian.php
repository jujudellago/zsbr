<?php
	/* Load bootstrap code */
	if (!defined('OS_WINDOWS')){
		define("OS_WINDOWS", "win");
	}
	if (!defined('OS_LINUX')){
		define("OS_LINUX", "linux");
	}
	if (!defined('OS_SUNOS')){
		define("OS_SUNOS", "SunOS");
	}
	if (!defined('OS_OTHER')){
		define("OS_OTHER", "other");
	}
	define ('RESIZE_KEEP_ASPECT_RATIO',1);
	define ('RESIZE_ABSOLUTE',2);
	define ('RESIZE_DONT_ENLARGE',4);
	define ('RESIZE_FIT_TO_DIMENSIONS',8);
	define ('RESIZE_CROP',16);  
    define ('RESIZE_CROP_NO_RESIZE', 32);
	define ('RESIZE_CONDITIONS_DEFAULT', RESIZE_KEEP_ASPECT_RATIO | RESIZE_DONT_ENLARGE);
	
	if (!defined('ORIGINAL_WIDTH')){
		define('ORIGINAL_WIDTH',0);
	}
	if (!defined('ORIGINAL_HEIGHT')){
		define('ORIGINAL_HEIGHT',0);
	}
	
	if (!defined('THUMB_WIDTH')){
		define('THUMB_WIDTH',150);
	}
	if (!defined('THUMB_HEIGHT')){
		define('THUMB_HEIGHT',150);
	}
	if (!defined('RESIZED_WIDTH')){
		define('RESIZED_WIDTH',400);
	}
	if (!defined('RESIZED_HEIGHT')){
		define('RESIZED_HEIGHT',400);
	}
	if (!defined('FRONTPAGE_WIDTH')){
		define('FRONTPAGE_WIDTH',200);
	}
	if (!defined('FRONTPAGE_HEIGHT')){
		define('FRONTPAGE_HEIGHT',200);
	}
	if (!defined('RESIZE_CONDITIONS_ORIGINAL')){
	    define ('RESIZE_CONDITIONS_ORIGINAL',RESIZE_CONDITIONS_DEFAULT);
	}
	if (!defined('RESIZE_CONDITIONS_THUMB')){
	    define ('RESIZE_CONDITIONS_THUMB',RESIZE_CROP);
	}
	if (!defined('RESIZE_CONDITIONS_RESIZED')){
	    define ('RESIZE_CONDITIONS_RESIZED',RESIZE_CONDITIONS_DEFAULT);
	}
	
	
	function getOS () {
		if(substr(PHP_OS, 0, 3) == 'WIN') {
			return OS_WINDOWS;
		} else if ( stristr(PHP_OS, "linux")) {
			return OS_LINUX;
		} else if ( stristr(PHP_OS, "SunOS")) {
			return OS_SUNOS;
		} else {
			return OS_OTHER;
		}
	}
	
	if (getOS() == OS_WINDOWS) {
		include("platform/fs_win32.php");
	} else {
		include("platform/fs_unix.php");
	}
	
	class ImageLibrarian{
		var $BaseDirectory;
		var $types;
                var $DefaultBackgroundColour;
				var $ResizedDimensionsCallback;
				var $OriginalDimensionsCallback;
				var $ThumbDimensionsCallback;
	
		function ImageLibrarian(){
			$this->setTypes(array("Original","Resized","Thumb"));
			$this->setBaseDirectory(DOC_BASE.GALLERY_IMAGE_DIR);
		}
		
                function setDefaultBackgroundColour($hex_string){
                        $this->DefaultBackgroundColour = $hex_string;
                }
                
                function getDefaultBackgroundColour(){
                        if ($this->DefaultBackgroundColour == "") return "000000";
                        else return $this->DefaultBackgroundColour;
                }
		
		function setBaseDirectory($_dir){
			if (!preg_match("/[\/\\\]$/",$_dir)){
				$_dir.= "/";
			}
			$this->BaseDirectory = $_dir;
		}
		
		function getBaseDirectory(){
			return $this->BaseDirectory;
		}
		
		function isAcceptableImage($_File){
		    if (file_exists($_File)){
    			$size = getimagesize($_File);
    			if (!$size){
    				return false;
    			}else{
    				// $size[2] is the image type. 1=Gif, 2=JPG, 3=PNG
    				if (in_array($size[2],array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG)))
    					return true;
    				else
    					return false;
    			}
    		}
    		else{
    		    // For checking just a filename (when checking in a Zip file)
    		    if (preg_match("/\.(jpg|jpeg|gif|png)$/",strtolower($_File))){
    		        return true;
    		    }
    		    else{
    		        return false;
    		    }
    		}
		}
		
		function makeDirectory($_dir){
			if (!file_exists($this->getBaseDirectory().$_dir)){
			    
			    $sub_dirs = explode('/',$_dir);
			    $current_directory = $this->getBaseDirectory();
			    foreach ($sub_dirs as $sub_dir){
			        $current_directory.= "/$sub_dir";
			        if ($sub_dir != "" and !file_exists($current_directory)){
        				if (!mkdir($current_directory,0777)){
        					return PEAR::raiseError("Unable to create directory ".$current_directory);
        				}
        			}
			    }
			    /*
				if (!chmod($this->getBaseDirectory().$_dir,0777)){
					return PEAR::raiseError("Unable to chmod on ".$this->getBaseDirectory().$_dir);
				}
				*/
				return true;
			}
			else{
				return true;
			}
		}
		
		function deleteDirectory($_dir, $DeleteFiles = false){
			if ($DeleteFiles){
				$DontDeleteFiles = array("..",".");
			
				if ($dh = opendir($this->getBaseDirectory().$_dir)) {
				   while (($file = readdir($dh)) !== false) {
				   		if (!in_array($file,$DontDeleteFiles)){
				   			if (is_dir($file)){
				   				if (PEAR::isError($result = $this->deleteDirectory($file,true))){
				   					return $result;
				   				}
				   			}
				   			else{
			   					if (!unlink($file)){
			   						return PEAR::raiseError("Unable to delete the file $file");
			   					}
				   			}
				   		}
				   }
				   closedir($dh);
				}
			}
			if (!rmdir($this->getBaseDirectory().$_dir)){
				return PEAR::raiseError("Unable to delete directory ".$this->getBaseDirectory().$_dir);
			}
			else{
				return true;
			}
		}
		
		/*****************************************************
		*  Function:	resizeImage($src,$dest,$height,$width,$resize_conditions)
		*  Purpose:		Resizes the image
		*				if either $height or $width are empty, then it
		*				resizes to the other.  If both are present, it resizes
		*				to the limiting dimension
		*				(e.g. a 20x40 image resized to height=100, width=100 would resize a 50x100 image)
		*  Returns:		true upon success, false upon no action (i.e. resize not necessary), PEAR::raiseError() otherwise
		*****************************************************/
		function resizeImage($src,$dest,$width=0,$height=0,$resize_conditions = RESIZE_CONDITIONS_DEFAULT,$jpeg_quality=90){	
			$regs = getimagesize($src);
			$current_width  = $regs[0];
			$current_height = $regs[1];
			switch($regs[2]){
			case IMAGETYPE_GIF: 
				$image_type = "GIF";
				break;
			case IMAGETYPE_JPEG:
				$image_type = "JPG";
				break;
			case IMAGETYPE_PNG:
				$image_type = "PNG";
				break;
			default:
				return PEAR::raiseError("The file $src is of a type that cannot be resized on this system");
				break;
			}
			if (!function_exists('gd_info')){
				return PEAR::raiseError("GD Library is not currently loaded, therefore, image resizing is not possible");
			}
			
			$newDimensions = $this->calculateNewDimensions($current_width,$current_height,$width,$height,$resize_conditions);
                        $resized_width = $newDimensions['width'];
                        $resized_height = $newDimensions['height'];
                        if (isset($newDimensions['fit_to_width']) and $newDimensions['fit_to_width'] != ""){
        			$image_width = $newDimensions['fit_to_width'];
        			$image_height = $newDimensions['fit_to_height'];
                                $dst_x = (int)($image_width - $resized_width)/2;
                                $dst_y = (int)($image_height - $resized_height)/2;
                                $src_x = 0;
                                $src_y = 0;
                        }
                        else{
        			$image_width = $resized_width;
        			$image_height = $resized_height;
                                $dst_x = 0;
                                $dst_y = 0;
                                $src_x = 0;
                                $src_y = 0;
                        }                        
			if ($resized_width == $current_width and $resized_height == $current_height){
				return false;
			}
			else{
                                if (isset($newDimensions['crop']) and $newDimensions['crop'] === true){
                                        $current_width = $newDimensions['crop_width'];
                                        $current_height = $newDimensions['crop_height'];
                                }
				$resized = &imagecreatetruecolor($image_width, $image_height);
                                imagefill($resized,0,0,hexdec($this->getDefaultBackgroundColour()));
				switch ($image_type){
				case "GIF":
					// load/create images
					$source = imagecreatefromgif($src);
					$resized = imagecreatetruecolor($image_width,$image_height);
					imagealphablending($resized, false);

					// get and reallocate transparency-color
					$colorTransparent = imagecolortransparent($source);
					if($colorTransparent >= 0) {
					  	$transcol = imagecolorsforindex($source, $colorTransparent);
					  	$colorTransparent = imagecolorallocatealpha($resized, $transcol['red'], $transcol['green'], $transcol['blue'], 127);
						imagepalettecopy($resized,$source);
						imagefill($resized,0,0,$colorTransparent);
					}

					// resample
					imagecopyresampled($resized, $source, $dst_x, $dst_y, $src_x, $src_y, $resized_width, $resized_height, $current_width, $current_height);		

					// restore transparency
					if($colorTransparent >= 0) {
					  imagecolortransparent($resized, $colorTransparent);
					  for($y=0; $y<$image_height; ++$y)
					    for($x=0; $x<$image_width; ++$x)
					      if(((imagecolorat($resized, $x, $y)>>24) & 0x7F) >= 100) imagesetpixel($resized, $x, $y, $colorTransparent);
					}
					
					// save GIF
					imagetruecolortopalette($resized, true, 255);
					imagesavealpha($resized, false);
					imagegif($resized, $dest);
					imagedestroy($resized);


					/*
					$source = imagecreatefromgif($src);
					$colorTransparent = imagecolortransparent($source);
					$resized = imagecreate($image_width,$image_height);
					imagepalettecopy($resized,$source);
					imagefill($resized,0,0,$colorTransparent);
					imagecolortransparent($resized, $colorTransparent);
					imagecopyresampled($resized, $source, $dst_x, $dst_y, $src_x, $src_y, $resized_width, $resized_height, $current_width, $current_height);		
					imagegif($resized,$dest);
					*/
					break;
				case "JPG":
					$source = imagecreatefromjpeg($src);
					imagecopyresampled($resized, $source, $dst_x, $dst_y, $src_x, $src_y, $resized_width, $resized_height, $current_width, $current_height);		
					imagejpeg($resized,$dest,$jpeg_quality);
					break;
				case "PNG":
					$source = imagecreatefrompng($src);
					imagealphablending($resized, false);
			        $color = imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
			        imagefill($resized, 0, 0, $color);
			        imagesavealpha($resized, true);
					imagecopyresampled($resized, $source, $dst_x, $dst_y, $src_x, $src_y, $resized_width, $resized_height, $current_width, $current_height);		
					imagepng($resized,$dest);
					break;
				default:
					return PEAR::raiseError("Unable to resize image $src");
				}
				return array("width" => $resized_width, "height" => $resized_height);
			}
		}
		
		function calculateNewDimensions($current_width,$current_height,$width,$height,$resize_conditions = RESIZE_CONDITIONS_DEFAULT){
			$dst_x = 0;
			// If the image is sized such that bigger_dim/smaller_dim > critical_ratio
			// Then instead of resizing it, we crop it.  
			/* Taken out cause it causes headaches
			$bigger_dim = $current_width > $current_height ? "width" : "height";
			$smaller_dim = $current_width <= $current_height ? "width" : "height";
			$critical_ratio = 5;
			if (${"current_".$bigger_dim}/${"current_".$smaller_dim} > $critical_ratio){
				if (${$smaller_dim} <= $width && ${$smaller_dim} != 0){
					// We Want to Crop!!
					$dst_x = (int)(${"current_".$bigger_dim}*0.2);
					${"current_".$bigger_dim} = ${"current_".$smaller_dim};
				}
			}
			*/
		
			if ($height == "") $height = "0";
			if ($width == "") $width = "0";
			$height = (int)$height;
			$width  = (int)$width;
		
			if ($height == 0) $height = $current_height;
			if ($width == 0) $width = $current_width;
			
			if (!is_numeric($height) or !is_numeric($width)){
				return PEAR::raiseError("Invalid height ($height) or width ($width)");
			}
			$height = (int)$height;
			$width  = (int)$width;
			
			if ($height == "" or $height == "0"){
				$resized_width = $width;
				$resized_height = (int)($width/$current_width*$current_height);
			}
			elseif ($width == "" or $width == "0"){
				$resized_height = $height;
				$resized_width = (int)($height/$current_height*$current_width);
			}
			else{
				if ($resize_conditions & RESIZE_ABSOLUTE){
					$resized_height = $height;
					$resized_width = $width;
				}
				else{
                                        if ($resize_conditions & RESIZE_CROP){
                                                $resized_width = $width;
                                                $resized_height = $height;
        					if ((float)($current_width/$width) > (float)($current_height/$height)){
        						// Limiting Dimension is Width
                                                        $crop_height = $current_height;
                                                        $crop_width  = (int)($width*$current_height/$height);
        					}
        					else{
                                                        $crop_height = (int)($height*$current_width/$width);
                                                        $crop_width  = $current_width;
        					}
                                        }
                                        else{
        					if ((float)($current_width/$width) > (float)($current_height/$height)){
        						// Limiting Dimension is Width
        						$resized_width = $width;
        						$resized_height = (int)($width/$current_width*$current_height);
        					}
        					else{
        						$resized_height = $height;
        						$resized_width = (int)($height/$current_height*$current_width);
        					}
                                        }
				}
			}
			
			// Don't want to resize an image to a '0' dimension
			if ($resized_height == 0) $resized_height = 1;
			if ($resized_width == 0) $resized_width = 1;
			
			// If we don't want to enlarge the image at all.  
			if ($resize_conditions & RESIZE_DONT_ENLARGE){
				if ($resized_height > $current_height or $resized_width > $current_width){
					$resized_height = $current_height;
					$resized_width = $current_width;
				}
			}
                        $ret = array("width" => $resized_width, "height" => $resized_height);
                        if ($resize_conditions & RESIZE_FIT_TO_DIMENSIONS){
                                $ret["fit_to_width"] = $width;
                                $ret["fit_to_height"] = $height;
                        }
                        
                        if ($resize_conditions & RESIZE_CROP){
                                $ret['crop'] = true;
                                $ret["crop_width"] = $crop_width;
                                $ret["crop_height"] = $crop_height;
                        }

                        return $ret;
                        
			
		}
		
		function checkInFile($file){
			if (!$this->isAcceptableImage($file)){
				return PEAR::raiseError('"'.basename($file)."\" is not an acceptable image and can't be checked in.  Acceptable images are GIF and JPEG.");
			}
			$pathinfo = pathinfo($file);
			$regs = getimagesize($file);
			$NewNames = $this->getCandidateNames($pathinfo['basename'],array('width' => $regs[0], 'height' => $regs[1]));
			$NewDimensions = $this->getCandidateDimensions();
			$return = array();
                        $realpath = realpath($pathinfo['dirname']);
			foreach ($NewNames as $Type => $NewName){
                                if ($Type != 'Original' or ORIGINAL_HEIGHT != 0 or ORIGINAL_WIDTH != 0){ // 
        				$newFile = "$realpath/$NewName";
        			        $CalculatedDimensions = $this->calculateNewDimensions($regs[0],$regs[1],$NewDimensions[$Type]['width'],$NewDimensions[$Type]['height'],$NewDimensions[$Type]['conditions']);
        				if ($CalculatedDimensions['width'] == $regs[0] and $CalculatedDimensions['height'] == $regs[1]){
        				        // Don't resize, just copy the file
                                                /*
        					if (realpath($file) != $newFile){
        						if (!copy($file,$newFile)){
        							return PEAR::raiseError("Unable to create $Type file at $newFile");
        						}
        					}
                                                */
        					$return[$Type] = array('name' => $NewNames['Original'], 'width' => $regs[0], 'height' => $regs[1]);
        				}
        				else{
        					$resized_results = $this->resizeImage($file,$newFile,$NewDimensions[$Type]['width'],$NewDimensions[$Type]['height'],$NewDimensions[$Type]['conditions']);
        					if (PEAR::isError($resized_results)){
        						return $resized_results;
        					}
        					if ($resized_results === false){
        					//  File wasn't resized.  Just indicate that Type is original file
        						$return[$Type] = array('name' => $pathinfo['basename'], 'width' => $regs[0], 'height' => $regs[1]);
        					}
        					else{
        						$return[$Type] = array('name' => $NewName, 'width' => $resized_results['width'], 'height' => $resized_results['width']);
        					}
        					$stat = stat($newFile);
        					if (function_exists('posix_getuid') and $stat['uid'] == posix_getuid()){
				                chmod($newFile,0666);
        					}
        				}
                                }
                                else{
        			        $return[$Type] = array('name' => $NewNames['Original'], 'width' => $regs[0], 'height' => $regs[1]);
                                }
			}
			return $return;
		}
		
		function checkInUploadedFile($uploaded_file,$file_name){
                        $pathinfo = pathinfo($file_name);
                        $valid_zip_files = array('zip');
                        if (in_array(strtolower($pathinfo['extension']),$valid_zip_files)){
                                return $this->checkInUploadedZipFile($uploaded_file,$file_name);
                        }
			if (!is_uploaded_file($uploaded_file)){
				return PEAR::raiseError("$uploaded_file is not an uploaded file");
			}
			$newLocation = $this->getBaseDirectory().$file_name;
			if (!move_uploaded_file($uploaded_file,$newLocation)){
				return PEAR::raiseError("Unable to move the uploaded file $uploaded_file");
			}
			chmod($newLocation,0666);
			return $this->checkInFile($newLocation);
		}
		
		function checkInUploadedZipFile($uploaded_file,$file_name){
			if (!is_uploaded_file($uploaded_file)){
				return PEAR::raiseError("$uploaded_file is not an uploaded file");
			}
                        $pathinfo = pathinfo($file_name);
                        $zipDirectory = $this->getBaseDirectory().$pathinfo['dirname'];
			$newLocation = $this->getBaseDirectory().$file_name;
			if (!move_uploaded_file($uploaded_file,$newLocation)){
                                
				return PEAR::raiseError("Unable to move the uploaded file $uploaded_file");
			}
                        $BeforeFiles = $this->getFileTimesInDirectory($zipDirectory);
                        // Trying a new way to unzip
                	    require_once('dUnzip2.inc.php');
                	    $ZipFile = new dUnzip2($newLocation);
                	    $List = $ZipFile->getList();
                	    foreach ($List as $FileName=>$trash){
                	        // Only check in acceptable images in the root directory of the zip file 
                	        // (in other words, it doesn't recurse)
                	        if ($FileName == basename($FileName) and $this->isAcceptableImage($FileName)){
                	            $ZipFile->unzip($FileName,"$zipDirectory/$FileName");
                	        }
                	    }
                        $AfterFiles = $this->getFileTimesInDirectory($zipDirectory);
                        $NewFiles = array_diff_assoc($AfterFiles,$BeforeFiles);
                        $CheckedInFiles = array();
                        
                        foreach ($NewFiles as $File => $ModTime){
		                set_time_limit(0);
                                if (!PEAR::isError($r = $this->checkInFile("$zipDirectory/$File"))){
                                        $CheckedInFiles[] = $r;
			                chmod("$zipDirectory/$File",0666);
                                }
                                else{           
                                        unlink("$zipDirectory/$File");
                                        return $r;
                                }
                        }
                        unlink($newLocation);
                        
			return $CheckedInFiles;
		}
                
                function getFileTimesInDirectory($dir){
                        $IgnoreFiles = array("..",".");
                
                        $files = array();
                        if ($dh = opendir($dir)) {
                           while (($file = readdir($dh)) !== false) {
                                        if (!in_array($file,$IgnoreFiles)){
                                                $files[$file] = filemtime("$dir/$file");
                                        }
                           }
                           closedir($dh);
                        }
                        return $files;
                }
		
		function checkInDirectory($_dir,$gallery_dir){
			$DontCheckInFiles = array("..",".");
                        $return = array();
		
			if ($dh = opendir($_dir)) {
			   while (($file = readdir($dh)) !== false) {
                                
			   		if (!in_array($file,$DontCheckInFiles) and !is_dir("$_dir/$file")){
                                                if ($this->isAcceptableImage($_dir."/".$file)){
			                                $newLocation = $this->getBaseDirectory()."$gallery_dir/$file";
                                                        copy ($_dir."/".$file,$newLocation);
                                                        if (PEAR::isError($result = $this->checkInFile($newLocation))){
                                                                return $result;
                                                        }
                                			$pathinfo = pathinfo($_dir."/".$file);
                                			$regs = getimagesize($_dir."/".$file);
                                			$NewNames = $this->getCandidateNames($pathinfo['basename'],array('width' => $regs[0], 'height' => $regs[1]));
                                                        $result['Original'] = array('name' => $NewNames['Original'], 'width' => $regs[0], 'height' => $regs[1]);
                                                        $return[] = $result;
                                                }
                                                set_time_limit(0);
			   		}
			   }
			   closedir($dh);
			}
                        return $return;
		}
		
		function getCandidateNames($basename,$parms = array()){
		//  if the file were /www/html/blah/index.html, basename would be index.html
			$types = $this->getTypes();
			$CandidateNames = array();
			
			foreach ($types as $type){
                                $functionname = "get".$type."Name";
				$CandidateNames[$type] = $this->$functionname($basename,$parms);
			}
                        return $CandidateNames;
		}
		
		function getCandidateDimensions(){
			$types = $this->getTypes();
			$CandidateDimensions = array();
			
			foreach ($types as $type){
                                $functionname = "get".$type."Dimensions";
				$CandidateDimensions[$type] = $this->$functionname();
			}
                        return $CandidateDimensions;
		}
		
		function getOriginalName($basename,$parms = array()){
			return $basename;
		}
		
		function getThumbName($basename,$parms = array()){
			$pathinfo = pathinfo($basename);
			$newName = str_replace(".".$pathinfo['extension'],"",$basename);
			$newName.= "_thumb";
			if ($pathinfo['extension'] != ""){
				$newName.= ".".$pathinfo['extension'];
			}
			return $newName;
		}
		
		function getResizedName($basename,$parms = array()){
                        if (!isset($parms['newDimensions']) or $parms['newDimensions'] == ""){
			        $Dimensions = $this->getResizedDimensions();
                        }
                        else{
			        $Dimensions = $parms['newDimensions'];
                        }
			$newDimensions = $this->calculateNewDimensions($parms['width'],$parms['height'],$Dimensions['width'],$Dimensions['height'],$Dimensions['conditions']);
			$pathinfo = pathinfo($basename);
			$newName = str_replace(".".$pathinfo['extension'],"",$basename);
			$newName.= "_".$newDimensions['width']."x".$newDimensions['height'].".".$pathinfo['extension'];
			return $newName;
		}
		
		function getFrontPageName($basename,$parms = array()){
			$Dimensions = $this->getFrontPageDimensions();
			$newDimensions = $this->calculateNewDimensions($parms['width'],$parms['height'],$Dimensions['width'],$Dimensions['height'],$Dimensions['conditions']);
			$pathinfo = pathinfo($basename);
			$newName = str_replace(".".$pathinfo['extension'],"",$basename);
			$newName.= "_".$newDimensions['fit_to_width']."x".$newDimensions['fit_to_height'].".".$pathinfo['extension'];
			return $newName;
		}
		
		function getOriginalDimensions(){
		    if ($this->getOriginalDimensionsCallback() != ""){
		        $function = $this->getOriginalDimensionsCallback();
		        return $function();
		    }
		    else{
			    return array("width" => ORIGINAL_WIDTH, "height" => ORIGINAL_HEIGHT, "conditions" => RESIZE_CONDITIONS_ORIGINAL);
			}
		}
				
		function getThumbDimensions(){
		    if ($this->getThumbDimensionsCallback() != ""){
		        $function = $this->getThumbDimensionsCallback();
		        return $function();
		    }
		    else{
			    return array("width" => THUMB_WIDTH, "height" => THUMB_HEIGHT, "conditions" => RESIZE_CONDITIONS_THUMB);
			}
		}
				
		function getResizedDimensions(){
		    if ($this->getResizedDimensionsCallback() != ""){
		        $function = $this->getResizedDimensionsCallback();
		        return $function();
		    }
		    else{
			    return array("width" => RESIZED_WIDTH, "height" => RESIZED_HEIGHT, "conditions" => RESIZE_CONDITIONS_RESIZED);
			}
		}
				
		function getFrontPageDimensions(){
			return array("width" => FRONTPAGE_WIDTH, "height" => FRONTPAGE_HEIGHT, "conditions" => RESIZE_KEEP_ASPECT_RATIO | RESIZE_FIT_TO_DIMENSIONS);
		}
		
		function setThumbDimensionsCallback($function){
		    $this->ThumbDimensionsCallback = $function;
		}
				
		function setResizedDimensionsCallback($function){
		    $this->ResizedDimensionsCallback = $function;
		}
				
		function setOriginalDimensionsCallback($function){
		    $this->OriginalDimensionsCallback = $function;
		}
				
		function getThumbDimensionsCallback(){
		    return $this->ThumbDimensionsCallback;
		}
				
		function getResizedDimensionsCallback(){
		    return $this->ResizedDimensionsCallback;
		}
				
		function getOriginalDimensionsCallback(){
		    return $this->OriginalDimensionsCallback;
		}
				
		function getTypes(){
			if (!is_array($this->types) or count($this->types) == 0){
				return array();
			}
			else{
				return $this->types;
			}
		}
		
		function setTypes($type_array){
			if (!is_array($type_array)){
				$this->types = array($type_array);
			}
			else{
				$this->types = $type_array;
			}
		}
	
                function rotateImage($file,$degrees){
			$regs = getimagesize($file);
			$current_width  = $regs[0];
			$current_height = $regs[1];
			switch($regs[2]){
			case 1: 
				$image_type = "GIF";
				break;
			case 2:
				$image_type = "JPG";
				break;
			default:
				return PEAR::raiseError("The file $file is of a type that cannot be rotated on this system");
				break;
			}
			
                        switch ($image_type){
                        case "GIF":
                                $source = imagecreatefromgif($file);
                                $colorTransparent = imagecolortransparent($source);
                                $rotated = imagerotate($source,$degrees,$colorTransparent);
                                imagegif($rotated,$file);
                                break;
                        case "JPG":
                                $source = imagecreatefromjpeg($file);
                                $rotated = imagerotate($source,$degrees,0);
                                imagejpeg($rotated,$file,80);
                                break;
                        default:
                                return PEAR::raiseError("Unable to resize image $file");
                        }
                        return true;
                }

		function imagecopyresampled($dest,$src,$src_x,$src_y,$dest_w,$dest_h,$src_w,$src_h){
			// Note, $dest_x & $dest_y will always be 0, that's why they're not included in the above
			$dest_x = $dest_y = 0;
			$regs = getimagesize($src);
			switch($regs[2]){
			case IMAGETYPE_GIF: 
				$image_type = "GIF";
				break;
			case IMAGETYPE_JPEG:
				$image_type = "JPG";
				break;
			case IMAGETYPE_PNG:
				$image_type = "PNG";
				break;
			default:
				return PEAR::raiseError("The file $src is of a type that cannot be resized on this system");
				break;
			}
			if (!function_exists('gd_info')){
				return PEAR::raiseError("GD Library is not currently loaded, therefore, image resizing is not possible");
			}
			$resized = &imagecreatetruecolor($dest_w, $dest_h);
			//imagefill($resized,0,0,hexdec($this->getDefaultBackgroundColour()));
			
			switch ($image_type){
			case "GIF":
				// load/create images
				$source = imagecreatefromgif($src);
				imagealphablending($resized, false);

				// get and reallocate transparency-color
				$colorTransparent = imagecolortransparent($source);
				if($colorTransparent >= 0) {
					$transcol = imagecolorsforindex($source, $colorTransparent);
					$colorTransparent = imagecolorallocatealpha($resized, $transcol['red'], $transcol['green'], $transcol['blue'], 127);
					imagepalettecopy($resized,$source);
					imagefill($resized,0,0,$colorTransparent);
				}

				// resample
				imagecopyresampled($resized, $source, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);		

				// restore transparency
				if($colorTransparent >= 0) {
					imagecolortransparent($resized, $colorTransparent);
					for($y=0; $y<$image_height; ++$y)
						for($x=0; $x<$image_width; ++$x)
							if(((imagecolorat($resized, $x, $y)>>24) & 0x7F) >= 100) imagesetpixel($resized, $x, $y, $colorTransparent);
				}

				// save GIF
				imagetruecolortopalette($resized, true, 255);
				imagesavealpha($resized, false);
				imagegif($resized, $dest);
				imagedestroy($resized);
				break;
			case "JPG":
				$source = imagecreatefromjpeg($src);
				imagecopyresampled($resized, $source, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);		
				imagejpeg($resized,$dest,90);
				break;
			case "PNG":
				$source = imagecreatefrompng($src);
				imagealphablending($resized, false);
				$color = imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
				imagefill($resized, 0, 0, $color);
				imagesavealpha($resized, true);
				imagecopyresampled($resized, $source, $dest_x, $dest_y, $src_x, $src_y, $dest_w, $dest_h, $src_w, $src_h);		
				imagepng($resized,$dest);
				break;
			default:
				return PEAR::raiseError("Unable to resize image $src");
			}
			return true;
		}
}
	
	

?>