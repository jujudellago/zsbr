<?php

require_once(PACKAGE_DIRECTORY."ImportExport/Importer.php");

class ShowImporter extends Importer{
    
    function ShowImporter(){
		$this->Importer();
		$this->default_encoding = 'UTF-8';
		$this->default_delimiter = 'comma';
		$this->limit = 25;
		
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('FestivalApp');
        
		$this->parameterPrefix = 'Show';
        $this->setContainer('SimpleShowContainer');
        $this->setUniqueKey(array('ShowTitle','ShowScheduleUID','ShowDay','ShowStartTime','ShowStage'));
        $this->setDisplayKey('ShowTitle');
        $this->setSQLKey('ShowID');

		$Bootstrap->primeAdminPage(); // I hate that I have to do this.
        $this->viewImportedRecordURL = $Bootstrap->makeAdminURL($Package,'update_show')."&id=";        
        $this->editImportedRecordURL = $Bootstrap->makeAdminURL($Package,'update_show')."&id=";        

        $this->ignoreParameter(array());

		$ExtraParameters = array();
		foreach ($this->Container->colname as $key => $parm){
			$ExtraParameters[] = $parm;
			$ExtraParameters[] = str_replace($this->parameterPrefix,'',$parm);
		}
		
		$this->addExtraParameter($ExtraParameters);
		$this->addExtraParameter(array('Year','ScheduleName','StageName','ShowDate','ShowArtists','ShowTime'));
		
		$this->ScheduleContainer = new ScheduleContainer();
		$this->ShowContainer = new ShowContainer();
		$this->SimpleShowContainer = new SimpleShowContainer();
		$this->FestivalContainer = new FestivalContainer();
		$this->ArtistContainer = new ArtistContainer();
		$this->FestivalArtistContainer = new FestivalArtistContainer();
    }
    
