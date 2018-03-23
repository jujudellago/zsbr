<?php

/*
Last update: 31 Oct 2012
*/

class Youtube_class {
	
	function getPlaylistUrl($criteria) {
		$id = $criteria['id'];
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		
		$url = 'https://gdata.youtube.com/feeds/api/playlists/'.$id.'';
		
		if($startIndex=='') $startIndex=1;
		if($maxResults=='') $maxResults=10;
		
	  	if(!empty($startIndex))
		  	$url .= '?start-index='.$startIndex.'&';
	  	if(!empty($maxResults))
		  	$url .= 'max-results='.$maxResults.'&';
		
		$url = substr($url,0,-1).'&v=2&alt=jsonc';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		
		return $url;
	}
	
	function getPlaylistsSearch($criteria) {
		$q = urlencode($criteria['q']);
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		
		$url = 'https://gdata.youtube.com/feeds/api/playlists/snippets?q='.$q.'&';
		
	  	if(!empty($startIndex))
		  	$url .= 'start-index='.$startIndex.'&';
	  	if(!empty($maxResults))
		  	$url .= 'max-results='.$maxResults.'&';
		
		$url = substr($url,0,-1).'&v=2&alt=jsonc';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		
		//echo $url;
		
		$content = $this->getDataFromUrl($url);
		$content = json_decode($content,true);
		//$content = $this->xml2array($content);
		
		return $content;
	}
	
	// feedId: top_rated, top_favorites, most_viewed, most_popular, most_recent, most_discussed, most_linked, most_responded, recently_featured, watch_on_mobile
	// time: today, this_week, this_month, all_time (all_time is the default parameter if no time selected)
	function getYoutubeStandardVideosFeeds($criteria) {
		$feed = $criteria['feed'];
		$time = $criteria['time'];
		$category = $criteria['category'];
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		$format = $criteria['format'];
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		if($feed=='most_recent') $time='';
		
	  	// Default values
	  	if(empty($feed)) $feed='most_popular';
	  	if(empty($format)) $format=5; //5=only videos that can be embedded
	  	
	  	if($category!='') $feed .= '_'.$category;
	  	
		$url = 'http://gdata.youtube.com/feeds/api/standardfeeds/'.$feed.'?';
		
		if(!empty($time))
			$url .= 'time='.$time.'&';
	  	if(!empty($startIndex))
		  	$url .= 'start-index='.$startIndex.'&';
	  	if(!empty($maxResults))
		  	$url .= 'max-results='.$maxResults.'&';
		if(!empty($format))
		   $url .= 'format='.$format.'&';
		
		$url = substr($url,0,-1).'&v=2&alt=jsonc';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		
		return $url;
	}
	
	function getChannelData($criteria=array()) {
		$username = $criteria['username'];
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		
		$url = 'http://gdata.youtube.com/feeds/api/users/'.$username.'?v=2';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		
		$content = $this->getDataFromUrl($url);
		$content = $this->formatChannelData($content);
		//$content = json_decode($content,true);
		//print_r($content);
		
		return $content;
	}
	
	function formatChannelData($content) {
		
		$content = $this->xml2array($content);
		//print_r($content);
		//echo '<br><br>';
		
		if($content['entry']['yt:username']!='') {
			$data['username'] = $content['entry']['yt:username'];
			$data['aboutMe'] = $content['entry']['yt:aboutMe'];
			$data['age'] = $content['entry']['yt:age'];
			$data['firstName'] = $content['entry']['yt:firstName'];
			$data['hometown'] = $content['entry']['yt:hometown'];
			$data['location'] = $content['entry']['yt:location'];
			$data['company'] = $content['entry']['yt:company'];
			$data['subscriberCount'] = $content['entry']['yt:statistics_attr']['subscriberCount'];
			$data['viewCount'] = $content['entry']['yt:statistics_attr']['viewCount'];
			$data['totalUploadViews'] = $content['entry']['yt:statistics_attr']['totalUploadViews'];
			$data['thumbnail'] = $content['entry']['media:thumbnail_attr']['url'];
			
			if(strpos($content['entry']['gd:feedLink']['5_attr']['rel'], 'user.uploads')>0) $data['nb_videos'] = $content['entry']['gd:feedLink']['5_attr']['countHint'];
			else if(strpos($content['entry']['gd:feedLink']['6_attr']['rel'], 'user.uploads')>0) $data['nb_videos'] = $content['entry']['gd:feedLink']['6_attr']['countHint'];
			else if(strpos($content['entry']['gd:feedLink']['4_attr']['rel'], 'user.uploads')>0) $data['nb_videos'] = $content['entry']['gd:feedLink']['4_attr']['countHint'];
		}
		
		return $data;
	}
	
