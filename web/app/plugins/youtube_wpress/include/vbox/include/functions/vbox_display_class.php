<?php

class Vbox_display_class {
	
	function getSocialShareButtons($url) {
		$facebookShareLink="http://www.facebook.com/share.php?u=".$url;
		$twitterShareLink="http://twitter.com/share?url=".$url."&related=Montreal_city";
		$favosaurusShareLink = 'http://favosaurus.com/share?u='.$url;
		
		$embedCode .= '<table width=100% style="margin-top:5px;"><tr>';
		$embedCode .= '<td style="text-align:right;">';
		$embedCode .= '<a href="javascript:openPopup(\''.$favosaurusShareLink.'\',\'1080\',\'650\');" title="Share on Favosaurus"><img src="'.$GLOBALS['path_vbox'].'include/graph/social32/favosaurus.png" style="padding-top:2px"></a>&nbsp;';
		$embedCode .= '<a href="javascript:openPopup(\''.$twitterShareLink.'\',\'800\',\'400\');" title="Share on Twitter"><img src="'.$GLOBALS['path_vbox'].'include/graph/social32/twitter.png" style="padding-top:2px"></a>&nbsp;';
		$embedCode .= '<a href="javascript:openPopup(\''.$facebookShareLink.'\',\'800\',\'400\');" title="Share on Facebook"><img src="'.$GLOBALS['path_vbox'].'include/graph/social32/facebook.png" style="padding-top:2px"></a>&nbsp;';
		$embedCode .= '</td></tr></table>';
		return $embedCode;
	}
	
	function displayVideosList($videosData,$criteria) {
		$type = $criteria['type'];
		
		//type=1: facebook type display
		if($type==1) {
			$this->displayVideosListFacebookLikeByVideosDatas($videosData,$criteria);
		}
		elseif($type==2) {
			$this->display_videos_grid($videosData,$criteria);
		}
	}
	
	function display_videos_grid($videosData,$criteria) {
		$pageNumber = $criteria['pageNumber'];
		$nb_display = $criteria['nb_display'];
		
		$nbTotal=$videosData['stats']['totalResults'];
		if($pageNumber=='') $pageNumber = 1;
		
		// patch for favorite's user videos
		if($nbTotal==0) {
			$nbTotal = count($videosData['videos']);
		}
		
		$start = $nb_display*$pageNumber-$nb_display;
		
		for($i=0;$i<count($videosData['videos']);$i++) {
			$videoid = $videosData['videos'][$i]['videoid'];
			$videoThumbnail = $videosData['videos'][$i]['thumbnail'];
			$title = $videosData['videos'][$i]['title'];
			$url = $videosData['videos'][$i]['url'];
			$duration = $videosData['videos'][$i]['duration'];
			$viewCount = $videosData['videos'][$i]['viewCount'];
			
			echo '<div class="imgGrid">';
			echo '<a href="http://www.youtube.com/embed/'.$videoid.'?autoplay=1" class="play_youtube iframe" title="'.$title.'">';
			echo '<img src="'.$videoThumbnail.'" class="thumbnail" style="width:80px; height:55px; margin-right:10px; margin-bottom:10px;" border=0>';
			//echo '<span class="play"></span>';
			echo '</a>';
			echo '</div>';
		}
		
		$criteria3['nbTotal'] = $nbTotal;
		$criteria3['start'] = $start;
		$criteria3['nb_display'] = $nb_display;
		
		$this->display_pagination($criteria3);
		
		?>
		
		<?php
	}
	
