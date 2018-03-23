/*
YouTube
*/

function selectYoutubeFeedType(feed_type, salt) {
	jQuery('.youtube_username_box_'+salt).hide();
	jQuery('.youtube_keywords_box_'+salt).hide();
	jQuery('.youtube_category_box_'+salt).hide();
	jQuery('.youtube_standard_feed_box_'+salt).hide();
	jQuery('.youtube_time_box_'+salt).hide();
	jQuery('.youtube_playlist_box_'+salt).hide();
	
	if(feed_type==1) {
		jQuery('.youtube_username_box_'+salt).show();
	}
	else if(feed_type==2) {
		jQuery('.youtube_keywords_box_'+salt).show();
	}
	else if(feed_type==3) {
		jQuery('.youtube_category_box_'+salt).show();
	}
	else if(feed_type==4) {
		jQuery('.youtube_standard_feed_box_'+salt).show();
		jQuery('.youtube_time_box_'+salt).show();
	}
	else if(feed_type==5) {
		jQuery('.youtube_playlist_box_'+salt).show();
	}
}

jQuery('.feed_type').live('change', function(event) {
	event.preventDefault();
	var id = jQuery(this).val();
	var salt = jQuery(this).attr('data-salt');
	selectYoutubeFeedType(id, salt);
});
