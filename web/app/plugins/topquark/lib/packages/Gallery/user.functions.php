<?php

    include_once(PACKAGE_DIRECTORY.'Common/UserFunction.php');
    
    class Gallery__UserFunctions extends UserFunction{
        
        function Gallery__UserFunctions(){
            global $Bootstrap;
            
            $this->setPackage('Gallery');
            $this->setFunctionParameters(); // See below
            
                        
        }
    
        function Gallery__UserPaint($parms,$package,&$smarty){

            $GalleryContainer = new GalleryContainer();
            $GalleryImageContainer = new GalleryImageContainer();            
            $ImageSetContainer = new ImageSetContainer();
            $ImageSetImageContainer = new ImageSetImageContainer();
            
            $Bootstrap = Bootstrap::getBootstrap();
    
            $return = "";

            if ($_GET['gid'] != ""){
                $Gallery = $GalleryContainer->getGallery($_GET['gid']);
                if (is_a($Gallery,'Gallery') and is_array($package->public_gallery_types) and !in_array($Gallery->getParameter('GalleryStatus'),$package->public_gallery_types)){
                    unset($Gallery);
                }
            }
            elseif ($_GET['sid'] != ""){
                $Gallery = $ImageSetContainer->getImageSet($_GET['sid']);
                if ($_GET['pid'] != ""){
                    $Image = $ImageSetContainer->getGalleryImage($_GET['sid'],$_GET['pid']);
                }                
            }
            elseif ($_GET['tags'] != "" and $Bootstrap->packageExists('Tags')){
                $Bootstrap->usePackage('Tags');
                $Gallery = new ImageSet();
                $Gallery->setParameter('ImageSetName',urldecode($_GET['tags']));
                $Gallery->setParameter('GalleryName',urldecode($_GET['tags']));
                $Search = true;
            }
            elseif ($_GET['pid'] != ""){
                $Image = $GalleryImageContainer->getGalleryImage($_GET['pid']);
                if (is_a($Image,'GalleryImage')){
                    $Gallery = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));
                }
            }
            elseif($parms['gid'] != ""){
                $Gallery = $GalleryContainer->getGallery($parms['gid']);
            }
            elseif($parms['gallery_name'] != ""){
                $Gallery = $GalleryContainer->getGalleryByName($parms['gallery_name']);
            }
            elseif($parms['sid'] != ""){
                $Gallery = $ImageSetContainer->getImageSet($parms['sid']);
            }

            if ($parms['base_url'] != ""){
                $this_base_url = $parms['base_url'];            
            }
            else{
                $this_base_url = $package->base_url;
            }
        
            if ($parms['image_size'] != ""){
                $ImageSize = 'GalleryImage'.$parms['image_size'];
            }
            else{
                $ImageSize = 'GalleryImageResized';
            }

            if ($parms['thumb_size'] != ""){
                $ThumbSize = 'GalleryImage'.$parms['thumb_size'];
            }
            else{
                $ThumbSize = 'GalleryImageThumb';
            }
        
            if ($parms['link_to_original'] == 'true' or $_GET['link'] == 'true'){
                $LinkToOriginal = true;
                $this_base_url.= "&link=true";
            }
        
            if ($parms['base_title'] != ""){
                $this_base_title = $parms['base_title'];
            }
            else{
                $this_base_title = $package->base_title;
            }
            
            if (is_a($Image,'GalleryImage')){
                if (is_a($Gallery,'ImageSet')){
                    $GalleryDirectoryObject = &$Image;
                }
                else{
                    $GalleryDirectoryObject = &$Gallery;
                }
            }

			if ($parms['subject'] == 'Index' and (is_a($Image,'GalleryImage') or is_a($Gallery,'ImageSet') or is_a($Gallery,'Gallery'))){
				$parms['subject'] = 'Images';
			}
            

            switch ($parms['subject']){
            
            /****************************************
            *
            *   Breadcrumbs
            *
            ****************************************/    
            case 'Breadcrumbs':
        
                // First the breadcrumbs div
                $return.= '<div id="photo_breadcrumbs"><p>';
                if ($_GET['gid'] == "" and $_GET['pid'] == "" and $_GET['sid'] == "" and $_GET['fid'] == "" and $parms['gid'] == "" and $parms['sid'] == "" and $parms['gallery_name'] == "" and $_GET['tags'] == ""){
                    if ($_GET['slideshow'] == 'true'){
                        $return.= "<a href='".$this_base_url."'>".$this_base_title."</a>";
                    }
                    else{
                        $return.= $this_base_title;
                    }
                }
                else{
                    $return.= "<a href='".$this_base_url."'>".$this_base_title."</a> - ";
            
                    if (is_a($Gallery,'Gallery')){
                        if ($_GET['pid'] != "" or $_GET['slideshow'] == 'true'){
                            $return.= "<a href='".$this_base_url."&amp;gid=".$Gallery->getParameter('GalleryID')."'>".$Gallery->getParameter('GalleryName')."</a>";
                        }
                        else{
                            $return.= $Gallery->getParameter('GalleryName');
                        }
                    }
                    elseif (is_a($Gallery,'ImageSet')){
                        if ($_GET['pid'] != "" or $_GET['slideshow'] == 'true'){
                            $return.= "<a href='".$this_base_url."&amp;sid=".$Gallery->getParameter('ImageSetID')."'>".$Gallery->getParameter('GalleryName')."</a>";
                        }
                        else{
                            $return.= $Gallery->getParameter('ImageSetName');
                        }
                    }
                    else{
                        $return.= "Unknown Gallery";
                    }
                }
                $return.= "</p></div>\n";
        
                // Now the Slideshow div
                $return.= '<div id="photo_slideshow"><p>';
                if ($_GET['slideshow'] == 'true' or !(is_a($Gallery,'Gallery') or is_a($Gallery,'ImageSet'))){
                    $return.= "&nbsp;";
                }
                else{
                    $slideshow_url = $this_base_url."&slideshow=true";
                    if(is_a($Gallery,'Gallery')){
                        $slideshow_url.="&gid=".$Gallery->getParameter('GalleryID');
                    }
                    elseif(is_a($Gallery,'ImageSet')){
                        if ($Search){
                            $slideshow_url.="&tags=".$_GET['tags'];
                        }
                        else{
                            $slideshow_url.="&sid=".$Gallery->getParameter('ImageSetID');
                        }
                    }
                    $return.= "<a href='#' id='slideshow_link'>Slideshow</a>";
                    $return.= "
                    <script type=\"text/javascript\">
            			$('slideshow_link').addEvent('click',function(e){
            			    e.stop();
            			    Milkbox.autoPlay({
            			        gallery: Milkbox.galleries[0],
            			        index: 0
            			    });
            			});
			        </script>
			        ";
                    
                }
                $return.= "</p></div>\n";
        
        
                break;
            case "":
            case "Images":
                // If a $_GET['gid'] parameter is supplied, then we're going to paint the gallery thumbnails
                // If a $_GET['sid'] parameter is supplied, then we're going to paint the image set thumbnails
                // If a $_GET['pid'] parameter is supplied, then we're going to paint that picture
                // If neither are supplied, we'll display thumbnails for all of the galleries.  
        
                if (!is_a($Gallery,'Gallery') and !is_a($Gallery,'ImageSet')){
                    if ($parms['what_to_show'] == 'ImageSets'){
                        $AllGalleries = $ImageSetContainer->getAllImageSets();
                    }
                    else{
                        if (!is_array($package->public_gallery_types)){
                            $gallery_types = array('public');
                        }
                        else{
                            $gallery_types = $package->public_gallery_types;
                        }
                        
                        $AllGalleries = $GalleryContainer->getAllGalleries('GalleryIndex','asc',$gallery_types);
                    }
                    if (!is_array($AllGalleries)){
                        $AllGalleries = array();
                    }
                    $Thumbnails = array();
                    foreach ($AllGalleries as $Gallery){
                        $Image = $Gallery->getPrimaryThumb();
                        if (is_a($Image,'GalleryImage')){
                            if ($parms['what_to_show'] == 'ImageSets'){
                                $Image->setParameter('ImageName',$Gallery->getParameter('ImageSetName'));
                                $Image->setParameter('ImageCaption',$Gallery->getParameter('ImageSetDescription'));
                                $Image->setParameter('ImageLink',$this_base_url."&sid=".$Gallery->getParameter('ImageSetID'));
                                $Image->setParameter('ImageGalleryDirectory',$Image->getGalleryDirectory());
                            }
                            else{
                                $Image->setParameter('ImageName',$Gallery->getParameter('GalleryName'));
                                $Image->setParameter('ImageCaption',$Gallery->getParameter('GalleryDescription'));
                                $Image->setParameter('ImageLink',$this_base_url."&gid=".$Gallery->getParameter('GalleryID'));
                                $Image->setParameter('ImageGalleryDirectory',$Gallery->getGalleryDirectory());
                            }
                            $Thumbnails[] = $Image;
                        }
                    }
                    $Displaying = 'Galleries';
                }
                elseif (!is_a($Image,'GalleryImage')){
                    if ($Search){
                        $TaggedObjectContainer = new TaggedObjectContainer();
            	        $SearchTags = explode(';',stripslashes(urldecode($_GET['tags'])));
                        $Thumbnails = $TaggedObjectContainer->getAllTaggedObjects($SearchTags,'GalleryImage','any');
                    }
                    else{
                        $Thumbnails = $Gallery->getAllGalleryImages();
                    }
                    if (!is_array($Thumbnails)){
                        $Thumbnails = array();
                    }
                    foreach ($Thumbnails as $key => $Image){
                        if (is_a($Gallery,'ImageSet')){
                            $GalleryDirectoryObject = &$Image;
                        }
                        else{
                            $GalleryDirectoryObject = &$Gallery;
                        }
                        $Thumbnails[$key]->setParameter('ImageLink',$this_base_url.(is_a($Gallery,'ImageSet') ? "&sid=".$Gallery->getParameter('ImageSetID') : "")."&pid=".$Image->getParameter('ImageID'));
                        $Thumbnails[$key]->setParameter('ImageGalleryDirectory',$GalleryDirectoryObject->getGalleryDirectory());
                    }
                    if (is_a($Gallery,'ImageSet')){
                        $Name = 'ImageSetName';
                        $Description = 'ImageSetDescription';
                    }
                    else{
                        $Name = 'GalleryName';
                        $Description = 'GalleryDescription';
                    }
                    if ($parms['display_title'] != 'false'){
                        $return.= "<h2 class='GalleryTitle'>".$Gallery->getParameter($Name)."</h2>";
                    }
                    $return.= "<p class='GalleryDescription'>".$Gallery->getParameter($Description)."</p>";
                    $Displaying = 'Images';
                }
                else{
                    if (isset($Thumbnails)){
                        unset($Thumbnails);
                    }
                }
        
            /****************************************
            *
            *   Display Single Image
            *
            ****************************************/    
                if (!isset($Thumbnails)){
                // Display the selected image
                    if (is_a($Image,'GalleryImage')){
                        $return.= "<div align='$TableAlign' style='clear:both'>";
                        $return.= "<table cellpadding='5'><tr>";
                        $ImageCell = "<td valign='top'>";
                        if ($LinkToOriginal){
                            $ImageCell.= "<a href='".$GalleryDirectoryObject->getGalleryDirectory().$Image->getParameter('GalleryImageOriginal')."'>";
                        }
                        $ImageCell.= "<img src='".$GalleryDirectoryObject->getGalleryDirectory().$Image->getParameter($ImageSize)."'>";
                        if ($LinkToOriginal){
                            $ImageCell.= "</a>";
                        }
                        $ImageCell.= "</td>";
                        
                        $CaptionCell = "<td valign='top'>";
                        if ($Image->getParameter('ImageCaption') != ""){
                            $CaptionCell.= "<p>".$Image->getParameter('ImageCaption')."<p>";
                        }
                        $CaptionCell.= "</td>";
                        switch ($parms['caption_placement']){
                        case 'top':
                            $return.= $CaptionCell."</tr><tr>".$ImageCell;
                            break;
                        case 'left':
                            $return.= $CaptionCell.$ImageCell;
                            break;
                        case 'bottom':
                            $return.= $ImageCell."</tr><tr>".$CaptionCell;
                            break;
                        default:
                            $return.= $ImageCell.$CaptionCell;
                            break;
                        }
                        $return.= "</tr></table>";
                        $return.= "</div>";
                    }    
                }
            /****************************************
            *
            *   Display the thumbnails
            *
            ****************************************/    
                else{
                    // Display the thumbnails
                    $smarty->assign('Displaying',$Displaying);
                    $smarty->assign('Thumbnails',$Thumbnails);
                    $smarty->assign('ThumbnailsCount',count($Thumbnails));
                    $smarty->assign('parms',$parms);
					$smarty->assign('gallery_base',CMS_INSTALL_URL);
                    $return.= $smarty->fetch(dirname(__FILE__)."/smarty/gallery.thumbnails.tpl");
                }    
                break;
            case 'Slideshow':
                switch ($parms['table_align']){
                case 'right':
                    $style = "float:right;margin-left:10px;";
                    break;
                case 'center':
                    $style = "text-align:center;";
                    break;
                case 'left':
                default:
                    $style = "float:left;margin-right:10px;";
                    break;
                }
                
                if (is_numeric($parms['width'])){
                    $width = intval($parms['width']);
                }
                else{
                    $width = '300';
                }
                
                if (is_numeric($parms['height'])){
                    $height = intval($parms['height']);
                }
                else{
                    $height = '200';
                }
                
                if ($parms['gid'] != ""){
                    $gid_string = "gid,".$parms['gid'].",";
                }
                elseif ($parms['sid'] != ""){
                    $gid_string = "sid,".$parms['sid'].",";
                }
                elseif ($parms['festival'] != ""){
                    $gid_string = "festival,".$parms['festival'].",";
                }
                else{
                    $gid_string = "";
                }
                
				$Thumb = $Gallery->getPrimaryThumb();
				$size = 'GalleryImageResized'; //($width > RESIZED_WIDTH || $height > RESIZED_HEIGHT ? 'GalleryImageOriginal' : 'GalleryImageResized')
				$no_flash_content = '<img src="'.$Thumb->getGalleryDirectory().$Thumb->getParameter($size).'" style="max-width:'.$width.'px;max-height:'.$height.'px;">';

				$swf_path = CMS_INSTALL_URL."lib/packages/Gallery/monoslideshow/monoslideshow.swf"; //CMS_INSTALL_DOC_BASE.'lib/packages/Gallery/monoslideshow/monoslideshow.swf';
				$swf_path = apply_filters('monoslideshow_url',$swf_path);
                if (!file_exists(str_replace(get_bloginfo('wpurl'),ABSPATH,$swf_path))){
					$return = "Using the TopQuark slideshow feature currently requires the installation of MonoSlideshow - a non-GPL piece of code.  It can be found at <a href=\"http://www.monoslideshow.com/\" target=\"_blank\">www.monoslideshow.com/</a>.<br/><br/>Once you purchase it, upload the .swf to your Media library and note the uploaded URL (i.e. http://mysite.com/wp-content/uploads/2011/06/monoslideshow.swf).  Then, use the filter 'path_to_monoslideshow' to set the url:<br/><code>add_filter('monoslideshow_url',create_function('\$path','return \"http://mysite.com/wp-content/uploads/2011/06/monoslideshow.swf\";'));</code><br/><br/>Very sorry for the inconvenience.  This issue will be addressed in a later release.";
				}
				else{
	                $UniqueKey = rand();
					static $FirstSlideshow;
					if (!isset($FirstSlideshow)){
						$FirstSlideshow = true;
					}
					if ($FirstSlideshow){
	                    $return = "<script src='".CMS_INSTALL_URL."lib/packages/Gallery/monoslideshow/swfobject.js' type='text/javascript'></script>";
						$FirstSlideshow = false;
					}
					else{
						$return = "";
					}
	                $return.= '
						<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'.$width.'" height="'.$height.'" id="monoSlideshow'.$UniqueKey.'_wrapper" style="'.$style.'">
							<param name="movie" value="'.$swf_path.'" />
							<param name="bgcolor" value="#FFFFFF">
							<param name="wmode" value="opaque">
							<param name="menu" value="false">
							<param name="flashvars" value="dataFile='.CMS_INSTALL_URL.'lib/packages/Gallery/monoslideshow/my_xml.php?parms=startWith,photos,'.$gid_string.'simple,true&amp;showLogo=false">
							<!--[if !IE]>-->
							<object type="application/x-shockwave-flash" data="'.$swf_path.'" width="'.$width.'" height="'.$height.'" id="monoSlideshow'.$UniqueKey.'">
								<param name="bgcolor" value="#FFFFFF">
								<param name="wmode" value="transparent">
								<param name="menu" value="false">
								<param name="flashvars" value="dataFile='.CMS_INSTALL_URL.'lib/packages/Gallery/monoslideshow/my_xml.php?parms=startWith,photos,'.$gid_string.'simple,true&amp;showLogo=false">
							<!--<![endif]-->
								'.$no_flash_content.'
							<!--[if !IE]>-->
							</object>
							<!--<![endif]-->
						</object>
						<script type="text/javascript">
							swfobject.registerObject("monoSlideshow'.$UniqueKey.'_wrapper", "8");
						</script>
						';
						
				}
                break;
            case 'Index':
                $AllGalleries = $GalleryContainer->getAllGalleries('GalleryDescription','desc');
                if (is_array($AllGalleries)){
                    foreach ($AllGalleries as $Gallery){
                        if ($Gallery->getParameter('GalleryStatus') == 'public'){
                            if (preg_match("/^([0-9\-]*)/",$Gallery->getParameter('GalleryDescription'),$matches)){
                                $Gallery->setParameter('GalleryYear',$matches[1]);
                            }
							else{
                                $Gallery->setParameter('GalleryYear','Unknown Year');
							}
                            if ($Gallery->getParameter('GalleryYear') != $CurrentYear){
                                if ($CurrentYear != ""){
				                    $smarty->assign('Displaying','Galleries');
				                    $smarty->assign('Thumbnails',$Thumbnails);
				                    $smarty->assign('ThumbnailsCount',count($Thumbnails));
				                    $smarty->assign('parms',$parms);
									$smarty->assign('gallery_base',CMS_INSTALL_URL);
				                    $return.= $smarty->fetch(dirname(__FILE__)."/smarty/gallery.thumbnails.tpl");
                                }
                                $return.=  "<h2 style='margin-bottom:0px;margin-top:15px'>".$Gallery->getParameter('GalleryYear')."</h2>\n";
			                    $Thumbnails = array();
                                //$return.=  "<ul style='margin-top:0px'>\n";
                                $CurrentYear = $Gallery->getParameter('GalleryYear');
                            }
                            //$return.=  "<li>\n";
	                        $Image = $Gallery->getPrimaryThumb();
	                        if (is_a($Image,'GalleryImage')){
	                            if ($parms['what_to_show'] == 'ImageSets'){
	                                $Image->setParameter('ImageName',$Gallery->getParameter('ImageSetName'));
	                                $Image->setParameter('ImageLink',$this_base_url."&sid=".$Gallery->getParameter('ImageSetID'));
	                                $Image->setParameter('ImageGalleryDirectory',$Image->getGalleryDirectory());
	                            }
	                            else{
	                                $Image->setParameter('ImageName',$Gallery->getParameter('GalleryName'));
	                                $Image->setParameter('ImageLink',$this_base_url."&gid=".$Gallery->getParameter('GalleryID'));
	                                $Image->setParameter('ImageGalleryDirectory',$Gallery->getGalleryDirectory());
	                            }
	                            $Thumbnails[] = $Image;
	                        }
							
                        }
                    }
                    if ($CurrentYear != ""){
	                    $smarty->assign('Displaying','Galleries');
	                    $smarty->assign('Thumbnails',$Thumbnails);
	                    $smarty->assign('ThumbnailsCount',count($Thumbnails));
	                    $smarty->assign('parms',$parms);
						$smarty->assign('gallery_base',CMS_INSTALL_URL);
	                    $return.= $smarty->fetch(dirname(__FILE__)."/smarty/gallery.thumbnails.tpl");
                    }
                }
                break;
            default:
                $return = "Don't know how to paint ".$parms['subject'];
            }
        
            return $return;
        }

		function Gallery__UserAjax($parms,$package,&$smarty){
	        $result = array();
			$result['retain'] = array();
	
	        if (isset($_FILES['photoupload']) )
	        {
				$GalleryContainer = new GalleryContainer();
				$GalleryImageContainer = new GalleryImageContainer();

				$Gallery = $GalleryContainer->getGallery($parms['id']);
				$wc = new whereClause();
				$wc->addCondition($GalleryImageContainer->getColumnName('GalleryID').' = ?',$parms['id']);
				
				$NumImages = $GalleryImageContainer->countAllObjects($wc);
	            /************************************************************
	            *  Uploading an image
	            ************************************************************/
	        	$NewImageSource = $_FILES['photoupload']['tmp_name'];
	        	$NewImageName = stripslashes($_FILES['photoupload']['name']);
	        	$error = false;
	        	$size = false;

	            if ($NewImageSource != "" AND $NewImageSource !="none"){
	                    if (PEAR::isError($NewImage = $Gallery->addUploadedGalleryImage($NewImageSource,$NewImageName))){

	                        $error = $NewImage->getMessage();
	                    }
	                    else{
	                        if (!is_array($NewImage)){
	                                $NewImage = array($NewImage);
	                        }
	                        $numNewImages = 0;
	                        foreach ($NewImage as $_Image){
	                            if ($NumImages == 0){
				                    $_Image->setParameter('PrimaryThumb',true);
	                            }
	                            else{
				                    $_Image->setParameter('PrimaryThumb',false);
	                            }
	                            if (preg_match("/(.*)\.[^\.]*/",$_Image->getParameter('GalleryImageOriginal'),$matches)){
	                                $_Image->setParameter('ImageTitle',$matches[1]);
	                            }
	                            else{
	                                $_Image->setParameter('ImageTitle',$_Image->getParameter('GalleryImageOriginal'));
	                            }
				                $_Image->setParameter('ImageIndex',$NumImages + 1);
	            				if (PEAR::isError($e = $GalleryImageContainer->updateGalleryImage($_Image))){
	                                $error = $e->getMessage();
	            				}
	                            else{
	                                $_Image->setParameter('isNewImage',true);
	                                $NumImages++;
	                                $numNewImages++;
	                            }
	                        }
	    	            }
	            }

	        	if ($error)
	        	{
	        		$result['result'] = 'failed';
	        		$result['message'] = $error;
	        	}
	        	else
	        	{
	        		$result['result'] = 'success';
	        		$result['retain']['size'] = "Uploaded an image"; // Spoof to deliver a message
	        		$result['retain']['number'] = $numNewImages;
	        	}

	        }
	        else
	        {
	            /************************************************************
	            *  Some other action
	            ************************************************************/
				switch ($_GET['action']){
				case 'reorder':
					if (isset($_GET['sid'])){
						$WorkingWith = 'ImageSet';
						$ImageSetContainer = new ImageSetContainer();
						$ImageSetImageContainer = new ImageSetImageContainer();
						$Gallery = $ImageSetContainer->getImageSet($_GET['sid']);
					}
					else{
						$WorkingWith = 'Gallery';
						$GalleryContainer = new GalleryContainer();
						$GalleryImageContainer = new GalleryImageContainer();
						$Gallery = $GalleryContainer->getGallery($_GET['id']);
					}
	                switch ($WorkingWith){
	                case "Gallery":
	                    $GalleryImageContainer->setImageIndex($Gallery->getParameter('GalleryID'),$_GET['image_id'],$_GET['index']);
	                    break;
	                case "ImageSet":
	                    $ImageSetImageContainer->setImageIndex($Gallery->getParameter('ImageSetID'),$_GET['image_id'],$_GET['index']);
	                    break;
	                }
	                $result['result'] = 'success';
					break;
				case 'resize':
					$GalleryContainer = new GalleryContainer();
		            $Gallery = $GalleryContainer->getGallery($_GET['id']);
					$result['message'] = 'id: '.$_GET['id'];
		            if (is_a($Gallery,'Gallery')){
		                $Images = $Gallery->getAllGalleryImages();
		                $result['retain']['total_images'] = count($Images);
		                $Images = array_slice($Images,$_GET['offset'],$_GET['limit']);

		                foreach ($Images as $Image){
		                    $Image->reCheckInImage();
		                }
		                $result['retain']['images_processed'] = count($Images);
		            }
		            $result['result'] = 'success';
		            break;
				}
	        }
	
			return $result;

		}
		
        function setFunctionParameters(){    
            
            $Subject = new FunctionParameter('paint','subject');
            $Subject->setParameterName('Subject');
            $Subject->setParameterDescription('The item you wish to paint');
            $Subject->addParameterValues(array('Images' => 'Images', 'Index' => 'Index', 'Breadcrumbs' => 'Navigation & Breadcrumbs', 'Slideshow' => 'Embed Slideshow'));
            $Subject->setParameterDefaultValue('Images');
                        
            $GalleryContainer = new GalleryContainer();
            $Bootstrap= Bootstrap::getBootstrap();
            $Package = $Bootstrap->usePackage($this->getPackage());
            if (!is_array($Package->gallery_types)){
                $Package->gallery_types = array('public','private');
            }
            
            $AllGalleries = $GalleryContainer->getAllGalleries('GalleryName','asc',$Package->gallery_types);
            $GalleriesParm = new FunctionParameter('paint','gid');
            $GalleriesParm->setParameterName('A specific gallery (optional)');
            $GalleriesParm->addParameterValues(array('' => '&lt;Choose from your galleries&gt;'));
            $GalleriesParm->setParameterDefaultValue('');
            foreach ($AllGalleries as $Gallery){
                $GalleriesParm->addParameterValues(array($Gallery->getParameter('GalleryID') => $Gallery->getParameter('GalleryName')));
            }
            
            $ImageSetContainer = new ImageSetContainer();
            $AllGalleries = $ImageSetContainer->getAllImageSets();
            $ImageSetsParm = new FunctionParameter('paint','sid');
            $ImageSetsParm->setParameterName('A specific image set (optional)');
            $ImageSetsParm->addParameterValues(array('' => '&lt;Choose from your Image Sets&gt;'));
            $ImageSetsParm->setParameterDefaultValue('');
            foreach ($AllGalleries as $Gallery){
                $ImageSetsParm->addParameterValues(array($Gallery->getParameter('ImageSetID') => $Gallery->getParameter('ImageSetName')));
            }
            
            
            $BaseTitleParm = new FunctionParameter('paint','base_title');
            $BaseTitleParm->setParameterName('Base Title (optional)');
            
            $ThumbSize = new FunctionParameter('paint','thumb_size');
            $ThumbSize->setParameterName('Thumb Size');
            $ThumbSize->addParameterValues(array('' => '&lt;Keep Default&gt;', 'Thumb' => 'Thumb (Default)', 'Resized' => 'Resized', 'Original' => 'Original'));
            $ThumbSize->setParameterDefaultValue('');
            $ImageSize = new FunctionParameter('paint','image_size');
            $ImageSize->setParameterName('Expanded Image Size');
            $ImageSize->addParameterValues(array('' => '&lt;Keep Default&gt;', 'Thumb' => 'Thumb', 'Resized' => 'Resized (Default)', 'Original' => 'Original'));
            $ImageSize->setParameterDefaultValue('');
            $LinkToOriginal = new FunctionParameter('paint','link_to_original');
            $LinkToOriginal->setParameterName('Link expanded image to original');
            $LinkToOriginal->addParameterValues(array('' => '&lt;Keep Default&gt;', 'true' => 'True', 'false' => 'False (Default)'));
            $CaptionPlacement = new FunctionParameter('paint','caption_placement');
            $CaptionPlacement->setParameterName('Caption placement on expanded image');
            $CaptionPlacement->addParameterValues(array('' => '&lt;Keep Default&gt;', 'left' => 'Left', 'right' => 'Right (Default)','top' => 'Top', 'bottom' => 'Bottom'));
            $WhatToShow = new FunctionParameter('paint','what_to_show');
            $WhatToShow->setParameterName('What to show');
            $WhatToShow->addParameterValues(array('' => '&lt;Keep Default&gt;', 'Galleries' => 'Galleries (Default)', 'ImageSets' => 'Image Sets'));
            
            $ShowFestivalParm = false;
            $Bootstrap = Bootstrap::getBootstrap();
            if ($Bootstrap->packageExists('FestivalApp')){
                $FestivalPackage = $Bootstrap->usePackage('FestivalApp');
                $FestivalContainer = new FestivalContainer();
                $AllFestivals = $FestivalContainer->getAllFestivals();
                $FestivalsParm = new FunctionParameter('paint','festival');
                $FestivalsParm->setParameterName('A specific festival (optional)');
                $FestivalsParm->addParameterValues(array('' => '&lt;Choose from your festivals&gt;'));
                $FestivalsParm->setParameterDefaultValue('');
				if (is_array($AllFestivals)){
	                foreach ($AllFestivals as $Festival){
	                    if ($Festival->getParameter('FestivalLineupIsPublished')){
	                        $ShowFestivalParm = true;
	                        $FestivalsParm->addParameterValues(array($Festival->getParameter('FestivalYear') => $Festival->getParameter('FestivalYear')));
	                    }
	                }            
				}
            }
            
            $Width = new FunctionParameter('paint','width');
            $Width->setParameterName('Width (default 300px)');
            $Height = new FunctionParameter('paint','height');
            $Height->setParameterName('Height (default 200px)');
            

            $Subject->addDependentParameter($BaseTitleParm,'Breadcrumbs');
            //$Subject->addDependentParameter($ThumbsPerRow,array('Images'));
            //$Subject->addDependentParameter($RowsPerPage,array('Images'));
            $Subject->addDependentParameter($ThumbSize,array('Images'));
            $Subject->addDependentParameter($ImageSize,array('Images'));
            $Subject->addDependentParameter($LinkToOriginal,array('Images'));
            $Subject->addDependentParameter($CaptionPlacement,array('Images'));
            $Subject->addDependentParameter($WhatToShow,array('Images','Breadcrumbs'));
            $Subject->addDependentParameter($GalleriesParm,array('Images','Breadcrumbs','Slideshow'));
            $Subject->addDependentParameter($ImageSetsParm,array('Images','Breadcrumbs','Slideshow'));
            if ($ShowFestivalParm){
                $Subject->addDependentParameter($FestivalsParm,array('Slideshow'));
            }
            $Subject->addDependentParameter($Width,array('Slideshow'));
            $Subject->addDependentParameter($Height,array('Slideshow'));
            
            $this->addFunctionParameter($Subject);
            
            $BaseURL = new FunctionParameter('paint','base_url');
            $BaseURL->setParameterName('Base URL (optional)');
            $this->addFunctionParameter($BaseURL);
            
            $TableAlign = new FunctionParameter('paint','table_align');
            $TableAlign->setParameterName('Alignment');
            $TableAlign->addParameterValues(array('' => '&lt;Keep Default&gt;', 'left' => 'Left (Default)', 'center' => 'Center', 'right' => 'Right'));
            $TableAlign->setParameterDefaultValue('');
            $this->addFunctionParameter($TableAlign);
        }
  }
?>