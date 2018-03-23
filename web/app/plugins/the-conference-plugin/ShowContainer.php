<?php

require_once(PACKAGE_DIRECTORY."Common/FancyObjectContainer.php");
require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("ArtistContainer.php");
require_once("FestivalContainer.php");
require_once("FestivalArtistContainer.php");
require_once("ScheduleContainer.php");
require_once("Show.php");

define ('SHOW_LISTING_DAY_STAGE_TIME',1);
define ('SHOW_LISTING_STAGE_DAY_TIME',2);

if (!defined ('SHOW_ARTIST_TABLE')){
    define('SHOW_ARTIST_TABLE',DATABASE_PREFIX."ShowArtists");
}

if (!defined ('SHOW_TABLE')){
    define('SHOW_TABLE',DATABASE_PREFIX."Shows");
}

class ShowContainer extends FancyObjectContainer{

	var $tablename;
	var $ArtistContainer;
	var $SimpleShowContainer;
	var $FestivalContainer;
    var $blank_cell_content = '&nbsp;';
	
	function ShowContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(SHOW_ARTIST_TABLE);
		$this->setColumnName('ShowID','ShowID');
		$this->setColumnName('ArtistID','ArtistID');
        $this->setColumnName('ArtistIsHost','ArtistIsHost');
        $this->setColumnName('ArtistOrder','ArtistOrder');

        $this->addTableName('Artist',ARTIST_TABLE);
        $this->addTableName('FestivalArtist',FESTIVAL_ARTIST_TABLE);
        $this->addTableName('Show',SHOW_TABLE);
        $this->addTableName('Schedule',SCHEDULE_TABLE);
                
		if (!$this->tableExists()){
			$this->initializeTable();
		}
		$this->ArtistContainer = new ArtistContainer();
		$this->ScheduleContainer = new ScheduleContainer();
		$this->SimpleShowContainer = new SimpleShowContainer();
		$this->FestivalContainer = new FestivalContainer();
		$this->FestivalArtistContainer = new FestivalArtistContainer();
		$this->Festival = null;
		
