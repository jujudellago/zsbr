<?php
    include_once("../../conf.php");  // from the LIB directory
    
    include_once(dirname(__FILE__)."/conf.php");
    
    $Package = new GalleryPackage();
    
    $GalleryImageContainer = new GalleryImageContainer();
    $GalleryContainer = new GalleryContainer();
    
    $Image = $GalleryImageContainer->getGalleryImage($_GET['id']);
    if (is_a($Image,'GalleryImage')){
        $Gallery = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));
        $file = $Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImage'.$_GET['size']);
        if (file_exists($file)){
            header("Location:".$Gallery->getGalleryDirectory().$Image->getParameter('GalleryImage'.$_GET['size']));
            exit();
            $imagesize = getimagesize($file);
            header("Content-disposition: inline; filename=\"".$Image->getParameter('GalleryImage'.$_GET['size'])."\"");
            header("Content-Type: " . $imagesize['mime']);
            readfile($file);
            exit();
        }
    }
    

?>