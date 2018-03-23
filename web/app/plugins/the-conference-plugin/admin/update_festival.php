<?php
/************************************************************
*
*
*************************************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'update_festival');
	$manage = $Bootstrap->makeAdminURL($Package,'manage');
	$edit = $Bootstrap->makeAdminURL($Package,'update_festival');
	$edit_schedule = $Bootstrap->makeAdminURL($Package,'edit_schedule');
	$update_artist = $Bootstrap->makeAdminURL($Package,'update_artist');
	
	$ImportExport = $Bootstrap->usePackage('ImportExport');
	$import = $Bootstrap->makeAdminURL($ImportExport,'import').'&p=FestivalApp&sub='.pluralize('Artist');
	$export = $Bootstrap->makeAdminURL($ImportExport,'export').'&p=FestivalApp&sub='.pluralize('Artist');

	define ('HTML_FORM_TH_ATTR',"valign=top align=left width='15%'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');
        
	define('DEFAULT_DATE',"yyyy-mm-dd");
	
        $FestivalContainer = new FestivalContainer();
        $FestivalArtistContainer = new FestivalArtistContainer();
        
        if ($_GET[YEAR_PARM]){
                $Festival = $FestivalContainer->getFestival(urldecode($_GET[YEAR_PARM]));
        }
        elseif ($_POST['Year'] != ""){
                $Festival = $FestivalContainer->getFestival($_POST['Year']);
        }
        if (!$Festival){
                $Festival = new Festival(urldecode($_GET[YEAR_PARM]));
        }
		else{
			$export.= '&FestivalYear='.urlencode($Festival->getParameter('FestivalYear'));
			$edit_schedule.= '&'.YEAR_PARM.'='.urlencode($Festival->getParameter('FestivalYear'));
		}

        
	
	/******************************************************************
	*  Field Level Validation
	*  Only performed if they've submitted the form
	******************************************************************/
	if (isset($_POST['form_submitted']) and $_POST['form_submitted'] == 'true'){
	
		// They hit the cancel button, return to the Manage Pages page
		if ($_POST['cancel']){
			header("Location:".$manage);
			exit();
		}
		
		/******************************************************************
		*  BEGIN EDITS
		*  If an edit fails, it adds an error to the message list.  
		******************************************************************/
        $FestivalArtistIDs = explode('&xi;',$_POST['strFestivalArtists']);
		if ($_POST['FestivalYear'] == ""){
			$MessageList->addMessage("You must specify a year for this ".vocabulary('Festival'),MESSAGE_TYPE_ERROR);
		}
        if (!preg_match("/([0-9]{4})/",$_POST['FestivalYear'],$Matches)){
			$MessageList->addMessage("You must include a valid year within the ".vocabulary('Festival')." Year specified.",MESSAGE_TYPE_ERROR);
        }
		
        $StartDate_ts = strtotime($_POST['FestivalStartDate']);
		if ($_POST['FestivalStartDate'] == "" or !($StartDate_ts > 0)){
			$MessageList->addMessage("You must specify a valid Start Date for the ".vocabulary('Festival'),MESSAGE_TYPE_ERROR);
		}
		
        $EndDate_ts = strtotime($_POST['FestivalEndDate']);
		if ($_POST['FestivalEndDate'] == "" or !($EndDate_ts > 0)){
			$MessageList->addMessage("You must specify a valid End Date for the ".vocabulary('Festival'),MESSAGE_TYPE_ERROR);
		}
        
        
        if ($_POST['FestivalYear'] != $Festival->getSavedParameter('FestivalYear') and $Festival->getSavedParameter('FestivalYear') != ""){
		    if ( ! function_exists( 'get_plugins' ) ){
		        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
	    	$plugin_folder = get_plugins( '/topquark' );
		    $plugin_file = 'topquark.php';
			if ($plugin_folder['topquark.php']['Version'] < '2.0.13'){
				$MessageList->addMessage("In order to change the ".vocabulary('Festival')." year, you must upgrade your Top Quark Architecture plugin.  Please visit the plugins page to upgrade to the latest version.",MESSAGE_TYPE_ERROR);
	            $_POST['FestivalYear'] = $Festival->getSavedParameter('FestivalYear');
			}
        }
        
        // Check and see if a festival exists already, if adding a new festival
        if ($Festival->getSavedParameter('FestivalYear') == ''){
            $_Festival = $FestivalContainer->getFestival($_POST['FestivalYear']);
            if ($_Festival){
                $MessageList->addMessage("There is already a ".vocabulary('Festival')." defined with that year.  You can only define one ".vocabulary('Festival')." per year, or use a different value within the year (i.e. 2011 Spring)",MESSAGE_TYPE_ERROR);
            }
        }
        
		if (!$MessageList->hasMessages()){
			if ($_POST['FestivalEndDate'] < $_POST['FestivalStartDate']){
			        $MessageList->addMessage("The ".vocabulary('Festival')." End Date must be after the ".vocabulary('Festival')." Start Date",MESSAGE_TYPE_ERROR);
            }
            if (preg_match("/([0-9]{4})/",$_POST['FestivalYear'],$Matches)){
                $FestivalYear = $Matches[1];
                if (date("Y",$StartDate_ts) != $FestivalYear){
    			        $MessageList->addMessage("Uhhh...the start date really should be in the same year as the ".vocabulary('Festival'),MESSAGE_TYPE_ERROR);
                }
                if (date("Y",$EndDate_ts) != $FestivalYear){
    			        $MessageList->addMessage("Uhhh...the end date really should be in the same year as the ".vocabulary('Festival'),MESSAGE_TYPE_ERROR);
                }
            }
		}
		
		/******************************************************************
		*  END EDITS
		******************************************************************/
				




						
		/******************************************************************
		*  BEGIN Set Parameters
		******************************************************************/
		$Festival->setParameter('FestivalYear',$_POST['FestivalYear']);
		$Festival->setParameter('FestivalStartDate',$_POST['FestivalStartDate']);
		$Festival->setParameter('FestivalEndDate',$_POST['FestivalEndDate']);
		$Festival->setParameter('FestivalLineupIsPublished',(isset($_POST['FestivalLineupIsPublished']) ? 1 : 0));
		$Festival->setParameter('FestivalDoNotPublishFeeds',(isset($_POST['FestivalDoNotPublishFeeds']) ? 1 : 0));
		/******************************************************************
		*  END Set Parameters
		******************************************************************/
		
		// If there are no messages/errors, then go ahead and do the update (or add)
		// Note: if they were deleting a version, then there will be a message, so
		// this section won't get performed
		if (!$MessageList->hasMessages()){
			if ($Festival->getSavedParameter('FestivalYear') == ""){
				// It's a new festival
				$result = $FestivalContainer->addFestival($Festival);
			}
			else{				
				$result = $FestivalContainer->updateFestival($Festival);
			}
			if (PEAR::isError($result)){
				$MessageList->addPearError($result);
			}
                        
            $result = $FestivalArtistContainer->setFestivalLineup($Festival->getParameter('FestivalYear'),$FestivalArtistIDs);
			if (PEAR::isError($result)){
				$MessageList->addPearError($result);
			}
                        
			if (!$MessageList->hasMessages()){
				if ($_POST['action'] == 'alpha_last'){
					$FestivalArtistContainer->sortLineupByLastName($Festival->getParameter('FestivalYear'));
				}
				if ($Package->enable_cache and !$Package->empty_cache_on_publish_only){
					// We're using the cache, so we need to remove the cache pages associate with this term
					$Package->emptyCache();
				}
				$MessageList->addMessage(vocabulary('Festival')." Successfully Updated");
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
        if($Festival->getParameter('FestivalYear') != ""){
	        $DefaultTab = 'ArtistsTab';
        }
        else{
                $DefaultTab = 'FestivalTab';
        }
	
	
	/***********************************************************************
	*
	*	Festival Tab
	*
	***********************************************************************/
	$FestivalTab = new HTML_Tab('Festival',vocabulary('Festival').' Dates');
	$FestivalTab->addText('FestivalYear',vocabulary('Festival').' Year*:',$Festival->getParameter('FestivalYear'));
	$FestivalTab->addPlainText('&nbsp;','* Usually the '.vocabulary('Festival').' year will just be a year.<br>If you need to define two '.pluralize('Festival').' for the same year, you can give one of them a special tag (i.e. "2008 (Forum)")');
	if ($Festival->getParameter('FestivalYear')){
		$feed_base = get_bloginfo('url').'/'.apply_filters('the_conference_plugin_feeds_basepath','the-conference-plugin').'/'.sanitize_title($Festival->getParameter('FestivalYear')).'/';
		$artist_feed = $feed_base.sanitize_title(pluralize('Artist'));
		$show_feed = $feed_base.sanitize_title(pluralize('Show'));		
		$feed_info = "Slug: ".sanitize_title($Festival->getParameter('FestivalYear')).'<br/>'.vocabulary('Artist').' Feed: <a href="'.$artist_feed.'" target="_blank">'.$artist_feed.'</a>'.'<br/>'.vocabulary('Show').' Feed: <a href="'.$show_feed.'" target="_blank">'.$show_feed.'</a><br/>';
		$feed_info.= HTML_Form::returnCheckbox('FestivalDoNotPublishFeeds', $Festival->getParameter('FestivalDoNotPublishFeeds'))." Do not publish feeds";
		$FestivalTab->addPlainText('Feeds',$feed_info);
	}
	// If an edit failed, we want to redisplay what the user entered
	if (isset($_POST['form_submitted']) and $_POST['form_submitted'] and $StartDate_ts < 0){
		$StartDate = $_POST['FestivalStartDate'];
	}
	elseif($Festival->getParameter('FestivalStartDate') != ""){
		$StartDate = $Festival->getParameter('FestivalStartDate');
	}
        else{
		$StartDate = DEFAULT_DATE;
        }
	if (isset($_POST['form_submitted']) and $_POST['form_submitted'] and $EndDate_ts < 0){
		$EndDate = $_POST['FestivalEndDate'];
	}
	elseif($Festival->getParameter('FestivalEndDate') != ""){
		$EndDate = $Festival->getParameter('FestivalEndDate');
	}
        else{
		$EndDate = DEFAULT_DATE;
        }

	$FestivalTab->addText('FestivalStartDate','Start Date:',$StartDate);
	$FestivalTab->addText('FestivalEndDate','End Date:',$EndDate);
	$FestivalTab->addPlainText('&nbsp;','<a href="'.$edit_schedule.'">edit schedule</a>');

	$admin_head_extras.='
	<script type="text/javascript">
		jQuery(function($){
			$("#FestivalStartDate").datepicker({ dateFormat: "yy-mm-dd" });
			$("#FestivalEndDate").datepicker({ dateFormat: "yy-mm-dd" });
		});
	</script>
	';
	
	/***********************************************************************
	*
	*	Artists Tab
	*
	***********************************************************************/
        $ArtistListBoxScript = "
        <script language='JavaScript' type='text/javascript'> 
        <!-- 
        var NS4 = (navigator.appName == 'Netscape' && parseInt(navigator.appVersion) < 5); 

        function addOption(theSel, theText, theValue) 
        { 
          var newOpt = new Option(theText, theValue); 
          var selLength = theSel.length; 
          if (theSel == document.Update_Form.AllArtists){
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
          if (theSel == document.Update_Form.FestivalArtists){
              var addAt = selLength;
              if (theSel.selectedIndex != -1){ 
                addAt = theSel.selectedIndex;
              }
              for (var x = addAt; x < selLength; x++){
                    var tmpOpt = new Option();
                    tmpOpt.text = theSel.options[x].text;
                    tmpOpt.value = theSel.options[x].value;
                    tmpOpt.selected = theSel.options[x].selected;
                    theSel.options[x] = newOpt
                    newOpt = tmpOpt;
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
			saveFestivalArtists();
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
                        openEditArtistWindow(document.Update_Form.FestivalArtists.name,'');
                }
                function openEditArtistWindow(selectBoxName,artistID){
                        var url = '".$update_artist."&sel='+selectBoxName;
                        if (selectBoxName == 'FestivalArtists'){
                            url += '&".YEAR_PARM."=".urlencode($Festival->getParameter('FestivalYear'))."';
                        }
                        if (artistID != ''){
                            url += '&id='+artistID;
                        }
                    var eWindow = window.open(url, 'EditArtist',
                     'scrollbars=yes,menubar=yes,resizable=yes,toolbar=no,width=800,height=600');
                }
                function updateArtist(theSel,theText,theValue){
                        var theOtherSel;
                        var i;
                        
                        if (theSel.name == 'AllArtists'){
                                theOtherSel = document.Update_Form.FestivalArtists;
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
                function saveFestivalArtists(){
                        var str = '';
                        var sep = '';
                        for (var i=0, len=document.Update_Form.FestivalArtists.length;i<len;i++){
                                str+= sep+document.Update_Form.FestivalArtists.options[i].value;
                                sep = '&xi;';
                        }
                        document.Update_Form.strFestivalArtists.value = str;
                }
        //--> 
        </script> 
        ";
	$admin_head_extras.=$ArtistEdittingScript;


	$ArtistsTab = new HTML_Tab('Artists',pluralize('Artist'));
        $ArtistContainer = new ArtistContainer();
        $FestivalArtistContainer = new FestivalArtistContainer();
        $AllArtists = $ArtistContainer->getAllArtists();
        $all_artist_ids = array();
        if (is_array($AllArtists)){
                foreach ($AllArtists as $Artist){
                        $all_artist_ids[] = $Artist->getParameter('ArtistID');
                }
        }
        else{
                $AllArtists = array();
        }
        if ($Festival->getParameter('FestivalYear') != ""){
                $FestivalArtists = $FestivalArtistContainer->getAllArtists($Festival->getParameter('FestivalYear'));
                if (is_a($FestivalArtists,'FestivalArtist')){
                    $FestivalArtists = array($FestivalArtists->getParameter('ArtistID') => $FestivalArtists);
                }
        }
        $festival_artist_ids = array();
        if (is_array($FestivalArtists)){
                foreach ($FestivalArtists as $Artist){
                        $festival_artist_ids[] = $Artist->getParameter('ArtistID');
                }
        }
        else{
                $FestivalArtists = array();
        }
        
        $ArtistSelection = "<table width='100%' align=center><tr>\n";
        $ArtistSelection.= "<td width=44% valign=top>".pluralize('Artist')." in the Database<br><select name='AllArtists' id='AllArtists' multiple='multiple' size=10 style='width: 100%'>\n";
        foreach ($AllArtists as $Artist){
                if (!in_array($Artist->getParameter('ArtistID'),$festival_artist_ids)){
                        $ArtistSelection.= "<option value='".$Artist->getParameter('ArtistID')."'>".$Artist->getHTMLFormattedParameter('ArtistFullName')."</option>\n";
                }
        }
        $ArtistSelection.= "</select>\n";
        $ArtistSelection.= "<br>Filter: <input type='text' name='nameFilter'/>\n";
        $ArtistSelection.= "<input type='button' name='Edit1' value='Edit Selected' onClick='editSelectedArtist(document.Update_Form.AllArtists);'/>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "<td width=6% valign=middle align=center>\n";
        $ArtistSelection.= "<input type='button' name='AddArtist' value='--&gt;' onclick='moveOptions(document.Update_Form.AllArtists,document.Update_Form.FestivalArtists);'><br><br>";
        $ArtistSelection.= "<input type='button' name='RemoveArtist' value='&lt;--' onclick='moveOptions(document.Update_Form.FestivalArtists,document.Update_Form.AllArtists);'>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "<td width=44% valign=top>".pluralize('Artist')." at the ".$Festival->getParameter('FestivalYear')." ".vocabulary('Festival')."<br><select name='FestivalArtists' id='FestivalArtists' multiple='multiple' size=10 style='width: 100%'>\n";
        foreach ($FestivalArtists as $Artist){
                $ArtistSelection.= "<option value='".$Artist->getParameter('ArtistID')."'>".$Artist->getHTMLFormattedParameter('ArtistFullName')."</option>\n";
        }
        $ArtistSelection.= "</select>\n";
        $ArtistSelection.= "<br><input type='button' name='Edit2' value='Edit Selected' onClick='editSelectedArtist(document.Update_Form.FestivalArtists);'/>\n";
        $ArtistSelection.= "<input type='button' name='AddArtist' value='Quick Add' onClick='quickAddArtist()'/>\n";
        $ArtistSelection.= "</td>\n";

        $ArtistSelection.= "<td width=6% valign=middle align=center>\n";
        $ArtistSelection.= "<input type='button' name='ArtistUp' value='&Lambda;' onclick='moveOptionsUp(document.Update_Form.FestivalArtists);'><br><br>";
        $ArtistSelection.= "<input type='button' name='ArtistDown' value='V' onclick='moveOptionsDown(document.Update_Form.FestivalArtists);'><br><br>";
        $ArtistSelection.= "<input type='button' name='ArtistAlpha' value='A-Z' onclick='alphabetizeArtists(\"FestivalArtists\");'>\n";
        $ArtistSelection.= "<input type='button' name='ArtistAlphaLastName' value='A-Z (last)' onclick='alphabetizeArtistsLastName();'>\n";
        $ArtistSelection.= "</td>\n";
        
        $ArtistSelection.= "</tr></table>\n";
        
        
	$ArtistsTab->addPlainText(pluralize('Artist').':<br/><a href="'.$import.'">import</a><br/><a href="'.$export.'">export</a>',$ArtistSelection);
    $PublishLineup = HTML_Form::returnCheckBox('FestivalLineupIsPublished',($Festival->getParameter('FestivalLineupIsPublished') == 1 ? true : false));
    $PublishLineup.= " Lineup is published";
    $ArtistsTab->addPlainText('&nbsp;',$PublishLineup);
	
	// We've defined the tabs.  Now let's add them
	$form->addTab($FestivalTab);
	$form->addTab($ArtistsTab);

	/***********************************************************************
	*
	*	Shortcodes Tab
	*
	***********************************************************************/
	if ($Festival->getSavedParameter('FestivalYear') != ""){
		// only do this for "not new" festivals
		$ShortcodeTab = new HTML_Tab('Shortcodes','Shortcodes');
		$shortcodes = array();
		$shortcodes[] = array(
			'shortcode' => 'the_'.sanitize_title(vocabulary('Festival')).'_lineup',
			'sample' => '[the_'.sanitize_title(vocabulary('Festival')).'_lineup]',
			'purpose' => pluralize('Artist'),
			'attributes' => array(
				'year' => array(
					'description' => 'The '.vocabulary('Festival').' year, as defined on the '.vocabulary('Festival').' Dates tab',
					'values' => $Festival->getParameter('FestivalYear')
				),
				'style' => array(
					'description' => 'The style for the listing.',
					'values' => array(
						'list' => 'List of Names',
						'expanded' => 'Thumbnails &amp; Descriptions',
						'floated' => 'Thumbnails &amp; Names in a Grid'
					)
				),
				'order' => array(
					'description' => 'The order for the listing',
					'values' => array(
						'default' => 'The order as defined on the '.pluralize('Artist').' tab',
						'first_name' => 'Alphabetical by first name',
						'last_name' => 'Alphabetical by last name',
						'random' => 'Random'
					)
				)
			),
			'examples' => array(
				'[the_'.sanitize_title(vocabulary('Festival')).'_lineup year="'.$Festival->getParameter('FestivalYear').'" style=expanded]',
				'[the_'.sanitize_title(vocabulary('Festival')).'_lineup year="'.$Festival->getParameter('FestivalYear').'" style=floated order=random]'
			)
		);
		$ScheduleContainer = new ScheduleContainer();
		$Schedules = $ScheduleContainer->getAllSchedules($Festival->getParameter('FestivalYear'));
		if (is_array($Schedules) and count($Schedules)){
			$sched_array = array();
			foreach ($Schedules as $Schedule){
				$sched_array[$Schedule->getParameter('ScheduleUID')] = $Schedule->getParameter('ScheduleName');
			}
			$shortcodes[] = array(
				'shortcode' => 'the_'.sanitize_title(vocabulary('Festival')).'_schedule',
				'sample' => '[the_'.sanitize_title(vocabulary('Festival')).'_schedule]',
				'purpose' => 'Schedule',
				'attributes' => array(
					'year' => array(
						'description' => 'The '.vocabulary('Festival').' year, as defined on the '.vocabulary('Festival').' Dates tab',
						'values' => $Festival->getParameter('FestivalYear')
					),
					'style' => array(
						'description' => 'The style for the schedule.',
						'values' => array(
							'default' => 'Shows the schedule, one grid for each day of your '.vocabulary('Festival'),
							'agenda' => 'A list, with start/end times on the left, description on the right',
							'details' => 'Lists the show details, as a list, not as a grid',
							'collapse_days' => 'Shows the schedule, one grid for each '.vocabulary('Stage'),
							'collapse_all' => 'Puts the entire schedule into a single grid, with days across the top.  Please note, this is a little buggy for complicated schedules. We\'re working on it.'
						)
					),
					'include_times' => array(
						'description' => 'Whether or not to include the times within the schedule',
						'values' => array(
							'true' => 'True',
							'false' => 'False'
						)
					),
					'schedule' => array(
						'description' => 'The ID number of a specific schedule.  Useful for '.pluralize('Festival').' with more than one schedules.  Find the ID on the Settings page for any of your schedules.',
						'values' => $sched_array
					)
				),
				'examples' => array(
					'[the_'.sanitize_title(vocabulary('Festival')).'_schedule year="'.$Festival->getParameter('FestivalYear').'"]',
					'[the_'.sanitize_title(vocabulary('Festival')).'_schedule year="'.$Festival->getParameter('FestivalYear').'" style=collapse_days]',
					'[the_'.sanitize_title(vocabulary('Festival')).'_schedule year="'.$Festival->getParameter('FestivalYear').'" style=collapse_all include_times=false]'
				)
			);
		}
		
		$shortcodes = apply_filters('the_conference_plugin_shortcodes',$shortcodes,$Festival);
		
		$ShortcodeText = '<p>The following shortcodes are available for use in your pages or posts.  Please note, you should only use one of these shortcodes per page/post.</p>';
		foreach ($shortcodes as $s){
			$ShortcodeText.= '<div class="shortcode-details">';
			$ShortcodeText.= '<h3>'.$s['purpose'].'</h3>';
			$ShortcodeText.= $s['sample'];
			$ShortcodeText.= '<h4>Parameters</h4>'.'<ul>';
			foreach ($s['attributes'] as $key => $att){
				$ShortcodeText.= "<li><strong>{$key}</strong> - {$att['description']}";
				if (is_array($att['values'])){
					$ShortcodeText.= '<br/>Possible Values:<ul style="margin-left:50px">';
					foreach ($att['values'] as $value => $use){
						$ShortcodeText.= "<li><strong>$value</strong> - $use</li>";
					}
					$ShortcodeText.= '</ul>';
				}
				$ShortcodeText.= "</li>";
			}
			$ShortcodeText.= '<h4>Examples</h4>'.'<ul>';
			foreach ($s['examples'] as $example){
				$ShortcodeText.= "<li>$example (<a href=\"".get_bloginfo('url')."/?conf_plugin_preview=true&amp;sc=".urlencode($example)."\" target=\"conf_plugin_preview\">preview</a>)</li>";
			}
			$ShortcodeText.= '<h4>Build Your Own</h4>';
			$ShortcodeText.= '<div class="shortcode-preview-form">';
			$ShortcodeText.= '<input type="hidden" name="shortcode" value="'.$s['shortcode'].'">';
			$ShortcodeText.= '<input type="hidden" name="preview" value="">';
			$ShortcodeText.= '['.$s['shortcode'];
			foreach ($s['attributes'] as $key => $att){
				if (is_array($att['values'])){
					$ShortcodeText.= " $key=";
					$ShortcodeText.= '<select name="'.$key.'">';
					$ShortcodeText.= '<option value=""></option>';
					foreach ($att['values'] as $value => $use){
						$ShortcodeText.= '<option value="'.esc_attr($value).'">'."$value".'</option>';
					}
					$ShortcodeText.= '</select>';
				}
				else{
					$ShortcodeText.= '<input type="hidden" name="'.$key.'" value="'.esc_attr($att['values']).'" class="simple-attr">';
					$ShortcodeText.= " $key=\"{$att['values']}\"";
				}
			}
			$ShortcodeText.= ']';
			$ShortcodeText.= ' (<a href="" class="shortcode-preview-link" target="conf_plugin_preview">preview</a>)';
			$ShortcodeText.= '</div>';
			$ShortcodeText.= '</div>'."\n";
		}
		$ShortcodeText.= '
		<script type="text/javascript">
		jQuery(function(){
			jQuery("#group_Shortcodes").find("select").change(function(){
				updatePreview(jQuery(this).parents("div.shortcode-preview-form"));
			});
			jQuery("#group_Shortcodes").find("div.shortcode-preview-form").each(function(){
				updatePreview(jQuery(this));
			});
		});
		updatePreview = function(form){
			var preview;
			preview = "["+form.find("input[name=shortcode]").val();
			form.find(".simple-attr").each(function(){
				preview+= " "+jQuery(this).attr("name")+"=\""+jQuery(this).val()+"\"";
			})
			form.find("select").each(function(){
				if (jQuery(this).val() != ""){
					preview+= " "+jQuery(this).attr("name")+"=\""+jQuery(this).val()+"\"";
				}
			});
			preview+="]";
			form.find("input[name=preview]").val(preview);
			form.find("a.shortcode-preview-link").attr("href","'.get_bloginfo('url').'/?conf_plugin_preview=true&sc="+encodeURIComponent(preview));
		}
		</script>
		';
		$ShortcodeTab->addPlainText('&nbsp;',$ShortcodeText);
		$form->addTab($ShortcodeTab);
	}

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
	$form->addSubmit('save','Save Changes','onClick="saveFestivalArtists()";');
	$form->addSubmit('cancel','Cancel');
	
	// Some hidden fields to help us out 
	$form->addHidden('form_submitted','true');
	$form->addHidden('action','');
	$form->addHidden('strFestivalArtists','nothing');
	$form->addHidden('Year',$Festival->getSavedParameter('FestivalYear'));
	
	// Finally, we set the Smarty variables as needed.
	$start_functions = array();
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
</script> 