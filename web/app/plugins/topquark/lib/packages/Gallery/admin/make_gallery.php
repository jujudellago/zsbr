<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	/*******************************************
    // The following was added in to allow the Gallery Application
    // to become an image browser
	********************************************/
    $JustBrowsing = false;
    if (array_key_exists('browsing',$_GET)){
        $JustBrowsing = true;
    }
    if ($JustBrowsing and $_GET['browsing'] != 'set'){
        $Bootstrap->clearBreadcrumbs();
    }
    
	$returnURL = $Bootstrap->makeAdminURL($Package,'manage');
	$update = $Bootstrap->makeAdminURL($Package,'update');
	
    if ($JustBrowsing){
        $returnURL.= "&browsing=".$_GET['browsing'];
        $update.= "&browsing=".$_GET['browsing'];
        if ($_GET['browsing'] == 'set'){
            $update.= "&set_id=".$_GET['set_id'];
        }
    }
    
	/*******************************************
	// Let's figure out if we're talking about a Gallery or an Image Set
	********************************************/
	$gid = (isset($_GET['id'])  ? $_GET['id'] : (isset($_POST['gid']) ? $_POST['gid'] : ''));
	$sid = (isset($_GET['sid'])  ? $_GET['sid'] : (isset($_POST['sid']) ? $_POST['sid'] : ''));
    $make = (isset($_GET['make']) ? $_GET['make'] : (isset($_POST['make']) ? $_POST['make'] : ''));
	if ($gid != ""){
	    $WorkingWith = "Gallery";
	}
	elseif ($sid != ""){
	    $WorkingWith = "ImageSet";
	}
	else{
	    switch ($make){
	    case "new_set":
	        $WorkingWith = "ImageSet";
	        break;
	    case "search":
	        if ($Bootstrap->packageExists('Tags')){
	            $Bootstrap->usePackage('Tags');
    	        $WorkingWith = "Search";
    	        if ($_GET['search'] != ""){
    	            $SearchTags = explode(';',TagContainer::urlDecode($_GET['search']));
    	        }
    	        else{
    	            $SearchTags = array();
    	        }
    	        break;
	        }
	    case "";
    	    header("Location:".$returnURL);
    	    exit();
    	    break;
	    default:
	        $WorkingWith = "Gallery";
	        break;
	    }
	}
	
	/*******************************************
	// Set the navigation items
	********************************************/
    if ($JustBrowsing){
        if ($_GET['browsing'] == 'set'){
	        $Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	        $Bootstrap->addURLToAdminBreadcrumb($Bootstrap->makeAdminURL($Package,'update')."&sid=".$_GET['set_id'],"Update Image Set");
	        $Bootstrap->addURLToAdminBreadcrumb($returnURL."&set_id=".$_GET['set_id'],'Browse for Image');
	    }
	    else{
	        $Bootstrap->addURLToAdminBreadcrumb($returnURL,$Package->admin_pages['manage']['title']);
	    }
	    $Bootstrap->addURLToAdminBreadcrumb($Bootstrap->makeAdminURL($Package,'update'),"Select Photo");
    }
    else{
        $Bootstrap->addURLToAdminBreadcrumb($returnURL,$Package->admin_pages['manage']['title']);
        switch($WorkingWith){
        case 'ImageSet':    
	        $Bootstrap->addURLToAdminBreadcrumb($update,"Update Image Set");
	        break;
        case 'Search':    
	        $Bootstrap->addURLToAdminBreadcrumb($update,"Search Results");
	        break;
	    case 'Gallery':
	        $Bootstrap->addPackagePageToAdminBreadcrumb($Package,'update');
	        break;
        }
	}
	
	define ('HTML_FORM_TH_ATTR',"valign=top align=left width='1%' style='font-size: 0.8em'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
	include_once(dirname(__FILE__).'/../../../TabbedForm.php');

	
	/*********************************************************
	// Get the Gallery/ImageSet, if one is defined
	*********************************************************/
	switch ($WorkingWith){
	case "Gallery":
	    $GalleryContainer = new GalleryContainer();
	    $gid = ($_GET['id'] != "" ? $_GET['id'] : $_POST['gid']);
    	if ($gid != ""){
    	    $Gallery = $GalleryContainer->getGallery($gid);
    	}
    	else{
            $Gallery = new Gallery();
            if ($_GET['status'] != ""){
                if ($Package->use_gallery_year){
                    $Gallery->setParameter('GalleryYear',$_GET['status']);
                }
                else{
                    $Gallery->setParameter('GalleryStatus',$_GET['status']);
                }
            }
        }

        $GalleryDirectoryObject = &$Gallery;
        break;
    case "ImageSet":
        $ImageSetContainer = new ImageSetContainer();
        $ImageSetImageContainer = new ImageSetImageContainer();
        if ($sid != ""){
            $Gallery = $ImageSetContainer->getImageSet($sid);
        }
        else{
            if ($make == "new_set"){
                $Gallery = new ImageSet();
            }
        }
        break;
    case "Search":
        $Gallery = new ImageSet();
        break;
    }    

	/*********************************************************
	// Get all of the Gallery Images
	*********************************************************/
	$GalleryImageContainer = new GalleryImageContainer();
    if ($Gallery->getParameter($Gallery->getIDParameter()) != ""){
        $AllGalleryImages = $Gallery->getAllGalleryImages();
    }
    elseif ($WorkingWith == 'Search'){
        if (count($SearchTags)){
            $TaggedObjectContainer = new TaggedObjectContainer();
            $mode = ($_GET['mode'] == 'all' ? 'all' : 'any');
            $AllGalleryImages = $TaggedObjectContainer->getAllTaggedObjects($SearchTags,'GalleryImage',$mode);
            if (!is_array($AllGalleryImages)){
                $AllGalleryImages = array();
            }
            if ($_GET['filter'] != "" or $_GET['favourites'] != ''){
                // Okay, have to filter out non-matching images.  This is processor intensive because
                // the filter is on the Gallery and we have to instantiate a Gallery for each image.  Grrrr.
                foreach ($AllGalleryImages as $key => $_Image){
                    if ($_GET['filter'] != ''){
                        $g = $_Image->getGallery();
                        if ($g->getParameter('GalleryYear') != $_GET['filter']){
                            unset($AllGalleryImages[$key]);
                        }
                    }
                    if ($_GET['favourites'] != '' and !$_Image->getParameter('ImageIsMarked')){
                        unset($AllGalleryImages[$key]);
                    }
                }
            }
        }
        else{
            // They didn't add any tags to the search, let's see what the filter and favourites parms are
            $wc = new whereClause();
            if ($_GET['filter'] != ""){
                // Get all the galleries with the year specified in the filter
                $wc_filter = new whereClause();
                $GalleryContainer = new GalleryContainer();
                $wc_filter->addCondition($GalleryContainer->getColumnName('GalleryYear').' = ?',$_GET['filter']);
                $FilteredGalleries = $GalleryContainer->getAllGalleriesWhere($wc_filter);
                $FilterIDs = array();
                if (is_array($FilteredGalleries)){
                    foreach ($FilteredGalleries as $g){
                        $FilterIDs[] = $g->getParameter('GalleryID');
                    }
                }
                if (count($FilterIDs)){
                    $wc->addCondition($GalleryImageContainer->getColumnName('GalleryID').' in (?'.str_repeat(',?',count($FilterIDs) - 1).')',$FilterIDs);                    
                }
            }
            if ($_GET['favourites'] != ''){
                $wc->addCondition($GalleryImageContainer->getColumnName('ImageIsMarked').' = 1');
            }
            if (count($wc->getConditions())){
                $Objects = $GalleryImageContainer->getAllObjects($wc);
                if (is_array($Objects)){
                    $AllGalleryImages = $GalleryImageContainer->manufactureGalleryImage($Objects);
                }
            }            
            if (!is_array($AllGalleryImages)){
                $AllGalleryImages = array();
            }
            
        }
        
        if ($_GET['add_all'] == 'true'){
            $ImageSetContainer = new ImageSetContainer();
            $ImageSetImageContainer = new ImageSetImageContainer();
            $ImageSetID = $_GET['set_id'];
            if (is_a($Set = $ImageSetContainer->getImageSet($ImageSetID),'ImageSet')){
                foreach ($AllGalleryImages as $i){
                    $ImageSetImageContainer->addImageSetImageID($ImageSetID,$i->getParameter('ImageID'));
                }
                $MessageList->addMessage('Added '.count($AllGalleryImages).' images to the Image Set "'.$Set->getParameter('ImageSetName').'"',MESSAGE_TYPE_MESSAGE);
            }
            else{
                $MessageList->addMessage('Could not find the Image Set you specified.  Sorry.',MESSAGE_TYPE_MESSAGE);
            }
        }
    }
    
	/******************************************************************
	*  Field Level Validation
	*  Only performed if they've submitted the form
	******************************************************************/
    if (isset($_REQUEST['active_tab']) and $_REQUEST['active_tab'] != ""){
        $DefaultTab = substr($_REQUEST['active_tab'],6);
    }
	if (isset($_POST['form_submitted'])){
		// They hit the cancel button, return to the Manage Pages page
		if (isset($_POST['cancel'])){
			header("Location:".$returnURL);
			exit();
		}
		
        if ($WorkingWith == "Gallery" and (!$JustBrowsing or $Gallery->getParameter('GalleryID') == "")){
		    if ($_POST['GalleryDefaultCredit'] == ""){
			    $MessageList->addMessage("Please specify who took the pictures.",MESSAGE_TYPE_ERROR);
			}
		}
		
    	/******************************************************************
    	*  BEGIN Set Parameters
    	******************************************************************/
		// Only set these fields if we're not browsing or we're adding a new gallery
	    if (!$JustBrowsing or $Gallery->getParameter($Gallery->getIDParameter()) == ""){
	        switch ($WorkingWith){
	        case "Gallery":
    	        if ($_POST['GalleryName'] == ""){
            	    $Gallery->setParameter('GalleryName',$_POST['GalleryDefaultCredit']);
            	}
            	else{
            	    $Gallery->setParameter('GalleryName',$_POST['GalleryName']);
            	}
            	if ($Package->use_gallery_year){
            	    $Gallery->setParameter('GalleryYear',$_POST['GalleryYear']);
            	}
                $Gallery->setParameter('GalleryDefaultCredit',$_POST['GalleryDefaultCredit']);
            	$Gallery->setParameter('GalleryDescription',$_POST['GalleryDescription']);
            	$Gallery->setParameter('GalleryStatus',$_POST['GalleryStatus']);
                break;
            case "ImageSet":
                $Gallery->setParameter('ImageSetName',$_POST['GalleryName']);
            	$Gallery->setParameter('ImageSetDescription',$_POST['GalleryDescription']);
            	$Gallery->setParameter('ImageSetStatus',$_POST['GalleryStatus']);
            	break;
            }
        }
    	
    	/******************************************************************
    	*  END Set Parameters
    	******************************************************************/
    	
        switch ($WorkingWith){
        case "Gallery":
        	if (!$MessageList->hasMessages() and $_POST['gid'] == ""){
        	    $Gallery->setGalleryDirectory();
    
        	    $result = $GalleryContainer->addGallery($Gallery);
        	    if (PEAR::isError($result)){
        	        $MessageList->addMessage($result->getMessage,MESSAGE_TYPE_ERROR);
        	    }
        	} 
        	elseif (!$MessageList->hasMessages()){
        	    $result = $GalleryContainer->updateGallery($Gallery);
        	    if (PEAR::isError($result)){
        	        $MessageList->addMessage($result->getMessage,MESSAGE_TYPE_ERROR);
        	    }
        	}
        	break;
        case "ImageSet":
        	if (!$MessageList->hasMessages() and $_POST['sid'] == ""){
        	    $result = $ImageSetContainer->addImageSet($Gallery);
        	    $DefaultTab = 'ThumbnailsTab';
        	    if (PEAR::isError($result)){
        	        $MessageList->addMessage($result->getMessage,MESSAGE_TYPE_ERROR);
        	    }
        	} 
        	elseif (!$MessageList->hasMessages()){
        	    $result = $ImageSetContainer->updateImageSet($Gallery);
        	    if (PEAR::isError($result)){
        	        $MessageList->addMessage($result->getMessage,MESSAGE_TYPE_ERROR);
        	    }
        	}
        	break;
    	}
	}
	
	
	/***********************************************************************
	*
	*	Set Display Defaults
	*
	***********************************************************************/
	if ($Package->use_gallery_year and $WorkingWith == "Gallery" and $Gallery->getParameter('GalleryYear') == "") $Gallery->setParameter('GalleryYear',date("Y"));
	
	/****************************************************************************
	*
	* BEGIN Display Code
	*    The following code sets how the page will actually display.  
	*
	****************************************************************************/
	// Declaration of the Form	
	$form = new HTML_TabbedForm($update,'post','Update_Form');
	
	/***********************************************************************
	*
	*	Gallery Info Tab
	*
	***********************************************************************/
	switch ($WorkingWith){
	case "Gallery":
        $GalleryInfoTab = new HTML_Tab('GalleryInfoTab','Gallery Information');
        if ($Package->use_gallery_year){
            $GalleryInfoTab->addText('GalleryYear','Gallery Year:',$Gallery->getParameter('GalleryYear'));            
        }
        $GalleryInfoTab->addText('GalleryName','Gallery Name:',$Gallery->getParameter('GalleryName'));
        $GalleryInfoTab->addSelect('GalleryStatus','Gallery Status:',array('public' => 'Public', 'private' => 'Private'),$Gallery->getParameter('GalleryStatus'));
        $GalleryInfoTab->addText('GalleryDefaultCredit','Photographer:',$Gallery->getParameter('GalleryDefaultCredit'));
        $GalleryInfoTab->addTextArea('GalleryDescription','Description:',$Gallery->getParameter('GalleryDescription'),70,5,0);
        break;
    case "ImageSet":
        $GalleryInfoTab = new HTML_Tab('GalleryInfoTab','Image Set Information');
        $GalleryInfoTab->addText('GalleryName','Image Set Name:',$Gallery->getParameter('ImageSetName'));
        $GalleryInfoTab->addSelect('GalleryStatus','Image Set Status:',array('public' => 'Public', 'private' => 'Private'),$Gallery->getParameter('ImageSetStatus'));
        $GalleryInfoTab->addTextArea('GalleryDescription','Description:',$Gallery->getParameter('ImageSetDescription'),70,5,0);
        break;
    }
    
	if (!$JustBrowsing or $Gallery->getParameter($Gallery->getIDParameter()) == ""){
	    if ($WorkingWith != 'Search'){
            $GalleryInfoTab->addPlainText('&nbsp;',HTML_Form::returnSubmit('Save Changes','save_changes').' '.HTML_Form::returnSubmit('Cancel','cancel'));
            $form->addTab($GalleryInfoTab);
        }
    }
        
    /***********************************************************************
    *
    *	Thumbnails Tab
    *
    ***********************************************************************/
    $ThumbnailsTab = new HTML_Tab('ThumbnailsTab','Thumbnails');
    if (isset($_GET['added'])){
        $ThumbnailsTab->addPlainText('&nbsp;',"<b>".$_GET['added']." New Image(s) added to the end</b>");
	    $DefaultTab= 'ThumbnailsTab';
    }
    
    if($WorkingWith == "ImageSet"){
        $UploadCell = "<a href='".$Bootstrap->makeAdminURL($Package,'manage')."&browsing=set&set_id=".$Gallery->getParameter('ImageSetID')."'>Find and add photos</a>";
        $ThumbnailsTab->addPlainText('&nbsp;',$UploadCell);
    }
    
    /***********************************************************************
    *   Set up the thumbnails template
    ***********************************************************************/
    $smarty->assign('WorkingWith',$WorkingWith);
    $smarty->assign('Gallery',$Gallery);
    $smarty->assign_by_ref('AllGalleryImages',$AllGalleryImages);
    $smarty->assign('TotalImages',count($AllGalleryImages));
    $smarty->assign('ThumbWidth',THUMB_WIDTH);
    $smarty->assign('ThumbHeight',THUMB_HEIGHT);
    $smarty->assign('MultiBoxWidth',(RESIZED_WIDTH * 2 + 20));
    $smarty->assign('MultiBoxHeight',(RESIZED_HEIGHT + 20));
    $smarty->assign('gallery_include_mootools',true);
    $smarty->assign('gallery_base',CMS_INSTALL_URL);
    $smarty->assign('AjaxURL',CMS_INSTALL_URL.'lib/packages/Common/ajax.php'."?package=Gallery&".session_name()."=".htmlspecialchars(session_id()));
    switch($WorkingWith){
    case "Gallery":
        $id_string = "id=".$Gallery->getParameter('GalleryID');
        break;
    case "ImageSet":
        $id_string = "sid=".$Gallery->getParameter('ImageSetID');
        break;
    case "Search":
        $id_string = "sid=search";
        break;
    }
    $EditImageURL = $Package->getPackageURL().'admin/edit_image.php?'.$id_string.'&'.session_name()."=".htmlspecialchars(session_id());
    $ImageSetContainer = new ImageSetContainer();
    $ImageSets = $ImageSetContainer->getAllImageSets(array('ImageSetIndex','ImageSetCreationDate'), array('asc','asc'), array("public","private"));            
    $smarty->assign('ImageSets',$ImageSets);
    if ($JustBrowsing){
        $EditImageURL.= "&browsing=".$_GET['browsing'];
        if ($_GET['browsing'] == 'set'){
            $EditImageURL.= "&set_id=".$_GET['set_id'];
        }
    }
    $smarty->assign('EditImageURL',$EditImageURL);
    
    $ThumbnailsTab->addPlainText('&nbsp;',$smarty->fetch(dirname(__FILE__)."/smarty/gallery.thumblist.tpl"));
    
    switch ($WorkingWith){
    case "ImageSet":
        if ($Gallery->getParameter('ImageSetID') == ""){            
            // Can't add photos until we have an ID
        }
        else{
            $form->addTab($ThumbnailsTab);
        }
        break;
    case "Gallery":
        if($Gallery->getParameter('GalleryID') != ""){
            $form->addTab($ThumbnailsTab);
        }
        break;
    case "Search":
        $form->addTab($ThumbnailsTab);
        break;
    }

    if ($JustBrowsing){
        $SelectImageScript = "
            <script language='javascript' type='text/javascript' src='".CMS_INSTALL_URL."lib/js/tinymce/jscripts/tiny_mce/tiny_mce_popup.js'></script>
            <script language='javascript' type='text/javascript'>
            <!--
                function selectImage(URL){
                    //call this function only after page has loaded
                    //otherwise tinyMCEPopup.close will close the
                    //'Insert/Edit Image' or 'Insert/Edit Link' window instead

                    var win = tinyMCEPopup.getWindowArg('window');

                    // insert information now
                    win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = URL;

                    // for image browsers: update image dimensions
                    if (win.resetImageData) win.resetImageData();
                    if (win.getImageData) win.getImageData();

                    // close popup window
                    tinyMCEPopup.close();
                }
              -->
            </script>
        ";
        
        $admin_head_extras = $SelectImageScript;
        
	    $form->addHidden('select_image',"");
	    $admin_head_extras.= "
        <script language='JavaScript' type='text/javascript'> 
        <!-- 
                function openSelectSizePopup(imageID){
                    var url = '".$Bootstrap->makeAdminURL($Package,'select_image', 'selectImageSize')."&id=' + imageID;
                    var width = 600;
                    var height = 350;
            		x = parseInt(screen.width / 2.0) - (width / 2.0);
            		y = parseInt(screen.height / 2.0) - (height / 2.0);

                    var eWindow = window.open(url, 'selectImageSize', 'scrollbars=yes,menubar=no,resizable=no,toolbar=no,width=' + width +',height=' + height + ',top=' + y + ',left=' + x);
                }
                
                // Remove the Popup CSS file
                function removePopupCSS(){
                    var allLinks = document.getElementsByTagName('link');
                    for (var i = 0; i < allLinks.length; i++) {
                        if (allLinks[i].href && allLinks[i].href.match(/\/editor_popup.css?$/)){
                            allLinks[i].parentNode.removeChild(allLinks[i]);
                        }
                    }
                }
            
	    -->
	    </script>
	    ";
    }
    
    
	if (!isset($DefaultTab) or $DefaultTab == ""){
	    if ((is_array($AllGalleryImages) and count($AllGalleryImages)) or $WorkingWith == 'Search'){	        
	        $DefaultTab= 'ThumbnailsTab';
	    }
	    else{
	        $DefaultTab= 'GalleryInfoTab';
	    }
	}
	if ($JustBrowsing){
	    if ($Gallery->getParameter($Gallery->getIDParameter()) == "" and $WorkingWith != 'Search'){
	        $DefaultTab= 'GalleryInfoTab';
	    }
	    else{	        
	        $DefaultTab= 'ThumbnailsTab';
	    }
	}

    /***********************************************************************
    *
    *	Upload Tab
    *
    ***********************************************************************/
    $UploadTab = new HTML_Tab('UploadTab','Upload Images');
    $FancyUploadURL = CMS_INSTALL_URL.'lib/packages/Common/ajax.php?package=Gallery&'.session_name()."=".htmlspecialchars(session_id())."&id=".$Gallery->getParameter('GalleryID');
    $FancyUploadRedirectURL = CMS_ADMIN_URL.$Bootstrap->makeAdminURL($Package,'update')."&id=".$Gallery->getParameter('GalleryID');
    if ($JustBrowsing){
        $FancyUploadURL.= "&browsing=".$_GET['browsing'];
        $FancyUploadRedirectURL.= "&browsing=".$_GET['browsing'];
        if ($_GET['browsing'] == 'set'){
            $FancyUploadURL.= "&set_id=".$_GET['set_id'];
            $FancyUploadRedirectURL.= "&set_id=".$_GET['set_id'];
        }
    }
    $smarty->assign('fu_redirect_url',$FancyUploadRedirectURL ."' + '&added=' + swiffy.files_uploaded + '");
    $smarty->assign('fu_url',$FancyUploadURL);
    $smarty->assign('fu_just_images',true);
    $smarty->assign('fu_base',CMS_INSTALL_URL); // relative path from script to "lib"
    $smarty->assign('fu_fieldname','photoupload');
    $smarty->assign('fu_include_mootools',false);
    $UploadTab->addPlainText("&nbsp",$smarty->fetch('fancyupload.tpl'));
    
    if ($WorkingWith == "Gallery" and $Gallery->getParameter('GalleryID') != ""){
        $form->addTab($UploadTab);
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
        if (!isset($DefaultTab) or $MessageList->hasErrors() or $DisplayContinueMessage){
            $DefaultTab = 'MessageTab';
        }
		$form->addTab($MessageTab);
	}
	
    
	// Some hidden fields to help us out 
	$form->addHidden('form_submitted','true');
    $form->addHidden('make',$make);
    switch ($WorkingWith){
    case "Gallery":
        $form->addHidden('gid',$Gallery->getParameter('GalleryID'));
        break;
    case "ImageSet":
        $form->addHidden('sid',$Gallery->getParameter('ImageSetID'));
        break;
    case "Search":
        $form->addHidden('search',htmlspecialchars(implode(";",$SearchTags)));
        break;
    }
    $$DefaultTab->setDefault();
	if (!isset($start_functions)){
		$start_functions = array();
	}
	if (!isset($admin_head_extras)){		
		$admin_head_extras = '';
	}
	
	if ($JustBrowsing){
	    $start_functions[] = "removePopupCSS();";
	}
	$smarty->assign('includes_tabbed_form',true);
	$smarty->assign('admin_start_function',$start_functions);
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	$smarty->assign('Tabs',$form->getTabs());
	$smarty->assign('admin_head_extras',$admin_head_extras);
  
  
	if ($MessageList->hasMessages() and $_GET['browsing'] == 'set'){
	    echo "<div align='center'>".$MessageList->toBullettedString()."</div>";
	}
  
?>
<?php
    switch ($WorkingWith){
    case "ImageSet":
        echo "<div style='width:90%;margin:10px auto'><strong>Note</strong>: Image Sets are collections of images from other galleries on your website.  You can add captions and reorder the photos in an Image Set without affecting the image in its original gallery.</div>";
        break;
    case "Search":
        echo "<div style='width:90%;margin:10px auto'>Search Results: ".count($AllGalleryImages)." images</div>";
        break;
    }
?>
<?php   $smarty->display('admin_form.tpl'); ?>
<?php
    /***********************************************************************
    *
    *	Some extra code to do with the searching
    *
    ***********************************************************************/
    if ($WorkingWith == "Search" and $Bootstrap->packageExists('Tags')){
        $smarty->assign('AjaxURL',CMS_INSTALL_URL.'lib/packages/Tags/admin/ajax.php?ajax=true&'.session_name()."=".htmlspecialchars(session_id()));
        $smarty->assign('tags_include_mootools',false);
        $smarty->assign('tags_base',CMS_INSTALL_URL);
        $smarty->assign('parms',array('searching' => true));
        $Tags = array();
        $TagContainer = new TagContainer();
        foreach ($SearchTags as $SearchTag){
            if (is_a($Tag = $TagContainer->getTagText($SearchTag),'Tag')){
                $Tags[] = $Tag;
            }
        }
        $smarty->assign('Tags',$Tags);
        // There are some extras that we want to put in the search
        $TagSearchExtras = "<p style='clear:both'>Include: <select name='SearchMode' id='SearchMode'><option ".($_GET['mode'] == 'any' ? 'selected' : '')." value='any'>Any Tag</option><option ".($_GET['mode'] == 'all' ? 'selected' : '')." value='all'>All Tags</option></select>\n";
        $TagSearchExtras.= " Filter by Year: <select name='SearchFilter' id='SearchFilter'><option ".($_GET['filter'] == '' ? 'selected' : '')." value=''>&lt;Any Year&gt;</option>";
        $GalleryContainer = new GalleryContainer();
        $AllYears = $GalleryContainer->getAllValues('GalleryYear');
        foreach ($AllYears as $y){
            $TagSearchExtras.= "<option ".($_GET['filter'] == $y ? 'selected' : '')." value='$y'>$y</option>";
        }
        $TagSearchExtras.= "</select>";
        $TagSearchExtras.= " Only Favourites: <input type='checkbox' name='SearchFavourites' id='SearchFavourites' ".($_GET['favourites'] ? 'checked' : '').">";
        $TagSearchExtras.= "</p>\n";
        
        $smarty->assign('TagSearchExtras',$TagSearchExtras);
        $TagWidget = $smarty->fetch(dirname(__FILE__).'/../../Tags/smarty/tags.search.tpl'); 
        echo "<div style='width:90%;margin:10px auto;'>$TagWidget";
        echo "<input type='button' id='Search' value='Refine' onclick='search();'>\n";
        echo "
        <script type='text/javascript' src='".CMS_INSTALL_URL."lib/js/php_js.js'></script>
        <script type='text/javascript'>
        function search(){
            if (!Tags.length && $('SearchFilter').value == '' && !$('SearchFavourites').checked){
                alert('You must enter at least one tag for your search');
            }
            else{
                var SearchString = urlencode(Tags.join(';'));
                var href = '".$update."&make=search&search=' + SearchString + '&mode=' + $('SearchMode').value;
                if ($('SearchFilter').value != ''){
                    href += '&filter=' + $('SearchFilter').value;
                }
                if ($('SearchFavourites').checked){
                    href += '&favourites=true';
                }
                window.location.href = href;
                return;
            }
        }
        </script>
        ";
        echo "</div>\n";
    }
?>