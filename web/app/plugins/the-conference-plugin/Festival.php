<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");
require_once("FestivalArtistContainer.php");
require_once("ShowContainer.php");
require_once("ScheduleContainer.php");

class Festival extends Parameterized_Object
{
    
    var $ScheduleContainer;
    
	function Festival($Festival_Year = '',$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('FestivalYear',$Festival_Year); 

		$this->setIDParameter('FestivalYear');
		$this->setNameParameter('FestivalYear');
		$this->ScheduleContainer = new ScheduleContainer();
	}
        
        function getPrettyDay($day_index){
			if (function_exists('apply_filters')){
				//return strftime( '%A', strtotime("+".$day_index." day",strtotime($this->getParameter('FestivalStartDate'))));
				return date(apply_filters('PrettyDayFormat',"l"),strtotime("+".$day_index." day",strtotime($this->getParameter('FestivalStartDate'))));
			}
			else{
                return date("l",strtotime("+".$day_index." day",strtotime($this->getParameter('FestivalStartDate'))));
			}
        }
        
        function getPrettyDays(){
                $Start = $this->getParameter('FestivalStartDate');
                $End   = $this->getParameter('FestivalEndDate');
                $Current = $Start;
                $ret = array();
                while ($Current <= $End){
						if (function_exists('apply_filters')){
							//$ret[] = strftime( '%A', strtotime($Current));
							$ret[] = date(apply_filters('PrettyDayFormat',"l"),strtotime($Current));
						}
						else{
	                        $ret[] = date("l",strtotime($Current));
						}
                        $Current = date ("Y-m-d",strtotime("+1 day",strtotime($Current)));
                }
                return $ret;
        }
        
        function getStageTimesArray($schedule_uid){
                if (is_a($type,'Schedule')){
                    $Schedule = $type;
                }
                else{
                    $Schedule = $this->ScheduleContainer->getSchedule($this->getParameter('FestivalYear'),$schedule_uid);
                }
                
                if (!$Schedule){
                    return array();
                }
                
                $Resolution = intval($Schedule->getParameter('ScheduleResolution'));
                $ret = array();
                $TypeStages = $Schedule->getParameter('ScheduleStages');
                foreach ($TypeStages as $s => $StageInfo){
                        $ret[$s] = array();
                        foreach ($StageInfo["Times"] as $d => $TimesArray){
                            
                                if (count($TimesArray)){
                                        $ret[$s][$d] = array();
                                        if (strlen($TimesArray[0]) != 4 
                                        or  !is_numeric($TimesArray[0])
                                        or  strlen($TimesArray[1]) != 4 
                                        or  !is_numeric($TimesArray[1])){
                                                return PEAR::raiseError("Unknown start/end times found: ".$TimesArray[0].", ".$TimesArray[1]);
                                        }
										// Going to make sure we're starting at an appropriate time given the resolution 
										// (i.e. 3:00 instead of 3:10 on a resolution of 15 minutes)
										$test = substr($TimesArray[0],2,2);
										if ($test % $Resolution !== 0){
											while ($test % $Resolution !== 0){
												$test-=1;
											}
											if (strlen($test) == 1){
												$test = "0$test";
											}
											$TimesArray[0] = substr($TimesArray[0],0,2).$test;
										}
                                        for ($t = $TimesArray[0]; $t <= $TimesArray[1]; $t+= $Resolution){
                                                if (strlen($t) != 4){
                                                    $t = "0".$t;
                                                }
                                                if (intval(substr($t,2,2)) >= 60){
                                                        $t+= 40;
                                                }
                                                if (strlen($t) != 4){
                                                    $t = "0".$t;
                                                }
                                                if ($t > $TimesArray[1]){
                                                    $t = $TimesArray[1];
                                                }
                                                $ret[$s][$d]["$t"] = Festival::getPrettyTime($t);
                                        }
                                }
                        }
                }
                return $ret;
        }
        
        function getStageTimesResolution($schedule_uid){
                $Schedule = $this->ScheduleContainer->getSchedule($this->getParameter('FestivalYear'),$schedule_uid);
                return $Schedule->getParameter('ScheduleResolution');
        }
        
