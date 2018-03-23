<?php

class Youtube_wpress_widget extends WP_Widget {
	
	//Register widget
	public function __construct() {
		add_action( 'widgets_init', create_function( '', 'register_widget( "youtube_wpress_widget" );' ) );
		parent::__construct(
	 		'youtube_wpress_widget', // Base ID
			'YouTube WPress', // Name
			array( 'description' => 'YouTube Feeds Widget (YouTube WPress plugin)' ) // Args
		);
	}
	
	function get_youtube_display($criteria=array()) {
		$nb_display = $criteria['nb_display'];
		$thumbnails_width = $criteria['thumbnails_width'];
		$feed_type = $criteria['feed_type'];
		$username = $criteria['username'];
		$playlist = $criteria['playlist'];
		$keywords = $criteria['keywords'];
		$category = $criteria['category'];
		$standard_feed = $criteria['standard_feed'];
		$time = $criteria['time'];
		
		add_action( 'wp_footer', array(__CLASS__, 'enqueue_youtube_js') );
		
		if($nb_display=='') $nb_display=6;
		if($thumbnails_width=='') $thumbnails_width='100px';
		
		$youtube_api_key = $GLOBALS['youtube_wpress']['youtube_settings']['youtube_api_key'];
		
		//Get the videos from the API
		if($feed_type==1) {
			$y1 = new Youtube_class();
			$url = $y1->getYoutubeUsernameVideos(array('username'=>$username, 'max-results'=>$nb_display, 'youtube_api_key'=>$youtube_api_key));
			$videoData = $y1->returnYoutubeVideosDatasByURL($url);
		}
		else if($feed_type==2) {
			$y1 = new Youtube_class();
			$url = $y1->getYoutubeSearchVideosFeeds(array('q'=>$keywords, 'max-results'=>$nb_display, 'youtube_api_key'=>$youtube_api_key));
			$videoData = $y1->returnYoutubeVideosDatasByURL($url);
		}
		else if($feed_type==3) {
			$y1 = new Youtube_class();
			$url = $y1->getYoutubeVideosByCategory(array('category'=>$category, 'max-results'=>$nb_display, 'youtube_api_key'=>$youtube_api_key));
			$videoData = $y1->returnYoutubeVideosDatasByURL($url);
		}
		else if($feed_type==4) {
			$y1 = new Youtube_class();
			$url = $y1->getYoutubeStandardVideosFeeds(array('feed'=>$standard_feed, 'time'=>$time, 'max-results'=>$nb_display, 'youtube_api_key'=>$youtube_api_key));
			$videoData = $y1->returnYoutubeVideosDatasByURL($url);
		}
		else if($feed_type==5) {
			$y1 = new Youtube_class();
			$url = $y1->getPlaylistUrl(array('id'=>$playlist, 'max-results'=>$nb_display+1, 'youtube_api_key'=>$youtube_api_key));
			$videoData = $y1->returnYoutubeVideosDatasByURL($url, array('playlist'=>1));
		}
		
		//Display videos
		$nb_total_videos = $videoData['stats']['totalResults'];
		$videos = $videoData['videos'];	
		
		for($i=0; $i<count($videos); $i++) {
			$videoid = $videos[$i]['videoid'];
			$title = $videos[$i]['title'];
			$description = $videos[$i]['description'];
			//$thumbnail = $videos[$i]['thumbnail'];
			$thumbnail = "http://img.youtube.com/vi/".$videoid."/0.jpg";
			$author = $videos[$i]['author'];
			$duration = $videos[$i]['duration'];
			$viewCount = $videos[$i]['viewCount'];
			
			$autoplay = $GLOBALS['youtube_wpress']['fancybox']['autoplay'];
			
			$d .= '<span class="ywpress-video-item">';
			$d .= '<a href="http://www.youtube.com/embed/'.$videoid.'?autoplay='.$autoplay.'" class="play_youtube iframe" title="'.htmlentities($title).'">';
			$d .= '<img src="'.$thumbnail.'" style="width: '.$thumbnails_width.';" alt="'.htmlentities($title).'">';
			$d .= '</a><br />'.htmlentities($title).'</span>';
		}
		
		return $d;
	}
	
