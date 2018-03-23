<?php
/************************************************************
*
*
*************************************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$manage = $Bootstrap->makeAdminURL($Package,'edit_schedule');
	$delete = $Bootstrap->makeAdminURL($Package,'delete_show');
	$update_artist = $Bootstrap->makeAdminURL($Package,'update_artist');

    define (HTML_FORM_TH_ATTR,"valign=top align=left width='15%'");
    define (HTML_FORM_TD_ATTR,"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');
    
    $FestivalArtistContainer = new FestivalArtistContainer();
    $FestivalContainer = new FestivalContainer();
    $ScheduleContainer = new ScheduleContainer();
    $ShowContainer = new ShowContainer();

    $id    = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : ""); 
    
    if ($id){
            $Show = $ShowContainer->getShow($id);
    }
    $ShowArtistIDs = array();
    if (!$Show){
            $Show = new Show();
            $year    = isset($_GET[YEAR_PARM]) ? urldecode($_GET[YEAR_PARM]) : (isset($_POST[YEAR_PARM]) ? $_POST[YEAR_PARM] : ""); 
            $schedule_uid = isset($_GET['type']) ? urldecode($_GET['type']) : (isset($_POST['type']) ? $_POST['type'] : ""); 
            $stage    = isset($_GET['stage']) ? $_GET['stage'] : (isset($_POST['stage']) ? $_POST['stage'] : ""); 
            $day    = isset($_GET['day']) ? $_GET['day'] : (isset($_POST['day']) ? $_POST['day'] : ""); 
            $time    = isset($_GET['time']) ? $_GET['time'] : (isset($_POST['time']) ? $_POST['time'] : ""); 
            if ($day != ""){
                    $Show->setParameter('ShowDay',$day);
            }
            if ($stage != ""){
                    $Show->setParameter('ShowStage',$stage);
            }
            if ($time != ""){
                    $Show->setParameter('ShowStartTime',$time);
                    $Show->setParameter('ShowEndTime',$time + 100); // default to 1 hour slots
            }
    }
    else{
            $year = $Show->getParameter('ShowYear');
            $schedule_uid = $Show->getParameter('ShowScheduleUID');
            foreach ($Show->getParameter('ShowArtists') as $Artist){
                $ShowArtistIDs[] = $Artist->getParameter('ArtistID');
            }
    }

	$Schedule = $ScheduleContainer->getSchedule($year,$schedule_uid);
	if (is_a($Schedule,'Schedule')){
		$ShowType = $Schedule->getParameter('ScheduleID');
	}
    
    
	$returnURL = $manage."&".YEAR_PARM."=".urlencode($year)."&type=".urlencode($schedule_uid)."&from=show".($Show->getParameter('ShowDay') != "" ? '&day='.$Show->getParameter('ShowDay') : '');
	$Bootstrap->addURLToAdminBreadcrumb($returnURL,$Package->admin_pages['edit_schedule']['title']);
	if ($Show->getParameter('ShowID') != '' and $Show->getParameter('ShowDay') == '' and $_POST['ShowDay'] == ''){
		$orphansURL = $Bootstrap->makeAdminURL($Package,'schedule_orphans')."&".YEAR_PARM."=".urlencode($year)."&schedule=".urlencode($schedule_uid);
		$Bootstrap->addURLToAdminBreadcrumb($orphansURL,$Package->admin_pages['schedule_orphans']['title']);
	}
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'update_show');

    // We need the year defined, so if it isn't, return to the manage page.
    if (empty($year)){
        header("Location:".$manage);
        exit();
    }
    $Festival = $FestivalContainer->getFestival($year);


    if ($_POST['show_all']){
        $_POST['form_submitted'] = 'false';
        
    }
 

    /******************************************************************
    *  Field Level Validation
    *  Only performed if they've submitted the form
    ******************************************************************/
    if ($_POST['form_submitted'] == 'true'){
    
        // They hit the cancel button, return to the Manage Pages page
        if ($_POST['cancel']){
            header("Location:".$returnURL);
            exit();
        }
        if ($_POST['delete']){
            header("Location:".$delete."&id=$id&".YEAR_PARM."=".urlencode($year));
            exit();
        }
        
        if ($_POST['ShowSwitchWith'] != ""){
            $SwitchWith = $ShowContainer->getShow($_POST['ShowSwitchWith']);
            $Target = array();
            $Target['ShowDay'] = $SwitchWith->getParameter('ShowDay');
            $Target['ShowStage'] = $SwitchWith->getParameter('ShowStage');
            $Target['ShowStartTime'] = $SwitchWith->getParameter('ShowStartTime');
            $Target['ShowEndTime'] = $SwitchWith->getParameter('ShowEndTime');
            foreach ($Target as $parm => $value){
                $SwitchWith->setParameter($parm,$Show->getParameter($parm));
                $Show->setParameter($parm,$value);
            }
            $ShowContainer->updateShow($SwitchWith);
            $ShowContainer->updateShow($Show);
            header("Location:".$Bootstrap->makeAdminURL($Package,'update_show')."&id=".$Show->getParameter('ShowID')."&switched=true");
            exit();
        }
        
        /******************************************************************
        *  BEGIN EDITS
        *  If an edit fails, it adds an error to the message list.  
        ******************************************************************/
        $ShowArtistIDs = explode('&xi;',$_POST['strShowArtists']);
        if ($_POST['ShowTitle'] == "" and $_POST['strShowArtists'] == ""){
            $MessageList->addMessage("You must specify a title or some ".pluralize('Artist')." for this ".vocabulary('Show')."",MESSAGE_TYPE_ERROR);
        }
                
                if (($_POST['ShowStartTime'] == "" and $_POST['ShowEndTime'] != "") or
                    ($_POST['ShowEndTime'] < $_POST['ShowStartTime'])){
            $MessageList->addMessage("The Start Time must precede the End Time",MESSAGE_TYPE_ERROR);
                }
        
        
        /******************************************************************
        *  END EDITS
        ******************************************************************/
                




                        
        /******************************************************************
        *  BEGIN Set Parameters
        ******************************************************************/
        $Show->setParameter('ShowYear',$year);
        $Show->setParameter('ShowType',$ShowType);
        $Show->setParameter('ShowScheduleUID',$schedule_uid);
        $Show->setParameter('ShowTitle',$_POST['ShowTitle']);
        $Show->setParameter('ShowDescription',$_POST['ShowDescription']);
        $Show->setParameter('ShowSponsor',$_POST['ShowSponsor']);
        $Show->setParameter('ShowNotesToArtist',$_POST['ShowNotesToArtist']);
        if (isset($_POST['ShowRepeatOfShowID'])){
            $Show->setParameter('ShowRepeatOfShowID',$_POST['ShowRepeatOfShowID']);
        }
                if ($_POST['ShowDay'] != ""){
                        $Show->setParameter('ShowDay',$_POST['ShowDay']);
                }
                else{
                        $Show->setParameter('ShowDay',null);
                }
                if ($_POST['ShowDay'] != "" and $_POST['ShowStage'] != ""){
                        $Show->setParameter('ShowStage',$_POST['ShowStage']);
                }
                else{
                        $Show->setParameter('ShowStage',null);
                }
                if ($_POST['ShowDay'] != "" and $_POST['ShowStage'] != "" and $_POST['ShowStartTime'] != "" and $_POST['ShowEndTime'] != ""){
                        $Show->setParameter('ShowStartTime',$_POST['ShowStartTime']);
                        $Show->setParameter('ShowEndTime',$_POST['ShowEndTime']);
                }
                else{
                        $Show->setParameter('ShowStartTime',null);
                        $Show->setParameter('ShowEndTime',null);
                }
                
                $Conflicts = $ShowContainer->getConflicts($Show);
                foreach ($Conflicts as $Conflict){
            $MessageList->addMessage($Conflict,MESSAGE_TYPE_ERROR);
                }

		if ($_POST['ShowEmbeddedScheduleUID'] != ""){
			$Show->setParameter('ShowEmbeddedScheduleUID',$_POST['ShowEmbeddedScheduleUID']);
			$Show->setParameter('ShowEmbeddedStageID',$_POST['ShowEmbeddedStageID']);
			if ($Show->getParameter('ShowEmbeddedStageID') == ''){
				$MessageList->addMessage('You must specify the '.vocabulary('Stage').' for the embedded schedule (on the '.pluralize('Artist').' tab).',MESSAGE_TYPE_ERROR);
			}
		}
		else{
			$Show->setParameter('ShowEmbeddedScheduleUID','');
			$Show->setParameter('ShowEmbeddedStageID','');
		}
                
        /******************************************************************
        *  END Set Parameters
        ******************************************************************/
        
        // If there are no messages/errors, then go ahead and do the update (or add)
        // Note: if they were deleting a version, then there will be a message, so
        // this section won't get performed
        if (!$MessageList->hasMessages()){
            if ($Show->getSavedParameter('ShowID') == ""){
                // It's a new show
                $result = $ShowContainer->addShow($Show);
                $id = $Show->getParameter('ShowID');
            }
            else{               
                $result = $ShowContainer->updateShow($Show);
            }
            if (PEAR::isError($result)){
                $MessageList->addPearError($result);
            }
                        
            $result = $ShowContainer->setShowLineup($Show->getParameter('ShowID'),$ShowArtistIDs,$_POST['HostArtistID']);
            if (PEAR::isError($result)){
                $MessageList->addPearError($result);
            }
                        
            if (!$MessageList->hasMessages()){
				if ($_POST['action'] == 'alpha_last'){
					$ShowContainer->sortLineupByLastName($Show->getParameter('ShowID'));
				}
                $MessageList->addMessage("".vocabulary('Show')." Successfully Updated");
                
                // Not the most elegant.  I have to call this function if it's a new Show
                // to properly get the Show Artists ($Show->getParameter('ShowArtists'))
                    $Show = $ShowContainer->getShow($Show->getParameter('ShowID'));
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
    $DefaultTab = 'ShowTab';

        
    
    
    /***********************************************************************
    *
    *   Show Tab
    *
    ***********************************************************************/
    
    $ShowTab = new HTML_Tab('Show',$ShowType);
        $Cell = HTML_Form::returnText('ShowTitle',$Show->getParameter('ShowTitle'));
        $Cell.= " (leave blank to use ".vocabulary('Artist')."'s name)";
        $ShowTab->addPlainText("$ShowType Title:",$Cell);
        
        $ShowTab->addTextArea('ShowDescription', 'Brief Description:',$Show->getParameter('ShowDescription'),60,12,0);
        $ShowTab->addText('ShowSponsor', 'Sponsored By:',$Show->getParameter('ShowSponsor'));
        $ShowTab->addTextArea('ShowNotesToArtist', 'Notes To '.pluralize('Artist').':',$Show->getParameter('ShowNotesToArtist'),50,3,0);
        
        //$Schedule = $ScheduleContainer->getSchedule($year,$schedule_uid);
        
        $TypeStages = $Schedule->getParameter('ScheduleStages');
        
        // Create the drop down box for day
        $s = array();
        $s[""] = "&lt;Select a Day&gt;";
        foreach ($TypeStages as $Stage){
                foreach ($Stage["Times"] as $i => $DayTimes){
                
                        if (is_array($DayTimes) and count($DayTimes)){
                                $s[$i] = $Festival->getPrettyDay($i);
                        }
                }
        }
        uksort($s,'sortDays');
        function sortDays($a,$b){ if ($a === "") return -1; elseif ($b === "") return 1; else return $a > $b;}
        $ShowTab->addSelect('ShowDay',"Day:",$s,$Show->getParameter('ShowDay'),1,'',false,"id='ShowDay' onchange='javascript:updateStagesAndTimes();'");
    
            // Create the drop down box for stage
        $s = array();
        $s[""] = "&lt;Select a ".vocabulary('Stage')."&gt;";
        foreach ($TypeStages as $i => $Stage){
                $s[$i] = $Stage["Name"];
        }
        $StageSelectCell = "";
        $StageSelectCell.= "<span id='StageDropdownList' style='display:none'>".HTML_Form::returnSelect('ShowStage',$s,$Show->getParameter('ShowStage'),1,'',false,"id='ShowStage' onchange='javascript:updateTimes();'");
        $StageSelectCell.= "<span id='StageTimes'>&nbsp;</span>\n";
        $StageSelectCell.= "</span>\n";
        $StageSelectCell.= "<span id='StageMessage' style='display:none'>Select the day first</span>\n";
        $ShowTab->addPlainText(vocabulary('Stage').':',$StageSelectCell);
        
        // Create the dropdown box for the Start Time
    $s = array();
        $StartTimeSelectCell.= "<span id='StartTimeDropdownList' style='display:none'>".HTML_Form::returnSelect('ShowStartTime',$s,$Show->getParameter('ShowStartTime'),1,'',false,"id='ShowStartTime' onchange='javascript:updateEndTime();'");
        $StartTimeSelectCell.= "</span>\n";
        $StartTimeSelectCell.= "<span id='StartTimeMessage' style='display:none'>Select the Day & ".vocabulary('Stage')." first</span>\n";
        $ShowTab->addPlainText('Start Time:',$StartTimeSelectCell);

        // Create the dropdown box for the End Time
    $s = array();
        $EndTimeSelectCell.= "<span id='EndTimeDropdownList' style='display:none'>".HTML_Form::returnSelect('ShowEndTime',$s,$Show->getParameter('ShowEndTime'),1,'',false,"id='ShowEndTime'");
        $EndTimeSelectCell.= "</span>\n";
        $ShowTab->addPlainText('End Time:',$EndTimeSelectCell);


        // Some Javascript to help with the Times
        $Festival = $FestivalContainer->getFestival($year);
        $StageTimes = $Festival->getStageTimesArray($schedule_uid);
        $StageTimesScript = "
        <script language='JavaScript' type='text/javascript'> 
        <!-- 
                var Selects = new Array();
                var Stages  = new Array();
        ";
        foreach ($StageTimes as $Stage => $DayTime){
                $StageTimesScript.= "Selects[$Stage] = new Array();\n";
                foreach ($DayTime as $Day => $TimeArray){
                        $StageTimesScript.= "Selects[$Stage][$Day] = new Array();\n";
                        $Start = current(array_keys($TimeArray));
                        $StageTimesScript.= "Selects[$Stage][$Day]['Start'] = '$Start';\n";
                        $End = array_pop(array_keys($TimeArray));
                        $StageTimesScript.= "Selects[$Stage][$Day]['End'] = '$End';\n";
                }
        }          

        
        $StageNames = $Festival->getStageNamesArray($schedule_uid);
        foreach($StageNames as $Day => $StageNamesArray){
                if (count($StageNamesArray)){
                        $StageTimesScript.= "Stages[$Day] = new Array();\n";
                        $StageTimesScript.= "Stages[$Day][''] = '<Select a ".vocabulary('Stage').">';\n";
                        foreach ($StageNamesArray as $s => $StageName){
                                $StageTimesScript.= "Stages[$Day][$s] = '".addslashes($StageName)."';\n";
                        }
                }
        }

                
        $StageTimesScript.= "                
                function switchSelect(theSel,theArray,selectedValue){
                        // First, remove all of the current elements
                        var x=document.getElementById(theSel);
                        var o;
                        for (var i = x.length - 1; i>=0; i--){
                                x.remove(i);
                        }
                        // Now add in all from the selected Selects element
                        for (t in theArray){
                            if (t == '' || t == '0' || parseInt(t)){
                                o=document.createElement('option');
                                o.text = theArray[t];
                                o.value = t;
                                try
                                  {
                                  x.add(o,null); // standards compliant
                                  }
                                catch(ex)
                                  {
                                  x.add(o); // IE only
                                  }
                                if (t == selectedValue){
                                        x.options.selectedIndex = x.length - 1;
                                }
                            }
                        }                
                }
                
                function switchTimesSelect(theSel,theArray,selectedValue){
                        // First, remove all of the current elements
                        var x=document.getElementById(theSel);
                        var o,hour,minute,xm;
                        for (var i = x.length - 1; i>=0; i--){
                                x.remove(i);
                        }
                        
                        o=document.createElement('option');
                        o.text = '<Select a Time>';
                        o.value = '';
                        try
                          {
                          x.add(o,null); // standards compliant
                          }
                        catch(ex)
                          {
                          x.add(o); // IE only
                          }
                        
                        // Now calculate the times to put in there
                        for (i = parseInt(theArray['Start'].replace(/^0+/,'')); i <=  parseInt(theArray['End'].replace(/^0+/,'')); i = i + 5){
                                o=document.createElement('option');
                                if (i < 1000){
                                    hour = parseInt(i.toString().substring(0,1));
                                    minute = parseInt(i.toString().substring(1,3));
                                }
                                else{
                                    hour = parseInt(i.toString().substring(0,2));
                                    minute = parseInt(i.toString().substring(2,4));
                                }
                                if (minute >= 60){
                                    minute -= 60;
                                    hour += 1;
                                    i += 40;
                                }

                                if (hour >= 24){
                                    if (hour == 24) { 
                                        hour = 12; 
                                    }
                                    else {                            
                                        hour -= 24;
                                    }
                                    xm = 'am';
                                }
                                else if (hour < 12){
                                    xm = 'am';
                                }
                                else{
                                    if (hour > 12){
                                        hour -= 12;
                                    }
                                    xm = 'pm';
                                }
                                if (minute < 10){
                                    minute = '0' + minute.toString();
                                }
                                o.text = hour + ':' + minute + xm;
                                if (i < 1000){
                                    o.value = '0' + i;
                                }
                                else{
                                    o.value = i;
                                }
                                try
                                  {
                                  x.add(o,null); // standards compliant
                                  }
                                catch(ex)
                                  {
                                  x.add(o); // IE only
                                  }
                                if (parseInt(t) == parseInt(selectedValue)){
                                        x.options.selectedIndex = x.length - 1;
                                }
                        }
                }
                
                function updateStagesAndTimes(){
                        updateStages();
                        updateTimes();
                }
                
                function updateStages(){
                        var DayControl = document.getElementById('ShowDay');
                        var day = DayControl.options[DayControl.options.selectedIndex].value;
                        var StageControl = document.getElementById('ShowStage');
                        var stage = StageControl.options[StageControl.options.selectedIndex].value;
                        if (day == ''){
                                document.getElementById('StageDropdownList').style.display = 'none';
                                document.getElementById('StageMessage').style.display = 'inline';
                        }
                        else{
                                document.getElementById('StageDropdownList').style.display = 'inline';
                                document.getElementById('StageMessage').style.display = 'none';
                                switchSelect('ShowStage',Stages[day],stage);
                        }
                }
                
                function updateTimes(){
                        var DayControl = document.getElementById('ShowDay');
                        var StageControl = document.getElementById('ShowStage');
                        var stage = StageControl.options[StageControl.options.selectedIndex].value;
                        var day = DayControl.options[DayControl.options.selectedIndex].value;
                        var StartTimeControl = document.getElementById('ShowStartTime');
                        var starttime, endtime;
                        if (StartTimeControl.options.selectedIndex >= 0){
                                starttime = StartTimeControl.options[StartTimeControl.options.selectedIndex].value;
                        }
                        var EndTimeControl = document.getElementById('ShowEndTime');
                        if (EndTimeControl.options.selectedIndex >= 0){
                                endtime = EndTimeControl.options[EndTimeControl.options.selectedIndex].value;
                        }
                        if (day == '' || stage == ''){
                                document.getElementById('StartTimeDropdownList').style.display = 'none';
                                document.getElementById('StartTimeMessage').style.display = 'inline';
                                document.getElementById('EndTimeDropdownList').style.display = 'none';
								document.getElementById('StageTimes').innerHTML = '';
                        }
                        else{
                                document.getElementById('StartTimeDropdownList').style.display = 'inline';
                                document.getElementById('StartTimeMessage').style.display = 'none';
                                document.getElementById('EndTimeDropdownList').style.display = 'inline';
                                switchTimesSelect('ShowStartTime',Selects[stage][day],starttime);
                                switchTimesSelect('ShowEndTime',Selects[stage][day],endtime);
                                StartTimeControl.remove(StartTimeControl.length - 1);
                                EndTimeControl.remove(1);
                                document.getElementById('StageTimes').innerHTML = '(".vocabulary('Stage')." runs from ' + StartTimeControl.options[1].text + ' to ' + EndTimeControl.options[EndTimeControl.length - 1].text + ')';
                        }
                
                }
                
                function updateEndTime(){
                        var StartTimeControl = document.getElementById('ShowStartTime');
                        var EndTimeControl = document.getElementById('ShowEndTime');
                        if (StartTimeControl.options.selectedIndex == 0){
                                EndTimeControl.options.selectedIndex = 0;
                        }
                        else {
                                if (EndTimeControl.options.selectedIndex < StartTimeControl.options.selectedIndex){
                                        EndTimeControl.options.selectedIndex = StartTimeControl.options.selectedIndex;
                                }
                        }
                }
                
                function setInitialValues(){
                        setInitialValue('ShowDay','".$Show->getParameter('ShowDay')."');
                        updateStages();
                        setInitialValue('ShowStage','".addslashes($Show->getParameter('ShowStage'))."');
                        updateTimes();
                        setInitialValue('ShowStartTime','".$Show->getParameter('ShowStartTime')."');
                        setInitialValue('ShowEndTime','".$Show->getParameter('ShowEndTime')."');
                }
                
                function setInitialValue(theSel,theValue){
                        var theControl = document.getElementById(theSel);
                        for(var i=0;i<theControl.length;i++){
                                if (theControl.options[i].value == theValue){
                                        theControl.selectedIndex = i;
                                }
                        }
                }
				
				getWPAjaxURL = function(){
					return '".get_bloginfo('wpurl')."/wp-admin/admin-ajax.php?nocache='+new Date().getTime();
				}

				function load(what){
					jQuery('#'+what+'Div').html('');
					var interval = setInterval(function(){
						var current = jQuery('#'+what+'Div').html();
						if (current == '. . . . . . . . . . '){
							current = ''
						}
						jQuery('#'+what+'Div').html(current + '. ');
					},250);
					jQuery.get(getWPAjaxURL(),
						{
							'action':'admin_show_data',
							'what':what,
							'".YEAR_PARM."':jQuery('#".YEAR_PARM."').val(),
							'show_id':jQuery('#id').val(),
							'schedule':jQuery('#type').val()
						}
						,function(data){
							clearInterval(interval);
							jQuery('#'+what+'Div').html(data);
						}
					);
				}
        -->
        </script>
        ";
        
    $admin_head_extras.=$StageTimesScript;
    

    $ShowTab->addPlainText('Switch with:','<div id="SwitchWithDiv"><a href="javascript:load(\'SwitchWith\')">click for options</a></div>');
    $ShowTab->addPlainText('Repeat of '.vocabulary('Show').':','<div id="RepeatOfShowDiv"><a href="javascript:load(\'RepeatOfShow\')">click for options</a></div>');
    
    if ($Bootstrap->packageExists('Tags') and $Show->getParameter('ShowTitle') != ""){
        $TagText = $Show->getParameter('ShowTitle')." (".$Show->getParameter('ShowYear').")";
        $Bootstrap->usePackage('Tags');
        $TagContainer = new TagContainer();
        $TaggedObjectContainer = new TaggedObjectContainer();
        $Tags = $TaggedObjectContainer->getTagsForObject($Show);
        if (!$Tags){
            $Tag = new Tag();
            $Tag->setParameter('TagText',$TagText);
            $TagContainer->addTag($Tag);
        }
        else{
            $Tag = current($Tags);
            if ($Tag->getParameter('TagText') != $TagText){
                $Tag->setParameter('TagText',$TagText);
                $TagContainer->updateTag($Tag);
            }
        }
        $TaggedObjectContainer->addTagToObject($Tag,$Show);
        $ShowTab->addPlainText('Tag:',"<strong>".$Tag->getParameter('TagText')."</strong><br /><strong>Use:</strong> Any photos tagged with this tag will become associated with this show");
    }
    
    /***********************************************************************
    *
    *   Artists Tab
    *
    ***********************************************************************/
        $ArtistListBoxScript = "
        <script language='JavaScript' type='text/javascript'> 
        <!-- 
        var NS4 = (navigator.appName == 'Netscape' && parseInt(navigator.appVersion) < 5); 

        function addOption(theSel, theText, theValue, orderAlpha) 
        { 
          var newOpt = new Option(theText, theValue); 
          var selLength = theSel.length; 
		  if (orderAlpha || orderAlpha == undefined){
	          for (var x = 0; x < selLength; x++){
	                a = newOpt.text.toLowerCase();
	                b = theSel.options[x].text.toLowerCase();
	                if (a < b){
	                        var tmpOpt = new Option();
	                        tmpOpt.text = theSel.options[x].text;
	                        tmpOpt.value = theSel.options[x].value;
	                        theSel.options[x] = newOpt;
	                        newOpt = tmpOpt;
	                }
	          }
		  }
          theSel.options[selLength] = newOpt; 
        } 

        function deleteOption(theSel, theIndex) 
        { 
          var selLength = theSel.length; 
          if(selLength>0) 
          { 
            theSel.options[theIndex] = null; 
          } 
        } 

        var matched = null; 
        var AllArtists = null; 
        
        function moveOptions(theSelFrom, theSelTo) 
        { 
          
          var selLength = theSelFrom.length; 
          var selectedText = new Array(); 
          var selectedIndices = new Array(); 
          var selectedValues = new Array(); 
          var selectedCount = 0; 
          
          while (theSelFrom.selectedIndex != -1) 
          { 
              selectedValues.push(theSelFrom.options[theSelFrom.selectedIndex].value); 
              selectedIndices.push(theSelFrom.selectedIndex); 
              selectedText.push(theSelFrom.options[theSelFrom.selectedIndex].text); 
              if (theSelFrom == document.Update_Form.AllArtists){ 
                    deleteFromAllArtists(theSelFrom.options[theSelFrom.selectedIndex].value); 
              } 
              deleteOption(theSelFrom,theSelFrom.selectedIndex); 
          } 
          
          // Add the selected text/values in reverse order. 
          // This will add the Options to the 'to' Select 
          // in the same order as they were in the 'from' Select. 
          for ( var i=0, len=selectedValues.length; i<len; ++i ) 
          { 
              if (theSelTo == document.Update_Form.AllArtists){ 
                    addToAllArtists(selectedValues[i],selectedText[i]); 
              } 
            addOption(theSelTo, selectedText[i], selectedValues[i]); 
          } 
          
          if(NS4) history.go(0); 
        } 
        
        function getOptions(theSel){ 
                var ret = new Array(); 
                for (var i=0, len=theSel.length; i<len; ++i){ 
                        ret[i] = new Array();
                        ret[i][\"value\"] = theSel.options[i].value; 
                        ret[i][\"text\"]  = theSel.options[i].text; 
                } 
                return ret; 
        } 
        
        function update() 
        {
           matched = document.Update_Form.nameFilter.value; 
           var re = new RegExp('^.*'+ matched +'.*$', 'mgi'); 
           for (var i=0, len=AllArtists.length; i<len; ++i){ 
                   if (AllArtists[i][\"text\"].match(re) != null){ 
                        addOption(document.Update_Form.AllArtists,AllArtists[i][\"text\"],AllArtists[i][\"value\"],false); 
                   } 
           } 
        } 

        function refresh() 
        { 
          if (AllArtists == null){ 
               AllArtists = getOptions(document.Update_Form.AllArtists);   
          } 
          var s = document.Update_Form.nameFilter.value; 
          if( s != matched) 
          { 
            document.Update_Form.AllArtists.options.length = 0; 
            update(); 
          } 
        } 
        
        function deleteFromAllArtists(value){ 
           for (var i in AllArtists){
                   if (AllArtists[i][\"value\"] == value){ 
                        AllArtists.splice(i,1); 
                   } 
           }
        } 
        
        function addToAllArtists(value,text){ 
           var len = AllArtists.length; 
           AllArtists[len] = new Array();
           AllArtists[len][\"text\"] = text; 
           AllArtists[len][\"value\"] = value; 
        } 

        //--> 
        </script> 
        ";                        
    $admin_head_extras.=$ArtistListBoxScript;
        $ArtistEdittingScript = "
        <script language='JavaScript' type='text/javascript'> 
        <!-- 
                function editSelectedArtist(theSel){
                        openEditArtistWindow(theSel.name,theSel.options[theSel.selectedIndex].value);
                }
                function quickAddArtist(){
                        openEditArtistWindow(document.Update_Form.ShowArtists.name,'');
                }
                function openEditArtistWindow(selectBoxName,artistID){
                        var url = '".$update_artist."&sel='+selectBoxName;
                        url += '&".YEAR_PARM."=".urlencode($Festival->getParameter('FestivalYear'))."';
                        if (artistID != ''){
                                url += '&id='+artistID;
                        }
                    var eWindow = window.open(url, 'EditArtist',
                     'scrollbars=yes,menubar=yes,resizable=yes,toolbar=no,width=600,height=600');
                }
                function updateArtist(theSel,theText,theValue){
                        var theOtherSel;
                        var i;
                        
                        if (theSel.name == 'AllArtists'){
                                theOtherSel = document.Update_Form.ShowArtists;
                        }
                        else{
                                theOtherSel = document.Update_Form.AllArtists;
                        }
                        
                        // First, update it in the AllArtists array
                        for (i = 0, len=AllArtists.length;i<len;i++){
                                if (AllArtists[i]['value'] == theValue){
                                    AllArtists[i]['text'] = theText;
                                }
                        }
                        // Check and see if the value is still in the 'theSel' box
                        for (i = 0, len=theSel.length;i<len;i++){
                                if (theSel.options[i].value == theValue){
                                    theSel.options[i].text = theText;
                                    theSel.options[i].selected = true;
                                    return;
                                }
                        }
                        
                        // Check and see if the value is still in the 'theOtherSel' box
                        for (i = 0, len=theOtherSel.length;i<len;i++){
                                if (theOtherSel.options[i].value == theValue){
                                    theOtherSel.options[i].text = theText;
                                    theOtherSel.options[i].selected = true;
                                    return;
                                }
                        }
                }
                function setSelectedArtistAsHost(theSel){
                    if (theSel.selectedIndex >= 0){
                        document.Update_Form.HostArtistID.value = theSel.options[theSel.selectedIndex].value;
                        saveShowArtists();
                        document.Update_Form.submit();
                    }
                }
                function saveShowArtists(){
                        var str = '';
                        var sep = '';
                        for (var i=0, len=document.Update_Form.ShowArtists.length;i<len;i++){
                                str+= sep+document.Update_Form.ShowArtists.options[i].value;
                                sep = '&xi;';
                        }
                        document.Update_Form.strShowArtists.value = str;
                }

		        function moveOptionsUp(theSel){
		          var tmpOpt;
		          var tmpOpt2;
		          for (var i=1, len=theSel.length; i<len; ++i){ 
		                if (theSel.options[i].selected){
		                        tmpOpt = new Option();
		                        tmpOpt.text = theSel.options[i - 1].text;
		                        tmpOpt.value = theSel.options[i - 1].value;
		                        tmpOpt2 = new Option();
		                        tmpOpt2.text = theSel.options[i].text;
		                        tmpOpt2.value = theSel.options[i].value;
		                        theSel.options[i - 1] = tmpOpt2;
		                        theSel.options[i - 1].selected = true;
		                        theSel.options[i] = tmpOpt;
		                }
		          }                
		        }

				function alphabetizeArtists(theSel){
					var mylist = jQuery('#'+theSel);
					var listitems = mylist.children('option').get();
					listitems.sort(function(a, b) {
					   var compA = jQuery(a).html().toUpperCase();
					   var compB = jQuery(b).html().toUpperCase();
					   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
					})
					jQuery.each(listitems, function(idx, itm) { mylist.append(itm); });	
				}

				function alphabetizeArtistsLastName(){
					saveShowArtists();
					jQuery('#action').val('alpha_last');
					jQuery('#Update_Form').submit();
				}

		        function moveOptionsDown(theSel){
		          var tmpOpt;
		          var tmpOpt2;
		          for (var i=theSel.length - 2; i>=0; i--){ 
		                if (theSel.options[i].selected){
		                        tmpOpt = new Option();
		                        tmpOpt.text = theSel.options[i + 1].text;
		                        tmpOpt.value = theSel.options[i + 1].value;
		                        tmpOpt2 = new Option();
		                        tmpOpt2.text = theSel.options[i].text;
		                        tmpOpt2.value = theSel.options[i].value;
		                        theSel.options[i + 1] = tmpOpt2;
		                        theSel.options[i + 1].selected = true;
		                        theSel.options[i] = tmpOpt;
		                }
		          }                
		        }
        //--> 
        </script> 
        ";
    $admin_head_extras.=$ArtistEdittingScript;


    $ArtistsTab = new HTML_Tab('Artists',pluralize('Artist'));
        
        // Get All Artists playing the festival
        $FestivalArtists = $FestivalArtistContainer->getAllArtists($year);        
        if (is_a($FestivalArtists,'FestivalArtist')){
            $FestivalArtists = array($FestivalArtists->getParameter('ArtistID') => $FestivalArtists);
        }
        if (!is_array($FestivalArtists)){
                $FestivalArtists = array();
        }
        
        $ArtistSelection = "<table width='100%' align=center><tr>\n";
        $ArtistSelection.= "<td width=44% valign=top>".pluralize('Artist')." at the $year ".vocabulary('Festival')."<br><select name='AllArtists' multiple='multiple' size=10 style='width: 100%'>\n";
        foreach ($FestivalArtists as $Artist){
                if (!in_array($Artist->getParameter('ArtistID'),$ShowArtistIDs)){
                        $ArtistSelection.= "<option value='".$Artist->getParameter('ArtistID')."'>".$Artist->getHTMLFormattedParameter('ArtistFullName')."</option>\n";
                }
        }
        $ArtistSelection.= "</select>\n";
        $ArtistSelection.= "<br>Filter: <input type='text' name='nameFilter'/>\n";
        $ArtistSelection.= "<input type='button' name='Edit1' value='Edit Selected' onClick='editSelectedArtist(document.Update_Form.AllArtists);'/>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "<td width=6% valign=middle align=center>\n";
        $ArtistSelection.= "<input type='button' name='AddArtist' value='--&gt;' onclick='moveOptions(document.Update_Form.AllArtists,document.Update_Form.ShowArtists);'><br><br>";
        $ArtistSelection.= "<input type='button' name='RemoveArtist' value='&lt;--' onclick='moveOptions(document.Update_Form.ShowArtists,document.Update_Form.AllArtists);'>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "<td width=44% valign=top>".pluralize('Artist')." in the $ShowType<br><select name='ShowArtists' id='ShowArtists' multiple='multiple' size=10 style='width: 100%'>\n";
        if (is_array($Show->getParameter('ShowArtists'))){
            foreach ($Show->getParameter('ShowArtists') as $Artist){
                if ($Show->getParameter('ShowHostArtistID') == $Artist->getParameter('ArtistID')){
                    $Host = HOST_STRING; 
                    $HostArtistID = $Artist->getParameter('ArtistID');
                }
                else{
                    $Host = "";
                }
                $ArtistSelection.= "<option value='".$Artist->getParameter('ArtistID')."'>".$Artist->getHTMLFormattedParameter('ArtistFullName')."$Host</option>\n";
            }
        }
        $ArtistSelection.= "</select>\n";
        $ArtistSelection.= "<br><input type='button' name='Edit2' value='Edit Selected' onClick='editSelectedArtist(document.Update_Form.ShowArtists);'/>\n";
        $ArtistSelection.= "<input type='button' name='Hostify' value='Set Selected As Host' onClick='setSelectedArtistAsHost(document.Update_Form.ShowArtists);'/>\n";
        $ArtistSelection.= "</td>\n";

        $ArtistSelection.= "<td width=6% valign=middle align=center>\n";
        $ArtistSelection.= "<input type='button' name='ArtistUp' value='&Lambda;' onclick='moveOptionsUp(document.Update_Form.ShowArtists);'><br><br>";
        $ArtistSelection.= "<input type='button' name='ArtistDown' value='V' onclick='moveOptionsDown(document.Update_Form.ShowArtists);'><br><br>";
        $ArtistSelection.= "<input type='button' name='ArtistAlpha' value='A-Z' onclick='alphabetizeArtists(\"ShowArtists\");'>\n";
        $ArtistSelection.= "<input type='button' name='ArtistAlphaLastName' value='A-Z (last)' onclick='alphabetizeArtistsLastName();'>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "</tr></table>\n";
        
        
    $ArtistsTab->addPlainText('Artists:',$ArtistSelection);
    
	if ($ShowContainer->SimpleShowContainer->colname['ShowEmbeddedScheduleUID'] != ''){
		// This version has the ability to embed a schedule into a show.  Let's proceed.
		$ArtistsTab->addPlainText('&nbsp;','Instead of including '.pluralize('Artist').' above, you can also specify a Schedule to associate with this '.$ShowType.'.  To do that, first, create the schedule, and then come back here and choose that schedule, then the stage from the drop down boxes.');
		$Schedules = $ScheduleContainer->getAllSchedules($Show->getParameter('ShowYear'));
		unset($Schedules[$Show->getParameter('ShowScheduleUID')]);
		$_Schedules = array('' => '&lt;Select a Schedule&gt;');
		$ScheduleStages = array();
		foreach ($Schedules as $sched_id => $Schedule){
			$_Schedules[$sched_id] = $Schedule->getParameter('ScheduleName');
			$ScheduleStages[$sched_id] = array();
			foreach ($Schedule->getParameter('ScheduleStages') as $stage_id => $Stage){
				$ScheduleStages[$sched_id][$stage_id] = $Stage['Name'];
			}
		}

        $ArtistsTab->addSelect('ShowEmbeddedScheduleUID',"Schedule:",$_Schedules,$Show->getParameter('ShowEmbeddedScheduleUID'),1,'',false, "id='ShowEmbeddedScheduleUID' onchange='javascript:updateScheduleStages();'");
        $StageSelectCell = "";
        $StageSelectCell.= "<span id='ScheduleStageDropdownList' style='display:none'>".HTML_Form::returnSelect('ShowEmbeddedStageID',array('' => '&lt;Select a '.vocabulary('Stage').'&gt;'),$Show->getParameter('ShowEmbeddedStageID'),1,'',false, "id='ShowEmbeddedStageID'")."</span>";
        $StageSelectCell.= "<span id='ScheduleStageMessage' style='display:none'>Select the schedule first</span>\n";
        $ArtistsTab->addPlainText(vocabulary('Stage').':',$StageSelectCell);

        $ScheduleStagesScript = "
        <script language='JavaScript' type='text/javascript'> 
        <!-- 
                var ScheduleStages  = new Array();
        ";
        foreach ($ScheduleStages as $sched_id => $Stages){
			$ScheduleStagesScript.= "ScheduleStages[$sched_id] = new Array();\n";
			foreach ($Stages as $stage_id => $Stage){
				$ScheduleStagesScript.= "ScheduleStages[$sched_id][$stage_id] = '".addslashes($Stage)."';\n";
			}
        }   
        $ScheduleStagesScript.= "
	        function updateScheduleStages(){
	                var ScheduleControl = document.getElementById('ShowEmbeddedScheduleUID');
	                var sched = ScheduleControl.options[ScheduleControl.options.selectedIndex].value;
	                var StageControl = document.getElementById('ShowEmbeddedStageID');
	                var stage = StageControl.options[StageControl.options.selectedIndex].value;
	                if (sched == ''){
	                        document.getElementById('ScheduleStageDropdownList').style.display = 'none';
	                        document.getElementById('ScheduleStageMessage').style.display = 'inline';
	                }
	                else{
	                        document.getElementById('ScheduleStageDropdownList').style.display = 'inline';
	                        document.getElementById('ScheduleStageMessage').style.display = 'none';
	                        switchSelect('ShowEmbeddedStageID',ScheduleStages[sched],stage);
	                }
	        }

            function setScheduleInitialValues(){
                    setInitialValue('ShowEmbeddedScheduleUID','".$Show->getParameter('ShowEmbeddedScheduleUID')."');
                    updateScheduleStages();
                    setInitialValue('ShowEmbeddedStageID','".$Show->getParameter('ShowEmbeddedStageID')."');
            }
            
        ";
        $ScheduleStagesScript.= "
		-->
		</script>";
    	$admin_head_extras.=$ScheduleStagesScript;
        

	}
    
    // We've defined the tabs.  Now let's add them
    $form->addTab($ShowTab);
    $form->addTab($ArtistsTab);

    /***********************************************************************
    *
    *   Message Tab
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
    $form->addSubmit('save','Save Changes','onClick="saveShowArtists()";');
    if ($id != ""){
        $form->addSubmit('delete','Delete '.vocabulary('Show'));
    }
    $form->addSubmit('cancel','Cancel');
    
    // Some hidden fields to help us out 
    $form->addHidden('form_submitted','true');
    $form->addHidden('strShowArtists','nothing');
	$form->addHidden('action','');
    $form->addHidden('HostArtistID',$HostArtistID);
    $form->addHidden(YEAR_PARM,htmlentities($year));
    $form->addHidden('type',htmlentities($schedule_uid));
    $form->addHidden('id',$id);
    $form->addHidden('day',$Show->getParameter('ShowDay'));
	$start_functions = array('jQuery("textarea#ShowDescription").markItUp(myMarkdownSettings);');    



    // Finally, we set the Smarty variables as needed.
    $smarty->assign('includes_tabbed_form',true);
    $smarty->assign('admin_start_function',$start_functions);
    $smarty->assign('form',$form);
    $smarty->assign('form_attr','width=90% align=center');
    $smarty->assign('Tabs',$form->getTabs());
    $smarty->assign('admin_head_extras',$admin_head_extras);
    
?>
<?php   $smarty->display('admin_form.tpl'); ?>
<script language='JavaScript1.2'> 
function trigger() 
{ 
  refresh(); 
  setTimeout('trigger()', 250); 
} 

setTimeout('trigger()', 250); 
setInitialValues();
<?php if ($ShowContainer->SimpleShowContainer->colname['ShowEmbeddedScheduleUID'] != "") : ?>
setScheduleInitialValues();
<?php endif; ?>
</script> 
