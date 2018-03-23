jQuery('.displayVideoPlayer').live('click', function(event) {
	event.preventDefault();
	var itemBox = jQuery(this).closest('.itemBox');
	var videoPlayer = jQuery('.videoPlayer', itemBox);
	var thumbnailBox = jQuery('.thumbnailBox', itemBox);
	
	if (videoPlayer.length > 0) {
		videoPlayer.remove();
		thumbnailBox.removeClass('videoPlayBoxStop').addClass('videoPlayBox');
	}
	else {
		var videoid = itemBox.attr('id');
		var type = itemBox.attr('type');
		var url = jQuery(this).attr('href');
		
		close_opened_video(videoid, thumbnailBox);
		
		thumbnailBox.removeClass('videoPlayBox').addClass('videoPlayBoxStop');
		itemBox.append('<div class="videoPlayer"></div>');
		videoPlayer = jQuery('.videoPlayer', itemBox);
		var video_details = jQuery('.video_details', itemBox).html();
		display_embed_player(videoPlayer, videoid, video_details);
	}
});

function close_opened_video(videoid, thumbnailBox) {
	var opened_videoid = jQuery('body').data('opened_videoid');
	if(opened_videoid!='' && opened_videoid!=undefined) {
		var toto = jQuery('body').data('thumbnailBox');
		toto.removeClass('videoPlayBoxStop').addClass('videoPlayBox');
		jQuery('.videoPlayer', '#'+opened_videoid).remove();
	}
	jQuery('body').data('opened_videoid', videoid);
	jQuery('body').data('thumbnailBox', thumbnailBox);
}

function display_embed_player(dom, videoid, video_details) {
	jQuery.ajax({
		type: 'POST',
		url: Wpress_framework.ajaxurl,
		data: 'action=listener_Youtube_wpress&method=display_embed_player&videoCode='+videoid+'&video_details='+video_details,
		success: function(msg) {
			dom.css('margin-top','20px').css('margin-bottom','5px').html(msg).append(video_details);
		}
	});
}