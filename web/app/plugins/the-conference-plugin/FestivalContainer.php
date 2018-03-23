<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("Festival.php");
require_once("FestivalArtistContainer.php");
require_once("ScheduleContainer.php");

if (!defined ('FESTIVAL_TABLE')){
    define('FESTIVAL_TABLE',DATABASE_PREFIX."Festivals");
}


class FestivalContainer extends ObjectContainer{

	var $tablename;
	var $Package;
	
	function FestivalContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(FESTIVAL_TABLE);
		$this->setColumnName('FestivalYear','FestivalYear');
		$this->setColumnName('FestivalStartDate','FestivalStartDate');
		$this->setColumnName('FestivalEndDate','FestivalEndDate');
		$this->setColumnName('FestivalLineupIsPublished','FestivalLineupIsPublished');
		$this->setColumnName('FestivalDoNotPublishFeeds','FestivalDoNotPublishFeeds');
		$this->setColumnName('FestivalModifiedTimestamp','FestivalModifiedTimestamp');
		
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
			  `FestivalYear` varchar(25) NOT NULL,
			  `FestivalStartDate` date Default NULL,
			  `FestivalEndDate` date Default NULL,
			  `FestivalLineupIsPublished` int(1) default 0,
			  `FestivalDoNotPublishFeeds` int(1) default 0,
			  `FestivalModifiedTimestamp` timestamp default NOW(),
			  PRIMARY KEY  (`FestivalYear`)
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
	
	function addFestival(&$Festival){
		$this->setTimestamp($Festival);
		return $this->addObject($Festival,true);
	}
	
	function updateFestival($Festival){
		$this->setTimestamp($Festival);
	    if ($Festival->getSavedParameter('FestivalStartDate') != "" and 
	    ($Festival->getParameter('FestivalStartDate') != $Festival->getSavedParameter('FestivalStartDate')
	    or $Festival->getParameter('FestivalEndDate') != $Festival->getSavedParameter('FestivalEndDate'))){
	        $ScheduleContainer = new ScheduleContainer();
	        $result = $ScheduleContainer->updateSchedules($Festival);
	        if (PEAR::isError($result)){
	            return $result;
	        }
	    }
		if ($Festival->getSavedParameter('FestivalYear') != $Festival->getParameter('FestivalYear')){
			// They're changing the year.  This is the piece that ties together the different containers.
			// I resisted allowing this change for a long time, but, really, why shouldn't I allow it
			$c = new ObjectContainer(); // a blank object container, so we can make use of the batchUpdate method
			$c->setDSN(DSN);
			
			$t = new ScheduleContainer(); // target
			$wc = new whereClause($t->getColumnName('ScheduleYear').' = ?',$Festival->getSavedParameter('FestivalYear'));
			$c->setTableName($t->getTableName());
			$c->batchUpdate($t->getColumnName('ScheduleYear'),$Festival->getParameter('FestivalYear'),$wc);

			$t = new FestivalArtistContainer(); // target
			$wc = new whereClause($t->getColumnName('FestivalYear').' = ?',$Festival->getSavedParameter('FestivalYear'));
			$c->setTableName($t->getTableName());
			$c->batchUpdate($t->getColumnName('FestivalYear'),$Festival->getParameter('FestivalYear'),$wc);

			$t = new SimpleShowContainer(); // target
			$wc = new whereClause($t->getColumnName('ShowYear').' = ?',$Festival->getSavedParameter('FestivalYear'));
			$c->setTableName($t->getTableName());
			$c->batchUpdate($t->getColumnName('ShowYear'),$Festival->getParameter('FestivalYear'),$wc);
			
			do_action('the_conference_plugin_year_changed',$Festival->getSavedParameter('FestivalYear'),$Festival->getParameter('FestivalYear')); // $old_year, $new_year
		}
		return $this->updateObject($Festival,true);
	}
	
	function setTimestamp(&$Festival){
		$Festival->setParameter('FestivalModifiedTimestamp', date("Y-m-d H:i:s"));
	}
	
	function FestivalExists($Festival_id){
		$Festival = $this->getFestival($Festival_id);
		if (PEAR::isError($Festival)){
			return $Festival;
		}
		else{
			if ($Festival) return true; else return false;
		}
		
	}
	
