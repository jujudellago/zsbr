<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'printables');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'delete_printable');
	$manage = $Bootstrap->makeAdminURL($Package,'printables');

	define (HTML_FORM_TH_ATTR,"valign=top align=left width='3%'");
	define (HTML_FORM_TD_ATTR,"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');

	$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
	$LetterContainer = new LetterContainer();
	if ($id){
		$Letter = $LetterContainer->getLetter($id);
	}
	else{
		header("Location:".$manage);
		exit();
	}

	if ($_POST['form_submitted'] == 'true'){
		if ($_POST['confirm']){
			$result = $LetterContainer->deleteLetter($id);
		}
		if (PEAR::isError($result)){
			$MessageList->addPearError($result);
		}
		else{
			header("Location:".$manage);
			exit();
		}
	}
	$MessageList->addMessage("Do you really want to delete the printable ".$Letter->getParameter('LetterName')." (This can't be undone)?");
	
	// Declaration of the Form	
	$form = new HTML_Form($Bootstrap->getAdminURL(),'post','Delete_Form');
	if ($MessageList->hasMessages()){
		$form->addPlainText('',$MessageList->toSimpleString());
	}
	$form->addSubmit('confirm','Yes, delete it');
	$form->addSubmit('cancel','No, don\'t!');
	$form->addHidden('form_submitted','true');
	$form->addHidden('id',$Letter->getParameter('LetterID'));
	
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	
?>
<?php   $smarty->display('admin_form.tpl'); ?>