	function displayVideosListFacebookLikeByVideosDatas($videosData,$criteria=array()) {
		$pageNumber = $criteria['pageNumber'];
		$nb_display = $criteria['nb_display'];
		
		$nbTotal=$videosData['stats']['totalResults'];
		if($pageNumber=='') $pageNumber = 1;
		
		// patch for favorite's user videos
		if($nbTotal==0) {
			$nbTotal = count($videosData['videos']);
		}
		
		$start = $nb_display*$pageNumber-$nb_display;
		
		$videoType=0; //0=youtube
		
		// display head menu
		$criteria3['nbTotal'] = $nbTotal;
		$criteria3['start'] = $start;
		$criteria3['nb_display'] = $nb_display;
		
		//$this->displayMenuPaginationNumber($criteria3);
		
		//$d .= $this->display_pagination($criteria3);
		$d .= '<br>';
		
		$gf1 = new Vbox_general_functions_class;
		
		for($i=0;$i<count($videosData['videos']);$i++) {
			$videoid = $videosData['videos'][$i]['videoid'];
			$videoThumbnail = $videosData['videos'][$i]['thumbnail'];
			$title = $videosData['videos'][$i]['title'];
			$url = $videosData['videos'][$i]['url'];
			$duration = $videosData['videos'][$i]['duration'];
			$viewCount = $videosData['videos'][$i]['viewCount'];
			$description = $videosData['videos'][$i]['description'];
			$category = $videosData['videos'][$i]['category'];
			$tags = $videosData['videos'][$i]['tags'];
			
			$width = 120 ;# $videosData['videos'][$i]['thumb_width'];
			#$height =  $videosData['videos'][$i]['thumb_height'];

			$style = '';
			if($width!='') $style .= 'width: '.$width.'px;';
			if($height!='') $style .= 'height: '.$height.'px;';
			
			
			
			
			if(count($tags)>0) $tags = implode(', ', $tags);
			
			$d .= '<div class="itemBox" id="'.$videoid.'" type="'.$videoType.'" style="overflow:hidden ">';
			
				$d .= '<div class="videoPlayBox thumbnailBox" style="float:left;'.$style.' margin-right:20px">';
					$d .= '<a href="'.$url.'" class="displayVideoPlayer">';
					$d .= '<img src="'.$videoThumbnail.'" style="'.$style.' padding-right:30px;" border=0>';
					$d .= '<span class="play"></span>';
					$d .= '</a>';
				$d .= '</div>';
				
				$d .= '<div><h5><a class="displayVideoPlayer black" href="'.$url.'">'.$title.'</a></h5>';
				$d .= '<p><small>'.$gf1->formatDuration($duration);
				$d .= ' - Views: '.number_format($viewCount).'</small></p></div>';
				
				$d .= '<div style="display:none" class="video_details">';
				if($description!='') $d .= '<div><b>Description:</b> '.$description.'</div>';
				//if($category!='') $d .= '<div><b>Category:</b> <a href="./index2.php?t=5&cat='.$category.'">'.$category.'</a></div>';
				//if($tags!='') $d .= '<div><b>Tags: </b>'.$tags.'</div>';
				$d .= '</div>';
				
			$d .= '</div>';
		}
		
		if(count($videosData['videos'])==0) {
			$d .= 'No results found';
		}
		
		$d .= '<br>';
		
		$d .= $this->display_pagination($criteria3);
		$d .= '<br>';
		
		return $d;
	}
	
	/*
	function displayMenuPaginationNumber($criteria) {
		$jsName = $criteria['jsName'];
		$jsCriteria = $criteria['jsCriteria'];
		$nbTotal = $criteria['nbTotal'];
		$pageNumber = $criteria['pageNumber'];
		$nb_display = $criteria['nb_display'];
		$headTitle = $criteria['headTitle'];
		$dom = $criteria['dom'];
		$noCount = $criteria['noCount'];
		$jsLeft = $criteria['jsLeft'];
		$jsRight = $criteria['jsRight'];
		
		if($jsLeft=='') $jsLeft = $jsName.'(\''.$dom.'\',\''.($pageNumber-1).'\',\''.$jsCriteria.'\')';
		if($jsRight=='') $jsRight = $jsName.'(\''.$dom.'\',\''.($pageNumber+1).'\',\''.$jsCriteria.'\')';
		
		//echo '<table border=0 width="100%" cellpadding=0 cellspacing=0 style="border-collapse:collapse"><tr><td style="padding:2px">';
		//if($nbTotal>$nb_display) {
			echo '<table width=100% border=0 class="titleHeaderBox">';
			echo '<tr>';
				if($noCount!=1) echo '<td style="padding:3px"><b>'.number_format($nbTotal).' '.$headTitle.'</b></td>';
				else echo '<td style="padding:3px"><b>'.$headTitle.'</b></td>';
				if($nbTotal>$nb_display) {
					echo '<td align="right" style="padding:3px">';
					echo '<a id="'.$dom.'_reload"></a>&nbsp;';
					if($pageNumber>1) { 
						echo '&nbsp; <a href="javascript:" onClick="'.$jsLeft.'" title="'.htmlentities('Previous').'">
						<img src="/'.$GLOBALS['path_vbox'].'include/graph/icons/leftarrow.png" style="padding-bottom:3px;" border=0></a>';
					}
					if($pageNumber>0) echo '&nbsp;<small><b>'.$pageNumber.'/'.ceil($nbTotal/$nb_display).'</b></small>';
					if($nbTotal>($nb_display*$pageNumber)) {
						echo ' <a href="javascript:" onClick="'.$jsRight.'" title="Next">
						<img src="/'.$GLOBALS['path_vbox'].'include/graph/icons/rightarrow.png" style="padding-bottom:3px;" border=0></a>';
					}
					echo '&nbsp;</td>';
				}
			echo '</tr>';
			echo '</table>';
			echo '<table><tr height=5><td></td></tr></table>';
		//}
		//echo '</td></tr></table>';
	}
	*/
	