	function getYoutubeVideosByCategory($criteria) {
		$category = $criteria['category'];
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		
		$url = 'http://gdata.youtube.com/feeds/api/videos/-/'.$category.'?v=2';
		
		if(!empty($startIndex))
		  $url .= '&start-index='.$startIndex;
		if(!empty($maxResults))
		   $url .= '&max-results='.$maxResults;
		
		$url = $url.'&alt=jsonc';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		//echo $url;
		return $url;
	}
	
	function getYoutubeUserFavoriteVideos($criteria) {
		$username = $criteria['username'];
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		
		$url = 'http://gdata.youtube.com/feeds/api/users/'.$username.'/favorites?v=2';
		
		if(!empty($startIndex))
		  $url .= '&start-index='.$startIndex;
		if(!empty($maxResults))
		   $url .= '&max-results='.$maxResults;
		
		$url = $url.'&alt=jsonc';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		
		return $url;
	}
	
	function getYoutubeUsernameVideos($criteria) {
		$username = $criteria['username'];
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		$orderby = $criteria['orderby']; // relevance, published, viewCount, rating
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		
		$url = 'http://gdata.youtube.com/feeds/api/users/'.$username.'/uploads?';
		
		if(!empty($startIndex))
		  $url .= 'start-index='.$startIndex.'&';
		if(!empty($maxResults))
		   $url .= 'max-results='.$maxResults.'&';
		if(!empty($orderby))
		  $url .= 'orderby='.$orderby.'&';
		
		$url = substr($url,0,-1);
		
		$url = $url.'&v=2&alt=jsonc';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		
		//echo $url.'<br><br>';
		
		return $url;
	}
	
	// get custom feeds depending on several parameters http://gdata.youtube.com/feeds/api/videos?
	function getYoutubeSearchVideosFeeds($criteria) {
		$url = 'http://gdata.youtube.com/feeds/api/videos?';
		$q = urlencode($criteria['q']);
		$orderby = $criteria['orderby']; // relevance, published, viewCount, rating
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		$author = $criteria['author'];
	  	$format = $criteria['format'];
	  	$lr = $criteria['lr']; // fr, en
	  	$safeSearch = $criteria['safeSearch']; //none, moderate, strict
	  	$time = $criteria['time']; // today, this_week, this_month, all_time
	  	$hd = $criteria['hd']; //possible value: true
		$youtube_api_key = $criteria['youtube_api_key'];
		
		if($youtube_api_key=='') $youtube_api_key = $GLOBALS['youtube_api_key'];
		
	  	// Default values
	  	if(empty($format)) $format=5; //5=only videos that can be embedded
	  	if(empty($orderby)) $orderby='relevance';
	  	if(empty($safeSearch)) $safeSearch='strict';
	  	//if(empty($lr)) $lr='en';
	  	
	  	if(!empty($q))
		  $url .= 'q='.$q.'&';
		if(!empty($orderby))
		  $url .= 'orderby='.$orderby.'&';
		if(!empty($startIndex))
		  $url .= 'start-index='.$startIndex.'&';
		if(!empty($maxResults))
		   $url .= 'max-results='.$maxResults.'&';
		if(!empty($author))
		   $url .= 'author='.$author.'&';
		if(!empty($format))
		   $url .= 'format='.$format.'&';
		if(!empty($lr))
		   $url .= 'lr='.$lr.'&';
		if(!empty($safeSearch))
		   $url .= 'safeSearch='.$safeSearch.'&';
		if(!empty($time))
		   $url .= 'time='.$time.'&';
		if(!empty($hd))
		   $url .= 'hd=true&';
		   
		$url = substr($url,0,-1).'&v=2&alt=jsonc';
		if($youtube_api_key!='') $url .= '&key='.$youtube_api_key;
		
		return $url;  
	}
	
