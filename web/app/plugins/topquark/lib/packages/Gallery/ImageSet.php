<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

class ImageSet extends Parameterized_Object
{
	function ImageSet($ImageSetID="",$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('ImageSetID',$ImageSetID);	

		$this->setIDParameter('ImageSetID');
		$this->setNameParameter('ImageSetName');

	}
	
	function getPrimaryThumb(){
		$ImageSetImageContainer = new ImageSetImageContainer();
		
		return $ImageSetImageContainer->getPrimaryThumb($this->getParameter('ImageSetID'));
	}
	
	function setPrimaryThumb($image_id = ""){
		$ImageSetImageContainer = new ImageSetImageContainer();
		
		if ($image_id == ""){
			return $ImageSetImageContainer->setPrimaryThumb($this->getParameter('ImageSetID'));
		}
		else{
			return $ImageSetImageContainer->setPrimaryThumb($this->getParameter('ImageSetID'),$image_id);		
		}
	}
	
	function getAllGalleryImages(){
		$ImageSetImageContainer = new ImageSetImageContainer();
		
		return $ImageSetImageContainer->getAllImageSetImages($this->getParameter('ImageSetID'));
	}
        
	function isPrivate(){
		switch (strtolower($this->getParameter('ImageSetStatus'))){
		case "private":
			return true;
			break;
		default:
			return false;
		}
	}
}
?>