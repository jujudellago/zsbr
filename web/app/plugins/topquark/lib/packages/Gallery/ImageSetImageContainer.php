<?php

require_once(PACKAGE_DIRECTORY."Common/FancyObjectContainer.php");
require_once("ImageSetContainer.php");
require_once("GalleryImageContainer.php");

if (!defined ('IMAGESETIMAGETABLE')){
	define ('IMAGESETIMAGETABLE',DATABASE_PREFIX.'ImageSetImage');
}

class ImageSetImageContainer extends FancyObjectContainer{

	var $tablename;
	
	function ImageSetImageContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(IMAGESETIMAGETABLE);
		$this->setColumnName("ImageSetID","ImageSetID");
		$this->setColumnName("ImageID","ImageSetImageID");
		$this->setColumnName("PrimaryThumb","ImageSetPrimaryThumb");
		$this->setColumnName("ImageCaption","ImageSetImageCaption");
		$this->setColumnName("ImageIndex","ImageSetImageSortIndex");

        $this->addTableName('GalleryImage',GALLERYIMAGETABLE);
        $this->addTableName('Gallery',GALLERYTABLE);
                
		if (!$this->tableExists()){
			$this->initializeTable();
		}
	}
	
	function initializeTable(){
		$this->ensureTableExists();
		
	}
	
	function ensureTableExists(){
		$create_query="
			CREATE TABLE `".$this->getTableName()."` ( 
				`ImageSetID` int(6) unsigned NOT NULL,
				`ImageSetImageID` int(6) unsigned NOT NULL,
				`ImageSetPrimaryThumb` int(1) unsigned default 0, 
				`ImageSetImageCaption` text default NULL, 
				`ImageSetImageSortIndex` int(6) default 1, 
				PRIMARY KEY (`ImageSetImageID`,`ImageSetID`),
				KEY (`ImageSetID`)
			) TYPE=MyISAM 
		";

		if ($this->tableExists()){
			return true;
		}
		else{
			$result = $this->createTable($create_query);
			if (PEAR::isError($result)){
				return $result;
			}
			else{
				return true;
			}
		}
	}
         
    function getLinkingWhereClause(){
        $wc = new whereClause();

        $GalleryImageContainer = new GalleryImageContainer();
        $wc->addCondition($this->getTableName() . "." . $this->getColumnName('ImageID') . " = " . $GalleryImageContainer->getTableName() . "." . $GalleryImageContainer->getColumnName('ImageID'));

        $GalleryContainer = new GalleryContainer();
        $wc->addCondition($GalleryImageContainer->getTableName() . "." . $GalleryImageContainer->getColumnName('GalleryID') . " = " . $GalleryContainer->getTableName() . "." . $GalleryContainer->getColumnName('GalleryID'));
        return $wc;
    }

	function addImageSetImage($Image,$ImageSetID,$Index=""){
        $Object = new Parameterized_Object();
        $Object->setParameter('ImageID',$Image->getParameter('ImageID'));
        $Object->setParameter('ImageSetID',$ImageSetID);
        $Object->setParameter('ImageIndex',$Index);
        $Object->setParameter('ImageCaption',$Image->getParameter('ImageCaption'));
        return $this->addObject($Object);
	}
	
	function addImageSetImageID($ImageSetID,$ImageID){
	    // First check and make sure this ImageID isn't already in the Image Set
	    if ($this->getImageSetImage($ImageSetID,$ImageID)){
	        return true;
	    }
	    
	    // Next, check and make sure it's a valid GalleryImage
	    $GalleryImageContainer = new GalleryImageContainer();
	    $newImage = $GalleryImageContainer->getGalleryImage($ImageID);
	    if (!is_a($newImage,'GalleryImage')){
	        return PEAR::raiseError("An image ID was passed that is not in the system.  The image was not added to the image set");
	    }
	    
	    $Object = new Parameterized_Object();

	    // Next we have to make it so it shows up at the end of the list
	    $ImageSetImages = $this->getAllImageSetImages($ImageSetID);
	    if (is_array($ImageSetImages)){
	        $index = count($ImageSetImages);
            $Object->setParameter('PrimaryThumb',0);
	    }
	    else{
	        $index= 0;
            $Object->setParameter('PrimaryThumb',1);
	    }
        $Object->setParameter('ImageID',$ImageID);
        $Object->setParameter('ImageSetID',$ImageSetID);
        $Object->setParameter('ImageIndex',$index);
        return $this->addObject($Object);
	}
	
	function getPrimaryThumb($ImageSetID){
        $wc = $this->getLinkingWhereClause();
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ImageSetID')." = ?",$ImageSetID);
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('PrimaryThumb')." = 1");
		if (PEAR::isError($Objects = $this->getAllObjects($wc))) return $Objects;
		if ($Objects){
		    return array_shift($this->manufactureImageSetImage($Objects));
		}
		else{
			return null;
		}
	}
	
	function setPrimaryThumb($ImageSetID,$ImageID=""){
		if ($ImageID == ""){
            $AllImages = $this->getAllImageSetImages($ImageSetID);
            if (count($AllImages)){
                    $newPrimary = array_shift($AllImages);
                    return $this->setPrimaryThumb($ImageSetID,$newPrimary->getParameter('ImageID'));
            }
            else{
                    return false;
            }
		}

		// un-primary the current thumb		
		$CurrentThumb = $this->getPrimaryThumb($ImageSetID);
		if ($CurrentThumb){
			$CurrentThumb->setParameter('PrimaryThumb',0);
			$this->updateImageSetImage($ImageSetID,$CurrentThumb);
		}
		
		// Get the image we'll make the new thumb out of
		$NewThumb = $this->getImageSetImage($ImageSetID,$ImageID);
		if ($NewThumb){
			$NewThumb->setParameter('PrimaryThumb',1);
			if (PEAR::isError($e = $this->updateImageSetImage($ImageSetID,$NewThumb))){
				return $e;
			}
			else{
				return true;
			}
		}
		else{
			return false;
		}
	}
	
	function getAllImageSetImages($ImageSetID, $_sort_field = "ImageSetImageSortIndex", $_sort_dir = "asc"){
        $wc = $this->getLinkingWhereClause();
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ImageSetID')." = ?",$ImageSetID);
		if (PEAR::isError($Objects = $this->getAllObjects($wc,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
		    return $this->manufactureImageSetImage($Objects);
		}
		else{
			return null;
		}
	}
	
	function setImageIndex($ImageSetID,$ImageID,$index){
		if (!is_int((int)$index)){
			return PEAR::raiseError("index must be an integer");
		}
		
		// The following returns in the order of the indexing
		$ImageSetImages = $this->getAllImageSetImages($ImageSetID);
		
		$new_index = 1;
		foreach ($ImageSetImages as $Image){
			if ($Image->getParameter('ImageID') == $ImageID){
				$Image->setParameter('ImageIndex',$index);
			}
			else{
				if ($new_index == $index){
					$new_index++;
				}
				$Image->setParameter('ImageIndex',$new_index++);
			}	
			$this->updateImageSetImage($ImageSetID,$Image);
		}
	}
	
	function getImageSetImage($ImageSetID,$ImageID){
	    $wc = $this->getLinkingWhereClause();
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ImageSetID')." = ?",$ImageSetID);
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ImageID')." = ?",$ImageID);
		if (PEAR::isError($Objects = $this->getAllObjects($wc))) return $Objects;

		if ($Objects){
		    return array_shift($this->manufactureImageSetImage($Objects));
		}
		else{
			return null;
		}
	}
	
	function updateImageSetImage($ImageSetID,$Image){
	    $wc = new whereClause();
        $wc->addCondition($this->getColumnName('ImageSetID')." = ?",$ImageSetID);
        $wc->addCondition($this->getColumnName('ImageID')." = ?",$Image->getParameter('ImageID'));
		return $this->updateObject($Image,$wc);
	}
	
	function manufactureImageSetImage($Objects){
            $GalleryImageObjects = $Objects[$this->getTableName('GalleryImage')];
            $GalleryImageContainer = new GalleryImageContainer();
            $GalleryImages = $GalleryImageContainer->manufactureGalleryImage($GalleryImageObjects);
            if (!is_array($GalleryImages)){
                $_GalleryImages = array($GalleryImages->getParameter('ImageID') => $GalleryImages);
            }
            else{
                $_GalleryImages = $GalleryImages;
            }
            

            $ImageSetObjects = $Objects[$this->getTableName('Main')];
            
            foreach ($ImageSetObjects as $_Object){
                if ($_Object->getParameter('ImageCaption') != ""){
                    $_GalleryImages[$_Object->getParameter('ImageID')]->setParameter('ImageCaption',$_Object->getParameter('ImageCaption'));
                }
                $_GalleryImages[$_Object->getParameter('ImageID')]->setParameter('PrimaryThumb',$_Object->getParameter('PrimaryThumb'));
                $_GalleryImages[$_Object->getParameter('ImageID')]->setParameter('ImageIndex',$_Object->getParameter('ImageIndex'));
                $_GalleryImages[$_Object->getParameter('ImageID')]->saveParameters();
            }
            
            if (!is_array($_GalleryImages)){
                    return array_shift($_GalleryImages);
            }
            else{
                    return $_GalleryImages;
            }
	    
	}
	        	
	function deleteImageSetImage($ImageSetID = "",$ImageID = ""){
		$wc = new whereClause();
        if ($ImageSetID != ""){
                $wc->addCondition($this->getColumnName('ImageSetID')." = ?",$ImageSetID);
        }
        if ($ImageID != ""){
                $wc->addCondition($this->getColumnName('ImageID')." = ?",$ImageID);
        }
        
        // First get the image, to see if it's primary (cause we'll have to reset that)
        if ($ImageID != ""){
            $Image = $this->getImageSetImage($ImageSetID,$ImageID);
        }
        
		$result = $this->deleteObject($wc);
		
		if ($ImageID != "" and $Image->getParameter('PrimaryThumb') == '1'){
		    $this->setPrimaryThumb($ImageSetID);
		}
		
		return $result;
		
	
	}
        
}

?>
