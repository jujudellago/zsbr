<?php
/*
Plugin Name: YouTube WPress
Plugin URI: http://yougapi.com/products/wp/youtube-for-wordpress/
Description: Integrate YouTube videos into WordPress (channels, featured videos, videos by categories, YouTube search)
Version: 2.0
Author: Yougapi Technology LLC
Author URI: http://yougapi.com
*/

require_once dirname( __FILE__ ).'/include/wp_framework/wp_framework.php';
require_once dirname( __FILE__ ).'/include/vbox/include/webzone.php';
require_once dirname( __FILE__ ).'/youtube_widget.php';


class Youtube_wpress extends Wp_framework_Youtube_wpress {
	
	function Youtube_wpress() {
		
		//Mandatory
		$settings['plugin_title'] = 'YouTube WPress';
		$settings['menu_title'] = 'YouTube WPress'; //used in menus in the admin
		$settings['plugin_class_name'] = 'Youtube_wpress';
		$settings['plugin_version'] = '2.0';
		
		//Settings page
		$settings['settings_page_link'] = 'youtube-wpress'; //leave empty to not have a settings page
		$settings['plugin_token'] = 'youtube_wpress'; //used to store data - should be unique
		
		//Optional
		$settings['js_files'] = array();
		$settings['css_files'] = array('include/css/style.css', 'include/js/fancybox/jquery.fancybox-1.3.4.css', 
		'include/vbox/include/css/style.css');
		$settings['js_files_admin'] = array('include/js/script.js', 'include/js/json2.js');
		$settings['css_files_admin'] = array();
		
		//Plugin settings
		$options = get_option($settings['plugin_token'].'_youtube_settings');
		$GLOBALS['youtube_wpress']['youtube_settings'] = $options;
		if($GLOBALS['youtube_wpress']['youtube_settings']['player_width']=='') $GLOBALS['youtube_wpress']['youtube_settings']['player_width'] = '100%';
		if($GLOBALS['youtube_wpress']['youtube_settings']['player_height']=='') $GLOBALS['youtube_wpress']['youtube_settings']['player_height'] = '320';
		if($GLOBALS['youtube_wpress']['youtube_settings']['autoplay']=='') $GLOBALS['youtube_wpress']['youtube_settings']['autoplay'] = 0;
		if($GLOBALS['youtube_wpress']['youtube_settings']['nb_display']=='') $GLOBALS['youtube_wpress']['youtube_settings']['nb_display'] = 20;
		if($GLOBALS['youtube_wpress']['youtube_settings']['thumb_width']=='') $GLOBALS['youtube_wpress']['youtube_settings']['thumb_width'] = 120;
		if($GLOBALS['youtube_wpress']['youtube_settings']['nb_display_preview']=='') $GLOBALS['youtube_wpress']['youtube_settings']['nb_display_preview'] = 5;
		
		$options = get_option($settings['plugin_token'].'_fancybox');
		$GLOBALS['youtube_wpress']['fancybox'] = $options;
		
		//Shortcodes
		add_shortcode('youtube_wpress', array(__CLASS__, 'youtube_wpress_shortcode'));
		
		//System settings - Not to be customized
		$settings['plugin_dir_url'] = plugin_dir_url( __FILE__ );
		$settings['dirname'] = dirname(__FILE__);
		$settings['plugin_dir_name'] = plugin_basename(dirname(__FILE__));
		$settings['main_file_name'] = $settings['plugin_dir_name'].'.php'; //used in the admin
		$settings['full_file_path'] = __FILE__;
		
		parent::init(array('settings'=>$settings));
	}
	
	function add_scripts_wp_footer() {
		
	}
	
	/*
	###############
	START shortcode
	*/
	
