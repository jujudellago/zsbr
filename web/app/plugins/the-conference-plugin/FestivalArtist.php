<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");
require_once("MediaContainer.php");
require_once("Artist.php");

class FestivalArtist extends Parameterized_Object
{
	function FestivalArtist($ArtistID = '',$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('ArtistID',$ArtistID);	

		$this->setIDParameter('ArtistID');
		$this->setNameParameter('ArtistID');
		
        require_once(PACKAGE_DIRECTORY."../Standard.php");
		$Bootstrap = Bootstrap::getBootstrap();
        $Bootstrap->usePackage('Gallery');
	}
	
	function parameterizeAssociatedMedia(){
	    $MediaContainer = new MediaContainer();
	    $GalleryImageContainer = new GalleryImageContainer();
	    $GalleryContainer = new GalleryContainer();
	    
	    $Objects = array(new Artist($this->getParameter('ArtistID')),new FestivalArtist($this->getParameter('ArtistID').'_'.$this->getParameter('FestivalYear')));
	    
	    // this loop is setup in such a way so the Year specific media overrides the default media
	    foreach ($Objects as $ArtistObject){
            $AllMedia = $MediaContainer->getAssociatedMedia($ArtistObject);
            if (!$AllMedia){
                    $AllMedia = array();
            }
            
    	    $AssociatedImages = array();
    	    $AssociatedMedia = array();
            
            foreach ($AllMedia as $Media){
                switch($Media->getParameter('MediaType')){
                case MEDIA_TYPE_GALLERYIMAGE:
                        $Image = $GalleryImageContainer->getGalleryImage($Media->getParameter('MediaLocation'));
                        if ($Image){
                            $Gallery = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));
                            $AssociatedImages[] = 
                                array('Thumb' => $Gallery->getGalleryDirectory().$Image->getParameter("GalleryImageThumb"), 
                                      'Resized' => $Gallery->getGalleryDirectory().$Image->getParameter("GalleryImageResized"),
                                      'Original' => $Gallery->getGalleryDirectory().$Image->getParameter("GalleryImageOriginal"));
                        }
                        break;
                case MEDIA_TYPE_AUDIO:
                case MEDIA_TYPE_MP3:    
                        $AssociatedMedia[] = $Media->getParameter("MediaLocation");
                        break;
                }
            }
            
            if (count($AssociatedImages)){
                $this->setParameter('ArtistAssociatedImages',$AssociatedImages);
            }
            if (count($AssociatedMedia)){
                $this->setParameter('ArtistAssociatedMedia',$AssociatedMedia);
            }
        }
	}
	
	function getAssociatedMedia(){
	    $MediaContainer = new MediaContainer();
	    $GalleryImageContainer = new GalleryImageContainer();
	    
	    $Objects = array(new Artist($this->getParameter('ArtistID')),new FestivalArtist($this->getParameter('ArtistID').'_'.$this->getParameter('FestivalYear')));
	    
	    // this loop is setup in such a way so the Year specific media overrides the default media
	    $returnAssociatedImages = array();
	    $returnAssociatedMedia = array();
	    foreach ($Objects as $ArtistObject){
            $AllMedia = $MediaContainer->getAssociatedMedia($ArtistObject);
            if (!$AllMedia){
                    $AllMedia = array();
            }
            
    	    $AssociatedImages = array();
    	    $AssociatedMedia = array();
            
            foreach ($AllMedia as $Media){
                switch($Media->getParameter('MediaType')){
                case MEDIA_TYPE_GALLERYIMAGE:
                        $Image = $GalleryImageContainer->getGalleryImage($Media->getParameter('MediaLocation'));
                        if ($Image){
                            $AssociatedImages[$Image->getParameter('ImageID')] = $Image;
                        }
                        break;
                case MEDIA_TYPE_AUDIO:
                case MEDIA_TYPE_MP3:    
                        $AssociatedMedia[] = $Media->getParameter("MediaLocation");
                        break;
                }
            }
            
            if (count($AssociatedImages)){
                $returnAssociatedImages = $AssociatedImages;
            }
            if (count($AssociatedMedia)){
                $returnAssociatedMedia = $AssociatedMedia;
            }
        }
        return array('Images' => $returnAssociatedImages, 'Media' => $returnAssociatedMedia);
	}
}
?>