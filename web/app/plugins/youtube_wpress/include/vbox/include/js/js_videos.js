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
		thumbnailBox.removeClass('videoPlayBox').addClass('videoPlayBoxStop');
		itemBox.append('<div class="videoPlayer"></div>');
		videoPlayer = jQuery('.videoPlayer', itemBox);
		
		var video_details = jQuery('.video_details', itemBox).html();
		
		displayEmbedVideo(videoPlayer,videoid,type,'100%','350','1',url,video_details);
	}
});

function displayEmbedVideo(dom,videoCode,videoProviderId,width,height,autoplay,url,video_details) {
	jQuery.ajax({
	  type: 'POST',
	  url: Vbox.ajaxurl + 'index.php?q=displayEmbedCode',
	  data: 'videoCode=' + videoCode + '&videoProviderId=' + videoProviderId + '&width=' + width + '&height=' + height 
	  + '&autoplay=' + autoplay + '&url=' + url,
	  success: function(msg){
	  	dom.css('padding-top','10px').css('padding-bottom','5px').html(msg).append(video_details);
	  }
	});
}

function displayVideosList() {
	var videoCriteria = jQuery('body').data('videoCriteria');
	
	jQuery.ajax({
	  type: 'POST',
	  url: Vbox.ajaxurl + 'index.php?q=displayVideosList',
	  data: 'videoCriteria=' + JSON.stringify(videoCriteria),
	  success: function(msg){
	  	jQuery('#'+videoCriteria.dom).html(msg);
	  	if(videoCriteria.display_type=='2') {
	  		//$("a.youtube_vid").fancybox({ 'opacity': true, 'overlayShow': false, 'transitionIn': 'elastic', 'transitionOut': 'elastic' });
	  		
	  		/*
            $("a.youtube_vid").fancybox({
                'opacity': true, 'overlayShow': true, 'transitionIn': 'elastic', 'transitionOut': 'elastic',
                frameWidth:640,
                frameHeight:360
            });
            */
            
			jQuery('.play_youtube').fancybox({
				'titleShow'     : true,
				'titlePosition' : 'inside',
				'autoScale'		: true,
				'width'			: '70%',
				'height'		: '70%',
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic',
				'easingIn'      : 'easeOutBack',
				'easingOut'     : 'easeInBack'
			});
	  	}
	  }
	});
}

jQuery('.vbox_pagination').live('click', function(event) {
	event.preventDefault();
	var pageNumber = jQuery(this).attr('title');
	var videoCriteria = jQuery('body').data('videoCriteria');
	videoCriteria.pageNumber = pageNumber;
	displayVideosList();
});

function openPopup(page, width, height) {
  	window.open(page, "", "scrollbars=yes,menubar=no,toolbar=no,resizable=yes,width="
    + width + ",height=" + height + ",left=" +
	((screen.width - 760)/2) + ",top=" + ((screen.height - 450)/2) );
}

jQuery('.itemBox').live('mouseover mouseout', function(event) {
	if (event.type == 'mouseover') {
		jQuery(this).addClass('itemBoxHover');
	} 
	else {
		jQuery(this).removeClass('itemBoxHover');
	}
});