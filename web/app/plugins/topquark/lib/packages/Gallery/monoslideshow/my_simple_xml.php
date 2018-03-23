<?php 
    header ("content-type: text/xml");  
    
    
    include_once(dirname(__FILE__)."/../../../Standard.php");
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
		imagePause = "6"
		startWith = "photos"
		showControls = "false"
		imageScaleMode = "scaleToFit"
		showImageInfo = "onRollOverIfAvailable"
		imageInfoDescriptionSize  = "8"
		imageInfoTextAlign = "center"
		imageInfoRoundedCorners = "2"
		imageInfoAlign = "topCenter"
		imageInfoMarginX = "0"
		imageInfoMarginY = "0"
		imageInfoAlpha = "0"
		imageInfoDescriptionColor = "000000"
		showLoadingIcon = "false"
		backgroundColor = "000000"
	/>

<?php	

    $Galleries = array();
    $Gallery = $GalleryContainer->getGalleryByName($parms['gallery_name']);
    if ($Gallery){
        $Images = $Gallery->getAllGalleryImages();
        $Galleries[$Gallery->getParameter('GalleryID')] = $Gallery;
    }

    echo "<album thumbnail=\"$PrimaryURL\" title=\"\" description=\"\" imagePath=\"\" thumbnailPath=\"\">\n";    
    if (is_array($Images)){
        shuffle($Images);
        $pattern = "/<a href='([^']*)' target='_blank'>([^<]*)<\/a>/i";
        foreach ($Images as $Image){
            $GalleryDirectory = $Galleries[$Image->getParameter('GalleryID')]->getGalleryDirectory();
            if (strpos($Image->getParameter('GalleryImageResized'),".gif")){
                $src = "lib/packages/".$Package->package_name."/gif2jpg.php?src=".urlencode($Galleries[$Image->getParameter('GalleryID')]->getDirectoryName().$Image->getParameter('GalleryImageOriginal'));
            }
            else{
                $src = $GalleryDirectory.$Image->getParameter('GalleryImageResized');
            }
            
            echo "\t<img src=\"$src\" title=\"\" description=\"\" link=\"\"/>\n";
        }
    }

	echo "</album>\n";
    
?>
	
</slideshow>