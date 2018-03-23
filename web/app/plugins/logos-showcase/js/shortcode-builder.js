function lshowcaseshortcodegenerate() {
	
	var order = document.getElementById('orderby').value;
	var category = document.getElementById('category').value;
	var url = document.getElementById('activeurl').value;
	var style = document.getElementById('style').value;
	var layout = document.getElementById('interface').value;
	var tooltip = document.getElementById('tooltip').value;
	var limit = document.getElementById('limit').value;

	
	var shortcode = document.getElementById('shortcode');
	var php = document.getElementById('phpcode');
	
	shortcode.innerHTML = "[show-logos orderby='"+order+"' category='"+category+"' activeurl='"+url+"' style='"+style+"' interface='"+layout+"' tooltip='"+tooltip+"' limit='"+limit+"']";
	php.innerHTML = "&lt;?php echo build_lshowcase('"+order+"','"+category+"','"+url+"','"+style+"','"+layout+"','"+tooltip+"','"+limit+"'); ?&gt; ";
	
		
	var preview = document.getElementById('preview');
	
	
	
var data = {
		action: 'my_action',
		porder: order,
		pcategory:category,
		purl:url,
		pstyle:style,
		pinterface:layout,
		ptooltip:tooltip,
		plimit: limit,
	};


	
jQuery.post(ajax_object.ajax_url, data, function(response) {
		preview.innerHTML=response;
		checkslider();
		checktooltip();
		
		
	});
	
	if(layout=="hcarousel") {
		var e = document.getElementById('hcarouselhelp');
        e.style.display = 'block';
		}
	else {
		var e = document.getElementById('hcarouselhelp');
        e.style.display = 'none';
	}
	
}



lshowcaseshortcodegenerate();