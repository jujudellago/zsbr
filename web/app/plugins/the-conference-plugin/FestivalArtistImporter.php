<?php

require_once(PACKAGE_DIRECTORY."ImportExport/Importer.php");

class ArtistImporter extends Importer{
    
    function ArtistImporter(){
		$this->Importer();
		$this->default_encoding = 'UTF-8';
		$this->default_delimiter = 'comma';
		$this->limit = 5;
		
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('FestivalApp');
        
		$this->parameterPrefix = 'Artist';
        $this->setContainer('ArtistContainer');
        $this->setUniqueKey('ArtistFullName');
        $this->setDisplayKey('ArtistFullName');
        $this->setSQLKey('ArtistID');

		$Bootstrap->primeAdminPage(); // I hate that I have to do this.
        $this->viewImportedRecordURL = $Bootstrap->makeAdminURL($Package,'update_artist')."&id=";        
        $this->editImportedRecordURL = $Bootstrap->makeAdminURL($Package,'update_artist')."&id=";        

        $this->ignoreParameter(array());

		$ExtraParameters = array();
		foreach ($this->Container->colname as $key => $parm){
			$ExtraParameters[] = $parm;
			$ExtraParameters[] = str_replace($this->parameterPrefix,'',$parm);
		}
		
		$this->addExtraParameter($ExtraParameters);
		$this->addExtraParameter(array('Year','ArtistImage','Image','MemberOf'));
		
		$this->FestivalArtistContainer = new FestivalArtistContainer();
		
		if (!isset($_SESSION['imported_artists'])){
			$_SESSION['imported_artists'] = array();
		}

    }
    
    function massageData(& $Object){
        foreach ($this->getExtraParameters() as $parm){
			if ($Object->getParameter($parm) != "" and $Object->getParameter($this->parameterPrefix.$parm) == ""){
				$Object->setParameter($this->parameterPrefix.$parm,$Object->getParameter($parm));
			}
        }
		$first = $Object->getParameter('ArtistFirstName');
		$last  = $Object->getParameter('ArtistLastName');
		$full = $first.(($first != "" and $last != "") ? " " : "").$last;
		$Object->setParameter('ArtistFirstName',$first);
		$Object->setParameter('ArtistLastName',$last);
		$Object->setParameter('ArtistFullName',$full);
		
		if ($Object->getParameter('ArtistWebsite') != '' and strpos($Object->getParameter('ArtistWebsite'),'http://') === false){
			$Object->setParameter('ArtistWebsite','http://'.$Object->getParameter('ArtistWebsite'));
		}
		if ($Object->getParameter('ArtistVideo') != '' and strpos($Object->getParameter('ArtistVideo'),'http://') === false){
			$Object->setParameter('ArtistVideo','http://'.$Object->getParameter('ArtistVideo'));
		}
		
		if ($Object->getParameter('ArtistImage') != ''){
			$this->current_image_url = $Object->getParameter('ArtistImage');
		}
		elseif ($Object->getParameter('Image') != ''){
			$this->current_image_url = $Object->getParameter('Image');
		}
		else{
			unset($this->current_image_url);
		}
		
		unset($this->current_band_id);
		if ($Object->getParameter('MemberOf') != ''){
			static $ArtistContainer;
			if (!isset($ArtistContainer)){
				$ArtistContainer = new ArtistContainer();
			}
			$Band = $ArtistContainer->getArtistByName($Object->getParameter('MemberOf'));
			if ($Band){
				$this->current_band_id = $Band->getParameter('ArtistID');
			}
			else{
				return PEAR::raiseError(sprintf('Tried to import %s as a member of "%s" which isn\'t yet in the database',vocabulary('Artist'),$Object->getParameter('MemberOf')));
			}
		}
		$this->year = $Object->getParameter('Year');
   	}

	function addObject(&$Object){
		if ($Object->getParameter('ArtistFullName') != ''){
			$result = $this->Container->addArtist($Object);
			return $result;
		}
		else{
			return true;
		}
	}

	function updateObject(&$Object){
		return $this->Container->updateArtist($Object);
	}

	function postImport(&$Object){
		if ($this->current_image_url != ''){
			$Artist = $this->Container->getArtist($Object->getParameter('ArtistID'));
			$Artist->addArtistImage($this->current_image_url,'General');
		}
		if ($this->year != ''){
			static $FestivalArtistContainer;
			if (!isset($FestivalArtistContainer)){
				$FestivalArtistContainer = new FestivalArtistContainer();
			}
			$FestivalArtistContainer->addFestivalArtist($this->year,$Object->getParameter('ArtistID'));
			if (isset($this->current_band_id)){
				$FestivalArtist = $FestivalArtistContainer->getFestivalArtist($this->year,$Object->getParameter('ArtistID'));
				$FestivalArtist->setParameter('ArtistBand',$this->current_band_id);
				$FestivalArtistContainer->updateFestivalArtist($FestivalArtist);
			}
			
			if (!isset($_SESSION['imported_artists'][$this->year])){
				$_SESSION['imported_artists'][$this->year] = array();
			}
			$_SESSION['imported_artists'][$this->year][] = $Object->getParameter('ArtistID');
		}
	}
	
	function postPerformImport(){
		if ($_SESSION['import_options']['synchronize'] !== true){
			// if it does === true, this will get unset in the synchronize method below
			unset($_SESSION['imported_artists']);
		}
	}

	function synchronize($StartTime){
		// The purpose of this synchronization is to set the lineup(s) according to the imported artists
		$ImportedArtists = $_SESSION['imported_artists'];
		unset($_SESSION['imported_artists']); // cleanup
		if (!is_array($ImportedArtists) or !count($ImportedArtists)){
			// nothing to do.
			return;
		}
		$FestivalContainer = new FestivalContainer();
		$FestivalArtistContainer = new FestivalArtistContainer();
		$CurrentArtists = array();
		foreach ($ImportedArtists as $Year => $ArtistIDs){
			$Festival = $FestivalContainer->getFestival($Year);
			if (!is_a($Festival,'Festival')){
				continue;
			}
			if (is_array($_SESSION['import_unchanged_ids']) and count($_SESSION['import_unchanged_ids'])){
				if (!isset($CurrentArtists[$Year])){
					$CurrentArtists[$Year] = $FestivalArtistContainer->getAllArtists($Year);
				}
				if (!is_array($CurrentArtists[$Year])){
					$AdditionalArtists = array();
				}
				else{
					// Include artists that didn't change on the import but that are in the festival lineup already
					$AdditionalArtists = array_intersect(array_keys($CurrentArtists[$Year]),$_SESSION['import_unchanged_ids']);
				}
			}
			else{
				$AdditionalArtists = array();
			}
			$ArtistIDs = array_merge($ArtistIDs,$AdditionalArtists);
			$FestivalArtistContainer->setFestivalLineup($Year,$ArtistIDs);
			$FestivalArtistContainer->sortLineupByLastName($Year);
		}
		return;
	}	
}

?>