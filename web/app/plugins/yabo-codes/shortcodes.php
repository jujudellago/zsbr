<?php

// Column shortcodes.
function adventure_one_column( $atts, $content = null ) {
	   return '<div class="one">' . do_shortcode($content) . '</div>';
	}
	add_shortcode('one_column', 'adventure_one_column');

function adventure_one_third( $atts, $content = null ) {
   return '<div class="one-third">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_third', 'adventure_one_third');

function adventure_one_third_last( $atts, $content = null ) {
   return '<div class="one-third last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('one_third_last', 'adventure_one_third_last');


function adventure_one_third_big( $atts, $content = null ) {
   return '<div class="one-third-big">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_third_big', 'adventure_one_third_big');

function adventure_one_third_big_last( $atts, $content = null ) {
   return '<div class="one-third-big last">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_third_big_last', 'adventure_one_third_big_last');


function adventure_two_third( $atts, $content = null ) {
   return '<div class="two-third">' . do_shortcode($content) . '</div>';
}
add_shortcode('two_third', 'adventure_two_third');

function adventure_two_third_last( $atts, $content = null ) {
   return '<div class="two-third last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('two_third_last', 'adventure_two_third_last');

function adventure_one_half( $atts, $content = null ) {
   return '<div class="one-half">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_half', 'adventure_one_half');

function adventure_one_half_last( $atts, $content = null ) {
   return '<div class="one-half last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('one_half_last', 'adventure_one_half_last');

function adventure_one_fourth( $atts, $content = null ) {
   return '<div class="one-fourth">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_fourth', 'adventure_one_fourth');

function adventure_one_fourth_last( $atts, $content = null ) {
   return '<div class="one-fourth last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('one_fourth_last', 'adventure_one_fourth_last');

function adventure_three_fourth( $atts, $content = null ) {
   return '<div class="inner-content">' . do_shortcode($content) . '</div>';
}
add_shortcode('inner_content', 'adventure_three_fourth');

function adventure_three_fourth_last( $atts, $content = null ) {
   return '<div class="inner-content last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('inner_content_last', 'adventure_three_fourth_last');

function adventure_one_fifth( $atts, $content = null ) {
   return '<div class="one-fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_fifth', 'adventure_one_fifth');

function adventure_one_fifth_last( $atts, $content = null ) {
   return '<div class="one-fifth last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('one_fifth_last', 'adventure_one_fifth_last');

function adventure_two_fifth( $atts, $content = null ) {
   return '<div class="two-fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('two_fifth', 'adventure_two_fifth');

function adventure_two_fifth_last( $atts, $content = null ) {
   return '<div class="two-fifth last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('two_fifth_last', 'adventure_two_fifth_last');

function adventure_three_fifth( $atts, $content = null ) {
   return '<div class="three-fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('three_fifth', 'adventure_three_fifth');

function adventure_three_fifth_last( $atts, $content = null ) {
   return '<div class="three-fifth last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('three_fifth_last', 'adventure_three_fifth_last');

function adventure_four_fifth( $atts, $content = null ) {
   return '<div class="four-fifth">' . do_shortcode($content) . '</div>';
}
add_shortcode('four_fifth', 'adventure_four_fifth');

function adventure_four_fifth_last( $atts, $content = null ) {
   return '<div class="four-fifth last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('four_fifth_last', 'adventure_four_fifth_last');

function adventure_one_sixth( $atts, $content = null ) {
   return '<div class="one-sixth">' . do_shortcode($content) . '</div>';
}
add_shortcode('one_sixth', 'adventure_one_sixth');

function adventure_one_sixth_last( $atts, $content = null ) {
   return '<div class="one-sixth last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('one_sixth_last', 'adventure_one_sixth_last');

function adventure_five_sixth( $atts, $content = null ) {
   return '<div class="five-sixth">' . do_shortcode($content) . '</div>';
}
add_shortcode('five_sixth', 'adventure_five_sixth');

function adventure_five_sixth_last( $atts, $content = null ) {
   return '<div class="five-sixth last">' . do_shortcode($content) . '</div><div class="clearboth"></div>';
}
add_shortcode('five_sixth_last', 'adventure_five_sixth_last');

function adventure_horizontal_line( $atts, $content = null ) {
   return '<div class="horizontal-line"></div>';
}
add_shortcode('horizontal_line', 'adventure_horizontal_line');
function adventure_spacer( $atts, $content = null ) {
   return '<div class="horizontal-spacer"></div>';
}
add_shortcode('spacer', 'adventure_spacer');



function adventure_clearboth( $atts, $content = null ) {
   return '<div class="clearboth"></div>';
}
add_shortcode('clearboth', 'adventure_clearboth');

function adventure_simple_notice( $atts, $content = null ) {
   return '<div class="simple-notice">' . do_shortcode($content) . '</div>';
}
add_shortcode('simple_notice', 'adventure_simple_notice');

function adventure_simple_error( $atts, $content = null ) {
   return '<div class="simple-error">' . do_shortcode($content) . '</div>';
}
add_shortcode('simple_error', 'adventure_simple_error');

function adventure_simple_info( $atts, $content = null ) {
   return '<div class="simple-info">' . do_shortcode($content) . '</div>';
}
add_shortcode('simple_info', 'adventure_simple_info');

function adventure_simple_success( $atts, $content = null ) {
   return '<div class="simple-success">' . do_shortcode($content) . '</div>';
}
add_shortcode('simple_success', 'adventure_simple_success');

function adventure_cancel_list( $atts, $content = null ) {
   return '<div class="cancel-list">' . do_shortcode($content) . '</div>';
}
add_shortcode('cancel_list', 'adventure_cancel_list');

function adventure_checklist_list( $atts, $content = null ) {
   return '<div class="checklist-list">' . do_shortcode($content) . '</div>';
}
add_shortcode('checklist_list', 'adventure_checklist_list');
function adventure_wide_checklist_list( $atts, $content = null ) {
   return '<div class="wide-checklist-list">' . do_shortcode($content) . '</div>';
}
add_shortcode('wide_checklist_list', 'adventure_wide_checklist_list');


function adventure_check_list( $atts, $content = null ) {
   return '<div class="check-list">' . do_shortcode($content) . '</div>';
}
add_shortcode('check_list', 'adventure_check_list');

function adventure_round_list( $atts, $content = null ) {
   return '<div class="round-list">' . do_shortcode($content) . '</div>';
}
add_shortcode('round_list', 'adventure_round_list');

function adventure_regular_list( $atts, $content = null ) {
   return '<div class="regular-list">' . do_shortcode($content) . '</div>';
}
add_shortcode('regular_list', 'adventure_regular_list');

function adventure_social_facebook( $atts, $content = null ) {
	$content = do_shortcode($content);
   return '<a class="social-link facebook" href="' . of_get_option('social_link_facebook', '') . '"  title="' . $content . '">' . $content . '</a>';
}
add_shortcode('social_facebook', 'adventure_social_facebook');

function adventure_social_twitter( $atts, $content = null ) {
	$content = do_shortcode($content);
   return '<a class="social-link twitter" href="' . of_get_option('social_link_twitter', '') . '"  title="' . $content . '">' . $content . '</a>';
}
add_shortcode('social_twitter', 'adventure_social_twitter');

function adventure_social_feed( $atts, $content = null ) {
	$content = do_shortcode($content);
   return '<a class="social-link feed" href="' . of_get_option('social_link_feed', '') . '"  title="' . $content . '">' . $content . '</a>';
}
add_shortcode('social_feed', 'adventure_social_feed');

function adventure_contact_phone( $atts, $content = null ) {
   return '<span class="contact phone">' . of_get_option('contact_info_phone', '') . '</span>';
}
add_shortcode('contact_phone', 'adventure_contact_phone');

function adventure_contact_fax( $atts, $content = null ) {
   return '<span class="contact fax">' . of_get_option('contact_info_fax', '') . '</span>';
}
add_shortcode('contact_fax', 'adventure_contact_fax');

function adventure_contact_email( $atts, $content = null ) {
	$content = of_get_option('contact_info_email', '');
   return '<a class="contact phone" href="mailto:' . $content . '"  title="Contact Email">' . $content . '</a>';
}
add_shortcode('contact_email', 'adventure_contact_email');


function adventure_text_align_left( $atts, $content = null ) {
   return '<div class="text-align-left">' . do_shortcode($content) . '</div>';
}
add_shortcode('text_align_left', 'adventure_text_align_left');

function adventure_img_align_left( $atts, $content = null ) {
   return '<img class="img-align-left"' . do_shortcode($content) . 'alt=" " > ' . '</img>';
}
add_shortcode('img_align_left', 'adventure_img_align_left');


function adventure_formatter($content) {
	$new_content = '';

	/* Matches the contents and the open and closing tags */
	$pattern_full = '{(\[raw\].*?\[/raw\])}is';

	/* Matches just the contents */
	$pattern_contents = '{\[raw\](.*?)\[/raw\]}is';

	/* Divide content into pieces */
	$pieces = preg_split($pattern_full, $content, -1, PREG_SPLIT_DELIM_CAPTURE);

	/* Loop over pieces */
	foreach ($pieces as $piece) {
		/* Look for presence of the shortcode */
		if (preg_match($pattern_contents, $piece, $matches)) {

			/* Append to content (no formatting) */
			$new_content .= $matches[1];
		} else {

			/* Format and append to content */
			$new_content .= wptexturize(wpautop($piece));
		}
	}

	return $new_content;
}

// Remove the 2 main auto-formatters
remove_filter('the_content', 'wpautop');
remove_filter('the_content', 'wptexturize');

// Before displaying for viewing, apply this function
add_filter('the_content', 'adventure_formatter', 99);
add_filter('widget_text', 'adventure_formatter', 99);


?>