	function enqueue_youtube_js() {
		
		$enable_fancybox = $GLOBALS['youtube_wpress']['fancybox']['enable_fancybox'];
		$fancybox_width = $GLOBALS['youtube_wpress']['fancybox']['fancybox_width'];
		$fancybox_height = $GLOBALS['youtube_wpress']['fancybox']['fancybox_height'];
		if($fancybox_width=='') $fancybox_width = '70%';
		if($fancybox_height=='') $fancybox_height = '70%';
		?>
		
		<script type="text/javascript">
		    jQuery(document).ready(function() {
				jQuery(".play_youtube").fancybox({
					'titleShow'     : true,
					'titlePosition' : 'inside',
					'autoScale'		: true,
					'width'			: '<?php echo $fancybox_width; ?>',
					'height'		: '<?php echo $fancybox_height; ?>',
					'transitionIn'	: 'elastic',
					'transitionOut'	: 'elastic',
					'easingIn'      : 'easeOutBack',
					'easingOut'     : 'easeInBack'
				});
		    });
		</script>
		
		<?php
		if($enable_fancybox=='') {
			wp_register_script('fancybox_js', plugin_dir_url( __FILE__ ).'include/js/fancybox/jquery.fancybox-1.3.4.pack.js', array('jquery'));
			wp_enqueue_script('fancybox_js');
			wp_register_script('easing_js', plugin_dir_url( __FILE__ ).'include/js/fancybox/jquery.easing-1.3.pack.js', array('jquery'));
			wp_enqueue_script('easing_js');			
		}
	}
	
	//Display
	public function widget( $args, $instance ) {
		extract( $args );
		$title = $instance['title'];
		$nb_display = $instance['nb_display'];
		$thumbnails_width = $instance['thumbnails_width'];
		$feed_type = $instance['feed_type'];
		$username = $instance['username'];
		$playlist = $instance['playlist'];
		$keywords = $instance['keywords'];
		$category = $instance['category'];
		$standard_feed = $instance['standard_feed'];
		$time = $instance['time'];
		
		echo $before_widget;
		if ( !empty($title) ) echo $before_title . $title . $after_title;
		
	    $d = self::get_youtube_display(array('nb_display'=>$nb_display, 'thumbnails_width'=>$thumbnails_width, 'feed_type'=>$feed_type, 
	    'username'=>$username, 'playlist'=>$playlist, 'keywords'=>$keywords, 'category'=>$category, 'standard_feed'=>$standard_feed, 'time'=>$time));		
		echo $d;
		
		echo $after_widget;
	}
	
	//Update
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		#$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['title'] = $new_instance['title'] ;
		$instance['nb_display'] = strip_tags( $new_instance['nb_display'] );
		$instance['thumbnails_width'] = strip_tags( $new_instance['thumbnails_width'] );
		$instance['feed_type'] = strip_tags( $new_instance['feed_type'] );
		$instance['username'] = strip_tags( $new_instance['username'] );
		$instance['playlist'] = strip_tags( $new_instance['playlist'] );
		$instance['keywords'] = strip_tags( $new_instance['keywords'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['standard_feed'] = strip_tags( $new_instance['standard_feed'] );
		$instance['time'] = strip_tags( $new_instance['time'] );
		return $instance;
	}
	
	//Form
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) $title = $instance[ 'title' ];
		if ( isset( $instance[ 'nb_display' ] ) ) $nb_display = $instance[ 'nb_display' ];
		else $nb_display = 6;
		if ( isset( $instance[ 'thumbnails_width' ] ) ) $thumbnails_width = $instance[ 'thumbnails_width' ];
		else $thumbnails_width = '100px';
		if ( isset( $instance[ 'feed_type' ] ) ) $feed_type = $instance[ 'feed_type' ];
		if ( isset( $instance[ 'username' ] ) ) $username = $instance[ 'username' ];
		if ( isset( $instance[ 'playlist' ] ) ) $playlist = $instance[ 'playlist' ];
		if ( isset( $instance[ 'keywords' ] ) ) $keywords = $instance[ 'keywords' ];
		if ( isset( $instance[ 'category' ] ) ) $category = $instance[ 'category' ];
		if ( isset( $instance[ 'standard_feed' ] ) ) $standard_feed = $instance[ 'standard_feed' ];
		if ( isset( $instance[ 'time' ] ) ) $time = $instance[ 'time' ];
		
		$salt = rand(999, 9999);
		
		?>
		<script>
		jQuery(document).ready(function(){
			<?php
			if($feed_type=='') $feed_type=999; //to avoid having it empty and break the JS function
			echo 'selectYoutubeFeedType('.$feed_type.', '.$salt.');';
			?>
		});
		</script>
		<?php
		
		$feedTypes = array('1'=>'Channel', '2'=>'Search', '3'=>'Category', '4'=>'Standard feeds', '5'=>'Playlist');
		
		$data_youtube_categories = array("Film"=>"Film & Animation", "Autos"=>"Autos & Vehicles", "Music"=>"Music", "Animals"=>"Pets & Animals", "Sports"=>"Sports", "Travel"=>"Travel & Events", "Games"=>"Gaming", "Comedy"=>"Comedy", "People"=>"People & Blogs", "News"=>"News & Politics", "Entertainment"=>"Entertainment", "Education"=>"Education", "Howto"=>"Howto & Style", "Nonprofit"=>"Nonprofits & Activism", "Tech"=>"Science & Technology");
		
