<?php

// contextual help to Add New page
function dk_speakout_help_addnew() {
	$tab_petitions = '
		<p><strong>' . __( "Title", "speakout" ) . '</strong>&mdash;' . __( "Enter the title of your petition, which will appear at the top of the petition form.", "speakout" ) . '</p>
		<p><strong>' . __( "Do not send email (only collect signatures)", "speakout" ) . '</strong>&mdash;' . __( "Use this option if do not wish to send petition emails to a target address.", "speakout" ) . '</p>
		<p><strong>' . __( "Target Email", "speakout" ) . '</strong>&mdash;' . __( "Enter the email address to which the petition will be sent. You may enter multiple email addresses, separated by commas.", "speakout" ) . '</p>
		<p><strong>' . __( "Email Subject", "speakout" ) . '</strong>&mdash;' . __( "Enter the subject of your petition email.", "speakout" ) . '</p>
		<p><strong>' . __( "Greeting", "speakout" ) . '</strong>&mdash;' . __( "Include a greeting to the recipient of your petition, such as \"Dear Sir,\" which will appear as the first line of the email.", "speakout" ) . '</p>
		<p><strong>' . __( "Petition Message", "speakout" ) . '</strong>&mdash;' . __( "Enter the content of your petition email.", "speakout" ) . '</p>
	';
	$tab_twitter_message = '
		<p><strong>' . __( "Twitter Message", "speakout" ) . '</strong>&mdash;' . __( "Enter a prepared tweet that will be presented to users when the Twitter button is clicked.", "speakout" ) . '</p>
	';
	$tab_petition_options = '
		<p><strong>' . __( "Confirm signatures", "speakout" ) . '</strong>&mdash;' . __( "Use this option to cause an email to be sent to the signers of your petition. This email contains a special link must be clicked to confirm the signer's email address. Petition emails will not be sent until the signature is confirmed.", "speakout" ) . '</p>
		<p><strong>' . __( "Allow messages to be edited", "speakout" ) . '</strong>&mdash;' . __( "Check this option to allow signatories to customize the text of their petition email.", "speakout" ) . '</p>
		<p><strong>' . __( "Set signature goal", "speakout" ) . '</strong>&mdash;' . __( "Enter the number of signatures you hope to collect. This number is used to calculate the progress bar display.", "speakout" ) . '</p>
		<p><strong>' . __( "Set expiration date", "speakout" ) . '</strong>&mdash;' . __( "Use this option to stop collecting signatures on a specific date.", "speakout" ) . '</p>
	';
	$tab_display_options = '
		<p><strong>' . __( "Display address fields", "speakout" ) . '</strong>&mdash;' . __( "Select the address fields to display in the petition form.", "speakout" ) . '</p>
		<p><strong>' . __( "Display custom field", "speakout" ) . '</strong>&mdash;' . __( "Add a custom field to the petition form for collecting additional data.", "speakout" ) . '</p>
		<p><strong>' . __( "Display opt-in checkbox", "speakout" ) . '</strong>&mdash;' . __( "Include a checkbox that allows users to consent to receiving further email.", "speakout" ) . '</p>
	';

	// create the tabs
	$screen = get_current_screen();

	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_petition',
		'title'   => __( "Petition", "speakout" ),
		'content' => $tab_petitions
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_twitter_message',
		'title'   => __( "Twitter Message", "speakout" ),
		'content' => $tab_twitter_message
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_petition_options',
		'title'   => __( "Petition Options", "speakout" ),
		'content' => $tab_petition_options
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_display_options',
		'title'   => __( "Display Options", "speakout" ),
		'content' => $tab_display_options
	));
}