    function massageData(& $Object){
        foreach ($this->getExtraParameters() as $parm){
			if ($Object->getParameter($parm) != "" and $Object->getParameter($this->parameterPrefix.$parm) == ""){
				$Object->setParameter($this->parameterPrefix.$parm,$Object->getParameter($parm));
			}
        }
		
		// First, let's parse the time
		if ($Object->getParameter('ShowTime') !== null and $Object->getParameter('ShowTime') !== ''){
			$time = $Object->getParameter('ShowTime');
			$time = str_replace("\n","",$time);
			if (preg_match("/[^0-9]*([0-9]+:[0-9]+).?(AM|PM|A.M.|P.M.) to$/i",$time,$matches)){
				// there's a starttime but no end time
				$start_time = date("Hi",strtotime($matches[1].$matches[2]));
				$end_time = $start_time + 300; // (arbitrary, 3 hours later)
			}
			elseif (preg_match("/[^0-9]*([0-9]+:[0-9]+).?(AM|PM|A.M.|P.M.) to ([0-9]+:[0-9]+).?(AM|PM|A.M.|P.M.)$/i",$time,$matches)){
				// there's a starttime and an end time
				$start_time = date("Hi",strtotime($matches[1].$matches[2]));
				$end_time = date("Hi",strtotime($matches[3].$matches[4]));
			}
			elseif (preg_match("/[^0-9]*([0-9]+:[0-9]+).?(AM|PM|A.M.|P.M.)$/i",$time,$matches)){
				$start_time = date("Hi",strtotime($matches[1].$matches[2]));
				if ($start_time % 100 == 0){
					$end_time = $start_time+30;
				}
				else{
					$end_time = $start_time+70;
				}
			}
			else{
				return PEAR::raiseError("Couldn't parse: ".$Object->getParameter('ShowTime'));
			}
			if ($start_time > $end_time){
				$end_time+=2400;
			}
			$Object->setParameter('ShowStartTime',$start_time);
			$Object->setParameter('ShowEndTime',$end_time);
		}

		// Oh, how fun.  We have a few steps to consider here.  
		// First, we need to make sure that the Schedule exists
		// Then, we need to make sure that the stage exists on the schedule and covers the appropriate times
		// Finally, we add the show to the schedule.  
		$Year = $Object->getParameter('Year');
		if ($Year == ""){
			return PEAR::raiseError("In order to include the show into the schedule, you must indicate which year you're working on.  Do this by adding a 'Year' column to your CSV.");
		}
		if (!isset($this->Festival)){
			$this->Festival = $this->FestivalContainer->getFestival($Year);
		}
		if (!is_a($this->Festival,'Festival')){
			return PEAR::raiseError("No festival found for '$Year'.  Please define one before importing.");
		}
		$this->AllSchedules = $this->ScheduleContainer->getAllSchedules($Year);
		
		$ScheduleName = $Object->getParameter('ScheduleName');
		// Look up the Schedule
		$Schedule = $this->getScheduleByName($ScheduleName,$Year,$Object);
		
		// Good, we've got the schedule, now let's check if the stage exists
		if ($Object->getParameter('StageName') !== null and $Object->getParameter('StageName') !== ''){
			$StageName = apply_filters('ShowImporter_massage_StageName',$Object->getParameter('StageName'),$Object);
			$StageFound = false;
			$ScheduleStages = $Schedule->getParameter('ScheduleStages');
			foreach($ScheduleStages as $stage_id => $Stage){
				if ($Stage['Name'] == $StageName){
					$StageFound = true;
					break;
				}
			}
			if (!$StageFound){
				$Stage = array();
				$Stage['Name'] = $StageName;
				$Stage['LocationConjunction'] = '';
				$Stage['Times'] = array();
				foreach ($this->Festival->getPrettyDays() as $day_id => $Day){
					$Stage['Times'][$day_id] = array();
				}
				//$ScheduleStages[] = $Stage;
				$stage_id++;
			}
		}
		
		// Okay, the stage is in, let's make sure the times are appropriate.
		// First, we have to find out what Festival Day we're dealing with
		if ($Object->getParameter('ShowDay') !== null and $Object->getParameter('ShowDay') !== ''){
			if (!is_numeric($Object->getParameter('ShowDay'))){
				$Object->setParameter('ShowDate',$Object->getParameter('ShowDay'));
				$Object->setParameter('ShowDay','');
			}
			else{
				$day_id = $Object->getParameter('ShowDay');
				$DayFound = true;
			}
		}
		if ($Object->getParameter('ShowDate') !== null and $Object->getParameter('ShowDate') !== ''){
			$DayFound = false;
			$FullDay = date("l",strtotime($Object->getParameter('ShowDate')));
			foreach ($this->Festival->getPrettyDays() as $day_id => $Day){
				if ($Day == $FullDay){
					$DayFound = true;
					break;
				}
			}
		}
		if (isset($DayFound) and !$DayFound){
			return PEAR::raiseError('Could not find an associated day in the '.vocabulary('Festival').' for '.$Object->getParameter('ShowDate'));
		}
		// just going to assume that the day is found, also that the start/end times are 2400 times.  
		// It's nice to be the programmer sometimes
		
		// First we're going to pad the start & end times with 0's.  This is because Excel will turn 
		// 0800 into 800 and 0000 into 0 unless you format the cells as text
		if (isset($day_id) and $Object->getParameter('ShowStartTime') !== null and $Object->getParameter('ShowStartTime') !== ''){
			if (strlen($Object->getParameter('ShowStartTime')) < 4){
				$Object->setParameter('ShowStartTime',str_pad($Object->getParameter('ShowStartTime'),4,'0',STR_PAD_LEFT));
			}
			if (strlen($Object->getParameter('ShowEndTime')) < 4){
				$Object->setParameter('ShowEndTime',str_pad($Object->getParameter('ShowEndTime'),4,'0',STR_PAD_LEFT));
			}

			if (!preg_match('/^[0-9]{4,4}$/',$Object->getParameter('ShowStartTime'))){
				return PEAR::raiseError('Show Start Time ('.$Object->getParameter('ShowStartTime').') doesn\'t seem valid');
			}
			if (!preg_match('/^[0-9]{4,4}$/',$Object->getParameter('ShowEndTime'))){
				return PEAR::raiseError('Show End Time ('.$Object->getParameter('ShowEndTime').') doesn\'t seem valid');
			}
			if ($Object->getParameter('ShowEndTime') < $Object->getParameter('ShowStartTime')){
				$Object->setParameter('ShowEndTime',intval($Object->getParameter('ShowEndTime')) + 2400);
			}

			if (!count($Stage['Times'][$day_id])){
				$Stage['Times'][$day_id][0] = $Object->getParameter('ShowStartTime');
				$Stage['Times'][$day_id][1] = $Object->getParameter('ShowEndTime');
				$Stage['Times'][$day_id][2] = ''; // Stage Sponsor, not used in this implementation
			}
			else{
				if ($Stage['Times'][$day_id][0] > $Object->getParameter('ShowStartTime')){
					$Stage['Times'][$day_id][0] = $Object->getParameter('ShowStartTime');
				}
				if ($Stage['Times'][$day_id][1] < $Object->getParameter('ShowEndTime')){
					$Stage['Times'][$day_id][1] = $Object->getParameter('ShowEndTime');
				}
			}
		}
		$ScheduleStages[$stage_id] = $Stage;

		// We've updated the stages, let's update the schedule
		$Schedule->setParameter('ScheduleStages',$ScheduleStages);
		if (!is_array($Schedule->getSavedParameter('ScheduleStages'))){
			$Schedule->params_saved['ScheduleStages'] = array();
		}
		$this->ScheduleContainer->updateSchedule($Schedule);
		
		// Okay, we have the stage added/updated to the schedule, now it's time to massage the Show
		$Object->setParameter('ShowYear',$Year);
		if (isset($day_id)){
			$Object->setParameter('ShowDay',$day_id);
		}
		if (isset($stage_id)){
			$Object->setParameter('ShowStage',$stage_id);
		}
		$Object->setParameter('ShowScheduleUID',$Schedule->getParameter('ScheduleUID'));
		$Object->setParameter('ShowType',$Schedule->getParameter('ScheduleID'));
		if (preg_match('/Schedule: ([^;]+);.*Stage: (.*)/',$Object->getParameter('ShowArtists'),$matches)){
			// this is special markup to indicate that the show actually contains an embedded schedule
			$Schedule = $this->getScheduleByName($matches[1],$Year,$Object);
			$StageFound = false;
			$ScheduleStages = $Schedule->getParameter('ScheduleStages');
			foreach($ScheduleStages as $stage_id => $Stage){
				if ($Stage['Name'] == $matches[2]){
					$StageFound = true;
					break;
				}
			}
			if (!$StageFound){
				return PEAR::raiseError("Couldn't find stage ".$matches[2]." to do the embedding on the show ".$Object->getParameter('ShowTitle'));
			}
			$Object->setParameter('ShowEmbeddedScheduleUID',$Schedule->getParameter('ScheduleUID'));
			$Object->setParameter('ShowEmbeddedStageID',$stage_id);
			$Object->setParameter('ShowArtists','');
		}
		
		$this->current_ShowArtists = $Object->getParameter('ShowArtists');
		
		$description = $Object->getParameter('ShowDescription');
		$description = preg_replace("/^[\r\n]+/",' ',$description);
		$description = str_replace("\r\n\r\n",'__n__',$description);
		$description = str_replace("\n\n",'__n__',$description);
		$description = str_replace("\n",' ',$description);
		$description = str_replace("\r",' ',$description);
		$description = str_replace('__n__',"\n\n",$description);		
		$description = str_replace('__c__',',',$description);
		$Object->setParameter('ShowDescription',$description);
		$this->Container->setTimestamp($Object);
		
   	}

