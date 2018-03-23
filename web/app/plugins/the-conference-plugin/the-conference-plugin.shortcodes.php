<?php
add_shortcode('the_conference_lineup','the_conference_lineup');
add_shortcode('the_'.sanitize_title(vocabulary('Festival')).'_lineup','the_conference_lineup');
function the_conference_lineup($atts){
	$defaults = array('year' => '', 'style' => 'list', 'order' => 'default', 'include' => '', 'exclude' => '');
	// options
	// - year -> any year for which you have a conference defined
	// - style -> list (default), expanded, floated
	// - order -> default, alphabetic, random
	// - include -> use this to ONLY include the specified artists (comma separated list of IDs)
	// - exclude -> use this to exclude the specified artists (comma separated list of IDs)
	$atts = shortcode_atts($defaults, $atts);
	extract($atts);
	
	$Bootstrap = Bootstrap::getBootstrap();
	$Package = $Bootstrap->usePackage('FestivalApp');
	$args = array('subject' => 'lineup', 'year' => $year, 'display_style' => $style, 'artist_order' => $order, 'include' => $include, 'exclude' => $exclude);
	include_once(dirname(__FILE__).'/../topquark/lib/Smarty_Instance.class.php');
	$smarty = new Smarty_Instance();
	
	$return = $Package->Paint($args, $smarty);
	return $return;
}

add_shortcode('the_conference_schedule','the_conference_schedule');
add_shortcode('the_'.sanitize_title(vocabulary('Festival')).'_schedule','the_conference_schedule');
function the_conference_schedule($atts){
	$defaults = array('year' => '', 'include_times' => 'true', 'schedule' => '', 'style' => '');
	// options
	// - year -> any year for which you have a conference defined
	// - include_times -> true, false => whether to show the times in the table
	// - style -> default (shows the schedule, one grid for each day of the conference, details (lists the show details, as a list, not as a grid), collapse_days (puts all of the days....), collapse_all (puts the entire...)',
	// - schedule -> ID number of the schedule to show (click on Settings to find ID)
	$atts = shortcode_atts($defaults, $atts);
	extract($atts);
	
	switch($style){
	case 'collapse_days':
		add_filter('schedule_painter_format',create_function('$ShowListingsFormat,$args','return "collapse_days";'),10,2);
		break;
	case 'collapse_all':
		add_filter('schedule_painter_format',create_function('$ShowListingsFormat,$args','
			$SchedulePainter = $args[0];
			$SchedulePainter->setShowExtrasCallback("the_conference_plugin_include_stage_name");
			return "collapse_all";
		'),10,2);
		break;
	}
	
	$Bootstrap = Bootstrap::getBootstrap();
	$Package = $Bootstrap->usePackage('FestivalApp');
	$args = array('subject' => 'schedule', 'year' => $year, 'show_times' => $include_times, 'show_type' => $schedule);
	if ($style == 'details'){
		$args['subject'] = 'show_details';
	}
	elseif($style == 'agenda'){
		$args['subject'] = 'agenda';
	}
	include_once(dirname(__FILE__).'/../topquark/lib/Smarty_Instance.class.php');
	$smarty = new Smarty_Instance();
	$return = $Package->Paint($args, $smarty);
	return $return;
}

function the_conference_plugin_include_stage_name($Show){
	return $Show->getParameter('ShowPrettyStage');
}



?>