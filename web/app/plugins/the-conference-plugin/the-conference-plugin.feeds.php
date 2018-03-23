<?php

add_action('init','conference_plugin_feeds_init');
function conference_plugin_feeds_init(){
	if (!defined('CONF_PLUGIN_FEED_QUERY_VAR')){
		define('CONF_PLUGIN_FEED_QUERY_VAR','the_feed');
	}
	if (!defined('CONF_PLUGIN_FEED_DATA_VAR')){
		define('CONF_PLUGIN_FEED_DATA_VAR','the_feed_data'); 
	}
	if (!defined('CONF_PLUGIN_FEED_YEAR_VAR')){
		define('CONF_PLUGIN_FEED_YEAR_VAR','the_feed_year'); 
	}
	add_action( 'pre_get_posts', 'conference_plugin_feeds_template_redirect' );	
}

add_filter('option_rewrite_rules','conference_plugin_feeds_rewrite_rules');
function conference_plugin_feeds_rewrite_rules($rules){
	$base = apply_filters('the_conference_plugin_feeds_basepath','the-conference-plugin');
	$feed_rules[$base.'/([^/]*)/'.sanitize_title(pluralize('Artist')).'/?$'] = 'index.php?'.CONF_PLUGIN_FEED_QUERY_VAR.'=on&'.CONF_PLUGIN_FEED_DATA_VAR.'=speakers&'.CONF_PLUGIN_FEED_YEAR_VAR.'=$matches[1]'; // the proposals page for a specific festival
	$feed_rules[$base.'/([^/]*)/'.sanitize_title(pluralize('Show')).'/?$'] = 'index.php?'.CONF_PLUGIN_FEED_QUERY_VAR.'=on&'.CONF_PLUGIN_FEED_DATA_VAR.'=sessions&'.CONF_PLUGIN_FEED_YEAR_VAR.'=$matches[1]'; // the proposals page for a specific festival
	
	// I want the feed rules to appear at the beginning - thereby taking precedence over other rules
	if (!is_array($rules)){
		$rules=[];
	}
	$rules = $feed_rules + $rules;
	
	return $rules;
}

add_filter('query_vars','conference_plugin_feeds_query_vars');
function conference_plugin_feeds_query_vars($query_vars){
	$query_vars[] = CONF_PLUGIN_FEED_QUERY_VAR;
	$query_vars[] = CONF_PLUGIN_FEED_DATA_VAR;
	$query_vars[] = CONF_PLUGIN_FEED_YEAR_VAR;
	return $query_vars;
}

