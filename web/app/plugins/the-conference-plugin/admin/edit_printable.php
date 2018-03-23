<?php
	if (!$Bootstrap){
	    die ("You cannot access this file directly");
	}

	define (HTML_FORM_TH_ATTR,"valign=top align=right width='15%'");
	define (HTML_FORM_TD_ATTR,"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');
    
    global $Year,$ShowContainer,$StageNamesLegend;

    $Package = $Bootstrap->usePackage('FestivalApp');
    
    $UseDatabases = $Package->useDatabases;

    $AjaxURL = CMS_INSTALL_URL.'lib/packages/Common/ajax.php?package=FestivalApp&'.session_name().'='.htmlspecialchars(session_id());
    
    foreach ($UseDatabases as $UseDatabase){
        // This will include the appropriate helper file and instantiate the helper
        // These are called by name later in this script
		// TODO = make this work....
		if ($Bootstrap->packageExists($UseDatabase['package'])){			
			$p = $Bootstrap->usePackage($UseDatabase['package']);
	        include_once(PACKAGE_DIRECTORY.$p->package_directory."/".$UseDatabase['helper'].".php");
	        $$UseDatabase['helper'] = new $UseDatabase['helper']();
		}
    }
	
	$edit = $Bootstrap->makeAdminURL($Package,'edit_printable'); //$Package->admin_pages['edit_printable']['url'];
	$printables = $Bootstrap->makeAdminURL($Package,'printables');

    if ($_GET[YEAR_PARM] != ""){
        $Year = $_GET[YEAR_PARM];
    }
    else{
        $Year = date("Y");
    }
    
	$LetterID = ($_GET['letter'] != "" ? $_GET['letter'] : '');
    $SelfURL = $edit."&amp;".YEAR_PARM."=$Year&amp;letter=".$LetterID;
    
	$Bootstrap->addURLToAdminBreadcrumb($Bootstrap->makeAdminURL($Package,'printables')."&".YEAR_PARM."=$Year",$Package->admin_pages['printables']['title']);
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'edit_printable');
		
    
	$LetterContainer = new LetterContainer();
	$ShowContainer = new ShowContainer();
	$FestivalContainer = new FestivalContainer();
    $FestivalArtistContainer = new FestivalArtistContainer();

    $LabelDetailsParms = array('LetterTopMargin' => '0.5"','LetterLabelHeight' => '3"','LetterSideMargin' => '0.25"','LetterLabelWidth' => '4"','LetterVerticalPitch' => '3"','LetterNumberAcross' => '2','LetterHorizontalPitch' => '4"','LetterNumberDown' => '3');
    
	
	$Letter = $LetterContainer->getLetter($LetterID);
	if (!$Letter){
	    $Letter = new Letter();
	}
	if (!$Letter->getParameter('LetterDataYear')){
	    // Default the year to the year of the first festival
    	$Festivals = $FestivalContainer->getAllFestivals();
    	if (!is_array($Festivals)){
	        $Year = date("Y");
	    }
	    else{
	        $FirstFestival = array_shift($Festivals);
	        $Year = $FirstFestival->getParameter('FestivalYear');
	    }
	}
	else{
	    $Year = $Letter->getParameter('LetterDataYear');
	}

    if ($Letter->getParameter('LetterLastPrintedSet') == "" or unserialize($Letter->getParameter('LetterLastPrintedSet')) === false){
        $Letter->setParameter('LetterLastPrintedSet',serialize(array()));
    }
    if ($Letter->getParameter('LetterAlreadyPrinted') == "" or unserialize($Letter->getParameter('LetterAlreadyPrinted')) === false){
        $Letter->setParameter('LetterAlreadyPrinted',serialize(array()));
    }
    
	
	$DisplayingForm = ($_POST['print'] == "" and $_GET['id'] == "" and $_GET['collapse'] == "" and $_GET['pdf'] == "");
	
    if (!function_exists("htmlspecialchars_decode")) {
        function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT) {
            return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
        }
    }

    if ($_POST['form_submitted'] != ""){
        if ($_POST['cancel'] != ''){
            header("Location:".$printables);
            exit();
        }
        if (isset($_POST['active_tab'])){
                $DefaultTab = substr($_POST['active_tab'],6);
        }
        if ($_POST['LetterName'] == ""){
            $MessageList->addMessage("The Printable Name cannot be left blank",MESSAGE_TYPE_ERROR);
        }
        if ($_POST['strShowArtists'] != ""){
            // I use this function (only native in PHP > 5.1) in this script
            $ShowArtistIDs = explode('&xi;',htmlspecialchars_decode(stripslashes($_POST['strShowArtists']),ENT_QUOTES));
        }
        else{
            $ShowArtistIDs = array();
        }
        if (!count($ShowArtistIDs) and $_POST['print'] != "" and $_POST['LetterPrintChoice'] == 'SelectedChoices'){
            $MessageList->addMessage("You must select at least one artist/person to print.",MESSAGE_TYPE_ERROR);
            $DisplayingForm = true;
        }
        
        $Letter->setParameter('LetterContent',$_POST['WYSIWYG_Editor']);
	    $Letter->setParameter('LetterContent',preg_replace("/(\n\r){2,}/m","\n\r",$Letter->getParameter('LetterContent')));
        $Letter->setParameter('LetterName',$_POST['LetterName']);
        $Letter->setParameter('LetterPageBreak',($_POST['LetterPageBreak'] ? 1 : 0));
        $Letter->setParameter('LetterType',$_POST['LetterType']);
        
        $LetterLabelDetails = array();
        foreach ($LabelDetailsParms as $LabelDetailsParm => $Default){
            $test = trim(htmlspecialchars_decode(stripslashes($_POST[$LabelDetailsParm])));
            if (in_array($LabelDetailsParm,array('LetterNumberAcross','LetterNumberDown'))){
                if (is_numeric($test)){
                    $LetterLabelDetails[$LabelDetailsParm] = $test;
                }
                else{
                    $LetterLabelDetails[$LabelDetailsParm] = $Default;
                }
            }
            else{
                if (preg_match('/\"$/',$test)){
                    $test = substr($test,0,strlen($test) - 1);
                }
                if (is_numeric($test)){
                    $LetterLabelDetails[$LabelDetailsParm] = $test.'"';
                }
                else{
                    $LetterLabelDetails[$LabelDetailsParm] = $Default;
                }
            }
        }
        $LetterLabelDetails['LetterCollateForReverse'] = ($_POST['LetterCollateForReverse'] ? 1 : 0);
        $Letter->setParameter('LetterLabelDetails',serialize($LetterLabelDetails));        
        
        $Letter->setParameter('LetterDataYear',$_POST['LetterDataYear']);
        $Letter->setParameter('LetterDataSource',$_POST['LetterDataSource']);
        $Letter->setParameter('LetterIgnoreAlreadyPrinted',($_POST['LetterIgnoreAlreadyPrinted'] ? 1 : 0));
        
        $Letter->setParameter('LetterPrintChoice',$_POST['LetterPrintChoice']);
        
        $Year = $Letter->getParameter('LetterDataYear');
        
        $LetterLastPrintedSet = unserialize($Letter->getParameter('LetterLastPrintedSet'));
        $LetterLastPrintedSet[$Year] = array();
        $LetterLastPrintedSet[$Year][$Letter->getParameter('LetterDataSource')] = $ShowArtistIDs;
        $Letter->setParameter('LetterLastPrintedSet',serialize($LetterLastPrintedSet));
            
        if (!$MessageList->hasMessages()){
            if ($Letter->getParameter('LetterID') == ""){
                $LetterContainer->addLetter($Letter);
			    $SelfURL = $edit."&amp;".YEAR_PARM."=$Year&amp;letter=".$Letter0>getParameter('LetterID');
            }
            else{
                $LetterContainer->updateLetter($Letter);
            }
        }
    }

    
    if ($DisplayingForm){
        $form = new HTML_TabbedForm($SelfURL,'post','Update_Form');
        
    	/***********************************************************************
    	*
    	*	Letter Tab
    	*
    	***********************************************************************/
	    $LetterTab = new HTML_Tab('LetterTab','Printable Info');
        $LetterTab->addText('LetterName',"Printable Name",$Letter->getParameter('LetterName'));
        
        // Letter Type
        $LetterTypes = array('letter' => 'Simple Letter/Report', 'label' => 'Label (multiple per page)');
        $LetterTab->addSelect('LetterType','Printable Type',$LetterTypes,$Letter->getParameter('LetterType'),1,'',false,"onChange='updateLetterVariables()'");
        
        //xxx
        $LetterDataSources = array();
        $LetterDataSources['artists']     = 'Artists at the Festival - by Name';
        if (array_key_exists('People',$UseDatabases)){
            $LetterDataSources['types']       = 'VIP\'s - by Festival Pass Type';
            $LetterDataSources['people']      = 'VIP\'s - by Last Name';
        }
        if (array_key_exists('Volunteers',$UseDatabases)){
            $LetterDataSources['assigned']    = 'Volunteers - by Assigned Area';
            $LetterDataSources['volunteers']  = 'Volunteers - by Name';
        }
        if (array_key_exists('Artisans',$UseDatabases)){
            $LetterDataSources['artisans']    = 'Artisans - by Name';
        }

        
        // Letter Variable Display code
        $LetterVariableDisplayCode = "
        <script type='text/javascript'>
        <!--
            function updateLetterVariables(){
                updateLetterVariablesAjax(); 

                updateDataSourceChoices();
                
                updatePrintableDetails();
            }
            
            function updateLetterVariablesAjax(){
                var source = document.Update_Form.LetterDataSource.options[document.Update_Form.LetterDataSource.selectedIndex].value;
                var type = document.Update_Form.LetterType.options[document.Update_Form.LetterType.selectedIndex].value;
                var e = document.getElementById('letter_variables');
                
                e.innerHTML = 'Retrieving available variable names';
                
                new Ajax.Request('$AjaxURL',
                  {
                    method:'get',
                    parameters: {cmd: 'ajax', req: 'variables', source: source, type: type},
                    onSuccess: function(transport, json){
                        if (document.Update_Form.LetterDataSource.value == source){
                            e.innerHTML = '';
                            for (var i in json){
                                e.innerHTML = e.innerHTML + '<nobr><a href=\\'#\\' onclick=\"insertVariableCode(\\'' + json[i] + '\\');\">' + json[i] + '</a></nobr><br>\\n';
                            }
                        }
                    },
                    onFailure: function(){ alert('Unable to retrieve available variables...') }
                  });
            }
            
            function updatePrintableDetails(){
                var type = document.Update_Form.LetterType.options[document.Update_Form.LetterType.selectedIndex].value;
                if (type == 'letter'){
                    document.getElementById('LetterDetails').style.display = 'block';
                    document.getElementById('LabelDetails').style.display = 'none';
                    document.getElementById('LabelPrintOptions').style.display = 'none';
                }
                else{
                    document.getElementById('LetterDetails').style.display = 'none';
                    document.getElementById('LabelDetails').style.display = 'block';
                    document.getElementById('LabelPrintOptions').style.display = 'block';
                }
            }
        -->
        </script>
        ";
        $admin_head_extras.= $LetterVariableDisplayCode;
        
        $TemplateLabel = "Template";
        $TemplateLabel.= "<p style='font-weight:normal;font-size:9pt;line-height:1.4em;'><strong>Available Variables</strong>";
        
        // Letter Variables
        // Step 1. Determine what to display initially
        switch ($Letter->getParameter('LetterType')){
        case "":
        case "letter":
            $LetterType = "letter";
            break;
        default:
            $LetterType = "pass";
            break;
        }
        //xxx
        switch ($Letter->getParameter('LetterDataSource')){
        case "":
            $LetterDataSource = "artists";
            break;
        default:
            $LetterDataSource = $Letter->getParameter('LetterDataSource');
            break;
        }
        
        // We'll fill in the letter_variables using ajax
        $TemplateLabel.= "<span id='letter_variables' style='float:right'></span>";
        $EditorCell = HTML_Form::returnTextArea('WYSIWYG_Editor',$Letter->getParameter('LetterContent'),70,12,0);
        
	    $LetterDetails = "<div id='LetterDetails'>\n";
	    $LetterDetails.= HTML_Form::returnCheckBox('LetterPageBreak',($Letter->getParameter('LetterID') == "" ? true : ($Letter->getParameter('LetterPageBreak') ? true : false)));
	    $LetterDetails.= " Page break after each person";
	    $LetterDetails.= "</div>\n";
	    $LetterLabelDetails = unserialize($Letter->getParameter('LetterLabelDetails'));
        if (!is_array($LetterLabelDetails)){
            $LetterLabelDetails = array();
            foreach ($LabelDetailsParms as $LabelDetailsParm => $Default){
                $LetterLabelDetails[$LabelDetailsParm] = $Default;                
            }
        }
	    $LabelDetails = "<div id='LabelDetails'>\n";
	    $LabelDetails.= "<table cellpadding='5' border='0'>\n";
	    $LabelDetails.= "<tr>\n";
	    $LabelDetails.= "<td>Top Margin:</td><td>".HTML_Form::returnText('LetterTopMargin',htmlspecialchars($LetterLabelDetails['LetterTopMargin']))."</td>";
	    $LabelDetails.= "<td>Side Margin:</td><td>".HTML_Form::returnText('LetterSideMargin',htmlspecialchars($LetterLabelDetails['LetterSideMargin']))."</td>";
	    $LabelDetails.= "<td rowspan='4'><img src='".$Package->getPackageURL()."admin/images/labels.gif'></td>";
	    $LabelDetails.= "</tr>\n";
	    $LabelDetails.= "<tr>\n";
	    $LabelDetails.= "<td>Label Height:</td><td>".HTML_Form::returnText('LetterLabelHeight',htmlspecialchars($LetterLabelDetails['LetterLabelHeight']))."</td>";
	    $LabelDetails.= "<td>Label Width:</td><td>".HTML_Form::returnText('LetterLabelWidth',htmlspecialchars($LetterLabelDetails['LetterLabelWidth']))."</td>";
	    $LabelDetails.= "</tr>\n";
	    $LabelDetails.= "<tr>\n";
	    $LabelDetails.= "<td>Vertical Pitch:</td><td>".HTML_Form::returnText('LetterVerticalPitch',htmlspecialchars($LetterLabelDetails['LetterVerticalPitch']))."</td>";
	    $LabelDetails.= "<td>Horizontal Pitch:</td><td>".HTML_Form::returnText('LetterHorizontalPitch',htmlspecialchars($LetterLabelDetails['LetterHorizontalPitch']))."</td>";
	    $LabelDetails.= "</tr>\n";
	    $LabelDetails.= "<tr>\n";
	    $LabelDetails.= "<td>Number Across:</td><td>".HTML_Form::returnText('LetterNumberAcross',htmlspecialchars($LetterLabelDetails['LetterNumberAcross']))."</td>";
	    $LabelDetails.= "<td>Number Down:</td><td>".HTML_Form::returnText('LetterNumberDown',htmlspecialchars($LetterLabelDetails['LetterNumberDown']))."</td>";
	    $LabelDetails.= "</tr>\n";
	    $LabelDetails.= "</table>\n";
	    $LabelDetails.= HTML_Form::returnCheckBox('LetterCollateForReverse',($LetterLabelDetails['LetterCollateForReverse'] ? true : false));
	    $LabelDetails.= " Collate for Reverse-Side Printing";
	    $LabelDetails.= "</div>\n";

        $EditorCell.= $LetterDetails.$LabelDetails;
	    $LetterTab->addPlainText($TemplateLabel,$EditorCell); //TextArea('WYSIWYG_Editor',$TemplateLabel,$Letter->getParameter('LetterContent'),70,12,0);
	    	    

	    //$LetterTab->addPlainText('&nbsp',$LetterDetails.$LabelDetails);
	    if ($DefaultTab == ""){
	        $DefaultTab = 'LetterTab';
	    }
	    
	    $form->addTab($LetterTab);

    	/***********************************************************************
    	*
    	*	Print Set Tab
    	* 
    	*   Allows the user to choose which artists to print in this run
    	*
    	***********************************************************************/
        // Get All Artists playing the festival
        // xxx
		try{
	        switch ($LetterDataSource){
	        case "artists":
	            //$FestivalArtists = $FestivalArtistContainer->getLineup($Year,'ArtistFullName');        
	            $AvailableChoices = $FestivalArtistContainer->getLineup($Year,'ArtistFullName');
	            break;
	        case "types":
				if (!is_a($PeoplePassesHelper,'PeoplePassesHelper')){
					var_dump(class_exists('PeoplePassesHelper'));
					throw new Exception('PeoplePassesHelper is not instantiated');
				}
	            $tmp = $PeoplePassesHelper->getPassTypes();
	            $AvailableChoices = array();
	            if (is_array($tmp)){
	                foreach ($tmp as $key => $value){
	                    $AvailableChoices[$value] = $value;
	                }
	            }
	            break;
	        case "people":
				if (!is_a($PeoplePassesHelper,'PeoplePassesHelper')){
					throw new Exception('PeoplePassesHelper is not instantiated');
				}
	            $AvailableChoices = $PeoplePassesHelper->getPeopleWithPasses();
	            break;
	        case "volunteers":
				if (!is_a($VolunteerCategoryHelper,'VolunteerCategoryHelper')){
					throw new Exception('VolunteerCategoryHelper is not instantiated');
				}
	            $AvailableChoices = $VolunteerCategoryHelper->getAssignedVolunteers();
	            break;
	        case "assigned":
				if (!is_a($VolunteerCategoryHelper,'VolunteerCategoryHelper')){
					throw new Exception('VolunteerCategoryHelper is not instantiated');
				}
	            $tmp = $VolunteerCategoryHelper->getCategories();
	            $AvailableChoices = array();
	            if (is_array($tmp)){
	                foreach ($tmp as $key => $value){
	                    $AvailableChoices[$value] = $value;
	                }
	            }
	            break;
	        case "artisans":
				if (!is_a($ArtisanContainer,'ArtisanContainer')){
					throw new Exception('ArtisanContainer is not instantiated');
				}
	            $tmpAvailableChoices = $ArtisanContainer->getAllArtisans($Year);
	            $AvailableChoices = array();
	            foreach ($tmpAvailableChoices as $tmpArtisan){
	                $AvailableChoices[$tmpArtisan->getParameter('frmArtisanID')] = $tmpArtisan;
	            }
	            break;
	        }
		}
		catch(Exception $e){
			$e->getMessage();
		}
        
        if (!is_array($AvailableChoices)){
            $AvailableChoices = array();
        }
        
        $ArtistListBoxScript = "
	    <script type='text/javascript' src='".CMS_INSTALL_URL."lib/packages/Common/prototype.js'></script>
        <script language='JavaScript' type='text/javascript'> 
        <!-- 
        var NS4 = (navigator.appName == 'Netscape' && parseInt(navigator.appVersion) < 5); 

        function addOption(theSel, theText, theValue) 
        { 
          var newOpt = new Option(theText, theValue); 
          var selLength = theSel.length; 
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
        var AllAvailableArtists = null;
        var AllAvailableTypes = null;
        var CurrentDataSource = '$LetterDataSource';
        var CurrentDataYear = '".str_replace("'","\\'",$Year)."';
        
        function moveOptions(theSelFrom, theSelTo) 
        { 
          
          if (AllArtists == null){ 
               AllArtists = getOptions(document.Update_Form.AllArtists);   
          } 

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

        function saveShowArtists(){
                var str = '';
                var sep = '';
                for (var i=0, len=document.Update_Form.ShowArtists.length;i<len;i++){
                        str+= sep+document.Update_Form.ShowArtists.options[i].value;
                        sep = '&xi;';
                }
                document.Update_Form.strShowArtists.value = str;
        }
        
        function addAllArtists(){
            var selFrom = document.Update_Form.AllArtists;
            var selTo = document.Update_Form.ShowArtists;
            
            while (selFrom.length > 0){
                selFrom.selectedIndex = 0;
                moveOptions(selFrom,selTo);
            }
            
        }
        
        function clearArtists(){
            var selTo = document.Update_Form.AllArtists;
            var selFrom = document.Update_Form.ShowArtists;
            
            while (selFrom.length > 0){
                selFrom.selectedIndex = 0;
                moveOptions(selFrom,selTo);
            }
            
        }
        
        
        function updateDataSourceChoices(){
            // We're going to use AJAX to call the server and get all of the 
            // values that we want to put into the Choices select box
            var newDataYear = document.Update_Form.LetterDataYear.options[document.Update_Form.LetterDataYear.selectedIndex].value;
            var newDataSource = document.Update_Form.LetterDataSource.options[document.Update_Form.LetterDataSource.selectedIndex].value;
            var key;
            var ChoicesAvailable = false;
            
            if (newDataSource != CurrentDataSource || ((CurrentDataSource == 'artists' || CurrentDataSource == 'artisans') && newDataYear != CurrentDataYear)){
                // Step 0. Hide/Show the Edit Selected Button
                if (newDataSource == 'types' || newDataSource == 'assigned'){
                    document.getElementById('EditSelected').style.display = 'none';
                }
                else{
                    document.getElementById('EditSelected').style.display = 'inline';
                }
                
                // Step 0.5 Disable the NameFilter Box
                document.getElementById('nameFilter').value = '';
                document.getElementById('nameFilterSpan').style.display = 'none';
                
                // Step 1.  Clear the Chosen list
                clearArtists();
                
                // Step 2.  Remove all options from the Available List
                while (document.Update_Form.AllArtists.length > 0){
                    document.Update_Form.AllArtists.remove(0);
                }
                
                // Step 2a.  Give the user a message
                document.getElementById('ArtistSelectionMessage').innerHTML = 'Please wait while we retrieve that information...';
                
                // Step3.  Make an AJAX call to get the new list
                //         Replace the Available list with the right type
                performAjaxRequest(newDataYear,newDataSource,0);


            }
            
            CurrentDataSource = newDataSource;
            CurrentDataYear = newDataYear;
        }
        
        function performAjaxRequest(year,source,start){
            var ChoicesAvailable;
            var limit = 250;
            
            new Ajax.Request('$AjaxURL',
              {
                method:'get',
                parameters: {cmd: 'ajax', req: 'names', ".YEAR_PARM.": year, source: source, limit: limit, start: start},
                onSuccess: function(transport, json){
                    if (document.Update_Form.LetterDataSource.value == source){
						if (json == null && transport['responseText'] != ''){
							json = eval(transport['responseText']);
						}
                        var items_fetched = 0;
                        for (var i in json){
                            items_fetched++;
                            ChoicesAvailable = true;
                            //xxx
                            if (source == 'artists' || source == 'people' || source == 'volunteers'){
                                key = i.replace('_','');
                            }
                            else{
                                key = html_entity_decode(i);
                            }

                            try
                            {
                                // standards compliant
                                document.Update_Form.AllArtists.add(new Option(html_entity_decode(json[i]),key),null);
                            }
                            catch(ex)
                            {
                                // IE Only
                                document.Update_Form.AllArtists.add(new Option(html_entity_decode(json[i]),key));
                            }
                            

                        }
                        if (items_fetched < limit){
                            // Step 3a.  Give the user a message
                            document.getElementById('ArtistSelectionMessage').innerHTML = '';
                            //xxx
                            if (!ChoicesAvailable){
                                if (source == 'artists'){
                                    document.getElementById('ArtistSelectionMessage').innerHTML = '<span style=\'color:red\'>There are no artists in the lineup for the selected festival year.</span>';
                                }
                                if (source == 'artisans'){
                                    document.getElementById('ArtistSelectionMessage').innerHTML = '<span style=\'color:red\'>There are no artisans for the selected festival year.</span>';
                                }
                                if (source == 'people'){
                                    document.getElementById('ArtistSelectionMessage').innerHTML = '<span style=\'color:red\'>There are no people in the database with values in the necessary \"Festival Pass\" field.</span>';
                                }
                            }

                            
                            // Step 4.  Add All options to the Chosen list
                            AllArtists = getOptions(document.Update_Form.AllArtists);   

                            // Step 5.  Re-enable the Filter
                            document.getElementById('nameFilterSpan').style.display = 'inline';
                        }
                        else{
                            document.getElementById('ArtistSelectionMessage').innerHTML = 'Fetching more names.  (Current count: '+(start+limit)+')';
                            performAjaxRequest(year,source,start+limit);
                        }
                    }
                },
                onFailure: function(){ alert('Unable to retrieve data source choices...') }
              });
        }
        
        function html_entity_decode( string ) {
            // http://kevin.vanzonneveld.net
            // +   original by: john (http://www.jd-tech.net)
            // +      input by: ger
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // *     example 1: html_entity_decode('Kevin &amp; van Zonneveld');
            // *     returns 1: 'Kevin & van Zonneveld'
 
            var ret, tarea = document.createElement('textarea');
            tarea.innerHTML = string;
            ret = tarea.value;
            return ret;
        }

        function editSelectedArtist(theSel){
                openEditArtistWindow(theSel.name,theSel.options[theSel.selectedIndex].value);
        }
        function openEditArtistWindow(selectBoxName,artistID){
            var url;
            if (document.Update_Form.LetterDataSource.value == 'artists'){
                url = '".$Bootstrap->makeAdminURL($Package,'update_artist')."&sel='+selectBoxName;
                url += '&".YEAR_PARM."=' + document.Update_Form.LetterDataYear.value;
                if (artistID != ''){
                        url += '&id='+artistID;
                }
            }
            else if (document.Update_Form.LetterDataSource.value == 'artisans'){
                url = '".CMS_ADMIN_URL.$Bootstrap->getAdminURL()."?package=Artisans&page=update&sel='+selectBoxName;
                if (document.Update_Form.LetterDataYear.value != '".$ArtisanContainer->getCurrentYear."'){
                    url += '&".YEAR_PARM."=' + document.Update_Form.LetterDataYear.value;
                }
                if (artistID != ''){
                        url += '&id='+artistID;
                }
            }
            else{  
                var poMMo;
                if (document.Update_Form.LetterDataSource.value == 'people'){
                    poMMo = 'poMMo';
                }
                else{
                    poMMo = document.Update_Form.LetterDataSource.value;
                }
                url = '".BASE_URL.(CMS_PLATFORM == 'WordPress' ? 'wp-content/plugins/' : 'lib/packages')."' + poMMo + '/admin/subscribers/subscribers_mod.php?sid=' + artistID + '&action=edit&table=subscribers';  // TODO
                alert(\"Okay, so a new window is about to open.\\n\\nMake your changes there.  You'll have to manually close the window when you're done.\");  
            }   
            var eWindow = window.open(url, 'EditArtist',
             'scrollbars=yes,menubar=yes,resizable=yes,toolbar=no,width=800,height=600');
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
        
        function update() 
        { 
           matched = document.Update_Form.nameFilter.value; 
           var re = new RegExp('^.*'+ matched +'.*$', 'mgi'); 
           for (var i=0, len=AllArtists.length; i<len; ++i){ 
                   if (AllArtists[i][\"text\"].match(re) != null){ 
                        addOption(document.Update_Form.AllArtists,AllArtists[i][\"text\"],AllArtists[i][\"value\"]); 
                   } 
           } 
        } 

        function trigger() 
        { 
          refresh(); 
          setTimeout('trigger()', 250); 
        } 

        function refresh() 
        { 
          if (AllArtists == null){ 
               AllArtists = getOptions(document.Update_Form.AllArtists);   
          } 
          var s = document.Update_Form.nameFilter.value; 
          if( s != matched ) 
          { 
            document.Update_Form.AllArtists.options.length = 0; 
            update(); 
          } 
        } 
        
        //--> 
        </script> 
        ";                        
        $admin_head_extras.=$ArtistListBoxScript;

        $ArtistsTab = new HTML_Tab('ArtistsTab','Who to Print');
        
    	$Festivals = $FestivalContainer->getAllFestivals();
    	$FestivalYears = array();
    	foreach ($Festivals as $Festival){
    	    $FestivalYears[$Festival->getParameter('FestivalYear')] = $Festival->getParameter('FestivalYear');
    	}

        $ArtistsTab->addSelect('LetterDataYear','Festival Year:',$FestivalYears,(preg_match("/[^0-9]/",$Year) ? $Year : (int)$Year),1,'',false,"onChange='updateDataSourceChoices()'");
        $ArtistsTab->addSelect('LetterDataSource','Data Source:',$LetterDataSources,$Letter->getParameter('LetterDataSource'),1,'',false,"onChange='updateLetterVariables()'");
	    $IgnoreAlreadyPrintedCode = HTML_Form::returnCheckBox('LetterIgnoreAlreadyPrinted',($Letter->getParameter('LetterID') == "" ? true : ($Letter->getParameter('LetterIgnoreAlreadyPrinted') ? true : false)));
	    $IgnoreAlreadyPrintedCode.= " Ignore people you've already printed";
	    $ArtistsTab->addPlainText('&nbsp',$IgnoreAlreadyPrintedCode);
        $ArtistSelection = "<table width='100%' align=center><tr>\n";
        $ArtistSelection.= "<td width=44% valign=top>Available choices<br><select name='AllArtists' multiple='multiple' size=10 style='width: 100%'>\n";
            

        $LetterLastPrintedSet = unserialize($Letter->getParameter('LetterLastPrintedSet'));
        if (array_key_exists($Year,$LetterLastPrintedSet) and is_array($LetterLastPrintedSet[$Year]) and array_key_exists($Letter->getParameter('LetterDataSource'),$LetterLastPrintedSet[$Year])){
            $ShowArtistIDs = $LetterLastPrintedSet[$Year][$Letter->getParameter('LetterDataSource')];
        }
        else{
            $ShowArtistIDs = array_keys($AvailableChoices);
        }
        if (!is_array($ShowArtistIDs)){
            $ShowArtistIDs = array();
        }
        // Make sure there are no Artist IDs to be shown that aren't in the Available Choices
        foreach ($ShowArtistIDs as $key => $ShowArtistID){
            if (!array_key_exists($ShowArtistID,$AvailableChoices)){
                unset($ShowArtistIDs[$key]);
            }
        }
        
        //xxx
        switch ($LetterDataSource){
        case 'artists':
            foreach ($AvailableChoices as $Artist){
                    if (!in_array($Artist->getParameter('ArtistID'),$ShowArtistIDs)){
                            $ArtistSelection.= "<option value='".$Artist->getParameter('ArtistID')."'>".$Artist->getHTMLFormattedParameter('ArtistFullName')."</option>\n";
                    }
            }
            break;
        case 'artisans':
            foreach ($AvailableChoices as $ArtisanID => $Artisan){
                    if (!in_array($Artisan->getParameter('frmArtisanID'),$ShowArtistIDs)){
                            $ArtistSelection.= "<option value='".$Artisan->getParameter('frmArtisanID')."'>".$Artisan->getHTMLFormattedParameter('frmBusinessName')."</option>\n";
                    }
            }
            break;
        case 'types':
        case 'assigned':
            foreach ($AvailableChoices as $Choice){
                    if (!in_array($Choice,$ShowArtistIDs)){
                            $ArtistSelection.= "<option value='".htmlspecialchars($Choice,ENT_QUOTES)."'>".$Choice."</option>\n";
                    }
            }
            break;
        case 'people':            
        case 'volunteers':            
            foreach ($AvailableChoices as $Choice){
                    if (!in_array($Choice->getParameter('id'),$ShowArtistIDs)){
                            if ($Choice->getParameter('LastName') == "" and $Choice->getParameter('FirstName') == ""){
                                $Choice->setParameter('FirstName',' (no name)');
                            }
                            $ArtistSelection.= "<option value='".$Choice->getParameter('id')."'>".$Choice->getParameter('FirstName')." ".$Choice->getParameter('LastName')."</option>\n";
                    }
            }
            break;
        }
        $ArtistSelection.= "</select>\n";
        $ArtistSelection.= "<br><span id='nameFilterSpan' style='display:inline'>Filter: <input type='text' name='nameFilter' id='nameFilter'/></span>\n";
        $ArtistSelection.= "<br><span id='ArtistSelectionMessage'>";
        // xxx
        if (!count($AvailableChoices)){
            $ArtistSelection.= "<span style='color:red'>";
            switch($LetterDataSource){
            case 'artists':
                $ArtistSelection.= "There are no artists in the lineup for the selected festival year.";
                break;
            case 'artisans':
                $ArtistSelection.= "There are no artisans for the selected festival year.";
                break;
            case 'people':
                $ArtistSelection.= "There are no people in the database with values in the necessary \"Festival Pass\" field.";
                break;
            case 'volunteers':
                $ArtistSelection.= "There are no volunteers in the database who have been assigned into an area.";
                break;
            }
            $ArtistSelection.= "</span>\n";
        }
        $ArtistSelection.= "<span>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "<td width=6% valign=middle align=center>\n";
        $ArtistSelection.= "<input type='button' name='AddArtist' value='--&gt;' onclick='moveOptions(document.Update_Form.AllArtists,document.Update_Form.ShowArtists);'><br><br>";
        $ArtistSelection.= "<input type='button' name='RemoveArtist' value='&lt;--' onclick='moveOptions(document.Update_Form.ShowArtists,document.Update_Form.AllArtists);'>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "<td width=44% valign=top>Print these ones<br><select name='ShowArtists' multiple='multiple' size=10 style='width: 100%'>\n";
        //xxx
        switch ($LetterDataSource){
        case 'artists':
            foreach ($ShowArtistIDs as $ShowArtistID){
                    $ArtistSelection.= "<option value='$ShowArtistID'>".$AvailableChoices[$ShowArtistID]->getParameter('ArtistFullName')."</option>\n";
            }
            break;
        case 'artisans':
            foreach ($ShowArtistIDs as $ShowArtistID){
                    $ArtistSelection.= "<option value='".$ShowArtistID."'>".$AvailableChoices[$ShowArtistID]->getParameter('frmBusinessName')."</option>\n";
            }
            break;
        case 'types':
        case 'assigned':
            foreach ($ShowArtistIDs as $ShowArtistID){
                    $ArtistSelection.= "<option value='".htmlspecialchars($ShowArtistID,ENT_QUOTES)."'>".$ShowArtistID."</option>\n";
            }
            break;
        case 'people':            
        case 'volunteers':            
            foreach ($ShowArtistIDs as $ShowArtistID){
                    if ($AvailableChoices[$ShowArtistID]->getParameter('FirstName') == "" and $AvailableChoices[$ShowArtistID]->getParameter('LastName') == ""){
                        $AvailableChoices[$ShowArtistID]->setParameter('FirstName',' (no name)');
                    }
                    $ArtistSelection.= "<option value='$ShowArtistID'>".$AvailableChoices[$ShowArtistID]->getParameter('FirstName')." ".$AvailableChoices[$ShowArtistID]->getParameter('LastName')."</option>\n";
            }
            break;
        }
        $ArtistSelection.= "</select>\n";
        $ArtistSelection.= "<br><input type='button' name='AddAll' value='Add All Choices' onClick='addAllArtists();'/>\n";
        $ArtistSelection.= "<input type='button' name='ClearArtists' value='Clear' onClick='clearArtists();'/>\n";
        $ArtistSelection.= "<input type='button' id='EditSelected' name='EditSelected' value='Edit Selected' onClick='editSelectedArtist(document.Update_Form.ShowArtists);' style='display:".(in_array($LetterDataSource,array('types','workshops','assigned')) ? 'none' : 'inline')."'/>\n";
        $ArtistSelection.= "</td>\n";

        $ArtistSelection.= "<td width=6% valign=middle align=center>&nbsp;</td>\n";
        
        $ArtistSelection.= "</tr></table>\n";
        
        $ArtistsTab->addPlainText('Choices:',$ArtistSelection);   
        
        $LabelPrintOptions = "<div id='LabelPrintOptions'>\n";
        $LabelPrintOptions.= "<p><strong>Choose...</strong>\n"; // filler
        $LabelPrintOptions.= "<input type='radio' name='LetterPrintChoice' value='SelectedChoices' ".(($Letter->getParameter('LetterPrintChoice') == "" or $Letter->getParameter('LetterPrintChoice') == "SelectedChoices") ? "checked" : "")."> Print above choices";
        $LabelPrintOptions.= "</p>\n";
        $LabelPrintOptions.= "<p><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Or...</strong>\n";
        $LabelPrintOptions.= "<input type='radio' name='LetterPrintChoice' value='TestPage' ".($Letter->getParameter('LetterPrintChoice') == "TestPage" ? "checked" : "")."> Print a test page (to align labels)";
        $LabelPrintOptions.= "</p>\n";
        $LabelPrintOptions.= "<p><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Or...</strong>\n";
        $LabelPrintOptions.= "<input type='radio' name='LetterPrintChoice' value='BlankPages' ".($Letter->getParameter('LetterPrintChoice') == "BlankPages" ? "checked" : "")."> Simply print ".HTML_Form::returnText('LetterPrintPagesNumber',1,3)." pages of this label";
        $LabelPrintOptions.= "</p>\n";
        $ArtistsTab->addPlainText('&nbsp;',$LabelPrintOptions);   
        
        $form->addTab($ArtistsTab); 	
    	
    	
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

	    $form->addHidden('form_submitted','true');
        $form->addHidden('strShowArtists','nothing');
        $form->addSubmit('save','Save Changes','onClick="saveShowArtists()"');
        $form->addSubmit('print',"Preview",'onClick="saveShowArtists()"');
	    $form->addSubmit('cancel','Cancel');
	    
	    $smarty->assign('includes_tabbed_form',true);
	    $smarty->assign('Tabs',$form->getTabs());
    	$smarty->assign('form',$form);
    	$smarty->assign('form_attr','width=90% align=center');
		if (file_exists(CMS_INSTALL_DOC_BASE.'lib/js/tinymce/')){
			$admin_head_extras.= "
	    	    <script language='javascript' type='text/javascript' src='".CMS_INSTALL_URL."lib/js/tinymce/jscripts/tiny_mce/tiny_mce_src.js'></script>
			";
		}
		else{
			$admin_head_extras.= "
	    	    <script language='javascript' type='text/javascript' src='".CMS_INSTALL_URL."lib/js/tinymce/jscripts/tiny_mce/tiny_mce_src.js'></script>
			";
		}
    	$admin_head_extras.= "
            <script language='javascript' type='text/javascript'>
            tinyMCE.init({
                theme : 'advanced',
            	mode : 'exact',
            	elements : 'WYSIWYG_Editor',
                theme_advanced_toolbar_location : 'top',
                plugins : 'fullscreen,preview,media,table,smartycode',
                theme_advanced_buttons1 : 'cut,copy,paste,undo,redo,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,'
                + 'styleselect,formatselect,fontsizeselect,|,forecolor,backcolor',
                theme_advanced_buttons2 : 'bullist,numlist,outdent,indent,'
                + 'link,unlink,image,media,separator,'
                + 'removeformat,cleanup,code,smartycode,fullscreen,separator,charmap,|,tablecontrols',
                theme_advanced_buttons3 : '',
                width : '800px',
                file_browser_callback : 'myFileBrowser',
                relative_urls : false,
                convert_urls : false, 
    		    theme_advanced_resizing : true,
                theme_advanced_layout_manager : 'SimpleLayout',
                theme_advanced_statusbar_location : 'bottom',
                extended_valid_elements : ''
                    +'object[align<bottom?left?middle?right?top|archive|border|class|classid'
                      +'|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name'
                      +'|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                      +'|onmouseout|onmouseover|onmouseup|standby|style|tabindex|title|type|usemap'
                      +'|vspace|width],'
                    +'param[id|name|type|value|valuetype<DATA?OBJECT?REF],'
                    +'script[charset|defer|language|src|type],'
                    +'form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang'
                      +'|method<get?post|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
                      +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit'
                      +'|style|title|target],'
                    +'style[dir<ltr?rtl|lang|media|title|type],'
                    +'textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name'
                      +'|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
                      +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
                      +'|readonly<readonly|rows|style|tabindex|title],'                
                    +'input[accept|accesskey|align<bottom?left?middle?right?top|alt'
                      +'|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang'
                      +'|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress'
                      +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
                      +'|readonly<readonly|size|src|style|tabindex|title'
                      +'|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text'
                      +'|usemap|value],'
                    +'option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick'
                      +'|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
                      +'|onmouseover|onmouseup|selected<selected|style|title|value],'
                    +'select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name'
                      +'|onblur|onchange|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
                      +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style'
                      +'|tabindex|title],'  
                    +'link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|media|onclick'
                      +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
                      +'|onmouseout|onmouseover|onmouseup|rel|rev|style|title|target|type],'
                    +'embed[src|flashVars|menu|quality|wmode|bgcolor|width|height|type|pluginspage]',
                force_p_newlines : true,
      		    apply_source_formatting : true
            });

            function myFileBrowser (field_name, url, type, win) {
                var cmsURL = '".CMS_ADMIN_URL.$Bootstrap->makeAdminURL('Common','file_browser')."';      // script URL

                // newer writing style of the TinyMCE developers for tinyMCE.openWindow

				tinyMCE.activeEditor.windowManager.open({
			        file : cmsURL + '&type=' + type, // PHP session ID is now included if there is one at all
			        title : '{/literal}{$Bootstrap->getAdminTitle('Common','file_browser')}{literal}',
			        width : 800,  // Your dimensions may differ - toy around with them!
			        height : 600,
			        close_previous : 'no',
			        resizable : 'yes',
			        scrollbars : 'yes'
				},
				{
			        window : win,
			        input : field_name,
			        browser_type : type
				});

                return false;
            }
        
            function getValue(inputItem){
                if (document.getElementById(inputItem).type == 'checkbox'){
                    return document.getElementById(inputItem).checked;
                }
                else{
                    return document.getElementById(inputItem).value;
                }
            }
            </script>	 
        ";   
    
        
        $Content = $smarty->fetch('admin_form.tpl');
    }
    else{
        
        if (!$ShowArtistIDs){
            $LetterLastPrintedSet = unserialize($Letter->getParameter('LetterLastPrintedSet'));
            if (array_key_exists($Year,$LetterLastPrintedSet) and array_key_exists($Letter->getParameter('LetterDataSource'),$LetterLastPrintedSet[$Year])){
                $ShowArtistIDs = $LetterLastPrintedSet[$Year][$Letter->getParameter('LetterDataSource')];
            }
            if (!is_array($ShowArtistIDs)){
                $ShowArtistIDs = array();
            }
        }
        if ($Letter->getParameter('LetterIgnoreAlreadyPrinted')){
            $LetterAlreadyPrinted = unserialize($Letter->getParameter('LetterAlreadyPrinted'));
            switch ($Letter->getParameter('LetterDataSource')){
            case 'artists':
            case 'people':
            case 'volunteers':
            case 'artisans':
                if (array_key_exists($Year,$LetterAlreadyPrinted) and array_key_exists($Letter->getParameter('LetterDataSource'),$LetterAlreadyPrinted[$Year])){
                    $ShowArtistIDs = array_diff($ShowArtistIDs,$LetterAlreadyPrinted[$Year][$Letter->getParameter('LetterDataSource')]);
                }
                break;
            default:
                // Other Data Sources are handled below by looping through the people and unsetting
                // any one who was in the AlreadyPrinted Set
                break;
            }
        }

        //xxx
        switch ($Letter->getParameter('LetterDataSource')){
        case 'artists':
            $FestivalArtists = $FestivalArtistContainer->getLineup($Year,'ArtistFullName');

            if (!is_array($FestivalArtists)){
                $FestivalArtists = array();
            }
        
            foreach ($FestivalArtists as $key => $FestivalArtist){
                if (!in_array($key,$ShowArtistIDs)){
                    unset($FestivalArtists[$key]);
                }
            }
            break;
        case 'artisans':
            $tmpPeople = $ArtisanContainer->getAllArtisans($Year);
            $People = array();
            foreach ($tmpPeople as $tmpArtisan){
                $People[$tmpArtisan->getParameter('frmArtisanID')] = $tmpArtisan;
            }

            if (!is_array($People)){
                $People = array();
            }
        
            foreach ($People as $key => $Person){
                if (!in_array($Person->getParameter('frmArtisanID'),$ShowArtistIDs)){
                    unset($People[$key]);
                }
            }
            break;
        case 'types':
            $People = $PeoplePassesHelper->getPeopleOfType($ShowArtistIDs);
            break;
        case 'people':
            $People = $PeoplePassesHelper->getSubscriber($ShowArtistIDs);
            break;
        case 'assigned':
            $People = $VolunteerCategoryHelper->getVolunteersAssignedToCategory($ShowArtistIDs);
            break;
        case 'volunteers':
            $People = $VolunteerCategoryHelper->getVolunteer($ShowArtistIDs);
            break;
        }

        if ($Letter->getParameter('LetterIgnoreAlreadyPrinted')){
            switch ($Letter->getParameter('LetterDataSource')){
            case 'artists':
            case 'people':
            case 'volunteers':
            case 'artisans':
                // Handled above
                break;
            default:
                $LetterDataSource = $Letter->getParameter('LetterDataSource');
                if ($LetterDataSource == 'types')       $LetterDataSource = 'people';
                if ($LetterDataSource == 'assigned')    $LetterDataSource = 'volunteers';
                if (array_key_exists($Year,$LetterAlreadyPrinted) and array_key_exists($LetterDataSource,$LetterAlreadyPrinted[$Year])){
                    $ap_array = $LetterAlreadyPrinted[$Year][$LetterDataSource];
                    foreach ($ap_array as $ap_id){
                        if (array_key_exists($ap_id,$People)){
                            unset($People[$ap_id]);
                        }
                    }
                }
                break;
            }
        }



        $Letter->smartyEncodeParameter('LetterContent');
        switch($Letter->getParameter('LetterType')){
        case 'label':
            // Some constants  TODO: make these adjustable via form
	        $LetterLabelDetails = unserialize($Letter->getParameter('LetterLabelDetails'));
	        if (!is_array($LetterLabelDetails)){
	            $LetterLabelDetails = array();
                foreach ($LabelDetailsParms as $LabelDetailsParm => $Default){
                    $LetterLabelDetails[$LabelDetailsParm] = $Default;                
                }
	        }
	        
	        /*
            $BadgeWidth = 3.75; // inches
            $BadgeHeight = 3; //inches
            $BadgeColumns = 2;
            $BadgeRows = 3;
            $Padding = 5; // pixels
            */

            // Okay, first we have to define the array of all of the passes
            $PrintPasses = array();

            if ($Letter->getParameter('LetterPrintChoice') == 'TestPage' or $Letter->getParameter('LetterPrintChoice') == 'BlankPages'){
                if ($Letter->getParameter('LetterPrintChoice') == 'TestPage' or !is_numeric($_POST['LetterPrintPagesNumber'])){
                    $Pages = 1;
                }
                else{
                    $Pages = intval($_POST['LetterPrintPagesNumber']);
                }
                $SpoofLabels = $Pages * $LetterLabelDetails['LetterNumberAcross'] * $LetterLabelDetails['LetterNumberDown'];
                for ($i = 0; $i < $SpoofLabels; $i++){
                    $PrintPasses[] = array();
                }
            }
            else{
                switch ($Letter->getParameter('LetterDataSource')){
                case 'artists':
                    $PassTypes = array("Performer","Guest");
                    foreach ($FestivalArtists as $ArtistID => $FestivalArtist){
                        $FestivalArtists[$ArtistID]->setParameter('ArtistShows',getShortArtistShows($ArtistID));
                    }
                    foreach ($FestivalArtists as $Artist){
                        if (!$Artist->getParameter('ArtistBand') and $Artist->getParameter('ArtistGuests') != ""){
                            // Use this to only put out one pass per band
                            //$PrintPasses[] = array('Type' => 'Foo', 'ArtistID' => $Artist->getParameter('ArtistID'), 'PassName' => "", 'Artist' => $Artist);
                        
                            // Use this to only put out passes for all performers and guests
                            $Passes = $Artist->getParameter('ArtistGuests');
                            foreach ($PassTypes as $PassType){
                                preg_match("/$PassType:(.*)/",$Passes,$matches);
                                $PassNamesString = trim ($matches[1]);
                                if ($PassNamesString != ""){
                                    $PassNamesArray = explode(",",$PassNamesString);
                                }
                                else{
                                    $PassNamesArray = array();
                                }
                                foreach ($PassNamesArray as $PassName){
                                    $PassName = trim($PassName);
                                    if ($PassName != "none"){
                                        if (preg_match("/\(([^\)]*)\)/",$PassName,$NumberOfPasses)){
                                            for ($i = 0; $i < $NumberOfPasses[1]; $i++){
                                                $PrintPasses[] = array('Type' => $PassType, 'ArtistID' => $Artist->getParameter('ArtistID'), 'PassName' => "", 'Artist' => $Artist);
                                            }
                                        }
                                        else{
                                            $PrintPasses[] = array('Type' => $PassType, 'ArtistID' => $Artist->getParameter('ArtistID'), 'PassName' => $PassName, 'Artist' => $Artist);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
                case 'artisans':
                    foreach ($People as $Person){
                        // The Main Artisan contact
                        $PrintPasses[] = array('PassName' => $Person->getParameter('frmYourName'), 'Artisan' => $Person);
                    
                        // The Artisan Assistants
                        $PrintPasses[] = array('PassName' => 'Assistant', 'Artisan' => $Person);
                        
                        /*
                        if ($Person->getParameter('frmAssistants')){
                            $PassNamesString = trim ($Person->getParameter('frmAssistants'));
                            if ($PassNamesString != ""){
                                $PassNamesArray = explode(",",$PassNamesString);
                            }
                            else{
                                $PassNamesArray = array();
                            }
                            foreach ($PassNamesArray as $PassName){
                                $PassName = trim($PassName);
                                if ($PassName != "none"){
                                    if (preg_match("/\(([^\)]*)\)/",$PassName,$NumberOfPasses) and is_numeric($NumberOfPasses[1])){
                                        for ($i = 0; $i < $NumberOfPasses[1]; $i++){
                                            $PrintPasses[] = array('PassName' => 'Assistant', 'Artisan' => $Person);
                                        }
                                    }
                                    else{
                                        $PrintPasses[] = array('PassName' => $PassName, 'Artisan' => $Person);
                                    }
                                }
                            }
                        }
                        */
                    }
                    break;
                case 'people':
                case 'types':
                    foreach ($People as $Person){
                        $PrintPasses[] = array('Person' => $Person);
                        if (intval($Person->getParameter('AdditionalFestivalPasses')) > 0){
                            $tmpPerson = new Parameterized_Object($Person->getParameters());
                            $tmpPerson->setParameter('AdditionalPass','true');
                            for($i = 0; $i < intval($Person->getParameter('AdditionalFestivalPasses')); $i++){
                                $PrintPasses[] = array('Person' => $tmpPerson);
                            }
                        }
                    }
                    break;
                default:
                    foreach ($People as $Person){
                        $PrintPasses[] = array('Person' => $Person);
                    }
                    break;
                }
            
                $BlankPasses = 0;
                while (count($PrintPasses) % ($LetterLabelDetails['LetterNumberAcross'] * $LetterLabelDetails['LetterNumberDown']) !== 0){
                    $PrintPasses[] = "";
                    $BlankPasses++;
                }
            }
            
            // If we're printing the back, we have to collate them so that the proper schedule
            // gets printed on the back of the proper pass.  To do that, all we do is switch the 
            // positions of each pair of passes
            if ($Letter->getParameter('LetterType') == 'label' and $LetterLabelDetails['LetterCollateForReverse'] and $LetterLabelDetails['LetterNumberAcross'] > 1){
                // We're going to grab and work with chunks of 'LetterNumberAcross' labels
                $offset = 0;
                $NewPrintPasses = array();
                for ($offset = 0; $offset < (count($PrintPasses) / $LetterLabelDetails['LetterNumberAcross']); $offset++){
                    $tmp = array_slice($PrintPasses,$offset * $LetterLabelDetails['LetterNumberAcross'],$LetterLabelDetails['LetterNumberAcross']);
                    $tmp = array_reverse($tmp);
                    $NewPrintPasses = array_merge($NewPrintPasses,$tmp);
                }
                
                $PrintPasses = $NewPrintPasses;
            }
    
    
            
            
            // Here's all the complicated stuff to actually print the suckers.
            $BadgesPrinted = 0;
            if (!count($PrintPasses)){
                $LetterContent = "
                <div align='center' style='text-align:left;width:500px;margin-left:auto;margin-right:auto'>
                <h2>Warning</h2>
                ";
                
                switch ($Letter->getParameter('LetterDataSource')){
                case 'artists':
                    $LetterContent.="
                    <p>No information was found for the selected artists as to what names should go on the passes.</p>
                    <p>To specify those names:</p>
                
                    <ol>
                    <li>Open up the Edit Artist window (the thing where you enter the bio information)</li>
                    <li>Click on the '".date("Y")." Festival Info' tab (or whatever festival year you're working on)</li>
                    <li>There is a box for \"Performer Passes\" and one for \"Guest Passes\".  This is where you'll enter them.  If you know the names, enter them separated by commas.  If you don't know the names, you can enter a number to say \"print this number of passes\".
                    </ol>
                
                    <p>So, for example, if you've got The Beatles coming, in the Performer Passes box, you'll enter</p>

                    <p style='margin-left:40px'>John Lennon, Paul McCartney, Ringo Starr, George Harrison, (1)</p>

                    <p>to indicate one pass for each of the boys and one extra performer pass for their surprise mystery guest.  In the Guest Passes box you'll enter</p>

                    <p style='margin-left:40px'>Yoko Ono, (3)</p>

                    <p>to indicate one pass for Yoko and 3 un-named guest passes.  </p>
                    ";
                    break;
                case 'types':
                    $LetterContent.="
                    <p>You have selected to print types for which there are no people in the database.  </p>
                    <p>Go through the database and set the \"Festival Pass\" field to the appropriate values for every person for whom you wish to print a pass</p>
                    ";
                    break;
                }
                if ($Letter->getParameter('LetterIgnoreAlreadyPrinted')){
                    $LetterContent.= "<p>It's possible that you've chosen to ignore passes you've already printed and we couldn't find any passes that aren't in the \"Already Printed Set\".</p>\n";
                    $LetterContent.= "<p>To reset the Already Printed set, choose that from the above dropdown box and click the Go button.</p>\n";
                }
                $LetterContent.= "</div>\n";
                
            }
            
            // Here's the actual generation of the labels
            foreach ($LabelDetailsParms as $LabelDetailsParm => $Default){
                $LetterLabelDetails[$LabelDetailsParm] = str_replace('"','',$LetterLabelDetails[$LabelDetailsParm]);
            }
            foreach ($PrintPasses as $PrintPass){
                /*
                if ($BadgesPrinted % $LetterLabelDetails['LetterNumberAcross'] === 0){
                    $MarginLeft = $LetterLabelDetails['LetterSideMargin'];
                }
                else{
                    $MarginLeft = '0';
                }
                */

                if (($BadgesPrinted % ($LetterLabelDetails['LetterNumberAcross'] * $LetterLabelDetails['LetterNumberDown'])) === 0){
                    if ($BadgesPrinted > 0){
                        $LetterContent.= "<div style='clear:both'>&nbsp;</div>\n";
                        $LetterContent.= "</div> <!-- LabelWrapper -->\n";
                        //$LetterContent.= "<div class='noprint'>&nbsp;</div>\n";
                    }
                    if ($BadgesPrinted === 0){
                        $LetterContent.= "<div class='noprint'>";
                        $LetterContent.= "<h3 style='text-align:center;margin-top:50px;'>".(count($PrintPasses) - $BlankPasses)." Passes Total</h3>";
                        $LetterContent.= "</div>\n";
                    }
                    else{
                        //$LetterContent.= "<div style='page-break-before:always'>&nbsp;</div>\n";
                    }
            
                    $Width = ($LetterLabelDetails['LetterHorizontalPitch'] * $LetterLabelDetails['LetterNumberAcross']) + 0.5;
                    $Height = $LetterLabelDetails['LetterTopMargin']; 
                    $MarginLeft = ceil($Width / 2);
                    if ($BadgesPrinted === 0){
                        $PageBreak = "";
                    }
                    else{
                        $PageBreak = ";page-break-before:always";
                    }
                    $LetterContent.= "<div style='height:{$Height}in;{$PageBreak}' class='noscreen'><img src='".$Package->getPackageURL()."admin/images/spacer.gif'></div>\n";
                    $LetterContent.= "<div class='LabelWrapper' style='width:{$Width}in;margin-left:-{$MarginLeft}in'>\n";
                    $LetterContent.= "<div class='noprint'><h3 style='text-align:center;margin-top:50px;'>Page ".($BadgesPrinted / ($LetterLabelDetails['LetterNumberAcross'] * $LetterLabelDetails['LetterNumberDown']) + 1)."</h3></div>\n";
                }

                $LetterContent.= "<div style='float:left;position:relative;overflow:hidden;width:{$LetterLabelDetails['LetterLabelWidth']}in;margin-right:".($LetterLabelDetails['LetterHorizontalPitch'] - $LetterLabelDetails['LetterLabelWidth'])."in;margin-bottom:".($LetterLabelDetails['LetterVerticalPitch'] - $LetterLabelDetails['LetterLabelHeight'])."in;height:{$LetterLabelDetails['LetterLabelHeight']}in;padding:5px' class='BorderNoPrint'>\n";
                //$LetterContent.= "<table width='100%' height='100%' cellpadding='0' cellspacing='0' style='position:relative;'><tr><td valign='middle'>\n";
                // Fill in the variables for the Pass Template
                if ($PrintPass != ""){ // will only be true when printing the back, when we need a blank pass
                    $smarty->assign('Year',$Year);
                    if ($Letter->getParameter('LetterPrintChoice') == 'TestPage'){
                        $Letter->setParameter('LetterContent',"<img width='100%' height='100%' src='".$Package->getPackageURL()."admin/images/grid.gif'/>");
                    }
                    elseif($Letter->getParameter('LetterPrintChoice') == 'BlankPages'){
                        
                    }
                    elseif ($Letter->getParameter('LetterDataSource') == 'artists'){
                        $smarty->assign('PassType',$PrintPass['Type']);
                        $smarty->assign('PassName',$PrintPass['PassName']);
                        $smarty->assign('Artist',deparameterize($PrintPass['Artist']));
                        $smarty->assign('StageNamesLegend',getStageNamesLegendString());
                    }
                    elseif($Letter->getParameter('LetterDataSource') == 'artisans'){
                        $smarty->assign('PassName',$PrintPass['PassName']);
                        $smarty->assign('Artisan',deparameterize($PrintPass['Artisan']));
                    }
                    else{
                        $smarty->assign('Person',deparameterize($PrintPass['Person']));
                    }
                    $smarty->assign('WhichColumn',$BadgesPrinted % $LetterLabelDetails['LetterNumberAcross']);
                    $smarty->assign('Content',$Letter->getParameter('LetterContent'));
                    $LetterContent.= $smarty->fetch('blank.tpl');
                }
                else{
                    $LetterContent.= "&nbsp;";
                }
                //$LetterContent.= "</td></tr></table>\n";
                $LetterContent.= "</div>\n";
                
                $BadgesPrinted++;
            }
            if ($BadgesPrinted > 0){
                $LetterContent.= "</div> <!-- LabelWrapper -->\n";
            }
            
            $Content = $LetterContent;
                
            break;
        case 'letter':
        default:
            $smarty->assign('Year',$Year);
            switch ($Letter->getParameter('LetterDataSource')){
            case 'artists':
                if ($_GET['id'] == ""){
                    foreach ($FestivalArtists as $ArtistID => $FestivalArtist){
                        $FestivalArtists[$ArtistID]->setParameter('ArtistShows',getArtistShows($ArtistID));
                    }
                    $LetterContent = '{foreach from=$FestivalArtists item=Artist name=PersonLoop}';
                    if ($Letter->getParameter('LetterPageBreak')){
                        $LetterContent.= "<h2 class='noprint'>".'{$Artist.ArtistFullName}'."</h2>\n";
                        $LetterContent.= "<div class='noscreen'>\n";
                    }
                    $LetterContent.= $Letter->getParameter('LetterContent');
                    if ($Letter->getParameter('LetterPageBreak')){
                        $LetterContent.= "</div>\n";
                        $LetterContent.= "<p class='noprint'><a href='".$SelfURL."&id=".'{$Artist.ArtistID}'."'>Expand</a></p>\n";
                        $LetterContent.= '<hr class=\'noprint\'>';
                        $LetterContent.= "<div style='page-break-after:always'>&nbsp;</div>\n";
                    }
                    $LetterContent.= '{/foreach}';
                    
                    if (!count($FestivalArtists)){
                        $LetterContent.= "<h3>Warning</h3><p>Nothing to print</p>";
                    }
                    $smarty->assign('FestivalArtists',deparameterize($FestivalArtists));
                }
                else{
                    $Artist = $FestivalArtistContainer->getFestivalArtist($Year,$_GET['id']);
                    $Artist->setParameter('ArtistShows',getArtistShows($Artist->getParameter('ArtistID')));
                    $smarty->assign('Artist',deparameterize($Artist));
        
                    $LetterContent = $Letter->getParameter('LetterContent');
                    $LetterContent.= "<p class='noprint'><a href='".$SelfURL."&collapse=true'>Collapse</a></p>\n";
                }
                break;
            default:
                if ($_GET['id'] == ""){
                    $LetterContent = '{foreach from=$FestivalArtists item=Artist name=PersonLoop}';
                    if ($Letter->getParameter('LetterPageBreak')){
                        $LetterContent.= "<h2 class='noprint'>".'{$Artist.ArtistFullName}'."</h2>\n";
                        $LetterContent.= "<div class='noscreen'>\n";
                    }
                    $LetterContent.= $Letter->getParameter('LetterContent');
                    if ($Letter->getParameter('LetterPageBreak')){
                        $LetterContent.= "</div>\n";
                        $LetterContent.= "<p class='noprint'><a href='".$SelfURL."&id=".'{$Artist.ArtistID}'."'>Expand</a></p>\n";
                        $LetterContent.= '<hr class=\'noprint\'>';
                        $LetterContent.= "<div style='page-break-after:always'>&nbsp;</div>\n";
                    }
                    $LetterContent.= '{/foreach}';


                    $LetterContent = '{foreach from=$People item=Person name=PersonLoop}';
                    if ($Letter->getParameter('LetterPageBreak')){
                        $LetterContent.= "<h2 class='noprint'>".'{$Person.Name}'."</h2>\n";
                        $LetterContent.= "<div class='noscreen'>\n";
                    }
                    $LetterContent.= $Letter->getParameter('LetterContent');
                    
                    if ($Letter->getParameter('LetterPageBreak')){
                        $LetterContent.= "</div>\n";
                        $LetterContent.= "<p class='noprint'><a href='".$SelfURL."&id=".'{$Person.id}'."'>Expand</a></p>\n";
                        $LetterContent.= '<hr class=\'noprint\'>';
                        $LetterContent.= "<div style='page-break-after:always'>&nbsp;</div>\n";
                    }
                    $LetterContent.= '{/foreach}';
                    if (!count($People)){
                        $LetterContent.= "<h3>Warning</h3><p>No people matching your search criteria were found.  Either update the database (updating the Festival Pass fields for people you want to print), or change your pass type choices.</p>";
                    }
    
                    $smarty->assign('People',deparameterize($People));
                }
                else{
                    $Person = $People[$_GET['id']];
                    $smarty->assign('Person',deparameterize($Person));
        
                    $LetterContent = $Letter->getParameter('LetterContent');
                    $LetterContent.= "<p class='noprint'><a href='".$SelfURL."&collapse=true'>Collapse</a></p>\n";
                }
                break;
            }
            
            $smarty->assign('Content',$LetterContent);
    	    $Content = $smarty->fetch('blank.tpl');            
        }
        
        
    }
    
    function deparameterize($Objects){
        if (!is_array($Objects)){
            $_Objects = array($Objects);
        }
        else{
            $_Objects = $Objects;
        }
        $return = array();
        
        foreach ($_Objects as $key => $_Object){
            if (is_a($_Object,'Parameterized_Object')){
                $return[$key] = $_Object->getParameters();
            }
        }
        
        if (!is_array($Objects)){
            return array_pop($return);
        }
        else{
            return $return;
        }
    }
    
    function getShortArtistShows($ArtistID){
        global $Year,$ShowContainer;
        global $StageNamesLegend;
        
        if (!is_array($StageNamesLegend)){
            $StageNamesLegend = array();
        }

        $AllArtistShows = $ShowContainer->getAllBandMemberShows($Year,$ArtistID,true,"",array('ShowDay','ShowStartTime','ShowStage'));
        
        $return = "";
        if (is_array($AllArtistShows) and count($AllArtistShows)){
            foreach ($AllArtistShows as $_ArtistID => $ArtistShows){
                if (is_array($ArtistShows['Shows']) and count($ArtistShows['Shows'])){
                    $return.= "<p style='margin:0;font-size:9pt'><b>".$ArtistShows['Artist']->getParameter('ArtistFullName')."</b>:  ";
                    foreach ($ArtistShows['Shows'] as $Show){
                        $return.= "<br />";
                        $return.= "&nbsp;&nbsp;&nbsp;- ";
                        if ($Show->getParameter('ShowTitle') == $Show->getArtistNames()){
                            $return.= "Concert - ";
                        }
                        else{
                            $return.= $Show->getParameter('ShowTitle')." - ";
                        }
                        $return.= date("D",strtotime($Show->getParameter('ShowPrettyDay')))." ";
                        $return.= $Show->getParameter('ShowPrettyStartTime')." ";
                        $StageInitials = substr(preg_replace("/[^A-Z]/","",$Show->getParameter('ShowPrettyStage')),0,2);
                        if (array_key_exists($StageInitials,$StageNamesLegend) and $StageNamesLegend[$StageInitials] != $Show->getParameter('ShowPrettyStage')){
                            $i = 2;
                            while (array_key_exists($StageInitials.$i,$StageNamesLegend)){
                                if ($StageNamesLegend[$StageInitials.$i] == $Show->getParameter('ShowPrettyStage')){
                                    break;
                                }
                                $i++;
                            }
                            $StageInitials.=$i;
                        }
                        $StageNamesLegend[$StageInitials] = $Show->getParameter('ShowPrettyStage');
                        $return.= "(".$StageInitials.")";
                    }
                    $return.= "</p>";
                    $return.= "\n";
                }
            }
        }
        return $return;
    }
    
    function getStageNamesLegendString(){
        global $StageNamesLegend;
        
        $return = "";
        $sep = "";
        foreach ($StageNamesLegend as $Initials => $StageName){
            $return.= "$sep($Initials) - $StageName";
            $sep = "; ";
        }
        return $return;
    }

    function getArtistShows($ArtistID){
        global $Year,$ShowContainer;
        
        $AllArtistShows = $ShowContainer->getAllBandMemberShows($Year,$ArtistID,true,"",array('ShowDay','ShowStartTime','ShowStage'));
        
        $return = "";
        if (is_array($AllArtistShows) and count($AllArtistShows)){
            foreach ($AllArtistShows as $_ArtistID => $ArtistShows){
                if (is_array($ArtistShows['Shows']) and count($ArtistShows['Shows'])){
                    $return.= "<p style='margin:0;font-size:9pt'><b>".$ArtistShows['Artist']->getParameter('ArtistFullName')."</b> is appearing at:  ";
                    $return.= "<ul style='text-align:left;margin-top:0'>";
                    foreach ($ArtistShows['Shows'] as $Show){
                        $return.= "<li><p style='margin:3px 0 0 0'>";
                        if ($Show->getParameter('ShowTitle') == $Show->getArtistNames()){
                            $return.= "Concert";
                        }
                        else{
                            $return.= $Show->getParameter('ShowTitle');
                        }
                        if ($Show->getParameter('ShowPrettyDay') != ""){
                            $return.= " - ".$Show->getParameter('ShowPrettyDay')." at ".$Show->getParameter('ShowPrettyStartTime')." ".$Show->getParameter('ShowLocationConjunction')." ".$Show->getParameter('ShowPrettyStage');
                        }
                        $return.= "</p>";
                        // Include the description and notes to artists
                        if ($Show->getParameter('ShowDescription') != "" or $Show->getParameter('ShowNotesToArtist') != ""){
                            $return.= "<ul>";
                            if ($Show->getParameter('ShowDescription') != ""){
                                $return.= "<li style='margin:0'><p style='margin:0'>".$Show->getParameter('ShowDescription')."</p></li>\n";
                            }
                            if ($Show->getParameter('ShowNotesToArtist') != ""){
                                $return.= "<li style='margin:0'><p style='margin:0'>".$Show->getParameter('ShowNotesToArtist')."</p></li>\n";
                            }
                            $return.= "</ul>";
                        }
                        $return.= "</li>\n";
                    }
                    $return.= "</ul></p>";
                    $return.= "\n";
                }
            }
        }
        return $return;
    }
    
    

    $admin_head_extras.= "
    <style type='text/css' media='print'>
    <!--
        body{margin:0px}
		div.outlined_box{display:none}
	".(CMS_PLATFORM == 'WordPress' ? "
		div#wphead{display:none}
		ul#adminmenu{display:none}
		div#screen-meta{display:none}
		div#update-nag{display:none}
		div#footer{display:none}
		div#wpbody-content{margin:0px !important;position:absolute;top:0px;left:0px;}
		table{
			border:1px solid black;
			border-collapse:collapse;
		}
		td{
			padding:5px;
		}
		
	".apply_filters('additional_printable_print_styles','') : ""
   )."
        .BorderNoPrint { border: 0; }
        .LabelWrapper { margin-left: ".str_replace('"','',$LetterLabelDetails['LetterSideMargin'])."in !important; }
        .noprint { display:none;}
        .noscreen { display:block; }
    -->
    </style>
    <style type='text/css' media='screen'>
    <!--
        .BorderNoPrint { border: 1px solid black; }
        .LabelWrapper { position:relative; left:50%; }
        .noprint { display:block;}
		".(CMS_PLATFORM == 'WordPress' ? "
			div#LetterPreview table{
				border:1px solid black;
				border-collapse:collapse;
			}
			div#LetterPreview td{
				padding:5px;
			}

		".apply_filters('additional_printable_screen_styles','') : ""
	   )."
        .noscreen { display:".($_GET['id'] == "" ? "none" : "block")."; }
    -->
    </style>
    <style type='text/css'>
    <!--
    body, p, li {
        font-family:Arial,Helvetica,sans-serif;
        font-size:10pt;
    }
    -->
    </style>
    <script type='text/javascript'>
    <!--
        function insertVariableCode(variable){
		    tinyMCE.execCommand('mceInsertContent', false, variable);
        }
        
    -->
    </script>
    ";
    
    if (!$DisplayingForm){
        $admin_head_extras.= "
		<script type='text/javascript' src='".CMS_INSTALL_URL."lib/packages/Common/prototype.js'></script>
        <script type='text/javascript'>
        <!--
        function performAlreadyPrintedAction(){
            var letter_id = '".$Letter->getParameter('LetterID')."';
            document.getElementById('AlreadyPrintedGoButton').value = 'Working...';
            new Ajax.Request('$AjaxURL',
              {
                method:'get',
                parameters: {cmd: 'ajax', req: 'already_printed', letter_id: letter_id, action: document.getElementById('AlreadyPrintedAction').value".($_GET['id'] != "" ? ", id: '".$_GET['id']."'" : "")."},
                onSuccess: function(transport){
                    var e = document.getElementById('AlreadyPrintedActionMessage'); 
                    if (transport.responseText == 'success'){

                        switch (document.getElementById('AlreadyPrintedAction').value){
                        case 'add':
                            e.innerHTML = 'Successfully added to set of \"Already Printed\"';
                            break;
                        case 'replace':
                            e.innerHTML = 'Successfully replaced set of \"Already Printed\"';
                            break;
                        case 'reset':
                            e.innerHTML = 'Successfully reset the \"Already Printed\" set';
                            break;
                        }
                    }
                    else{
                        e.innerHTML = 'There was a problem performing the requested action';
                        alert(transport.responseText);
                    }
                    document.getElementById('AlreadyPrintedGoButton').value = 'Go';
                },
                onFailure: function(){ alert('Unable to retrieve available variables...') }
              });
        }   
        
        -->
        </script>
        ";
    }
    else{
        $start_functions.="updateLetterVariablesAjax();";
        $start_functions.="updatePrintableDetails();";
        $start_functions.="trigger();";
    }
    $smarty->assign('admin_start_function',$start_functions);
    $smarty->assign('admin_head_extras',$admin_head_extras);
?>
<?php 
	if (!$DisplayingForm){
	    echo "<div class='noprint' style='text-align:center'>\n";
	}
	if (!$DisplayingForm){
	    echo "<p><b>Note:</b> When you print, the above banner will not print.  <a href='$SelfURL'>Return to Template</a></p>\n";
	    echo "<p><b>To Print:</b> Select print from your browser's File menu\n";
	    echo "</p>\n";
	    $Options = array();
	    $Options['add']     = 'Add '.($_GET['id'] == "" ? "these" : "this").' to the set of "Already Printed"';
	    $Options['replace'] = 'Replace the set of "Already Printed" with '.($_GET['id'] == "" ? "these" : "this").'';
	    $Options['reset']   = 'Reset the "Already Printed" set';
	    echo "<p>".HTML_Form::returnSelect('AlreadyPrintedAction', $Options, 'add');
	    echo " <input type='button' id='AlreadyPrintedGoButton' name='Go' value='Go' onclick=\"performAlreadyPrintedAction()\">";
	    echo "\n<br>\n<span id='AlreadyPrintedActionMessage'>&nbsp;</span>";
	    echo "</p>\n";
	    echo "</div>\n";
		echo "<div id='LetterPreview'>\n";
	}    
	echo $Content;        
	if (!$DisplayingForm){
		echo "\n</div> <!-- / LetterPreview -->";
	}
?>