		$this->default_sort_field = array();
		$this->default_sort_field[] = $this->getTableName('Show').".".$this->SimpleShowContainer->getColumnName('ShowDay');
		$this->default_sort_field[] = $this->getTableName('Show').".".$this->SimpleShowContainer->getColumnName('ShowStage');
		$this->default_sort_field[] = $this->getTableName('Show').".".$this->SimpleShowContainer->getColumnName('ShowStartTime');
		$this->default_sort_field[] = $this->getTableName('Show').".".$this->SimpleShowContainer->getColumnName('ShowID');
		$this->default_sort_field[] = $this->getTableName('Main').".".$this->ArtistContainer->getColumnName('ArtistIsHost');
		$this->default_sort_field[] = $this->getTableName('Main').".".$this->ArtistContainer->getColumnName('ArtistOrder');
		$this->default_sort_field[] = $this->getTableName('Artist').".".$this->ArtistContainer->getColumnName('ArtistFullName');
		$this->default_sort_dir = array('asc','asc','asc','asc','desc','asc','asc');
	}
	
	function initializeTable(){
		$this->ensureTableExists();
		
	}
	
	function ensureTableExists(){
		$create_query="
			CREATE TABLE `".$this->getTableName()."` ( 
			  `ShowID` int(6) NOT NULL,
			  `ArtistID` int(6) NOT NULL,
			  `ArtistIsHost` int(1),
			  `ArtistOrder` int(6) UNSIGNED DEFAULT NULL,
			  PRIMARY KEY  (`ShowID`,`ArtistID`)
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
            $wc = new whereClause();
            $wc->addCondition($this->getTableName() . "." . $this->getColumnName('ArtistID') . " = " . $this->getTableName('Artist') . "." . $this->ArtistContainer->getColumnName('ArtistID'));
            $wc->addCondition($this->getTableName() . "." . $this->getColumnName('ShowID') . " = " . $this->getTableName('Show') . "." . $this->SimpleShowContainer->getColumnName('ShowID'));
            $wc->addCondition($this->getTableName() . "." . $this->getColumnName('ArtistID') . " = " . $this->getTableName('FestivalArtist') . "." . $this->FestivalArtistContainer->getColumnName('ArtistID'));
            $wc->addCondition($this->getTableName('Show') . "." . $this->getColumnName('ShowYear') . " = " . $this->getTableName('FestivalArtist') . "." . $this->FestivalArtistContainer->getColumnName('FestivalYear'));
            $wc->addCondition($this->getTableName('Schedule') . "." . $this->ScheduleContainer->getColumnName('ScheduleUID') . " = " . $this->getTableName('Show') . "." . $this->SimpleShowContainer->getColumnName('ShowScheduleUID'));
            return $wc;
    }

	function addShow(&$Show){
			return $this->SimpleShowContainer->addShow($Show);
	}
	
	function updateShow($Show){
		return $this->SimpleShowContainer->updateShow($Show);
	}
	
    function setShowLineup($ShowID, $ArtistIDs = array(),$hostArtistID = ""){
		$ArtistIDs = array_filter($ArtistIDs,create_function('$a','return !empty($a);'));
		if (empty($ArtistIDs)){
			// nothing to add
			return true; 
		}
            // First, need to reset the current lineup
            $wc = new whereClause();
            $wc->addCondition($this->getColumnName('ShowID')." = ?",$ShowID);
            if (PEAR::isError($result = $this->deleteObject($wc))){
                    return $result;
            }
            
            //Now, add all of the ArtistIDs in the passed Array
            foreach ($ArtistIDs as $order => $ArtistID){
                    if (PEAR::isError($result = $this->addShowArtist($ShowID,$ArtistID,$ArtistID == $hostArtistID,$order))){
                            return $result;
                    }
            }
    }

	function sortLineupByLastName($ShowID){
		$Show = $this->getShow($ShowID,'ArtistLastName','asc');
		if (is_array($Show->getParameter('ShowArtists'))){
			$ArtistIDs = array_keys($Show->getParameter('ShowArtists'));
			$this->setShowLineup($ShowID,$ArtistIDs,$Show->getParameter('ShowHostArtistID'));
		}
	}
    
	function addShowArtist($show_id,$Artist,$ArtistIsHost,$order=0){
	        $Object = new Parameterized_Object();
	        $Object->setParameter('ShowID',$show_id);
	        if ($ArtistIsHost){
	                $Object->setParameter('ArtistIsHost',1);
	        }
	        else{
	                $Object->setParameter('ArtistIsHost',0);
	        }
	        if (is_a($Artist,'Artist')){
	                $artist_id = $Artist->getParameter('ArtistID');
	        }
	        else{
	                $artist_id = $Artist;
	        }
	        $Object->setParameter('ArtistID',$artist_id);
	        $Object->setParameter('ArtistOrder',$order);
			return $this->addObject($Object);
	}
	
	function getShow($show_id,$ArtistSort = "",$ArtistSortDir = "asc"){
		
			// If there's a lineup, then this will return the Show with the properly formatted Lineup, etc.
			$wc = $this->getLinkingWhereClause();
	        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ShowID')." = ?",$show_id);
			if ($ArtistSort != ""){
			    if (!is_array($ArtistSort)){
			        $ArtistSort = array($ArtistSort);
			        $ArtistSortDir = array($ArtistSortDir);
			    }
			    $_sort_field = array();
			    $_sort_dir = array();
			    foreach ($ArtistSort as $i => $asort){
			        $_sort_field[] = $this->getTableName('Artist').".".$this->ArtistContainer->getColumnName($asort);
			        $_sort_dir[] = $ArtistSortDir[$i];
			    }
			}
			else{
		        $_sort_field[] = $this->getTableName('Main').".".$this->getColumnName('ArtistOrder');
		        $_sort_dir[] = 'asc';
			}
			if (PEAR::isError($Objects = $this->getAllObjects($wc,$_sort_field, $_sort_dir))) return $Objects;
			if ($Objects){
				return array_shift($this->manufactureShow($Objects));
			}
			else{
				// No lineup set yet, just attempt to get the SimpleShow
				return $this->SimpleShowContainer->getShow($show_id);
			}
	}
	
    function manufactureShow($Object,$debug = false){
            if (!is_array($Object)){
                    $Objects = array($Object);
            }
            else{
                    $Objects = $Object;
            }
            
			$CurrentShowID = "";
			$Shows = array();
		    $Artists = array();
		    
			if (is_array($Object[$this->getTableName('Show')])){
    			foreach ($Object[$this->getTableName('Show')] as $index => $results){
    				if ($CurrentShowID != $results->getParameter('ShowID')){
    					if ($Show){
    						$this->prettifyShow($Show);
    						$Shows[$Show->getParameter('ShowID')] = $Show;
    					}
    					$Show = $this->SimpleShowContainer->manufactureShow($results);
    					$CurrentShowID = $Show->getParameter('ShowID');
    				}
    				
    				
    				
    				$tmp_Artist = $Object[$this->getTableName('Artist')][$index];
    				if (empty($Artists[$tmp_Artist->getParameter('ArtistID')])){
						/* Trying a new way - less memory intensive
    				    $SpecialObject = array();
    				    $SpecialObject[$this->getTableName('Artist')] = $Object[$this->getTableName('Artist')][$index];
    				    $SpecialObject[$this->getTableName('FestivalArtist')] = $Object[$this->getTableName('FestivalArtist')][$index];
    					$Artists[$tmp_Artist->getParameter('ArtistID')] = $this->FestivalArtistContainer->manufactureFestivalArtist($SpecialObject);
						*/
						$SimpleArtist = new Artist();
						$SimpleArtist->setParameter('ArtistID',$tmp_Artist->getParameter('ArtistID'));
						$SimpleArtist->setParameter('ArtistFullName',$tmp_Artist->getParameter('ArtistFullName'));
						unset($SimpleArtist->params_saved);
						$Artists[$tmp_Artist->getParameter('ArtistID')] = $SimpleArtist;
    				}
    				$ShowArtists = $Show->getParameter('ShowArtists');
    				$ShowArtistNames = $Show->getParameter('ShowArtistNames');
    				if (!is_array($ShowArtists)){
    					$ShowArtists = array();
    					$ShowArtistNames = array();
    				}
    				$ShowArtists[$tmp_Artist->getParameter('ArtistID')] = $Artists[$tmp_Artist->getParameter('ArtistID')];
    				$ShowArtistNames[] = $tmp_Artist->getParameter('ArtistFullName');
    				$Show->setParameter('ShowArtists',$ShowArtists);
    				$Show->setParameter('ShowArtistNames',$ShowArtistNames);
    				if ($Object[$this->getTableName('Main')][$index]->getParameter('ArtistIsHost')){
    				    $Show->setParameter('ShowHostArtistID',$tmp_Artist->getParameter('ArtistID'));
    				}
    			}
    			if ($Show){
    				$this->prettifyShow($Show);
    				$Shows[$Show->getParameter('ShowID')] = $Show;
    			}
    		}
    		
            if (!is_array($Object)){
                    return array_shift($Shows);
            }
            else{
                    return $Shows;
            }
    }	

	function prettifyShow(&$Show){

        // Set Some variables that will be handy
			if ($Show->getParameter('ShowTitle') == ""){
				$Show->setParameter('ShowTitle', $Show->getArtistNames());
			}

	}

	function getArtist($artist_id){
		return $this->ArtistContainer->getArtist($artist_id);
	}
	
	function getAllShowsWithArtist($Year,$ArtistID,$ScheduleUID = "",$ShowSortField = array('ShowDay','ShowStartTime','ShowStage'),$ShowSortDir = array('asc','asc','asc'),$OnlyPublished = true){
        $wc = $this->getLinkingWhereClause();
        $wc->addCondition($this->getTableName('Main').".".$this->getColumnName('ArtistID')." = ?",$ArtistID);			
        $wc->addCondition($this->getTableName('Show').".".$this->getColumnName('ShowYear')." = ?",$Year);			
		if ($OnlyPublished){
			$wc->addCondition($this->getTableName('Schedule').".".$this->ScheduleContainer->getColumnName('ScheduleIsPublished').' = 1'); // Only get published shows
		}
		if ($ScheduleUID != ""){
		    if (!is_array($ScheduleUID)){
		        $ScheduleUID = array($ScheduleUID);
		    }
			if (count($ScheduleUID)){
				// Only get shows where the schedule has been published
			    $wc2 = new whereClause();
			    $wc2->setConnector('OR');
			    foreach ($ScheduleUID as $t){
				    $wc2->addCondition($this->getTableName('Show').".".$this->SimpleShowContainer->getColumnName('ShowScheduleUID')." = ?",$t);
			    }
			    $wc->addCondition($wc2);
			}
			else{
				// There are no schedules to lookup, so therefore, return an empty array
				return array();
			}
		}
		if (PEAR::isError($Objects = $this->getAllObjects($wc,$ShowSortField, $ShowSortDir))){
			return $Objects;
		} 

		if ($Objects){
			$Shows = $this->manufactureShow($Objects);
			$ShowIDs = array();
			foreach ($Shows as $Show){
				$ShowIDs[] = $Show->getParameter('ShowID');
			}
			$wc = new whereClause();
			$wc->addCondition($this->getTableName('Show').".".$this->SimpleShowContainer->getColumnName('ShowID').' in (?'.str_repeat(',?',count($ShowIDs) - 1).')',$ShowIDs);
			$Shows = $this->getAllShowsWhere($wc,$ShowSortField,$ShowSortDir);
			return $Shows;
		}
		else{
			return array();
		}
	}
	
	function getAllBandMemberShows($Year,$ArtistID,$IncludeMainAct = true,$ScheduleUID = "",$ShowSortField = array('ShowDay','ShowStartTime','ShowStage'),$ShowSortDir = array('asc','asc','asc')){
		// This function returns all shows where a band member of the ArtistID band is booked in a show
		$BandMembers = $this->FestivalArtistContainer->getAllBandMembers($Year,$ArtistID,$IncludeMainAct);
		$BandMemberShows = array();
		if (is_array($BandMembers)){
			foreach ($BandMembers as $BandMember){
				$Shows = $this->getAllShowsWithArtist($Year,$BandMember->getParameter('ArtistID'),$ScheduleUID,$ShowSortField,$ShowSortDir);
				$BandMemberShows[$BandMember->getParameter('ArtistID')] = array("Artist" => $BandMember,"Shows" => $Shows);
			}
		}
		return $BandMemberShows;
	}
	
	function getAllShows($ShowYear = "",$ScheduleUID = "",$ShowSortField = array('ShowDay','ShowStage','ShowStartTime','ShowID'),$ShowSortDir = array('asc','asc','asc','asc'),$ArtistSortField = 'ArtistOrder',$ArtistSortDir = 'asc',$PutHostFirst = true){
		// Here's a tricky one.  We want to get back all Shows regardless of whether they have a lineup set or not.
		// The way we're going to do it is to get all SimpleShows first (sans lineup) and then use those Show IDs
		// to get the Show Lineups. 
		$wc = new whereClause();
		if ($ShowYear != ""){
			$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowYear')." = ?",$ShowYear);
		}
		if ($ScheduleUID != ""){
		    if (!is_array($ScheduleUID)){
		        $ScheduleUID = array($ScheduleUID);
		    }
		    $wc2 = new whereClause();
		    $wc2->setConnector('OR');
		    foreach ($ScheduleUID as $t){
			    $wc2->addCondition($this->SimpleShowContainer->getColumnName('ShowScheduleUID')." = ?",$t);
		    }
			$wc->addCondition($wc2);
		}
		$AllShows = $this->SimpleShowContainer->getAllShows($wc,$ShowSortField,$ShowSortDir);
		if (!is_array($AllShows)){ // No Shows Found
			return array();
		}
		else{
			return $this->getLineupsForSimpleShows($AllShows,$ShowSortField,$ShowSortDir,$ArtistSortField,$ArtistSortDir,$PutHostFirst);
		}
	}
	
	function getAllShowsWhere($whereClause = "",$ShowSortField = array('ShowDay','ShowStage','ShowStartTime','ShowID'),$ShowSortDir = array('asc','asc','asc','asc'),$ArtistSortField = 'ArtistOrder',$ArtistSortDir = 'asc',$PutHostFirst = true){
		$AllShows = $this->SimpleShowContainer->getAllShows($whereClause,$ShowSortField,$ShowSortDir);
		if (!is_array($AllShows)){ // No Shows Found
			return array();
		}
		else{
			return $this->getLineupsForSimpleShows($AllShows,$ShowSortField,$ShowSortDir,$ArtistSortField,$ArtistSortDir,$PutHostFirst);
		}
	}
		
	function getLineupsForSimpleShows($AllShows,$ShowSortField = array('ShowDay','ShowStage','ShowStartTime','ShowID'),$ShowSortDir = array('asc','asc','asc','asc'),$ArtistSortField = 'ArtistOrder',$ArtistSortDir = 'asc',$PutHostFirst = true){
		// I ran into memory problems with this when doing ALL SHOWS at a time (when the number of shows is O(2000))
		// So, instead of getting lineups for simple shows all at once, we'll do it in batch
		$BatchSize = 250;
		for ($s = 0; $s < count($AllShows); $s += $BatchSize){
			$ShowIDs = array_slice(array_keys($AllShows),$s,$BatchSize);
		
			// Now, get all of the ShowArtists for the found Shows.  
	        $wc = $this->getLinkingWhereClause();
			$wc->addCondition($this->getTableName() . "." . $this->getColumnName('ShowID')." in (".implode(",",$ShowIDs).")");
		
			if (!is_array($ShowSortField)){
				$ShowSortField = array($ShowSortField);
				$ShowSortDir = array($ShowSortDir);
			}
			if (!is_array($ArtistSortField)){
				$ArtistSortField = array($ArtistSortField);
				$ArtistSortDir = array($ArtistSortDir);
			}
			$_sort_field = array();
			$_sort_dir = array();
			foreach ($ShowSortField as $index => $field){
				$sort_field[] = $this->getTableName('Show').".".$field;
				$sort_dir[] = $ShowSortDir[$index];
			}
			if ($PutHostFirst){
			    $sort_field[] = $this->getTableName('Main').".".$this->getColumnName('ArtistIsHost');
				$sort_dir[] = 'desc';
			}
			foreach ($ArtistSortField as $index => $field){
				$sort_field[] = $this->getTableName(($field == 'ArtistOrder' ? 'Main' : 'Artist')).".".$field;
				$sort_dir[] = $ArtistSortDir[$index];
			}
			if (PEAR::isError($Objects = $this->getAllObjects($wc,$sort_field, $sort_dir))) return $Objects;
			$AllShowsWithLineup = $this->manufactureShow($Objects);
		
			// Finally, for all of the Shows with lineups, replace the Show in the AllShows array with the Lineup'ed one. 
			if (is_array($AllShowsWithLineup)){
	    		foreach ($AllShowsWithLineup as $ShowID => $Show){
	    			$AllShows[$ShowID] = $Show;
	    		}
	    	}
		}

		return $AllShows;
	}
	
	function deleteShow($ShowID){
		// First, delete all of the ShowArtists for this show
		$wc = new whereClause();
		$wc->addCondition($this->getColumnName('ShowID')." = ?",$ShowID);
		if (PEAR::isError($result = $this->deleteObject($wc))) return ($result);
		
		// Now, delete the Show
		return $this->SimpleShowContainer->deleteShow($ShowID);
	}
	
    function deleteShows($Year,$ScheduleUID){
        $wc = new whereClause();
        $wc->addCondition($this->SimpleShowContainer->getColumnName('ShowYear').'=?',$Year);
        $wc->addCondition($this->SimpleShowContainer->getColumnName('ShowScheduleUID').'=?',$ScheduleUID);
        
        $Shows = $this->SimpleShowContainer->getAllShows($wc);
        
        if (is_array($Shows)){
            foreach ($Shows as $Show){
                $result = $this->deleteShow($Show->getParameter('ShowID'));
                if (PEAR::isError($result)){
                    return $result;
                }
            }
        }
        return true;
    }
    
	function deleteShowArtist($ShowID,$ArtistID = ""){
		$wc = new whereClause();
        if ($ShowID != ""){
                $wc->addCondition($this->getColumnName('ShowID')." = ?",$ShowID);
        }
        if ($ArtistID != ""){
                $wc->addCondition($this->getColumnName('ArtistID')." = ?",$ArtistID);
        }

		return $this->deleteObject($wc);
	
	}
        
	function deleteArtist($ArtistID){
	    // Remove all references to the artist
		$wc = new whereClause();
		$wc->addCondition($this->getColumnName('ArtistID')." = ?",$ArtistID);
		return $this->deleteObject($wc);
	}
	
        function getShowListingsArray($ScheduleUID,$Year,$Day = ""){
			/************************
			This function returns an array with all of the listings of a particular type (Workshop or Concert) in a given year.
			The array is formatted such that it can be used easily to create a pretty looking schedule.
			
			The format is 
			
			return = array(
				    "PrettyHeading" => string - {a nice usable form of the heading, i.e. "Friday"}
					"Headings"      => array - {column titles for the table under "PrettyHeading", i.e. "Main Stage"}
					"HeadingSponsors" => array - {sponsors for the Headings}
					"Times"			=> array - {indexed by Canonical Time, a list of Pretty Times for the day.  
						                        Takes all stages into account, and increments the times by the Resolution set in Schedules table}
					"Resolution"	=> string - resolution (in minutes) of the listings array
					"Shows"			=> array - {shows appearing on that day.  Indexed by ShowStage and ShowStartTime}
				)
			
			
			************************/
                $Festival = $this->FestivalContainer->getFestival($Year);
                $StageNames = $Festival->getStageNamesArray($ScheduleUID);
                $AllStageTimes = $Festival->getStageTimesArray($ScheduleUID);
                $AllStageSponsors = $Festival->getStageSponsorsArray($ScheduleUID);
                $Resolution = $Festival->getStageTimesResolution($ScheduleUID);
                $AllStageTimes[""] = array();
                
                $Bootstrap = Bootstrap::getBootstrap();
	            $Bootstrap->addTimestamp('Getting All Shows for '.$ScheduleUID);
				if ($Day === null){
					$wc = new whereClause();
					$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowYear')." = ?",$Year);
					$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowScheduleUID')." = ?",$ScheduleUID);
					$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowDay')." is NULL");
					$Shows = $this->getAllShowsWhere($wc);
				}
				elseif ($Day !== ""){
					$wc = new whereClause();
					$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowYear')." = ?",$Year);
					$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowScheduleUID')." = ?",$ScheduleUID);
					$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowDay')." = ?",$Day);
					$Shows = $this->getAllShowsWhere($wc);
				}
				else{
					$Shows = $this->getAllShows($Year,$ScheduleUID);
				}
	            $Bootstrap->addTimestamp('Got All Shows for '.$ScheduleUID);

                if (!is_array($Shows) or !count($Shows)){     
						$ScheduleContainer = new ScheduleContainer();
						$Schedule = $ScheduleContainer->getSchedule($Year,$ScheduleUID);
                        $ShowListings = "No {$Schedule->getParameter('ScheduleID')}s Added Yet";
                }
                else{
                
                        // Create the proper headings.  
                        $Headings = array("ShowDay" => array(), "ShowStage" => array());
                        foreach ($StageNames as $Day => $StageArray){
                                if (count($StageArray)){
                                        $Headings["ShowDay"][$Day] = $Festival->getPrettyDay($Day);
                                        $Headings["ShowStage"][$Day] = array();
                                        foreach ($StageArray as $Stage => $StageName){
                                                $Headings["ShowStage"][$Day][$Stage] = $StageName;
                                        }
                                }
                        }

                        $CurrentField1 = "-1"; // Initialize
						$Field1 = 'ShowDay'; // Maybe I'll make it so I can sort in other ways
						$Field2 = 'ShowStage';
                        $ShowListings = array();
                        foreach ($Shows as $Show){
	
								unset($Show->params_saved); // Don't need these....

                                if ($Show->getParameter('ShowDay') == ""
                                or  $Show->getParameter('ShowStage') == ""
                                or  $Show->getParameter('ShowStartTime') == ""){
                                        $Index = "Unassigned";
                                        if (empty($ShowListings[$Index])){
                                                $ShowListings[$Index] = array();
                                                $ShowListings[$Index]["Headings"] = $Index;
                                                $ShowListings[$Index]["Shows"] = array();
                                        }
                                        $ShowListings[$Index]["Shows"][$Show->getParameter('ShowID')] = @$Show;
                                }
                                else{
                                        if ($Show->getParameter($Field1) != $CurrentField1 ){
                                                $CurrentField1 = $Show->getParameter($Field1);
                                                
                                                $Index = $Show->getParameter($Field1);
                                                $ShowListings[$Index] = array();
                                                $ShowListings[$Index]["PrettyHeading"] = $Show->getParameter('ShowPrettyDay');
                                                if ($Field1 == 'ShowDay'){
                                                        $ShowListings[$Index]["Headings"] = $Headings['ShowStage'][$Show->getParameter('ShowDay')];
                                                        $ShowListings[$Index]["HeadingSponsors"] = array();
                                                        foreach ($AllStageSponsors as $Stage => $Sponsors){
                                                            $ShowListings[$Index]["HeadingSponsors"][$Stage] = $Sponsors[$Show->getParameter('ShowDay')];
                                                        }
                                                }
                                                else{
                                                        $ShowListings[$Index]["Headings"] = $Headings['ShowDay'];
                                                }
                                                $ShowListings[$Index]["Shows"] = array();
                                                $StageTimes = array();
                                                if ($Field1 == 'ShowStage'){
                                                        foreach ($AllStageTimes[$CurrentField1] as $Day => $DayTimes){
                                                                if (!is_array($DayTimes)){
                                                                        $DayTimes = array();
                                                                }
                                                                foreach ($DayTimes as $CanonicalTime => $PrettyTime){
                                                                        $StageTimes[$CanonicalTime] = $PrettyTime;
                                                                }
                                                        }
                                                }
                                                else{
                                                        foreach ($AllStageTimes as $Stage => $Ignore){
                                                                $DayTimes = $AllStageTimes[$Stage][$CurrentField1];
                                                                if (!is_array($DayTimes)){
                                                                        $DayTimes = array();
                                                                }
                                                                foreach ($DayTimes as $CanonicalTime => $PrettyTime){
                                                                        $StageTimes[$CanonicalTime] = $PrettyTime;
                                                                }
                                                        }
                                                }
												// don't show the ending time on the schedule
												ksort($StageTimes);
                                                array_pop($StageTimes);
                                                $ShowListings[$Index]["Times"] = $StageTimes;
                                                $ShowListings[$Index]["Resolution"] = $Resolution;
                                                
                                        }
                                        if (empty($ShowListings[$Index]["Shows"][$Show->getParameter($Field2)])){
                                                $ShowListings[$Index]["Shows"][$Show->getParameter($Field2)] = array();
                                        }
                                        if (!in_array($Show->getParameter('ShowStartTime'),array_keys($StageTimes))){
                                            // The start time isn't in the canonical times that the listings are returning.  
                                            // That being the case, this perfectly legitimate show wouldn't show up on the schedule
                                            // So, I'll find the closest time and spoof the show as being there, adding a parameter called
                                            // 'ShowStartTimeSpoofed'  
                                            foreach ($StageTimes as $CanonicalTime => $PrettyTime){
                                                if ($CanonicalTime > $Show->getParameter('ShowStartTime')){
                                                    break;
                                                }
                                                $SpoofTime = $CanonicalTime;
                                            }
                                            $Show->setParameter('ShowStartTimeSpoofed',true);
                                            $tmpTime = $SpoofTime;
                                        }
                                        else{
                                            $tmpTime = $Show->getParameter('ShowStartTime');
                                        }

										$_Show = new Show();
										unset($_Show->params_saved);
										foreach ($Show->getParameters() as $key => $value){
											$_Show->setParameter($key,$value);
										}

                                        if (!isset($ShowListings[$Index]["Shows"][$Show->getParameter($Field2)][$tmpTime])){
                                            $ShowListings[$Index]["Shows"][$Show->getParameter($Field2)][$tmpTime] = $_Show;
                                        }
                                        else{
                                            if (!is_array($ShowListings[$Index]["Shows"][$Show->getParameter($Field2)][$tmpTime])){
                                                $ShowListings[$Index]["Shows"][$Show->getParameter($Field2)][$tmpTime] = array($ShowListings[$Index]["Shows"][$Show->getParameter($Field2)][$tmpTime]);
                                            }
                                            $ShowListings[$Index]["Shows"][$Show->getParameter($Field2)][$tmpTime][] = $_Show;
                                        }
                                }
                        }
                }
				unset($Shows);
                return $ShowListings;
        }
        
	function getConflicts($Show){
	    return $this->SimpleShowContainer->getConflicts($Show);
	}
	
	function getEmbeddedShows($Show){
		if ($Show->getParameter('ShowEmbeddedScheduleUID') === '' or $Show->getParameter('ShowEmbeddedStageID') === ''){
			return null;
		}
		$wc = new whereClause();
		$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowScheduleUID').' = ?',$Show->getParameter('ShowEmbeddedScheduleUID'));
		$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowStage').' = ?',$Show->getParameter('ShowEmbeddedStageID'));
		$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowDay').' = ?',$Show->getParameter('ShowDay'));
		$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowStartTime').' >= ?',$Show->getParameter('ShowStartTime'));
		$wc->addCondition($this->SimpleShowContainer->getColumnName('ShowStartTime').' <= ?',$Show->getParameter('ShowEndTime'));
		return $this->getAllShowsWhere($wc);
	}
    
}

