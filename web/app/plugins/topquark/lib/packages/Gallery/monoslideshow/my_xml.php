<?php 
    header ("content-type: text/xml");  
    
    
    @include_once(dirname(__FILE__)."/../../../Standard.php");
    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
    $Package = $Bootstrap->usePackage('Gallery'); 
    $GalleryContainer = new GalleryContainer();
    
    // For whatever reason, the SWF doesn't accept ?var1=one&var2=two" in the URL (it accepts the first, but not the others)
    // So, to get around that, the variables get passed in as a GET parm called 'parms' in the form
    //     ?parms=var1,one,var2,two,...
    if ($_GET['parms'] != ""){
        $URLparms = explode(",",$_GET['parms']);
        $parms = array();
        for ($i = 0; $i < count($URLparms); $i+= 2){
            $parms[$URLparms[$i]] = $URLparms[$i + 1];
        }
    }
    
    
?>

<!--
	Monoslideshow 1.32 configuration file
	Please visit http://www.monoslideshow.com for more info
-->

<slideshow>
	
	<preferences
	    imageTransition = "blend"
		imagePause = "4"
		showControls = "<?php echo $parms['simple'] == 'true' ? 'false' : 'true'; ?>"
		showImageInfo = "<?php echo $parms['simple'] == 'true' ? 'ifAvailable' : 'ifAvailable'; ?>"
		linkToImage = "<?php echo $parms['simple'] == 'true' ? 'false' : 'true'; ?>"
		controlAlign = "topRight"
		imageScaleMode = "<?php echo ($parms['imageScaleMode'] != "" ? $parms['imageScaleMode'] : ($parms['simple'] == 'true' ? 'scaleToFit' /*'scaleToFill' */ : 'scaleToFit')); ?>"
		imageInfoTextAlign = "center"
		imageInfoRoundedCorners = "2"
		imageInfoAlign = "bottomCenter"
		imageInfoMarginX = "0"
		imageInfoMarginY = "0"
		imageInfoAlpha = "0"
		imageInfoDescriptionColor = "<?php echo ($parms['text_colour'] != '' ? $parms['text_colour'] : ($parms['simple'] == 'true' ? 'FFd810' : '000000')); ?>"
		imageInfoTitleColor = "<?php echo ($parms['text_colour'] != '' ? $parms['text_colour'] : ($parms['simple'] == 'true' ? 'FFd810' : '000000')); ?>"
		imageInfoTitleContainsNumber = "false"
		showLoadingIcon = "<?php echo $parms['simple'] == 'true' ? 'false' : 'true'; ?>"
		backgroundColor = "<?php echo ($Package->slideshow_background != "" ? $Package->slideshow_background : "ffffff"); ?>"
		thumbnailWidth = "<?php echo THUMB_WIDTH; ?>"
		thumbnailHeight = "<?php echo THUMB_HEIGHT; ?>"
		albumWidth = "<?php echo THUMB_WIDTH; ?>"
		albumHeight = "<?php echo THUMB_HEIGHT; ?>"
		albumInfoHeight = "<?php echo THUMB_HEIGHT; ?>"
		thumbnailBrightnessAdjustment = "0"
		thumbnailHoverBrightnessAdjustment = "0"
		albumHoverBrightnessAdjustment = "0"
		<?php
		    if ($parms['startWith'] != ""){
		        $StartWith = $parms['startWith'];
		    }
		    else{
		        $StartWith = "albumsThenThumbnails";
    		}
		?>
		startWith = "<?php echo $StartWith; ?>"
		backgroundMusicFadeIn = "<?php echo $parms['simple'] == 'true' ? 'false' : 'true'; ?>"
		autoPause = "false"
	/>

