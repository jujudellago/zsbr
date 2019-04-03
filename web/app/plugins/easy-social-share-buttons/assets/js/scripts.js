// Easy Social Share Buttons Scripts
var essb = (function (document, window) {

	function getSocialShareCounts (shareUrl, shareElements) {

		// Set up vars
		var ajaxUrl = easy_social_share_buttons_ajax_vars.easy_social_share_buttons_ajax_url,
			data = {
				action: 'essb_get_social_counts',
				url: shareUrl,
				essb_ajax_nonce: easy_social_share_buttons_ajax_vars.easy_social_share_buttons_ajax_nonce
			},
			params = Object.keys(data).map(
	            function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
	        ).join('&'),
	        xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

	    // Send ajax post
	    xhr.open('POST', ajaxUrl);
	    xhr.onreadystatechange = function() {
	        if (xhr.readyState>3 && xhr.status==200) { processShareData(xhr.responseText, shareElements); }
	    };
	    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
	    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	    xhr.send(params);
	}

	function addClass (el, className) {
		if (el.classList) {
			el.classList.add(className);
		} else {
			el.className += ' ' + className;
		}
	}

	function processShareData(res, shareElements) {
		var data = JSON.parse(res);

		for (var i = shareElements.length - 1; i >= 0; i--) {
			appendShareCounts(data, shareElements[i]);
		};
	}

	function appendShareCounts (data, shareGroupEl) {
	    var className = 'ess-social-count--is-ready',
	    	el;

	    el = shareGroupEl.querySelector('.ess-social-count--facebook');

	    if (el !== null) {
	    	el.innerHTML = data.facebook;
	    	addClass(el, className);
	    }

	    el = shareGroupEl.querySelector('.ess-social-count--gplus');

	    if (el !== null) {
	    	el.innerHTML = data.google;
	    	addClass(el, className);
	    }

	    el = shareGroupEl.querySelector('.ess-social-count--pinterest');

	    if (el !== null) {
	    	el.innerHTML = data.pinterest;
	    	addClass(el, className);
	    }
	}

	function init () {
		var elements = document.querySelectorAll('.ess-buttons--count'),
			shareButtonGroups = [];

		// Find all share button groups on page
		Array.prototype.forEach.call(elements, function(el, i){
			var shareUrl = el.getAttribute('data-ess-count-url');

			if (shareButtonGroups.length > 0) {
				for (var k = shareButtonGroups.length - 1; k >= 0; k--) {
					if ( shareUrl === shareButtonGroups[k].url ) {
						shareButtonGroups[k].elements.push(el);
					} else {
						shareButtonGroups.push({
							url: shareUrl,
							elements: [el]
						});
					}
				}
			} else {
				shareButtonGroups.push({
					url: shareUrl,
					elements: [el]
				});
			}
		});

		for (var i = shareButtonGroups.length - 1; i >= 0; i--) {
			getSocialShareCounts(shareButtonGroups[i].url, shareButtonGroups[i].elements);
		};
	}

	// Run code when document is ready
	// in case the document is already rendered
	if (document.readyState != 'loading') init();
	// modern browsers
	else if (document.addEventListener) document.addEventListener('DOMContentLoaded', init);
	// IE <= 8
	else document.attachEvent('onreadystatechange', function(){
	    if (document.readyState == 'complete') init();
	});
}(document, window));