	function getYoutubeRelatedVideos($criteria) {
		$videoid = $criteria['videoid'];
		$startIndex = $criteria['start-index'];
		$maxResults = $criteria['max-results'];
		
		$url = 'http://gdata.youtube.com/feeds/api/videos/'.$videoid.'/related?v=2';
		
		if(!empty($startIndex))
		  $url .= '&start-index='.$startIndex;
		if(!empty($maxResults))
		   $url .= '&max-results='.$maxResults;
		
		$url .= '&alt=jsonc';
		if($GLOBALS['youtube_api_key']!='') $url .= '&key='.$GLOBALS['youtube_api_key'];
		//echo $url;
		
		return $url;
	}
	
	function getYoutubeVideoDataByVideoId($videoid) {
		
		$url = 'http://gdata.youtube.com/feeds/api/videos/'.$videoid.'?v=2&alt=jsonc';
		if($GLOBALS['youtube_api_key']!='') $url .= '&key='.$GLOBALS['youtube_api_key'];
		
		$content = $this->getDataFromUrl($url);
		$content = json_decode($content,true);
		
		if($content['data']['id']!='') {
			// returned values
			$videoData['videoid'] = $content['data']['id'];
			$videoData['url'] = $content['data']['player']['default'];
			$videoData['title'] = $content['data']['title'];
			$videoData['description'] = $content['data']['description'];
			$videoData['author'] = $content['data']['uploader'];
			$videoData['thumbnail'] = $content['data']['thumbnail']['sqDefault'];
			$videoData['duration'] = $content['data']['duration'];
			$videoData['viewCount'] = $content['data']['viewCount'];
			$videoData['rating'] = $content['data']['rating'];
			$videoData['uploaded'] = $content['data']['uploaded']; //date
			$videoData['category'] = $content['data']['category'];
			$videoData['tags'] = $content['data']['tags'];
			$videoData['commentCount'] = $content['data']['commentCount'];
		}
		
		return $videoData;
	}
	
