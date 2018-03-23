<?php
    include_once("../../../Standard.php");
	if (CMS_PLATFORM == 'WordPress'){
	    $AuthorizationNotNecessary = true; 
	}
    include_once("../../../../admin/AdminStandard.php");
    
    $Bootstrap = Bootstrap::getBootstrap();
    $Package = $Bootstrap->usePackage('Gallery');        
    


	// Let's figure out if we're talking about a Gallery or an Image Set
	$gid = (isset($_GET['id'])  ? $_GET['id'] : (isset($_POST['gid']) ? $_POST['gid'] : ''));
	$sid = (isset($_GET['sid'])  ? $_GET['sid'] : (isset($_POST['sid']) ? $_POST['sid'] : ''));
	if ($gid != ""){
	    $WorkingWith = "Gallery";
	}
	elseif ($sid != ""){
	    $WorkingWith = "ImageSet";
	}
	if (isset($_GET['browsing'])){
	    $JustBrowsing = true;
	}
	// Get the gallery, if one is defined
	switch ($WorkingWith){
	case "Gallery":
	    $GalleryContainer = new GalleryContainer();
	    $GalleryImageContainer = new GalleryImageContainer();
    	$Gallery = $GalleryContainer->getGallery($gid);
    	$Image = $GalleryImageContainer->getGalleryImage($_GET['image_id']);
        break;
    case "ImageSet":
        if ($sid == 'search'){
	        $GalleryImageContainer = new GalleryImageContainer();
    	    $Image = $GalleryImageContainer->getGalleryImage($_GET['image_id']);
    	    $WorkingWith = 'Gallery';
        }
        else{
            $ImageSetContainer = new ImageSetContainer();
            $Gallery = $ImageSetContainer->getImageSet($sid);
        	$Image = $ImageSetContainer->getGalleryImage($sid,$_GET['image_id']);
        }
        break;
    }
    
    if (!is_a($Image,'GalleryImage')){
        echo "Could not find the image you were looking to edit";
        exit();
    }
    
    /**************************************************************************************
    *
    *   Ajax Processing
    *
    **************************************************************************************/
    if (isset($_GET['ajax']) and $_GET['ajax'] == 'true'){
        $result = array();
 
        switch ($_GET['action']){
        case 'rotate':
            if ($Image){
                if ($_GET['direction'] == "counter_clockwise"){
                        $Image->rotateImage(90);
                }
                else {
                        $Image->rotateImage(270);
                }
            }
            $result['result'] = 'success';
            $result['new_resized'] = $Image->getParameter('GalleryImageResized');
            $result['new_thumb'] = $Image->getParameter('GalleryImageThumb');
            $result['new_original'] = $Image->getParameter('GalleryImageOriginal');
            break;
        case 'caption':
            if ($Image){
                $Image->setParameter('ImageCaption',$_GET['caption']);
                if ($WorkingWith == 'Gallery'){
                    $GalleryImageContainer->updateGalleryImage($Image);
                }
                else{
        	        $ImageSetContainer->updateGalleryImage($sid,$Image);
                }
            }
            $result['result'] = 'success';
            break;
        case 'credit':
            if ($Image){
                $Image->setParameter('ImageCredit',$_GET['credit']);
                $GalleryImageContainer->updateGalleryImage($Image);
            }
            $result['result'] = 'success';
            break;
        case 'primary':
            if ($Image){
                $Gallery->setPrimaryThumb($Image->getParameter('ImageID'));
            }
            $result['result'] = 'success';
            break;
        case 'mark':
            if ($Image){
                $Image->setParameter('ImageIsMarked',1);
                $r = $GalleryImageContainer->updateGalleryImage($Image);
            }
            $result['result'] = 'success';
            break;
        case 'unmark':
            if ($Image){
                $Image->setParameter('ImageIsMarked',0);
                $GalleryImageContainer->updateGalleryImage($Image);
            }
            $result['result'] = 'success';
            break;
        case 'delete':
            if (strpos($_GET['image_id'],',')){
                $ImageIDs = explode(',',$_GET['image_id']);
            }
            else{
                $ImageIDs = array($_GET['image_id']);
            }
            foreach ($ImageIDs as $ImageID){
                switch ($WorkingWith){
                case "Gallery":
                    $e = $GalleryImageContainer->deleteGalleryImage($ImageID);
                    break;
                case "ImageSet":
                    $e = $ImageSetContainer->deleteGalleryImage($sid,$ImageID);
                    break;
                }
            }
            $result['result'] = 'success';
            break;
        case 'add_to_set':
            $ImageSetImageContainer = new ImageSetImageContainer();
            if (strpos($_GET['image_id'],',')){
                $ImageIDs = explode(',',$_GET['image_id']);
            }
            else{
                $ImageIDs = array($_GET['image_id']);
            }
            if ($_GET['set_id'] != ""){
                foreach ($ImageIDs as $ImageID){
                    $ImageSetImageContainer->addImageSetImageID($_GET['set_id'],$ImageID);
                }
            }
            $result['result'] = 'success';
            break;
		case 'new-thumb':
	        if ($Image){
				$src = $Image->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImageResized');
				$dest = $current_thumb = $Image->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImageThumb');
				$token = '_'.substr(md5($_GET['id'].$_GET['w'].$_GET['h'].$_GET['x'].$_GET['y']),0,5);
				if (($pos = strrpos($dest,'.')) !== false){
					$dest = substr($dest,0,$pos-strlen($token)).$token.substr($dest,$pos);
				}
				else{
					// Just incase there isn't an extension
					$dest.= substr($dest,-1*strlen($token)).$token;
				}
				$ImageLibrarian = new ImageLibrarian();
				extract($_GET);
				if (!(is_numeric($x) and is_numeric($y) and is_numeric($w) and is_numeric($h) and is_numeric($dest_w) and is_numeric($dest_h))){
		            $result['result'] = 'failure';
					$result['message'] = 'Invalid values passed';
					break;
				}
				$r = $ImageLibrarian->imagecopyresampled($dest,$src,$x,$y,$dest_w,$dest_h,$w,$h);
				if (PEAR::isError($r)){
		            $result['result'] = 'failure';
					$result['message'] = $e->getMessage();
				}
				else{
		            $result['result'] = 'success';
					$Image->setParameter('GalleryImageThumb',basename($dest));
					$GalleryImageContainer->updateGalleryImage($Image);
					unlink($current_thumb);
					$result['new_thumb'] = $Image->getGalleryDirectory().$Image->getParameter('GalleryImageThumb');
				}
	        }
			break;
        }
 
        if (!headers_sent() )
        {
        	header('Content-type: application/json');
        }
 
        echo json_encode($result);
        exit();
    }
    





    $smarty->assign('Image',$Image);
    $smarty->assign('WorkingWith',$WorkingWith);
    if (isset($_GET['sid']) and $_GET['sid'] == 'search'){
        $smarty->assign('SearchResults',true);
    }
    $smarty->assign('Gallery',$Gallery);
    $ImageSize = getImageSize($Image->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImageResized'));
    $smarty->assign('ImageWidth',($ImageSize[0] > 400 ? 400 : $ImageSize[0]));
    if ($Bootstrap->packageExists('Tags')){
        $Bootstrap->usePackage('Tags');
        $TaggedObjectContainer = new TaggedObjectContainer();
        $Tags = $TaggedObjectContainer->getTagsForObject($Image);
        $smarty->assign('AjaxURL',CMS_INSTALL_URL.'lib/packages/Tags/admin/ajax.php?ajax=true&'.session_name()."=".htmlspecialchars(session_id()));
        $smarty->assign('tags_include_mootools',false);
        $smarty->assign('tags_base',CMS_INSTALL_URL);
        $smarty->assign('parms',array('object_class' => 'GalleryImage', 'object_id' => $Image->getParameter('ImageID')));
        $smarty->assign('Tags',$Tags);
        $TagWidget = $smarty->fetch(dirname(__FILE__).'/../../Tags/smarty/tags.input.tpl'); 
        $smarty->assign('TagWidget',$TagWidget);
    }
    $smarty->assign('gallery_base',CMS_INSTALL_URL);
    $AjaxURL = CMS_INSTALL_URL.'lib/packages/'.$Package->package_name.'/admin/edit_image.php?ajax=true';
    $AjaxURL.= '&'.session_name()."=".htmlspecialchars(session_id());
    $AjaxURL.= ($WorkingWith == 'Gallery' ? '&id=' : '&sid=').$Gallery->getParameter($Gallery->getIDParameter());
    $AjaxURL.= '&image_id='.$Image->getParameter('ImageID');
    if (isset($JustBrowsing) and $JustBrowsing and isset($_GET['browsing']) and $_GET['browsing'] == 'set'){
        $AjaxURL.= '&set_id='.$_GET['set_id'];
        $ImageSetImageContainer = new ImageSetImageContainer();
        if ($ImageSetImageContainer->getImageSetImage($_GET['set_id'],$Image->getParameter('ImageID'))){
            $smarty->assign('AlreadyInImageSet',true);
        }
    }
    $smarty->assign('AjaxURL',$AjaxURL);
    $smarty->assign('AllowCreditOnImage',$Package->allow_credit_on_image);
    $smarty->display(dirname(__FILE__).'/smarty/gallery.edit_image.tpl');
?>