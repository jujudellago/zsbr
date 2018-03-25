<?php
	header('Content-type: text/javascript');
	if (!function_exists('wp')){
		require_once( realpath(dirname(__FILE__) . '/../../../../../wp/wp-load.php') );
	}
?>
getAjaxURL = function(){
	return '<?php echo get_bloginfo('url'); ?>/app/plugins/topquark/lib/packages/Common/ajax.php'+'?nocache='+new Date().getTime();
}

getWPAjaxURL = function(){
	return '<?php echo get_bloginfo('wpurl').'/wp-admin/admin-ajax.php'; ?>'+'?nocache='+new Date().getTime();
}

getParameterByName = function( name )
{
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}
