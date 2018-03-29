<?php
/*
Plugin Name: Yabo-codes
Plugin URI: http://yabo-concept.ch
Description: Various usual codes
Version: 1.0
Author: Julien Ramel
Author URI: http://yabo-concept.ch
*/




add_action('init', 'ls_enable_required_js_in_wordpress');
function ls_enable_required_js_in_wordpress() {
	
	if (!is_admin()) {

		//jQuery 
	   // wp_deregister_script('jquery');
	   // wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js');
	   // wp_enqueue_script('jquery');
		
		// content slider 
		// http://brenelz.com/blog/build-a-content-slider-with-jquery/
	  //  wp_deregister_script('bxslider');
      //  wp_register_script('bxslider',  'http://bxslider.com/sites/default/files/jquery.bxSlider.min.js');
	  //  wp_enqueue_script('bxslider', 'http://bxslider.com/sites/default/files/jquery.bxSlider.min.js');

	}
	
	    wp_register_script('yabo-codes', '/../app/plugins/yabo-codes/js/yabo-codes.js');
	    wp_enqueue_script('yabo-codes');
	
	
}
add_action('init','ls_add_css_scripts');
function ls_add_css_scripts() {
	wp_enqueue_style( 'yabo-codes-contentslider', '/../app/plugins/yabo-codes/css/yabo-codes.css');
}

add_action('wp_footer', 'add_ls_onload' );
function add_ls_onload() {
    ?>
   <script type="text/javascript">
        /***************************************************
                Nivo Slider
        ***************************************************/
        jQuery.noConflict()(function($){
            jQuery(document).ready(function($){
            $(window).load(function() {
				
            });
        });
        });
    </script>
    <?php
}

include_once('shortcodes.php');


?>