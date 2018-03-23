<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("ImageSet.php");

if (!defined ('IMAGESETTABLE')){
	define ('IMAGESETTABLE',DATABASE_PREFIX.'ImageSet');
}
    
class ImageSetContainer extends ObjectContainer
{

	var $tablename;
	
	function ImageSetContainer(){
		$this->DB_Object();
		$this->setDSN(DSN);
		$this->setTableName(IMAGESETTABLE);
		$this->setColumnName("ImageSetID","ImageSetID");
		$this->setColumnName("ImageSetName","ImageSetName");
		$this->setColumnName("ImageSetDescription","ImageSetDescription");
		$this->setColumnName("ImageSetStatus","ImageSetStatus");
		$this->setColumnName("ImageSetCreationDate","ImageSetCreationDate");
		$this->setColumnName("ImageSetDefaultCaption","ImageSetDefaultCaption");
		$this->setColumnName("ImageSetIndex","ImageSetSortIndex");
		$this->setColumnName("ImageSetUpdateDate","ImageSetUpdateDate");
		
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
				`ImageSetID` int(6) unsigned AUTO_INCREMENT NOT NULL,
				`ImageSetName` varchar(255) default NULL,
				`ImageSetDescription` varchar(255) default NULL,
				`ImageSetStatus` varchar(20) DEFAULT NULL,
				`ImageSetCreationDate` datetime default NULL, 
				`ImageSetUpdateDate` datetime default NULL, 
				`ImageSetSortIndex` int(6) default 1, 
				`ImageSetDefaultCaption` varchar(255) default NULL, 
				PRIMARY KEY (`ImageSetID`),
				KEY (`ImageSetDescription`)
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
	
	
	function addImageSet(&$ImageSet){
                $this->setTimestamps($ImageSet);
		return $this->addObject($ImageSet);
	}	
        
	function setTimestamps(&$ImageSet){
		$now = date("Y-m-d H:i:s");
		$ImageSet->setParameter('ImageSetCreationDate',$now);
		$ImageSet->setParameter('ImageSetUpdateDate',$now);
	}

	
	function updateImageSet(&$ImageSet){
		$now = date("Y-m-d H:i:s");
		$ImageSet->setParameter('ImageSetUpdateDate',$now);
		return $this->updateObject($ImageSet);
	}
        
	function getAllImageSets($sort_field = array('ImageSetIndex','ImageSetCreationDate'), $sort_dir = array('asc','asc'), $types = array("public")){
		$wc = new whereClause();
                
        if (!is_array($types)){
                $types = array($types);
        }
        if (count($types)){
            $wcTypes = new whereClause();
            $wcTypes->setConnector('OR');
            foreach ($types as $type){
                    $wcTypes->addCondition($this->getColumnName('ImageSetStatus')." = ?",$type);
            }
            $wc->addCondition($wcTypes);
        }
        

        if (PEAR::isError($Objects = $this->getAllObjects($wc,$sort_field, $sort_dir))){ return $Objects;}
		if ($Objects){
			$ImageSets = array();
			$current_id = "";
			foreach ($Objects as $Object){
				$current_id = $Object->getParameter('ImageSetID');
				$ImageSets[$current_id] = new ImageSet($current_id,$Object->getParameters());
				$ImageSets[$current_id]->saveParameters();
			}
			return $ImageSets;
		}
		else{
			return array();
		}
                
                
    }

	function getImageSet($ImageSetID){
		$wc = new whereClause($this->getColumnName('ImageSetID')." = ?",$ImageSetID);
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
			$current_id = $Object->getParameter('ImageSetID');
			return new ImageSet($current_id,$Object->getParameters());
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
	}
	
	function getImageSetByName($ImageSetName){
		$wc = new whereClause($this->getColumnName('ImageSetName')." = ?",$ImageSetName);
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
			$current_id = $Object->getParameter('ImageSetID');
			return new ImageSet($current_id,$Object->getParameters());
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
		
      	}
	
	function getPrimaryThumb($ImageSetID){
		$ImageSetImageContainer = new ImageSetImageContainer();
		return $ImageSetImageContainer->getPrimaryThumb($ImageSetID);
	}

	function setImageSetIndex($ImageSetID,$index){
		if (!is_int((int)$index)){
			return PEAR::raiseError("index must be an integer");
		}
	
		// The following returns in the order of the indexing
		$ImageSets = $this->getAllImageSets('ImageSetIndex','asc',array("public","private"));
		
		$new_index = 1;
		foreach ($ImageSets as $ImageSet){
			if ($ImageSet->getParameter('ImageSetID') == $ImageSetID){
				$ImageSet->setParameter('ImageSetIndex',$index);
			}
			else{
				if ($new_index == $index){
					$new_index++;
				}
				$ImageSet->setParameter('ImageSetIndex',$new_index++);
			}	
			$this->updateImageSet($ImageSet);
		}
	}
	
	function getGalleryImage($ImageSetID,$ImageID){
		$ImageSetImageContainer = new ImageSetImageContainer();
	    return $ImageSetImageContainer->getImageSetImage($ImageSetID,$ImageID);
	}
	
	function updateGalleryImage($ImageSetID,$Image){
		$ImageSetImageContainer = new ImageSetImageContainer();
	    return $ImageSetImageContainer->updateImageSetImage($ImageSetID,$Image);
	}
	
	function deleteGalleryImage($ImageSetID,$ImageID){
		$ImageSetImageContainer = new ImageSetImageContainer();
	    return $ImageSetImageContainer->deleteImageSetImage($ImageSetID,$ImageID);
	}
	
	function deleteImageSet($ImageSetID){
		$ImageSetImageContainer = new ImageSetImageContainer();
		
		if (PEAR::isError($e = $ImageSetImageContainer->deleteImageSetImage($ImageSetID))){
			return $e;
		}
		$wc = new whereClause($this->getColumnName('ImageSetID')." = ?",$ImageSetID);
		if (PEAR::isError($result = $this->deleteObject($wc))){
			return $result;
		}
		
		return true;
	}	
        
}

?>