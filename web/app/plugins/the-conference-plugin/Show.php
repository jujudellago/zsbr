<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

if (!defined('HOST_STRING')){
    define('HOST_STRING'," (h)");
}

class Show extends Parameterized_Object
{
	function Show($show_id = '',$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('ShowID',$show_id); 

		$this->setIDParameter('ShowID');
		$this->setNameParameter('ShowTitle');
	}
        
        function getListing($TitleCallback = "",$ArtistCallback = "",$IncludeDescription = false,$ArtistSep = "<br />"){
	
                $return = "";

				if (function_exists('apply_filters') and $return = apply_filters('show-getListing',$return,array(&$this,$TitleCallback,$ArtistCallback,$IncludeDescription,$ArtistSep))){
					return $return;
				}
            
                if ($this->getParameter('ShowStartTimeSpoofed')){
                    $return.= "<p class='ShowListingStartTime'>(".$this->getParameter('ShowPrettyStartTime').")</p>";
                }
                
                // Note: For the ArtistURL, you must have a URLParm= at the end for the ArtistID
                $return.= "<p class='ShowListingTitle'>";
                if ($TitleCallback != ""){
                        $return.= "<a href='".$TitleCallback($this)."'>";
                }
				if (trim($this->getParameter('ShowTitle')) == ""){
					$return.= $this->getParameter('ShowType');
				}
				else{
				    $return.= $this->getParameter('ShowTitle');
				}	
                if ($TitleCallback != ""){
                        $return.= "</a>";
                }
                $return.="</p>";

				if ($this->getParameter('ShowTitle') != ($ArtistNames = $this->getArtistNames())){
                    $return.= "<p class='ShowListingArtists'>";
					$sep = "";
                    foreach ($this->getParameter('ShowArtists') as $ArtistID => $Artist){
                            $return.= "$sep";
                            if ($this->getParameter('ShowHostArtistID') == $ArtistID){$Host = HOST_STRING;}
                            else{$Host = "";}
                            if ($ArtistCallback != ""){
                                    $return.= "<a href='".$ArtistCallback($Artist)."'>".$Artist->getParameter('ArtistFullName')."</a>$Host";
                            }
                            else{
                                    $return.= $Artist->getParameter('ArtistFullName').$Artist->getParameter('ArtistIsHost').$Host;
                            }
                            $sep = "$ArtistSep\n";
                    }
                    $return.= "</p>";
				}
				if ($this->getSponsor() != ""){
				    $return.= "<p class='ShowSponsor'>".$this->getSponsorDisplay()."</p>";
				}
                if ($IncludeDescription and $this->getParameter('ShowDescription') != ""){
                        $return.= "<p class='ShowListingDescription'>".$this->getParameter('ShowDescription')."</p>\n";
                }
				if ($this->getParameter('ShowEmbeddedScheduleUID')){
					$ShowContainer = new ShowContainer();
					$EmbeddedShows = $ShowContainer->getEmbeddedShows($this);
					if (is_array($EmbeddedShows) and count($EmbeddedShows)){
						$return.= '<p class="show-embedded-schedule">';
						foreach ($EmbeddedShows as $EmbeddedShow){
							$return.= '<span class="show-embedded-time">'.$EmbeddedShow->getParameter('ShowPrettyStartTime').' - </span> <span class="show-embedded-artist">'.$EmbeddedShow->getParameter('ShowTitle').'</span><br/>';
						}
						$return.= '</p>';
					}
				}
                
                
                return $return;
        }
        
		function getArtistNames($ArtistBaseURL = "",$sep = ", ",$HostString = HOST_STRING){
		    
		    // $ArtistBaseURL should end with something like id= so the Artist ID can be appended to it
		    $Artists = $this->getParameter('ShowArtists');
		    $ArtistNames = array();
		    foreach ($Artists as $ArtistID => $Artist){
	            $_ArtistID = ($Artist->getParameter('ArtistBand') ? $Artist->getParameter('ArtistBand') : $ArtistID);
		        if ($ArtistBaseURL != ""){
		            $tmp = "<a href='$ArtistBaseURL".$_ArtistID."'>".$Artist->getParameter('ArtistFullName')."</a>";
		        }
		        else{
		            $tmp = $Artist->getParameter('ArtistFullName');
		        }
		            
                if ($this->getParameter('ShowHostArtistID') == $ArtistID){
                    $tmp.= $HostString;
                }
                $ArtistNames[] = $tmp;
		    }
			return implode($sep,$ArtistNames);
        }

		function getSponsor(){
			return apply_filters('ShowSponsor',$this->getParameter('ShowSponsor'),array(&$this));
		}
		
		function getSponsorDisplay(){
			return apply_filters('ShowSponsorDisplay','Sponsor: '.$this->getParameter('ShowSponsor'),array(&$this));
		}
        
}
?>