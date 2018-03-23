<?php
/************************************************************
*
*
*************************************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
    $Bootstrap->addTimestamp('Entered manage_schedules.php');
        
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'edit_schedule');
	global $manage,$edit,$manage_schedule,$schedule;
	$manage = $Bootstrap->makeAdminURL($Package,'manage');
	$edit = $Bootstrap->makeAdminURL($Package,'update_show');
	$manage_schedule = $Bootstrap->makeAdminURL($Package,'edit_schedule');
	$schedule = $Bootstrap->makeAdminURL($Package,'edit_schedule');

	include_once(PACKAGE_DIRECTORY."Common/ObjectLister.php");
	define (HTML_FORM_TH_ATTR,"valign=top align=left width='10%'");
	define (HTML_FORM_TD_ATTR,"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');
	
	define('DEFAULT_DATE',"yyyy-mm-dd");
	
    $FestivalContainer = new FestivalContainer();
    $ScheduleContainer = new ScheduleContainer();
    
    global $ShowContainer, $Festival;
	$ShowContainer = new ShowContainer();
	$SimpleShowContainer = new SimpleShowContainer();
	
    if ($_GET[YEAR_PARM]){
            $Year = urldecode($_GET[YEAR_PARM]);
    }
    elseif ($_POST['Year'] != ""){
            $Year = $_POST['Year'];
    }
    if (!$Year or !($Festival = $FestivalContainer->getFestival($Year))){
            header("Location:".$manage);
            exit();
    }	

    $Bootstrap->addTimestamp('Got Festival');
    
	$smarty->assign('title',"Manage Schedules for $Year");
    
    if ($_POST['copy_festival']){
        $ScheduleContainer->copySchedules($_POST['CopyFestival'],$Year);
        header("Location:".$manage_schedule."&".YEAR_PARM."=".urlencode($Year));
        exit();
    }
    
    if ($_POST['actionAction'] != ""){
        switch ($_POST['actionAction']){
            case 'deleteStage':
                // User has requested to delete a stage
                $ScheduleContainer->deleteStage($Year,$_POST['actionType'],$_POST['actionStage']);
                header("Location:".$manage_schedule."&".YEAR_PARM."=".urlencode($Year)."&tab=".$_POST['actionType']);
                break;
            case 'moveStageUp':
                // User has requested to move a stage up in the listing
                $ScheduleContainer->moveStageUp($Year,$_POST['actionType'],$_POST['actionStage']);
                header("Location:".$manage_schedule."&".YEAR_PARM."=".urlencode($Year)."&tab=".$_POST['actionType']);
                break;
            case 'deleteSchedule':
                $ScheduleContainer->deleteSchedule($Year,$_POST['actionType']);
                header("Location:".$manage_schedule."&".YEAR_PARM."=".urlencode($Year));
                break;
            default:
                header("Location:".$manage_schedule."&".YEAR_PARM."=".urlencode($Year));
                break;
        }
        exit();
    }
    
    if ($_GET['tab']){
        // This is to spoof the display of a particular tab
        $_POST['active_tab'] = 'group_'.HTML_Tab::returnID(urldecode($_GET['tab'])).'s';
    }

	$Festival = $FestivalContainer->getFestival($Year);
	$FestivalDays = $Festival->getPrettyDays();
		
	/******************************************************************
	*  Field Level Validation
	*  Only performed if they've submitted the form
	******************************************************************/
	if ($_POST['form_submitted'] == 'true'){
	
        foreach ($_POST as $k=>$v){
                $_POST[$k] = stripslashes(trim($v));
        }
	
		// They hit the cancel button, return to the Manage Festivals page
		if ($_POST['cancel']){
			header("Location:".$manage);
			exit();
		}
		
		if ($_POST['ScheduleTabs'] != ""){
		    $ScheduleTabs = explode(",",$_POST['ScheduleTabs']);
		}
		else{
		    $ScheduleTabs = array();
		}
				
		$Schedules = $ScheduleContainer->getAllSchedules($Year);

	    $ScheduleMessages = array();
		foreach ($ScheduleTabs as $ScheduleTab){
		    $Messages = new MessageList();
            $id = preg_replace("/[^A-Za-z0-9_]/","_",$ScheduleTab);
		    $Schedule = $Schedules[$ScheduleTab];
		    if (!$Schedule and $ScheduleTab == 'New' and $_POST[$id."_ScheduleID"] != ''){
		        $Schedule = new Schedule();
		        $Schedule->setParameter('ScheduleYear',$Year);
		    }
		    if ($Schedule){
		        if ($_POST[$id."_ScheduleID"] == ""){
		            $Messages->addMessage('You cannot blank out the Type of '.vocabulary('Show'),MESSAGE_TYPE_ERROR);
		        }
		        else{
    		        $Schedule->setParameter('ScheduleID',htmlentities($_POST[$id."_ScheduleID"]));
    		    }
		        if ($_POST[$id."_ScheduleName"] == ""){
		            $Messages->addMessage('You cannot leave the Schedule Name blank',MESSAGE_TYPE_ERROR);
		        }
		        else{
    		        $Schedule->setParameter('ScheduleName',htmlentities($_POST[$id."_ScheduleName"]));
    		    }
    		    if ($_POST[$id."_ScheduleResolution"] != $Schedule->getParameter('ScheduleResolution')){
        		    $Schedule->setParameter('ScheduleResolution',$_POST[$id."_ScheduleResolution"]);
        		    /*
        		    if ($SimpleShowContainer->hasResolutionConflicts($Schedule)){
    		            $Messages->addMessage('There are shows on the schedule that would not display properly for the resolution you chose.  Shows must start and end at times that agree with the resolution (i.e. if the resolution is 30 minutes, a show could not run from 5:15 to 6:00).  If you wish to change the resolution, first adjust the start/end times of any conflicting shows.',MESSAGE_TYPE_ERROR);
        		        $Schedule->setParameter('ScheduleResolution',$Schedule->getSavedParameter('ScheduleResolution'));
        		    }
        		    */
    		    }
    		    
    		    $ScheduleStages = $Schedule->getParameter('ScheduleStages');
    		    $ScheduleStages[] = "";
    		    foreach ($ScheduleStages as $s => $Stage){
    		        if ($Stage == ""){ // The placeholder for new stages
    		            if ($_POST[$id."_".$s."_Name"] == ""){ // Just break, they didn't add a new stage
    		                array_pop($ScheduleStages);
    		                break;
    		            }
    		            else{
    		                $ScheduleStages[$s] = array();
    		            }
    		        }
    		        $ScheduleStages[$s]['Name'] = $_POST[$id."_".$s."_Name"];
    		        $ScheduleStages[$s]['LocationConjunction'] = $_POST[$id."_".$s."_LocationConjunction"];
    		        foreach (array_keys($FestivalDays) as $d){
    		            $StartTime = make24HourTime($_POST["{$id}_{$s}_{$d}_StartTime"]);
    		            $EndTime = make24HourTime($_POST["{$id}_{$s}_{$d}_EndTime"]);
    		            if ($StartTime == "" and $EndTime == ""){
    		                $wc = new whereClause();
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowYear').'=?',$Schedule->getParameter('ScheduleYear'));
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowScheduleUID').'=?',$Schedule->getSavedParameter('ScheduleUID'));
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowDay').'=?',$d);
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowStage').'=?',$s);
    		                $Shows = $SimpleShowContainer->getAllShows($wc);
    		                if (is_array($Shows) and count($Shows)){
    		                    $Messages->addMessage('You tried to close the '.$ScheduleStages[$s]['Name'].' '.vocabulary('Stage').' on '.$FestivalDays[$d].', yet there are '.$ScheduleTab.'s scheduled on that day.  Delete or adjust them and then closing this '.vocabulary('Stage').' again.',MESSAGE_TYPE_MESSAGE);
    		                }
    		                else{
    		                    $ScheduleStages[$s]['Times'][$d] = array();
    		                }
    		            }
    		            elseif($StartTime and $EndTime){
    		                $wc = new whereClause();
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowYear').'=?',$Schedule->getParameter('ScheduleYear'));
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowScheduleUID').'=?',$Schedule->getSavedParameter('ScheduleUID'));
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowDay').'=?',$d);
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowStage').'=?',$s);
    		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowStartTime').' > ?','');
    		                $wc2 = new whereClause();
    		                $wc2->setConnector('OR');
    		                $wc2->addCondition($SimpleShowContainer->getColumnName('ShowStartTime').'<?',$StartTime);
    		                $wc2->addCondition($SimpleShowContainer->getColumnName('ShowEndTime').'>?',$EndTime);
    		                $wc->addCondition($wc2);
    		                $Shows = $SimpleShowContainer->getAllShows($wc);
    		                if (is_array($Shows) and count($Shows)){
    		                    $Messages->addMessage('You tried to change the times for '.$ScheduleTab.'s on the '.$Festival->getPrettyStageName($Schedule->getParameter('ScheduleUID'),$s).' on '.$FestivalDays[$d].'.  There are '.$ScheduleTab.'s scheduled at that time.  Delete or adjust them and then try changing these times again.',MESSAGE_TYPE_MESSAGE);
    		                }
    		                else{
    		                    $ScheduleStages[$s]['Times'][$d] = array($StartTime,$EndTime,$_POST["{$id}_{$s}_{$d}_Sponsor"]);
    		                }
    		            }
    		            else{
    		                $Messages->addMessage('Conflicting or invalid Start/End times entered (start='.$_POST["{$id}_{$s}_{$d}_StartTime"].', end='.$_POST["{$id}_{$s}_{$d}_EndTime"].').  Time changes were ignored',MESSAGE_TYPE_MESSAGE);
    		            }
    		        }
    		    }
    		    $Schedule->setParameter('ScheduleStages',$ScheduleStages);
                $Schedule->setParameter('ScheduleIsPublished',$_POST[$id.'_ScheduleIsPublished'] ? 1 : 0);
                
    		    if (!$Messages->hasErrors()){
    		        if ($ScheduleTab == 'New'){
        		        $result = $ScheduleContainer->addSchedule($Schedule);
        		        if (PEAR::isError($result)){
        		            $Schedule->setParameter('ScheduleID','New');
        		        }
        		    }
        		    else{
        		        $SavedID = $Schedule->getSavedParameter('ScheduleID');
        		        $result = $ScheduleContainer->updateSchedule($Schedule);
        		    }
        		    if (PEAR::isError($result)){
                        $Messages->addMessage($result->getMessage(),MESSAGE_TYPE_ERROR);
        		    }
        		    else{
        		        if ($Messages->hasMessages()){
        		            $Messages->addMessage('Changes successfully made except for the exceptions listed above',MESSAGE_TYPE_MESSAGE);
        		        }
        		        else{
        		            $Messages->addMessage('Schedule successfully updated.',MESSAGE_TYPE_MESSAGE);
        		        }
        		        if ($ScheduleTab == 'New'){ // Need to unset all of the POST variables for the new tab
        		            $_POST['New_ScheduleName'] = "";
        		            $_POST['New_ScheduleID'] = "";
        		            $_POST['active_tab'] = "";
        		            $Schedule->saveParameters();
        		            $NewType = $Schedule->getParameter('ScheduleID');
        		        }
        		        else{
        		            if ($Schedule->getParameter('ScheduleID') != $SavedID){
        		                $_POST['active_tab'] = 'group_'.HTML_Tab::returnID($Schedule->getParameter('ScheduleID')).'s';
        		            }
        		        }
        		    }
        		}
		        $ScheduleMessages[$Schedule->getParameter('ScheduleID')] = $Messages;
		    }
        }
	}
	
	
	/****************************************************************************
	*
	* BEGIN Display Code
	*    The following code sets how the page will actually display.  
	*
	****************************************************************************/
	// Declaration of the Form	
	$form = new HTML_TabbedForm($Bootstrap->getAdminURL(),'post','Update_Form');
	
	/***********************************************************************
	*
	*	Create the Tabs
	*
	***********************************************************************/
		$SchedulePainter = new SchedulePainter();
		$Bootstrap->addTimestamp('Getting All Schedules');
		$Schedules = $ScheduleContainer->getAllSchedules($Year);
		$Bootstrap->addTimestamp('Got All Schedules');

		$ScheduleResolutions = array();
		$ScheduleResolutions["15"] = '15 Minutes';
		$ScheduleResolutions["30"] = '30 Minutes';
		$ScheduleResolutions["60"] = '60 Minutes';
		
		$ScheduleTabs = array();

		/*  i just can't bring myself to do it this way.  it adds huge overhead for something that won't
		  get changed very often at all.  So, I'll make them plain text and add checks for them
		$TimesArray = array();
		$TimesArray[""] = "N/A";
		for ($h = 6; $h < 30; $h++){
		    for ($m = 0; $m <= 45; $m += 15){
		        $pm = ($h < 12 or $h >= 24) ? 'am' : 'pm';
		        $hour = $h;
		        if ($hour > 12){ $hour -= 12; }
		        if ($hour > 12){ $hour -= 12; }
		        $TimesArray[$h.sprintf("%02d",$m)] = sprintf("%d:%02d$pm",$hour,$m);
		    }
		}
		*/
		
		if (!count($Schedules)){
		    // This is a festival without any schedules defined.
		    // Give the user an opportunity to copy schedules from another festival
		    $Festivals = $FestivalContainer->getAllFestivals();
		    $CopyableFestivals = array();
		    foreach ($Festivals as $_Festival){
		        $_Schedules = $ScheduleContainer->getAllSchedules($_Festival->getParameter('FestivalYear'));
		        if (count($_Schedules)){
		            $CopyableFestivals[$_Festival->getParameter('FestivalYear')] = $_Festival->getParameter('FestivalYear');
		        }
		    }
		    if (count($CopyableFestivals)){
    		    $CopySchedules = new HTML_Tab("CopySchedules",'Copy Schedules');
    		    $CopySchedules->addPlainText('&nbsp;','There are currently no schedules defined for this '.vocabulary('Festival').'.  If you wish, you can define the schedules from scratch <strong>or</strong> copy schedules from another festival');
    		    $CopySchedules->addSelect('CopyFestival','<nobr>Copy Schedules from '.vocabulary('Festival').' Year:</nobr>',$CopyableFestivals);
    		    $CopySchedules->addPlainText('&nbsp;','Note: This is the only opportunity you will get to perform this time-saving action');
    		    $CopySchedules->addSubmit('copy_festival','Copy Schedules from Selected '.vocabulary('Festival'));
    		    $form->addTab($CopySchedules);
    		    $DefaultTab = 'CopySchedules';
	        }
		}

		$use_interface = (CMS_PLATFORM == 'WordPress' ? 'new' : 'old');
		//$use_interface = 'old';
		include("manage_schedules.{$use_interface}_interface.php");
		
      

	/***********************************************************************
	*
	*	Message Tab
	*
	***********************************************************************/
	// We display messages on a new tab.  this will be the default tab that displays when the page gets redisplayed	
	if ($MessageList->hasMessages()){
		$MessageTab = new HTML_Tab('Messages',$MessageList->getSeverestType());
		$MessageTab->addPlainText('Messages',"<p>&nbsp;<p>".$MessageList->toBullettedString());
		$DefaultTab = 'MessageTab';
		$form->addTab($MessageTab);
	}
	
	$$DefaultTab->setDefault();
	
	// Here are the buttons
	$form->addSubmit('cancel','Cancel');
	
	// Some hidden fields to help us out 
	$form->addHidden('form_submitted','true');
	$form->addHidden('Year',$Year);
	$form->addHidden('ScheduleTabs',htmlentities(implode(",",$ScheduleTabs)));
	$form->addHidden('actionAction','');
	$form->addHidden('actionType','');
	$form->addHidden('actionStage','');
	
	// Finally, we set the Smarty variables as needed.
	$smarty->assign('includes_tabbed_form',true);
	$smarty->assign('includes_subtabs',true);
	$smarty->assign('admin_start_function',$start_functions);
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	$smarty->assign('Tabs',$form->getTabs());
	$smarty->assign('admin_head_extras',$admin_head_extras);
	
?>
<?php   $smarty->display('admin_form.tpl'); ?>
<?php $Bootstrap->dumpTimeStamps(); // ENABLE_TIMER === true and $_GET['timer'] == 'true' ?>
