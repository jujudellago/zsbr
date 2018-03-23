<?php

	$NewSchedule = new Schedule();
	$NewSchedule->setParameter('ScheduleName','Create New Schedule');
	$NewSchedule->setParameter('ScheduleID',$_POST['New_ScheduleID']);
	$NewSchedule->setParameter('ScheduleResolution',$_POST['New_ScheduleResolution']);
	$NewSchedule->setParameter('ScheduleStages',array());
	$Schedules['New'] = $NewSchedule; // Place holder for new 

	foreach ($Schedules as $Type => $Schedule){
	    $TabName = "{$Type}Tab";
	    $ScheduleTabs[] = $Type;

	    if (!isset($DefaultTab)){
	        $DefaultTab = $TabName;
	    }
    
		$$TabName = new HTML_Tab("{$Type}s",$Schedule->getParameter('ScheduleName'));
		if ($_POST['active_tab'] == 'group_'.$$TabName->getID() or $Type == $NewType){
	        $DefaultTab = $TabName;
		}
	
	    $TabSet = array();
		if ($Type != "New"){
			$SchedulePainter->setBlankCellContent("&nbsp;<a href='".$edit."&type=".urlencode($Type)."&".YEAR_PARM."=".urlencode($Year)."".'&stage=$h&day=$Heading&time=$CanonicalTime\'><img src=\''.$Package->getPackageURL().'admin/images/plus.gif\' border=0></a>');
			$SchedulePainter->setShowTitleURLCallback("getShowTitleURL");
			$Content = "<p style='margin-top:0'><a href='".$edit."&type=".urlencode($Type)."&".YEAR_PARM."=".urlencode($Year)."'>Add a ".$Schedule->getParameter('ScheduleID')." to the ".$Schedule->getParameter('ScheduleName')."</a></p>";
    
	        $Bootstrap->addTimestamp('Getting Show Listings Array for '.$Type);
	        $ShowListingsArray = $ShowContainer->getShowListingsArray($Type,$Year);
	        $Bootstrap->addTimestamp('Got Show Listings Array for '.$Type);
	        $TypeResolution = $Schedule->getParameter('ScheduleResolution'); 
	        $Bootstrap->addTimestamp('Painting Schedule for '.$Type);
	        $Content.= $SchedulePainter->paintSchedule($ShowListingsArray,$edit."&id=");
	        $Bootstrap->addTimestamp('Painted Schedule for '.$Type);
    
	        $Tab1 = new HTML_Tab("{$Type}_1","Add/Edit {$Type}s");
	        $Tab1->addPlainText('&nbsp;',$Content);
	        $TabSet[] = &$Tab1;
	    }
    
	    $Tab2 = new HTML_Tab("{$Type}_2","Change ".$Schedule->getParameter('ScheduleName')." Settings");
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
	    $Tab2->addText($id."_ScheduleName",'Schedule Name:',$Name);
	    $Tab2->addText($id."_ScheduleID",'Type of Show:',$Schedule->getParameter('ScheduleID'),HTML_FORM_TEXT_SIZE,50);
	    $Tab2->addSelect($id."_ScheduleResolution",'<nobr>Schedule Resolution:</nobr>',$ScheduleResolutions,intval($Schedule->getParameter('ScheduleResolution')));
	    $Tab2->addPlainText('Stages:','Use the area below to set the stage names and the times they run on each day.');
	    $Tab2->addPlainText('&nbsp;','Note: Start/End Times may be entered as either 2400 time <strong>or</strong> must include the am or pm.  Leave times blank to indicate stage is not running on that day.');

	    $StageTable = "<table class='schedule_table' cellpadding='5' border='1' style='border-collapse:collapse'>";
	    $StageTable.= "<tr>";
	    $StageTable.= "<td valign='bottom' rowspan='2' align='middle'>Location<br>Conjunction</td>";
	    $StageTable.= "<td valign='bottom' rowspan='2' align='middle'>Stage<br>Name</td>";
	    foreach ($FestivalDays as $Day){
	        $StageTable.= "<td valign='bottom' colspan='3' align='middle'>$Day</td>";
	    }
	    $StageTable.= "</tr>";
	    $StageTable.= "<tr>";
	    foreach ($FestivalDays as $Day){
	        $StageTable.= "<td valign='bottom' align='middle'>Start</td>";
	        $StageTable.= "<td valign='bottom' align='middle'>End</td>";
	        $StageTable.= "<td valign='bottom' align='middle'>Sponsor</td>";
	    }
	    if ($Type != "New"){
	        $StageTable.= "<td colspan='2'>&nbsp;</td>\n";
	    }
	    $StageTable.= "</tr>";
	    $StageTable.= "<tr>\n";
	    $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>on the</td>";
	    $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>Example Stage</td>";
	    $RandomStartTimes = array("","11:00am","4:00pm");
	    $RandomEndTimes = array("","6:00pm","1:00am");
	    $RandomSponsors = array("","ACME Inc.","XYZ Corp.");
	    foreach ($FestivalDays as $i => $Day){
	        $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>".$RandomStartTimes[$i % 3]."</td>";
	        $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'>".$RandomEndTimes[$i % 3]."</td>";
	        $StageTable.= "<td align='left' style='background:#dddddd;font-size:0.8em;'><nobr>".$RandomSponsors[$i % 3]."</nobr></td>";
	    }
	    if ($Type != "New"){
	        $StageTable.= "<td>&nbsp;</td>\n";
	    }
	    $StageTable.= "</tr>\n";
	    $Stages = $Schedule->getParameter('ScheduleStages'); 
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
            
	            /*  See note near TimesArray above
	            $StageTable.= "<td valign='bottom' align='middle'>";
	            $StageTable.= HTML_Form::returnSelect("{$id}_{$s}_{$d}_StartTime",$TimesArray,intval($StartTime));
	            $StageTable.= "</td>";
	            $StageTable.= "<td valign='bottom' align='middle'>";
	            $StageTable.= HTML_Form::returnSelect("{$id}_{$s}_{$d}_EndTime",$TimesArray,intval($EndTime));
	            $StageTable.= "</td>";
	            */
	        }
	        if ($Type != "New"){
	            $StageTable.= "<td valign='middle' style='text-align:left;white-space:nowrap'>\n";
	            if (count($Stage)){
	                $StageTable.= "<a href=\"javascript:confirmDeleteStage('".htmlentities($Type)."','$s');\"><img src='".$Package->getPackageURL()."admin/images/del.gif' border='0' alt='Delete Stage' title='Delete Stage'></a>\n";
	                if ($s > 0){
	                    $StageTable.= "<a href=\"javascript:moveStageUp('".htmlentities($Type)."','$s');\"><img src='".$Package->getPackageURL()."admin/images/up.gif' border='0' alt='Move Stage Up' title='Move Stage Up'></a>\n";
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
	    $StageTable.= "<p style='margin-top:0px;margin-bottom:5px'>Note: Use the blank row at the bottom to add a new stage</p>";
    
	    $Tab2->addPlainText('&nbsp;',$StageTable);
    
	    $PublishSchedule = HTML_Form::returnCheckBox($id.'_ScheduleIsPublished',($Schedule->getParameter('ScheduleIsPublished') == 1 ? true : false));
	    $PublishSchedule.= " This Schedule gets published";
	    $Tab2->addPlainText('&nbsp;',$PublishSchedule);
    
    
	    $Tab2->addSubmit('save_changes','Save Changes');
	    $TabSet[] = &$Tab2;
    
	    $$TabName->addPlainText('&nbsp',"<h2 style='margin-top:5px;margin-bottom:0'>".$Schedule->getParameter('ScheduleName').(($Type != 'New') ? "&nbsp;<a href=\"javascript:confirmDeleteSchedule('".htmlentities($Type)."')\"><img src='".$Package->getPackageURL()."admin/images/del.gif' border='0' alt='Delete Schedule' title='Delete Schedule'></a>" : "")."</h2>");

		if ($_POST['active_tab'] == 'group_'.$$TabName->getID() and $_GET['from'] == 'show'){
	        $Tab1->setDefault();
		}
		elseif ($_POST['active_tab'] == 'group_'.$$TabName->getID() or $Type == 'New' or $Type == $NewType){
	        $Tab2->setDefault();
	    }
	    else{
	        $Tab1->setDefault();
	    }
    
	    $$TabName->addTabSet($TabSet,'width=90% align=center style="padding:0 30px;padding-top:0;"');
    
	    $admin_head_extras.= $$TabName->returnTabSetScript($TabSet);

	    $form->addTab($$TabName);
	}
	$Bootstrap->addTimestamp('Added all of the tabs ');


	$admin_head_extras.= "
	    <script language='javascript' type='text/javascript'>
	    <!--
    
	    function confirmDeleteStage(Type,Stage){
	        var str = \"Are you sure you wish to delete this stage?  This cannot be undone.  \\n\\nAny \"+Type+\"s scheduled on this stage won't be deleted, but they will become unscheduled.\"; 
	        if (confirm(str)){
	            document.Update_Form.actionAction.value = 'deleteStage';
	            document.Update_Form.actionType.value = Type;
	            document.Update_Form.actionStage.value = Stage;
	            document.Update_Form.submit();
	        }
	    }
    
	    function confirmDeleteSchedule(Type){
	        var str = \"Are you sure you wish to delete this schedule?  This cannot be undone.  \\n\\nAll \"+Type+\"s on the schedule will be deleted.\"; 
	        if (confirm(str)){
	            document.Update_Form.actionAction.value = 'deleteSchedule';
	            document.Update_Form.actionType.value = Type;
	            document.Update_Form.submit();
	        }
	    }
    
	    function moveStageUp(Type,Stage){
	        document.Update_Form.actionAction.value = 'moveStageUp';
	        document.Update_Form.actionType.value = Type;
	        document.Update_Form.actionStage.value = Stage;
	        document.Update_Form.submit();
	    }
	    -->
	    </script>
	";

	if (!function_exists('getShowTitleURL')){
		function getShowTitleURL(&$Show){
		    global $edit;
		 	if (is_a($Show,'Show')){
		 	    return $edit."&id=".$Show->getParameter('ShowID');
		 	}
		 	else{
		 		die("Invalid Object Passed: ".get_class($Show));
		 	}
		}
	}

	$StatisticsTab = new HTML_Tab("Statistics","Statistics");
	$StatisticsTab->addPlainText('&nbsp;','<div id="StatisticsDiv">To View Statistics <a href="#" onclick="getStatistics()">click here</a></div>');
	$admin_head_extras.= '
	<script type="text/javascript" src="'.CMS_INSTALL_URL.'lib/js/mootools-1.2.1-core.js"></script>
	<script type="text/javascript" src="'.CMS_INSTALL_URL.'lib/js/mootools-1.2-more.js"></script>
	<script type="text/javascript" src="'.CMS_INSTALL_URL.'lib/js/php_js.js"></script>
	';
	$admin_head_extras.= "
	    <script language='javascript' type='text/javascript'>
	    <!--
	    var AjaxURL = '".CMS_INSTALL_URL.'lib/packages/Common/ajax.php'."?package=FestivalApp&".session_name()."=".htmlspecialchars(session_id())."';
    
	    function getStatistics(){
	        \$('StatisticsDiv').innerHTML = 'Retrieving Statistics...';
	        var req = new Request({
	           method: 'get',
	           url: AjaxURL,
	           data: { 'subject' : 'get_statistics', '".YEAR_PARM."' : '$Year' },
	           onComplete: function(response) { 
	                var json = \$H(JSON.decode(response, true));
	                if (json.get('result') != 'success') {
	                    alert(response);
	                }
	                else if (json.get('data') != null ) {
	                    \$('StatisticsDiv').innerHTML = 'To Refresh <a href=\"#\" onclick=\"getStatistics()\">click here</a>' + json.get('data');
	                }
	           }
	       }).send();
        
	    }
	    -->
	    </script>
    
	";

	$form->addTab($StatisticsTab);

	
	if (!count($ScheduleTabs)){
	    $MessageList->addMessage("You have not yet created any schedules for this festival.  You must define your schedules before you can add anything to them.  <a href='".$manage."'>Click here</a> to define schedules for this festival.",MESSAGE_TYPE_MESSAGE);
	}

?>