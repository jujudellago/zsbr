<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");
require_once("MediaContainer.php");

class Artist extends Parameterized_Object
{
	function Artist($ArtistID = '',$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('ArtistID',$ArtistID);	

		$this->setIDParameter('ArtistID');
		$this->setNameParameter('ArtistFullName');
		
        require_once(PACKAGE_DIRECTORY."../Standard.php");
		$Bootstrap = Bootstrap::getBootstrap();
        $Bootstrap->usePackage('Gallery');
	}
        
    function getArtistURL(){
            return "";
    }

	function parameterizeAssociatedMedia(){
	    $MediaContainer = new MediaContainer();
	    $GalleryImageContainer = new GalleryImageContainer();
	    $GalleryContainer = new GalleryContainer();
	    
	    $Objects = array($this);
	    
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
						if (is_a($Image,'GalleryImage')){
	                        $Gallery = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));
	                        if ($Image){
	                            $AssociatedImages[] = 
	                                array('Thumb' => $Gallery->getGalleryDirectory().$Image->getParameter("GalleryImageThumb"), 
	                                      'Resized' => $Gallery->getGalleryDirectory().$Image->getParameter("GalleryImageResized"),
	                                      'Original' => $Gallery->getGalleryDirectory().$Image->getParameter("GalleryImageOriginal"));
	                        }
						}
						else{
							$MediaContainer->deleteMedia($Media->getParameter('MediaID'));
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
	
	function addArtistImage($Source,$Type){
		static $GalleryContainer, $MediaContainer;
		if (!isset($GalleryContainer)){
			$GalleryContainer = new GalleryContainer();
			$MediaContainer = new MediaContainer();
		}
		$GalleryName = $Type.' Artist Images';
        $Gallery = $GalleryContainer->getGalleryByName($GalleryName);
        $NewGallery = false;
        if (!$Gallery){
				$safe_name = preg_replace('/[^A-Za-z0-9]/','_',$Type).'ArtistImages';
                $Gallery = new Gallery();
                $Gallery->setParameter('GalleryName',$GalleryName);
                $Gallery->setParameter('GalleryDescription','A private gallery with '.$GalleryName.'.  Images in this gallery are automatically updated using The Conference Plugin');
                $Gallery->setParameter('GalleryDefaultCredit','Various');
                switch ($Package->galleryPackage){
                case 'AdvancedGallery':
                    $Gallery->setParameter('GalleryYear','System'); 
                    $Gallery->setParameter('GalleryStatus','private');
                    $Gallery->setParameter('GalleryDirectory',date("Y").'/'.$safe_name.'/');
                    break;
                case 'Gallery':
                default:
                    $Gallery->setParameter('GalleryStatus','system');
                    $Gallery->setParameter('GalleryDirectory',$safe_name.'/');
                    break;
                }
                
                $GalleryContainer->addGallery($Gallery);
                $NewGallery = true;
        }

		if (function_exists('apply_filters')){
			$DimensionCallbacks = apply_filters('set_gallery_dimension_callbacks',array(),$Gallery->getParameter('GalleryName'));
		}
		else{
            $DimensionCallbacks = array();
            // Don't crop the thumbs and don't resize the originals
            $DimensionCallbacks['Thumb'] = create_function('','return array("width" => THUMB_WIDTH, "height" => THUMB_HEIGHT, "conditions" => '.RESIZE_CONDITIONS_DEFAULT.');');
            $DimensionCallbacks['Original'] = create_function('','return array("width" => 0, "height" => 0, "conditions" => '.RESIZE_CONDITIONS_DEFAULT.');');
		}
		
		if (is_array($Source)){
			// From an upload widget
	        $GalleryImage = $Gallery->addUploadedGalleryImage($Source['tmp_name'],$Source['name'],$DimensionCallbacks);
		}
		else{
			// From a URL
			list($url,$args) = explode('?',$Source);
			
			$this->parameterizeAssociatedMedia();
			if (is_array($this->getParameter('ArtistAssociatedImages'))){
				foreach ($this->getParameter('ArtistAssociatedImages') as $existing_images){
					if (basename($existing_images['Original']) == basename($url)){
						// Image already exists, no need to add it again.  
						return true;
					}
				}
			}
	        $ImageLibrarian = new ImageLibrarian();
	        
	        if (is_array($Gallery->Package->galleries_to_keep_original) and in_array($Gallery->getParameter('GalleryName'),$Gallery->Package->galleries_to_keep_original)){
	            $ImageLibrarian->setOriginalDimensionsCallback(create_function('',"return array('width' => 0, 'height' => 0, 'conditions' => RESIZE_KEEP_ASPECT_RATIO);"));
	        }
	
            $GalleryImageContainer = new GalleryImageContainer();
            $ImageLibrarian->makeDirectory($Gallery->getDirectoryName());

			$Dest = $ImageLibrarian->getBaseDirectory().$Gallery->getDirectoryName().basename($url);

			if (true or !@copy($Source,$Dest)){
				// try curling it, if it's a url
				if (!preg_match('/^(http|https):\/\//',$Source) or !$this->curl_image($Source,$Dest)){
					return PEAR::raiseError('Unable to copy URL to gallery location at '.$Dest);
				}
			}
            
            foreach ($ImageLibrarian->getTypes() as $type){
                if (array_key_exists($type,$DimensionCallbacks)){
                    $function = "set".$type."DimensionsCallback";
                    $ImageLibrarian->$function($DimensionCallbacks[$type]);
                }
            }
            $results = $ImageLibrarian->checkInFile($Dest);
            if (PEAR::isError($results)){
                    return $results;
            }
            $GalleryImages = array();
            if (!isset($results[0]) and count($results)){
                    $results = array($results);
            }
            foreach ($results as $result){
				$GalleryImage = $GalleryImageContainer->manufactureGalleryImage(new Parameterized_Object());
				$GalleryImage->setParameter('GalleryID',$Gallery->getParameter('GalleryID'));
				$GalleryImage->setParameter('GalleryImage',basename($Dest));
				foreach ($result as $type => $parms){
				        $GalleryImage->setParameter('GalleryImage'.$type,$parms['name']);
				}

				if (PEAR::isError($e = $GalleryImageContainer->addGalleryImage($GalleryImage))){        
				        return $e;
				}
            }
		}

        if ($NewGallery){
            $Gallery->setPrimaryThumb();
        }
        if (PEAR::isError($GalleryImage)){
			return PEAR::raiseError("Unable to upload Media file. ".$GalleryImage->getMessage());
        }
        else{
            $Media = new Media();
            $Media->setParameter('MediaType',MEDIA_TYPE_GALLERYIMAGE);
            $Media->setParameter('MediaLocation',$GalleryImage->getParameter('ImageID'));
            $Media->setParameter('MediaAssociatedObject',get_class(($Type == 'General' ? new Artist() : new FestivalArtist())));
            $Media->setParameter('MediaAssociatedObjectID',$this->getParameter('ArtistID').($Type != 'General' ? '_'.$Type : ''));
            $MediaContainer->addMedia($Media);
        }
	}
	
	function curl_image($img_url,$filename) {
		$ch = curl_init(str_replace(array(' '),array('%20'),$img_url));
		
		$fp = fopen($filename, 'wb');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		$mime = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
		fclose($fp);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($result and $code != 404){
			return true;
		}
		@unlink($filename);
		return false;
	}

}
?>