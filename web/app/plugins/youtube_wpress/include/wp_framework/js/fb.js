
jQuery('#click_me_btn').live('click', function(event) {
	event.preventDefault();
	fb_connect_callback();
});

function fb_connect_callback() {
	jQuery.ajax({
		type: 'POST',
		url: Fb_ypbox.ajaxurl,
		data: 'action='+Fb_ypbox.action+'&method=fb_connect_callback',
		success: function(msg) {
			if(Fb_ypbox.connect_redirect!='') window.location = Fb_ypbox.connect_redirect;
			else window.location.reload(true);
		}
	});
}

/*
START Facebook login logout functionalities
*/

jQuery('#fb_box_fb_login_btn').live('click', function(event) {
	event.preventDefault();
	fb_box_fb_login();
});

jQuery('#fb_box_fb_logout_btn').live('click', function(event) {
	event.preventDefault();
	fb_box_fb_logout();
});

function fb_box_fb_logout() {
	FB.logout(function(response) {
		if(Fb_ypbox.logout_redirect!='') window.location = Fb_ypbox.logout_redirect;
		else window.location.reload(true);
	});
}

function fb_box_fb_login() {
	FB.login(function(response) {
	
	if (jQuery.browser.opera) {
        FB.XD._transport="postmessage";
        FB.XD.PostMessage.init();
	}
	
	if (response.authResponse) {
		if(Fb_ypbox.connect_redirect!='') window.location = Fb_ypbox.connect_redirect;
		else window.location.reload(true);
	}
	else {
	}
	}, {scope:Fb_ypbox.scope});
}

/*
END Facebook login logout functionalities
*/