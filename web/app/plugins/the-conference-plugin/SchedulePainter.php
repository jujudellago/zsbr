<?php
require_once("Show.php");
require_once("Artist.php");

class SchedulePainter{
    
    var $show_times;

	function SchedulePainter(){
	    $this->show_times = true;
	    $this->setShowStyleCallback(null);
	    $this->setShowClassCallback(null);
	    $this->setBlankCellContent('&nbsp;');
	}
	
	function getShowStyle(&$Show){
	    if (($Callback = $this->getShowStyleCallback()) != null){
	        return $Callback($Show);
	    }
	}
	
	function getShowClass(&$Show){
	    if (($Callback = $this->getShowClassCallback()) != null){
	        return " ".$Callback($Show);
	    }
	    else{
	        return "";
	    }
	}
	
	function setShowStyleCallback($ShowStyleCallback){
	    $this->ShowStyleCallback = $ShowStyleCallback;
	}
	
	function getShowStyleCallback(){
	    return $this->ShowStyleCallback;
	}
	
	function setShowClassCallback($ShowClassCallback){
	    $this->ShowClassCallback = $ShowClassCallback;
	}
	
	function getShowClassCallback(){
	    return $this->ShowClassCallback;
	}
	
	function setShowTitleURLCallback($ShowTitleURLCallback){
	    $this->ShowTitleURLCallback = $ShowTitleURLCallback;
	}
	
	function getShowTitleURLCallback(){
	    return $this->ShowTitleURLCallback;
	}
	
	function setArtistURLCallback($ArtistURLCallback){
	    $this->ArtistURLCallback = $ArtistURLCallback;
	}
	
	function getArtistURLCallback(){
	    return $this->ArtistURLCallback;
	}
	
	function setShowExtrasCallback($ShowExtrasCallback){
	    $this->ShowExtrasCallback = $ShowExtrasCallback;
	}
	
	function getShowExtrasCallback(){
	    return $this->ShowExtrasCallback;
	}

	function setBlankCellContent($_blank_cell_content){
	    $this->blank_cell_content = $_blank_cell_content;
	}
	
	function getBlankCellContent(){
	    return $this->blank_cell_content;
	}
	
	function setRowHeight($height){
	    $this->rowHeight = $height;
	}
	
	function getRowHeight(){
	    return $this->rowHeight;
	}
	
