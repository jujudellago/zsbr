<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

class GalleryImage extends Parameterized_Object
{
	
	var $Gallery; 
	function GalleryImage($gallery_id,$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('GalleryID',$gallery_id);	

		$this->setIDParameter('ImageID');
		$this->setNameParameter('GalleryImageOriginal');
	}
        
        function rotateImage($degrees){
                $GalleryContainer = new GalleryContainer();
                $ImageLibrarian = new ImageLibrarian();
                $Gallery = $GalleryContainer->getGallery($this->getParameter('GalleryID'));
                if (!$Gallery){
                        return PEAR::raiseError("Couldn't find Gallery");
                }
                
                if (!copy($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$this->getParameter('GalleryImageOriginal'),TEMP_DIR.$this->getParameter('GalleryImageOriginal'))){
                        return PEAR::raiseError("Couldn't make a copy of the file");
                }
                
                // Delete the old (unrotated) files
                $types = $ImageLibrarian->getTypes();
                foreach ($types as $type){
                        if (file_exists($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$this->getParameter('GalleryImage'.$type))){
                                unlink($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$this->getParameter('GalleryImage'.$type));
                        }
                }
                
                $ImageLibrarian->rotateImage(TEMP_DIR.$this->getParameter('GalleryImageOriginal'),$degrees);
                $path_parts = pathinfo($this->getParameter('GalleryImageOriginal'));
                $path_parts['basename'] = substr($path_parts['basename'], 0, -(strlen($path_parts['extension']) + ($path_parts['extension'] == '' ? 0 : 1)));
                $pattern = "/^(.*)_r(\d{1,3})$/";
                if (preg_match($pattern,$path_parts['basename'],$matches)){
                        $rotation = ($matches[2] + $degrees) % 360;
                        $newOriginalName = $matches[1]."_r$rotation";
                }
                else{
                        $newOriginalName = $path_parts['basename']."_r$degrees";
                }
                if ($path_parts['extension'] != ""){
                        $newOriginalName.= ".".$path_parts['extension'];
                }
                
                                
                copy(TEMP_DIR.$this->getParameter('GalleryImageOriginal'),$Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$newOriginalName);
                unlink(TEMP_DIR.$this->getParameter('GalleryImageOriginal'));
                $result = $ImageLibrarian->checkInFile($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$newOriginalName);
                foreach ($result as $type => $parms){
                        $this->setParameter('GalleryImage'.$type,$parms['name']);
                }
                $GalleryImageContainer = new GalleryImageContainer();
                $GalleryImageContainer->updateGalleryImage($this);
                
                return true;
        }
        
        function getGallery(){
                $GalleryContainer = new GalleryContainer();
                $Gallery = $GalleryContainer->getGallery($this->getParameter('GalleryID'));
                return $Gallery;
        }
        function getGalleryDirectory($_relative = IMAGE_DIR_RELATIVE,$addSlash = false){
            if (!$this->Gallery){
                $this->Gallery = $this->getGallery();
            }
            return $this->Gallery->getGalleryDirectory($_relative,$addSlash);
        }
        
        function reCheckInImage(){
            // For now, this is a stub that basically just rechecks in the image to
            // give it the dimensions defined in the SIZE constants
            $File = $this->getGalleryDirectory(IMAGE_DIR_FULL).$this->getParameter('GalleryImageOriginal');
            $ImageLibrarian = new ImageLibrarian();
	        if (is_array($this->Gallery->Package->galleries_to_keep_original) and in_array($this->getParameter('GalleryName'),$this->Gallery->Package->galleries_to_keep_original)){
	            $ImageLibrarian->setOriginalDimensionsCallback(create_function('',"return array('width' => 0, 'height' => 0, 'conditions' => RESIZE_KEEP_ASPECT_RATIO);"));
	        }
	        if ($this->Gallery->getParameter('GalleryName') == 'General Artist Images'){
	            $ImageLibrarian->setThumbDimensionsCallback(create_function('','return array("width" => THUMB_WIDTH, "height" => THUMB_HEIGHT, "conditions" => '.RESIZE_CONDITIONS_DEFAULT.');'));
	        }
			if (function_exists('apply_filters')){
				$dimension_callbacks = apply_filters('set_gallery_dimension_callbacks',array(),$this->Gallery->getParameter('GalleryName'));
			}
            foreach ($ImageLibrarian->getTypes() as $type){
                if (array_key_exists($type,$dimension_callbacks)){
                    $function = "set".$type."DimensionsCallback";
                    $ImageLibrarian->$function($dimension_callbacks[$type]);
                }
            }
            $result = $ImageLibrarian->checkInFile($File);
            if (PEAR::isError($result)){
                echo $result->getMessage();
            }
            else{
                foreach ($ImageLibrarian->getTypes() as $Type){
                    if ($result[$Type]['name'] != $this->getParameter('GalleryImage'.$Type)){
                        if ($this->getParameter('GalleryImage'.$Type) != $this->getParameter('GalleryImageOriginal') and file_exists($this->getGalleryDirectory(IMAGE_DIR_FULL).$this->getParameter('GalleryImage'.$Type))){
                            unlink($this->getGalleryDirectory(IMAGE_DIR_FULL).$this->getParameter('GalleryImage'.$Type));
                        }
                        $this->setParameter('GalleryImage'.$Type,$result[$Type]['name']);
                        $GalleryImageContainer = new GalleryImageContainer();
                        $GalleryImageContainer->updateGalleryImage($this);
                    }
                }
            }
        }
}
?>