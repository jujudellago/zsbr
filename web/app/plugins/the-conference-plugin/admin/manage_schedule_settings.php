<?php
	if (!$Bootstrap){
	    die ("You cannot access this file directly");
	}

	global $manage,$edit,$manage_schedule,$schedule;
	$manage = $Bootstrap->makeAdminURL($Package,'manage');
	$manage_schedule = $Bootstrap->makeAdminURL($Package,'edit_schedule');
	$schedule_settings = $Bootstrap->makeAdminURL($Package,'schedule_settings');

	
	include_once(PACKAGE_DIRECTORY."Common/ObjectLister.php");
	define (HTML_FORM_TH_ATTR,"valign=top align=left width='10%'");
	define (HTML_FORM_TD_ATTR,"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');
	
	$ScheduleContainer = new ScheduleContainer();
	$FestivalContainer = new FestivalContainer();
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
	

	if ($_REQUEST['schedule'] == 'New'){
		$Schedule = new Schedule();
		$Schedule->setParameter('ScheduleYear',$Year);
		$Schedule->setParameter('ScheduleID','New');
	}
	else{
		$Schedule = $ScheduleContainer->getSchedule($Year,urldecode($_REQUEST['schedule']));
	}
	$manage_schedule.= "&".YEAR_PARM."=".urlencode($Year);
	if (!is_a($Schedule,'Schedule')){
        header("Location:".$manage_schedule);
        exit();
	}
	
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	if ($Schedule->getParameter('ScheduleID') != 'New'){
		$manage_schedule.= "&type=".$Schedule->getParameter('ScheduleUID');
	}
	$Bootstrap->addURLToAdminBreadcrumb($manage_schedule,$Package->admin_pages['edit_schedule']['title']);
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'schedule_settings');

	$FestivalDays = $Festival->getPrettyDays();
	
	$smarty->assign('title',"Schedule Settings for ".$Schedule->getParameter('ScheduleName'));
	
    if ($_POST['actionAction'] != ""){
        switch ($_POST['actionAction']){
            case 'deleteStage':
                // User has requested to delete a stage
                $ScheduleContainer->deleteStage($Year,$_POST['actionType'],$_POST['actionStage']);
                header("Location:".$schedule_settings."&".YEAR_PARM."=".urlencode($Year)."&schedule=".$_POST['actionType']);
                break;
            case 'moveStageUp':
                // User has requested to move a stage up in the listing
                $ScheduleContainer->moveStageUp($Year,$_POST['actionType'],$_POST['actionStage']);
                header("Location:".$schedule_settings."&".YEAR_PARM."=".urlencode($Year)."&schedule=".$_POST['actionType']);
                break;
            case 'sortAlpha':
                // User has requested to sort the stages alphabetically
                $ScheduleContainer->sortStages($Year,$_POST['actionType']);
                header("Location:".$schedule_settings."&".YEAR_PARM."=".urlencode($Year)."&schedule=".$_POST['actionType']);
                break;
            default:
                header("Location:".$manage_schedule);
                break;
        }
        exit();
    }
	
	
	
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
			header("Location:".$manage_schedule);
			exit();
		}
		
	    $ScheduleMessages = array();
	    $Messages = new MessageList();
	    if ($Schedule){
			$id = preg_replace('/[^a-zA-Z0-9_]/','_',$Schedule->getParameter('ScheduleID'));
	        if ($_POST[$id."_ScheduleID"] == ""){
	            $Messages->addMessage('You cannot leave the Schedule Item Name blank.',MESSAGE_TYPE_ERROR);
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
   		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowUID').'=?',$Schedule->getSavedParameter('ScheduleUID'));
   		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowDay').'=?',$d);
   		                $wc->addCondition($SimpleShowContainer->getColumnName('ShowStage').'=?',$s);
   		                $Shows = $SimpleShowContainer->getAllShows($wc);
   		                if (is_array($Shows) and count($Shows)){
   		                    $Messages->addMessage('You tried to close the '.$ScheduleStages[$s]['Name'].' '.vocabulary('Stage').' on '.$FestivalDays[$d].', yet there are '.$id.'s scheduled on that day.  Delete or adjust them and then closing this stage again.',MESSAGE_TYPE_MESSAGE);
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
   		                    $Messages->addMessage('You tried to change the times for '.$id.'s on the '.$Festival->getPrettyStageName($Schedule->getParameter('ScheduleUID'),$s).' on '.$FestivalDays[$d].'.  There are '.$id.'s scheduled at that time.  Delete or adjust them and then try changing these times again. '.$wc->getRealString(),MESSAGE_TYPE_MESSAGE);
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
   		        if ($id == 'New'){
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
       		        if ($id == 'New'){ // Need to unset all of the POST variables for the new tab
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
	
	
	/****************************************************************************
	*
	* BEGIN Display Code
	*    The following code sets how the page will actually display.  
	*
	****************************************************************************/
	// Declaration of the Form	
	$form = new HTML_TabbedForm($Bootstrap->getAdminURL(),'post','Update_Form');

	$Type = $Schedule->getParameter('ScheduleID');
    $Tab2 = new HTML_Tab("Schedule","Change ".$Schedule->getParameter('ScheduleName')." Settings");
    if (is_array($ScheduleMessages) and is_a($ScheduleMessages[$Type],'MessageList') and $ScheduleMessages[$Type]->hasMessages()){
        $Tab2->addPlainText('Messages',"<p>&nbsp;<p>".$ScheduleMessages[$Type]->toBullettedString());
    }
    $id = preg_replace("/[^A-Za-z0-9_]/","_",$Type);
    if ($Type == 'New'){
        $Name = $_POST['New_ScheduleName'];
    }
    else{
        $Name = $Schedule->getParameter('ScheduleName');
    }
	$ScheduleResolutions = array();
	$ScheduleResolutions["5"] = '5 Minutes';
	$ScheduleResolutions["15"] = '15 Minutes';
	$ScheduleResolutions["20"] = '20 Minutes';
	$ScheduleResolutions["30"] = '30 Minutes';
	$ScheduleResolutions["60"] = '60 Minutes';
	
	if ($Schedule->getParameter('ScheduleUID') != ""){
	    $Tab2->addPlainText('Schedule ID:',$Schedule->getParameter('ScheduleUID'));
	}
    $Tab2->addText($id."_ScheduleName",'Schedule Name:',$Name);
    $Tab2->addText($id."_ScheduleID",'Schedule Item Name:',($Schedule->getParameter('ScheduleID') != 'New' ? $Schedule->getParameter('ScheduleID') : vocabulary('Show')),HTML_FORM_TEXT_SIZE,50);
    $Tab2->addSelect($id."_ScheduleResolution",'<nobr>Schedule Resolution:</nobr>',$ScheduleResolutions,intval($Schedule->getParameter('ScheduleResolution')));
    $Tab2->addPlainText(pluralize('Stage').':','Use the area below to set the '.vocabulary('Stage').' names and the times they run on each day.');
    $Tab2->addPlainText('&nbsp;','Note: Start/End Times may be entered as either 2400 time <strong>or</strong> must include the am or pm.  Leave times blank to indicate '.vocabulary('Stage').' is not running on that day.');

    $StageTable = "<table class='schedule_table' cellpadding='5' border='1' style='border-collapse:collapse'>";
    $StageTable.= "<tr>";
    $StageTable.= "<td valign='bottom' rowspan='2' align='middle'>Location<br>Conjunction</td>";
    $StageTable.= "<td valign='bottom' rowspan='2' align='middle'>".vocabulary('Stage')."<br>Name</td>";
    foreach ($FestivalDays as $Day){
        $StageTable.= "<td valign='bottom' colspan='3' align='middle'>$Day</td>";
    }
    if ($Type != "New"){
        $StageTable.= "<td rowspan='3'><input type='button' value='A-Z' onclick='sortAlpha(".$Schedule->getParameter('ScheduleUID').")'></td>\n";
    }
    $StageTable.= "</tr>";
    $StageTable.= "<tr>";
    foreach ($FestivalDays as $Day){
        $StageTable.= "<td valign='bottom' align='middle'>Start</td>";
        $StageTable.= "<td valign='bottom' align='middle'>End</td>";
        $StageTable.= "<td valign='bottom' align='middle'>Sponsor</td>";
    }
    $StageTable.= "</tr>";
    $StageTable.= "<tr>\n";
    $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>on the</td>";
    $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>Example ".vocabulary('Stage')."</td>";
    $RandomStartTimes = array("","11:00am","4:00pm");
    $RandomEndTimes = array("","6:00pm","1:00am");
    $RandomSponsors = array("","ACME Inc.","XYZ Corp.");
    foreach ($FestivalDays as $i => $Day){
        $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>".$RandomStartTimes[$i % 3]."</td>";
        $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>".$RandomEndTimes[$i % 3]."</td>";
        $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'><nobr>".$RandomSponsors[$i % 3]."</nobr></td>";
    }
    $StageTable.= "</tr>\n";
    $Stages = $Schedule->getParameter('ScheduleStages'); 
	if (!is_array($Stages)){
		$Stages = array();
	}
    $Stages[] = array();
    foreach ($Stages as $s => $Stage){
        $StageTable.= "<tr>\n";
        $StageTable.= "<td valign='bottom'><input type='text' name='{$id}_{$s}_LocationConjunction' value=\"".htmlspecialchars($Stage['LocationConjunction'])."\"></td>\n";
        $StageTable.= "<td valign='bottom'><input type='text' name='{$id}_{$s}_Name' value=\"".htmlspecialchars($Stage['Name'])."\"></td>\n";
        foreach ($FestivalDays as $d => $Day){
            if (count($Stage['Times'][$d])){
                $StartTime = Festival::getPrettyTime($Stage['Times'][$d][0]);
                $EndTime   = Festival::getPrettyTime($Stage['Times'][$d][1]);
                $Sponsor = $Stage['Times'][$d][2];
            }
            else{
                $StartTime = "";
                $EndTime   = "";
                $Sponsor = "";
            }
            $StageTable.= "<td valign='bottom' align='middle'><input type='text' size='8' name='{$id}_{$s}_{$d}_StartTime' value=\"".htmlspecialchars($StartTime)."\"></td>\n";
            $StageTable.= "<td valign='bottom' align='middle'><input type='text' size='8' name='{$id}_{$s}_{$d}_EndTime' value=\"".htmlspecialchars($EndTime)."\"></td>\n";
            $StageTable.= "<td valign='bottom' align='middle'><input type='text' size='8' name='{$id}_{$s}_{$d}_Sponsor' value=\"".htmlspecialchars($Sponsor)."\"></td>\n";
       
        }
        if ($Type != "New"){
            $StageTable.= "<td valign='middle' style='text-align:left;white-space:nowrap'>\n";
            if (count($Stage)){
                $StageTable.= "<a href=\"javascript:confirmDeleteStage('".htmlentities($Schedule->getParameter('ScheduleUID'))."','$s');\"><img src='".$Package->getPackageURL()."admin/images/del.gif' border='0' alt='Delete Stage' title='Delete Stage'></a>\n";
                if ($s > 0){
                    $StageTable.= "<a href=\"javascript:moveStageUp('".htmlentities($Schedule->getParameter('ScheduleUID'))."','$s');\"><img src='".$Package->getPackageURL()."admin/images/up.gif' border='0' alt='Move Stage Up' title='Move Stage Up'></a>\n";
                }
            }
            else{
                $StageTable.= "&nbsp;";
            }
            $StageTable.= "</td>\n";
        }
        $StageTable.= "</tr>\n";
    }
    $StageTable.= "</table>";
    $StageTable.= "<p style='margin-top:0px;margin-bottom:5px'>Note: Use the blank row at the bottom to add a new ".vocabulary('Stage')."</p>";

    $Tab2->addPlainText('&nbsp;',$StageTable);

    $PublishSchedule = HTML_Form::returnCheckBox($id.'_ScheduleIsPublished',($Schedule->getParameter('ScheduleIsPublished') == 1 ? true : false));
    $PublishSchedule.= " This Schedule gets published";
    $Tab2->addPlainText('&nbsp;',$PublishSchedule);


    $Tab2->addSubmit('save_changes','Save Changes');
	$Tab2->addSubmit('cancel','Cancel');
	$Tab2->setDefault();
    $form->addTab($Tab2);

	$admin_head_extras.= "
	    <script language='javascript' type='text/javascript'>
	    <!--

	    function confirmDeleteStage(Type,Stage){
	        var str = \"Are you sure you wish to delete this ".vocabulary('Stage')."?  This cannot be undone.  \\n\\nAny ".pluralize('Show')." scheduled on this stage won't be deleted, but they will become unscheduled.\"; 
	        if (confirm(str)){
	            document.Update_Form.actionAction.value = 'deleteStage';
	            document.Update_Form.actionType.value = Type;
	            document.Update_Form.actionStage.value = Stage;
	            document.Update_Form.submit();
	        }
	    }

	    function moveStageUp(Type,Stage){
	        document.Update_Form.actionAction.value = 'moveStageUp';
	        document.Update_Form.actionType.value = Type;
	        document.Update_Form.actionStage.value = Stage;
	        document.Update_Form.submit();
	    }
	
		function sortAlpha(Type){
	        document.Update_Form.actionAction.value = 'sortAlpha';
	        document.Update_Form.actionType.value = Type;
	        document.Update_Form.submit();
		}
	    -->
	    </script>
	";

	// Here are the buttons

	// Some hidden fields to help us out 
	$form->addHidden('form_submitted','true');
	$form->addHidden('Year',$Year);
	$form->addHidden('actionAction','');
	$form->addHidden('actionType','');
	$form->addHidden('actionStage','');
	$form->addHidden('schedule',($Schedule->getParameter('ScheduleUID') != "" ? $Schedule->getParameter('ScheduleUID') : 'New'));

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
