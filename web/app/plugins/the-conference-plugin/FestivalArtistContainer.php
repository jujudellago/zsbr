<?php

require_once(PACKAGE_DIRECTORY."Common/FancyObjectContainer.php");
require_once("ArtistContainer.php");
require_once("FestivalContainer.php");
require_once("FestivalArtist.php");

if (!defined ('FESTIVAL_ARTIST_TABLE')){
    define('FESTIVAL_ARTIST_TABLE',DATABASE_PREFIX."FestivalArtists");
}

class FestivalArtistContainer extends FancyObjectContainer{

	var $tablename;
	
	function FestivalArtistContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(FESTIVAL_ARTIST_TABLE);
		$this->setColumnName('FestivalYear','FestivalYear');
		$this->setColumnName('ArtistID','ArtistID');
        $this->setColumnName('LineupOrder','LineupOrder');
        $this->setColumnName('ArtistIsActive','ArtistIsActive');
		$this->setColumnName('ArtistDescription','ArtistDescription');
		$this->setColumnName('ArtistLongDescription','ArtistLongDescription');
		$this->setColumnName('ArtistProgramDescription','ArtistProgramDescription');
		$this->setColumnName('ArtistBand','ArtistBand');
		$this->setColumnName('ArtistFee','ArtistFee');
		$this->setColumnName('ArtistFeeDescription','ArtistFeeDescription');
		$this->setColumnName('ArtistAccommodationDescription','ArtistAccommodationDescription'); 
		$this->setColumnName('ArtistGuests','ArtistGuests'); 
		$this->setColumnName('ArtistTechnicalRequirements','ArtistTechnicalRequirements'); 
		$this->setColumnName('ArtistDoNotPublish','ArtistDoNotPublish'); 
		$this->setColumnName('ArtistModifiedTimestamp','ArtistModifiedTimestamp'); 
		
        $this->addTableName('Artist',ARTIST_TABLE);
                
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
			  `ArtistID` int(6) NOT NULL,
                          `LineupOrder` int(4) NOT NULL,
                          `ArtistIsActive` int(1) NOT NULL default 0,
                          `ArtistDescription` text,
                          `ArtistLongDescription` text,
                          `ArtistProgramDescription` text,
						  `ArtistBand` int(7) default NULL,
						  `ArtistFee` varchar(25) default NULL,
						  `ArtistFeeDescription` text default NULL,
						  `ArtistAccommodationDescription` text default NULL,
						  `ArtistGuests` text default NULL,
						  `ArtistTechnicalRequirements` text default NULL,
						  `ArtistModifiedTimestamp` timestamp default NOW(),
						  `ArtistDoNotPublish` int(1) unsigned default 0, 
	          PRIMARY KEY  (`FestivalYear`,`ArtistID`),
              KEY (`LineupOrder`)
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
         
    function getLinkingWhereClause(){
            $ArtistContainer = new ArtistContainer();
            $wc = new whereClause();
            $wc->addCondition($this->getTableName() . "." . $this->getColumnName('ArtistID') . " = " . $ArtistContainer->getTableName() . "." . $ArtistContainer->getColumnName('ArtistID'));
            return $wc;
    }