/*******************************************************************
*  SimpleShowContainer is the Object container for _just_ the Show
*  information.  After a couple of attempts to keep the ShowContainer
*  and ShowArtistContainers separate, I finally figured out that it 
*  just makes sense to have a single ShowContainer (the old ShowArtistContainer)
*  that knows how to add/delete shows, set lineups, get the Artist information
*  etc.  So, that's what this new ShowContainer is.  
*******************************************************************/

class SimpleShowContainer extends ObjectContainer{

	var $tablename;
	var $FestivalContainer;
	var $ScheduleContainer;
	
	function SimpleShowContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(SHOW_TABLE);
		$this->setColumnName('ShowID','ShowID');
		$this->setColumnName('ShowYear','ShowYear');
		$this->setColumnName('ShowType','ShowType');
		$this->setColumnName('ShowScheduleUID','ShowScheduleUID');
		$this->setColumnName('ShowTitle','ShowTitle');
		$this->setColumnName('ShowDescription','ShowDescription');
		$this->setColumnName('ShowNotesToArtist','ShowNotesToArtist');
		$this->setColumnName('ShowStage','ShowStage');
		$this->setColumnName('ShowDay','ShowDay');
		$this->setColumnName('ShowStartTime','ShowStartTime');
		$this->setColumnName('ShowEndTime','ShowEndTime');
		$this->setColumnName('ShowSponsor','ShowSponsor');
		$this->setColumnName('ShowRepeatOfShowID','ShowRepeatOfShowID');
		$this->setColumnName('ShowEmbeddedScheduleUID','ShowEmbeddedScheduleUID');
		$this->setColumnName('ShowEmbeddedStageID','ShowEmbeddedStageID');		
		$this->setColumnName("ShowModifiedTimestamp","ShowModifiedTimestamp");
		
