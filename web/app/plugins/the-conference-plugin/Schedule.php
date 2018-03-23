<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

class Schedule extends Parameterized_Object
{
	function Schedule($Schedule_id = '',$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('ScheduleID',$Schedule_id);	

		$this->setIDParameter('ScheduleUID');
		$this->setNameParameter('ScheduleName');
		if (!$this->getParameter('ScheduleStages')){
		    $this->setParameter('ScheduleStages',array());
		}
	}
	
	function getScheduledDays(){
		$Stages = $this->getParameter('ScheduleStages');
		$ScheduledDays = array();
		foreach ($Stages as $s => $Stage){
			foreach ($Stage['Times'] as $d => $Times){
				if ($Times[0] != "" and $Times[1] != ""){
					$ScheduledDays[$d] = true;
				}
			}
		}
		ksort($ScheduledDays);
		$this->setParameter('ScheduledDays',array_keys($ScheduledDays));
		return $this->getParameter('ScheduledDays');
	}
}
?>