	// Pagination general function
	function display_pagination($criteria) {
		$nbTotal = $criteria['nbTotal'];
		$start = $criteria['start'];
		$nb_display = $criteria['nb_display'];
		$nbPageMax = $criteria['nbPageMax'];
		
		if($nbPageMax=='') $nbPageMax=10;
		
		// Pagination display
		if($nb_display!=0) $begin = $start/$nb_display;
		$debut = $begin-round($nbPageMax/2);
		$fin = $begin+round($nbPageMax/2);
		if($nb_display!=0) $nbPageResult = $nbTotal/$nb_display;
		
		$tab = $_GET;
		if(count($tab)>0) {
			foreach($tab as $ind=>$value) {
				if($ind!='vp') $getString .= '&'.$ind.'='.$value;
			}		
		}
		
		/*
		echo '$nbTotal: '.$nbTotal.'<br>';
		echo '$start: '.$start.'<br>';
		echo '$nb_display: '.$nb_display.'<br>';
		*/
		
		if($nbTotal>0 && $nbTotal>$nb_display) {
			if($fin<$nbPageMax)$fin = $nbPageMax;
			if($debut<0)$debut = 0;
			$previous = $begin-1;
			$next = $begin+1;
			
			#$d .= '<center>';
			$d .= '<div class="clearboth" style="clear:both;padding-bottom:10px"></div><div class="pagination"><ul>';
			if($previous>=0) {
				$tmpStart = ($previous*$nb_display);
				#$d .= '<a class="vbox_pagination" href="./?vp='.($previous+1).$getString.'" title="'.($previous+1).'"><small><<</small></a>&nbsp;'; //($previous+1)
				$d .= '<li><a  class=" videosListPagination"  href="./?vp='.($previous+1).$getString.'" title="'.($previous+1).'"><small><<</small></a></li>'; //($previous+1)
			}
			
			for ($i=$debut; $i<$fin && $i<$nbPageResult;$i++) {
			  $k = $i+1;
			  $start = $i*$nb_display;
			  $tmpStart = $start;
			  
			 # if ($i == $begin)  $d .= '<font color="red"><small>'.$k.'</small>&nbsp;</font>';
 			  if ($i == $begin)  $d .= '<li class="current active"><span>'.$k.'</span></li>';
			  else {
			  #	$d .= '<a class="vbox_pagination" href="./?vp='.($i+1).$getString.'" title="'.($i+1).'"><small>'.$k.'</small></a>&nbsp;'; //($i+1)
				$d .= '<li><a  class=" videosListPagination" href="./?vp='.($i+1).$getString.'" title="'.($i+1).'">'.$k.'</a></li>'; //($i+1)
			  }
			}
			
			if($next<$nbPageResult) {
				$tmpStart = ($next*$nb_display);
				#$d .= '&nbsp;<a class="vbox_pagination" href="./?vp='.($next+1).$getString.'" title="'.($next+1).'"><small>>></small></a>'; //($next+1)
				$d .= '<li><a  class=" videosListPagination" href="./?vp='.($next+1).$getString.'" title="'.($next+1).'"><small>>></small></a></li>'; //($next+1)
			}
			#$d .= '</center>';
			$d .= '</ul></div>';
		}
		
		return $d;
	}

}
	
?>