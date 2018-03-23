<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("ShowContainer.php");
require_once("Schedule.php");

if (!defined ('SCHEDULE_TABLE')){
    define('SCHEDULE_TABLE',DATABASE_PREFIX."Schedules");
}

class ScheduleContainer extends ObjectContainer
{

	var $tablename;
	
	function ScheduleContainer(){
		$this->DB_Object();
		$this->setDSN(DSN);
		$this->setTableName(SCHEDULE_TABLE);
		$this->setColumnName("ScheduleUID","ScheduleUID");
		$this->setColumnName("ScheduleID","ScheduleID");
		$this->setColumnName("ScheduleYear","ScheduleYear");
		$this->setColumnName("ScheduleStages","ScheduleStages");
		$this->setColumnName("ScheduleResolution","ScheduleResolution");
		$this->setColumnName("ScheduleName","ScheduleName");
		$this->setColumnName("ScheduleIsPublished","ScheduleIsPublished");
		$this->setColumnName("ScheduleModifiedTimestamp","ScheduleModifiedTimestamp");
		
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
				`ScheduleUID` int(6) unsigned AUTO_INCREMENT NOT NULL,
				`ScheduleID` varchar(50) default NULL,
				`ScheduleYear` varchar(25) default NULL,
				`ScheduleStages` text DEFAULT NULL,
				`ScheduleResolution` int(2) default NULL, 
				`ScheduleName` varchar(128) default NULL, 
				`ScheduleIsPublished` int(1) default NULL, 
				`ScheduleModifiedTimestamp` timestamp default NOW(),
				PRIMARY KEY (`ScheduleUID`),
				KEY (`ScheduleYear`,`ScheduleID`)
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
	
	
	function addSchedule(&$Schedule){
		$this->setTimestamps($Schedule);
	    $StagesSaved = $Schedule->getParameter('ScheduleStages');
	    $Schedule->setParameter('ScheduleStages',serialize($StagesSaved));
        if (false and $this->getSchedule($Schedule->getParameter('ScheduleYear'),$Schedule->getParameter('ScheduleID'))){
            // One of that type already exists.  Can't have two of the same type
            return PEAR::raiseError("A schedule is already defined with ".$Schedule->getParameter('ScheduleID')."s.  Two schedules cannot have the same type of show.");
        }
		$result = $this->addObject($Schedule);
	    $Schedule->setParameter('ScheduleStages',$StagesSaved);
	    return $result;
	}	
        
	function updateSchedule(&$Schedule){
		$this->setTimestamps($Schedule);
		
		// If the user has reordered or deleted stages, then we need to update all shows to reflect the 
		// new indexing.  The Show fields that need to be reindexed are ShowStage and ShowEmbeddedStageID
		// 
		// We now have Embedded Schedules.  If the user has reordered or deleted stages, then the indexing has
		// changed and we need to update any shows that have this schedule as the embedded schedule
		// Before we go too far, we'll see if we actually need to worry about it (which we only do if there are Shows
		// with this schedule as the EmbeddedScheduleUID
		$PreviousStages = array();
		$CurrentStages = array();
		foreach ($Schedule->getSavedParameter('ScheduleStages') as $s => $Stage){
			$PreviousStages[$s] = $Stage['Name'];
		}
		foreach ($Schedule->getParameter('ScheduleStages') as $s => $Stage){
			$CurrentStages[$s] = $Stage['Name'];
		}
		$StageMapArray = array(); // Key equals OldIndex, value = NewIndex
		$StagesHaveChanged = false;
		foreach ($PreviousStages as $s => $StageName){
			if ($CurrentStages[$s] != $StageName){
				// We've found a change.  Need to update
				$NewIndex = array_search($StageName,$CurrentStages);
				if ($NewIndex === false){
					// Just a name change on the same stage
					$StageMapArray[$s] = $s;
				}
				else{
					$StageMapArray[$s] = $NewIndex;
				}
				$StagesHaveChanged = true;
			}
			else{
				$StageMapArray[$s] = $s;
			}
		}
	    $ShowContainer = new SimpleShowContainer();
		if ($StagesHaveChanged){
			$wc = new whereClause();
			$wc->setConnector('OR');
			$wc->addCondition($ShowContainer->getColumnName('ShowEmbeddedScheduleUID').' = ?',$Schedule->getParameter('ScheduleUID'));
			$wc->addCondition($ShowContainer->getColumnName('ShowScheduleUID').' = ?',$Schedule->getParameter('ScheduleUID'));
			$ScheduledShows = $ShowContainer->getAllShows($wc);
			if (is_array($ScheduledShows) and count($ScheduledShows)){
				foreach ($ScheduledShows as $Show){
					if ($Show->getParameter('ShowScheduleUID') == $Schedule->getParameter('ScheduleUID')){
						$OldStageIndex = $Show->getParameter('ShowStage');
						if ($StageMapArray[$OldStageIndex] != $OldStageIndex){
							$Show->setParameter('ShowStage',$StageMapArray[$OldStageIndex]);
							$ShowContainer->updateShow($Show);
						}
					}
					if ($Show->getParameter('ShowEmbeddedScheduleUID') == $Schedule->getParameter('ScheduleUID')){
						$OldStageIndex = $Show->getParameter('ShowEmbeddedStageID');
						if ($StageMapArray[$OldStageIndex] != $OldStageIndex){
							$Show->setParameter('ShowEmbeddedStageID',$StageMapArray[$OldStageIndex]);
							$ShowContainer->updateShow($Show);
						}
					}
				}
			}
		}
		
	    $StagesSaved = $Schedule->getParameter('ScheduleStages');
	    $Schedule->setParameter('ScheduleStages',serialize($StagesSaved));
	    if ($Schedule->getParameter('ScheduleID') != $Schedule->getSavedParameter('ScheduleID') and $Schedule->getSavedParameter('ScheduleID') != ""){
	        if (false and $this->getSchedule($Schedule->getParameter('ScheduleYear'),$Schedule->getParameter('ScheduleID'))){
	            // UPDATE - I removed this restriction...
				// One of that type already exists.  Can't have two of the same type
	            return PEAR::raiseError("A schedule is already defined with ".$Schedule->getParameter('ScheduleID')."s.  Two schedules cannot have the same type of show.");
	        }
	        else{
	            // Ok, we're safe to change it, but we have to update all shows that had the old type
	            $wc = new whereClause();
	            $wc->addCondition($ShowContainer->getColumnName('ShowYear')." = ?",$Schedule->getParameter('ScheduleYear'));
	            $wc->addCondition($ShowContainer->getColumnName('ShowScheduleUID')." = ?",$Schedule->getParameter('ScheduleUID'));
	            $Shows = $ShowContainer->getAllShows($wc);
	            if (is_array($Shows)){
	                foreach ($Shows as $Show){
	                    $Show->setParameter('ShowType',$Schedule->getParameter('ScheduleID'));
	                    $ShowContainer->updateShow($Show);
	                }
	            }
	        }
	    }
	    $id_parm = $Schedule->getIDParameter();
	    $Schedule->setIDParameter('ScheduleUID');
		$result = $this->updateObject($Schedule);
	    $Schedule->setParameter('ScheduleStages',$StagesSaved);
	    $Schedule->setIDParameter($id_parm);
	    return $result;
	}
	
	function setTimestamps(&$Schedule){
		$Schedule->setParameter('ScheduleModifiedTimestamp', date("Y-m-d H:i:s"));
	}
        
	function getAllSchedules($Year){
		$wc = new whereClause();
        $wc->addCondition($this->getColumnName('ScheduleYear')." = ?",$Year);
        if (PEAR::isError($Objects = $this->getAllObjects($wc,$this->getColumnName('ScheduleName'), 'asc'))){ return $Objects;}
		if ($Objects){
		    return $this->manufactureSchedule($Objects);
		}
		else{
			return array();
		}
    }

    function manufactureSchedule($Object){
            if (!is_array($Object)){
                    $_Objects = array($Object);
            }
            else{
                    $_Objects = $Object;
            }

            $Schedules = array();

			// This problem came up when dealing with exporting
			// I passed in objects from several years.  The IDParameter (ScheduleID)
			// was duplicated across years, so the returned Array only had one instance (since the ID Parameter is the key in the Returned Array)
			// To fix it, I'll find out first if that's the case and then use that info later
			$AllYears = array();
			foreach ($_Objects as $_Object){
				if (!in_array($_Object->getParameter('ScheduleYear'),$AllYears)){
					$AllYears[] = $_Object->getParameter('ScheduleYear');
				}
			}
			if (count($AllYears) > 1){
				$MultipleYears = true;
			}
			else{
				$MultipleYears = false;
			}
            foreach ($_Objects as $_Object){
                    $_tmp_Schedule = new Schedule();
    				$Stages = unserialize($_Object->getParameter('ScheduleStages'));
    				if (!is_array($Stages)){
    				    $Stages = null;
    				}
    				$_Object->setParameter('ScheduleStages',$Stages);
                    $_parms = $_Object->getParameters();
                    foreach ($_parms as $key=>$value){
                            $_tmp_Schedule->setParameter($key,$value);
                    }
                    $_tmp_Schedule->saveParameters();
					if ($MultipleYears){
	                    $Schedules[] = $_tmp_Schedule;
					}
					else{
	                    $Schedules[$_tmp_Schedule->getParameter($_tmp_Schedule->getIDParameter())] = $_tmp_Schedule;
					}
            }
            
            if (!is_array($Object)){
                    return array_shift($Schedules);
            }
            else{
                    return $Schedules;
            }
    }	
        
	function getSchedule($Year,$Schedule_id){
		$wc = new whereClause($this->getColumnName('ScheduleUID')." = ?",$Schedule_id);
        $wc->addCondition($this->getColumnName('ScheduleYear')." = ?",$Year);
		
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
		    return $this->manufactureSchedule($Object);
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
	}

	function getScheduleByUID($schedule_id){
		$wc = new whereClause($this->getColumnName('ScheduleUID')." = ?",$schedule_id);
		
		$Object = $this->getObject($wc);
		if ($Object and is_a($Object,'Parameterized_Object')){
		    return $this->manufactureSchedule($Object);
		}
		elseif(PEAR::isError($Object)){
			return $Object;
		}
		else{
			return null;
		}
	}
	
	function getScheduleIDs($Year){
		$wc = new whereClause();
        $wc->addCondition($this->getColumnName('ScheduleYear')." = ?",$Year);
        if (PEAR::isError($Objects = $this->getAllObjects($wc,$this->getColumnName('ScheduleUID'), 'asc'))){ return $Objects;}
		if ($Objects){
		    $ScheduleIDs = array();
		    foreach ($Objects as $Object){
		        $ScheduleIDs[] = $Object->getParameter('ScheduleUID');
		    }
		    return $ScheduleIDs;
		}
		else{
			return array();
		}
	}
	
	function deleteAllSchedules($Year){
	    $ScheduleIDs = $this->getScheduleIDs($Year);
	    foreach ($ScheduleIDs as $ScheduleID){
	        $this->deleteSchedule($Year,$ScheduleID);
	    }
	    return true;
	}
	
	function deleteSchedule($Year,$Schedule_id){
	    $Schedule = $this->getSchedule($Year,$Schedule_id);
	    if ($Schedule){
	        $ShowContainer = new ShowContainer();
	        $result = $ShowContainer->deleteShows($Year,$Schedule_id);
	        if (PEAR::isError($result)){
	            return $result;
	        }
	        else{
	            return $this->deleteScheduleUID($Schedule->getParameter('ScheduleUID'));
	        }
	    }
	    return true;
	}	
	
	function deleteScheduleUID($ScheduleUID){
		$wc = new whereClause($this->getColumnName('ScheduleUID')." = ?",$ScheduleUID);
		if (PEAR::isError($result = $this->deleteObject($wc))){
			return $result;
		}
		
		return true;
	}	
	
	function sortStages($Year,$Schedule_id){
		// Function to sort the stages alphabetically
	    $Schedule = $this->getSchedule($Year,$Schedule_id);
		if (is_a($Schedule,'Schedule')){
			$Stages = $Schedule->getParameter('ScheduleStages');
			$Stages[] = array('Name' => ''); // This allows me to make the indices 1-based
			usort($Stages,create_function('$a,$b','
				if ($a["Name"] == $b["Name"]){ return 0; }
				return ($a["Name"] < $b["Name"]) ? -1 : 1;
			'));
			unset($Stages[0]);
			$Schedule->setParameter('ScheduleStages',$Stages);
			$this->updateSchedule($Schedule);
		}
	}
	
	function deleteStage($Year,$Schedule_id,$Stage){
	    $Schedule = $this->getSchedule($Year,$Schedule_id);
	    
	    // We have to unschedule all shows scheduled on this stage
	    //$ShowContainer = new SimpleShowContainer();
	    //$result = $ShowContainer->unscheduleShows($Year,$Schedule_id,$Stage);

	    if (!PEAR::isError($result)){
	        // Now, we have to move all of the other stages up one...
	        $ScheduleStages = $Schedule->getParameter('ScheduleStages');
	        $ScheduleStages = array_merge(array_slice($ScheduleStages,0,$Stage),array_slice($ScheduleStages,$Stage + 1));
			/*
            for ($s = $Stage + 1;$s <= count($ScheduleStages); $s++){
                $result = $ShowContainer->resetStageIndex($Year,$Schedule_id,$s,$s - 1);
                if (PEAR::isError($result)){
                    break;
                }
            }
			*/
        }
        if (!PEAR::isError($result)){
            $Schedule->setParameter('ScheduleStages',$ScheduleStages);
            $result = $this->updateSchedule($Schedule);
        }
        
        if (PEAR::isError($result)){
            return $result;
        }
        else{
            return true;
        }
            
	    
	}

	function moveStageUp($Year,$Schedule_id,$Stage){
	    $Schedule = $this->getSchedule($Year,$Schedule_id);
	    if ($Schedule){
	        $ScheduleStages = $Schedule->getParameter('ScheduleStages');
	        if ($Stage > count($ScheduleStages)){
	            return PEAR::raiseError('The third parameter must be less than the total number of stages');
	        }
	        
    	    // We have to move all of the shows up....
    	    //$ShowContainer = new SimpleShowContainer();
    	    //$result = $ShowContainer->moveStageIndexUp($Year,$Schedule_id,$Stage);

    	    if (!PEAR::isError($result)){
    	        // Now, we can move the stage up....
    	        
    	        $NewScheduleStages = array();
                foreach ($ScheduleStages as $s => $_Stage){
                    if ($s == intval($Stage) - 1){
                        $NewScheduleStages[] = $ScheduleStages[$Stage];
                    }
                    if ($s != intval($Stage)){
                        $NewScheduleStages[] = $ScheduleStages[$s];
                    }
                }
                $Schedule->setParameter('ScheduleStages',$NewScheduleStages);
            }
            if (!PEAR::isError($result)){
                $result = $this->updateSchedule($Schedule);
                if (PEAR::isError($result)){
                    // Something went wrong
                    //$ShowContainer->moveStageIndexDown($Year,$Schedule_id,$Stage); // Reset our shows
                }
            }
        }
        else{
            return PEAR::raiseError('Could not find the schedule you were looking for');
        }
        
        if (PEAR::isError($result)){
            return $result;
        }
        else{
            return true;
        }
    }
    
    function updateSchedules($Festival){
        /*************************************************************
        *  There are a few of cases that we need to handle
        *   1) They've added days to the end of the festival - no problem, no further clarification needed
        *   2) They've added days to the beginning of the festival - no problem, shows will stay on the same actual day (i.e. Friday)
        *   3) Shifted the entire festival (number of days stays the same (or increases)) - no problem, no further clarification needed.  shows will stay on same festival day (i.e. day 1)
        *   4) They've shortened the festival.  If shows exist on those days, get confirmation that it's okay to delete the shows
        *************************************************************/
        $Schedules = $this->getAllSchedules($Festival->getParameter('FestivalYear'));
        if (!$Schedules){ // no schedules defined yet
            return true;
        }
        
	    if ($Festival->getParameter('FestivalStartDate') == $Festival->getSavedParameter('FestivalStartDate')
	    and $Festival->getParameter('FestivalEndDate') == $Festival->getSavedParameter('FestivalEndDate')){
            // No change
            return true;
        }
        
        // Case 3 - no need to do anything
        if (dateDiff($Festival->getParameter('FestivalStartDate'),$Festival->getParameter('FestivalEndDate')) ==
            dateDiff($Festival->getSavedParameter('FestivalStartDate'),$Festival->getSavedParameter('FestivalEndDate'))){
               return true;
        }
        
        $ShowContainer = new ShowContainer();
        $SimpleShowContainer = new SimpleShowContainer();
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());


        /************ Deal with a change to the start date ****************/
        $DaysDiffStart = dateDiff($Festival->getParameter('FestivalStartDate'),$Festival->getSavedParameter('FestivalStartDate'));
        if ((int)$DaysDiffStart === 0){
            // Okay everything is okay continue;
        }
        else{
            if ($DaysDiffStart < 0){
                // They've chopped days off.  We're going to delete any shows on chopped days (without warning)
                // Yes, i know we should warn, but it's a rare use case dagnammit, and this is already complicated enough
                $wc = new whereClause();
                $wc->addCondition($SimpleShowContainer->getColumnName('ShowYear').' = ?',$Festival->getParameter('FestivalYear'));
                $wc->addCondition($SimpleShowContainer->getColumnName('ShowDay').' < ?',intval($DaysDiffStart * -1));
                
                //echo $wc->getRealString();
                
                $Shows = $SimpleShowContainer->getAllShows($wc);
                if (is_array($Shows)){
                    foreach ($Shows as $Show){
                        $ShowContainer->deleteShow($Show->getParameter('ShowID'));
                    }
                }
                $DayAdjust = "- ".intval($DaysDiffStart * -1);
            }
            else{
                $DayAdjust = "+ $DaysDiffStart";
            }
                
            // Now, we'll adjust the day index of any shows....
            // Need to change day on all shows by offset equal to $DaysDiffStart
            $query = "UPDATE ".$SimpleShowContainer->getTableName()." SET ".
                $SimpleShowContainer->getColumnName('ShowDay')." = ".$SimpleShowContainer->getColumnName('ShowDay')." $DayAdjust ".
                "WHERE ".$SimpleShowContainer->getColumnName('ShowYear')." = '".$Festival->getParameter('FestivalYear')."'";
            $sth = $dbh->prepare($query);

            //echo $dbh->executeEmulateQuery($sth);

            $result = $dbh->execute($sth);
            if (DB::isError($result)){
                return PEAR::raiseError("Error: ".$result->getMessage());
            }
        }
        
        /************ Deal with a change to the end date ****************/
        $DaysDiffEnd = dateDiff($Festival->getParameter('FestivalEndDate'),$Festival->getSavedParameter('FestivalEndDate'));
        if ((int)$DaysDiffEnd === 0){
            // Okay everything is okay continue;
        }
        else{
            if ($DaysDiffEnd > 0){
                // They've chopped days off.  We're going to orphan any shows on chopped days (without warning)
                // Yes, i know we should warn, but it's a rare use case dagnammit, and this is already complicated enough
                
                // First figure out the number of days in the "old" festival
                $OldLength = dateDiff($Festival->getSavedParameter('FestivalStartDate'),$Festival->getSavedParameter('FestivalEndDate'));
                
                $wc = new whereClause();
                $wc->addCondition($SimpleShowContainer->getColumnName('ShowYear').' = ?',$Festival->getParameter('FestivalYear'));
                $wc->addCondition($SimpleShowContainer->getColumnName('ShowDay').' > ?',intval($OldLength - $DaysDiffEnd));
                
                $Shows = $SimpleShowContainer->getAllShows($wc);
				if (is_array($Shows)){
	                foreach ($Shows as $Show){
						$Show->setParameter('ShowDay',null);
						$Show->setParameter('ShowStage',null);
						$Show->setParameter('ShowStartTime',null);
						$Show->setParameter('ShowEndTime',null);
						$ShowContainer->updateShow($Show);
	                }
				}                
            }
        }
        foreach ($Schedules as $Type => $Schedule){
            $ScheduleStages = $Schedule->getParameter('ScheduleStages');
            foreach ($ScheduleStages as $Stage => $StageTimes){
                // Deal with changes to the Start Date
                if ($DaysDiffStart >= 0){
                    for($d = 0; $d < $DaysDiffStart; $d++){
                        array_unshift($ScheduleStages[$Stage]["Times"],array());
                    }
                }
                else{
                    for($d = 0; $d > $DaysDiffStart; $d--){
                        array_shift($ScheduleStages[$Stage]["Times"]);
                    }
                }

                // Deal with changes to the End Date
                if ($DaysDiffEnd > 0){
                    for($d = 0; $d < $DaysDiffEnd; $d++){
                        array_pop($ScheduleStages[$Stage]["Times"]);
                    }
                }
                else{
                    for($d = 0; $d > $DaysDiffEnd; $d--){
                        $ScheduleStages[$Stage]["Times"][] = array();
                    }
                }

            }
            $Schedule->setParameter('ScheduleStages',$ScheduleStages);
            $this->updateSchedule($Schedule);
        }
        
    }
    
    function copySchedules($FromYear,$ToYear){
        $Schedules = $this->getAllSchedules($FromYear);
        
        foreach ($Schedules as $Schedule){
            $Schedule->setParameter('ScheduleYear',$ToYear);
            $Schedule->setParameter('ScheduleUID','');
            $this->addSchedule($Schedule);
        }
        
        // This might only be tricky if the number of days in the from festival is different than the 
        // number of days in the to festival.  This code takes care of that in kind of a hacked way
        $FestivalContainer = new FestivalContainer();
        $FromFestival = $FestivalContainer->getFestival($FromYear);
        $ToFestival = $FestivalContainer->getFestival($ToYear);
        $FromDays = dateDiff($FromFestival->getParameter('FestivalStartDate'),$FromFestival->getParameter('FestivalEndDate'));
        $ToDays = dateDiff($ToFestival->getParameter('FestivalStartDate'),$ToFestival->getParameter('FestivalEndDate'));
        if ($FromDays == $ToDays){
            // Okay everything is okay continue;
        }
        else{
            $FromPrettyDays = $FromFestival->getPrettyDays();
            $ToPrettyDays = $ToFestival->getPrettyDays();
            
            $SavedStartDate = $ToFestival->getParameter('FestivalStartDate');
            $SavedEndDate = $ToFestival->getParameter('FestivalEndDate');
            
            if ($FromPrettyDays[0] == $ToPrettyDays[0]){  
                // Festivals start on the same day, adjust days at the end
                $ToFestival->setParameter('FestivalEndDate',date("Y-m-d",strtotime($ToFestival->getParameter('FestivalEndDate')." ".($FromDays > $ToDays ? "+" : "-")." ".abs($FromDays - $ToDays)." days")));
                $ToFestival->saveParameters();
                $ToFestival->setParameter('FestivalEndDate',$SavedEndDate);
            }
            else{ // if ($FromPrettyDays[count($FromPrettyDays) - 1] == $ToPrettyDays[count($ToPrettyDays) - 1]){
                // Festivals end on the same day, adjust the days at the beginning
                // TODO - even if they don't end on the same day, we'll just tack the days to the beginning
                // Handling all of the use cases for the other is proving too difficult, and not nearly interesting enough
                $ToFestival->setParameter('FestivalStartDate',date("Y-m-d",strtotime($ToFestival->getParameter('FestivalStartDate')." ".($FromDays > $ToDays ? "-" : "+")." ".abs($FromDays - $ToDays)." days")));
                $ToFestival->saveParameters();
                $ToFestival->setParameter('FestivalStartDate',$SavedStartDate);
            }
            $this->updateSchedules($ToFestival);
        }
    }
	    
}

function dateDiff($start, $end) {
    $start_ts = strtotime($start);
    $end_ts = strtotime($end);
    $diff = $end_ts - $start_ts;
    return floor($diff / 86400);
}

function make24HourTime($time){
    if (strlen($time) == 4 
        and is_numeric($time) 
        and intval(substr($time,0,2)) <= 59
        and intval($time) < 3000){ // 3000 is arbitrary.  this system would treat it like 6:00am the next day
        return $time;
    }
    elseif ($time == ""){
        return $time;
    }
    if (!preg_match("/^([0-9]+):([0-9]+)(am|pm)$/",strtolower($time),$matches)){
        return false;
    }
    elseif (intval($matches[1]) > 12 or intval($matches[2]) > 59){ 
        return false;
    }
    else{
        $h = intval($matches[1]);
        if ($matches[3] == 'pm'){
            if ($h == 12){
                $hour = 12;
            }
            else{
                $hour = $h + 12;
            }
        }
        else{
            if ($h == 12){
                $hour = 24;
            }
            elseif ($h < 5){
                    $hour = $h + 24;
            }
            else{
                $hour = sprintf("%02d",$h);
            }
        }
        return $hour.$matches[2];     
    }
}
?>