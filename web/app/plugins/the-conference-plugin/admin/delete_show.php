<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$manage = $Bootstrap->makeAdminURL($Package,'edit_schedule');
	$delete = $Bootstrap->makeAdminURL($Package,'delete_show');
	$edit = $Bootstrap->makeAdminURL($Package,'update_show');
	$update_artist = $Bootstrap->makeAdminURL($Package,'update_artist');

	define (HTML_FORM_TH_ATTR,"valign=top align=left width='3%'");
	define (HTML_FORM_TD_ATTR,"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');

	$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
	$year = $_GET[YEAR_PARM] ? $_GET[YEAR_PARM] : $_POST[YEAR_PARM];
	
	$ManageYearSchedules = $manage."&".YEAR_PARM."=".urlencode($year);
	$Bootstrap->addURLToAdminBreadcrumb($ManageYearSchedules,$Package->admin_pages['edit_schedule']['title']);
	$UpdateShow = $edit."&id=$id";
	$Bootstrap->addURLToAdminBreadcrumb($UpdateShow,$Package->admin_pages['update_show']['title']);
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'delete_show');
	
	$ShowContainer = new ShowContainer();
	if ($id){
		$Show = $ShowContainer->getShow($id);
	}
	if (!$id or !is_a($Show,'Show')){
		header("Location:".$manage);
		exit();
	}
	else{
	    $ManageYearSchedules.= "&tab=".urlencode($Show->getParameter('ShowScheduleUID'))."&from=show";
	}

	if ($_POST['form_submitted'] == 'true'){
		if ($_POST['confirm']){
			$result = $ShowContainer->deleteShow($id);
			$returnURL = $ManageYearSchedules;
		}
		else{
		    $returnURL = $UpdateShow;
		}
		if (PEAR::isError($result)){
			$MessageList->addPearError($result);
		}
		else{
			if ($Package->enable_cache and !$Package->empty_cache_on_publish_only){
				// We're using the cache, so we need to remove the cache pages associate with this term
				$Package->emptyCache();
			}
			header("Location:".$returnURL);
			exit();
		}
	}
        $Listing = "<table width=25% cellpadding=5 align=center><tr><td align=center>";
        $Listing.= "<b>".$Show->getParameter('ShowTitle')."</b><br>";
		if ($Show->getParameter('ShowPrettyDay') != ""){
			$Listing.= $Show->getParameter('ShowPrettyDay');
		}
		if ($Show->getParameter('ShowPrettyStage') != ""){
			$Listing.= " on ".$Show->getParameter('ShowPrettyStage');
		}
		if ($Show->getParameter('ShowPrettyStartTime') != ""){
			$Listing.= " at ".$Show->getParameter('ShowPrettyStartTime');
		}
        if (count($Show->getParameter('ShowArtists'))){
                $Listing.= " with ".$Show->getArtistNames();
        }
                
        $Listing.= "</td></tr></table>";
	$MessageList->addMessage("Do you really want to delete the following ".vocabulary('Show')." (This can't be undone)?<br>$Listing");
	
	// Declaration of the Form	
	$form = new HTML_Form($Bootstrap->getAdminURL(),'post','Delete_Form');
	if ($MessageList->hasMessages()){
		$form->addPlainText('',$MessageList->toSimpleString());
	}
	$form->addSubmit('confirm','Yes, delete it');
	$form->addSubmit('cancel','No, don\'t!');
	$form->addHidden('form_submitted','true');
    $form->addHidden(YEAR_PARM,$year);
	$form->addHidden('id',$Show->getParameter('ShowID'));
	
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	
?>
<?php   $smarty->display('admin_form.tpl'); ?>
