<?php

require_once(PACKAGE_DIRECTORY."ImportExport/Exporter.php");

class ShowExporter extends Exporter{
    
    function ShowExporter(){
		$this->Exporter();
		$this->default_encoding = 'UTF-8';
		$this->default_delimiter = 'comma';
		
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('FestivalApp');
        
		$this->parameterPrefix = '';
		$this->manufacturer = 'manufactureShow';
        $this->setContainer('SimpleShowContainer');

		$this->ArtistContainer = new FestivalArtistContainer();
		$this->ShowContainer = new ShowContainer();
        
        $this->ignoreParameter(array(
			'ShowID',
			'ShowModifiedTimestamp',
			'ShowPrettyStage',
			'ShowPrettyDay',
			'ShowPrettyStartTime',
			'ShowPrettyEndTime',
			'ShowLocationConjunction',
			'ShowType',
			'ShowScheduleUID',
			'ShowArtistNames',
			'ShowRepeatOfShowID',
			'RepeatOfID',
			'ShowEmbeddedStageID',
			'ShowEmbeddedScheduleUID',
			'ShowYear',
			'ShowStage'
		));
		$this->sort_parms = array('ShowEmbeddedScheduleUID','ShowTitle');
		$this->sort_dir = array('asc','asc');
		
		$this->addFilterParameter('ShowYear');
		$this->addExtraParameter('ScheduleName','StageName','Year');
		$this->ScheduleContainer = new ScheduleContainer();
		$this->Schedules = array();
    }
    
    function massageData(& $Object){
		$Shows = $this->ShowContainer->getLineupsForSimpleShows(array($Object->getParameter('ShowID') => $Object));
		$Object = current($Shows);
		if (!isset($this->Schedules[$Object->getParameter('ShowYear')])){
			$this->Schedules[$Object->getParameter('ShowYear')] = $this->ScheduleContainer->getAllSchedules($Object->getParameter('ShowYear'));
		}
		$Object->setParameter('StageName',$Object->getParameter('ShowPrettyStage'));
		$schedj = $this->Schedules[$Object->getParameter('ShowYear')][$Object->getParameter('ShowScheduleUID')];
		$Object->setParameter('ScheduleName',$schedj->getParameter('ScheduleName'));
		if ($Object->getParameter('ShowEmbeddedScheduleUID') != '' and $Object->getParameter('ShowEmbeddedScheduleUID') != '0'
		and isset($this->Schedules[$Object->getParameter('ShowYear')][$Object->getParameter('ShowEmbeddedScheduleUID')])){
			// There's an embedded schedule
			$schedj = $this->Schedules[$Object->getParameter('ShowYear')][$Object->getParameter('ShowEmbeddedScheduleUID')];
			$stages = $schedj->getParameter('ScheduleStages');
			$stage = $stages[$Object->getParameter('ShowEmbeddedStageID')]['Name'];
			$EmbeddedString = 'Schedule: '.$schedj->getParameter('ScheduleName').'; Stage: '.$stage;
			$Object->setParameter('ShowArtists',$EmbeddedString);
		}
		elseif (is_array($Object->getParameter('ShowArtistNames'))){
			$ArtistNames = array();
			foreach ($Object->getParameter('ShowArtistNames') as $ArtistName){
				$ArtistName = str_replace(',','__c__',$ArtistName);
				$ArtistNames[] = $ArtistName;
			}
			$Object->setParameter('ShowArtists',implode(',',$ArtistNames));
		}
		
		$Object->setParameter('Year',$Object->getParameter('ShowYear'));
		
   	}

	function postExport(& $Artist){
	}
	
	function customizeWhereClause(& $wc){
	}
    
}