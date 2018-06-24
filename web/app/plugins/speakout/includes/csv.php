<?php
ini_set('max_execution_time', 180); // extend maximum execution time

// generate CSV file for download
if ( isset( $_REQUEST['csv'] ) && $_REQUEST['csv'] == 'signatures' ) {
	// make sure it executes before headers are sent
	add_action( 'admin_menu', 'dk_speakout_signatures_csv' );
	function dk_speakout_signatures_csv() {
		// check security: ensure user has authority and intention
		if ( ! current_user_can( 'publish_posts' ) ) wp_die( __( 'Insufficient privileges: You need to be an editor to do that.', 'speakout' ) );
		check_admin_referer( 'dk_speakout-download_signatures' );

		include_once( 'class.signature.php' );
		$signatures = new dk_speakout_Signature();

		$petition_id = isset( $_REQUEST['pid'] ) ? $_REQUEST['pid'] : ''; // petition id

		// retrieve signatures from the database
		$csv_data = $signatures->all( $petition_id, 0, 0, 'csv' );

		// display error message if query returns no results
		if ( count( $csv_data ) < 1 ) {
			echo '<h1>' . __( "No signatures found.", "dk_speakout" ) . '</h1>';
			die();
		}

		// construct CSV filename
		$counter = 0;
		foreach ( $csv_data as $file ) {
			if ( $counter < 1 ) {
				$filename_title = stripslashes( str_replace( ' ', '-', $file->title ) );
				$filename_date  = date( 'Y-m-d', strtotime( current_time( 'mysql', 0 ) ) );
				$filename = $filename_title . '_' . $filename_date . '.csv';
			}
			$counter ++;
		}

		// set up CSV file headers
		header( 'Content-Type: text/octet-stream; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: public' ); // supposed to make stuff work over https

		// get the column headers translated
		$firstname      = __( 'First Name', 'speakout' );
		$lastname       = __( 'Last Name', 'speakout' );
		$email          = __( 'Email Address', 'speakout' );
		$street         = __( 'Street Address', 'speakout' );
		$city           = __( 'City', 'speakout' );
		$state          = __( 'State', 'speakout' );
		$postcode       = __( 'Postal Code', 'speakout' );
		$country        = __( 'Country', 'speakout' );
		$date           = __( 'Date Signed', 'speakout' );
		$confirmed      = __( 'Confirmed', 'speakout' );
		$petition_title = __( 'Petition Title', 'speakout' );
		$petitions_id   = __( 'Petition ID', 'speakout' );
		$email_optin    = __( 'Email Opt-in', 'speakout' );
		$custom_message = __( 'Custom Message', 'speakout' );
		$language       = __( 'Language', 'speakout' );
		$IP_address     = __( 'IP Address', 'speakout' );

		// If set, use the custom field label as column header instead of "Custom Field"
		$counter = 0;
		foreach ( $csv_data as $label ) {
			if ( $counter < 1 ) {
				if ( $label->custom_field_label != '' ) {
					$custom_field_label = stripslashes( $label->custom_field_label );
				}
				else {
					$custom_field_label = __( 'Custom Field', 'speakout' );
				}
			}
			$counter ++;
		}

		// construct CSV file header row
		// must use double quotes and separate with tabs
		$csv = "$firstname	$lastname	$email	$street	$city	$state	$postcode	$country	$custom_field_label	$date	$confirmed	$petition_title	$petitions_id	$email_optin	$custom_message	$language	$IP_address";
		$csv .= "\n";

		// construct CSV file data rows
		foreach ( $csv_data as $signature ) {
			// convert the 1, 0, or '' values of confirmed to readable format
			$confirm = $signature->is_confirmed;
			if ( $confirm == 1 ) {
				$confirm = __( 'confirmed', 'speakout' );
			}
			elseif ( $confirm == 0 ) {
				$confirm = __( 'unconfirmed', 'speakout' );
			}
			else {
				$confirm = '...';
			}
			// convert the 1, 0, or '' values of optin to readable format
			$optin = $signature->optin;
			if ( $optin == 1 ) {
				$optin = __( 'yes', 'speakout' );
			}
			elseif ( $optin == 0 ) {
				$optin = __( 'no', 'speakout' );
			}
			else {
				$optin = '...';
			}
			$csv .=  stripslashes( '"' . $signature->first_name . '"	"' . $signature->last_name . '"	"' . $signature->email . '"	"' . $signature->street_address . '"	"' . $signature->city . '"	"' . $signature->state . '"	"' . $signature->postcode . '"	"' . $signature->country . '"	"' . $signature->custom_field . '"	"' . $signature->date . '"	"' . $confirm . '"	"' . $signature->title . '"	"' . $signature->petitions_id . '"	"' . $optin . '"	"' . $signature->custom_message . '"	"' . $signature->language . '"	"' . $signature->IP_address . '"' );
			$csv .= "\n";
		}

		// output CSV file in a UTF-8 format that Excel can understand
		echo chr( 255 ) . chr( 254 ) . mb_convert_encoding( $csv, 'UTF-16LE', 'UTF-8' );
		exit;
	}
}

?>