	function getAllArtists($FestivalYear, $_sort_field = "", $_sort_dir = "asc"){
        $wc = $this->getLinkingWhereClause();
        $ArtistContainer = new ArtistContainer();
        if ($_sort_field == ""){
                $_sort_field = $this->getTableName('Main') . "." . $ArtistContainer->getColumnName('LineupOrder');
        }
        if ($FestivalYear != ""){
            $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('FestivalYear')." = ?",$FestivalYear);
        }
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistIsActive')." = 1");
		if (PEAR::isError($Objects = $this->getAllObjects($wc,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
		    /*
            $ArtistObjects = $Objects[$this->getTableName('Artist')];
            return $ArtistContainer->manufactureArtist($ArtistObjects);
            */
            return $this->manufactureFestivalArtist($Objects);
		}
		else{
			return null;
		}
	}
	
	function getLineup($FestivalYear, $_sort_field = "", $_sort_dir = "asc"){
	        $wc = $this->getLinkingWhereClause();
	        $ArtistContainer = new ArtistContainer();
	        if ($_sort_field == ""){
	                $_sort_field = $this->getTableName('Main') . "." . $ArtistContainer->getColumnName('LineupOrder');
	        }
	        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('FestivalYear')." = ?",$FestivalYear);
	        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistIsActive')." = 1");
	        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistDoNotPublish')." = 0");
	        $wc2 = new whereClause();
	        $wc2->setConnector('OR');
	        $wc2->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistBand')." = 0");
	        $wc2->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistBand')." is NULL");
	        $wc->addCondition($wc2);
			if (PEAR::isError($Objects = $this->getAllObjects($wc,$_sort_field, $_sort_dir))) return $Objects;
	

			if ($Objects){
    		    /*
                $ArtistObjects = $Objects[$this->getTableName('Artist')];
                return $ArtistContainer->manufactureArtist($ArtistObjects);
                */
                $Artists = $this->manufactureFestivalArtist($Objects);
                if (!is_array($Artists)){
                    $Artists = array($Artists->getParameter('ArtistID') => $Artists);
                }
                return $Artists;
			}
			else{
				return null;
			}
	}
	

	function getAllBandMembers($Year,$ArtistID,$IncludeMainAct = true){
		$wc = $this->getLinkingWhereClause();
		$wc->addCondition($this->getTableName('Main').'.'.$this->getColumnName('FestivalYear')." = ?",$Year);
		
		$wc2 = new whereClause();
		$wc2->setConnector("OR");
		$wc2->addCondition($this->getTableName('Main').'.'.$this->getColumnName('ArtistBand')." = ?",$ArtistID);
		if ($IncludeMainAct){
			$wc2->addCondition($this->getTableName('Main').'.'.$this->getColumnName('ArtistID')." = ?",$ArtistID);			
		}
		$wc->addCondition($wc2);
		
		if (PEAR::isError($Objects = $this->getAllObjects($wc))) return $Objects;
		
		$Artists = $this->manufactureFestivalArtist($Objects);
		
		if (is_a($Artists,'FestivalArtist')){
		    $Artists = array($Artists->getParameter('ArtistID') => $Artists);
		}
		
		if (is_array($Artists) and $IncludeMainAct){
			if (!function_exists('putMainActFirst')){
				function putMainActFirst($a, $b){
					if ($a->getParameter('ArtistBand') == ""){
						return -1;
					}
					elseif ($b->getParameter('ArtistBand') == ""){
						return 1;
					}
					elseif ($a->getParameter('ArtistFullName') == $b->getParameter('ArtistFullName')){
						// Shouldn't happen
						return 0;
					}
					else{
						return ($a->getParameter('ArtistFullName') < $b->getParameter('ArtistFullName')) ? -1 : 1;
					}
				}
			}
			uasort($Artists,"putMainActFirst");
			
		}
		return $Artists;
	}

