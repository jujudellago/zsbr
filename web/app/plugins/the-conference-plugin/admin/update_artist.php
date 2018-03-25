<?php
/************************************************************
*
*
*************************************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$manage = $Bootstrap->makeAdminURL($Package,'manage');
	$from = ((isset($_GET['from']) and $_GET['from'] != '')? $_GET['from'] : ((isset($_POST['from']) and $_POST['from'] != '') ? $_POST['from'] : 'manage_artists'));
	
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,$from);
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'update_artist');
	
	if ($Bootstrap->packageExists('flamplayer')){
	    $AllowMP3s = true;
	    $Bootstrap->usePackage('flamplayer');
	    $SongContainer = new SongContainer();
	    $SongArtistContainer = new SongArtistContainer();
	}
	else{
	    $AllowMP3s = false;
	}
	
	define ('HTML_FORM_TH_ATTR',"valign=top align=left width='20%'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
    define('HTML_FORM_MAX_FILE_SIZE', 10485760); // 10 MB
	include_once(PACKAGE_DIRECTORY.'../TabbedForm.php');
	

    $id    = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : ""); 
    $sel    = isset($_GET['sel']) ? $_GET['sel'] : (isset($_POST['sel']) ? $_POST['sel'] : ""); 
    $AdditionalTab    = isset($_GET[YEAR_PARM]) ? urldecode($_GET[YEAR_PARM]) : (isset($_POST['additional_tab']) ? $_POST['additional_tab'] : ""); 
    $popup = $sel != "";
    
    $ArtistContainer = new ArtistContainer();
    $FestivalArtistContainer = new FestivalArtistContainer();
    $Artist = null;
    if($id != ""){
            $Artist = $ArtistContainer->getArtist($id);
    }
    if (!$Artist){
            $Artist = new Artist();
    }

    $MediaContainer = new MediaContainer();
    if (isset($_POST['del']) and $_POST['del'] != ""){       
            $MediaContainer->deleteMedia($_POST['del']);
    }
    $GalleryImageContainer = new GalleryImageContainer();

    $PassTypes = array("Performer","Guest");

    $GalleryContainer = new GalleryContainer();

	/******************************************************************
	*  Field Level Validation
	*  Only performed if they've submitted the form
	******************************************************************/
	if (isset($_POST['form_submitted']) and $_POST['form_submitted'] == 'true'){
	
		// They hit the cancel button, return to the Manage Pages page
		if (isset($_POST['cancel'])){
            if (!$popup){
        			header("Location:".$Bootstrap->makeAdminURL($Package,$from));
        			exit();
            }
            else{
                    $close_window = true;
            }
		}
        else{
		
        		/******************************************************************
        		*  BEGIN EDITS
        		*  If an edit fails, it adds an error to the message list.  
        		******************************************************************/
        		if ($_POST['ArtistFullName'] == ""){
        			$MessageList->addMessage("You have to give the ".vocabulary('Artist')." a name!!",MESSAGE_TYPE_ERROR);
        		}
        		if ($_POST['ArtistWebsite'] != "" and !strpos($_POST['ArtistWebsite'],":")){
        			$_POST['ArtistWebsite'] = "http://" . $_POST['ArtistWebsite'];
        		}
			   if ($_POST['ArtistVideo'] != "" and !strpos($_POST['ArtistVideo'],":")){
		       		#$_POST['ArtistVideo'] = "http://" . $_POST['ArtistVideo'];
		       }
		

        		/******************************************************************
        		*  END EDITS
        		******************************************************************/
        				
        
        
        
        
        						
        		/******************************************************************
        		*  BEGIN Set Parameters
        		******************************************************************/
        		$Artist->setParameter('ArtistFullName',$_POST['ArtistFullName']);
        		$Artist->setParameter('ArtistFirstName',$_POST['ArtistFirstName']);
        		$Artist->setParameter('ArtistLastName',$_POST['ArtistLastName']);
        		$Artist->setParameter('ArtistWebsite',$_POST['ArtistWebsite']);
        		$Artist->setParameter('ArtistVideo',$_POST['ArtistVideo']);
        		$Artist->setParameter('ArtistExtra1',$_POST['ArtistExtra1']);
        		$Artist->setParameter('ArtistExtra2',$_POST['ArtistExtra2']);
        		$Artist->setParameter('ArtistExtra3',$_POST['ArtistExtra3']);
        		$Artist->setParameter('ArtistDescription',$_POST['ArtistDescription']);
        		$Artist->setParameter('ArtistLongDescription',$_POST['ArtistLongDescription']);
        		$Artist->setParameter('ArtistProgramDescription',$_POST['ArtistProgramDescription']);
        		
        		if ($AllowMP3s){
            		$SongNumber = 0;
            		while(isset($_POST["Song_{$SongNumber}_id"])){
        		        $Song = $SongContainer->getSong($_POST["Song_{$SongNumber}_id"]);
        		        if ($Song){
            		        $Song->setParameter('SongTitle',stripslashes($_POST["Song_{$SongNumber}_title"]));
            		        $SongContainer->updateSong($Song);
            		    }
        		        $SongNumber++;
            		}
        		}
        		
        		/******************************************************************
        		*  END Set Parameters
        		******************************************************************/
        		
        		// If there are no messages/errors, then go ahead and do the update (or add)
        		// Note: if they were deleting a version, then there will be a message, so
        		// this section won't get performed
        		if (!$MessageList->hasMessages()){
        			if ($Artist->getSavedParameter('ArtistID') == ""){
        				$result = $ArtistContainer->addArtist($Artist);
        				$id = $Artist->getParameter('ArtistID');
                        $newArtist = true;
        			}
        			else{				
        				$result = $ArtistContainer->updateArtist($Artist);
                        $newArtist = false;
        			}
        			if (PEAR::isError($result)){
        				$MessageList->addPearError($result);
        			}
        			else{
						if ($Package->enable_cache and !$Package->empty_cache_on_publish_only){
							// We're using the cache, so we need to remove the cache pages associate with this term
							$Package->emptyCache();
						}
        			    if ($_POST['years'] != ""){
        			        $Years = explode(",",$_POST['years']);
        			    }
        			    else{
        			        $Years = array();
        			    }
                        $FestivalInfo = array();
        			    foreach ($Years as $year){
                            // Year Prefix for the variables to remove unwanted characters
                            $year_prefix = getSafeYear($year);
                            
        			        $_tmp = new Parameterized_Object();
                    		$_tmp->setParameter('ArtistDescription',$_POST[$year_prefix.'ArtistDescription']);
                    		$_tmp->setParameter('ArtistLongDescription',$_POST[$year_prefix.'ArtistLongDescription']);
                    		$_tmp->setParameter('ArtistProgramDescription',$_POST[$year_prefix.'ArtistProgramDescription']);
                            $_tmp->setParameter('ArtistFee',$_POST[$year_prefix.'ArtistFee']);
                            $_tmp->setParameter('ArtistFeeDescription',$_POST[$year_prefix.'ArtistFeeDescription']);
                            $_tmp->setParameter('ArtistAccommodationDescription',$_POST[$year_prefix.'ArtistAccommodationDescription']);
                            $ArtistGuests = "";
                            foreach ($PassTypes as $PassType){
                                if ($_POST[$year_prefix.$PassType.'Passes'] != ""){
                                    $ArtistGuests.= "$PassType: ".trim($_POST[$year_prefix.$PassType.'Passes'])."\n";
                                }
                            }
                            $_tmp->setParameter('ArtistGuests',$ArtistGuests);
                            $_tmp->setParameter('ArtistTechnicalRequirements',$_POST[$year_prefix.'ArtistTechnicalRequirements']);
                            $_tmp->setParameter('ArtistBand',$_POST[$year_prefix.'ArtistBand']);
                    		$_tmp->setParameter('ArtistDoNotPublish',isset($_POST[$year_prefix.'ArtistDoNotPublish']) ? 1 : 0);
                            $FestivalInfo[$year] = $_tmp;
        			    }
        			    if (count($FestivalInfo)){
            			    $Artist->setParameter('FestivalInfo',$FestivalInfo);
            			    $result = $FestivalArtistContainer->updateFestivalInfo($Artist);
            			}
            			
	                    // Now deal with the uploaded media
	                    
	                    // First, the default media
			            $MediaSource=$_FILES['ArtistMedia']['tmp_name'];
			            if (!$MessageList->hasMessages() and $MediaSource != "" AND $MediaSource !="none"){
			                if ($AllowMP3s and in_array($_FILES['ArtistMedia']['type'],array('audio/mpeg','audio/mp3'))){
			                    if (move_uploaded_file($MediaSource,$SongContainer->getMusicDirectory().stripslashes($_FILES['ArtistMedia']['name']))){
			                        // First, check to see that the Artist is already in the music database
			                        $SongArtist = $SongArtistContainer->getSongArtist($Artist->getParameter('ArtistID'));
			                        if (!$SongArtist){
			                            $SongArtistContainer->addArtist($Artist);
			                        }
			                        else{
			                            $SongArtistContainer->updateArtist($Artist);
			                        }
			                        $Song = new Song();
                            		$Song->setParameter('SongArtistID',$Artist->getParameter('ArtistID'));
                            		$Song->setParameter('SongTitle',preg_replace('/(^[0-9][0-9] |.mp3$)/','',stripslashes($_FILES['ArtistMedia']['name'])));
                            		$Song->setParameter('SongFileName',$_FILES['ArtistMedia']['name']);
                            		$result = $SongContainer->addSong($Song);
                                    if (PEAR::isError($result)){
                        					$MessageList->addMessage("Unable to upload audio file. ".$result->getMessage(),MESSAGE_TYPE_ERROR);
                                    }
                                    else{
                                        $Media = new Media();
                                        $Media->setParameter('MediaType',MEDIA_TYPE_MP3);
                                        $Media->setParameter('MediaLocation',$Song->getParameter('SongID'));
                                        $Media->setParameter('MediaAssociatedObject',get_class(new Artist()));
                                        $Media->setParameter('MediaAssociatedObjectID',$Artist->getParameter('ArtistID'));
                                        $MediaContainer->addMedia($Media);
                                    }
			                    }
    			            }
    			            elseif(substr($_FILES['ArtistMedia']['type'],0,5) == 'image'){
								$ImageResult = $Artist->addArtistImage($_FILES['ArtistMedia'],'General');
								if (PEAR::isError($ImageResult)){
                					$MessageList->addMessage($ImageResult->getMessage(),MESSAGE_TYPE_ERROR);
								}
                            }
                        }
	                    
        			    foreach ($Years as $year){
                            // Year Prefix for the variables to remove unwanted characters
                            $year_prefix = getSafeYear($year);
                            
    			            $MediaSource=$_FILES[$year_prefix.'ArtistMedia']['tmp_name'];
    			            if (!$MessageList->hasMessages() and $MediaSource != "" AND $MediaSource !="none"){
			                    if ($AllowMP3s and $_FILES[$year_prefix.'ArtistMedia']['type'] == 'audio/mpeg'){
    			                    if (move_uploaded_file($MediaSource,$SongContainer->getMusicDirectory().$_FILES[$year_prefix.'ArtistMedia']['name'])){
    			                        // First, check to see that the Artist is already in the music database
    			                        $SongArtist = $SongArtistContainer->getSongArtist($Artist->getParameter('ArtistID'));
    			                        if (!$SongArtist){
    			                            $SongArtistContainer->addArtist($Artist);
    			                        }
    			                        else{
    			                            $SongArtistContainer->updateArtist($Artist);
    			                        }
    			                        $Song = new Song();
                                		$Song->setParameter('SongArtistID',$Artist->getParameter('ArtistID'));
                                		$Song->setParameter('SongTitle',$_FILES[$year_prefix.'ArtistMedia']['name']);
                                		$Song->setParameter('SongFileName',$_FILES[$year_prefix.'ArtistMedia']['name']);
                                		$result = $SongContainer->addSong($Song);
                                        if (PEAR::isError($result)){
                            					$MessageList->addMessage("Unable to upload audio file. ".$result->getMessage(),MESSAGE_TYPE_ERROR);
                                        }
                                        else{
                                            $Media = new Media();
                                            $Media->setParameter('MediaType',MEDIA_TYPE_MP3);
                                            $Media->setParameter('MediaLocation',$Song->getParameter('SongID'));
                                            $Media->setParameter('MediaAssociatedObject',get_class(new FestivalArtist()));
                                            $Media->setParameter('MediaAssociatedObjectID',$Artist->getParameter('ArtistID').'_'.$year);
                                            $MediaContainer->addMedia($Media);
                                        }
    			                    }
    			                }
    			                elseif(substr($_FILES[$year_prefix.'ArtistMedia']['type'],0,5) == 'image'){
									$ImageResult = $Artist->addArtistImage($_FILES[$year_prefix.'ArtistMedia'],$year);
									if (PEAR::isError($ImageResult)){
	                					$MessageList->addMessage($ImageResult->getMessage(),MESSAGE_TYPE_ERROR);
									}
                                }
                            }
            			}
    			    }
                         
            }
                                

        	if (!$MessageList->hasMessages()){
                if (!$popup or $_POST['save_n_add']){
                        $MessageList->addMessage(vocabulary('Artist')." Successfully Updated");
                        if ($_POST['save_n_add']){
                                $update_parent = true;
                                $update_fullname = $Artist->getParameter('ArtistFullName');
                                $update_id = $Artist->getParameter('ArtistID');
                                $Artist = new Artist();
                                unset($id);
                        }
                }
                else{
                        $close_window = true;
                }
        	}
        }
	}
	
	
	/****************************************************************************
	*
	* BEGIN Display Code
	*    The following code sets how the page will actually display.  
	*
	****************************************************************************/
	// Declaration of the Form	
	$form = new HTML_TabbedForm($Bootstrap->getAdminURL(),'post','Update_Form','','multipart/form-data');
	$DefaultTab = 'ArtistTab';
	
	
	// Before we begin the display portion, retreive the Festival Information
    $FestivalArtistContainer->getAllFestivalInfo($Artist);
    $FestivalInfoArray = $Artist->getParameter('FestivalInfo');
	
	/***********************************************************************
	*
	*	Artist Tab
	*
	***********************************************************************/

	$ArtistTab = new HTML_Tab('Artist','General Info');
        
    if ($MessageList->hasMessages() and $popup){
            $ArtistTab->addPlainText($MessageList->getSeverestType(),$MessageList->toBullettedString());
    }
    
	$ArtistTab->addPlainText(vocabulary('Artist').' ID',$Artist->getParameter('ArtistID'));
	$ArtistTab->addText('ArtistFullName','Full Name:',htmlspecialchars($Artist->getParameter('ArtistFullName')));
	$ArtistTab->addText('ArtistFirstName','First Name:',htmlspecialchars($Artist->getParameter('ArtistFirstName')));
	$ArtistTab->addText('ArtistLastName','Last Name:',htmlspecialchars($Artist->getParameter('ArtistLastName')));
	$ArtistTab->addText('ArtistWebsite','Website:',$Artist->getParameter('ArtistWebsite'));
	$ArtistTab->addText('ArtistVideo','Video:',$Artist->getParameter('ArtistVideo'));
	$ArtistTab->addText('ArtistExtra1','Extra 1:',$Artist->getParameter('ArtistExtra1'));
	$ArtistTab->addText('ArtistExtra2','Extra 2:',$Artist->getParameter('ArtistExtra2'));
	$ArtistTab->addText('ArtistExtra3','Extra 3:',$Artist->getParameter('ArtistExtra3'));

	
	$FestivalAppearancesString = "";
	if (count($FestivalInfoArray)){
    	$sep = "";
    	foreach (array_keys($FestivalInfoArray) as $FestivalYear){
    	    $FestivalAppearancesString.= $sep."<a href=\"javascript:switchto('group_".getSafeYear($FestivalYear)."FestivalTab')\">$FestivalYear</a>";
    	    $sep = ", ";
    	}
    }
	$ArtistTab->addPlainText(vocabulary('Festival').' Appearances:',$FestivalAppearancesString);
	
	
	$ArtistTab->addPlainText('&nbsp',"<hr>");
	if ($FestivalAppearancesString == ""){
	    $FestivalAppearancesString = "by <a href='$manage'>adding the ".vocabulary('Artist')." to a ".vocabulary('Festival')."</a>";
	}
	else{
	    $FestivalAppearancesString = "on the $FestivalAppearancesString tab";
	}
	    
	$ArtistTab->addPlainText('&nbsp',"You can set default values for the ".vocabulary('Artist')." descriptions and photos here.  They can be overridden for a specific ".vocabulary('Festival')." $FestivalAppearancesString.");
    $ArtistTab->addTextArea('ArtistDescription', 'Brief Description:',$Artist->getParameter('ArtistDescription'),50,3,0);
    $ArtistTab->addTextArea('ArtistLongDescription', 'Longer Description:',$Artist->getParameter('ArtistLongDescription'),50,6,0);
    $ArtistTab->addTextArea('ArtistProgramDescription', 'Program Bio:',$Artist->getParameter('ArtistProgramDescription'),50,6,0);

    $ArtistTab->addFile('ArtistMedia','Upload Media:');
    $AllMedia = $MediaContainer->getAssociatedMedia($Artist);

    $SongNumber = 0;
    if (is_array($AllMedia) and count($AllMedia)){
            $MediaString = "<table cellpadding='5' border='0'><tr>";
            foreach ($AllMedia as $Media){
                    $MediaString.= "<td valign=top align=left>";
                    if ($Media->getParameter('MediaType') == MEDIA_TYPE_GALLERYIMAGE){
                            $GalleryImage = $GalleryImageContainer->getGalleryImage($Media->getParameter('MediaLocation'));
                            if (is_a($GalleryImage,'GalleryImage')){
                                $Gallery = $GalleryImage->getGallery();
                                if (is_a($Gallery,'Gallery')){
										$MediaString.= '<div class="image-div">'."\n";
                                        $MediaString.= "<img src='".$Gallery->getGalleryDirectory().$GalleryImage->getParameter('GalleryImageThumb')."' class='gallery-".$GalleryImage->getParameter('GalleryID')." image-".$GalleryImage->getParameter('ImageID')."'><br>";
                                        $MediaString.= "<a href='".$Gallery->getGalleryDirectory().$GalleryImage->getParameter('GalleryImageOriginal')."' target='_blank'>".$GalleryImage->getParameter('GalleryImageOriginal')."</a> <font size=-1>(<a href='javascript:deleteMedia(".$Media->getParameter('MediaID').");'>delete</a>, <a href=\"#\" id=\"edit-image-".$Media->getParameter('MediaLocation')."\" class=\"edit-image\">edit</a>)</font><br>";
										$MediaString.= '</div>'."\n";
                                }
                            }
                    }
                    elseif($AllowMP3s and $Media->getParameter('MediaType') == MEDIA_TYPE_MP3){
                            $Song = $SongContainer->getSong($Media->getParameter('MediaLocation'));
                            if (is_a($Song,'Song')){
                                $MediaString.= "<a href='".$SongContainer->getMusicURL().htmlentities($Song->getParameter('SongFileName'),ENT_QUOTES)."' target='_blank'><img src='".$Package->getPackageURL()."/admin/images/mp3.jpg' border='0'><br />".$Song->getParameter('SongFileName')."</a> <font size=-1>(<a href='javascript:deleteMedia(".$Media->getParameter('MediaID').");'>delete</a>)</font>";
                                $MediaString.= "<br /><font size='-1'>Title: </font>".HTML_FORM::returnText("Song_{$SongNumber}_title",$Song->getParameter('SongTitle'));
                                $form->addHidden("Song_{$SongNumber}_id",$Song->getParameter('SongID'));
                                $SongNumber++;
                            }
                    }
                    else{
                            $MediaString.= "<a href='".BASE_URL.ARTIST_DIRECTORY_REL.$Media->getParameter('MediaLocation')."' target='_blank'>".basename($Media->getParameter('MediaLocation'))."</a> <font size=-1>(<a href='javascript:deleteMedia(".$Media->getParameter('MediaID').");'>delete</a>)</font><br>";
                    }
                    $MediaString.= "</td>";
            }
            $MediaString.= "</tr></table>";
            $ArtistTab->addPlainText('Loaded Media:',$MediaString);
    }
    
    if ($Bootstrap->packageExists('Tags') and $Artist->getParameter('ArtistFullName') != ""){
        $TagText = $Artist->getParameter('ArtistFullName');
        $Bootstrap->usePackage('Tags');
        $TagContainer = new TagContainer();
        $TaggedObjectContainer = new TaggedObjectContainer();
        $Tags = $TaggedObjectContainer->getTagsForObject($Artist);
        if (!$Tags){
            $Tag = new Tag();
            $Tag->setParameter('TagText',$TagText);
            $TagContainer->addTag($Tag);
        }
        else{
            $Tag = current($Tags);
            if ($Tag->getParameter('TagText') != $TagText){
                $Tag->setParameter('TagText',$TagText);
                $TagContainer->updateTag($Tag);
            }
        }
        $TaggedObjectContainer->addTagToObject($Tag,$Artist);
        $ArtistTab->addPlainText('Tag:',"<strong>".$Tag->getParameter('TagText')."</strong><br /><strong>Use:</strong> Any photos tagged with this tag will become associated with this artist");
    }
    
	$form->addTab($ArtistTab);

	/***********************************************************************
	*
	*	Festival Tabs
	*
	***********************************************************************/
    if ($AdditionalTab != "" and !array_key_exists($AdditionalTab,$FestivalInfoArray)){
        $FestivalInfoArray[$AdditionalTab] = new Parameterized_Object();
    }
    $Years = array();
    foreach ($FestivalInfoArray as $year => $FestivalInfo){
        
        // Year Prefix for the variables to remove unwanted characters
        $year_prefix = getSafeYear($year);
        
        // Only show acts in the Festival Lineup in the band dropdown
        $AllArtists = $FestivalArtistContainer->getAllArtists($year,'ArtistFullName');
        $Bands = array();
        $Bands[""] = "&lt;Choose a group&gt;";
        if (is_array($AllArtists)){
            foreach ($AllArtists as $_Artist){
                if (!$_Artist->getParameter('ArtistBand') and $_Artist->getParameter('ArtistID') != $Artist->getParameter('ArtistID')){
                    $Bands[$_Artist->getParameter('ArtistID')] = $_Artist->getParameter('ArtistFullName');
                }
            }
        }
    
        $Years[] = $year;
        $TabName = $year_prefix."FestivalTab";
        $$TabName = new HTML_Tab($TabName,$year.' '.vocabulary('Festival').' Info');
        
        if ($_GET[YEAR_PARM] != "" and $year === urldecode($_GET[YEAR_PARM]) and $Artist->getParameter('ArtistID') != ""){
            $DefaultTab = $TabName;
        }
            
        if (count($Bands) > 1){
            $BandSelectCell = "".vocabulary('Artist')." is actually a member of ...";
            $BandSelectCell.= HTML_Form::returnSelect($year_prefix.'ArtistBand',$Bands,intval($FestivalInfo->getParameter('ArtistBand')));
            $BandSelectCell.= "...and shouldn't appear in the ".vocabulary('Festival')." lineup.";
            $$TabName->addPlainText('Group (optional):',$BandSelectCell);
        }

		$$TabName->addPlainText('Do Not Publish:',HTML_Form::returnCheckbox($year_prefix.'ArtistDoNotPublish',$FestivalInfo->getParameter('ArtistDoNotPublish')).' Check to prevent this '.vocabulary('Artist').' from being listed in the published lineup');

        $$TabName->addTextArea($year_prefix.'ArtistDescription', 'Brief Description:',$FestivalInfo->getParameter('ArtistDescription'),50,3,0);
        $$TabName->addTextArea($year_prefix.'ArtistLongDescription', 'Longer Description:',$FestivalInfo->getParameter('ArtistLongDescription'),50,6,0);
        $$TabName->addTextArea($year_prefix.'ArtistProgramDescription', 'Program Bio:',$FestivalInfo->getParameter('ArtistProgramDescription'),50,6,0);
        $$TabName->addFile($year_prefix.'ArtistMedia','Upload Media:');
            
        $AllMedia = array();
        $FestivalArtist = new FestivalArtist($Artist->getParameter('ArtistID').'_'.$year);
        $AllMedia = $MediaContainer->getAssociatedMedia($FestivalArtist);
    
        if (is_array($AllMedia) and count($AllMedia)){
                $MediaString = "<table cellpadding='5' border='0'><tr>";
                foreach ($AllMedia as $Media){
                        $MediaString.= "<td valign=top align=left>";
                        if ($Media->getParameter('MediaType') == MEDIA_TYPE_GALLERYIMAGE){
                            $GalleryImage = $GalleryImageContainer->getGalleryImage($Media->getParameter('MediaLocation'));
                            if (is_a($GalleryImage,'GalleryImage')){
                                $Gallery = $GalleryImage->getGallery();
                                if (is_a($Gallery,'Gallery')){
									$MediaString.= '<div class="image-div">'."\n";
                                    $MediaString.= "<img src='".$Gallery->getGalleryDirectory().$GalleryImage->getParameter('GalleryImageThumb')."' class='gallery-".$GalleryImage->getParameter('GalleryID')." image-".$GalleryImage->getParameter('ImageID')."'><br>";
                                    $MediaString.= "<a href='".$Gallery->getGalleryDirectory().$GalleryImage->getParameter('GalleryImageOriginal')."' target='_blank'>".$GalleryImage->getParameter('GalleryImageOriginal')."</a> <font size=-1>(<a href='javascript:deleteMedia(".$Media->getParameter('MediaID').");'>delete</a>, <a href=\"#\" id=\"edit-image-".$Media->getParameter('MediaLocation')."\" class=\"edit-image\">edit</a>)</font><br>";
									$MediaString.= '</div>'."\n";
                                }
                            }
                        }
                        elseif($AllowMP3s and $Media->getParameter('MediaType') == MEDIA_TYPE_MP3){
                                $Song = $SongContainer->getSong($Media->getParameter('MediaLocation'));
                                if (is_a($Song,'Song')){
                                    $MediaString.= "<a href='".$SongContainer->getMusicURL().htmlentities($Song->getParameter('SongFileName'),ENT_QUOTES)."' target='_blank'><img src='".$Package->getPackageURL()."/admin/images/mp3.jpg' border='0'><br />".$Song->getParameter('SongTitle')."</a> <font size=-1>(<a href='javascript:deleteMedia(".$Media->getParameter('MediaID').");'>delete</a>)</font>";
                                    $MediaString.= "<br /><font size='-1'>Title: </font>".HTML_FORM::returnText("Song_{$SongNumber}_title",$Song->getParameter('SongTitle'));
                                    $form->addHidden("Song_{$SongNumber}_id",$Song->getParameter('SongID'));
                                    $SongNumber++;
                                }
                        }
                        else{
                                $MediaString.= "<a href='".BASE_URL.ARTIST_DIRECTORY_REL.$Media->getParameter('MediaLocation')."' target='_blank'>".basename($Media->getParameter('MediaLocation'))."</a> <font size=-1>(<a href='javascript:deleteMedia(".$Media->getParameter('MediaID').");'>delete</a>)</font><br>";
                        }
                        $MediaString.= "</td>";
                }
                $MediaString.= "</tr></table>";
                $$TabName->addPlainText('Loaded Media:',$MediaString);
        }
        
        if ($Package->isFullVersion()){
            $$TabName->addPlainText('&nbsp;',"<hr>");
        	/***********************************************************************
        	*
        	*	Technical Requirements Portion
        	*
        	***********************************************************************/

        	$$TabName->addText($year_prefix.'ArtistFee','Fee:',htmlspecialchars($FestivalInfo->getParameter('ArtistFee')));
            $$TabName->addTextArea($year_prefix.'ArtistFeeDescription', 'Fee Details:',$FestivalInfo->getParameter('ArtistFeeDescription'),50,3,0);
            $$TabName->addTextArea($year_prefix.'ArtistAccommodationDescription', 'Accommodation Details:',$FestivalInfo->getParameter('ArtistAccommodationDescription'),50,3,0);
    
            foreach ($PassTypes as $PassType){
                $pattern = "/$PassType:(.*)/";
                if (preg_match($pattern,$FestivalInfo->getParameter('ArtistGuests'),$matches)){
                    $Passes = trim($matches[1]);
                }
                else{
                    $Passes = "";
                }
                $$TabName->addTextArea($year_prefix.$PassType.'Passes', "$PassType Passes:<p style='font-weight:normal;font-size:0.8em;margin-top:0;'>(Separate multiple names by commas)</p>",$Passes,50,2,0);
            }
            $$TabName->addTextArea($year_prefix.'ArtistTechnicalRequirements', 'Tech Requirements:',$FestivalInfo->getParameter('ArtistTechnicalRequirements'),50,3,0);
        }
        
	    $form->addTab($$TabName);
    }
        
        
    
    
	
	/***********************************************************************
	*
	*	Message Tab
	*
	***********************************************************************/
	// We display messages on a new tab.  this will be the default tab that displays when the page gets redisplayed	
	if ($MessageList->hasMessages() and !$popup){
		$MessageTab = new HTML_Tab('Messages',$MessageList->getSeverestType());
		$MessageTab->addPlainText('Messages',"<p>&nbsp;<p>".$MessageList->toBullettedString());
		$DefaultTab = 'MessageTab';
		$form->addTab($MessageTab);
	}
	
	$$DefaultTab->setDefault();
	
	// Here are the buttons
    if ($popup and $id == ""){
            $form->addSubmit('save_n_add','Save & Add Another');
            $form->addSubmit('save_n_close','Save & Close');
    }
    else{
            $form->addSubmit('save','Save Changes');
    }
	$form->addSubmit('cancel','Cancel');
	
	// Some hidden fields to help us out 
	$form->addHidden('form_submitted','true');
	$form->addHidden('id',$id);
	$form->addHidden('sel',$sel);
	$form->addHidden('del','');
	$form->addHidden('from',$from);
	$form->addHidden('additional_tab',$AdditionalTab);
	$form->addHidden('years',implode(",",$Years));
	
	// Finally, we set the Smarty variables as needed.
        if ((isset($close_window) and $close_window) or (isset($update_parent) and $update_parent)){
                $start_functions = array();
                if (!$_POST['cancel']){
                        if ($update_parent){
                                $start_functions[] = "updateParentWindow('$sel','$update_id','".str_replace("'","\\'",$update_fullname)."');"; 
                        }
                        else{
                                $start_functions[] = "updateParentWindow('$sel','".$Artist->getParameter('ArtistID')."','".str_replace("'","\\'",$Artist->getParameter('ArtistFullName'))."');"; 
                        }
                        $updateParentScript = "<script language='JavaScript'>\n<!--
                        
                                function updateParentWindow(theSel,theValue,theText){
                        ";
                        if ($newArtist){
                                $updateParentScript.= "window.opener.addOption(window.opener.document.getElementById(theSel),theText,theValue);";
                        }
                        else{
                                $updateParentScript.= "window.opener.updateArtist(window.opener.document.getElementById(theSel),theText,theValue);";
                        }
                        $updateParentScript.= "
                                }
                                --></script>
                        ";
                        $admin_head_extras.= $updateParentScript;
                }
                if ($close_window){
                        $start_functions[] = "window.close();";
                }
	        $smarty->assign('admin_start_function',$start_functions);
        }
        $UpdateAsTypingScript = "<script language='JavaScript'>\n<!--
        var strFullName;
        var strFirstName;
        var strLastName;
        function updateFullName() 
        { 
                document.Update_Form.ArtistFullName.value = document.Update_Form.ArtistFirstName.value;
                if (document.Update_Form.ArtistLastName.value != ''){
                        if (document.Update_Form.ArtistFullName.value != ''){
                              document.Update_Form.ArtistFullName.value+= ' ';
                        }
                        document.Update_Form.ArtistFullName.value+= document.Update_Form.ArtistLastName.value;
                }
                updateStrings();
        } 
        
        function updateFirstLastNames(){
           var str = document.Update_Form.ArtistFullName.value;
           var tmp;
           var pos;
           pos = str.search(document.Update_Form.ArtistFirstName.value+' ');
           if (pos >= 0){
                tmp = document.Update_Form.ArtistFirstName.value;
                document.Update_Form.ArtistLastName.value = str.substr(tmp.length+1);
                updateStrings();
                return;
           }
           pos = str.search(' '+document.Update_Form.ArtistLastName.value);
           if (pos >= 0){
                document.Update_Form.ArtistFirstName.value = str.substr(0,pos);
                updateStrings();
                return;
           }
           var first_space = str.search(' ');
           if (first_space >= 0){
                document.Update_Form.ArtistFirstName.value = str.substr(0,first_space);
                document.Update_Form.ArtistLastName.value = str.substr(first_space + 1);
           }
           else{
                document.Update_Form.ArtistFirstName.value = str;
                document.Update_Form.ArtistLastName.value = '';
           
           }
           updateStrings();
        }
        function updateStrings(){
           strFullName = document.Update_Form.ArtistFullName.value;
           strFirstName = document.Update_Form.ArtistFirstName.value;
           strLastName = document.Update_Form.ArtistLastName.value;
        }

        function trigger() 
        { 
          refresh(); 
          setTimeout('trigger()', 100); 
        } 

        function refresh() 
        { 
          if (strFullName == null){ 
                strFullName = document.Update_Form.ArtistFullName.value;
          } 
          if (strFirstName == null){ 
               strFirstName = document.Update_Form.ArtistFirstName.value;   
          } 
          if (strLastName == null){ 
               strLastName = document.Update_Form.ArtistLastName.value;   
          } 
          if (strFullName != document.Update_Form.ArtistFullName.value){
               //alert ('updating first/last names');
                updateFirstLastNames();
          }
          if (strFirstName != document.Update_Form.ArtistFirstName.value || 
              strLastName != document.Update_Form.ArtistLastName.value){
               //alert ('updating full name');
                updateFullName();
          }
        } 

		jQuery(document).ready(function(){
			jQuery('textarea#ArtistLongDescription').markItUp(myMarkdownSettings);
		});
        
        function deleteMedia(id){
            document.Update_Form.del.value = id;
            document.Update_Form.submit();
        }

                --></script>     
        ";        
        $admin_head_extras.= $UpdateAsTypingScript;

		$thumbEditScript = '
		<script type="text/javascript">
			jQuery(function($){
				var $image,$dialog,jcrop_api;
				$("#Update_Form").find(".edit-image").click(function(e){
					$image = $(this).parents("div.image-div").find("img");
					if (typeof $image.data("src") == "undefined"){
						$image.data("src",$image.attr("src"));
					}
					$image.css("visibility","hidden");
					$image.parent("div.image-div").css("background","url('.plugins_url('topquark').'/lib/js/multiBox/Images/mb_Components/loader.gif) 50% 50% no-repeat");
					$dialog = $("#edit-image-dialog");
					if (!$dialog.length){
						$dialog = $("<div/>").attr("id","edit-image-dialog").appendTo("body");
					}
					$dialog.empty();
					$.get(ajaxurl,{
						action: "edit_image_markup",
						id: $(this).attr("id").match(/[0-9]+$/)[0]
					},function(results){
						try{
							var result = $.parseJSON(results);
						}
						catch(e){
							alert("Unable to retrieve information about image.  Sorry about that");
							return;
						}
						$dialog.html(result.markup).dialog({
							resizable: false,
							modal: true,
							draggable: false,
							height: "auto",
							width: result.width,
							title: "Edit Thumbnail",
							close: function(event, ui){
								jcrop_api.destroy();
								$image.attr("src",$image.data("src"));
								$image.css("visibility","visible");
								$image.parent("div.image-div").css("background","none");
							}
						});
						setupJCrop();
					});
					e.stopPropagation();
					e.preventDefault();
					return false;
				});
				
				var twidth, theight;
				setupJCrop = function(){
					if (typeof jcrop_api != "undefined"){
						jcrop_api.destroy();
					}
					// Create variables (in this scope) to hold the API and image size
					var boundx, boundy;
					twidth = parseInt($("#edit-image-thumb").attr("width"));
					theight = parseInt($("#edit-image-thumb").attr("height"));
					$("#edit-image-original").Jcrop({
						onChange: updatePreview,
						onSelect: updatePreview,
						aspectRatio: twidth/theight
					},function(){
				        // Use the API to get the real image size
				        var bounds = this.getBounds(); // image [width, height]
				        boundx = bounds[0];
				        boundy = bounds[1];
				        // Store the API in the jcrop_api variable
				        jcrop_api = this;
					});
					function updatePreview(c){
						// c = coordinates = {h,w,x,x2,y,y2}
						if ($("#edit-image-thumb").css("display") == "inline"){
							$("#edit-image-thumb").css("display","none");
							$("#edit-image-preview").css("display","inline");
						}
						if (parseInt(c.w) > 0){
							var rx = twidth / c.w;
							var ry = theight / c.h;

							$("#edit-image-preview").css({
								width: Math.round(rx * boundx) + "px",
								height: Math.round(ry * boundy) + "px",
								marginLeft: "-" + Math.round(rx * c.x) + "px",
								marginTop: "-" + Math.round(ry * c.y) + "px"
							});
						}
					}			
					$("#edit-image-save").click(function(){
						var s = jcrop_api.tellSelect();
						$("#edit-image-thumb").css({display:"inline",visibility:"hidden"});
						$("#edit-image-preview-wrapper").css("background","url('.plugins_url('topquark').'/lib/js/multiBox/Images/mb_Components/loader.gif) 50% 50% no-repeat");
						$("#edit-image-preview").css("display","none");
						var gallery_id = $image.attr("class").match(/gallery-([0-9]+)/)[1];
						var image_id = $image.attr("class").match(/image-([0-9]+)/)[1];
						$.ajax({
			               type: "GET",
			               url: "'.plugins_url('topquark').'/lib/packages/Gallery/admin/edit_image.php?ajax=true",
			               data: { action : "new-thumb", id: gallery_id, image_id: image_id, x : s.x, y : s.y, w : s.w, h : s.h, dest_w : twidth, dest_h : theight },
			               success: function(response) { 
								$image.data("src",response.new_thumb);
								$dialog.dialog("close");
								return;
			               }
						})
					});
				}
			});
		</script>
		';
	$admin_head_extras.= $thumbEditScript;
	$smarty->assign('includes_tabbed_form',true);
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	$smarty->assign('Tabs',$form->getTabs());
	$smarty->assign('admin_head_extras',$admin_head_extras);
        if ($popup){
                $smarty->assign('hide_navigation',true);
        }
        
    function getSafeYear($year){
        return preg_replace("/[^a-zA-Z0-9]/","_",$year);
    }    
	
	
?>
<?php   $smarty->display('admin_form.tpl'); ?>
<script language='JavaScript1.2'> 
function trigger() 
{ 
  refresh(); 
  setTimeout('trigger()', 100); 
} 

setTimeout('trigger()', 100); 
</script> 