<?php	

    if ($parms['gid'] != ""){
        $Gallery = $GalleryContainer->getGallery($parms['gid']);
        if (is_a($Gallery,'Gallery')){
            $Images = $Gallery->getAllGalleryImages();
            $GalleryTitle = $Gallery->getParameter('GalleryName');
            //$GalleryDescription = $Gallery->getParameter('GalleryDescription');
            $Galleries = array($Gallery->getParameter('GalleryID') => $Gallery);
            $Primary = $Gallery->getPrimaryThumb();
            $Primary->setParameter('GalleryDirectory',$Gallery->getGalleryDirectory());
        }
    }
    elseif ($parms['sid'] != ""){
        $ImageSetContainer = new ImageSetContainer();
        $Gallery = $ImageSetContainer->getImageSet($parms['sid']);
        if (is_a($Gallery,'ImageSet')){
            $Images = $Gallery->getAllGalleryImages();
            $GalleryTitle = $Gallery->getParameter('ImageSetName');
            $GalleryDescription = $Gallery->getParameter('ImageSetDescription');
            $Galleries = array($Gallery->getParameter('ImageSetID') => $Gallery);
            $Primary = $Gallery->getPrimaryThumb();
            $Primary->setParameter('GalleryDirectory',$Primary->getGalleryDirectory());
        }
    }
    elseif ($parms['festival'] != ""){
        $Galleries = array();
        $Images = array();
        $Bootstrap->usePackage('FestivalApp');
        $FestivalContainer = new FestivalContainer();
        $MediaContainer = new MediaContainer();
        $Festival = $FestivalContainer->getFestival($parms['festival']);
        $Lineup = $Festival->getLineup();
        foreach ($Lineup as $Performer){
            $AssociatedMedia = $Performer->getAssociatedMedia();
            $AssociatedImages = $AssociatedMedia['Images'];
            if (is_array($AssociatedImages)){
                foreach ($AssociatedImages as $Image){
                    if (!$Primary){
                        $Primary = $Image;
                    }
                    $Images[$Image->getParameter('ImageID')] = $Image;
                    if (!array_key_exists($Image->getParameter('GalleryID'),$Galleries)){
                        $Galleries[$Image->getParameter('GalleryID')] = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));
                    }
                }
            }
        }
    }
    else{
        $Galleries = $GalleryContainer->getAllGalleries();
        $GalleryTitle = "All images on the website";
        $Images = array();
        foreach ($Galleries as $Gallery){
            if (!$Primary){
                $Primary = $Gallery->getPrimaryThumb();
                $Primary->setParameter('GalleryDirectory',$Gallery->getGalleryDirectory());
            }
            $Images = array_merge($Images,$Gallery->getAllGalleryImages());
        }
    }

    if ($parms['playlist'] != ""){
        if ($Bootstrap->packageExists('flamplayer')){
            $Bootstrap->usePackage('flamplayer');
        }
        else{
            $parms['playlist'] = "";
        }
    }
    
    // Get Songs
    $showMuteButton = 'false';
    $BackgroundMusic = "";
    if ($parms['playlist'] != ""){
        $SongContainer = new SongContainer();
        if ($parms['playlist'] == 'random'){
            $Songs = $SongContainer->getRandomSongs(5);
        }
        else{
            $Songs = $SongContainer->getSongsInPlaylist($parms['playlist']);
        }
        if (is_array($Songs) and count($Songs)){
            shuffle($Songs);
            $showMuteButton = 'true';
            $BackgroundMusic = "backgroundMusic=\"";
            $sep = "";
            foreach ($Songs as $Song){
                $BackgroundMusic.= $sep.$SongContainer->getMusicDirectory().$Song->getParameter('SongFileName');
                $sep = ",";
            }
            $BackgroundMusic.= "\"";
        }
    }

    if (is_a($Primary,'GalleryImage')){
        $PrimaryURL = $Primary->getParameter('GalleryDirectory').$Primary->getParameter('GalleryImageThumb');
    }
    if ($parms['gid'] != "" and is_a($Gallery,'Gallery')){
        $GalleryDirectory = $Gallery->getGalleryDirectory();
    }
    echo "<album thumbnail=\"$PrimaryURL\" showMuteButton=\"$showMuteButton\" $BackgroundMusic title=\"".htmlspecialchars($GalleryTitle)."\" description=\"".htmlspecialchars($GalleryDescription)."\" imagePath=\"$GalleryDirectory\" thumbnailPath=\"$GalleryDirectory\">\n";    
    if (is_array($Images)){
        foreach ($Images as $Image){
        	$description = "";
        	if ($Image->getParameter('ImageCredit') != "" and $parms['simple'] != 'true'){
        	    $description.= "\n\nPhoto by: ".$Image->getParameter('ImageCredit').", ".$Image->getParameter('ImageYear');
        	}
        	if ($Image->getParameter('ImageCaption') != ""){
        	    $title = $Image->getParameter('ImageCaption');
        	}
        	else{
        	    $title = " ";
        	}
        	$LinkPath = "";
            if (is_a($Gallery,'ImageSet')){
                $GalleryDirectory = $Image->getGalleryDirectory();
            }
            elseif ($parms['gid'] == ""){
                $GalleryDirectory = $Galleries[$Image->getParameter('GalleryID')]->getGalleryDirectory();
            }
            else{
                $GalleryDirectory = "";
                $LinkPath = $Galleries[$Image->getParameter('GalleryID')]->getGalleryDirectory();
            }
            if ($LinkPath == ""){
                $LinkPath = $GalleryDirectory;
            }
            echo "\t<img src=\"".$GalleryDirectory.$Image->getParameter('GalleryImageResized')."\" title=\"".htmlspecialchars($title)."\" description=\"".htmlspecialchars($description)."\" thumbnail=\"".$GalleryDirectory.$Image->getParameter('GalleryImageThumb')."\" ".($parms['simple'] == 'true' ? '' : "link=\"".$LinkPath.$Image->getParameter('GalleryImageOriginal')."\"")." target=\"_blank\"/>\n";
        }
    }

	echo "</album>\n";
    
?>
	
</slideshow>