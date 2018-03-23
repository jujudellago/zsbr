<?php
	/*******************************************
	* delete_gallery.php
	*
	********************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'delete');
    $returnURL = $Bootstrap->makeAdminURL($Package,'manage');
	$delete = $Bootstrap->makeAdminURL($Package,'delete');
	
	define ('HTML_FORM_TH_ATTR',"valign=top align=left width='1%' style='font-size: 0.8em'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
	include_once(dirname(__FILE__)."/../../../TabbedForm.php");

	$_id = (isset($_GET['id'])  ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : ''));
	$_sid = (isset($_GET['sid'])  ? $_GET['sid'] : (isset($_POST['sid']) ? $_POST['sid'] : ''));
	if ($_id != ""){
	    $WorkingWith = "Gallery";
	}
	elseif ($_sid != ""){
	    $WorkingWith = "ImageSet";
	}
	if ($WorkingWith == ""){
		header("Location:".$returnURL);
		exit();
	}
     
    switch ($WorkingWith){
    case "Gallery":
        $Container = new GalleryContainer();
        $Gallery = $Container->getGallery($_id);
        break;
    case "ImageSet":
        $Container = new ImageSetContainer();
        $Gallery = $Container->getImageSet($_sid);
        break;
    }
	$GalleryImageContainer = new GalleryImageContainer();
        
	if (!$Gallery){
		header("Location:".$returnURL);
		exit();
	}
	if (isset($_POST['form_submitted']) and $_POST['form_submitted'] == 'true'){
		if ($_POST['confirm']){
		    switch ($WorkingWith){
		    case "Gallery":
			    $result = $Container->deleteGallery($_id);
			    break;
			case "ImageSet":
			    $result = $Container->deleteImageSet($_sid);
			    break;
			}
		}
		if (PEAR::isError($result)){
			$MessageList->addPearError($result);
		}
		else{
			header("Location:".$returnURL);
			exit();
		}
	}
	
    switch ($WorkingWith){
    case "Gallery":
	    $MessageList->addMessage("Do you really want to delete this gallery and all images in it? (<b>This can't be undone</b>)");
	    break;
	case "ImageSet":
	    $MessageList->addMessage("Do you really want to delete this image set? (<b>This can't be undone</b>)");
	    break;
	}
	// Declaration of the Form	
	$form = new HTML_Form($delete,'post','Delete_Form');
	if ($MessageList->hasMessages()){
		$form->addPlainText('',$MessageList->toSimpleString(),'','align=center');
	}
        $Thumb = $Gallery->getPrimaryThumb();
        if ($Thumb != null){
            $form->addPlainText('',"<img src='".$Thumb->getGalleryDirectory().$Thumb->getParameter('GalleryImageThumb')."'>",'','align=center');
        }
       
        $buttons = HTML_Form::returnSubmit('Yes, delete it','confirm');
        $buttons.= " ".HTML_Form::returnSubmit('No, don\'t!','cancel');
        $form->addPlainText('',$buttons,'','align=center');
	$form->addBlank(1);
	$form->addHidden('form_submitted','true');
    switch ($WorkingWith){
    case "Gallery":
	    $form->addHidden('id',$Gallery->getParameter('GalleryID'));
	    break;
	case "ImageSet":
	    $form->addHidden('sid',$Gallery->getParameter('ImageSetID'));
	    break;
	}
	$form->addHidden('return',$returnURL);
	
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	
?>
<?php   $smarty->display('admin_form.tpl'); ?>