        function getStageSponsorsArray($schedule_uid){
                if (is_a($schedule_uid,'Schedule')){
                    $Schedule = $schedule_uid;
                }
                else{
                    $Schedule = $this->ScheduleContainer->getSchedule($this->getParameter('FestivalYear'),$schedule_uid);
                }
                $ret = array();
                $TypeStages = $Schedule->getParameter('ScheduleStages');
                foreach ($TypeStages as $s => $StageInfo){
                        $ret[$s] = array();
                        foreach ($StageInfo["Times"] as $d => $TimesArray){
                            if (count($TimesArray)){
                                $ret[$s][$d] = $TimesArray[2];
                            }
                        }
                }
                return $ret;
            
        }
        function getPrettyTime24($time){
			 if (strlen($time) != 4 or !is_numeric($time)){
                        return "invalid 24hr time";
              }
              else{
				  $hour = substr($time,0,2);
                  $minute = substr($time,2,2);
	 			  return "$hour:$minute";
			  }
		}
        function getPrettyTime($time){
                if (strlen($time) != 4 or !is_numeric($time)){
                        return "invalid 24hr time";
                }
                else{
                        $hour = substr($time,0,2);
                        $minute = substr($time,2,2);
                        if (intval($hour) >= 24){
                                if ($hour == 24) { 
                                    $hour = 12; 
                                }
                                else {                            
                                    $hour -= 24;
                                }
                                $xm = "am";
                        }
                        elseif (intval($hour) < 12){
                                $xm = "am";
                        }
                        else{
                                if ($hour > 12){
                                        $hour -= 12;
                                }
                                $xm = "pm";
                        }
                        if (substr($hour,0,1) == '0'){
                            $hour = substr($hour,1);
                        }
                        return "$hour:$minute$xm";
                }
        }
        
        function getStageNamesArray($schedule_uid){
                // Returns the stage names for each day
                // The days are 0-indexed, as are the stages
                $Schedule = $this->ScheduleContainer->getSchedule($this->getParameter('FestivalYear'),$schedule_uid);
                if (!$Schedule){
                    return array();
                }
                
                $TypeStages = $Schedule->getParameter('ScheduleStages');
                if (!is_array($TypeStages)){
					return array();
                }

                $ret = array();
                foreach ($TypeStages as $s => $StageInfo){
                        foreach ($StageInfo["Times"] as $Day => $TimesArray){
                                if (!is_array($ret[$Day])){
                                        $ret[$Day] = array();
                                }
                                if (count($TimesArray)){
                                        $ret[$Day][$s] = $StageInfo["Name"];
                                }
                        }
                }
                return $ret;
        }
        
        function getPrettyStageName($schedule_uid,$stage_id){
                $Schedule = $this->ScheduleContainer->getSchedule($this->getParameter('FestivalYear'),$schedule_uid);
				if (!is_a($Schedule,'Schedule')){
					return 'Stage';
				}
                $TypeStages = $Schedule->getParameter('ScheduleStages');
                
                return $TypeStages[$stage_id]["Name"];
        }

		function getLineup($_sort_field = "",$sort_dir = ""){
				$FestivalArtistContainer = new FestivalArtistContainer();
				return $FestivalArtistContainer->getLineup($this->getParameter('FestivalYear'),$_sort_field,$sort_dir);
		}

		function getAllArtists($sort_field = "",$sort_dir = "asc"){
				$FestivalArtistContainer = new FestivalArtistContainer();
				return $FestivalArtistContainer->getAllArtists($this->getParameter('FestivalYear'),$sort_field,$sort_dir);
		}
		
		function getAllShows(){
				$ShowContainer = new ShowContainer();
				return $ShowContainer->getAllShows($this->getParameter('FestivalYear'));
		}
		
		function getAllStages(){
				$Stages = array();
				$Schedules = $this->ScheduleContainer->getAllSchedules($this->getParameter('FestivalYear'));
				if (is_array($Schedules)){
					$Stages = array();
					foreach($Schedules as $Schedule){
						$Stages[$Schedule->getParameter('ScheduleUID')] = array();
						$_stages = maybe_unserialize($Schedule->getParameter('ScheduleStages'));
						if (is_array($_stages)){
							foreach ($_stages as $_s => $_stage){
								$Stages[$Schedule->getParameter('ScheduleUID')][$_s] = $_stage;
							}
						}
					}
				}
				return $Stages;
		}
		
		function getAllScheduleUIDs(){
			$ScheduleUIDs = array();
			$Schedules = $this->ScheduleContainer->getAllSchedules($this->getParameter('FestivalYear'));
			if (is_array($Schedules)){
				foreach($Schedules as $Schedule){
					$ScheduleUIDs[$Schedule->getParameter('ScheduleID')] = $Schedule->getParameter('ScheduleUID');
				}
			}
			return $ScheduleUIDs;
		}
}
?>