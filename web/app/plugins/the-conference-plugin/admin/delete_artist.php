<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage_artists');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'delete_artist');
	$manage = $Bootstrap->makeAdminURL($Package,'manage_artists');

	define ('HTML_FORM_TH_ATTR',"valign=top align=left width='3%'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');

	$id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
	$ArtistContainer = new ArtistContainer();
	if ($id){
		$Artist = $ArtistContainer->getArtist($id);
	}
	else{
		header("Location:".$manage);
		exit();
	}

	if (isset($_POST['form_submitted']) and $_POST['form_submitted'] == 'true'){
		if ($_POST['confirm']){
			$result = $ArtistContainer->deleteArtist($id);
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
	$MessageList->addMessage("Do you really want to delete ".$Artist->getParameter('ArtistFullName')." (This can't be undone)?");
	
	// Declaration of the Form	
	$form = new HTML_Form($Bootstrap->getAdminURL(),'post','Delete_Form');
	if ($MessageList->hasMessages()){
		$form->addPlainText('',$MessageList->toSimpleString());
	}
	$form->addSubmit('confirm','Yes, delete it');
	$form->addSubmit('cancel','No, don\'t!');
	$form->addHidden('form_submitted','true');
	$form->addHidden('id',$Artist->getParameter('ArtistID'));
	
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	
?>
<?php   $smarty->display('admin_form.tpl'); ?>
