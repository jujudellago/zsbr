<?php
	/*******************************************
	* file_browser.php
	*
	* This is a wrapper file to load the Gallery
	* Manager, which will let the user browse for or 
	* upload an image file.  
	*
	********************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
    // now, we're going to load the Gallery Package.  
    // First, we'll save the current package, to restore it after including the gallery admin page
    $SavePackage = $Package;
    $thisURL = $Bootstrap->getAdminURL()."&type=".$_GET['type'];
    
    switch ($_GET['type']){
    case 'image':
        if ($Bootstrap->packageExists('AdvancedGallery')){
            $Package = $Bootstrap->usePackage('AdvancedGallery');
        }
        else{
            $Package = $Bootstrap->usePackage('Gallery');
        }
    
        $_GET['browsing'] = true;
    
        include ($Package->getPackageDirectory().$Package->admin_pages['manage']['url']);
        break;
    case 'media':
        include ($Package->getPackageDirectory().$Package->admin_pages['media_browser']['url']);
        break;
    case 'file':
        include ($Package->getPackageDirectory().$Package->admin_pages['link_browser']['url']);
        break;
    default:
        $smarty->assign('hide_navigation',true);
        echo "<center>".$_GET['type']." browser not implemented yet.  <a href='javascript:window.close();'>Close Window</a></center>";
    }

    // Now restore the package
    $Package = $SavePackage;

?>
