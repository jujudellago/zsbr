<?php
	/*******************************************
	* export.php
	*
	********************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'export');
    $returnURL = $Bootstrap->makeAdminURL($Package,'manage');
    $export = $Bootstrap->makeAdminURL($Package,'export');
	
	define ('HTML_FORM_TH_ATTR',"valign=top align=center width='1%' style='font-size: 0.8em'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
	include_once(CMS_INSTALL_DOC_BASE.'lib/TabbedForm.php');
    include_once(dirname(__FILE__).'/../dZip.inc.php');
    include_once(dirname(__FILE__).'/../dUnzip2.inc.php');

	$_id = $_GET['id'] ? $_GET['id'] : $_POST['id'];
	$_sid = $_GET['sid'] ? $_GET['sid'] : $_POST['sid'];
	$dir = DOC_BASE.CMS_ASSETS_DIRECTORY.'published/';
	if (!is_dir($dir) and !mkdir($dir)){
		echo 'Unable to access the published directory.  Make sure '.$dir.' exists with full write rights.';
		return;
	}
	
	if ($_GET['del'] != ''){
	    $filename = urldecode($_GET['del']);
	    if (file_exists($dir.$filename) and $_GET['cs'] == substr(md5($filename),3,4)){
	        unlink($dir.$filename);
	    }
	}
	
	if ($_id != ""){
	    $WorkingWith = "Gallery";
	    $export.= "&id=".$_id;
	}
	elseif ($_sid != ""){
	    $WorkingWith = "ImageSet";
	    $export.= "&sid=".$_sid;
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
	$ImageSetImageContainer = new ImageSetImageContainer();
        
	if (!$Gallery){
		header("Location:".$returnURL);
		exit();
	}
	if ($_POST['form_submitted'] == 'true'){
		// They hit the cancel button
		if (isset($_POST['cancel'])){
			header("Location:".$returnURL);
			exit();
		}
                
        $zip_name = preg_replace("/[^A-Za-z0-9]/","_",$_POST['Name']).".zip";
	    if (!file_exists($dir)){
	        $MessageList->addMessage('Unable to access the published directory.  Make sure '.$dir.' exists with full write rights.',MESSAGE_TYPE_ERROR);
	    }
	    else{
	        if ($WorkingWith == 'Gallery'){
    	        if ($_POST['OnlyFavourites']){
    	            $GalleryImages = $GalleryImageContainer->getAllMarkedGalleryImages($Gallery->getParameter('GalleryID'));
    	        }
    	        else{
    	            $GalleryImages = $GalleryImageContainer->getAllGalleryImages($Gallery->getParameter('GalleryID'));
    	        }
    	    }
    	    else{
    	        $GalleryImages = $ImageSetImageContainer->getAllImageSetImages($Gallery->getParameter('ImageSetID'));
    	    }
    	    
    	    if (is_array($GalleryImages)){
    	        
    	        $zip = new dZip($dir.$zip_name);
    	        $ImagesCopied = 0;
    	        foreach ($GalleryImages as $GalleryImage){
	                if ($_POST['Randomize']){
	                    $FileName = md5($GalleryImage->getParameter('GalleryImageOriginal')).'.jpg';
	                }
	                else{
	                    $FileName = $GalleryImage->getParameter('GalleryImageOriginal');
	                }

	                if ($WorkingWith == 'ImageSet'){
	                    $GalleryDirectoryObject = &$GalleryImage;
	                }
	                else{    	                    
	                    $GalleryDirectoryObject = &$Gallery;
	                }
	                
	                $src = $GalleryDirectoryObject->getGalleryDirectory(IMAGE_DIR_FULL).$GalleryImage->getParameter('GalleryImageOriginal');
	                $zip->addFile($src,$FileName);
                    $ImagesCopied++;
    	        }
    	        
    	        $zip->save();
    	        
    	        $MessageList->addMessage('Successfully created a zip file with '.$ImagesCopied.' images.',MESSAGE_TYPE_MESSAGE);
    	        $MessageList->addMessage('To retrieve it, download <a href="'.BASE_URL.CMS_ASSETS_DIRECTORY.'published/'.$zip_name.'">this file</a>.',MESSAGE_TYPE_MESSAGE);
    	    }
    	    else{
    	        $MessageList->addMessage('Could not find any images',MESSAGE_TYPE_MESSAGE);
    	    }
	    }
	}
	
	// Declaration of the Form	
	$form = new HTML_Form($_SESSION['PHP_SELF'],'post','Delete_Form');

	if (is_a($Gallery,'Gallery') or is_a($Gallery,'ImageSet')){
 		$Thumb = $Gallery->getPrimaryThumb();
        if (is_a($Gallery,'ImageSet')){
            $GalleryDirectoryObject = &$Thumb;
        }
        else{
            $GalleryDirectoryObject = &$Gallery;
        }
	    $WhatItIsCode = "<img src='".$GalleryDirectoryObject->getGalleryDirectory().$Thumb->getParameter('GalleryImageThumb')."'>";
	}
	if ($MessageList->hasMessages()){
		$form->addPlainText('',$MessageList->toSimpleString(),'','align=center');
	}
	$form->addPlainText('Exporting',$WhatItIsCode);
    if (!is_a($Gallery,'ImageSet')){
	    $form->addCheckbox('OnlyFavourites','Only Favourites',$_POST['OnlyFavourites']);
	}
	$form->addCheckbox('Randomize','Randomize?',$_POST['Randomize']);
	$form->addPlainText('&nbsp;',"Randomizing will rename photos with a random name so that you can slideshow a gallery and they'll appear in random order.");
	$form->addText('Name','Name',($_POST['form_submitted'] ? $_POST['Name'] : $Gallery->getParameter($Gallery->getNameParameter())));
	
	$form->addSubmit('export','Export');
	$form->addSubmit('cancel','Cancel');

	$form->addHidden('form_submitted','true');
    switch ($WorkingWith){
    case "Gallery":
	    $form->addHidden('id',$Gallery->getParameter('GalleryID'));
	    break;
	case "ImageSet":
	    $form->addHidden('sid',$Gallery->getParameter('ImageSetID'));
	    break;
	}
	$form->addHidden('return',$returnID);
	
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	
?>
<div style="float:right;width:20%;border:1px solid black;padding:5px;margin:10px 5% 0px 0px">
    <h3><u>Existing Exports</u></h3>
    <ul>
<?php
    $published = glob($dir.'*.zip');
    foreach ($published as $p){
        echo "<li>";
        $filename = str_replace($dir,'',$p);
        echo "<a href='".str_replace(DOC_BASE,BASE_URL,$p)."'>".$filename."</a>";
        $unzip = new dUnzip2($p);
        echo " - <a href='$export&del=".urlencode($filename)."&cs=".substr(md5($filename),3,4)."'>delete</a>";
        echo "</li>";
    }
?>      
    </ul>
</div>
<div style="float:left;width:65%;margin-left:2%">
<?php   $smarty->display('admin_form.tpl'); ?>
</div>
