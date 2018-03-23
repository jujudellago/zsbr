<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("GalleryImage.php");
require_once("ImageLibrarian.php");

if (!defined ('GALLERYIMAGETABLE')){
	define ('GALLERYIMAGETABLE',DATABASE_PREFIX.'GalleryImage');
}

class GalleryImageContainer extends ObjectContainer
{
        var $types;	
		var $GalleryContainer;
		var $Galleries;
        
	function GalleryImageContainer(){
		$this->DB_Object();
		$this->setDSN(DSN);
		$this->setTableName(GALLERYIMAGETABLE);
		$this->setColumnName("GalleryID","GalleryID");
		$this->setColumnName("PrimaryThumb","GalleryPrimaryThumb");
		$this->setColumnName("GalleryImageOriginal","GalleryImage");
		$this->setColumnName("GalleryImageThumb","GalleryThumb");
		$this->setColumnName("GalleryImageResized","GalleryResized");
		$this->setColumnName("ImageCaption","GalleryImageCaption");
		$this->setColumnName("ImageCredit","GalleryImageCredit");
		$this->setColumnName("ImageIndex","GalleryImageSortIndex");
		$this->setColumnName("ImageID","GalleryImageID");
		$this->setColumnName("ImageIsMarked","ImageIsMarked");

                $this->setTypes(array('GalleryImageOriginal','GalleryImageThumb','GalleryImageResized'));
		
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
				`GalleryID` int(6) unsigned NOT NULL,
				`GalleryName` varchar(255) default NULL,
				`GalleryImageID` int(6) unsigned AUTO_INCREMENT NOT NULL,		
				`GalleryPrimaryThumb` int(1) unsigned default 0, 
				`GalleryImage` varchar(255) default NULL, 
				`GalleryImageCaption` text default NULL, 
				`GalleryImageCredit` varchar(255) default NULL, 
				`GalleryThumb` varchar(255) default NULL, 
				`GalleryResized` varchar(255) default NULL, 
				`GalleryImageSortIndex` int(6) default 1, 
				`ImageIsMarked` int(1) default 0, 
				PRIMARY KEY (`GalleryImageID`),
				KEY (`GalleryID`)
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
        
        function getTypes(){
                return $this->types;
        }
        
        function setTypes($types){
                $this->types = $types;
        }
		
	function addGalleryImage(&$galleryimage){
		return $this->addObject($galleryimage);
	}	
	
	function updateGalleryImage($galleryimage){
		return $this->updateObject($galleryimage);
	}
	
	function getPrimaryThumb($gallery_id){
                $wc = new whereClause();
                $wc->addCondition($this->getColumnName('GalleryID')." = ?",$gallery_id);
                $wc->addCondition($this->getColumnName('PrimaryThumb')." = 1");
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
                        return $this->manufactureGalleryImage($Object);
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
	}
	
	function setPrimaryThumb($gallery_id,$image_id=""){
		if ($image_id == ""){
                        $AllImages = $this->getAllGalleryImages($gallery_id);
                        if (count($AllImages)){
                                $newPrimary = array_shift($AllImages);
                                return $this->setPrimaryThumb($gallery_id,$newPrimary->getParameter('ImageID'));
                        }
                        else{
                                return false;
                        }
		}

		// un-primary the current thumb		
		$CurrentThumb = $this->getPrimaryThumb($gallery_id);
		if ($CurrentThumb){
			$CurrentThumb->setParameter('PrimaryThumb',0);
			$this->updateGalleryImage($CurrentThumb);
		}
		
		// Get the image we'll make the new thumb out of
		$NewThumb = $this->getGalleryImage($image_id);
		if ($NewThumb){
			$NewThumb->setParameter('PrimaryThumb',1);
			if (PEAR::isError($e = $this->updateGalleryImage($NewThumb))){
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
	
	function getAllGalleryImages($gallery_id = "",$sort_field = "ImageIndex", $sort_dir = 'asc'){
		$wc = new whereClause();
                
		if ($gallery_id != ""){
                        $wc->addCondition($this->getColumnName('GalleryID')." = ?",$gallery_id);
                }
                
		
                if (PEAR::isError($Objects = $this->getAllObjects($wc,$sort_field, $sort_dir))){ return $Objects; }
		if ($Objects){
			$galleryimages = array();
			$current_id = "";
			foreach ($Objects as $Object){
				$current_id = $Object->getParameter('GalleryID');
                                if (!isset($galleryimages[$current_id])){
                                        $galleryimages[$current_id] = array();
                                }
				$galleryimages[$current_id][] = $this->manufactureGalleryImage($Object);
			}
                        if ($gallery_id != ""){
                                if (!isset($galleryimages[$gallery_id])){
                                        return array();
                                }
			        return $galleryimages[$gallery_id];
                        }
                        else{
                                return $galleryimages;
                        }
		}
		else{
			return array();
		}
	}
	
	function getAllGalleryImagesByID(){
		$wc = new whereClause();
                
        if (PEAR::isError($Objects = $this->getAllObjects($wc,'GalleryImageID', 'asc'))){ return $Objects; }
		if ($Objects){
			$galleryimages = array();
			foreach ($Objects as $Object){
			    $galleryimages[$Object->getParameter('ImageID')] = $this->manufactureGalleryImage($Object);
			}
            return $galleryimages;
		}
		else{
			return array();
		}
	}
	
	
	function getAllMarkedGalleryImages($gallery_id = "", $sort_field = "ImageIndex", $sort_dir = 'asc'){
		$wc = new whereClause();
                
		if ($gallery_id != ""){
            $wc->addCondition($this->getColumnName('GalleryID')." = ?",$gallery_id);
        }
        $wc->addCondition($this->getColumnName('ImageIsMarked')." = 1");
                
        if (PEAR::isError($Objects = $this->getAllObjects($wc,$sort_field, $sort_dir))){ return $Objects; }
		if ($Objects){
			$galleryimages = array();
			$current_id = "";
			foreach ($Objects as $Object){
				$galleryimages[] = $this->manufactureGalleryImage($Object);
			}
            return $galleryimages;
		}
		else{
			return array();
		}
	}
	
	function getRandomGalleryImage($gallery_id = ""){
		$images = $this->getAllGalleryImages($gallery_id,"rand()"," LIMIT 1");
		if (PEAR::isError($images)){
			return $images;
		}
		
		if($images){
                        if (is_array($images[0])){
                                return $images[0][0];
                        }
			return $images[0];
		}
		else{
			return null;
		}
	}
				
	function getGalleryImage($image_id){
		$wc = new whereClause($this->getColumnName('ImageID')." = ?",$image_id);
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
                        return $this->manufactureGalleryImage($Object);
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
			
	}
	
        function manufactureGalleryImage($Object){
                if (!is_array($Object)){
                        $_Objects = array($Object);
                }
                else{
                        $_Objects = $Object;
                }
                
                if (!$this->GalleryContainer){
                    $this->GalleryContainer = new GalleryContainer();
                }
                if (!is_array($this->Galleries)){
                    $this->Galleries = array();
                }
                $Galleries = &$this->Galleries;
                $GalleryContainer = &$this->GalleryContainer;
                
                $GalleryImages = array();
                foreach ($_Objects as $_Object){
                    $_tmp_GalleryImage = new GalleryImage($_Object->getParameter('ImageID'));
                    $_parms = $_Object->getParameters();
                    foreach ($_parms as $key=>$value){
                            $_tmp_GalleryImage->setParameter($this->getParameterName($key),$value);
                    }
                    
                    if ($_Object->getParameter('GalleryID') != ""){
                        if (!array_key_exists($_Object->getParameter('GalleryID'),$Galleries)){
                            $Galleries[$_Object->getParameter('GalleryID')] = $GalleryContainer->getGallery($_Object->getParameter('GalleryID'));
                        }

                        // Set the credit properly
                        if (!$_tmp_GalleryImage->getParameter('ImageCredit')){
                            $_tmp_GalleryImage->setParameter('ImageCredit',$Galleries[$_Object->getParameter('GalleryID')]->getParameter('GalleryDefaultCredit'));
                        }
                        if ($Galleries[$_Object->getParameter('GalleryID')]->getParameter('GalleryYear') != ""){
                            $_tmp_GalleryImage->setParameter('ImageYear',$Galleries[$_Object->getParameter('GalleryID')]->getParameter('GalleryYear'));
                        }
                    }
                    $_tmp_GalleryImage->saveParameters();
                    $GalleryImages[$_tmp_GalleryImage->getParameter($_tmp_GalleryImage->getIDParameter())] = $_tmp_GalleryImage;
                }
                
                if (!is_array($Object)){
                        return array_shift($GalleryImages);
                }
                else{
                        return $GalleryImages;
                }
        }	
	
        function setImageIndex($gallery_id,$image_id,$index){
		if (!is_int((int)$index)){
			return PEAR::raiseError("index must be an integer");
		}
	
		// The following returns in the order of the indexing
		$Images = $this->getAllGalleryImages($gallery_id);
		
		$new_index = 1;
		foreach ($Images as $Image){
			if ($Image->getParameter('ImageID') == $image_id){
				$Image->setParameter('ImageIndex',$index);
			}
			else{
				if ($new_index == $index){
					$new_index++;
				}
				$Image->setParameter('ImageIndex',$new_index++);
			}	
			if (PEAR::isError($e = $this->updateGalleryImage($Image))){
                                return $e;
                        }
		}
                return true;
	}
	
	
	function deleteGalleryImage($image_id){
        $GalleryImage = $this->getGalleryImage($image_id);
		if ($GalleryImage){
    	    // First, let's delete tags, if they exist
    	    $Bootstrap = Bootstrap::getBootstrap();
    	    if ($Bootstrap->packageExists('Tags')){
    	        $Bootstrap->usePackage('Tags');
    	        $TaggedObjectContainer = new TaggedObjectContainer();
    	        $TaggedObjectContainer->removeAllTagsForObject($GalleryImage);
    	    }

			$imagetypes = $this->getTypes();
			foreach ($imagetypes as $type){
                $wc = new whereClause();
                $wc->setConnector("OR");
                $values = array();
				if (($image = $GalleryImage->getParameter($type)) != ""){
        			foreach ($imagetypes as $type){
                        $wc->addCondition($this->getColumnName($type)." = ?",$image);
                    }
                    $wc2 = new whereClause($wc);
                    $wc2->addCondition($this->getColumnName('GalleryID')." = ?",$GalleryImage->getParameter('GalleryID'));
                    if (count($this->getAllObjects($wc2)) == 1){
                        // Need to get the Gallery so we know what directory to delete it out of....grrrr
                        $GalleryContainer = new GalleryContainer();
                        $Gallery = $GalleryContainer->getGallery($GalleryImage->getParameter('GalleryID'));
                        
                        if ($Gallery and file_exists($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$image)){
                            unlink($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$image);
                        }
                    }
                }
            }
        }
                
		$wc = new whereClause($this->getColumnName('ImageID')." = ?",$image_id);
		if (PEAR::isError($result = $this->deleteObject($wc))){
			return $result;
		}
        if ($GalleryImage and $GalleryImage->getParameter('PrimaryThumb')){
                $this->setPrimaryThumb($GalleryImage->getParameter('GalleryID'));
        }
        return true;

	}

	function deleteAllGalleryImages($gallery_id){
		$GalleryImages = $this->getAllGalleryImages($gallery_id);
		
		if (is_array($GalleryImages)){
			foreach ($GalleryImages as $GalleryImage){
				if (PEAR::isError($e = $this->deleteGalleryImage($GalleryImage->getParameter('ImageID')))){
					return $e;
				}	
			}
		}
	
		return true;
	}
	
	function resizeImage($ImageID,$NewWidth,$NewHeight = ""){
	    $Image = $this->getGalleryImage($ImageID);
	    if (!is_a($Image,'GalleryImage')){
	        return PEAR::raiseError("Couldn't find that image");
	    }
	    $GalleryContainer = new GalleryContainer();
	    $Gallery = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));

	    $ImageLibrarian = new ImageLibrarian();
	    $src = $Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImageOriginal');
	    $regs = getimagesize($src);
	    $ResizedName = $ImageLibrarian->getResizedName($Image->getParameter('GalleryImageOriginal'),array('width' => $regs[0], 'height' => $regs[1], 'newDimensions' => array('width' => $NewWidth, 'height' => $NewHeight, 'conditions' => RESIZE_CONDITIONS_DEFAULT)));
	    $dest = $Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$ResizedName;
	    if (!PEAR::isError($res = $ImageLibrarian->resizeImage($src,$dest,$NewWidth,$NewHeight))){
	        if ($Image->getParameter('GalleryImageResized') != $Image->getParameter('GalleryImageOriginal')){
	            unlink($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImageResized'));
	        }
	        $Image->setParameter('GalleryImageResized',$ResizedName);
	        $this->updateGalleryImage($Image);
	        return true;
	    }
	    else{
	        return $res;
	    }
	}
	
}


?>
