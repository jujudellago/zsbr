<?php

require_once(PACKAGE_DIRECTORY."ImportExport/Exporter.php");

class ArtistExporter extends Exporter{
    
    function ArtistExporter(){
		$this->Exporter();
		$this->default_encoding = 'UTF-8';
		$this->default_delimiter = 'comma';
		
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('FestivalApp');
        
		$this->parameterPrefix = 'Artist';
		$this->manufacturer = 'manufactureFestivalArtist';
        $this->setContainer('FestivalArtistContainer');
        
        $this->ignoreParameter(array(
			'ArtistID',
			'ArtistIsActive',
			'ArtistGuests',
			'ArtistModifiedTimestamp',
			'ArtistFullName',
			'FestivalYear',
			'ArtistBand',
			'ArtistFee',
			'ArtistFeeDescription',
			'ArtistAccommodationDescription',
			'ArtistTechnicalRequirements',
			'ArtistDoNotPublish',
			'LineupOrder',
			'ArtistDefaultDescription',
			'ArtistDefaultLongDescription',
			'ArtistDefaultProgramDescription'
		));
		$this->sort_parms = array('LineupOrder');
		$this->sort_dir = array('asc');
		
		$this->addFilterParameter('FestivalYear');
		$this->addExtraParameter('AllFestivalYears','ArtistImage','Year','MemberOf');
		
		$this->MediaContainer = new MediaContainer();
		$this->GalleryImageContainer = new GalleryImageContainer();
		$this->GalleryContainer = new GalleryContainer();
    }
    
    function massageData(& $Object){
		$Object->setParameter('Year',$Object->getParameter('FestivalYear'));
		// Find the thumbnail
        $AllMedia = $this->MediaContainer->getAssociatedMedia(new Artist($Object->getParameter('ArtistID')));
        if (!$AllMedia) $AllMedia = array();

        foreach ($AllMedia as $Media){
            switch($Media->getParameter('MediaType')){
            case MEDIA_TYPE_GALLERYIMAGE:
                    $Image = $this->GalleryImageContainer->getGalleryImage($Media->getParameter('MediaLocation'));
                    $Gallery = $this->GalleryContainer->getGallery($Image->getParameter('GalleryID'));
                    if ($Image){
						$Object->setParameter('ArtistImage',get_bloginfo('wpurl').'/'.GALLERY_IMAGE_DIR.$Gallery->getDirectoryName().rawurlencode($Image->getParameter("GalleryImageOriginal")));
                    }
                    break;
			}
        }

		if ($Object->getParameter('ArtistBand') != ''){
			static $ArtistContainer;
			if (!isset($ArtistContainer)){
				$ArtistContainer = new ArtistContainer();
			}
			$Band = $ArtistContainer->getArtist($Object->getParameter('ArtistBand'));
			if ($Band){
				$Object->setParameter('MemberOf',$Band->getParameter('ArtistFullName'));
			}
		}
   	}

	function postExport(& $Artist){
	}
	
	function customizeWhereClause(& $wc){
		$wc->addCondition($this->Container->getColumnName('ArtistIsActive').' = 1');
	}
    
}