    function paintSchedule(&$ShowListingsArray,$ShowListingsFormat = "default"){
    // Note: For the TitleURL, you must have a URLParm= at the end for the ShowID
    // Note: For the ArtistURL, you must have a URLParm= at the end for the ArtistID

			$ShowListingsFormat = apply_filters('schedule_painter_format',$ShowListingsFormat,array(&$this,&$ShowListings));
	
            $AllShowListings = $ShowListingsArray;
            if (!is_array($AllShowListings)){
                    $Content = "<p>$AllShowListings</p>";
            }
            else{
                $Content = "";
                $tmpTitleURL = "";
                $TitleCallback = $this->getShowTitleURLCallback();
                $ArtistCallback = $this->getArtistURLCallback();
                $ShowExtrasCallback = $this->getShowExtrasCallback();
                if (count($AllShowListings["Unassigned"])){
                        $Content.= "<p class='ShowListingHeading'>".$AllShowListings["Unassigned"]["Headings"]."</p>\n";
                        $UnassignedShowsPerRow = 4;
                        $Content.= "<table class='ShowListingTable' border='1'><tr>\n";
                        $UnassignedShowsPrinted = 0;
                        foreach ($AllShowListings["Unassigned"]["Shows"] as $Show){
                            if (is_int($UnassignedShowsPrinted/$UnassignedShowsPerRow) and $UnassignedShowsPrinted > 0){
                                    $Content.= "</tr><tr>";
                            }
                            if (is_a($Show,'Show')){
                                $Content.= "<td class='ShowListingCell'>".$Show->getListing($TitleCallback,$ArtistCallback)."</td>\n";
                            }
                            else{
                                $Content.= "<td class='ShowListingCell'>Error - contact Trevor</td>\n";
                            }
                            $UnassignedShowsPrinted++;
                        }
                        while ($UnassignedShowsPrinted > $UnassignedShowsPerRow and !is_int($UnassignedShowsPrinted/$UnassignedShowsPerRow)){
                                $Content.= "<td>&nbsp;</td>";
                                $UnassignedShowsPrinted++;
                        }
                        $Content.= "</tr></table>\n";
                }
                
                switch ($ShowListingsFormat){
                case 'collapse_days':
                    /**************************************
                     We need to reformat the AllShowListingsArray - fun fun fun.
                     
                     The format that the thing comes in is:
                     array(
                         [day_index]    => ['PrettyHeading'] = "Saturday"
                                        => ['Headings'] = array([stage_index] => {StageNames})
                                        => ['HeadingSponsors'] => array([stage_index] => {Sponsor})
                                        => ['Shows'] = array([stage_index] = array([time] => {Show}))
                                        => ['Times'] = array(TimeArray);
                                        => ['Resolution'] = Resolution
                    )
                    
                    In this section, we're going to collapse it to:
                    array(
                         [stage_index]  => ['PrettyHeading'] = "Stage Name"
                                        => ['Headings'] = array({PrettyDays})
                                        => ['HeadingSponsors'] => array({Sponsors})
                                        => ['Shows'] = array([day_index] = array([time] => {Show}))
                                        => ['Times'] = array(TimeArray);
                                        => ['Resolution'] = Resolution
                    )
                    ***************************************/
                    $NewShowListings = array();
                    foreach ($AllShowListings as $Heading => $ShowListings){
                        foreach ($ShowListings["Headings"] as $h => $SubHeading){
                            if (!array_key_exists($h,$NewShowListings)){
                                $NewShowListings[$h] = array('PrettyHeading' => $SubHeading, 'Headings' => array(), 'HeadingSponsors' => array(), 'Shows' => array());
                            }
                            $NewShowListings[$h]['Headings'][$Heading] = $ShowListings['PrettyHeading'];
                            if (is_array($ShowListings["HeadingSponsors"]) and $ShowListings["HeadingSponsors"][$h] != ""){
                                $NewShowListings[$h]['HeadingSponsors'][$Heading] = $ShowListings["HeadingSponsors"][$h];
                            }
                            $NewShowListings[$h]['Shows'][$Heading] = $ShowListings['Shows'][$h];
                            if (!array_key_exists('Times',$NewShowListings[$h])){
                                $NewShowListings[$h]['Times'] = $ShowListings['Times'];
                                $NewShowListings[$h]['Resolution'] = $ShowListings['Resolution'];
                            }
                            else{
                                // array_merge doesn't work because of the numeric keys, so we'll have to do it ourselves
                                foreach ($ShowListings['Times'] as $CanonicalTime => $PrettyTime){
                                    if (!array_key_exists($CanonicalTime,$NewShowListings[$h]['Times'])){
                                        $NewShowListings[$h]['Times'][$CanonicalTime] = $PrettyTime;
                                    }
                                }
                                ksort($NewShowListings[$h]['Times']);
                            }
                        }
                    }
                    $AllShowListings = $NewShowListings;
                    break;
                case 'collapse_all': 
                    /**************************************
					 Collapses it so it's times down the left, days across the top and all stages appear in the single schedule.
                     We need to reformat the AllShowListingsArray - fun fun fun.

                     The format that the thing comes in is:
                     array(
                         [day_index]    => ['PrettyHeading'] = "Saturday"
                                        => ['Headings'] = array([stage_index] => {StageNames})
                                        => ['HeadingSponsors'] => array([stage_index] => {Sponsor})
                                        => ['Shows'] = array([stage_index] = array([time] => {Show}))
                                        => ['Times'] = array(TimeArray);
                                        => ['Resolution'] = Resolution
                    )

                    In this section, we're going to collapse it to:
                    array(
                         [0]  			=> ['PrettyHeading'] = {Schedule Name}
                                        => ['Headings'] = array({PrettyDays})
                                        => ['HeadingSponsors'] => array({Sponsors})
                                        => ['Shows'] = array([day_index] = array([time] => {Show}))
                                        => ['Times'] = array(TimeArray);
                                        => ['Resolution'] = Resolution
                    )
                    ***************************************/
                    $NewShowListings = array('PrettyHeading' => '', 'Headings' => array(), 'HeadingSponsors' => array(), 'Shows' => array());
                    foreach ($AllShowListings as $Heading => $ShowListings){
                        $NewShowListings['Headings'][$Heading] = $ShowListings['PrettyHeading'];
						$NewShowListings['Shows'][$Heading] = array();
                        foreach ($ShowListings["Headings"] as $h => $SubHeading){
                            if (is_array($ShowListings["HeadingSponsors"]) and $ShowListings["HeadingSponsors"][$h] != ""){
                                $NewShowListings['HeadingSponsors'][$Heading] = $ShowListings["HeadingSponsors"][$h];
                            }
							if (is_array($ShowListings['Shows'][$h])){
								foreach ($ShowListings['Shows'][$h] as $CanonicalTime => $Show){
									if (!is_array($Show)){
										$Show = array($Show);
									}
									if (!array_key_exists($CanonicalTime,$NewShowListings['Shows'][$Heading])){
										$NewShowListings['Shows'][$Heading][$CanonicalTime] = array();
									}
									foreach ($Show as $s){
										// Set the header, finally, now that we know what kind of show we're dealing with
										if ($NewShowListings['PrettyHeading'] == ""){
											$ScheduleContainer = new ScheduleContainer();
											$Schedule = $ScheduleContainer->getSchedule($s->getParameter('ShowYear'),$s->getParameter('ShowScheduleUID'));
											if (is_a($Schedule,'Schedule')){
												$NewShowListings['PrettyHeading'] = $Schedule->getParameter('ScheduleName');
											}
										}
										$NewShowListings['Shows'][$Heading][$CanonicalTime][] = $s;
									}
								}
							}
                            if (!array_key_exists('Times',$NewShowListings)){
                                $NewShowListings['Times'] = $ShowListings['Times'];
                                $NewShowListings['Resolution'] = $ShowListings['Resolution'];
                            }
                            else{
                                // array_merge doesn't work because of the numeric keys, so we'll have to do it ourselves
                                foreach ($ShowListings['Times'] as $CanonicalTime => $PrettyTime){
                                    if (!array_key_exists($CanonicalTime,$NewShowListings['Times'])){
                                        $NewShowListings['Times'][$CanonicalTime] = $PrettyTime;
                                    }
                                }
                                ksort($NewShowListings['Times']);
                            }
                        }
                    }
                    $AllShowListings = array($NewShowListings);
                    break;
                default:
                    break;
                }

				$first = "first";
                foreach ($AllShowListings as $Heading => $ShowListings){
                    if ($Heading !== "Unassigned" and is_array($ShowListings["Headings"]) and count($ShowListings["Headings"])){
                        $Content.= "<p class='ShowListingHeading $first'>".$ShowListings["PrettyHeading"]."</p>\n";
						$first = "";
						$Content.= "<div class='ShowListingTableWrapper'>\n";
                        $Content.= "<table class='ShowListingTable'>\n";
					
						// Figure out the width to make the cells
						if (function_exists('apply_filters')){
							$void = apply_filters('schedule_painter_options',array(&$this,&$ShowListings,&$Heading));
						}
						$Columns = count($ShowListings["Headings"]);
						$Rows = count($ShowListings["Times"]) + 1;
						
						$TimeColumnWidth  = "11";
						$Width = floor((100 - intval($TimeColumnWidth))/intval($Columns));
						// top left cell
                        $Content.= "<tr>";
                        if ($this->show_times and in_array($this->times_position,array('','left','both'))){
                            $Content.= "<td  class='ShowListingRowHeadingCell' width='$TimeColumnWidth%'>&nbsp;</td>\n";
                        }

						// Row with all of the column Headers
                        foreach ($ShowListings["Headings"] as $h => $SubHeading){
                                if ($h !== ""){
                                        $Content.= "<td class='ShowListingColumnHeadingCell' width='$Width%'><p class='ShowListingColumnHeading'>$SubHeading</p>";
                                        if (is_array($ShowListings["HeadingSponsors"]) and $ShowListings["HeadingSponsors"][$h] != ""){
                                            $Content.= "<p class='ShowListingColumnHeadingSponsor'>".$this->getHeadingSponsorDisplay($ShowListings["HeadingSponsors"][$h])."</p>";
                                        }
                                        $Content.= "</td>\n";
                                }
                        }
                        if ($this->show_times and in_array($this->times_position,array('right','both'))){
                            $Content.= "<td  class='ShowListingRowHeadingCell' width='$TimeColumnWidth%'>&nbsp;</td>\n";
                        }
						if ($this->show_handle){
							$Content.= "<td rowspan='$Rows' class='table-handle'>&nbsp;</td>";
						}
						
                        $Content.= "</tr>\n";

                        // Figure out the Resolution
                        /*** Old Way
                        $ResolutionTime = array_slice(array_keys($ShowListings["Times"]),0,2);
                        $Resolution = intval($ResolutionTime[1]) - intval($ResolutionTime[0]);
                        ****/
                        $Resolution = $ShowListings["Resolution"];
                    

						// Using the Times passed back, go row-by-row to create the schedule
                        foreach ($ShowListings["Times"] as $CanonicalTime => $PrettyTime){
                                $Content.= "<tr>";
		                        if ($this->show_times and in_array($this->times_position,array('','left','both'))){
                                    $Content.= "<td class='ShowListingRowHeadingCell'><p class='ShowListingRowHeading'>$PrettyTime</p>";
                                    if ($this->getRowHeight() != ""){
                                        $Content.= "<img style='float:left' src='images/spacer.gif' width=1 height=".$this->getRowHeight().">\n";
                                    }
                                    $Content.= "</td>\n";
                                }
                                foreach ($ShowListings["Headings"] as $h => $SubHeading){
                                        if ($h !== ""){
												if (!is_array($ShowListings["Shows"][$h])){
													$ShowListings["Shows"][$h] = array();
												}
												ksort($ShowListings["Shows"][$h]);
                                                if ($ShowListings["Shows"][$h][$CanonicalTime] == ""){  
 														// I Heart Hackabees - the conclusion
                                                        $Content.= "<td class='ShowListingBlankCell'>".eval('return "'.$this->getBlankCellContent().'";')."</td>\n";
                                                }
                                                else{
                                                    if(is_a($ShowListings["Shows"][$h][$CanonicalTime],'Show')){
                                                        $Show = $ShowListings["Shows"][$h][$CanonicalTime];
                                                        $Shows = array($Show);
                                                    }
                                                    elseif (is_array($ShowListings["Shows"][$h][$CanonicalTime])){
                                                        $Show = $ShowListings["Shows"][$h][$CanonicalTime][0];
                                                        $Shows = $ShowListings["Shows"][$h][$CanonicalTime];
                                                    }
                                                    else{
                                                        unset($Show);
                                                    }
                                                    if(is_a($Show,'Show')){
														// If there is more than one show, then we are going to span some rows and show all
														// in an inside .ShowListingTable.  This is tricky.  if one of the shows is
														// much longer than the others, then we might have to absorb later shows in the ListingsArray
														
														unset($maxEnd);
														unset($minEnd);
														if (count($Shows) > 1){
															// Step 1. Find the max & min ShowEndTime
															foreach ($Shows as $tmp){
																if (!isset($maxEnd) or $tmp->getParameter('ShowEndTime') > $maxEnd){
																	$maxEnd = $tmp->getParameter('ShowEndTime');
																}
																if (!isset($minEnd) or $tmp->getParameter('ShowEndTime') < $minEnd){
																	$minEnd = $tmp->getParameter('ShowEndTime');
																}
															}
															
														}
														else{
															$maxEnd = $minEnd = $Show->getParameter('ShowEndTime');
														}
														
														// Step 2. See if there are other shows in the Listings Array
														// with a start time after $CanonicalTime but before $maxEnd
														foreach(array_keys($ShowListings["Shows"][$h]) as $testTime){
															if ($testTime > $CanonicalTime and $testTime < $maxEnd){
																// Oh boy, found one. 
																if (is_a($ShowListings["Shows"][$h][$testTime],'Show')){
																	$Shows[] = $ShowListings["Shows"][$h][$testTime];
																}
																elseif (is_array($ShowListings["Shows"][$h][$testTime])){
																	$Shows = array_merge($Shows,$ShowListings["Shows"][$h][$testTime]);
																}
																unset($ShowListings["Shows"][$h][$testTime]);
															}
														}
														
														$inner_columns = array();
														if (count($Shows) > 1 and $minEnd != $maxEnd){
															// This is the tricky case.  We've got multiple shows to display 
															// within an inner table.ShowListingTable.  
															// I'm going to approach it by splitting the shows into columns
															// I've got two arrays to deal with: $Shows and $AdditionalShows
															foreach ($Shows as $_Show){
																$ShowStart = $_Show->getParameter('ShowStartTime');
																$ShowEnd = $_Show->getParameter('ShowEndTime');
																if (!count($inner_columns)){
																	$inner_columns[] = array($_Show);
																}
																else{
																	// figure out which column to put the show into
																	// This is done by looking at the times currently 
																	// in each column and seeing if the current fits
																	foreach ($inner_columns as $c => $colShows){
																		$fits = true;
																		foreach ($colShows as $colShow){
																			if (!$fits){
																				continue;
																			}
																			// Different cases:
																			// 1. $_Show start time sits between $colShow start & end time
																			// 2. $_Show end time sits between $colShow start & end time
																			// 3. $_Show start time before $colShow start and $_Show end time after $colShow end time
																			$ColShowStart = $colShow->getParameter('ShowStartTime');
																			$ColShowEnd = $colShow->getParameter('ShowEndTime');
																			if (($ShowStart >= $ColShowStart and $ShowStart < $ColShowEnd)
																			or  ($ShowEnd > $ColShowStart and $ShowEnd <= $ColShowEnd)
																			or  ($ShowStart <= $ColShowStart and $ShowEnd >= $ColShowEnd)){
																				$fits = false;
																			}
																		}
																		if ($fits){
																			$inner_columns[$c][] = $_Show;
																			break;
																		}
																		else{
																			continue;
																		}
																	}
																	if (!$fits){
																		$inner_columns[] = array($_Show);
																	}
																}
															}
														}														
	
                                                        $Start = $CanonicalTime; //$Show->getParameter('ShowStartTime');
                                                        //$End = $Show->getParameter('ShowEndTime');
                                                        $Rows = 0;
                                                        for ($t = $Start; $t < $maxEnd; $t += $Resolution){
																$t = $this->adjustTime($t);
                                                                if ($t < $maxEnd){
                                                                        $Rows++;
																		if ($t != $Start and $ShowListings["Shows"][$h][$t] == ''){
                                                                        	$ShowListings["Shows"][$h][$t] = "spanned"; // This just needs to be set to something to do the magic.  
																		}
                                                                }
                                                        }
														if (count($inner_columns)){
															// Special Case
															// First, let's sort the inner_columns by start time
															foreach ($inner_columns as $i => $inner_shows){
																usort($inner_shows,create_function('$a,$b','$_a = $a->getParameter("ShowStartTime"); $_b = $b->getParameter("ShowStartTime"); return ($_a < $_b ? -1 : 1);'));
																$inner_columns[$i] = $inner_shows;
															}
                                                            $Content.= "<td rowspan='$Rows' style='padding:0;margin:0;'><table class='ShowListingTable' style='' border='0'>";
															$cell_width = 'width="'.floor(100/count($inner_columns)).'%"';
	                                                        for ($t = $Start; $t < $maxEnd; $t += $Resolution){
																$t = $this->adjustTime($t);
																$Content.= "<tr><td class='hidden'>$t</td>"; // Need to add the cell to allow the spanning of the rows below to work
																foreach ($inner_columns as $i => $inner_shows){
																	if (count($inner_shows)){
																		$inner_show = current($inner_shows); // First show
																		if ($inner_show->getParameter('ShowStartTime') == $t){
																			// First, figure out the rows to span
																			$span_rows = 0;
																			for ($_t = $t; $_t < $inner_show->getParameter('ShowEndTime'); $_t += $Resolution){
																				$_t = $this->adjustTime($_t);
																				$span_rows++;
																			}
																			$Style = $this->getShowStyle($inner_show);
																			if ($Style != ''){
																				$Style = "style='$Style'";
																			}
				                                                            $Class = $this->getShowClass($inner_show);
																			$Content.= "<td rowspan=\"$span_rows\" $cell_width class=\"ShowListingCell$Class\" $Style>".$inner_show->getListing($TitleCallback,$ArtistCallback);
																			if ($ShowExtrasCallback != ''){
																				$Content.= $ShowExtrasCallback($inner_show);
																			}
																			$Content.= "</td>";
																			array_shift($inner_shows); // Only do this show once
																			$inner_columns[$i] = $inner_shows;
																		}
																		else{
					                                                        //$Content.= "<td class='ShowListingBlankCell'>".eval('return "'.$this->getBlankCellContent().'";')."</td>\n";
																		}
																	}
																}
																$Content.= "</tr>\n";
															}
                                                            $Content.= "</table></td>";
														}
														else{
															// Default Case
	                                                        if (count($Shows) > 1){
	                                                            $Content.= "<td rowspan='$Rows' style='padding:0;margin:0;'><table class='ShowListingTable' style='' border='0'><tr>";
																$cell_width = 'width="'.floor(100/count($Shows)).'%"';
	                                                            $Rows = 1;
	                                                        }
															else{
																$cell_width = '';
															}
	                                                        foreach ($Shows as $tmpIndex => $Show){
	                                                            if (($Style = $this->getShowStyle($Show)) != ""){
	                                                                $Style = "style='$Style'";
	                                                            }
	                                                            elseif ($tmpIndex > 0){
	                                                                $Style.="style='border-left:1px solid black'";
	                                                            }
	                                                            $Class = $this->getShowClass($Show);
	                                                            if (!$this->show_times and $Show->getParameter('ShowStartTimeSpoofed')){
	                                                                $Show->setParameter('ShowStartTimeSpoofed',false);
	                                                            }
	                                                            $Content.= "<td rowspan='$Rows' class='ShowListingCell$Class' $cell_width $Style>".$Show->getListing($TitleCallback,$ArtistCallback);
	                                                            if ($ShowExtrasCallback != ""){
	                                                                $Content.= $ShowExtrasCallback($Show);
	                                                            }
	                                                            $Content.= "</td>\n";
	                                                        }
	                                                        if (count($Shows) > 1){
	                                                            $Content.= "</tr></table></td>";
	                                                        }
														}
                                                    }
                                                }
                                        }
                                }
		                        if ($this->show_times and in_array($this->times_position,array('right','both'))){
                                    $Content.= "<td class='ShowListingRowHeadingCell on-right'><p class='ShowListingRowHeading'>$PrettyTime</p>";
                                    if ($this->getRowHeight() != ""){
                                        $Content.= "<img style='right:left' src='images/spacer.gif' width=1 height=".$this->getRowHeight().">\n";
                                    }
                                    $Content.= "</td>\n";
                                }
                                $Content.= "</tr>\n";
                        }

                        $Content.= "</table>\n";
						$Content.= "</div> <!-- ShowListingTableWrapper -->\n";
                    }
                }
            }
            return $Content;
    }

	function adjustTime($t){
        if (strlen($t) != 4){
            $t = "0".$t;
        }
        if (intval(substr($t,2,2)) >= 60){
                $t+= 40;
        }
        if (strlen($t) != 4){
            $t = "0".$t;
        }
		return $t;
	}

	function getHeadingSponsorDisplay($Sponsor){
		if ($return = apply_filters('HeadingSponsor',$Sponsor)){
			return $return;
		}
		else{
			return "Sponsor: $Sponsor";
		}
	}
    
}
?>