	function getAllFestivals($ArtistID, $_sort_field = "", $_sort_dir = "asc"){
        $wc = $this->getLinkingWhereClause();
        $FestivalContainer = new FestivalContainer();
        if ($_sort_field == ""){
                $_sort_field = $this->getTableName('Main') . "." . $FestivalContainer->getColumnName('FestivalYear');
        }
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistID')." = ?",$ArtistID);
		if (PEAR::isError($Objects = $this->getAllObjects($wc,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
                $FestivalObjects = $Objects[$this->getTableName('Main')];
                $ret = array();
                foreach ($FestivalObjects as $Festival){
                        $ret[] = $Festival->getParameter('FestivalYear');
                }
                return $ret;
		}
		else{
			return null;
		}
	}
	
	function getAllFestivalInfo(&$Artist){
        $wc = $this->getLinkingWhereClause();
        $FestivalContainer = new FestivalContainer();
        $_sort_field = $this->getTableName('Main') . "." . $FestivalContainer->getColumnName('FestivalYear');
        $_sort_dir = "desc"; 
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistID')." = ?",$Artist->getParameter('ArtistID'));
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistIsActive')." = 1");
		if (PEAR::isError($Objects = $this->getAllObjects($wc,$_sort_field, $_sort_dir))) return $Objects;
		
	    $FestivalInfo = array();
		if ($Objects){
            $FestivalInfoObjects = $Objects[$this->getTableName('Main')];
            foreach ($FestivalInfoObjects as $FestivalInfoObject){
                $year = $FestivalInfoObject->getParameter('FestivalYear');
                $_tmp_Object = new Parameterized_Object();
                $_parms = $FestivalInfoObject->getParameters();
                foreach ($_parms as $key=>$value){
                    if ($key != 'FestivalYear' and $key != 'ArtistID' and $key != 'LineupOrder'){
                        $_tmp_Object->setParameter($key,$value);
                    }
                }
                $_tmp_Object->saveParameters();
                $FestivalInfo["$year"] = $_tmp_Object;
            }
		}
		$Artist->setParameter('FestivalInfo',$FestivalInfo);
	}
	
	function getAllFestivalYears(&$Artist){
        $wc = $this->getLinkingWhereClause();
        $FestivalContainer = new FestivalContainer();
        $_sort_field = $this->getTableName('Main') . "." . $FestivalContainer->getColumnName('FestivalYear');
        $_sort_dir = "desc"; 
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistID')." = ?",$Artist->getParameter('ArtistID'));
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistIsActive')." = 1");
		if (PEAR::isError($Objects = $this->getAllObjects($wc,$_sort_field, $_sort_dir))) return $Objects;
		
	    $FestivalYears = array();
		if ($Objects){
            $FestivalInfoObjects = $Objects[$this->getTableName('Main')];
            foreach ($FestivalInfoObjects as $FestivalInfoObject){
                $FestivalYears[] = $FestivalInfoObject->getParameter('FestivalYear');
            }
		}
		$Artist->setParameter('FestivalYears',$FestivalYears);
	}
	
	function updateFestivalInfo(&$Artist){
	    $FestivalInfoArray = $Artist->getParameter('FestivalInfo');
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());

	    foreach ($FestivalInfoArray as $year => $_object){
    		$update_parms = array();
			if (function_exists('apply_filters')){
				$_object->setParameter('FestivalYear',$year);
				$_object = apply_filters('update_FestivalArtist_object',$_object);
			}
		
    		$parms = $_object->getParameters();
		
    		foreach ($parms as $k=>$v){
    			// Only update if it's not the ID, if the column exists and if the value has changed
				// Also, don't add in the FestivalYear (in case I added it above)
    			if (array_key_exists($k,$this->colname) and ($v != $_object->getSavedParameter($k) or $_object->getSavedParameter($k) == "") and $k != 'FestivalYear'){
    				$update_parms[$k] = $v;
    			}
    		}

    		if (count($update_parms)){
    		    $wc = new whereClause();
    		    $wc->addCondition($this->getColumnName('FestivalYear')."= ?",$year);
    		    $wc->addCondition($this->getColumnName('ArtistID')."= ?",$Artist->getParameter('ArtistID'));
    		    $query = "";
    		    if($this->getObject($wc)){
    			    $query = $dbh->buildManipSQL($this->getTableName(),array_keys($update_parms),DB_AUTOQUERY_UPDATE);
    			    $query.= " WHERE ".$this->getColumnName('FestivalYear')."= ? AND ".$this->getColumnName('ArtistID')."=?";
    		    }
			    $update_parms['FestivalYear'] = sprintf($year); // Made this sprintf to avoid case where 2011 = '2011 (ArtsU)' in MySQL world
			    $update_parms['ArtistID'] = $Artist->getParameter('ArtistID');
			    
			    if ($query == ""){  // Meaning no row yet exists in the database -> create INSERT query
    			    $query = $dbh->buildManipSQL($this->getTableName(),array_keys($update_parms),DB_AUTOQUERY_INSERT);
    		    }
		
    			$sth = $dbh->prepare($query); 
    			//echo $dbh->executeEmulateQuery($sth,$update_parms);
			
    			$result = $dbh->execute($sth,array_values($update_parms));
    			if (DB::isError($result)){
    				return $result;
    			}
    		}
    		$_object->saveParameters();
    		$FestivalInfoArray[$year] = $_object;
	    }
	    $Artist->setParameter('FestivalInfo',$FestivalInfoArray);
	    return true;
	}
	
	function getFestivalArtist($Year,$Artist_id){
	    $wc = $this->getLinkingWhereClause();
	    $wc->addCondition($this->getTableName('Main').'.'.$this->getColumnName('ArtistID').' = ?',$Artist_id);
	    $wc->addCondition($this->getTableName('Main').'.'.$this->getColumnName('FestivalYear').' = ?',$Year);
		
		if (PEAR::isError($Object = $this->getAllObjects($wc))) return $Object;
		
		if ($Object){
            return $this->manufactureFestivalArtist($Object);
		}
		else{
			return null;
		}
	}
        
	function manufactureFestivalArtist($Object){
        // This expects there to be (at least) two elements in each Object 
        // The first is the FestivalArtist table data.  The second is the Artist table data
        $_Objects = $Object;
        if (!is_array($_Objects[$this->getTableName('Artist')])){
            $_Objects[$this->getTableName('Artist')] = array($_Objects[$this->getTableName('Artist')]);
            $_Objects[$this->getTableName('Main')] = array($_Objects[$this->getTableName('Main')]);
        }
        
        $FestivalArtists = array();
        foreach ($_Objects[$this->getTableName('Artist')] as $index => $_Object){
			if (is_a($_Object,'Parameterized_Object')){
	            $_tmp_Artist = ArtistContainer::manufactureArtist($_Object);

	            // Addresses a bug in FancyObjectContainer which doesn't name parameters correctly for non-Main tables
	            $_tmp_Artist->setParameter('ArtistDescription',$_tmp_Artist->getParameter('ArtistDefaultDescription'));
	            $_tmp_Artist->setParameter('ArtistLongDescription',$_tmp_Artist->getParameter('ArtistDefaultLongDescription'));
	            $_tmp_Artist->setParameter('ArtistProgramDescription',$_tmp_Artist->getParameter('ArtistDefaultProgramDescription'));
	            $_tmp_Artist->setParameter('ArtistDefaultDescription',null);
	            $_tmp_Artist->setParameter('ArtistDefaultLongDescription',null);

	            $_tmp_FestivalArtist = $_Objects[$this->getTableName('Main')][$index];
	            $_parms = $_tmp_FestivalArtist->getParameters();
	            foreach ($_parms as $key=>$value){
	                if (($key != 'ArtistDescription' and $key != 'ArtistLongDescription' and $key != 'ArtistProgramDescription') or $value != ""){
	                    $_tmp_Artist->setParameter($this->getParameterName($key),$value);
	                }
	            }

	            if (!$_tmp_Artist->getParameter('ArtistBand')){
	                $_tmp_Artist->setParameter('ArtistBand',"");
	            }

	            $_tmp_Artist = new FestivalArtist($_tmp_Artist->getParameter('ArtistID'),$_tmp_Artist->getParameters());


	            $_tmp_Artist->saveParameters();
	            if (array_key_exists($_tmp_Artist->getParameter($_tmp_Artist->getIDParameter()),$FestivalArtists)){
	                $__temp = $FestivalArtists[$_tmp_Artist->getParameter($_tmp_Artist->getIDParameter())];
	                if ($__temp->getParameter('AllFestivalYears') == ""){
	                    $__temp->setParameter('AllFestivalYears',array($__temp->getParameter('FestivalYear')));
	                }
					if (!in_array($_tmp_Artist->getParameter('FestivalYear'),$__temp->getParameter('AllFestivalYears'))){
		                $__temp->setParameter('AllFestivalYears',array_merge($__temp->getParameter('AllFestivalYears'),array($_tmp_Artist->getParameter('FestivalYear'))));
					}
	                $FestivalArtists[$_tmp_Artist->getParameter($_tmp_Artist->getIDParameter())] = $__temp;
	            }
	            else{
	                $FestivalArtists[$_tmp_Artist->getParameter($_tmp_Artist->getIDParameter())] = $_tmp_Artist;
	            }
			}
        }
        
        if (count($FestivalArtists) == 1){
                return array_shift($FestivalArtists);
        }
        else{
                return $FestivalArtists;
        }
    }	
    
    	
	function deleteFestivalArtist($FestivalYear = "",$ArtistID = ""){
		$wc = new whereClause();
                if ($FestivalYear != ""){
                        $wc->addCondition($this->getColumnName('FestivalYear')." = ?",$FestivalYear);
                }
                if ($ArtistID != ""){
                        $wc->addCondition($this->getColumnName('ArtistID')." = ?",$ArtistID);
                }
		
		return $this->deleteObject($wc);
	
	}
	
	function updateFestivalArtist($FestivalArtist){
		return $this->updateObject($FestivalArtist);
	}	
        
    function setFestivalLineup($FestivalYear, $ArtistIDs = array()){
            // We need to get the current lineup so we can remove any artists from shows who have been removed from the lineup
            $CurrentArtists = $this->getAllArtists($FestivalYear);
			set_time_limit(0); // For big lineups, this could take a while.  This could be more efficient....
            if (is_array($CurrentArtists)){
                foreach ($CurrentArtists as $ArtistID => $Artist){
                    if (!in_array($ArtistID,$ArtistIDs)){
                        // First, have to get all shows the artist is in this year
                        if (!$ShowContainer){
                            $ShowContainer = new ShowContainer();
                        }
                        $Shows = $ShowContainer->getAllShowsWithArtist($FestivalYear,$ArtistID);
                        if (is_array($Shows)){
                            foreach ($Shows as $Show){
                                $ShowContainer->deleteShowArtist($Show->getParameter('ShowID'),$ArtistID);
                            }
                        }
                    }
                }
            }
            // First, need to reset the current lineup
		    if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
		    $wc = new whereClause();
		    $wc->addCondition($this->getColumnName('FestivalYear')." = ?",$FestivalYear);
			$where_clause = " WHERE ".$wc->getSafeString();
			$where_parms = $wc->getValues();
		    $query = "UPDATE ".$this->getTableName('Main')." SET ".$this->getColumnName('ArtistIsActive')." = 0, ".$this->getColumnName('LineupOrder')." = 0 ".$where_clause;
			$sth = $dbh->prepare($query); 
			$result = $dbh->execute($sth,$where_parms);
			if (DB::isError($result)){
				return $result;
			}
			
            //Now, add all of the ArtistIDs in the passed Array
            $i = 0;
            foreach ($ArtistIDs as $ArtistID){
                $FestivalArtist = new FestivalArtist();
                if (PEAR::isError($result = $this->addFestivalArtist($FestivalYear,$ArtistID,$i))){
                        return $result;
                }
                $i++;
            }
    }

	function sortLineupByLastName($FestivalYear){
		$ArtistContainer = new ArtistContainer();
		$Lineup = $this->getAllArtists($FestivalYear,$this->getTableName('Artist') . "." . $ArtistContainer->getColumnName('ArtistLastName'));
		if (is_array($Lineup)){
			$this->setFestivalLineup($FestivalYear,array_keys($Lineup));
		}
	}

	function addFestivalArtist($FestivalYear,$Artist,$LineupOrder=""){
        $Object = new FestivalArtist();
        $Object->setParameter('FestivalYear',$FestivalYear);
        $Object->setParameter('LineupOrder',$LineupOrder);
        $Object->setParameter('ArtistIsActive',1);
        if (is_a($Artist,'Artist')){
                $artist_id = $Artist->getParameter('ArtistID');
        }
        else{
                $artist_id = $Artist;
        }
        $Object->setParameter('ArtistID',$artist_id);
		$result = $this->addObject($Object,true);
		if (PEAR::isError($result)){ // Object exists already, update it
		    // Unfortunately the DB_Object object isn't set up for a two-column Key, so we have to craete the call here
    		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
    		$query = "UPDATE ".$this->getTableName()." SET ".$this->getColumnName('LineupOrder'). " = ?, ".$this->getColumnName('ArtistIsActive')." = 1 WHERE ".$this->getColumnName('FestivalYear')." = ? AND ".$this->getColumnName('ArtistID')." = ?";
    		$update_parms= array ($LineupOrder,$FestivalYear,$artist_id);
    		$sth = $dbh->prepare($query); 
    		//echo $dbh->executeEmulateQuery($sth,$update_parms);
			
			$result = $dbh->execute($sth,array_values($update_parms));
			if (DB::isError($result)){
				return $result;
			}
		}
        return true;
	}
	
	
}

?>