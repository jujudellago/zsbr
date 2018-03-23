<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("MediaClass.php");

define('MEDIA_TYPE_IMAGE','image');
define('MEDIA_TYPE_MP3','mp3');
define('MEDIA_TYPE_AUDIO','audio');
define('MEDIA_TYPE_GALLERYIMAGE','galleryimage');
define('MEDIA_TYPE_UNKNOWN','');

if (!defined ('MEDIA_TABLE')){
    define('MEDIA_TABLE',DATABASE_PREFIX."Media");
}

class MediaContainer extends ObjectContainer{

	var $tablename;
	
	function MediaContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(MEDIA_TABLE);
		$this->setColumnName('MediaID','MediaID');
		$this->setColumnName('MediaType','MediaType');
		$this->setColumnName('MediaLocation','MediaLocation');
		$this->setColumnName('MediaAssociatedObject','MediaAssociatedObject');
		$this->setColumnName('MediaAssociatedObjectID','MediaAssociatedObjectID');
                
		
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
			  `MediaID` int(7) NOT NULL auto_increment,
                          `MediaType` char(20) default '',
                          `MediaLocation` varchar(255) default '',
                          `MediaAssociatedObject` char(25) default '',
                          `MediaAssociatedObjectID` varchar(30) default '',
			  PRIMARY KEY  (`MediaID`), 
                          KEY (`MediaType`),
                          KEY (`MediaAssociatedObject`),
                          KEY (`MediaAssociatedObjectID`)
			) ENGINE MyISAM 
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
	
	function addMedia(&$Media){
		return $this->addObject($Media);
	}
	
	function updateMedia($Media){
		return $this->updateObject($Media);
	}

	
	function getAssociatedMedia($Object){
            if (!is_a($Object,'Parameterized_Object')){     
                    return (PEAR::raiseError('Object must be a Parameterized_Object'));
            }
            $wc = new whereClause();
            $wc->addCondition($this->getColumnName('MediaAssociatedObject')." = ?",get_class($Object));
            $wc->addCondition($this->getColumnName('MediaAssociatedObjectID')." = ?",$Object->getParameter($Object->getIDParameter()));
            return $this->getAllMedia($wc,'MediaID');
    }
        
	function getAllMedia($whereClause = "",$_sort_field = 'MediaType', $_sort_dir = 'asc'){
		if (PEAR::isError($Objects = $this->getAllObjects($whereClause,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
                        return $this->manufactureMedia($Objects);
		}
		else{
			return null;
		}
	}

        function manufactureMedia($Object){
                if (!is_array($Object)){
                        $_Objects = array($Object);
                }
                else{
                        $_Objects = $Object;
                }
                
                $Medias = array();
                foreach ($_Objects as $_Object){
                        $_tmp_Media = new Media();
                        $_parms = $_Object->getParameters();
                        foreach ($_parms as $key=>$value){
                                $_tmp_Media->setParameter($key,$value);
                        }
                        $_tmp_Media->saveParameters();
                        $Medias[$_tmp_Media->getParameter($_tmp_Media->getIDParameter())] = $_tmp_Media;
                }
                
                if (!is_array($Object)){
                        return array_shift($Medias);
                }
                else{
                        return $Medias;
                }
        }	
        
	function getMedia($Media_id){
		
		$wc = new whereClause($this->getColumnName('MediaID')." = ?",$Media_id);
		
		if (PEAR::isError($Object = $this->getObject($wc))) return $Object;
		
		if ($Object){
                        return $this->manufactureMedia($Object);
		}
		else{
			return null;
		}
	}
        
	
	function deleteMedia($Media_id){
                $Media = $this->getMedia($Media_id);
                if (is_a($Media_id,'whereClause')){
                        $wc = $Media_id;
                }
                else{
		        $wc = new whereClause($this->getColumnName('MediaID')." = ?",$Media_id);
                }
                $TargetedMedia = $this->getAllMedia($wc);
                if (is_array($TargetedMedia)){
                        foreach ($TargetedMedia as $Media){
                                if ($Media->getParameter('MediaType') == MEDIA_TYPE_GALLERYIMAGE){
                                        if (!isset($GalleryImageContainer)){
                                                $GalleryImageContainer = new GalleryImageContainer();
                                        }
                                        $GalleryImageContainer->deleteGalleryImage($Media->getParameter('MediaLocation'));
                                }
                                elseif ($Media->getParameter('MediaType') == MEDIA_TYPE_MP3){
                                    if (!isset($SongContainer)){
                                        $SongContainer = new SongContainer();
                                    }
                                    $SongContainer->deleteSong($Media->getParameter('MediaLocation'));
                                }
                                else{
                                        if (file_exists(ARTIST_DIRECTORY.$Media->getParameter('MediaLocation'))){
                                                unlink(ARTIST_DIRECTORY.$Media->getParameter('MediaLocation'));
                                        }
                                }
                        }
                }
		return $this->deleteObject($wc);
	}
        
        function deleteAssociatedMedia($Object,$Media_id = ""){
                if (is_a($Object,'Parameterized_Object')){
                        $wc = new whereClause();
                        $wc->addCondition($this->getColumnName('MediaAssociatedObject')." = ?",get_class($Object));
                        $wc->addCondition($this->getColumnName('MediaAssociatedObjectID')." = ?",$Object->getParameter($Object->getIDParameter()));
                        if ($Media_id != ""){
                                $wc->addCondition($this->getColumnName('MediaID')." = ?",$Media_id);
                        }
                        return $this->deleteMedia($wc);
                }
                return (PEAR::raiseError("Object must be a Parameterized Object"));
        }
	
}

?>