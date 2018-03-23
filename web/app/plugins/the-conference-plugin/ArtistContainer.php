<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("Artist.php");
require_once("FestivalArtistContainer.php");
require_once("ShowContainer.php");

if (!defined ('ARTIST_TABLE')){
    define('ARTIST_TABLE',DATABASE_PREFIX."Artists");
}


class ArtistContainer extends ObjectContainer{

	var $tablename;
	
	function ArtistContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(ARTIST_TABLE);
		$this->setColumnName('ArtistID','ArtistID');
		$this->setColumnName('ArtistLastName','ArtistLastName');
		$this->setColumnName('ArtistFirstName','ArtistFirstName');
		$this->setColumnName('ArtistFullName','ArtistFullName');
		$this->setColumnName('ArtistWebsite','ArtistWebsite');
		$this->setColumnName('ArtistVideo','ArtistVideo');
		$this->setColumnName('ArtistExtra1','ArtistExtra1');
		$this->setColumnName('ArtistExtra2','ArtistExtra2');
		$this->setColumnName('ArtistExtra3','ArtistExtra3');
		$this->setColumnName('ArtistDescription','ArtistDefaultDescription');
		$this->setColumnName('ArtistLongDescription','ArtistDefaultLongDescription');
		$this->setColumnName('ArtistProgramDescription','ArtistDefaultProgramDescription');
		
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
			  `ArtistID` int(7) NOT NULL auto_increment,
                          `ArtistLastName` varchar(128) default '',
                          `ArtistFirstName` varchar(128) default '',
                          `ArtistFullName` varchar(255) default '',
                          `ArtistWebsite` varchar(255) default '',
 						   `ArtistVideo` varchar(255) default '',
 						   `ArtistExtra1` varchar(255) default '',
 						   `ArtistExtra2` varchar(255) default '',
 						   `ArtistExtra3` varchar(255) default '',
                          `ArtistDefaultDescription` text,
                          `ArtistDefaultLongDescription` text,
                          `ArtistDefaultProgramDescription` text,
			  PRIMARY KEY  (`ArtistID`), 
                          UNIQUE INDEX (`ArtistFullName`), 
                          KEY (`ArtistLastName`, `ArtistFirstName`)
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
	
	function addArtist(&$Artist){
		return $this->addObject($Artist);
	}
	
	function updateArtist($Artist){
		return $this->updateObject($Artist);
	}
	
	function getAllArtists($whereClause = null,$_sort_field = 'ArtistFullName', $_sort_dir = 'asc'){
		if (PEAR::isError($Objects = $this->getAllObjects($whereClause,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
                        return $this->manufactureArtist($Objects);
		}
		else{
			return null;
		}
	}
	
        function manufactureArtist($Object){
                if (!is_array($Object)){
                        $_Objects = array($Object);
                }
                else{
                        $_Objects = $Object;
                }
                
                $Artists = array();
                foreach ($_Objects as $_Object){
                        $_tmp_Artist = new Artist();
                        $_parms = $_Object->getParameters();
                        foreach ($_parms as $key=>$value){
                                $_tmp_Artist->setParameter($key,$value);
                        }
                        $_tmp_Artist->saveParameters();
                        $Artists[$_tmp_Artist->getParameter($_tmp_Artist->getIDParameter())] = $_tmp_Artist;
                }
                
                if (!is_array($Object)){
                        return array_shift($Artists);
                }
                else{
                        return $Artists;
                }
        }	
        
	function getArtist($Artist_id){
		
		$wc = new whereClause($this->getColumnName('ArtistID')." = ?",$Artist_id);
		
		if (PEAR::isError($Object = $this->getObject($wc))) return $Object;
		
		if ($Object){
                        return $this->manufactureArtist($Object);
		}
		else{
			return null;
		}
	}
        
	
	function deleteArtist($Artist_id){
		static $FestivalArtistContainer,$ShowContainer,$MediaContainer;
		if (!isset($FestivalArtistContainer)){
            $FestivalArtistContainer = new FestivalArtistContainer();
			$ShowContainer = new ShowContainer();
			$MediaContainer = new MediaContainer();
		}
		if (PEAR::isError($result = $FestivalArtistContainer->deleteFestivalArtist("",$Artist_id))){
		        return $result;
		}

		if (PEAR::isError($result = $ShowContainer->deleteArtist("",$Artist_id))){
		        return $result;
		}

		if (PEAR::isError($result = $MediaContainer->deleteAssociatedMedia(new Artist($Artist_id)))){
		        return $result;
		}


                
		$wc = new whereClause($this->getColumnName('ArtistID')." = ?",$Artist_id);
		
		return $this->deleteObject($wc);
	
	}
	
	function getArtistByName($name){
		$wc = new whereClause();
		$wc->addCondition($this->getColumnName('ArtistFullName').' = ?',$name);
		$Artists = $this->getAllArtists($wc);
		if (is_array($Artists)){
			return current($Artists);
		}
		else{
			return null;
		}
	}
	
}

?>