function cpf_make_key($key,$prefix){
	$key = preg_replace('/^'.$prefix.'/','',$key);
	if ($key != 'ID'){
		$key = preg_replace('/([A-Z])/',' $1',$key);
	}
	$key = sanitize_title($key);
	$key = str_replace('u-i-d','uid',$key);
	$key = str_replace('i-d','id',$key);
	return $key;
}
function conference_plugin_feeds_template_redirect(){
	if ('on' == get_query_var(CONF_PLUGIN_FEED_QUERY_VAR) and in_array(get_query_var(CONF_PLUGIN_FEED_DATA_VAR),array('speakers','sessions'))) {
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('FestivalApp');
		$FestivalContainer = new FestivalContainer();
		global $tq_Conference;
		$tq_Conference = $FestivalContainer->getFestival(get_query_var(CONF_PLUGIN_FEED_YEAR_VAR));
		if (!is_a($tq_Conference,'Festival')){
			// Maybe the query_var is a slug.  Let's see if the festival exists de-sluggified
			$AllYears = $FestivalContainer->getAllValues('FestivalYear');
			if (is_array($AllYears)){
				foreach ($AllYears as $TestYear){
					if (get_query_var(CONF_PLUGIN_FEED_YEAR_VAR) == sanitize_title($TestYear)){
						$tq_Conference = $FestivalContainer->getFestival($TestYear);
						break;
					}
				}
			}
		}
		if (!is_a($tq_Conference,'Festival') or $tq_Conference->getParameter('FestivalDoNotPublishFeeds') == '1'){
			// No Conference found, return to allow 404 message
			return;
		}

		switch(get_query_var(CONF_PLUGIN_FEED_DATA_VAR)){
		case 'speakers':
			$root = sanitize_title(pluralize('Artist'));
			$el_root = sanitize_title(vocabulary('Artist'));
			$xml = new SimpleXMLElement('<'.$root.'></'.$root.'>');
			$FestivalArtists = $Package->getPublished('FestivalArtists',$tq_Conference->getParameter('FestivalYear'));
			if (empty($FestivalArtists)){
				// It hasn't been published with the schedules, let's check if it's just been published on it's own
				if ($tq_Conference->getParameter('FestivalLineupIsPublished')){
					$FestivalArtists = $tq_Conference->getAllArtists();
				}
			}
			$FestivalArtistContainer = new FestivalArtistContainer();
			$MediaContainer = new MediaContainer();
			$GalleryImageContainer = new GalleryImageContainer();
			$GalleryContainer = new GalleryContainer();
			$ImageLibrarian = new ImageLibrarian();
			$ImageTypes = $ImageLibrarian->getTypes();
		
			if (empty($FestivalArtists)){
				die('No published information available');
			}
			
			$ignore_parms = apply_filters('conference_plugin_feeds_ignore_speaker_parms',array('default-description','default-long-description','default-program-description','fee','fee-description','accommodation-description','guests','technical-requirements','festival-year','is-active'));

			foreach ($FestivalArtists as $ArtistID => $Artist){
				if (is_a($Artist,'FestivalArtist')){
					$Artist = $Artist->getParameters();
					$Artist['Shows'] = array();
				}
				if (!($Artist['ArtistIsActive'])){
					continue;
				}
				
				$speaker = $xml->addChild($el_root);
				// Grrr....need to get the images.  Why haven't I made this easy yet?!?!
				if ($Artist['ArtistBand']){
		        	$AllMedia = $MediaContainer->getAssociatedMedia(new Artist($Artist['ArtistBand']));
				}
				else{
		        	$AllMedia = $MediaContainer->getAssociatedMedia(new Artist($ArtistID));
				}
		        if (!$AllMedia) $AllMedia = array();

				$Artist['Images'] = array();
		        foreach ($AllMedia as $Media){
		            switch($Media->getParameter('MediaType')){
		            case MEDIA_TYPE_GALLERYIMAGE:
	                    $Image = $GalleryImageContainer->getGalleryImage($Media->getParameter('MediaLocation'));
	                    if ($Image){
							if (!is_a($Gallery,'Gallery') or $Gallery->getParameter('GalleryID') != $Image->getParameter('GalleryID')){
			                    $Gallery = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));
							}
							
							$base = get_bloginfo('wpurl').'/'.GALLERY_IMAGE_DIR.$Gallery->getDirectoryName();
							$i = array();
							foreach ($ImageTypes as $type){
								$i[strtolower($type)] = $base.rawurlencode($Image->getParameter("GalleryImage$type"));
							}
							$Artist['Images'][] = $i;
	                    }
	                    break;
					}
		        }
				
				foreach ($Artist as $key => $value){
					$made_key = cpf_make_key($key,'Artist');
					if (in_array($made_key,$ignore_parms)){
						continue;
					}
					if (!is_array($value)){
						$speaker->$made_key = $value;
					}
					else{
						switch($key){
						case 'Shows':
							// These are the sesssions the speaker is appearing in
							$sessions = $speaker->addChild(sanitize_title(pluralize('Show')));
							$sessions->addAttribute('type','array');
							foreach ($value as $ShowID){
								$sessions->addChild('id',$ShowID);
							}
							break;
						case 'Images':
							// These are the speaker images
							$images = $speaker->addChild('images');
							$images->addAttribute('type','array');
							foreach ($value as $i){
								$image = $images->addChild('image');
								foreach ($i as $type => $url){
									$image->$type = $url;
								}
							}
							break;
						}
					}
				}
				
				do_action_ref_array('conference_plugin_feeds_speaker_data',array(&$speaker,$Artist));
			}
			break;
		case 'sessions':
			$root = sanitize_title(pluralize('Show'));
			$el_root = sanitize_title(vocabulary('Show'));
			$xml = new SimpleXMLElement('<'.$root.'></'.$root.'>');
			$Shows = $Package->getPublished('Shows',$tq_Conference->getParameter('FestivalYear'));
			if (empty($Shows)){
				die('No published information available');
			}
			$ignore_parms = apply_filters('conference_plugin_feeds_ignore_speaker_parms',array('artist-names','location-conjunction','pretty-stage','pretty-day','stage','schedule-uid'));
			add_filter('PrettyDayFormat',$lambda = create_function('$f','return "Y-m-d";')); 
			$days = $tq_Conference->getPrettyDays();

			foreach ($Shows as $ShowID => $Show){
				$Show = $Show->getParameters();
				// A little massaging
				$Show['ShowDate'] = $days[$Show['ShowDay']];
				$Show['ShowVenue'] = $Show['ShowPrettyStage'];
				$Show['ShowDay'] = $Show['ShowPrettyDay'];
				
				$session = $xml->addChild($el_root);
				foreach ($Show as $key => $value){
					$made_key = cpf_make_key($key,'Show');
					if (in_array($made_key,$ignore_parms)){
						continue;
					}
					if (!is_array($value)){
						$session->$made_key = $value;
					}
					else{
						switch($key){
						case 'ShowArtists':
							$speakers = $session->addChild(sanitize_title(pluralize('Artist')));
							foreach ($value as $artist){
								$speaker = $speakers->addChild(sanitize_title(vocabulary('Artist')));
								$speaker->id = $artist->getParameter('ArtistID');
								$key = "full-name";
								$speaker->$key = $artist->getParameter('ArtistFullName');
							}
							break;
						}
					}
				}
				
				do_action_ref_array('conference_plugin_feeds_session_data',array(&$session,$Artist));
			}
			break;
		}
		if (!empty($xml)){
			header('Content-Type: text/xml'); 
			echo $xml->asXML();
		}
		die();
	}
}

?>