	function addObject(&$Object){
		$return = $this->Container->addShow($Object);
		$tmp = $this->setLineup($Object);
		if (PEAR::isError($tmp)){
			return $tmp;
		}
		return $return;
	}

	function updateObject(&$Object){
		$return = $this->Container->updateShow($Object);
		$tmp = $this->setLineup($Object);
		if (PEAR::isError($tmp)){
			return $tmp;
		}
		return $return;
	}
	
	function setLineup(&$Show){
		if (!isset($this->Lineup)){
			$this->Lineup = $this->Festival->getLineup();
		}
		if (!is_array($this->Lineup)){
			$this->Lineup = array();
		}
		$FestivalArtistIDs = array_keys($this->Lineup);
		$ArtistNames = trim($this->current_ShowArtists);
		if ($ArtistNames != ''){
			$ArtistNames = array_map('trim',$this->quotesplit($ArtistNames));
			if (count($ArtistNames)){
				// Need to make sure there is such an artist in the lineup
				$ShowArtistIDs = array();
				$ShowArtistNames = array();
				foreach ($ArtistNames as $ArtistName){
					$ArtistName = preg_replace("/[\n\r]+/"," ",$ArtistName);
					$ArtistName = str_replace("__c__",",",$ArtistName);
					if (strpos($ArtistName,' ') !== false and strpos($ArtistName,'|') === false){
						// Intelligent Guess
						$ArtistName = preg_replace('/ ([^ ]+)/','|$1',trim($ArtistName));
					}
					list($first_name,$last_name) = explode('|',$ArtistName);
					$ArtistName = str_replace("|"," ",$ArtistName);
					$wc = new whereClause();
					$wc->addCondition($this->ArtistContainer->getColumnName('ArtistFullName').' = ?',$ArtistName);
					$Artists = $this->ArtistContainer->getAllArtists($wc);
					if (is_array($Artists) and count($Artists)){
						$Artist = current($Artists);
					}
					else{
						$Artist = new Artist();
						$Artist->setParameter('ArtistFullName',$ArtistName);
						$Artist->setParameter('ArtistFirstName',$first_name);
						$Artist->setParameter('ArtistLastName',$last_name);
						$this->ArtistContainer->addArtist($Artist);
					}
					if (!array_key_exists($Artist->getParameter('ArtistID'),$this->Lineup)){
						$this->Lineup[$Artist->getParameter('ArtistID')] = $Artist;
						$FestivalArtistIDs = array_keys($this->Lineup);
						$this->FestivalArtistContainer->addFestivalArtist($this->Festival->getParameter('FestivalYear'),$Artist);
					}
				
					// The Artist is in the lineup, let's add them to the show
					$ShowArtistIDs[] = $Artist->getParameter('ArtistID');
					$ShowArtistNames[] = $ArtistName;
				}
				$this->ShowContainer->setShowLineup($Show->getParameter('ShowID'),$ShowArtistIDs);
				if ($Show->getParameter('ShowTitle') == ''){
					$Show->setParameter('ShowTitle',implode(', ',$ShowArtistNames));
					$this->Container->updateShow($Show);
				}
			}
		}
	}