// contextual help for Settings page
function dk_speakout_help_settings() {
	$tab_petition_form = '
		<p>' . __( "These settings control the display of the [emailpetition] shortcode and sidebar widget.", "speakout" ) . '</p>
		<p><strong>' . __( "Petition Theme", "speakout" ) . '</strong>&mdash;' . __( "Select a CSS theme that will control the appearance of petition forms.", "speakout" ) . '</p>
		<p><strong>' . __( "Widget Theme", "speakout" ) . '</strong>&mdash;' . __( "Select a CSS theme that will control the appearance of petition widgets.", "speakout" ) . '</p>
		<p><strong>' . __( "Submit Button Text", "speakout" ) . '</strong>&mdash;' . __( "Enter the text that displays in the orange submit button on petition forms.", "speakout" ) . '</p>
		<p><strong>' . __( "Success Message", "speakout" ) . '</strong>&mdash;' . __( "Enter the text that appears when a user successfully signs your petition with a unique email address.", "speakout" ) . '</p>
		<p><strong>' . __( "Share Message", "speakout" ) . '</strong>&mdash;' . __( "Enter the text that appears above the Twitter and Facebook buttons after the petition form has been submitted.", "speakout" ) . '</p>
		<p><strong>' . __( "Expiration Message", "speakout" ) . '</strong>&mdash;' . __( "Enter the text to display in place of the petition form when a petition is past its expiration date.", "speakout" ) . '</p>
		<p><strong>' . __( "Already Signed Message", "speakout" ) . '</strong>&mdash;' . __( "Enter the text to display when a petition is signed using an email address that has already been submitted.", "speakout" ) . '</p>
		<p><strong>' . __( "Opt-in Default", "speakout" ) . '</strong>&mdash;' . __( "Choose whether the opt-in checkbox is checked or unchecked by default.", "speakout" ) . '</p>
		<p><strong>' . __( "Display signature count", "speakout" ) . '</strong>&mdash;' . __( "Choose whether you wish to display the number of signatures that have been collected.", "speakout" ) . '</p>
	';
	$tab_confirmation_emails = '
		<p>' . __( "These settings control the content of the confirmation emails.", "speakout" ) . '</p>
		<p><strong>' . __( "Email From", "speakout" ) . '</strong>&mdash;' . __( "Enter the email address associated with your website. Confirmation emails will be sent from this address.", "speakout" ) . '</p>
		<p><strong>' . __( "Email Subject", "speakout" ) . '</strong>&mdash;' . __( "Enter the subject of the confirmation email.", "speakout" ) . '</p>
		<p><strong>' . __( "Email Message", "speakout" ) . '</strong>&mdash;' . __( "Enter the content of the confirmation email.", "speakout" ) . '</p>
	';
	$tab_signature_list = '
		<p>' . __( "These settings control the display of the [signaturelist] shortcode.", "speakout" ) . '</p>
		<p><strong>' . __( "Title", "speakout" ) . '</strong>&mdash;' . __( "Enter the text that appears above the signature list.", "speakout" ) . '</p>
		<p><strong>' . __( "Theme", "speakout" ) . '</strong>&mdash;' . __( "Select a CSS theme that will control the appearance of signature lists.", "speakout" ) . '</p>
		<p><strong>' . __( "Rows", "speakout" ) . '</strong>&mdash;' . __( "Enter the number of signatures that will be displayed in the signature list.", "speakout" ) . '</p>
		<p><strong>' . __( "Columns", "speakout" ) . '</strong>&mdash;' . __( "Select the columns that will appear in the signature list.", "speakout" ) . '</p>
	';
	$tab_admin_display = '
		<p>' . __( "These settings control the look of the plugin's options pages within the WordPress administrator.", "speakout" ) . '</p>
		<p><strong>' . __( "Petitions table shows", "speakout" ) . '</strong>&mdash;' . __( "Enter the number of rows to display in the \"Email Petitions\" table (default=20)", "speakout" ) . '</p>
		<p><strong>' . __( "Signatures table shows", "speakout" ) . '</strong>&mdash;' . __( "Enter the number of rows to display in the \"Signatures\" table (default=50)", "speakout" ) . '</p>
		<p><strong>' . __( "CSV file includes", "speakout" ) . '</strong>&mdash;' . __( "Select the subset of signatures that will be included in CSV file downloads", "speakout" ) . '</p>
	';

	// create the tabs
	$screen = get_current_screen();

	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_petition_form',
		'title'   => __( "Petition Form", "speakout" ),
		'content' => $tab_petition_form
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_signature_list',
		'title'   => __( "Signature List", "speakout" ),
		'content' => $tab_signature_list
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_confirmation_emails',
		'title'   => __( "Confirmation Emails", "speakout" ),
		'content' => $tab_confirmation_emails
	));
	$screen->add_help_tab( array (
		'id'      => 'dk_speakout_help_admin_display',
		'title'   => __( "Admin Display", "speakout" ),
		'content' => $tab_admin_display
	));
}
?>