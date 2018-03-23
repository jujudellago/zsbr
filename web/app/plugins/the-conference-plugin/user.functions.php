<?php

    include_once(PACKAGE_DIRECTORY.'Common/UserFunction.php');
    
    class FestivalApp__UserFunctions extends UserFunction{
        
        function FestivalApp__UserFunctions(){
            global $Bootstrap;
            $this->setPackage('FestivalApp');

			if (!defined('YEAR_PARM')){
				define('YEAR_PARM','year');
			}
            
            $this->setFunctionParameters(); // See below
        }
    
        function FestivalApp__UserPaint($parms,$package,&$smarty){
            global $Bootstrap,$ArtistDetailPage,$ShowDetailsPage,$Year;
			$parms['subject'] = strtolower($parms['subject']);
			
            $Year = ($_GET[YEAR_PARM] != "" ? urldecode($_GET[YEAR_PARM]) : ($parms['year'] != "" ? $parms['year'] : date("Y")));
            
			if (CMS_PLATFORM == 'WordPress'){
				if ($parms['artist_article'] == ""){
					$parms['artist_article'] = apply_filters('topquark_FestivalApp_permalink','?subject=lineup','lineup');
				}
				if ($parms['schedule_article'] == ""){
					$parms['schedule_article'] = apply_filters('topquark_FestivalApp_permalink','?subject=schedule','schedule');
				}
				if ($parms['show_details_article'] == ""){
					$parms['show_details_article'] = apply_filters('topquark_FestivalApp_permalink','?subject=show_details','show_details');
				}
			}
			if ($_GET['subject'] != ""){
				if (!in_array($parms['subject'],array('lineup_list'))){ // don't override these ones...
					$parms['subject'] = $_GET['subject'];
				}
			}
			
            $ArtistDetailPage = $parms['artist_article'];
            if ($ArtistDetailPage == ""){
                $ArtistDetailPage = $_GET[INDEX_PAGE_PARM];
            }
			$ShowDetailsPage = $parms['show_details_article'];
			
			// Okay, so, we're going to enable the cache
			// First thing to know is what parms are we dealing with...
			if ($package->enable_cache){
				parse_str($_SERVER['QUERY_STRING'],$cache_parms);
				if (array_key_exists('timer',$cache_parms)){
					unset($cache_parms['timer']);
				}
				if (!array_key_exists('year',$cache_parms)){
					$cache_parms['year'] = $Year;						
				}
				if (!array_key_exists('subject',$cache_parms)){
					$cache_parms['subject'] = $parms['subject'];						
				}
				$cache_parms = array_merge($parms,$cache_parms);
			}
			if ($package->enable_cache){
				if (($cachedPage = $package->getCachedPage($cache_parms)) !== false){
					return $cachedPage;
				}
			}			
			
            
            $FestivalContainer = new FestivalContainer();
            $FestivalArtistContainer = new FestivalArtistContainer();
            $ArtistContainer = new ArtistContainer();

			$save_template_dir = $smarty->template_dir;
			$smarty->template_dir = dirname(__FILE__).'/smarty/';
			$smarty->assign('Year',$Year);
			$smarty->assign('packageURL',$package->getPackageURL());
			$smarty->assign_by_ref('package',$package);
            $smarty->assign('SchedulePage',user__getArticleURL($parms['schedule_article'],$Year));
            $smarty->assign('ShowDetailPage',user__getArticleURL($parms['show_details_article'],$Year));
            $ArtistURL = user__getArticleURL($parms['artist_article'],$Year).'speaker=';
			$smarty->assign('ArtistURL',$ArtistURL);
			$p = $Bootstrap->usePackage('Gallery');
			$smarty->assign('GalleryDirectory',$p->getPackageDirectory());
			$smarty->assign('GalleryURL',$p->base_url);
			$smarty->assign('DOC_BASE',DOC_BASE);

            $Festival = $FestivalContainer->getFestival($Year);
            if (!is_a($Festival,'Festival')){
                // try to default to last year's festival
            	if (is_numeric($Year)){
            	    $LastYear = $Year - 1;
                    $Festival = $FestivalContainer->getFestival($LastYear);
                    if (is_a($Festival,'Festival') and $Festival->getParameter('FestivalLineupIsPublished')){
                        $Year = $LastYear;
                    }
                }
                else{
                    $Year = date("Y") - 1;
                }
            }
			if (is_a($Festival,'Festival')){
				if ($Festival->getParameter('FestivalEndDate') >= date("Y-m-d")){
					$smarty->assign('CurrentYear',true);
				}
				else{
					$smarty->assign('CurrentYear',false);
				}
			}
            switch ($parms['subject']){
			case 'lineup':
			case 'lineup_list':
				$NotPublishedMessage = "<p style='".$parms['lineup_style']."'>The $Year lineup is not yet published.  Please check back again soon.</p>";
	            if (!is_a($Festival,'Festival') or !$Festival->getParameter('FestivalLineupIsPublished')){
	                $return.= $NotPublishedMessage;
					break;
	            }
	
				// If we're here, the lineup is published, hurrah
                switch ($parms['artist_order']){
                case 'alphabetic':
                case 'first_name':
                    $sort_field = 'ArtistFullName';
                    $sort_dir = 'asc';
                    break;
                case 'last_name':
                    $sort_field = 'ArtistLastName';
                    $sort_dir = 'asc';
                    break;
                case 'random':
                    $sort_field = 'RAND()';
                    $sort_dir = '';
                    break;
                default:
                    $sort_field = "";
                    $sort_dir = "";
                }
	            $Lineup = $Festival->getLineup($sort_field,$sort_dir);
                
                if (is_a($Lineup,'FestivalArtist')){
                    $Lineup = array($Lineup);
                }
                if (!is_array($Lineup)){
	                $return.= $NotPublishedMessage;
                }
                else{            
	        		if ($_GET['speaker'] != "" and array_key_exists($_GET['speaker'],$Lineup)){
						$Artist = $Lineup[$_GET['speaker']];
					}
					if (!empty($parms['include'])){
						$include = explode(",",$parms['include']);
						if(!is_array($include)){
							$include = array();
						}
						$include = array_map('trim',$include);
						$Lineup = array_filter($Lineup,create_function('$a','
							return in_array($a->getParameter("ArtistID"),explode(",","'.implode(',',$include).'"));
						'));
					}
					if (!empty($parms['exclude'])){
						$exclude = explode(",",$parms['exclude']);
						if(!is_array($exclude)){
							$exclude = array();
						}
						$exclude = array_map('trim',$exclude);
						$Lineup = array_filter($Lineup,create_function('$a','
							return !in_array($a->getParameter("ArtistID"),explode(",","'.implode(',',$exclude).'"));
						'));
					}
					if (function_exists('apply_filters')){
						$Lineup = apply_filters('FestivalApp_paint_lineup_set_lineup',$Lineup,$parms,$package,array(&$smarty));
					}
					$smarty->assign('Lineup',$Lineup);
                   
					if ($parms['subject'] == 'lineup' and isset($Artist)){
						// Let's display the Artist Detail
                        $Artist->setParameter('ArtistWebsiteURL',$Artist->getParameter('ArtistWebsite'));
                        $Artist->setParameter('ArtistWebsite',preg_replace("/^[^:]*:(\/\/)?([^\/].*)[\/]?$/U","$2",$Artist->getParameter('ArtistWebsite')));
            	        if (file_exists($package->etcDirectory."{$Year}ScheduleNames.txt")){
            	            // Schedule has been published, okay to get the Artist Shows
            	            $ShowContainer = new ShowContainer();
            	            
							$Shows = $ShowContainer->getAllShowsWithArtist($Artist->getParameter('FestivalYear'),$Artist->getParameter('ArtistID'));
                            if (is_array($Shows) and count($Shows)){
								$Artist->setParameter('ArtistShows',user__getArtistShows($Artist,$Shows,$smarty));
								$BandMemberShows = $ShowContainer->getAllBandMemberShows($Artist->getParameter('FestivalYear'),$Artist->getParameter('ArtistID'),false);
								$RenderedBandMemberShows = array();
                                if (is_array($BandMemberShows) and count($BandMemberShows)){
                                    foreach($BandMemberShows as $BandMemberID => $_BandMemberShows){
                                        $_BandMember = $_BandMemberShows['Artist'];
                                        $Shows = $_BandMemberShows['Shows'];
                                        if (is_array($Shows) and count($Shows)){
											$RenderedBandMemberShows[] = user__getArtistShows($_BandMember,$Shows,$smarty);
                                        }
                                    }
                                }
								$Artist->setParameter('BandMemberShows',$RenderedBandMemberShows);
                            }
                        }
						$smarty->assign('Artist',$Artist);
						if ($Bootstrap->packageExists('flamplayer')){
							$smarty->assign('flamPackage',$Bootstrap->usePackage('flamplayer'));
						}
                        if ($Bootstrap->packageExists('Tags')){
                            $Bootstrap->usePackage('Tags');
                            if (!$TagContainer){
                                $TagContainer = new TagContainer();
                                $TaggedObjectContainer = new TaggedObjectContainer();
                            }
                            $Tags = $TaggedObjectContainer->getTagsForObject($ArtistContainer->getArtist($Artist->getParameter('ArtistID')));
                            if ($Tags){
                                $Tag = current($Tags);
                                $_Images = $TaggedObjectContainer->getAllTaggedObjects($Tag->getParameter('TagID'),'GalleryImage');
                                if (is_array($_Images)){
									$smarty->assign('TaggedImagesURL',$smarty->get_template_vars('GalleryURL').'&amp;tags='.urlencode($Tag->getParameter('TagText')));
									$smarty->assign('TaggedImages',$_Images);
                                }
                            }
                        }
						$Template = 'festivalapp.artist_detail.tpl';
					}
					else{
	                    switch ($parms['display_style']){
	                    case 'expanded':
							$Template = 'festivalapp.expanded_lineup.tpl';
							break;
	                    case 'floated':
							$Template = 'festivalapp.floated_lineup.tpl';
	                        break;
	                    default:
							$Template = 'festivalapp.lineup_listing.tpl';
		                    break;
	                    }
					}
	            }
            
				break;
			case 'schedule':
        		if (!file_exists($package->etcDirectory."{$Year}ScheduleNames.txt")){
					$smarty->assign('NotFound',true);
				}
				else{
					$SchedulePainter = new SchedulePainter();
                    if ($parms['show_times'] == 'false'){
                        $SchedulePainter->show_times = false;
                    }
                    $SchedulePainter->setShowClassCallback("user__setShowClass");
                	$SchedulePainter->setShowTitleURLCallback("user__getShowTitleURL");
                	$SchedulePainter->setArtistURLCallback("user__getArtistURL");

					$smarty->assign_by_ref('SchedulePainter',$SchedulePainter);
                    $Serialized = @file_get_contents($package->etcDirectory."{$Year}ScheduleNames.txt");
					$ScheduleNames = unserialize($Serialized);
                    $smarty->assign('ScheduleNames',$ScheduleNames);
	                $Type = ($_GET['type'] != "" ? urldecode($_GET['type']) : ($parms['show_type'] != '' ? $parms['show_type'] : ""));
                    if ($Type == ""){
                        $Type = current(array_keys($ScheduleNames));
                    }
					$smarty->assign('Type',$Type);
					$smarty->assign('FestivalArtists',$Festival->getLineup('ArtistFullName','ASC'));
                    if (file_exists($package->etcDirectory."{$Year}_{$Type}_ListingsArray.txt")){
                        $Serialized = @file_get_contents($package->etcDirectory."{$Year}_{$Type}_ListingsArray.txt");
                        $ShowListingsArray = unserialize($Serialized);
						$smarty->assign('ShowListingsArray',$ShowListingsArray);
                    }
				}
				$Template = 'festivalapp.schedule.tpl';
				break;
			case 'show_details':
			case 'agenda':
	        	if (!file_exists($package->etcDirectory."{$Year}Shows.txt")){
	        	    $Shows = "";
	            }
	            else{
	                $Serialized = file_get_contents($package->etcDirectory."{$Year}Shows.txt");
	                $Shows = unserialize($Serialized);
	            }
            
	            if ($Shows == ''){
					$smarty->assign('NotFound',true);
	            }
	            else{
					if (!empty($parms['show_type'])){
						$Shows = array_filter($Shows,create_function('$s','return $s->getParameter("ShowScheduleUID") == "'.$parms['show_type'].'";'));
					}
				    if ($_GET['sort'] != 'alpha'){
				        // Need to sort it specially
				        uasort($Shows,'user__SortByDay');
				    }
					if (function_exists('apply_filters')){
						$Shows = apply_filters('FestivalApp_paint_show_details_set_shows',$Shows,$parms,$package,array(&$smarty));
					}
					$smarty->assign('Shows',$Shows);
                    if ($Bootstrap->packageExists('Tags')){
						$smarty->assign('TagPackage',$Bootstrap->usePackage('Tags'));
						$smarty->assign('TagContainer',new TagContainer());
						$smarty->assign('TaggedObjectContainer',new TaggedObjectContainer());
                    }
					switch($parms['subject']){
					case 'show_details':
						$Template = "festivalapp.show_details.tpl";
						break;
					case 'agenda':
						$Template = "festivalapp.agenda.tpl";
						break;
					}
				}
				break;
            case 'festival_archives':
                if ($parms['lineup_published'] == 'false'){
                    $LineupPublished = false;
                }
                else{
                    $LineupPublished = true;
                }
                if ($parms['schedule_published'] == 'true'){
                    $SchedulePublished = true;
                }
                else{
                    $SchedulePublished = false;
                }
                $return = user__getFestivalArchives($LineupPublished,$SchedulePublished);
                break;
            case 'all_festival_artists':
                $FestivalArtistContainer = new FestivalArtistContainer();
                $AllFestivalArtists = $FestivalArtistContainer->getAllArtists("","ArtistFullName","asc");
                $Festivals = $FestivalContainer->getAllFestivals();

                $return = "";
                $CurrentLetter = "";
                foreach ($AllFestivalArtists as $key => $FestivalArtist){
                    if ($FestivalArtist->getParameter('ArtistBand') != ""){
                        unset($AllFestivalArtists[$key]);
                    }
                }
				$return .= "<p>All ".count($AllFestivalArtists)." ".pluralize('Artist')." who have been at the ".vocabulary('Festival')."</p>";
                foreach ($AllFestivalArtists as $FestivalArtist){
                    if (strtoupper(substr($FestivalArtist->getParameter('ArtistFullName'),0,1)) != $CurrentLetter){
                        if ($CurrentLetter != ""){
                            $return.= "</ul>\n";
                        }
                        else{
                            $return.= "<div class='LeftColumn'>\n";
                        }
                        $CurrentLetter = strtoupper(substr($FestivalArtist->getParameter('ArtistFullName'),0,1));
                        if ($CurrentLetter >= "M"){
                            $return.= "</div>\n";
                            $return.= "<div class='RightColumn'>\n";
                        }
                        $return.= "<h2>$CurrentLetter</h2>\n";
                        $return.= "<ul class='AllArtistList'>\n";
                    }
                    $tmpFestival = $Festivals[$FestivalArtist->getParameter('FestivalYear')];
                    $LIOpened = false;
                    if ($tmpFestival->getParameter('FestivalLineupIsPublished')){
                        $return.= "<li>";
                        $return.= "<a href='".user__getArticleURL($ArtistDetailPage,$FestivalArtist->getParameter('FestivalYear'))."&speaker=".$FestivalArtist->getParameter('ArtistID')."'>".$FestivalArtist->getParameter('ArtistFullName')."</a>\n";
                        $return.= " (<a href='".user__getArticleURL($ArtistDetailPage,$FestivalArtist->getParameter('FestivalYear'))."&speaker=".$FestivalArtist->getParameter('ArtistID')."'>".$FestivalArtist->getParameter('FestivalYear')."</a>";
                        $LIOpened = true;
                        $sep = ",";
                    }
                    if (is_array($FestivalArtist->getParameter('AllFestivalYears'))){
                        if (!$LIOpened){
                            $tmpFestival = $Festivals[current($FestivalArtist->getParameter('AllFestivalYears'))];
                            if ($tmpFestival->getParameter('FestivalLineupIsPublished')){
                                $return.= "<li>";
                                $return.= "<a href='".user__getArticleURL($ArtistDetailPage,current($FestivalArtist->getParameter('AllFestivalYears')))."&speaker=".$FestivalArtist->getParameter('ArtistID')."'>".$FestivalArtist->getParameter('ArtistFullName')."</a>";
                                $return.= " (";
                                $LIOpened = true;
                            }
                        }
                        foreach ($FestivalArtist->getParameter('AllFestivalYears') as $FestivalYear){
                            $tmpFestival = $Festivals[$FestivalYear];
                            if ($tmpFestival->getParameter('FestivalLineupIsPublished')){
                                if ($FestivalYear != $FestivalArtist->getParameter('FestivalYear')){
                                    if (!$LIOpened){
                                        $return.= "<li>";
                                        $return.= "<a href='".user__getArticleURL($ArtistDetailPage,$FestivalYear)."&speaker=".$FestivalArtist->getParameter('ArtistID')."'>".$FestivalArtist->getParameter('ArtistFullName')."</a>";
                                        $return.= " (";
                                        $LIOpened = true;
                                        $sep = "";
                                    }
                                    $return.= $sep."<a href='".user__getArticleURL($ArtistDetailPage,$FestivalYear)."&speaker=".$FestivalArtist->getParameter('ArtistID')."'>".$FestivalYear."</a>";
                                    $sep = ",";
                                }
                            }
                        }
                    }
                    if ($LIOpened){
                        $return.= ")</li>\n";
                    }
                }
                if ($CurrentLetter != ""){
                    $return.= "</ul>\n";
                    $return.= "</div>\n";
                }
                break;
			default:
				$return = "Sorry, I don't know how to paint ".$parms['subject'];
			}
            if ($Template != ""){
                $smarty->assign('parms',$parms);
				$return = $smarty->fetch($Template);
				$smarty->template_dir = $save_template_dir;
            }
			if ($package->enable_cache){
				$package->writeCachedPage($cache_parms,$return);
			}
            return $return;
        }
        
        function FestivalApp__UserRetrieve($parms,$package,&$smarty){
            $FestivalContainer = new FestivalContainer();
            $results = "";
            
            $Year = ($_GET[YEAR_PARM] != "" ? urldecode($_GET[YEAR_PARM]) : $parms['year'] != "" ? $parms['year'] : date("Y"));
            
            switch ($parms['subject']){
            case 'Lineup':
                $Festival = $FestivalContainer->getFestival($Year);
                if (is_a($Festival,'Festival') and $Festival->getParameter('FestivalLineupIsPublished')){
                    $results = $Festival->getLineup();
                }
                break;
            case 'DaysLeftTilFestival':    
                $Festivals = $FestivalContainer->getAllFestivals();
                if (is_array($Festivals)){
                    $NextFestival = current($Festivals);
                    $results =  round((strtotime($NextFestival->getParameter('FestivalStartDate')) - strtotime(date('Y-m-d')))/86400);
                }
                break;
            case 'ArchiveYears':
                if ($parms['lineup_published'] == 'false'){
                    $LineupPublished = false;
                }
                else{
                    $LineupPublished = true;
                }
                if ($parms['schedule_published'] == 'true'){
                    $SchedulePublished = true;
                }
                else{
                    $SchedulePublished = false;
                }
                $results = user__getFestivalArchives($LineupPublished,$SchedulePublished);
                break;
            default:
                $results = "Don't know how to retrieve ".$parms['subject'];
                break;
            }
            if ($parms['var'] != ""){
                $smarty->assign($parms['var'],$results);
            }
            else{
                $smarty->assign('results',$results);
            }
			if ($parms['display']){
				return $results;
			}
			else{
	            return null;   
			}
        }   

		function FestivalApp__UserAjax($parms,$package,&$smarty){
			$Bootstrap = Bootstrap::getBootstrap();
			
			$return = array();
			
			$Year = ($parms[YEAR_PARM] != "" ? $parms[YEAR_PARM] : date("Y")); 
			
			switch ($parms['subject']){
			case 'get_statistics':
				include_once(PACKAGE_DIRECTORY."Common/ObjectLister.php");
				$FestivalContainer = new FestivalContainer();
				$ScheduleContainer = new ScheduleContainer();
				global $Festival,$ShowContainer;
				
				$Festival = $FestivalContainer->getFestival($Year);
				$ShowContainer = new ShowContainer();
				
	    		$ObjectLister = new ObjectLister();
	    	    $Bootstrap->addTimestamp('Getting All '.pluralize('Artist'));
	    		$AllArtists = $Festival->getAllArtists("ArtistFullName","asc");
	    	    $Bootstrap->addTimestamp('Got All '.pluralize('Artist'));

	    		/** TODO - make it so that you can put band members for the listing. 
	    		foreach ($AllArtists as $tmpArtistID => $tmpArtist){
	    		    if ($tmpArtist->getParameter('ArtistBand') != ""){
	    		        $tmpArtist[$tmpArtist->getParameter('ArtistBand')]->setParameter('ArtistEntourage',$tmpArtistID);
	    		    }
	    		}   
	    		*/
	    		$ObjectLister->addColumn(vocabulary('Artist').' Name','displayArtistName','20%');
	    		function displayArtistName($Object){
	    		 	if (is_a($Object,'Parameterized_Object')){
	    	            return $Object->getParameter('ArtistFullName');
	    		 	}
	    		 	else{
	    		 		return "aInvalid Object Passed: ".get_class($Object);
	    		 	}
	    		}

	    	    $smarty->assign('ObjectListCycleColors',array("#ffffff","#eeeeff"));
	    	    $Bootstrap->addTimestamp('Displaying Statistics');
			    $Schedules = $ScheduleContainer->getAllSchedules($Year);
	            foreach ($Schedules as $Type => $Schedule){
	                if ($Type != 'New'){
	                    $new_function = create_function('$Object','
	            			global $ShowContainer, $Festival;
	            		 	if (is_a($Object,"Parameterized_Object")){
	            				$Shows = $ShowContainer->getAllShowsWithArtist($Festival->getParameter("FestivalYear"),$Object->getParameter("ArtistID"),"'.$Type.'");
	            				$ShowDays = array();
	            				if (is_array($Shows)){
	            				    foreach ($Shows as $Show){
	            				        if (!isset($ShowDays[$Show->getParameter("ShowPrettyDay")])){
	            				            $ShowDays[$Show->getParameter("ShowPrettyDay")] = 0;
	            				        }
	            				        $ShowDays[$Show->getParameter("ShowPrettyDay")] = $ShowDays[$Show->getParameter("ShowPrettyDay")] + 1;
	            				    }
	            				}
	            				$return = count($Shows);
	            				foreach ($ShowDays as $ShowDay => $Count){
	            				    $return.= " ".substr($ShowDay,0,3).": $Count";
	            				}
	            				return $return;
	            		 	}
	            		 	else{
	            		 		return "bInvalid Object Passed: ".get_class($Object);
	            		 	}
	                    ');
	        		    $ObjectLister->addColumn($Type.'s',$new_function,'20%');
	        		}
	        	}

	        	$ObjectLister->addColumn('Total',create_function('$Object','
	        	        global $ShowContainer, $Festival;
	            		 	if (is_a($Object,"Parameterized_Object")){
	            				$Shows = $ShowContainer->getAllShowsWithArtist($Festival->getParameter("FestivalYear"),$Object->getParameter("ArtistID"));
	            				return count($Shows);
	            		 	}
	            		 	else{
	            		 		return "cInvalid Object Passed: ".get_class($Object);
	            		 	}
	                    ')
	                    ,'20%');


	    		$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	        	$smarty->assign('ObjectListCellPadding', "5px");
	    	    $Bootstrap->addTimestamp('Getting ObjectList');
	    		$smarty->assign('ObjectList', $ObjectLister->getObjectList($AllArtists));
	    	    $Bootstrap->addTimestamp('Got ObjectList');
	    		$smarty->assign('ObjectEmptyString',"There are currently no ".pluralize('Artist')." in the ".vocabulary('Festival').", so we can't give you any statistics.");

	    		$Content = $smarty->fetch('admin_listing.tpl');
	    	    $Bootstrap->addTimestamp('Displayed Statistics');
	            $return['result'] = 'success';
	            $return['data'] = $Content;
	            break;
			case 'serialize_festival':
		        include_once(PACKAGE_DIRECTORY.'Common/class.json.php');
		        // make JSON return
		        $json = array();
		        $encoder = new json;
		
				if (function_exists('do_action')){
					do_action('serialize_festival');
				}
		
				$FestivalContainer = new FestivalContainer();
				$ScheduleContainer = new ScheduleContainer();
				$Schedules = $ScheduleContainer->getAllSchedules($Year);
				if (!is_array($Schedules) or !count($Schedules)){
					$json['message'] = 'Could not find any schedules to publish for '.$Year;
					$json['result'] = 'stop';
				    die(json_encode($json));
				}
			    $Types = array();
			    foreach ($Schedules as $Type => $Schedule){
			        if ($Schedule->getParameter('ScheduleIsPublished')){
			            $Types[$Schedule->getParameter('ScheduleUID')] = $Schedule->getParameter('ScheduleID');
			        }
			    }
			
				set_time_limit(0);
			
				if (is_array($package->skip_publishing_steps) and in_array($_GET['step'],$package->skip_publishing_steps)){
					// I heart hackabees
					$json['message'] = '';
					$json['result'] = 'next_step';
					$_GET['step']++;
					while(in_array($_GET['step'],$package->skip_publishing_steps)){
						$_GET['step']++;
					}
				}
				switch ($_GET['step']){
				case '1':
				/******************************************
				*   Serialize the Schedule Array
				******************************************/
					if ($_GET['getinfo']){
						$json['message'] = "Publishing Schedule Names";
						break;
					}
					// Okay, let's empty the published files first
		            foreach (glob($package->etcDirectory."{$Year}*.txt") as $filename) {
		                unlink ($filename);
		            }
					
				    $ScheduleNames = array();
				    $Types = array();
				    foreach ($Schedules as $Type => $Schedule){
				        if ($Schedule->getParameter('ScheduleIsPublished')){
				            $ScheduleNames[$Schedule->getParameter('ScheduleUID')] = $Schedule->getParameter('ScheduleName');
				            $Types[$Schedule->getParameter('ScheduleUID')] = $Schedule->getParameter('ScheduleID');
				        }
				    }
				    $Serialized = serialize($ScheduleNames);
				    $handle = fopen($package->etcDirectory."{$Year}ScheduleNames.txt","w");
				    if ($handle){
				            fwrite($handle,$Serialized);
				            fclose($handle);
				            $json['message']= "<p>Schedule Names successfully published</p>";
							$json['result'] = 'next_step';
				    }
					break;
				case '2':
			    /******************************************
			    *   Serialize the Schedule Display Array
			    ******************************************/
				    $ShowContainer = new ShowContainer();
					$Type = isset($_GET['substep']) ? $_GET['substep'] : current(array_keys($Types));
					if ($_GET['getinfo']){
						if (!count($Types)){
							$json['message'] = "<p>No Schedules have been published</p>";
							$json['result'] = 'next_step';
						}
						else{
							$Schedule = $Schedules[$Type];
							$json['message'] = "Publishing {$Schedule->getParameter('ScheduleName')}";
						}
						break;
					}
					
					// Was having memory issues with this function, so let's split it so 
					// that we get each day at a time.
					$Schedule = $Schedules[$Type];
					$ScheduledDays = $Schedule->getScheduledDays();
					$ShowListingsArray = array();
					foreach ($ScheduledDays as $day_id){
						$l = $ShowContainer->getShowListingsArray($Type,$Year,$day_id);
						if (is_array($l)){
				        	$ShowListingsArray[$day_id] = $l[$day_id];            
						}
					}
					if (empty($ShowListingsArray)){
						$ShowListingsArray = $l; // Will be a string saying nothing is published
					}
			        //$ShowListingsArray = $ShowContainer->getShowListingsArray($Type,$Year);            
			        $Serialized = serialize($ShowListingsArray);
			        $handle = fopen($package->etcDirectory."{$Year}_{$Type}_ListingsArray.txt","w");
			        if ($handle){
			                fwrite($handle,$Serialized);
			                fclose($handle);
							$Schedule = $Schedules[$Type];
			                $json['message'] = "<p>{$Schedule->getParameter('ScheduleName')} successfully published</p>";
							$next_one = false;
							$substep = '';
							foreach (array_keys($Types) as $k){
								if ($next_one){
									$substep = $k;
									break;
								}
								elseif($k == $Type){
									$next_one = true;
								}
							}
							if ($substep != ''){
								$json['substep'] = $substep;
								$json['result'] = 'this_step_again';
							}
							else{
								$json['result'] = 'next_step';
							}
			        }
					break;
				case '3':
				    /******************************************
				    *   Serialize All Shows
				    ******************************************/
					if ($_GET['getinfo']){
						$json['message'] = "Publishing All ".pluralize('Show');
						break;
					}
			    	$ShowContainer = new ShowContainer();
					$Shows = array();
					foreach (array_keys($Types) as $Type){
						$tmp = $ShowContainer->getAllShows($Year,$Type);
						if (is_array($tmp)){
							$Shows += $tmp;
						}
						//$Shows += $ShowContainer->getAllShows($Year,$Type);
					}
					unset($tmp);
				    //$Shows= $ShowContainer->getAllShows($Year,array_keys($Types)); // BUG: when using ShowTitle to sort, the ArtistNames get messed up.  "ShowTitle","asc");  
					uasort($Shows,create_function('$a,$b','
						if ($a->getParameter("ShowTitle") == $b->getParameter("ShowTitle")){
							return 0;
						}
						else{
							return ($a->getParameter("ShowTitle") < $b->getParameter("ShowTitle") ? -1 : 1);
						}
					'));	
					foreach ($Shows as $k => $Show){
						// don't need to serialize the saved params
						unset($Shows[$k]->params_saved);
						if (is_array($Show->getParameter('ShowArtists'))){
							$NewShowArtists = array();
							foreach ($Show->getParameter('ShowArtists') as $key => $Artist){
								unset($Artist->params_saved);
								$NewShowArtists[$key] = $Artist;
							}
							$Show->setParameter('ShowArtists',$NewShowArtists);
						}
					}
				    $Serialized = serialize($Shows);
				    $handle = fopen($package->etcDirectory."{$Year}Shows.txt","w");
				    if ($handle){
				            fwrite($handle,$Serialized);
				            fclose($handle);
				            $json['message']= "<p>".pluralize('Show')." successfully published</p>";
							$json['result'] = 'next_step';
				    }
					break;
				case '4':
					/******************************************
					*   Output the Artists
					******************************************/
					if ($_GET['getinfo']){
						$json['message'] = "Publishing All ".pluralize('Artist');
						break;
					}

					$FestivalArtistContainer = new FestivalArtistContainer();
					$MediaContainer = new MediaContainer();
					$GalleryImageContainer = new GalleryImageContainer();
					if (class_exists('SongContainer')){
					    $SongContainer = new SongContainer();
					}
					$ShowContainer = new ShowContainer();

					$FestivalArtists = $FestivalArtistContainer->getAllArtists($Year);
					if (is_a($FestivalArtists,'FestivalArtist')){
						$FestivalArtists = array($FestivalArtists->getParameter('ArtistID') => $FestivalArtists);
					}
					if (!is_array($FestivalArtists)){
						$FestivalArtists = array();
					}

					$SimpleArtists = array();
					$a = 0;
					$AllMP3s = array();

					foreach ($FestivalArtists as $key => $Artist){
					        $a = $key;
					        $SimpleArtists[$a] = $Artist->getParameters();
					        $ArtistMedia = array();
					        if ($Artist->getParameter('ArtistID') != ""){
					            // First, check for media from the current festival year
					            $_Artist = new FestivalArtist();
					            $_Artist->setParameter('ArtistID',$Artist->getParameter('ArtistID')."_$Year");
					            $AllMedia = $MediaContainer->getAssociatedMedia($_Artist);
					            $SongFound = false;
					            $ImageFound = false;
					            if (is_array($AllMedia)){
					                foreach ($AllMedia as $Media){
					                    switch($Media->getParameter('MediaType')){
					                    case MEDIA_TYPE_GALLERYIMAGE:
					                        $ImageFound = true;
					                        break;
					                    case MEDIA_TYPE_MP3:    
					                        $SongFound = true;
					                        break;
					                    }
					                }
					            }

					            // Now get the general media
					            $_Artist = new Artist();
					            $_Artist->setParameter('ArtistID',$Artist->getParameter('ArtistID'));
					            $GeneralMedia = $MediaContainer->getAssociatedMedia($_Artist);

					            // We'll only add them if nothing was found on the year specific
					            if (is_array($GeneralMedia)){
					                foreach ($GeneralMedia as $Media){
					                    switch($Media->getParameter('MediaType')){
					                    case MEDIA_TYPE_GALLERYIMAGE:
					                        if (!$ImageFound){
					                            $AllMedia[] = $Media;
					                        }
					                        break;
					                    case MEDIA_TYPE_MP3:    
					                        if (!$SongFound){
					                            $AllMedia[] = $Media;
					                        }
					                        break;
					                    }
					                }
					            }

					        }
					        $i = 0;
					        if (!is_array($AllMedia)){
					                $AllMedia = array();
					        }
					        $Gallery = null;
					        foreach ($AllMedia as $Media){
					                switch($Media->getParameter('MediaType')){
					                case MEDIA_TYPE_GALLERYIMAGE:
					                        $Image = $GalleryImageContainer->getGalleryImage($Media->getParameter('MediaLocation'));
					                        if ($Gallery === null and $Image){
					                            $Gallery = $Image->getGallery();
					                            $GalleryDirectory = $Gallery->getGalleryDirectory(IMAGE_DIR_FULL);
					                        }
					                        if ($Image){
					                                $ArtistMedia[$i] = array('type' => 'image');
					                                $ArtistMedia[$i]['thumb'] = 'images/artists/'.$Image->getParameter("GalleryImageThumb");
					                                $ArtistMedia[$i]['resized'] = 'images/artists/'.$Image->getParameter("GalleryImageResized");
					                                $ArtistMedia[$i]['original'] = 'images/artists/'.$Image->getParameter("GalleryImageOriginal");
					                        }
					                        break;
					                case MEDIA_TYPE_MP3:    
					                        $ArtistMedia[$i] = array('type' => 'mp3');
					                        $Song = $SongContainer->getSong($Media->getParameter('MediaLocation'));
					                        $Parms = $Song->getParameters();
					                        foreach ($Parms as $key2 => $value){
					                            $ArtistMedia[$i][$SongContainer->getColumnName($key2)] = $value;
					                        }
					                        break;
					                }
					                $i++;
					        }
					        $SimpleArtists[$a]['media'] = $ArtistMedia;

							/**************************************************************************************
							* I removed this step of the publishing process because it really made this unscalable.
							* The parts of the plugins that used it have been modified to just get the shows from the
							* database directly.  
							***************************************************************************************/
							/*
					        $ArtistShows = $ShowContainer->getAllShowsWithArtist($Year,$key,array_keys($Types),array('ShowDay','ShowStartTime'),array('asc','asc'));
								if (PEAR::isError($ArtistShows)){
									echo $ArtistShows->message;
									exit();
								}
					        $SimpleArtistShows = array();
					        foreach ($ArtistShows as $Show){
								// don't need to serialize the saved params
								unset($Show->params_saved);
								if (is_array($Show->getParameter('ShowArtists'))){
									$NewShowArtists = array();
									foreach ($Show->getParameter('ShowArtists') as $key => $Artist){
										unset($Artist->params_saved);
										$NewShowArtists[$key] = $Artist;
									}
									$Show->setParameter('ShowArtists',$NewShowArtists);
								}
					            $SimpleArtistShows[] = $Show;
					        }
					        $SimpleArtists[$a]['Shows'] = $SimpleArtistShows;
							*/
							
							// Oh, might as well at least get the show ids
							// Note: the bit with teh ScheduleIsPublished extra select is to ensure that shows that are embedded shows also get pulled (oft times the embedded schedules are not published)
							$query = "
							SELECT 
								Shows.ShowID 
							FROM ".
								$ShowContainer->getTableName('Show')." Shows, ".
								$ShowContainer->getTableName()." Artist, ".
								$ShowContainer->getTableName('Schedule')." Schedule 
							WHERE Shows.ShowID = Artist.ShowID 
							  AND Schedule.ScheduleUID = Shows.ShowScheduleUID 
							  AND Artist.ArtistID = $a 
							  AND Shows.ShowYear = '".addslashes($Year)."' 
							  AND (Schedule.ScheduleIsPublished = 1 
								OR (SELECT ShowID FROM ".$ShowContainer->getTableName('Show')." s2 WHERE s2.ShowEmbeddedScheduleUID = Schedule.ScheduleUID LIMIT 1) IS NOT NULL
							  )
							ORDER BY Shows.ShowDay ASC, Shows.ShowStartTime ASC";
							
							global $wpdb;
							$ShowIDsArray = $wpdb->get_results($query,ARRAY_N);
							$ShowIDs = array();
							if (is_array($ShowIDsArray)){
								foreach ($ShowIDsArray as $row){
									$ShowIDs[] = $row[0];
								}
							}
							$SimpleArtists[$a]['Shows'] = $ShowIDs;

					}
					foreach ($SimpleArtists as $ArtistID => $Artist){
					    if (($BandID = $Artist['ArtistBand']) != ""){
					        if ($SimpleArtists[$BandID]['ArtistBandMemberIDs'] == ""){
					            $SimpleArtists[$BandID]['ArtistBandMemberIDs'] = array();
					        }
					        $SimpleArtists[$BandID]['ArtistBandMemberIDs'][] = $ArtistID;
					    }
					}
					$Serialized = serialize($SimpleArtists);
					$handle = fopen($package->etcDirectory."{$Year}FestivalArtists.txt","w");
					if ($handle){
					        fwrite($handle,$Serialized);
					        fclose($handle);
				            $json['message']= "<p>".pluralize('Artist')." successfully published</p>";
							$json['result'] = 'next_step';
					}	
					break;
				case '5':
				/******************************************
				*   Output the MP3s
				******************************************/
					if ($Bootstrap->packageExists('flamplayer')){
						$Bootstrap->usePackage('flamplayer');
						if ($_GET['getinfo']){
							$json['message'] = "Publishing MP3s";
							break;
						}
					    $FlamData = array();
					    $FlamData['Songs'] = array();
					    $FlamData['Artists'] = array();
					    $SongContainer = new SongContainer();
					    $AllSongs = $SongContainer->getAllSongs("",'SongID','asc');
					    if (is_array($AllSongs)){
					        foreach ($AllSongs as $song_id => $Song){
					            $Parms = $Song->getParameters();
					            $Song = array();
					            foreach ($Parms as $key => $value){
					                $Song[$SongContainer->getColumnName($key)] = $value;
					            }

					            $FlamData['Songs'][$song_id] = $Song;
					        }
					    }
					    $SongArtistContainer = new SongArtistContainer();
					    $AllArtists = $SongArtistContainer->getAllSongArtists("",'SongArtistID','asc');
					    if (is_array($AllArtists)){
					        foreach ($AllArtists as $Artist_id => $Artist){
					            $Parms = $Artist->getParameters();
					            $Artist = array();
					            foreach ($Parms as $key => $value){
					                $Artist[$SongArtistContainer->getColumnName($key)] = $value;
					            }

					            $FlamData['Artists'][$Artist_id] = $Artist;
					        }
					    }
					    $Serialized = serialize($FlamData);
					    $handle = fopen($package->etcDirectory."FlamData.txt","w");
					    if ($handle){
					            fwrite($handle,$Serialized);
					            fclose($handle);
					            $json['message']= "<p>MP3s successfully published</p>";
								$json['result'] = 'next_step';
					    }
					}
					else{
			            $json['message']= "";
						$json['result'] = 'next_step';
					}
					break;
				case '6':
					if ($package->enable_cache){
						// We're using the cache, so we need to remove the cache pages associate with this term
						$package->emptyCache();
					}
		            $json['message']= "<p>Cache successfully cleared</p>";
					$json['result'] = 'next_step';
					break;
				case '7':
					$json['message'] = '<p>All done.  <a href="javascript:history.go(-1)">Head back</a></p>';
					$json['result'] = 'stop';
					break;
				}
		        if ($_GET['test']){
		            echo $encoder->encode($json);
		            exit();
		        }
			    if (false and !headers_sent() )
			    {
			    	header('Content-type: application/json');
			    }

			    echo json_encode($json);
				exit();
				break;
			case '':
				if ($_GET['req'] != ""){
			        include_once(PACKAGE_DIRECTORY.'Common/class.json.php');
			        // make JSON return
			        $json = array();
			        $encoder = new json;

				    foreach ($package->useDatabases as $UseDatabase){
				        // This will include the appropriate helper file and instantiate the helper
				        // These are called by name later in this script
						// TODO = make this work....
						if ($Bootstrap->packageExists($UseDatabase['package'])){			
							$p = $Bootstrap->usePackage($UseDatabase['package']);
					        include_once(PACKAGE_DIRECTORY.$p->package_directory."/".$UseDatabase['helper'].".php");
					        $$UseDatabase['helper'] = new $UseDatabase['helper']();
						}
				    }

					$LetterContainer = new LetterContainer();
					$FestivalContainer = new FestivalContainer();
				    $FestivalArtistContainer = new FestivalArtistContainer();
				
			        switch ($_GET['req']){
			        case 'names':
			            //xxx
			            switch ($_GET['source']){
			            case 'artists':
			                $FestivalArtists = $FestivalArtistContainer->getLineup($_GET[YEAR_PARM],'ArtistFullName');
			                if (!is_array($FestivalArtists)){
			                        $FestivalArtists = array();
			                }
			                foreach ($FestivalArtists as $Artist){
			                    $json["_".$Artist->getParameter('ArtistID')] = $Artist->getParameter('ArtistFullName');
			                }
			                break;
			            case 'types':
			                $PeoplePassTypes = $PeoplePassesHelper->getPassTypes();
			                foreach ($PeoplePassTypes as $PeoplePassType){
			                    $json[$PeoplePassType] = $PeoplePassType;
			                }
			                break;
			            case 'people':
			                $PeopleWithPasses = $PeoplePassesHelper->getPeopleWithPasses();
			                foreach ($PeopleWithPasses as $PersonWithPass){
			                    if ($PersonWithPass->getParameter('LastName') == "" and $PersonWithPass->getParameter('FirstName') == ""){
			                        $PersonWithPass->setParameter('FirstName',' (no name)');
			                    }
			                    $json["_".$PersonWithPass->getParameter('id')] = $PersonWithPass->getParameter('FirstName').' '.$PersonWithPass->getParameter('LastName');
			                }
			                break;
			            case 'assigned':
			                $Categories = $VolunteerCategoryHelper->getCategories();
			                foreach ($Categories as $key => $Category){
			                    $json[$Category] = $Category;
			                }
			                break;
			            case 'volunteers':
			                $AssignedVolunteers = $VolunteerCategoryHelper->getAssignedVolunteers();
			                foreach ($AssignedVolunteers as $AssignedVolunteer){
			                    if ($AssignedVolunteer->getParameter('Name') == ""){
			                        $AssignedVolunteer->setParameter('Name',' (no name)');
			                    }
			                    $json["_".$AssignedVolunteer->getParameter('id')] = $AssignedVolunteer->getParameter('Name');
			                }
			                break;
			            case 'artisans':
			                $Artisans = $ArtisanContainer->getAllArtisans($_GET[YEAR_PARM]);
			                if (is_array($Artisans)){
			                    foreach ($Artisans as $ArtisanID => $Artisan){
			                        $json[$Artisan->getParameter('frmArtisanID')] = $Artisan->getParameter('frmBusinessName');
			                    }
			                    natcasesort($json);
			                }
			                break;
			            }
			            break;
			        case 'variables':
			            $i = 0;
			            switch ($_GET['source']){
			            case 'artists':
			                $_year = date("Y");
			                $tmpLineup = $FestivalArtistContainer->getLineup($_year);
			                if (!is_array($tmpLineup)){
			                    $LastYear = $_year - 1;
			                    $tmpLineup = $FestivalArtistContainer->getLineup($LastYear);
			                }
			                if (is_array($tmpLineup)){
			                    $tmpArtist = array_pop($tmpLineup);
			                }
			                else{
			                    $tmpArtist = new Parameterized_Object();
			                }
			                $tmpArtist->setParameter('ArtistShows','');
			                $tmpArtist = $tmpArtist->getParameters();
			                if ($_GET['type'] != 'letter'){
			                    $ArtistsPassVariables = array('Year','PassType','PassName');
			                    foreach ($ArtistsPassVariables as $PassVariable){
			                        $json["_".$i++] = '{$'.$PassVariable.'}';
			                    }
			                }
			                foreach (array_keys($tmpArtist) as $key){
			                    $json["_".$i++] = '{$Artist.'.$key.'}';
			                }
			                break;
			            case 'artisans':
			                $_year = date("Y");
			                $tmpArtisans = $ArtisanContainer->getAllArtisans($_year);
			                if (!is_array($tmpArtisans)){
			                    $LastYear = $_year - 1;
			                    $tmpArtisans = $ArtisanContainer->getAllArtisans($_year);
			                }
			                if (is_array($tmpArtisans)){
			                    $tmpArtisan = array_pop($tmpArtisans);
			                }
			                else{
			                    $tmpArtisan = new Parameterized_Object();
			                }
			                $tmpArtisan = deparameterize($tmpArtisan);
			                if ($_GET['type'] != 'letter'){
			                    $ArtistsPassVariables = array('Year','PassName');
			                    foreach ($ArtistsPassVariables as $PassVariable){
			                        $json["_".$i++] = '{$'.$PassVariable.'}';
			                    }
			                }
			                foreach (array_keys($tmpArtisan) as $key){
			                    if (substr($key,0,3) == 'frm'){
			                        $json["_".$i++] = '{$Artisan.'.$key.'}';
			                    }
			                }
			                break;
			            default:
			                $json["_".$i++] = '{$Year}';
			                $json["_".$i++] = '{$Person.Email}';
			                switch ($_GET['source']){
			                case 'people':
			                case 'types':
			                    $SubscriberFields = $PeoplePassesHelper->getSubscriberFields();
			                    break;
			                case 'volunteers':
			                case 'assigned':
			                    $SubscriberFields = $VolunteerCategoryHelper->getSubscriberFields();
			                    $json["_".$i++] = '{$Person.Name}';
			                    break;
			                }
			                foreach ($SubscriberFields as $SubscriberField){
			                    $json["_".$i++] = '{$Person.'.$SubscriberField.'}';
			                }
			                break;
			            }
			            break;
			        case 'already_printed':
			            $Letter = $LetterContainer->getLetter($_GET['letter_id']);

			            // The format of this array is:
			            // [Year][DataSource] = array(all id's printed for that year/datasource);
			            if ($Letter->getParameter('LetterAlreadyPrinted') == "" or unserialize($Letter->getParameter('LetterAlreadyPrinted')) === false){
			                $Letter->setParameter('LetterAlreadyPrinted',serialize(array()));
			            }
			            $LetterYear = $Letter->getParameter('LetterDataYear');
			            $LetterDataSource = $Letter->getParameter('LetterDataSource');
			            if ($LetterDataSource == 'types')       $LetterDataSource = 'people';
			            if ($LetterDataSource == 'assigned')    $LetterDataSource = 'volunteers';
			            if ($LetterDataSource == 'workshops')   $LetterDataSource = 'forum';

			            $LetterAlreadyPrinted = unserialize($Letter->getParameter('LetterAlreadyPrinted'));
			            if (!is_array($LetterAlreadyPrinted)) $LetterAlreadyPrinted = array();
			            if (!is_array($LetterAlreadyPrinted[$LetterYear])) $LetterAlreadyPrinted[$LetterYear] = array();
			            if (!is_array($LetterAlreadyPrinted[$LetterYear][$LetterDataSource])) $LetterAlreadyPrinted[$LetterYear][$LetterDataSource] = array();
			            $ap = $LetterAlreadyPrinted[$LetterYear][$LetterDataSource];

			            if ($_GET['id'] == ""){
			                $LetterLastPrintedSet = unserialize($Letter->getParameter('LetterLastPrintedSet'));
			                if (!is_array($LetterLastPrintedSet)) $LetterLastPrintedSet = array();
			                if (!is_array($LetterLastPrintedSet[$LetterYear])) $LetterLastPrintedSet[$LetterYear] = array();
			                if (!is_array($LetterLastPrintedSet[$LetterYear][$LetterDataSource])) $LetterLastPrintedSet[$LetterYear][$LetterDataSource] = array();
			                $lps = $LetterLastPrintedSet[$LetterYear][$LetterDataSource];
			                switch ($Letter->getParameter('LetterDataSource')){
			                case 'artists':
			                case 'people':
			                case 'volunteers':
			                case 'artisans':
			                    // These guys already store the LastPrintedSet as specific ID's
			                    break;
			                case 'types':
			                    $People = $PeoplePassesHelper->getPeopleOfType($lps);
			                    break;
			                case 'assigned':
			                    $People = $VolunteerCategoryHelper->getVolunteersAssignedToCategory($lps);
			                    break;
			                }
			                if (isset($People)){
			                    $lps = array();
			                    foreach ($People as $Person){
			                        $lps[] = $Person->getParameter('id');
			                    }
			                }
			            }
			            else{
			                $lps = array($_GET['id']);
			            }

			            switch($_GET['action']){
			            case 'add':
			                $ap = array_merge($ap,array_diff($lps,$ap));
			                break;
			            case 'replace':
			                $ap = array_diff($lps,$ap);
			                break;
			            case 'reset':
			                $ap = array();
			                break;
			            }
			            $LetterAlreadyPrinted[$LetterYear][$LetterDataSource] = $ap;
			            $Letter->setParameter('LetterAlreadyPrinted',serialize($LetterAlreadyPrinted));
			            $LetterContainer->updateLetter($Letter);

			            echo "success";
			            exit();
			            break;
			        }


			        if ($_GET['limit'] != ""){
			            $json = array_slice($json,$_GET['start'],$_GET['limit']);
			        }
			        if ($_GET['test']){
			            echo $encoder->encode($json);
			            exit();
			        }
			        header('x-json: '.$encoder->encode($json));    
			        exit();
				}
	        }
			return $return;
		}

        
        function setFunctionParameters(){
            // Here's a list of the parameters that I need for this application
            // Festivals:
            //  - Festival lineup (just the artist names)
            //      - link url
            //      - text align
            //      - order (lineup order, alphabetic)
            //  - Festival schedule (the schedule)
            //      - Main act colour
            //      - Band member colour
            //      - Highlighted Show colour
            //      - Subject to change?
            //  - Festivals with published schedules
            global $Bootstrap;
            
            $Subject = new FunctionParameter('paint','subject');
            $Subject->setParameterName('Subject');
            $Subject->setParameterDescription('The item you wish to paint');
            $Subject->addParameterValues(array('Lineup' => 'The Festival Lineup','Detail' => 'Artist Detail','Schedule' => 'Festival Schedule','ShowDetails' => 'Show Descriptions','DaysLeftTilFestival' => 'Days Left to Next Festival','AllFestivalArtists' => 'All Festival Artists'));
            $Subject->setParameterDefaultValue('Lineup');

            $ArtistURL = new FunctionParameter('paint','artist_article');
            $LineupURL = new FunctionParameter('paint','lineup_article');
            $ShowDetailsURL = new FunctionParameter('paint','show_details_article');
            $ScheduleURL = new FunctionParameter('paint','schedule_article');
            $ArtistURL->setParameterName('Article for Artist Detail (default: "current article")');
            $LineupURL->setParameterName('Article for Full Lineup (default: "current article")');
            $ShowDetailsURL->setParameterName('Article for Show Details (default: "current article")');
            $ScheduleURL->setParameterName('Article for Schedule (default: "current article")');
            $ArtistURL->addParameterValues(array('' => '&lt;Keep Default&gt;'));
            $LineupURL->addParameterValues(array('' => '&lt;Keep Default&gt;'));
            $ShowDetailsURL->addParameterValues(array('' => '&lt;Keep Default&gt;'));
            $ScheduleURL->addParameterValues(array('' => '&lt;Keep Default&gt;'));
            if ($Bootstrap->packageExists('Article')){
                $ArticlePackage = $Bootstrap->usePackage('Article');
                if ($ArticlePackage->useSectionManager){
                    $BlogContainer = new BlogContainer();
                    $expandedBlogs = $BlogContainer->expandBlogs();
                    $Blogs = array();
                    foreach ($expandedBlogs as $Blog){
                        $Blogs[$Blog->getParameter('BlogURLKey')] = $Blog->getParameter('BlogURLKey');
                    }
                }
                else{
                    $Blogs = $ArticlePackage->Blogs;
                }
                $ArticleContainer = new ArticleContainer();
                foreach ($Blogs as $BlogID => $Blog){
                    if (is_array($Blog)){
                        foreach ($Blog as $_Blog){
                            $Articles = $ArticleContainer->getAllPublishedArticles($_Blog);
                            if (is_array($Articles) and count($Articles)){
                                $ArtistURL->addParameterValues(array($_Blog => $_Blog));
                                $LineupURL->addParameterValues(array($_Blog => $_Blog));
                                $ShowDetailsURL->addParameterValues(array($_Blog => $_Blog));
                                $ScheduleURL->addParameterValues(array($_Blog => $_Blog));
                                foreach ($Articles as $ArticleID => $Article){
                                    $ArtistURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                                    $LineupURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                                    $ShowDetailsURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                                    $ScheduleURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                                }
                            }
                        }
                    }
                    else{
                        $_Blog = $Blog;
                        $Articles = $ArticleContainer->getAllPublishedArticles($_Blog);
                        if (is_array($Articles) and count($Articles)){
                            $ArtistURL->addParameterValues(array($_Blog => $_Blog));
                            $LineupURL->addParameterValues(array($_Blog => $_Blog));
                            $ShowDetailsURL->addParameterValues(array($_Blog => $_Blog));
                            $ScheduleURL->addParameterValues(array($_Blog => $_Blog));
                            foreach ($Articles as $ArticleID => $Article){
                                $ArtistURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                                $LineupURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                                $ShowDetailsURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                                $ScheduleURL->addParameterValues(array($ArticleID => " - ".$Article->getParameter('ArticleTitle')));
                            }
                        }
                    }
                }
            }
            
            $LineupStyle = new FunctionParameter('paint','display_style');
            $LineupStyle->setParameterName('Display Style');
            $LineupStyle->addParameterValues(array('' => '&lt;Keep Default&gt;', 'names' => 'Just Names (Default)', 'expanded' => 'Names, Pictures and Brief Bio', 'floated' => 'Names and Pictures' ));
            
            $ShowListingsStyle = new FunctionParameter('paint','show_listings_style');
            $ShowListingsStyle->setParameterName('Show Listings Style');
            $ShowListingsStyle->addParameterValues(array('' => '&lt;Keep Default&gt;', 'default' => 'x=Stages,y=Times (Default)', 'collapse_days' => 'x=Days,y=Times'));
            
            $ShowTimes = new FunctionParameter('paint','show_times');
            $ShowTimes->setParameterName('Display the Times?');
            $ShowTimes->addParameterValues(array('' => '&lt;Keep Default&gt;', 'true' => 'True (Default)', 'false' => 'False'));
            
            
            $TextAlign = new FunctionParameter('paint','text_align');
            $TextAlign->setParameterName('Alignment');
            $TextAlign->addParameterValues(array('' => '&lt;Keep Default&gt;', 'center' => 'Center (Default)', 'left' => 'Left', 'right' => 'Right'));
            
            $ArtistOrder = new FunctionParameter('paint','artist_order');
            $ArtistOrder->setParameterName('Sort Order');
            $ArtistOrder->addParameterValues(array('' => '&lt;Keep Default&gt;', 'lineup_order' => 'Lineup Order (Default)', 'alphabetic' => 'Alphabetic'));
            
          
            $IncludeArchives = new FunctionParameter('paint','include_archives');
            $IncludeArchives->setParameterName('Include Archives');
            $IncludeArchives->setParameterDescription('Include the list of Festival Archives?');
            $IncludeArchives->addParameterValues(array('' => '&lt;Keep Default&gt;', 'true' => 'True (Default)', 'false' => 'False'));

            $Subject->addDependentParameter($IncludeArchives,array('Lineup','Schedule'));
            $Subject->addDependentParameter($LineupStyle,'Lineup');
            $Subject->addDependentParameter($TextAlign,'Lineup');
            $Subject->addDependentParameter($ArtistOrder,'Lineup');
            $Subject->addDependentParameter($LineupURL,array('Schedule'));
            $Subject->addDependentParameter($ShowListingsStyle,array('Schedule'));
            $Subject->addDependentParameter($ShowTimes,array('Schedule'));
            $Subject->addDependentParameter($ShowDetailsURL,array('Schedule','Detail'));
            $Subject->addDependentParameter($ScheduleURL,array('ShowDetails','Detail'));
            $Subject->addDependentParameter($ArtistURL,array('Lineup','Schedule','ShowDetails','AllFestivalArtists'));
            
            
            $this->addFunctionParameter($Subject);
            
            
            $Retrieve = new FunctionParameter('retrieve','subject');
            $Retrieve->setParameterName('Subject');
            $Retrieve->setParameterDescription('The item you wish to retrieve');
            $Retrieve->addParameterValues(array('Lineup' => 'The Festival Lineup','DaysLeftTilFestival' => 'Days Left to Next Festival','ArchiveYears' => 'Archive Festival Years'));
            $Retrieve->setParameterDefaultValue('Lineup');
            
            $Assign = new FunctionParameter('retrieve','var');
            $Assign->setParameterName('Assign');
            $Assign->setParameterDescription('The variable to assign the results to');
            
            $LineupPublished = new FunctionParameter('retrieve','lineup_published');
            $LineupPublished->setParameterName('Lineup Published');
            $LineupPublished->setParameterDescription('Only include year if the Lineup is Published');
            $LineupPublished->addParameterValues(array('' => '&lt;Keep Default&gt;', 'true' => 'True (Default)', 'false' => 'False'));

            $SchedulePublished = new FunctionParameter('retrieve','schedule_published');
            $SchedulePublished->setParameterName('Schedule Published');
            $SchedulePublished->setParameterDescription('Only include year if the Schedule is Published');
            $SchedulePublished->addParameterValues(array('' => '&lt;Keep Default&gt;', 'true' => 'True', 'false' => 'False (Default)'));

            $Retrieve->addDependentParameter($LineupPublished,array('ArchiveYears'));
            $Retrieve->addDependentParameter($SchedulePublished,array('ArchiveYears'));
            
            $this->addFunctionParameter($Retrieve);
            $this->addFunctionParameter($Assign);
        }
    }
    
    function user__setShowClass(&$Show){
            global $BandMembersHaveGigs;
    	 	if (is_a($Show,'Show')){
    	 	    $return = "";
    	 	    if ($_GET['sid'] != "" and $_GET['sid'] == $Show->getParameter('ShowID')){
    	 	        $return = "HighlightedShowCell";
    	 	    }
    	 	    if ($_GET['id'] != ""){
        	 	    foreach ($Show->getParameter('ShowArtists') as $Artist){
        	 	        // Full band takes precendence
        	 	        if ($Artist->getParameter('ArtistID') == $_GET['id']){
        	 	            $return = "MainActShowCell";
        	 	        }
        	 	        elseif ($return == "" and $Artist->getParameter('ArtistBand') == $_GET['id']){
        	 	            $return = "BandMemberShowCell";
        	 	            $BandMembersHaveGigs = true;
        	 	        }
        	 	    }
	 	        }
				if (function_exists('apply_filters')){
					$return = apply_filters('user__setShowClass',$return,$Show);
				}
    	 	    return $return;
    	 	}
    	 	else{
    	 		die("Invalid Object Passed: ".get_class($Show));
    	 	}
    }
    
    function user__getShowTitleURL(&$Show){
        global $ShowDetailsPage;
	 	if (is_a($Show,'Show')){
	 	    if ($Show->getParameter('ShowTitle') == $Show->getArtistNames()){
	 	        $Artists = $Show->getParameter('ShowArtists');
	 	        $Artist = array_shift($Artists);
	 	        return user__getArtistURL($Artist);
	 	    }
	 	    else{
	 	        return user__getArticleURL($ShowDetailsPage,$Show->getParameter('ShowYear'))."sid=".$Show->getParameter('ShowID')."#".$Show->getParameter('ShowID');
	 	    }
	 	}
	 	else{
	 		die("Invalid Object Passed: ".get_class($Show));
	 	}
    }
    
    function user__getArtistURL(&$Artist){
        global $ArtistDetailPage,$Bootstrap;
        
	 	if (is_a($Artist,'FestivalArtist') or is_a($Artist,'Artist')){
            $ArtistURL = user__getArticleURL($ArtistDetailPage,$Artist->getParameter('FestivalYear'));
            $ArtistURL.= "speaker=";
	 	    if ($Artist->getParameter('ArtistBand') != "" and $Artist->getParameter('ArtistBand') != "0"){
	 	        $ArtistURL.= $Artist->getParameter('ArtistBand');
	 	    }
	 	    else{
	 	        $ArtistURL.= $Artist->getParameter('ArtistID');
	 	    }
	 	    return $ArtistURL;
	 	}
		elseif(!$Artist){
			return "";
		}
	 	else{
	 		die("Invalid Object Passed: ".get_class($Artist));
	 	}
    }
    
    function user__getArticleURL($ParmArticle,$Year = ""){
        global $Bootstrap;
        if ($Bootstrap->packageExists('Article')){
            if ($ParmArticle == ''){
                $ArticleURL = $Bootstrap->getIndexURL().$_GET[INDEX_PAGE_PARM];
            }
            elseif(is_numeric($ParmArticle)){
                $ArticleURL = $Bootstrap->getIndexURL().$ParmArticle;
            }
            else{
                $ArticleURL = $ParmArticle;
            }
        }
        else{
            $ArticleURL = $ParmArticle;
        }
        if ($ArticleURL != ""){
			if ($ArticleURL == '?'){
				$sep = "";
			}
			else{
	            $sep = (strpos($ArticleURL,'?') !== false) ? '&' : '?';
			}
            if ($Year != ""){
                $ArticleURL.= $sep.YEAR_PARM.'='.urlencode($Year);
                $sep = '&';
            }
        }
        
        return $ArticleURL.$sep;
        
    }

	function user__BandMembersHaveGigs($Year,$ArtistID,$Type){
        $ShowContainer = new ShowContainer();
		if (count($ShowContainer->getAllBandMemberShows($Year,$ArtistID,false,$Type))){
			return true;
		}
		else{
			return false;
		}
	}
    
    function user__SortByDay($a,$b){
        if ($a->getParameter('ShowDay') < $b->getParameter('ShowDay')){
            return -1;
        }
        elseif ($a->getParameter('ShowDay') > $b->getParameter('ShowDay')){
            return 1;
        }
        elseif ($a->getParameter('ShowStartTime') < $b->getParameter('ShowStartTime')){
            return -1;
        }
        elseif ($a->getParameter('ShowStartTime') > $b->getParameter('ShowStartTime')){
            return 1;
        }
        else{
            return ($a->getParameter('ShowTitle') < $b->getParameter('ShowTitle') ? -1 : 1);
        }
            
    }
    
    function user__getArtistShows($Artist,$Shows,&$smarty){
        global $SchedulePage,$ShowDetailPage,$Year,$Bootstrap;
        
        if (!is_a($Artist,'Parameterized_Object')){
            $tmp = new Parameterized_Object();
            $tmp->setParameters($Artist);
            $Artist = $tmp;
        }

        $return = "";
        if (is_array($Shows) and count($Shows)){
			$smarty->assign_by_ref('Artist',$Artist);
			$smarty->assign_by_ref('Shows',$Shows);
			$return = $smarty->fetch('festivalapp.artist_shows.tpl');
        }
        return $return;
    }
    
    function user__getFestivalArchives($LineupPublished = true, $SchedulesPublished = false,$OnlyPastFestivals = true){
        global $Year;
        $FestivalContainer = new FestivalContainer();
        $AllFestivals = $FestivalContainer->getAllFestivals();

        $return = "";
        if (is_array($AllFestivals)){
            $TitleShown = false;
			if (function_exists('apply_filters')){
				$article = apply_filters('topquark_FestivalApp_permalink','?subject=lineup','lineup');
			}
			else{
				$article = "";
			}
            foreach ($AllFestivals as $_Festival){
                if ($_Festival->getParameter('FestivalYear') != $Year and 
                (!$LineupPublished or $_Festival->getParameter('FestivalLineupIsPublished')) and 
                (!$SchedulesPublished or file_exists($package->etcDirectory."".$_Festival->getParameter('FestivalYear')."ScheduleNames.txt")) and 
                (!$OnlyPastFestivals or $_Festival->getParameter('FestivalEndDate') < date("Y-m-d"))){
                    if (!$TitleShown){
                        $return.= "<h3>Past ".vocabulary('Festival')." Archives</h3>\n";
                        $return.= "<ul class='festival_archives'>\n";
                        $TitleShown = true;
                    }
                    $return.= "<li><a href='".user__getArticleURL($article).YEAR_PARM."=".urlencode($_Festival->getParameter('FestivalYear'))."'>".$_Festival->getParameter('FestivalYear')."</a></li>\n";
                }
            }
        }
        if ($TitleShown){
            $return.= "</ul>\n";
        }
        
		if (function_exists('apply_filters')){
			$article = apply_filters('topquark_FestivalApp_permalink','?subject=all_festival_artists','all_festival_artists');
		}
		else{
			$article = "";
		}
		$return.= "<p>View the <a href='$article'>Complete ".vocabulary('Artist')." Index</a></p>";
		
        return $return;

    }
    
?>