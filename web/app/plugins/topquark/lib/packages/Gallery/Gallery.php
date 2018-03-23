<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

define ('IMAGE_DIR_FULL',1);
define ('IMAGE_DIR_RELATIVE',2);

class Gallery extends Parameterized_Object
{
	function Gallery($gallery_id="",$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('GalleryID',$gallery_id);	

		$this->setIDParameter('GalleryID');
		$this->setNameParameter('GalleryName');
		
		//Hackage
		$packagename = preg_replace('/.*\/([^\/]*)$/','$1',dirname(dirname(__FILE__)."/.."));
		$Bootstrap = Bootstrap::getBootstrap();
		$this->Package = $Bootstrap->usePackage($packagename);
	}
	
	function getPrimaryThumb(){
		$GalleryImageContainer = new GalleryImageContainer();
		
		return $GalleryImageContainer->getPrimaryThumb($this->getParameter('GalleryID'));
	}
	
	function setPrimaryThumb($image_id = ""){
		$GalleryImageContainer = new GalleryImageContainer();
		
		if ($image_id == ""){
			return $GalleryImageContainer->setPrimaryThumb($this->getParameter('GalleryID'));
		}
		else{
			return $GalleryImageContainer->setPrimaryThumb($this->getParameter('GalleryID'),$image_id);		
		}
	}
	
	function getAllGalleryImages(){
		$GalleryImageContainer = new GalleryImageContainer();
		
		return $GalleryImageContainer->getAllGalleryImages($this->getParameter('GalleryID'));
	}
	
	function isPrivate(){
		switch (strtolower($this->getParameter('GalleryStatus'))){
		case "private":
			return true;
			break;
		default:
			return false;
		}
	}
        
        function getDirectoryName(){
                return $this->getParameter('GalleryDirectory');
            
            /*  Old Way
                $dir = $this->getParameter('GalleryName');
                return preg_replace("/[^a-zA-Z0-9_]/","",$dir);
            */
        }
        
        function setGalleryDirectory(){
            $SafeName = str_replace(' ','_',$this->getParameter('GalleryDefaultCredit'));
            $SafeName = preg_replace("/[^A-Za-z0-9_]/","",$SafeName);
            if ($this->Package->use_gallery_year){
                $BaseDirectory = DOC_BASE.GALLERY_IMAGE_DIR.$this->getParameter('GalleryYear')."/$SafeName/";
            }
            else{
                $BaseDirectory = DOC_BASE.GALLERY_IMAGE_DIR."$SafeName/";
            }
            $Roll = 1;
            while (file_exists($BaseDirectory."Roll_$Roll")){
                $Roll++;
            }
            if ($this->Package->use_gallery_year){
                $this->setParameter('GalleryDirectory',$this->getParameter('GalleryYear')."/$SafeName/"."Roll_$Roll/");
            }
            else{
                $this->setParameter('GalleryDirectory',"$SafeName/"."Roll_$Roll/");
            }
        }

        function addUploadedGalleryImage($uploaded_filename,$filename,$dimension_callbacks = array()){
		        $ImageLibrarian = new ImageLibrarian();
		        
		        if (is_array($this->Package->galleries_to_keep_original) and in_array($this->getParameter('GalleryName'),$this->Package->galleries_to_keep_original)){
		            $ImageLibrarian->setOriginalDimensionsCallback(create_function('',"return array('width' => 0, 'height' => 0, 'conditions' => RESIZE_KEEP_ASPECT_RATIO);"));
		        }
		
                $GalleryImageContainer = new GalleryImageContainer();
                $ImageLibrarian->makeDirectory($this->getDirectoryName());
                
                foreach ($ImageLibrarian->getTypes() as $type){
                    if (array_key_exists($type,$dimension_callbacks)){
                        $function = "set".$type."DimensionsCallback";
                        $ImageLibrarian->$function($dimension_callbacks[$type]);
                    }
                }
#                $results = $ImageLibrarian->checkInUploadedFile($uploaded_filename,$this->getDirectoryName()."/".$filename);
                $results = $ImageLibrarian->checkInUploadedFile($uploaded_filename,$this->getDirectoryName().$filename);
                if (PEAR::isError($results)){
                        return $results;
                }
                $GalleryImages = array();
                if (!isset($results[0]) and count($results)){
                        $results = array($results);
                }
                foreach ($results as $result){
                        $GalleryImage = $GalleryImageContainer->manufactureGalleryImage(new Parameterized_Object());
                        $GalleryImage->setParameter('GalleryID',$this->getParameter('GalleryID'));
                        $GalleryImage->setParameter('GalleryImage',$filename);
                        foreach ($result as $type => $parms){
                                $GalleryImage->setParameter('GalleryImage'.$type,$parms['name']);
                        }
                        
                        if (PEAR::isError($e = $GalleryImageContainer->addGalleryImage($GalleryImage))){        
                                return $e;
                        }
                        $GalleryImages[] = $GalleryImage;
                }
                if (count($GalleryImages) == 1){
                        return array_shift($GalleryImages);
                }
                else{
                        return $GalleryImages;
                }
                
        }
        