		$datas_youtubeStandardFeeds = array(
		                   		"most_viewed"=>"Most viewed",
								"top_rated"=>"Top rated",
		                   		"top_favorites"	=>"Top favorites",
		                   		"most_popular"=>"Most popular",
		               	   		"most_recent"=>"Most recent",
		               	   		"most_discussed"=>"Most discussed",
		               	   		"recently_featured"=>"Recently featured",
		               	   		);
		               	   		
		$datas_youtubeTimeParameters = array(
								"today"=>"Today",
		                   		"this_week"=>"This week",
		                   		"this_month"=>"This month videos",
								"all_time"=>"All time",
		               	   		);
		?>
		
		<div style="margin-bottom:5px;">
			<label><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</div>
		
		<div style="margin-bottom:5px;">
			<label><?php _e( 'Number of display:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'nb_display' ); ?>" name="<?php echo $this->get_field_name( 'nb_display' ); ?>" type="text" value="<?php echo esc_attr( $nb_display ); ?>" />
		</div>
		
		<div style="margin-bottom:5px;">
			<label><?php _e( 'Thumbnails width: (in pixels - Ex: 100px)' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'thumbnails_width' ); ?>" name="<?php echo $this->get_field_name( 'thumbnails_width' ); ?>" type="text" value="<?php echo esc_attr( $thumbnails_width ); ?>" />
		</div>
		
		<div style="margin-bottom:5px;">
			<label><?php _e( 'Feed type:' ); ?></label>
			<select class="widefat feed_type" id="<?php echo $this->get_field_id( 'feed_type' ); ?>" name="<?php echo $this->get_field_name( 'feed_type' ); ?>" data-salt="<?php echo $salt; ?>" ><option></option>
			<?php
			foreach($feedTypes as $ind=>$value) {
				if($ind==$feed_type) echo '<option value="'.$ind.'" selected>'.$value.'</option>';
				else echo '<option value="'.$ind.'">'.$value.'</option>';
			}
			?>
			</select>
		</div>
		
		<div style="margin-bottom:5px;" class="youtube_username_box_<?php echo $salt; ?>">
			<label><?php _e( 'Username' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
		</div>
		
		<div style="margin-bottom:5px;" class="youtube_playlist_box_<?php echo $salt; ?>">
			<label><?php _e( 'Playlist id' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'playlist' ); ?>" name="<?php echo $this->get_field_name( 'playlist' ); ?>" type="text" value="<?php echo esc_attr( $playlist ); ?>" />
		</div>
		
		<div style="margin-bottom:5px;" class="youtube_keywords_box_<?php echo $salt; ?>">
			<label><?php _e( 'Keywords' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'keywords' ); ?>" name="<?php echo $this->get_field_name( 'keywords' ); ?>" type="text" value="<?php echo esc_attr( $keywords ); ?>" />
		</div>
		
		<div style="margin-bottom:5px;" class="youtube_category_box_<?php echo $salt; ?>">
			<label><?php _e( 'Category:' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" ><option></option>
			<?php
			foreach($data_youtube_categories as $ind=>$value) {
				if($ind==$category) echo '<option value="'.$ind.'" selected>'.$value.'</option>';
				else echo '<option value="'.$ind.'">'.$value.'</option>';
			}
			?>
			</select>
		</div>
		
		<div style="margin-bottom:5px;" class="youtube_standard_feed_box_<?php echo $salt; ?>">
			<label><?php _e( 'Feed:' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'standard_feed' ); ?>" name="<?php echo $this->get_field_name( 'standard_feed' ); ?>" ><option></option>
			<?php
			foreach($datas_youtubeStandardFeeds as $ind=>$value) {
				if($ind==$standard_feed) echo '<option value="'.$ind.'" selected>'.$value.'</option>';
				else echo '<option value="'.$ind.'">'.$value.'</option>';
			}
			?>
			</select>
		</div>
		
		<div style="margin-bottom:5px;" class="youtube_time_box_<?php echo $salt; ?>">
			<label><?php _e( 'Time:' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'time' ); ?>" name="<?php echo $this->get_field_name( 'time' ); ?>" ><option></option>
			<?php
			foreach($datas_youtubeTimeParameters as $ind=>$value) {
				if($ind==$time) echo '<option value="'.$ind.'" selected>'.$value.'</option>';
				else echo '<option value="'.$ind.'">'.$value.'</option>';
			}
			?>
			</select>
		</div>
		
		<?php
	}
}

new Youtube_wpress_widget();

?>