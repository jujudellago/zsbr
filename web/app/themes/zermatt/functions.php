<?php

// enqueue the child theme stylesheet




Function wp_schools_enqueue_scripts() {
	wp_register_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css?s=3116'  );
	wp_enqueue_style( 'childstyle' );
	
	wp_register_script("matchheight", get_stylesheet_directory_uri() ."/js/jquery.matchHeight-min.js",array('jquery'),true);
	wp_enqueue_script('matchheight');
	wp_register_script("zs-scripts", get_stylesheet_directory_uri() ."/js/zs.js" ,array('jquery'),true);
	wp_enqueue_script('zs-scripts');
	
	
	wp_register_script("addevent", "https://addevent.com/libs/atc/1.6.1/atc.min.js");
	wp_enqueue_script('addevent');

	
	
}
add_action( 'wp_enqueue_scripts', 'wp_schools_enqueue_scripts', 11);



/* Add js */
#add_action('wp_enqueue_scripts', 'zs_scripts',12);
#function zs_scripts() {
#
#	wp_enqueue_script("zs-scripts", get_stylesheet_directory_uri() ."/js/zs.js" ,array(),false,true);
#	wp_enqueue_script("matchheight", get_stylesheet_directory_uri() ."/js/jquery.matchHeight-min.js",array(),false,true);
#}



// <!-- noformat on --> and <!-- noformat off --> functions

function newautop($text)
{
	$newtext = "";
	$pos = 0;

	$tags = array('<!-- noformat on -->', '<!-- noformat off -->');
	$status = 0;

	while (!(($newpos = strpos($text, $tags[$status], $pos)) === FALSE))
	{
		$sub = substr($text, $pos, $newpos-$pos);

		if ($status)
			$newtext .= $sub;
		else
			$newtext .= convert_chars(wptexturize(wpautop($sub)));      //Apply both functions (faster)

		$pos = $newpos+strlen($tags[$status]);

		$status = $status?0:1;
	}

	$sub = substr($text, $pos, strlen($text)-$pos);

	if ($status)
		$newtext .= $sub;
	else
		$newtext .= convert_chars(wptexturize(wpautop($sub)));      //Apply both functions (faster)

	//To remove the tags
	$newtext = str_replace($tags[0], "", $newtext);
	$newtext = str_replace($tags[1], "", $newtext);

	return $newtext;
}

function newtexturize($text)
{
	return $text;   
}

function new_convert_chars($text)
{
	return $text;   
}

remove_filter('the_content', 'wpautop');
add_filter('the_content', 'newautop');

remove_filter('the_content', 'wptexturize');
add_filter('the_content', 'newtexturize');

remove_filter('the_content', 'convert_chars');
add_filter('the_content', 'new_convert_chars');