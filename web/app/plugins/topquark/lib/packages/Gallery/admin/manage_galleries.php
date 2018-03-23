<?php
	/*******************************************
	* manage.php
	*
	* Use this to modify or delete galleries from the system
	*
	********************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }

    $tmp = $Package;
	global $delete, $edit, $manage, $export,$JustBrowsing, $Package;
    global $galleries_shown, $numGalleries, $numImageSets;
	$Package = $tmp;

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
    
	/*******************************************
	// Set the navigation items
	********************************************/
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$edit = $Bootstrap->makeAdminURL($Package,'update');
	$delete = $Bootstrap->makeAdminURL($Package,'delete');
	$export = $Bootstrap->makeAdminURL($Package,'export');
	$manage = $Bootstrap->makeAdminURL($Package,'manage');

    if ($JustBrowsing){
        $edit.= "&browsing=".$_GET['browsing'];
        if ($_GET['browsing'] == 'set'){
            $edit.= "&set_id=".$_GET['set_id'];
        }
    }
    
    if ($JustBrowsing and $_GET['browsing'] == 'set'){
        $Bootstrap->addURLToAdminBreadcrumb($Bootstrap->makeAdminURL($Package,'update')."&sid=".$_GET['set_id'],'Update Image Set');
        $Bootstrap->addURLToAdminBreadcrumb($manage,'Browse for Image');
    }


	include_once(dirname(__FILE__)."/../../Common/ObjectLister.php");
	define ('HTML_FORM_TH_ATTR',"valign=top align=left width='1%' style='font-size: 0.8em'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
	include_once(dirname(__FILE__)."/../../../TabbedForm.php");

	$GalleryContainer = new GalleryContainer();
	$GalleryImageContainer = new GalleryImageContainer();
	$ImageSetContainer = new ImageSetContainer();

	if (isset($_REQUEST['action'])){
	    switch($_REQUEST['action']){
		case "reorder":
		    if ($_REQUEST['imagesetid'] != ""){
			    $ImageSetContainer->setImageSetIndex($_REQUEST['imagesetid'],$_REQUEST['galleryindex']);
			    $DefaultTab = 'ImageSetTab';
			}
			else{
	    		$GalleryContainer->setGalleryIndex($_REQUEST['galleryid'],$_REQUEST['galleryindex']);
	    		$Gallery = $GalleryContainer->getGallery($_REQUEST['galleryid']);
	            $DefaultTab = ucwords($Gallery->getParameter('GalleryStatus'))." GalleriesTab";
	        }
	    	break;
		}
	}
	/****************************************************************************
	*
	* BEGIN Display Code
	*    The following code sets how the page will actually display.  
	*
	****************************************************************************/
	// Declaration of the Form	
	$form = new HTML_TabbedForm($manage,'post','Update_Form');
	
	/*********************************************************************
    *
	*  All Galleries Tab
    *
	*********************************************************************/
	$ObjectLister = new ObjectLister();

	$ObjectLister->addColumn('Thumbnail','displayThumb','15%');
	$ObjectLister->addColumn('Name','displayName','15%');
	$ObjectLister->addColumn(($Package->use_gallery_year ? 'Year &amp; ' : '').'Description','displayDescription','40%');
	$ObjectLister->addColumn('','displayNavigation','14%');
	$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	$smarty->assign('ObjectListCellPadding',5);
	$smarty->assign('ObjectListWidth','100%');
	$smarty->assign('ObjectEmptyString',"There are currently no galleries on this site.  <a href='".$edit."&make=new'>Click to Add a New Gallery</a>");
	
	/*******************************************
	// Get the Galleries
	********************************************/
	if ($Package->use_gallery_year){
	    $GalleryTypes = $GalleryContainer->getAllValues('GalleryYear');
	    if (!is_array($GalleryTypes)){
	        $GalleryTypes = array(date("Y"));
	    }
	    $GalleryTypes = array_reverse($GalleryTypes);
    }
    else{
        if (is_array($Package->gallery_types)){
            $GalleryTypes = $Package->gallery_types;
        }
        else{
            $GalleryTypes = array('public','private','system');
        }
    }

    $FirstType = "";
    foreach ($GalleryTypes as $Type){
        if ($FirstType == ""){
            $FirstType = $Type;
        }
        if ($Package->use_gallery_year){
            $wc = new WhereClause();
            $wc->addCondition($GalleryContainer->getColumnName('GalleryYear')." = ?",$Type);
            $AllGalleries = $GalleryContainer->getAllGalleriesWhere($wc,array('GalleryName'),array('asc'),array('public','private'));
        }
        else{
            $AllGalleries = $GalleryContainer->getAllGalleries(array('GalleryIndex','GalleryCreationDate'), array('asc','asc'),array($Type));
        }
        $galleries_shown = 0;
        $numGalleries = count($AllGalleries);
        if (count($AllGalleries) or $Type == $FirstType){
            $TabTitle = ucwords($Type)." Galleries";
            $TabName = $TabTitle."Tab";
            $$TabName = new HTML_Tab($TabName,$TabTitle);
            if ($Type == 'public' or $Type == 'private' or $Package->use_gallery_year){
                $$TabName->addPlainText('&nbsp;',"<a href='".$edit."&make=new&status=$Type'>Add a new gallery</a>");
            }
            // There was a bug in firefox (untested in other browsers) where it wasn't recognizing the first 
            // reorder form.  Dunno why.  Adding a dummy form fixed it.
            if (!$Package->use_gallery_year){
                $$TabName->addPlainText('&nbsp;',"<form name='dummy_form'></form>\n");
            }
            $smarty->assign('ObjectList', $ObjectLister->getObjectList($AllGalleries));
            $$TabName->addPlainText('&nbsp;',$smarty->fetch('admin_listing.tpl'));
            $form->addTab($$TabName);
            if (!isset($DefaultTab) or $DefaultTab == ""){
                $DefaultTab = $TabName;
            }
        }
    }

    /*********************************************************************
    *
    *   Image Sets Tab
    *
    *********************************************************************/
    $ImageSetTab = new HTML_Tab('ImageSetTab','Image Sets');
    $ImageSets = $ImageSetContainer->getAllImageSets(array('ImageSetIndex','ImageSetCreationDate'), array('asc','asc'), array("public","private"));
    $numImageSets = count($ImageSets);
    $galleries_shown = 0;
    $ImageSetTab->addPlainText('&nbsp;',"<a href='".$edit."&make=new_set'>Add a new image set</a>");
    // There was a bug in firefox (untested in other browsers) where it wasn't recognizing the first 
    // reorder form.  Dunno why.  Adding a dummy form fixed it.
    $ImageSetTab->addPlainText('&nbsp;',"<form name='dummy_form2'></form>\n");
    $smarty->assign('ObjectList', $ObjectLister->getObjectList($ImageSets));
	$smarty->assign('ObjectEmptyString',"There are currently no image sets on this site.  <a href='".$edit."&make=new_set'>Click to Add a New Image Set</a>");
    $ImageSetTab->addPlainText('&nbsp;',$smarty->fetch('admin_listing.tpl'));
    $form->addTab($ImageSetTab);

    /*********************************************************************
    *
    *   Search Tab
    *
    *********************************************************************/
	$admin_head_extras = '';
    if ($Bootstrap->packageExists('Tags')){
        $Bootstrap->usePackage('Tags');
        $TagContainer = new TagContainer();
        $smarty->assign('AjaxURL',CMS_INSTALL_URL.'lib/packages/Tags/admin/ajax.php?ajax=true&'.session_name()."=".htmlspecialchars(session_id()));
        $smarty->assign('tags_include_mootools',true);
        $smarty->assign('tags_base',CMS_INSTALL_URL);
        $smarty->assign('parms',array('searching' => true));
        $smarty->assign('Tags',array());
        
        // There are some extras that we want to put in the search
        $TagSearchExtras = "<p style='clear:both'>Include: <select name='SearchMode' id='SearchMode'><option selected value='any'>Any Tag</option><option value='all'>All Tags</option></select>\n";
        
        $TagSearchExtras.= " Filter by ".($Package->use_gallery_year ? 'Year' : 'Status').": <select name='SearchFilter' id='SearchFilter'><option selected value=''>&lt;Any ".($Package->use_gallery_year ? 'Year' : 'Status')."&gt;</option>";
        foreach ($GalleryTypes as $t){
            $TagSearchExtras.= "<option value='$t'>$t</option>";
        }
        $TagSearchExtras.= "</select>";
        $TagSearchExtras.= " Only Favourites: <input type='checkbox' name='SearchFavourites' id='SearchFavourites'>";
        $TagSearchExtras.= "</p>\n";
        
        $smarty->assign('TagSearchExtras',$TagSearchExtras);
        $TagWidget = $smarty->fetch(dirname(__FILE__).'/../../Tags/smarty/tags.search.tpl'); 
        
        $SearchTab = new HTML_Tab('SearchTab','Search');
        $SearchTab->addPlainText('&nbsp;','Use this feature to search by Tag.  Enter one or more tags that you\'d like to search for, then click the Search Button.');
        $SearchTab->addPlainText('&nbsp;',$TagWidget);
        $SearchButton = "<input type='button' id='Search' value='Search' onclick='search();'>\n";
        $SearchTab->addPlainText('&nbsp;',$SearchButton);
        $admin_head_extras.= "
	    <script type='text/javascript' src='".CMS_INSTALL_URL."lib/js/php_js.js'></script>
        <script type='text/javascript'>
        function search(){
            if (!Tags.length && $('SearchFilter').value == '' && !$('SearchFavourites').checked){
                alert('You must enter at least one tag for your search');
            }
            else{
                var SearchString = urlencode(Tags.join(';'));
                var href = '".$edit."&make=search&search=' + SearchString + '&mode=' + $('SearchMode').value;
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
        $form->addTab($SearchTab);
    }
    

    $$DefaultTab->setDefault();
	$smarty->assign('includes_tabbed_form',true);
	//$smarty->assign('admin_start_function',$start_functions);
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	$smarty->assign('Tabs',$form->getTabs());
	$smarty->assign('admin_head_extras',$admin_head_extras);
	
	
	function displayThumb($Object){
		global $edit;
	 	if (is_a($Object,'Gallery')){
	 		$Thumb = $Object->getPrimaryThumb();
			if (PEAR::isError($Thumb)){
				return $Thumb->getMessage();
			}
			else{
				if ($Thumb != null){
					return "<a href='".$edit."&id=".$Object->getParameter('GalleryID')."'><img border=0 src=\"".$Object->getGalleryDirectory().$Thumb->getParameter('GalleryImageThumb')."\"></a>";
				}
				else{
					return "&nbsp;";
				}
			}
	 	}
	 	elseif (is_a($Object,'ImageSet')){
	 		$Thumb = $Object->getPrimaryThumb();
			if (PEAR::isError($Thumb)){
				return $Thumb->getMessage();
			}
			else{
				if ($Thumb != null){
					return "<a href='".$edit."&sid=".$Object->getParameter('ImageSetID')."'><img border=0 src=\"".$Thumb->getGalleryDirectory().$Thumb->getParameter('GalleryImageThumb')."\"></a>";
				}
				else{
					return "&nbsp;";
				}
			}
		}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
	
	function displayName($Object){
		global $edit;
	 	if (is_a($Object,'Gallery')){
			return "<a href='".$edit."&id=".$Object->getParameter('GalleryID')."'>".$Object->getParameter('GalleryName')."</a>";
	 	}
	 	if (is_a($Object,'ImageSet')){
			return "<a href='".$edit."&sid=".$Object->getParameter('ImageSetID')."'>".$Object->getParameter('ImageSetName')."</a>";
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}

	function displayDescription($Object){
	    global $Package;
	 	if (is_a($Object,'Gallery')){
			$return = '';
			$sep = '';
	 	    if ($Package->use_gallery_year){
    	 	    $return = $Object->getParameter('GalleryYear');
    	 	    $sep = " - ";
    	 	}
	 	    if ($Object->getParameter('GalleryDescription') != ""){
	 	        $return .= $sep.$Object->getParameter('GalleryDescription');
	 	    }
	 		return $return;
	 	}
	 	elseif (is_a($Object,'ImageSet')){
	 	    $return = "";
	 	    if ($Object->getParameter('ImageSetDescription') != ""){
	 	        $return = $Object->getParameter('ImageSetDescription');
	 	    }
	 		return $return;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}

	function displayNavigation($Object){
		global $delete, $edit, $manage, $export,$JustBrowsing, $Package;
        global $galleries_shown, $numGalleries, $numImageSets;
		$galleries_shown++;
	 	if (is_a($Object,'Gallery')){
	 	    if ($JustBrowsing){
	 		    $_ret =  "<a href='$edit&id=".$Object->getParameter('GalleryID')."'>select</a> ";
	 		}
	 		else{
    	 		$_ret =  "<a href='".$edit."&id=".$Object->getParameter('GalleryID')."'>edit</a> ";
    	 		$_ret.=  "<a href='".$delete."&id=".$Object->getParameter('GalleryID')."'>delete</a> ";
    	 		$_ret.=  "<a href='".$export."&id=".$Object->getParameter('GalleryID')."'>export</a> ";
    	 	}
            if (!$Package->use_gallery_year and $numGalleries > 1){
    			$_ret.= "\n<form action='".$manage."' method='post' name='reorderGallery$galleries_shown' id='reorderGallery$galleries_shown'>\n";
    			$_ret.= "<input type=hidden name=galleryid value=".$Object->getParameter('GalleryID').">\n";
    			//$_ret.= "<input type=hidden name=package value='".$Package->package_name."'>\n";
    			//$_ret.= "<input type=hidden name=".ADMIN_PAGE_PARM." value='manage'>\n";
    			$_ret.= "<input type=hidden name=action value='reorder'>\n";
    			$_ret.= "<br>Order: <select name='galleryindex' onChange=\"this.form.submit();\")>";
    			for($i = 1 ; $i <= $numGalleries ; $i++){
    				$selected = $galleries_shown == $i ? "selected" : "";
    				$_ret.= "<option value='$i' $selected>$i</option>\n";
    			} 
    			$_ret.= "</select>\n";
    			$_ret.= "</form>\n";
            }
			$_ret.= "<div style=\"font-size:10px\">Shortcode: <input style=\"font-size:10px\" value=\"[topquark gid=".$Object->getParameter('GalleryID')." action=paint package=Gallery]\" readonly=\"readonly\"></div>\n";
	 		return $_ret;
	 	}
	 	elseif (is_a($Object,'ImageSet')){
	 	    if ($JustBrowsing){
	 		    $_ret =  "<a href='$edit&sid=".$Object->getParameter('ImageSetID')."'>select</a> ";
	 		}
	 		else{
    	 		$_ret =  "<a href='".$edit."&sid=".$Object->getParameter('ImageSetID')."'>edit</a> ";
    	 		$_ret.=  "<a href='".$delete."&sid=".$Object->getParameter('ImageSetID')."'>delete</a> ";
    	 		$_ret.=  "<a href='".$export."&sid=".$Object->getParameter('ImageSetID')."'>export</a> ";

                if ($numImageSets > 1){
        			$_ret.= "\n<form action='".$manage."' method=post name='reorderImageSet$galleries_shown'>\n";
        			$_ret.= "<input type=hidden name=imagesetid value=".$Object->getParameter('ImageSetID').">\n";
        			//$_ret.= "<input type=hidden name=package value='".$Package->package_name."'>\n";
        			//$_ret.= "<input type=hidden name=page value='manage'>\n";
        			$_ret.= "<input type=hidden name=action value='reorder'>\n";
        			$_ret.= "<br>Order: <select name='galleryindex' onChange=\"document.reorderImageSet$galleries_shown.submit()\")\">";
        			for($i = 1 ; $i <= $numImageSets ; $i++){
        				$selected = $galleries_shown == $i ? "selected" : "";
        				$_ret.= "<option value='$i' $selected>$i</option>\n";
        			} 
        			$_ret.= "</select>\n";
        			$_ret.= "</form>\n";
                }
    	 	}
			$_ret.= "<div style=\"font-size:10px\">Shortcode: <input style=\"font-size:10px\" value=\"[topquark sid=".$Object->getParameter('ImageSetID')." action=paint package=Gallery]\" readonly=\"readonly\"></div>\n";
	 		return $_ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
	
?>
<?php
    if ($JustBrowsing){
        if ($_GET['browsing'] == 'set'){
            $href = $Bootstrap->makeAdminURL($Package,'update')."&sid=".$_GET['set_id'];
        }
        else{
            $href = "javascript:window.close()";
        }
        echo "<h3 style='text-align:center'>Browse to a gallery to select a photo or <a href='$href'>cancel</a></h3>\n";
    }
?>    
<?php   $smarty->display('admin_form.tpl'); ?>