	function getAllFestivals($whereClause = "",$_sort_field = 'FestivalYear', $_sort_dir = 'desc'){
		if (PEAR::isError($Objects = $this->getAllObjects($whereClause,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
                        return $this->manufactureFestival($Objects);
		}
		else{
			return null;
		}
	}

    function manufactureFestival($Object){
            if (!is_array($Object)){
                    $_Objects = array($Object);
            }
            else{
                    $_Objects = $Object;
            }
            
            $Festivals = array();
			if (!$this->Package){
				$Bootstrap = Bootstrap::getBootstrap();
				$this->Package = $Bootstrap->usePackage('FestivalApp');
			}
            foreach ($_Objects as $_Object){
                    $_tmp_Festival = new Festival();
                    $_parms = $_Object->getParameters();
                    foreach ($_parms as $key=>$value){
                            $_tmp_Festival->setParameter($key,$value);
                    }
                    if (file_exists($this->Package->etcDirectory.$_tmp_Festival->getParameter('FestivalYear')."ScheduleNames.txt")){
                        $_tmp_Festival->setParameter('FestivalScheduleIsPublished',true);
                    }
                    $_tmp_Festival->saveParameters();
                    $Festivals[$_tmp_Festival->getParameter($_tmp_Festival->getIDParameter())] = $_tmp_Festival;
            }
            
            if (!is_array($Object)){
                    return array_shift($Festivals);
            }
            else{
                    return $Festivals;
            }
    }	
    
	function getFestival($Festival_id){
		
		$wc = new whereClause($this->getColumnName('FestivalYear')." = ?",$Festival_id);
		
		if (PEAR::isError($Object = $this->getObject($wc))) return $Object;
		
		if ($Object){
                        return $this->manufactureFestival($Object);
		}
		else{
			return null;
		}
	}
        
	function deleteFestival($FestivalYear){
        $FestivalArtistContainer = new FestivalArtistContainer();
        if (PEAR::isError($result = $FestivalArtistContainer->deleteFestivalArtist($FestivalYear))){
                return $result;
        }
        
        $ScheduleContainer = new ScheduleContainer();
        if (PEAR::isError($result = $ScheduleContainer->deleteAllSchedules($FestivalYear))){
                return $result;
        }
                
		$wc = new whereClause($this->getColumnName('FestivalYear')." = ?",$FestivalYear);
		
		return $this->deleteObject($wc);
	
	}
	
	function performReset($FestivalYear,$ClearLineup = false,$ClearSchedules = false,$ClearOrphans = false){
		// This new function will remove all schedules from the passed festival as well as search the artist
		// database for orphaned artists (those not assigned to any festival) and delete them.
		// It's useful for the import process to be able to start from a clean slate.  
		
		// $Parms should be an array.  
		// 	- if 'lineup' is present, the lineup will be cleared
		//  - if 'schedules' is present
		
		$Festival = $this->getFestival($FestivalYear);
		if (!is_a($Festival,'Festival')){
			return;
		}
		
		$FestivalArtistContainer = new FestivalArtistContainer();
		$ArtistContainer = new ArtistContainer();
		$ScheduleContainer = new ScheduleContainer();

		// Step 1.  Reset the lineup and remove
		if ($ClearLineup){
			$FestivalArtistContainer->setFestivalLineup($FestivalYear,array());
			$FestivalArtistContainer->deleteFestivalArtist($FestivalYear);
			$Festival->setParameter('FestivalLineupIsPublished',false);
			$this->updateFestival($Festival);
		}
		
		// Step 2.  Get all the schedules and delete them.  Also deletes shows
		if ($ClearSchedules){
			$ScheduleContainer->deleteAllSchedules($FestivalYear);

			// Step 2a.  Unpublish schedules
			$Bootstrap = Bootstrap::getBootstrap();
			$Package = $Bootstrap->usePackage('FestivalApp');
	        foreach (glob($Package->etcDirectory."{$FestivalYear}*.txt") as $filename) {
	            unlink ($filename);
	        }
		}
				
		// Step 3.  Delete any orphaned Artists
		if ($ClearOrphans){
			$artist_id = $ArtistContainer->getColumnName('ArtistID');
			$artist_table = $ArtistContainer->getTableName();
			$festival_artist_id = $FestivalArtistContainer->getColumnName('ArtistID');
			$festival_artist_year = $FestivalArtistContainer->getColumnName('FestivalYear');
			$festival_artist_table = $FestivalArtistContainer->getTableName();
			
			$query = "SELECT $artist_id FROM $artist_table WHERE $artist_id NOT IN (SELECT DISTINCT($festival_artist_id) FROM $festival_artist_table)";
			$dbh = $this->connectDB();
			$sth = $dbh->prepare($query);

			//echo $dbh->executeEmulateQuery($sth);

			$result =& $dbh->execute($sth);
			if (PEAR::isError($result)){
				return PEAR::raiseError("Error:  ".$result->getMessage()." <br>Query:  $query");
			}
			$numRows = $result->numRows();
			if($numRows){
				for ($i = 0; $i < $numRows; $i++){
					$arr = array();
					$parms = array();
					$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
					$ArtistContainer->deleteArtist($arr[$artist_id]);
				}		
			}		
		}
		
		return true;
	}
	
}

?>