	function youtube_wpress_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'username' => '',
			'nb_display' => '',
			'featured' => '',
			'feed' => '',
			'time' => '',
			'category' => '',
			'search' => '',
			'q' => '',
			'search_filters' => '',
			'playlist' => '',
		), $atts ) );
		
		$page_number = $_GET['vp'];
		if($page_number=='') $page_number=1;
		
		if($nb_display=='') $nb_display = $GLOBALS['youtube_wpress']['youtube_settings']['nb_display'];
		if($nb_display=='') $nb_display = 20;
		
		if($search=='1') {
			if($_GET['q']!='') $q = $_GET['q'];
			$display = self::display_search(array('q'=>$q, 'search_filters'=>$search_filters, 'page_number'=>$page_number, 'nb_display'=>$nb_display));
		}
		else if($id!='') {
			/*
			$yw1 = new Youtube_wpress();
			$yd1 = new Youtube_wpress_display();
			if($thumbnail=='1') $display = $yd1->display_thumbnail_video(array('id'=>$id, 'width'=>$width, 'height'=>$height, 'thumbnail_type'=>$thumbnail_type, 'link'=>$link, 'hd'=>$hd));
			else $display = $yw1->display_embed_video(array('id'=>$id, 'width'=>$width, 'height'=>$height, 'autoplay'=>$autoplay, 'hd'=>$hd, 'description'=>$description));
			*/
		}
		else if($username!='') {
			$display = self::display_channel(array('username'=>$username, 'page_number'=>$page_number, 'nb_display'=>$nb_display));
		}
		else if($playlist!='') {
			$display = self::display_playlist_videos(array('playlist'=>$playlist, 'page_number'=>$page_number, 'nb_display'=>$nb_display));
		}
		else if($featured==1) {
			$display = self::display_featured_videos(array('feed'=>$feed, 'time'=>$time, 'category'=>$category, 'page_number'=>$page_number, 'nb_display'=>$nb_display));
		}
		//should be at the end => only a category specified
		else if($category!='') {
			$display = self::display_category_videos(array('category'=>$category, 'page_number'=>$page_number, 'nb_display'=>$nb_display));
		}
		return $display;
	}
		
	/*
	END shortcode
	#############
	*/
	
	//Playlists
	function display_playlist_videos($criteria='') {
		$playlist = $criteria['playlist'];
		$page_number = $criteria['page_number'];
		$nb_display = $criteria['nb_display'];
			
		$startIndex = ($page_number*$nb_display)-$nb_display+1;
		$preview = $GLOBALS['youtube_wpress']['youtube_settings']['preview'];
		$nb_display_preview = $GLOBALS['youtube_wpress']['youtube_settings']['nb_display_preview'];
		
		//Display results
		if(is_singular()) {
			$criteria2['id'] = $playlist;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getPlaylistUrl($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url, array('playlist'=>1));
			$fd1 = new Vbox_display_class();
			$d = $fd1->displayVideosListFacebookLikeByVideosDatas($videosData, array('pageNumber'=>$page_number, 'nb_display'=>$nb_display));
		}
		else if($preview==1) {
			$criteria2['id'] = $playlist;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display_preview;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getPlaylistUrl($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url, array('playlist'=>1));
			
			$d = self::display_preview_videos($videosData);
		}
		
		return $d;
	}
	
	//Search
	function display_search($criteria='') {
		$q = $criteria['q'];
		$search_filters = $criteria['search_filters'];
		$page_number = $criteria['page_number'];
		$nb_display = $criteria['nb_display'];
		
		$startIndex = ($page_number*$nb_display)-$nb_display+1;
		$preview = $GLOBALS['youtube_wpress']['youtube_settings']['preview'];
		$nb_display_preview = $GLOBALS['youtube_wpress']['youtube_settings']['nb_display_preview'];
		
		//Display results
		if(is_singular()) {
			if($q!='') {
				$criteria2['q'] = $q;
				$criteria2['start-index'] = $startIndex;
				$criteria2['max-results'] = $nb_display;
				$criteria2['safeSearch'] = $GLOBALS['youtube_wpress']['youtube_settings']['safeSearch'];
				$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
				$v1 = new Youtube_class();
				$url = $v1->getYoutubeSearchVideosFeeds($criteria2);
				$videosData = $v1->returnYoutubeVideosDatasByURL($url);				
			}
			
			if($search_filters==1) {
				$d .= self::display_search_filters(array('q'=>$q));
			}
			
			if($q!='') {
				$fd1 = new Vbox_display_class();
				$d .= $fd1->displayVideosListFacebookLikeByVideosDatas($videosData, array('pageNumber'=>$page_number, 'nb_display'=>$nb_display));
			}
		}
		else if($preview==1) {
			if($search_filters==1) {
				$d .= self::display_search_filters(array('q'=>$q));
			}
			
			if($q!='') {
				$criteria2['q'] = $q;
				$criteria2['start-index'] = $startIndex;
				$criteria2['max-results'] = $nb_display_preview;
				$criteria2['safeSearch'] = $GLOBALS['youtube_wpress']['youtube_settings']['safeSearch'];
				$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
				$v1 = new Youtube_class();
				$url = $v1->getYoutubeSearchVideosFeeds($criteria2);
				$videosData = $v1->returnYoutubeVideosDatasByURL($url);
				
				$d = self::display_preview_videos($videosData);			
			}
		}
		
		return $d;
	}
	
	function display_search_filters($criteria='') {
		if(!is_singular()) {
			$action = 'action="'.get_permalink().'"';
		}
		
		$display .= '<form '.$action.'>';
		$display .= '<input type="text" id="q" name="q" value="'.$criteria['q'].'" style="width:320px;"> ';
		$display .= '<input type="submit" value="Search videos">';
		$display .= '</form><br>';
		
		return $display;
	}
	
	//Categories
	function display_category_videos($criteria='') {
		$category = $criteria['category'];
		$page_number = $criteria['page_number'];
		$nb_display = $criteria['nb_display'];
		
		$startIndex = ($page_number*$nb_display)-$nb_display+1;
		$preview = $GLOBALS['youtube_wpress']['youtube_settings']['preview'];
		$nb_display_preview = $GLOBALS['youtube_wpress']['youtube_settings']['nb_display_preview'];
		
		//Display results
		if(is_singular()) {
			$criteria2['category'] = $category;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getYoutubeVideosByCategory($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url);
			$fd1 = new Vbox_display_class();
			$d = $fd1->displayVideosListFacebookLikeByVideosDatas($videosData, array('pageNumber'=>$page_number, 'nb_display'=>$nb_display));
		}
		else if($preview==1) {
			$criteria2['category'] = $category;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display_preview;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getYoutubeVideosByCategory($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url);
			
			$d = self::display_preview_videos($videosData);
		}
		
		return $d;
	}
	
	//Featured videos
	function display_featured_videos($criteria='') {
		$feed = $criteria['feed'];
		$time = $criteria['time'];
		$category = $criteria['category'];
		$page_number = $criteria['page_number'];
		$nb_display = $criteria['nb_display'];
		
		$startIndex = ($page_number*$nb_display)-$nb_display+1;
		$preview = $GLOBALS['youtube_wpress']['youtube_settings']['preview'];
		$nb_display_preview = $GLOBALS['youtube_wpress']['youtube_settings']['nb_display_preview'];
		
		//Display results
		if(is_singular()) {
			$criteria2['feed'] = $feed;
			$criteria2['time'] = $time;
			$criteria2['category'] = $category;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getYoutubeStandardVideosFeeds($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url);
			$fd1 = new Vbox_display_class();
			$d = $fd1->displayVideosListFacebookLikeByVideosDatas($videosData, array('pageNumber'=>$page_number, 'nb_display'=>$nb_display));
		}
		else if($preview==1) {
			$criteria2['feed'] = $feed;
			$criteria2['time'] = $time;
			$criteria2['category'] = $category;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display_preview;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getYoutubeStandardVideosFeeds($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url);
			
			$d = self::display_preview_videos($videosData);
		}
		
		return $d;
	}
	
	//Channel display
	function display_channel($criteria='') {
		$username = $criteria['username'];
		$page_number = $criteria['page_number'];
		$nb_display = $criteria['nb_display'];
		
		$startIndex = ($page_number*$nb_display)-$nb_display+1;
		$preview = $GLOBALS['youtube_wpress']['youtube_settings']['preview'];
		$nb_display_preview = $GLOBALS['youtube_wpress']['youtube_settings']['nb_display_preview'];
		
		//Display results
		if(is_singular()) {
			$criteria2['username'] = $username;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getYoutubeUsernameVideos($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url);
			
			$fd1 = new Vbox_display_class();
			$d = $fd1->displayVideosListFacebookLikeByVideosDatas($videosData, array('pageNumber'=>$page_number, 'nb_display'=>$nb_display));
		}
		else if($preview==1) {
			$criteria2['username'] = $username;
			$criteria2['start-index'] = $startIndex;
			$criteria2['max-results'] = $nb_display_preview;
			$criteria2['youtube_api_key'] = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
			$v1 = new Youtube_class();
			$url = $v1->getYoutubeUsernameVideos($criteria2);
			$videosData = $v1->returnYoutubeVideosDatasByURL($url);
			
			$d = self::display_preview_videos($videosData);
		}
		
		return $d;
	}
	
	function display_preview_videos($videosData) {
		
		$width = $GLOBALS['youtube_wpress']['youtube_settings']['thumb_width'];
		$height = $GLOBALS['youtube_wpress']['youtube_settings']['thumb_height'];
		
		$style = '';
		if($width!='') $style .= 'width: '.$width.'px';
		if($height!='') $style .= 'height: '.$height.'px';
		
		for($i=0; $i<count($videosData['videos']); $i++) {
			$videoid=$videosData['videos'][$i]['videoid'];
			$thumbnail = "http://img.youtube.com/vi/".$videoid."/0.jpg";
		
		
			$d .= '<span class="ywpress-video-item">';
			$d .= '<a href="http://www.youtube.com/embed/'.$videoid.'?autoplay='.$GLOBALS['youtube_wpress']['fancybox']['autoplay'].'" class="play_youtube iframe" title="'.htmlentities($videosData['videos'][$i]['title']).'">';
			$d .= '<img src="'.$thumbnail.'" style="'.$style.'" alt="'.htmlentities($videosData['videos'][$i]['title']).'">';
			$d .= '</a></span> ';
		}
		
		return $d;
	}
	
	/*
	##############
	START Settings
	*/
	function settings_page_display() {
		$enable_tab = array(''=>'Enabled', 'false'=>'Disabled');
		$yes_no = array('1'=>'Yes', ''=>'No');
		$enable_tab = array(''=>'Enabled', 'false'=>'Disabled');
		$safe_search_tab = array('none'=>'None', 'moderate'=>'Moderate', 'strict'=>'Strict');
		
		$sections['youtube_settings']['title'] = 'YouTube settings';
		$sections['youtube_settings']['form'][] = array('type'=>'text', 'name'=>'youtube_api_key', 'title'=>'<b>YouTube API key</b> <small>(Optional - <a href="https://code.google.com/apis/youtube/dashboard" target="_blank">Get your key</a>)</small>');
		$sections['youtube_settings']['form'][] = array('type'=>'text', 'name'=>'player_width', 'title'=>'<b>Player width</b> (Value number or percent - default: 100%)');
		$sections['youtube_settings']['form'][] = array('type'=>'text', 'name'=>'player_height', 'title'=>'<b>Player height</b> (Value number or percent -  default: 320)');
		$sections['youtube_settings']['form'][] = array('type'=>'radio', 'name'=>'autoplay', 'title'=>'<b>Autoplay video</b>', 'values'=>$yes_no);
		$sections['youtube_settings']['form'][] = array('type'=>'text', 'name'=>'nb_display', 'title'=>'<b>Number of videos to display</b> (20 by default)');
		$sections['youtube_settings']['form'][] = array('type'=>'radio', 'name'=>'preview', 'title'=>'<b>Preview videos in posts lists</b>', 'values'=>$yes_no);
		$sections['youtube_settings']['form'][] = array('type'=>'text', 'name'=>'nb_display_preview', 'title'=>'<b>Number of videos to preview in the posts lists</b> (5 by default)');
		$sections['youtube_settings']['form'][] = array('type'=>'text', 'name'=>'thumb_width', 'title'=>'<b>Thumbnail images width</b> (Value in pixels - ex: 80)');
		$sections['youtube_settings']['form'][] = array('type'=>'text', 'name'=>'thumb_height', 'title'=>'<b>Thumbnail images height</b> (Value in pixels - ex: 80 - leave empty to adjust to a given width)');
		$sections['youtube_settings']['form'][] = array('type'=>'radio', 'name'=>'safeSearch', 'title'=>'<b>Safe search to restrict content</b>', 'values'=>$safe_search_tab);
		
		$sections['fancybox']['title'] = 'Fancybox plugin';
		$sections['fancybox']['form'][] = array('type'=>'radio', 'name'=>'enable_fancybox', 'title'=>'<b>Enable Fancybox:</b> <small>(JS & CSS files)</small>', 'values'=>$enable_tab);
		$sections['fancybox']['form'][] = array('type'=>'text', 'name'=>'fancybox_width', 'title'=>'<b>Fancybox player width</b> (in percent - by default: 70%)');
		$sections['fancybox']['form'][] = array('type'=>'text', 'name'=>'fancybox_height', 'title'=>'<b>Fancybox player height</b> (in percent - by default: 70%)');
		$sections['fancybox']['form'][] = array('type'=>'radio', 'name'=>'autoplay', 'title'=>'<b>Autoplay video</b>', 'values'=>$yes_no);
		
		parent::settings_page_display(array('sections'=>$sections));
		
		echo '<br><div>Created by <a href="http://yougapi.com">Yougapi Technology</a></div>';
		echo 'We provide support to all our buyers ! If you have any question please contact us using the form on our <a href="http://codecanyon.net/user/yougapi?ref=yougapi">profile page here</a>';
	}
	
	/*
	END Settings
	###########
	*/
	
	
	/*
	###############################
	START Plugin Activation Functions
	*/
	
	//Executed on plugin activation
	function on_plugin_activation() {
		
	}
	
	/*
	End plugin activation functions
	###############################
	*/
	
	function ajax_listeners() {
		$method = $_POST['method'];
		
		//default function used with the "fb_connect_callback" JS function
		if($method=='display_embed_player') {
			$videoCode = $_POST['videoCode'];
			$width = $GLOBALS['youtube_wpress']['youtube_settings']['player_width'];
			$height = $GLOBALS['youtube_wpress']['youtube_settings']['player_height'];
			$autoplay = $GLOBALS['youtube_wpress']['youtube_settings']['autoplay'];
			
			//$hd = $GLOBALS['youtube_wpress']['youtube_settings']['hd'];
			//$hd = 1;
			
			$embed = '<iframe width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$videoCode.'?autoplay='.$autoplay.'&modestbranding=1&hd='.$hd.'&iv_load_policy=3" frameborder="0" allowfullscreen></iframe>';
			
			echo $embed;
		}
		
		exit;
	}
}

new Youtube_wpress();

?>