	function postImport(& $Show){
	}
	
	function postPerformImport(){
	}

	function synchronize($StartTime){
		$timestamp = date("Y-m-d H:i:s",$StartTime);
		
		// Sanity check (no security hole here)
		if (!$timestamp or $timestamp >= time()){
			// invalid timestampe or one after right now (duh)
			return false;
		}

		// First, let's get the year(s) we were dealing with
		$wc = new whereClause();
		$wc->setConnector('OR');
		$wc->addCondition($this->Container->getColumnName('ShowModifiedTimestamp').' >= ?',$timestamp);
		if (is_array($_SESSION['import_unchanged_ids']) and count($_SESSION['import_unchanged_ids'])){
			$wc->addCondition($this->Container->getColumnName('ShowID').' IN ('.implode(',',$_SESSION['import_unchanged_ids']).')');
		}
		$Shows = $this->Container->getAllShows($wc);
		$AllYears = array();
		if (is_array($Shows)){
			foreach ($Shows as $Show){
				$AllYears[$Show->getParameter('ShowYear')] = $Show->getParameter('ShowYear');
			}
		}
		$wc = new whereClause();
		$wc->addCondition($this->Container->getColumnName('ShowModifiedTimestamp').' < ?',$timestamp);
		if (count($AllYears)){
			$wc->addCondition($this->Container->getColumnName('ShowYear').' IN ('.implode(',',array_keys($AllYears)).')');
		}
		if (is_array($_SESSION['import_unchanged_ids']) and count($_SESSION['import_unchanged_ids'])){
			$wc->addCondition($this->Container->getColumnName('ShowID').' NOT IN ('.implode(',',$_SESSION['import_unchanged_ids']).')');
		}
		$Shows = $this->Container->getAllShows($wc);
		if (is_array($Shows)){
			foreach ($Shows as $Show){
				$this->Container->deleteShow($Show->getParameter('ShowID'));
			}
		}
	}	
	
	function getScheduleByName($ScheduleName,$Year,$Object){
		$ScheduleFound = false;
		if (is_array($this->AllSchedules)){
			foreach ($this->AllSchedules as $Schedule){
				if ($Schedule->getParameter('ScheduleName') == $ScheduleName){
					$ScheduleFound = true;
					break;
				}
			}
		}
		if (!$ScheduleFound){
			// Need to create one
			$Schedule = new Schedule();
			$Schedule->setParameter('ScheduleYear',$Year);
			$Schedule->setParameter('ScheduleName',$ScheduleName);
			$Schedule->setParameter('ScheduleID','Session');
			$Schedule->setParameter('ScheduleStages',array());
			$Schedule->setParameter('ScheduleResolution',15);
			$Schedule->setParameter('ScheduleIsPublished',true);
			$Schedule = apply_filters('ShowImporter_massage_Schedule',$Schedule,$Object);
			$this->ScheduleContainer->addSchedule($Schedule);
			$this->AllSchedules = $this->ScheduleContainer->getAllSchedules($Year);
		}
		return $Schedule;
	}
}