		if (!$this->tableExists()){
			$this->initializeTable();
		}
		
		$this->FestivalContainer = new FestivalContainer();
		$this->ScheduleContainer = new ScheduleContainer();
	}
	
	function initializeTable(){
		$this->ensureTableExists();
		
	}
	
	function ensureTableExists(){
                // The Times are 24 hour clock, and will allow a time like 2600 to mean 2am that night
		$create_query="
			CREATE TABLE `".$this->getTableName()."` ( 
				  `ShowID` int(6) unsigned AUTO_INCREMENT NOT NULL
				, `ShowYear` varchar(25) NOT NULL
				, `ShowType` varchar(50) DEFAULT NULL
				, `ShowScheduleUID` int(6) unsigned DEFAULT NULL
				, `ShowTitle` varchar(255) DEFAULT NULL
				, `ShowDescription` text DEFAULT NULL
				, `ShowNotesToArtist` text DEFAULT NULL
				, `ShowStage` int(2) unsigned DEFAULT NULL
				, `ShowDay` int(2) unsigned DEFAULT NULL
				, `ShowStartTime` char(4) DEFAULT NULL  
				, `ShowEndTime` char(4) DEFAULT NULL
				, `ShowSponsor` varchar(255) DEFAULT NULL
				, `ShowRepeatOfShowID` int(6) unsigned default null
				, `ShowEmbeddedScheduleUID` int(6) unsigned DEFAULT NULL
				, `ShowEmbeddedStageID` int(2) unsigned DEFAULT NULL
				, `ShowModifiedTimestamp` timestamp default NOW()
				, PRIMARY KEY  (`ShowID`)
				, KEY (`ShowYear`)
				, KEY (`ShowStage`,`ShowDay`,`ShowStartTime`)
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
	
	function addShow(&$Show){
		$this->setTimestamp($Show);
		return $this->addObject($Show,true);
	}
	
	function updateShow($Show){
		$this->setTimestamp($Show);
		return $this->updateObject($Show);
	}
	
	function setTimestamp(&$Show){
		$Show->setParameter('ShowModifiedTimestamp', date("Y-m-d H:i:s"));
	}
	
	function ShowExists($Show_id){
		$Show = $this->getShow($Show_id);
		if (PEAR::isError($Show)){
			return $Show;
		}
		else{
			if ($Show) return true; else return false;
		}
		
	}
	
	function getAllShows($whereClause = "",$_sort_field = array('ShowDay','ShowStage','ShowStartTime'), $_sort_dir = array('asc','asc','asc')){
		if (PEAR::isError($Objects = $this->getAllObjects($whereClause,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
                        return $this->manufactureShow($Objects);
		}
		else{
			return null;
		}
	}

        function manufactureShow($Object){
                if (!is_array($Object)){
                        $_Objects = array($Object);
                }
                else{
                        $_Objects = $Object;
                }
                
                $Shows = array();
                foreach ($_Objects as $_Object){
                        $_tmp_Show = new Show();
                        $_parms = $_Object->getParameters();
                        foreach ($_parms as $key=>$value){
                                $_tmp_Show->setParameter($key,$value);
                        }
                        $id = $_tmp_Show->getParameter($_tmp_Show->getIDParameter());                                
						$this->prettifyShow($_tmp_Show);
                        $_tmp_Show->saveParameters();
                        $Shows[$id] = $_tmp_Show;
                }
                
                if (!is_array($Object)){
                        return array_shift($Shows);
                }
                else{
                        return $Shows;
                }
        }	
        
		function prettifyShow(&$Show){

	        // Set Some variables that will be handy
				if (empty($this->Festival) or $this->Festival->getParameter('FestivalYear') != $Show->getParameter('ShowYear')){
				    $Bootstrap = Bootstrap::getBootstrap();
				    $Bootstrap->addTimestamp('Instantiating $this->Festival');
					$this->Festival = $this->FestivalContainer->getFestival($Show->getParameter('ShowYear'));
				}
				if ($Show->getParameter('ShowDay') != ""){
				        $Show->setParameter('ShowPrettyDay',$this->Festival->getPrettyDay($Show->getParameter('ShowDay')));
				}
				if ($Show->getParameter('ShowStage') != ""){
				        $Show->setParameter('ShowPrettyStage',$this->Festival->getPrettyStageName($Show->getParameter('ShowScheduleUID'),$Show->getParameter('ShowStage')));
				}
				if ($Show->getParameter('ShowStartTime') != ""){
				        $Show->setParameter('ShowPrettyStartTime',$this->Festival->getPrettyTime($Show->getParameter('ShowStartTime')));
							$Show->setParameter('ShowPrettyStartTime24',$this->Festival->getPrettyTime24($Show->getParameter('ShowStartTime')));
				
				}
				if ($Show->getParameter('ShowEndTime') != ""){
				        $Show->setParameter('ShowPrettyEndTime24',$this->Festival->getPrettyTime24($Show->getParameter('ShowEndTime')));
						
				}
				$Show->setParameter('ShowArtists',array());
				$Show->setParameter('ShowArtistNames',array());
				
				// Set the Location conjunction
				if ($Show->getParameter('ShowStage') != ""){
    				$Schedule = $this->ScheduleContainer->getSchedule($Show->getParameter('ShowYear'),$Show->getParameter('ShowScheduleUID'));
    				if ($Schedule){
    				    $Stages = $Schedule->getParameter('ScheduleStages');
    				    $Show->setParameter('ShowLocationConjunction',$Stages[$Show->getParameter('ShowStage')]['LocationConjunction']);
    				}
    			}
    			
    			// Get Repeats
    			if (is_array($Repeats = $this->getRepeats($Show->getParameter('ShowID')))){
    			    $Show->setParameter('ShowRepeats',$Repeats);
    			}
		}

	function getShow($Show_id){
		
		$wc = new whereClause($this->getColumnName('ShowID')." = ?",$Show_id);
		
		if (PEAR::isError($Object = $this->getObject($wc))) return $Object;
		
		if ($Object){
                        return $this->manufactureShow($Object);
		}
		else{
			return null;
		}
	}
	
	function getRepeats($Show_id){
		$wc = new whereClause($this->getColumnName('ShowRepeatOfShowID')." = ?",$Show_id);
		
		if (PEAR::isError($Object = $this->getObject($wc))) return $Object;
		
		if ($Object){
		    if (!is_array($Object)){
		        $Object = array($Object);
		    }
		    $RepeatIDs = array();
		    foreach ($Object as $key => $tmpShow){
		        $RepeatIDs[] = $tmpShow->getParameter('ShowID');
		    }
		    return $RepeatIDs;
		}
		else{
			return null;
		}
	}
        
	function deleteShow($show_id){
		$wc = new whereClause($this->getColumnName('ShowID')." = ?",$show_id);
		return $this->deleteObject($wc);
	
	}
  
        
        function getConflicts($Show,$Really = false){
				// I've made improvements to the Schedule Painter that allow multiple shows at the same time
				// to display properly.  Therefore, I'm deprecating this functionality, unless they pass in true for $Really
				if (!$Really){
					return array();
				}
	
                if ($Show->getParameter('ShowDay') == "" 
                or  $Show->getParameter('ShowStage') == ""
                or  $Show->getParameter('ShowStartTime') == ""){
                        // Show is not fully assigned, therefore no conflict possible.  
                        return array();
                }
                $wc = new whereClause();
                $wc->addCondition($this->getColumnName('ShowYear')."=?",$Show->getParameter('ShowYear'));
                $wc->addCondition($this->getColumnName('ShowScheduleUID')."=?",$Show->getParameter('ShowScheduleUID'));
                $wc->addCondition($this->getColumnName('ShowDay')."=?",$Show->getParameter('ShowDay'));
                $wc->addCondition($this->getColumnName('ShowStage')."=?",$Show->getParameter('ShowStage'));
                $wc->addCondition($this->getColumnName('ShowID')."<>?",$Show->getParameter('ShowID'));
                $wc_times = new whereClause();
                $wc_times->setConnector("OR");
                /*
                        test: 1200 - 1300
                        
                        Conflicting Shows
                        s1: 1100 - 1400  -> Case 1
                        s2: 1130 - 1230  -> Case 2
                        s3: 1230 - 1330  -> Case 3
                        s4: 1100 - 1200  -> Case 4 (ok)
                        s5: 1300 - 1400  -> Case 5 (ok)
                */
                $wc_case1 = new whereClause();
                $wc_case1->addCondition($this->getColumnName('ShowStartTime')."<=?",$Show->getParameter('ShowStartTime'));
                $wc_case1->addCondition($this->getColumnName('ShowEndTime').">=?",$Show->getParameter('ShowEndTime'));
                $wc_times->addCondition($wc_case1);
                
                $wc_case2 = new whereClause();
                $wc_case2->addCondition($this->getColumnName('ShowEndTime').">?",$Show->getParameter('ShowStartTime'));
                $wc_case2->addCondition($this->getColumnName('ShowEndTime')."<=?",$Show->getParameter('ShowEndTime'));
                $wc_times->addCondition($wc_case2);
                
                $wc_case3 = new whereClause();
                $wc_case3->addCondition($this->getColumnName('ShowStartTime').">=?",$Show->getParameter('ShowStartTime'));
                $wc_case3->addCondition($this->getColumnName('ShowStartTime')."<?",$Show->getParameter('ShowEndTime'));
                $wc_times->addCondition($wc_case3);
                
                $wc->addCondition($wc_times);
                //return array($wc->getRealString());
                $ConflictingShows = $this->getAllShows($wc);
                if (!is_array($ConflictingShows) or !count($ConflictingShows)){
                        return array();
                }
                else{
                        $Conflicts = array();
                        foreach ($ConflictingShows as $ConflictingShow){
                            if ($ConflictingShow->getParameter('ShowStartTime') != $Show->getParameter('ShowStartTime') or 
                                $ConflictingShow->getParameter('ShowEndTime') != $Show->getParameter('ShowEndTime')){
                                    
                                // Allow shows that run from/to the same times - I can display these in with the schedule painter
                                $Conflicts[] = vocabulary('Show')." is in conflict with another ".$Show->getParameter('ShowType')." running from ".Festival::getPrettyTime($ConflictingShow->getParameter('ShowStartTime'))." to ".Festival::getPrettyTime($ConflictingShow->getParameter('ShowEndTime')).".";
                            }
                        }
                        return $Conflicts;
                }
                
        }
        
        function unscheduleShows($Year,$ScheduleUID,$Stage){
            $wc = new whereClause();
            $wc->addCondition($this->getColumnName('ShowYear').'=?',$Year);
            $wc->addCondition($this->getColumnName('ShowScheduleUID').'=?',$ScheduleUID);
            $wc->addCondition($this->getColumnName('ShowStage').'=?',$Stage);
            
            $Shows = $this->getAllShows($wc);
            
            if (is_array($Shows)){
                foreach ($Shows as $Show){
                    $Show->setParameter('ShowStage',null);
                    $Show->setParameter('ShowDay',null);
                    $Show->setParameter('ShowStartTime',null);
                    $Show->setParameter('ShowEndTime',null);
                    $result = $this->updateShow($Show);
                    if (PEAR::isError($result)){
                        return $result;
                    }
                }
            }
            return true;
        }
        
        function moveStageIndexUp($Year,$ScheduleUID,$Stage){
            $this->resetStageIndex($Year,$ScheduleUID,intval($Stage) - 1,99);
            $this->resetStageIndex($Year,$ScheduleUID,$Stage,intval($Stage) - 1);
            $this->resetStageIndex($Year,$ScheduleUID,99,$Stage);
        }
        
        function moveStageIndexDown($Year,$ScheduleUID,$Stage){
            $this->resetStageIndex($Year,$ScheduleUID,intval($Stage) + 1,99);
            $this->resetStageIndex($Year,$ScheduleUID,$Stage,intval($Stage) + 1);
            $this->resetStageIndex($Year,$ScheduleUID,99,$Stage);
        }
        
        function resetStageIndex($Year,$ScheduleUID,$OldIndex,$NewIndex){
		    if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
            
            $query = 'UPDATE '.$this->getTableName().' SET '.$this->getColumnName('ShowStage').' = ? WHERE ';
            $query.= $this->getColumnName('ShowYear'). ' = ? AND ';
            $query.= $this->getColumnName('ShowScheduleUID'). ' = ? AND ';
            $query.= $this->getColumnName('ShowStage'). ' = ?';
            
            $update_parms = array($NewIndex,$Year,$ScheduleUID,$OldIndex);
            
    		$sth = $dbh->prepare($query); 
    	    //echo $dbh->executeEmulateQuery($sth,$update_parms);
			
			$result = $dbh->execute($sth,array_values($update_parms));
			if (DB::isError($result)){
				return $result;
			}
            return true;
        }
        
        function hasResolutionConflicts($Schedule){
            $StageTimesArray = Festival::getStageTimesArray($Schedule);
            foreach ($StageTimesArray as $Stage => $TimesArray){
                foreach ($TimesArray as $Day => $Times){
                    // Create an array that will help us in a moment
                    $HelpfulArray = array();
                    foreach ($Times as $CanonicalTime => $PrettyTime){
                        $HelpfulArray[$CanonicalTime] = '?';
                    }
                    
                    $wc = new whereClause();
                    $wc->addCondition($this->getColumnName('ShowYear').' = ?',$Schedule->getParameter('ScheduleYear'));
                    $wc->addCondition($this->getColumnName('ShowScheduleUID').' = ?',$Schedule->getParameter('ScheduleUID'));
                    $wc->addCondition($this->getColumnName('ShowStage').' = ?',$Stage);
                    $wc->addCondition($this->getColumnName('ShowDay').' = ?',$Day);
                    $wc->addCondition($this->getColumnName('ShowStartTime').' IS NOT NULL');
                    $wc->addCondition($this->getColumnName('ShowEndTime').' IS NOT NULL');
                    $wc->addCondition($this->getColumnName('ShowStartTime').' > ?','');
                    $wc->addCondition($this->getColumnName('ShowEndTime').' > ?','');
                    
                    $wc2 = new whereClause();
                    $wc2->setConnector('OR');
                    $wc2->addCondition($this->getColumnName('ShowStartTime').' NOT IN ('.implode(',',$HelpfulArray).')',array_keys($HelpfulArray));
                    $wc2->addCondition($this->getColumnName('ShowEndTime').' NOT IN ('.implode(',',$HelpfulArray).')',array_keys($HelpfulArray));
                    
                    $wc->addCondition($wc2);
                    
                    $Shows = $this->getAllShows($wc);
                    if ($Shows){
                        return true;
                    }
                }
            }
            return false;
        }

		function findOrphanShows($ScheduleUID){
			$wc = new whereClause();
			$wc->addCondition($this->getColumnName('ShowScheduleUID').' = ?',$ScheduleUID);
			$wc->addCondition($this->getColumnName('ShowDay').' is NULL');
			return $this->getAllShows($wc);
		}

}

?>