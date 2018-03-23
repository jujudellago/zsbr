<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("Gallery.php");
require_once("ImageLibrarian.php");

if (!defined ('GALLERYTABLE')){
	define ('GALLERYTABLE',DATABASE_PREFIX.'Gallery');
}
    
class GalleryContainer extends ObjectContainer
{

	var $tablename;
	
	function GalleryContainer(){
		$this->DB_Object();
		$this->setDSN(DSN);
		$this->setTableName(GALLERYTABLE);
		$this->setColumnName("GalleryID","GalleryID");
		$this->setColumnName("GalleryName","GalleryName");
		$this->setColumnName("GalleryYear","GalleryYear");
		$this->setColumnName("GalleryDirectory","GalleryDirectory");
		$this->setColumnName("GalleryDescription","GalleryDescription");
		$this->setColumnName("GalleryStatus","GalleryStatus");
		$this->setColumnName("GalleryCreationDate","GalleryCreationDate");
		$this->setColumnName("GalleryDefaultCredit","GalleryDefaultCredit");
		$this->setColumnName("GalleryIndex","GallerySortIndex");
		$this->setColumnName("GalleryUpdateDate","GalleryUpdateDate");
		
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
				`GalleryID` int(6) unsigned AUTO_INCREMENT NOT NULL,
				`GalleryName` varchar(255) default NULL,
				`GalleryYear` char(4) default NULL,
				`GalleryDirectory` varchar(255) default NULL,
				`GalleryDescription` varchar(255) default NULL,
				`GalleryStatus` varchar(20) DEFAULT NULL,
				`GalleryCreationDate` datetime default NULL, 
				`GalleryUpdateDate` datetime default NULL, 
				`GallerySortIndex` int(6) default 1, 
				`GalleryDefaultCredit` varchar(255) default NULL, 
				PRIMARY KEY (`GalleryID`),
				KEY (`GalleryDescription`)
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
	
	
	function addGallery(&$gallery){
                $this->setTimestamps($gallery);
		return $this->addObject($gallery);
	}	
        
	function setTimestamps(&$gallery){
		$now = date("Y-m-d H:i:s");
		$gallery->setParameter('GalleryCreationDate',$now);
		$gallery->setParameter('GalleryUpdateDate',$now);
	}

	
	function updateGallery(&$gallery){
		$now = date("Y-m-d H:i:s");
		$gallery->setParameter('GalleryUpdateDate',$now);
		if (($gallery->Package->use_gallery_year and $gallery->getParameter('GalleryYear') != $gallery->getSavedParameter('GalleryYear'))
		or  $gallery->getParameter('GalleryDefaultCredit') != $gallery->getSavedParameter('GalleryDefaultCredit')
		or  $gallery->getParameter('GalleryDirectory') != $gallery->getSavedParameter('GalleryDirectory')){
	        // We have to do some directory finangling.  
	        $ImageLibrarian = new ImageLibrarian();
	        if ($gallery->getParameter('GalleryDirectory') != $gallery->getSavedParameter('GalleryDirectory')){
	            $SourceDirectory = DOC_BASE.GALLERY_IMAGE_DIR.$gallery->getSavedParameter('GalleryDirectory');
    	        $DestinationDirectory = DOC_BASE.GALLERY_IMAGE_DIR.$gallery->getParameter('GalleryDirectory');
	        }
	        else{
    	        $SourceDirectory = $gallery->getGalleryDirectory(IMAGE_DIR_FULL,false);
    	        $gallery->setGalleryDirectory();
    	        $DestinationDirectory = $gallery->getGalleryDirectory(IMAGE_DIR_FULL,false);
    	    }
	        
	        
	        $ImageLibrarian->makeDirectory($gallery->getParameter('GalleryDirectory'));
	        
            if (file_exists($SourceDirectory)){
                    if (!rename($SourceDirectory,$DestinationDirectory)){
                            return PEAR::raiseError("Sorry, we were unable to rename this gallery");
                    }
            }
	        
	    }
		return $this->updateObject($gallery);
	}
        

	function getAllGalleriesWhere($WhereClause,$sort_field = array('GalleryIndex','GalleryCreationDate'), $sort_dir = array('asc','asc'),$types = array('public')){
	    $wc = new whereClause();
	    
	    if (is_a($WhereClause,'whereClause')){
	        $wc->addCondition($WhereClause);
	    }
	    if (!is_array($types)){
	        $types = array($types);
	    }
        if (count($types)){
            $wcTypes = new whereClause();
            $wcTypes->setConnector('OR');
            foreach ($types as $type){
                    $wcTypes->addCondition($this->getColumnName('GalleryStatus')." = ?",$type);
            }
            $wc->addCondition($wcTypes);
        }
	        
        if (PEAR::isError($Objects = $this->getAllObjects($wc,$sort_field, $sort_dir))){ return $Objects;}
		if ($Objects){
		    return $this->manufactureGallery($Objects);
		}
		else{
			return array();
		}
	}
        
	function getAllGalleries($sort_field = array('GalleryIndex','GalleryCreationDate'), $sort_dir = array('asc','asc'), $types = array("public")){
		$wc = new whereClause();
                
                if (!is_array($types)){
                        $types = array($types);
                }
                if (count($types)){
                        $wcTypes = new whereClause();
                        $wcTypes->setConnector('OR');
                        foreach ($types as $type){
                                $wcTypes->addCondition($this->getColumnName('GalleryStatus')." = ?",$type);
                        }
                        $wc->addCondition($wcTypes);
                }
                

                if (PEAR::isError($Objects = $this->getAllObjects($wc,$sort_field, $sort_dir))){ return $Objects;}
		if ($Objects){
			$galleries = array();
			$current_id = "";
			foreach ($Objects as $Object){
				$current_id = $Object->getParameter('GalleryID');
				$galleries[$current_id] = new Gallery($current_id,$Object->getParameters());
				$galleries[$current_id]->saveParameters();
			}
			return $galleries;
		}
		else{
			return array();
		}
                
                
        }

	function getGallery($gallery_id){
		$wc = new whereClause($this->getColumnName('GalleryID')." = ?",$gallery_id);
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
			$current_id = $Object->getParameter('GalleryID');
			return new Gallery($current_id,$Object->getParameters());
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
	}
	
    function manufactureGallery($Object){
            if (!is_array($Object)){
                    $_Objects = array($Object);
            }
            else{
                    $_Objects = $Object;
            }
            
            $Galleries = array();
            foreach ($_Objects as $_Object){
                    $_tmp_Gallery = new Gallery($_Object->getParameter('GalleryID'));
                    $_parms = $_Object->getParameters();
                    foreach ($_parms as $key=>$value){
                            $_tmp_Gallery->setParameter($this->getParameterName($key),$value);
                    }
                    $_tmp_Gallery->saveParameters();
                    $Galleries[$_tmp_Gallery->getParameter($_tmp_Gallery->getIDParameter())] = $_tmp_Gallery;
            }
            
            if (!is_array($Object)){
                    return array_shift($Galleries);
            }
            else{
                    return $Galleries   ;
            }
    }	

	function getGalleryByName($gallery_name){
		$wc = new whereClause($this->getColumnName('GalleryName')." = ?",$gallery_name);
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
			$current_id = $Object->getParameter('GalleryID');
			return new Gallery($current_id,$Object->getParameters());
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
		
      	}
	
	function getPrimaryThumb($gallery_id){
		$GalleryImageContainer = new GalleryImageContainer();
		return $GalleryImageContainer->getPrimaryThumb($gallery_id);
	}

	function setGalleryIndex($gallery_id,$index){
		if (!is_int((int)$index)){
			return PEAR::raiseError("index must be an integer");
		}
	
		// Only going to reorder galleries of the same status/year
		$_Gallery = $this->getGallery($gallery_id);
		if (!is_a($_Gallery,'Gallery')){
		    return PEAR::raiseError("couldn't find a gallery with that index");
		}
		else{
		    
		    if ($_Gallery->Package->use_gallery_year){
                $wc = new WhereClause();
                $wc->addCondition($this->getColumnName('GalleryYear')." = ?",$_Gallery->getParameter('GalleryYear'));
                $Galleries = $this->getAllGalleriesWhere($wc,'GalleryIndex','asc',array("public","private"));
		    }
		    else{
    		    $Galleries = $this->getAllGalleries('GalleryIndex','asc',array($_Gallery->getParameter('GalleryStatus')));
		    }
		
    		$new_index = 1;
    		foreach ($Galleries as $Gallery){
    			if ($Gallery->getParameter('GalleryID') == $gallery_id){
    				$Gallery->setParameter('GalleryIndex',$index);
    			}
    			else{
    				if ($new_index == $index){
    					$new_index++;
    				}
    				$Gallery->setParameter('GalleryIndex',$new_index++);
    			}	
    			$this->updateGallery($Gallery);
    		}
    	}
	}
	
	function deleteGallery($gallery_id){
		$GalleryImageContainer = new GalleryImageContainer();
		
		if (PEAR::isError($e = $GalleryImageContainer->deleteAllGalleryImages($gallery_id))){
			return $e;
		}
		
                $Gallery = $this->getGallery($gallery_id);
                if ($Gallery and file_exists($Gallery->getGalleryDirectory(IMAGE_DIR_FULL))){
                        @rmdir($Gallery->getGalleryDirectory(IMAGE_DIR_FULL));
                }
		$wc = new whereClause($this->getColumnName('GalleryID')." = ?",$gallery_id);
		if (PEAR::isError($result = $this->deleteObject($wc))){
			return $result;
		}
		
		return true;
	}	
        
}

?>