	// http://gdata.youtube.com/feeds/api/standardfeeds/top_favorites
	function returnYoutubeVideosDatasByURL($feedURL,$addDatas=array()) {
		$q = $addDatas['q'];
		$username = $addDatas['username'];
		$favorite = $addDatas['favorite'];
		$playlist = $addDatas['playlist'];
		
		if($feedURL!='') {
			
			$content = $this->getDataFromUrl($feedURL);
			$content = json_decode($content,true);
			$videosList = $content['data']['items'];
			
			//print_r($content);
			
			if($favorite==1) {
				for($i=0; $i<count($videosList); $i++) {
					$videosDatas['videos'][$i]['videoid'] = $videosList[$i]['video']['id'];
					$videosDatas['videos'][$i]['url'] = $videosList[$i]['video']['player']['default'];
					$videosDatas['videos'][$i]['title'] = $videosList[$i]['video']['title'];
					$videosDatas['videos'][$i]['description'] = $videosList[$i]['video']['description'];
					$videosDatas['videos'][$i]['author'] = $videosList[$i]['video']['uploader'];
					$videosDatas['videos'][$i]['thumbnail'] = $videosList[$i]['video']['thumbnail']['sqDefault'];
					$videosDatas['videos'][$i]['duration'] = $videosList[$i]['video']['duration'];
					$videosDatas['videos'][$i]['viewCount'] = $videosList[$i]['video']['viewCount'];
					$videosDatas['videos'][$i]['rating'] = $videosList[$i]['video']['rating'];
					$videosDatas['videos'][$i]['updated'] = $videosList[$i]['video']['updated'];
					$videosDatas['videos'][$i]['uploaded'] = $videosList[$i]['video']['uploaded'];
					$videosDatas['videos'][$i]['category'] = $videosList[$i]['video']['category'];
					$videosDatas['videos'][$i]['tags'] = $videosList[$i]['video']['tags'];
					$videosDatas['videos'][$i]['accessControl'] = $videosList[$i]['video']['accessControl'];
				}
			}
			elseif($playlist==1) {
				
				$videosDatas['playlist_id'] = $content['data']['id'];
				$videosDatas['author'] = $content['data']['author'];
				$videosDatas['title'] = $content['data']['title'];
				$videosDatas['description'] = $content['data']['description'];
				
				for($i=0; $i<count($videosList); $i++) {
					$videosDatas['videos'][$i]['playlist_id'] = $videosList[$i]['id'];
					$videosDatas['videos'][$i]['position'] = $videosList[$i]['position'];
					$videosDatas['videos'][$i]['playlist_author'] = $videosList[$i]['author'];
					
					$videosDatas['videos'][$i]['videoid'] = $videosList[$i]['video']['id'];
					$videosDatas['videos'][$i]['url'] = $videosList[$i]['video']['player']['default'];
					$videosDatas['videos'][$i]['title'] = $videosList[$i]['video']['title'];
					$videosDatas['videos'][$i]['description'] = $videosList[$i]['video']['description'];
					$videosDatas['videos'][$i]['author'] = $videosList[$i]['video']['uploader'];
					$videosDatas['videos'][$i]['thumbnail'] = $videosList[$i]['video']['thumbnail']['sqDefault'];
					$videosDatas['videos'][$i]['duration'] = $videosList[$i]['video']['duration'];
					$videosDatas['videos'][$i]['viewCount'] = $videosList[$i]['video']['viewCount'];
					$videosDatas['videos'][$i]['rating'] = $videosList[$i]['video']['rating'];
					$videosDatas['videos'][$i]['updated'] = $videosList[$i]['video']['updated'];
					$videosDatas['videos'][$i]['uploaded'] = $videosList[$i]['video']['uploaded'];
					$videosDatas['videos'][$i]['category'] = $videosList[$i]['video']['category'];
					$videosDatas['videos'][$i]['tags'] = $videosList[$i]['video']['tags'];
					$videosDatas['videos'][$i]['accessControl'] = $videosList[$i]['video']['accessControl'];
				}
			}
			else {
				for($i=0; $i<count($videosList); $i++) {
					$videosDatas['videos'][$i]['videoid'] = $videosList[$i]['id'];
					$videosDatas['videos'][$i]['url'] = $videosList[$i]['player']['default'];
					$videosDatas['videos'][$i]['title'] = $videosList[$i]['title'];
					$videosDatas['videos'][$i]['description'] = $videosList[$i]['description'];
					$videosDatas['videos'][$i]['author'] = $videosList[$i]['uploader'];
					$videosDatas['videos'][$i]['thumbnail'] = $videosList[$i]['thumbnail']['sqDefault'];
					$videosDatas['videos'][$i]['duration'] = $videosList[$i]['duration'];
					$videosDatas['videos'][$i]['viewCount'] = $videosList[$i]['viewCount'];
					$videosDatas['videos'][$i]['rating'] = $videosList[$i]['rating'];
					$videosDatas['videos'][$i]['updated'] = $videosList[$i]['updated']; //2011-06-11T07:53:07.000Z
					// substr($videosList[$i]['updated'], 0, 10).' '.substr($videosList[$i]['updated'], 11, 8)
					$videosDatas['videos'][$i]['uploaded'] = $videosList[$i]['uploaded']; //2011-06-11T07:53:07.000Z
					$videosDatas['videos'][$i]['category'] = $videosList[$i]['category'];
					$videosDatas['videos'][$i]['tags'] = $videosList[$i]['tags'];
					$videosDatas['videos'][$i]['accessControl'] = $videosList[$i]['accessControl'];
				}
			}
			
		    $videosDatas['stats']['totalResults'] = $content['data']['totalItems'];
		    $videosDatas['stats']['startIndex'] = $content['data']['startIndex'];
		    $videosDatas['stats']['itemsPerPage'] = $content['data']['itemsPerPage'];
		    $videosDatas['stats']['q'] = $q; // searched query
		    $videosDatas['stats']['username'] = $username; // username searched
		    
		    return $videosDatas;
		}
	}
	
