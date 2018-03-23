<?php

/*********************************************************
* To Customize, replace the following
*	- the_conference_plugin_ 			-> a unique function prefix
*	- The Conference Plugin 	-> the name of the plugin
* 	- the-conference-plugin 	-> the plugin slug at the mothership
*	- $mothership 	-> the actual mothership
*********************************************************/

// add the admin options page
add_action('admin_menu', 'the_conference_plugin_plugin_admin_add_page');
function the_conference_plugin_plugin_admin_add_page() {
	add_options_page('The Conference Plugin', 'The Conference Plugin', 'manage_options', 'the-conference-plugin', 'the_conference_plugin_plugin_options_page');
}

function the_conference_plugin_option($what = null){
	static $options;
	if (!isset($options)){
		$options = get_option('the_conference_plugin_plugin_options');
	}
	if (isset($what)){
		if (strpos($what,'the_conference_plugin_') === false and isset($options['the_conference_plugin_'.$what])){
			$what = 'the_conference_plugin_'.$what;
		}
		return $options[$what];
	}
	else{
		return $options;
	}
}
// display the admin options page
function the_conference_plugin_plugin_options_page() {
	?>
	<div>
	<h2>The Conference Plugin</h2>
	<form action="options.php" method="post">
	<?php settings_fields('the_conference_plugin_plugin_options'); ?>
	<?php do_settings_sections('the-conference-plugin'); ?>

	<p class="submit">
	<input id="submit" class="button-primary" type="submit" value="Save Changes" name="submit">
	</p>
	</form></div>
<?php
}

// add the admin settings and such
add_action('admin_init', 'the_conference_plugin_plugin_admin_init');
function the_conference_plugin_plugin_admin_init(){
	register_setting( 'the_conference_plugin_plugin_options', 'the_conference_plugin_plugin_options', 'the_conference_plugin_plugin_options_validate' );
	
	add_settings_section('the_conference_plugin_plugin_vocabulary', 'Vocabulary', 'the_conference_plugin_plugin_section_vocabulary', 'the-conference-plugin');
	add_settings_field('the_conference_plugin_vocab_festival', 'Event:', 'the_conference_plugin_plugin_setting_vocab', 'the-conference-plugin', 'the_conference_plugin_plugin_vocabulary','the_conference_plugin_vocab_festival');
	add_settings_field('the_conference_plugin_vocab_show', 'Session:', 'the_conference_plugin_plugin_setting_vocab', 'the-conference-plugin', 'the_conference_plugin_plugin_vocabulary','the_conference_plugin_vocab_show');
	add_settings_field('the_conference_plugin_vocab_artist', 'Person:', 'the_conference_plugin_plugin_setting_vocab', 'the-conference-plugin', 'the_conference_plugin_plugin_vocabulary','the_conference_plugin_vocab_artist');
	add_settings_field('the_conference_plugin_vocab_stage', 'Venue:', 'the_conference_plugin_plugin_setting_vocab', 'the-conference-plugin', 'the_conference_plugin_plugin_vocabulary','the_conference_plugin_vocab_stage');

	add_settings_section('the_conference_plugin_plugin_other', 'Other Settings', 'the_conference_plugin_plugin_section_other', 'the-conference-plugin');
	add_settings_field('the_conference_plugin_enable_cache', 'Enable Cache:', 'the_conference_plugin_plugin_setting_cache', 'the-conference-plugin', 'the_conference_plugin_plugin_other','the_conference_plugin_enable_cache');
}

function the_conference_plugin_plugin_options_validate($input){
	$valid = array(
		'the_conference_plugin_vocab_festival' => '',
		'the_conference_plugin_vocab_show' => '',
		'the_conference_plugin_vocab_artist' => '',
		'the_conference_plugin_vocab_stage' => '',
		'the_conference_plugin_vocab_festival_plural' => '',
		'the_conference_plugin_vocab_show_plural' => '',
		'the_conference_plugin_vocab_artist_plural' => '',
		'the_conference_plugin_vocab_stage_plural' => '',
		'the_conference_plugin_enable_cache' => false
	);
	$input = wp_parse_args($input,apply_filters('the_conference_plugin_valid_options',$valid));
	return $input;
}

function the_conference_plugin_plugin_section_vocabulary(){
	echo '<p>Different events use different vocabulary.  Some have "Speakers" others have "Panelists" and others have "Performers".  Use this section to change the vocabulary used for your events.';
}

function the_conference_plugin_plugin_section_other(){
	
}

function the_conference_plugin_plugin_setting_vocab($what){
	$singular = the_conference_plugin_option($what);
	$plural = the_conference_plugin_option($what.'_plural');
	if ($plural == '' and $singular != ''){
		$plural = pluralize($singular);
	}
	$name = $id = $what;
	$description = '';
	switch($what){
	case 'the_conference_plugin_vocab_festival':	
		$description = 'What do you call your event?  For example, is it a "Conference", a "Festival", a "Tournament"';
		break;
	case 'the_conference_plugin_vocab_show':	
		$description = 'What do you call a thing on your event\'s schedule?  For example, is it a "Session", a "Show", a "Match"';
		break;
	case 'the_conference_plugin_vocab_stage':	
		$description = 'What do you call the place your scheduled items happen at?  For example, is it a "Room", a "Stage", a "Venue"';
		break;
	case 'the_conference_plugin_vocab_artist':	
		$description = 'What do you call someone who is booked at your event?  For example, are they a "Panelist", a "Performer", an "Athlete"';
		break;
	}
	echo "Singular: <input id=\"$id\" name=\"the_conference_plugin_plugin_options[$name]\" type=\"text\" value=\"".esc_attr($singular)."\" /> ";
	echo "Plural: <input id=\"$id\" name=\"the_conference_plugin_plugin_options[{$name}_plural]\" type=\"text\" value=\"".esc_attr($plural)."\" />";
	echo "<br/>";
	echo "<span class=\"description\">$description</span>";
}

function the_conference_plugin_plugin_setting_cache($what){
	$option = the_conference_plugin_option($what);
	$name = $id = $what;
	echo "<input id=\"$id\" name=\"the_conference_plugin_plugin_options[$name]\" type=\"checkbox\" ".($option ? 'checked="checked"' : '')." /><br/>";
}

function vocabulary($what){
	$options = the_conference_plugin_option();
	$option_name = 'the_conference_plugin_vocab_'.strtolower($what);
	if (isset($options[$option_name]) and $options[$option_name] != ''){
		return $options[$option_name];
	}
	else{
		switch($what){
		case 'Festival': return 'Conference';
		case 'Artist': return 'Speaker';
		case 'Show': return 'Session';
		case 'Stage': return 'Venue';
		}
		return $what;
	}
}

function pluralize($what){
	$options = the_conference_plugin_option();
	$option_name = 'the_conference_plugin_vocab_'.strtolower($what).'_plural';
	if (isset($options[$option_name]) and $options[$option_name] != ''){
		return $options[$option_name];
	}
	else{
		return vocabulary($what).'s';
	}
}

?>