        function addDirectory($directory){
		        $ImageLibrarian = new ImageLibrarian();
                $GalleryImageContainer = new GalleryImageContainer();
                $ImageLibrarian->makeDirectory($this->getDirectoryName());
                
                // Okay, in order to make the process faster with the threaded execution,
                // we have to check in the images one file at a time.
                
                // So, step 1 is to get all of the image names.  
                $DontCheckInFiles = array("..",".");
                $dh = opendir($directory);
                $CheckInFiles = array();
                while (($file = readdir($dh)) !== false) {  
                    if (!in_array($file,$DontCheckInFiles) and !is_dir("$directory/$file") and $ImageLibrarian->isAcceptableImage("$directory/$file")){
                        $CheckInFiles[] = $file;
                    }
                }
                
                // Now, step 2, for all of those files, check them in one at a time
                $GalleryImages = array();
                
                // We'll set the index based on the number of image in the gallery currently
                $CurrentImages = $this->getAllGalleryImages();
                if (!is_array($CurrentImages) or !count($CurrentImages)){
                    $ImageIndex = 1;
                }
                else{
                    $ImageIndex = count($CurrentImages) + 1;
                }
                foreach ($CheckInFiles as $file){
                    $newLocation = $this->getGalleryDirectory(IMAGE_DIR_FULL,false)."$file";
                    copy ("$directory/$file",$newLocation);
                    if (PEAR::isError($result = $ImageLibrarian->checkInFile($newLocation))){
                            return $result;
                    }
        			$pathinfo = pathinfo("$directory/$file");
        			$regs = @getimagesize($_dir."/".$file);
        			$NewNames = $ImageLibrarian->getCandidateNames($pathinfo['basename'],array('width' => $regs[0], 'height' => $regs[1]));
                    $result['Original'] = array('name' => $NewNames['Original'], 'width' => $regs[0], 'height' => $regs[1]);
                    
                    $GalleryImage = $GalleryImageContainer->manufactureGalleryImage(new Parameterized_Object());
                    $GalleryImage->setParameter('GalleryID',$this->getParameter('GalleryID'));
                    foreach ($result as $type => $parms){
                            $GalleryImage->setParameter('GalleryImage'.$type,$parms['name']);
                    }
                    $GalleryImage->setParameter('ImageIndex',$ImageIndex);
                    
                    if (PEAR::isError($e = $GalleryImageContainer->addGalleryImage($GalleryImage))){        
                            return $e;
                    }
                    if ($ImageIndex == 1){
                        $this->setPrimaryThumb();
                    }
                    $GalleryImages[] = $GalleryImage;
                    $ImageIndex++;
                    set_time_limit(0);
                }
                if (count($GalleryImages) == 1){
                        return array_shift($GalleryImages);
                }
                else{
                        return $GalleryImages;
                }
        }
        
        function addImageFile($filename){
		$ImageLibrarian = new ImageLibrarian();
                $GalleryImageContainer = new GalleryImageContainer();
                $ImageLibrarian->makeDirectory($this->getDirectoryName());
                $result = $ImageLibrarian->checkInFile($filename);
                if (PEAR::isError($result)){
                        return $result;
                }
                $GalleryImage = $GalleryImageContainer->manufactureGalleryImage(new Parameterized_Object());
                $GalleryImage->setParameter('GalleryID',$this->getParameter('GalleryID'));
                foreach ($result as $type => $parms){
                        $GalleryImage->setParameter('GalleryImage'.$type,$parms['name']);
                }
                        
                if (PEAR::isError($e = $GalleryImageContainer->addGalleryImage($GalleryImage))){        
                        return $e;
                }
                return $GalleryImage;
        }
        
        function getGalleryDirectory($_relative = IMAGE_DIR_RELATIVE,$addSlash = false){
        	switch ($_relative){
        	case IMAGE_DIR_FULL:
        		$base_path = DOC_BASE;
        		break;
        	default: 
        		$base_path = RELATIVE_BASE_URL;
        		break;
        	}
                $ret = $base_path.GALLERY_IMAGE_DIR.$this->getDirectoryName();
                if ($addSlash){
                        return $ret."/";
                }
        	return $ret;
        }
        
}
?>