	function getDataFromUrl($url) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	function xml2array($contents, $get_attributes=1, $priority = 'tag') { 
	    if(!$contents) return array(); 
	
	    if(!function_exists('xml_parser_create')) { 
	        //print "'xml_parser_create()' function not found!"; 
	        return array(); 
	    } 
	
	    //Get the XML parser of PHP - PHP must have this module for the parser to work 
	    $parser = xml_parser_create(''); 
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss 
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
	    xml_parse_into_struct($parser, trim($contents), $xml_values); 
	    xml_parser_free($parser); 
	
	    if(!$xml_values) return;//Hmm... 
	
	    //Initializations 
	    $xml_array = array(); 
	    $parents = array(); 
	    $opened_tags = array(); 
	    $arr = array(); 
	
	    $current = &$xml_array; //Refference 
	
	    //Go through the tags. 
	    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array 
	    foreach($xml_values as $data) { 
	        unset($attributes,$value);//Remove existing values, or there will be trouble 
	
	        //This command will extract these variables into the foreach scope 
	        // tag(string), type(string), level(int), attributes(array). 
	        extract($data);//We could use the array by itself, but this cooler. 
	
	        $result = array(); 
	        $attributes_data = array(); 
	         
	        if(isset($value)) { 
	            if($priority == 'tag') $result = $value; 
	            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode 
	        } 
	
	        //Set the attributes too. 
	        if(isset($attributes) and $get_attributes) { 
	            foreach($attributes as $attr => $val) { 
	                if($priority == 'tag') $attributes_data[$attr] = $val; 
	                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
	            } 
	        } 
	
	        //See tag status and do the needed. 
	        if($type == "open") {//The starting of the tag '<tag>' 
	            $parent[$level-1] = &$current; 
	            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
	                $current[$tag] = $result; 
	                if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
	                $repeated_tag_index[$tag.'_'.$level] = 1; 
	
	                $current = &$current[$tag]; 
	
	            } else { //There was another element with the same tag name 
	
	                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array 
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
	                    $repeated_tag_index[$tag.'_'.$level]++; 
	                } else {//This section will make the value an array if multiple tags with the same name appear together 
	                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
	                    $repeated_tag_index[$tag.'_'.$level] = 2; 
	                     
	                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
	                        $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
	                        unset($current[$tag.'_attr']); 
	                    } 
	
	                } 
	                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
	                $current = &$current[$tag][$last_item_index]; 
	            } 
	
	        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
	            //See if the key is already taken. 
	            if(!isset($current[$tag])) { //New Key 
	                $current[$tag] = $result; 
	                $repeated_tag_index[$tag.'_'.$level] = 1; 
	                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data; 
	
	            } else { //If taken, put all things inside a list(array) 
	                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 
	
	                    // ...push the new element into that array. 
	                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
	                     
	                    if($priority == 'tag' and $get_attributes and $attributes_data) { 
	                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
	                    } 
	                    $repeated_tag_index[$tag.'_'.$level]++; 
	
	                } else { //If it is not an array... 
	                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
	                    $repeated_tag_index[$tag.'_'.$level] = 1; 
	                    if($priority == 'tag' and $get_attributes) { 
	                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
	                             
	                            $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
	                            unset($current[$tag.'_attr']); 
	                        } 
	                         
	                        if($attributes_data) { 
	                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
	                        } 
	                    } 
	                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken 
	                } 
	            } 
	
	        } elseif($type == 'close') { //End of tag '</tag>' 
	            $current = &$parent[$level-1]; 
	        } 
	    } 
	     
	    return($xml_array); 
	}
}

?>