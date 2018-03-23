<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'reset');
	$manage = $Bootstrap->makeAdminURL($Package,'manage');

	define (HTML_FORM_TH_ATTR,"valign=top align=left width='20%'");
	define (HTML_FORM_TD_ATTR,"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');

	$id = $_GET[YEAR_PARM] ? urldecode($_GET[YEAR_PARM]) : $_POST['id'];
	$FestivalContainer = new FestivalContainer();
	if ($id){
		$Festival = $FestivalContainer->getFestival($id);
	}
	else{
		header("Location:".$manage);
		exit();
	}

	if ($_POST['form_submitted'] == 'true'){
		if ($_POST['confirm']){
			$result = $FestivalContainer->performReset($id,$_POST['lineup'],$_POST['schedules'],$_POST['orphans']);
		}
		if (PEAR::isError($result)){
			$MessageList->addPearError($result);
		}
		else{
			if ($Package->enable_cache and !$Package->empty_cache_on_publish_only){
				// We're using the cache, so we need to remove the cache pages associate with this term
				$Package->emptyCache();
			}
			header("Location:".$manage);
			exit();
		}
	}
	$MessageList->addMessage("Resetting a ".vocabulary('Festival')." allows you to remove all or part of the data associated with a particular ".vocabulary('Festival').".  Additionally, it can search the database for ".pluralize('Artist')." that are not connected to any ".vocabulary('Festival')." and delete them.  These actions are useful when you are importing data and wish to start with a clean slate.  <br/><br/>So, choose your options below and hit Continue");
	
	// Declaration of the Form	
	$form = new HTML_Form($Bootstrap->getAdminURL(),'post','Reset_Form');
	$form->addCheckbox('lineup','Reset Lineup:',true);
	$form->addCheckbox('schedules','Reset Schedules (all '.pluralize('Show').' will be deleted)',true);
	$form->addCheckbox('orphans','Remove '.pluralize('Artist').' not associated with any '.vocabulary('Festival'),true);
	$form->addSubmit('confirm','Continue');
	$form->addSubmit('cancel','Cancel');
	$form->addHidden('form_submitted','true');
	$form->addHidden('id',$Festival->getParameter('FestivalYear'));
	
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	
	if ($MessageList->hasMessages()){
		echo $MessageList->toSimpleString();
	}
?>
<?php   $smarty->display('admin_form.tpl'); ?>
