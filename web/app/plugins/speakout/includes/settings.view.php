<script type="text/javascript">
// check if jquery is loaded, if not, load.
  if(typeof jQuery == 'undefined'){
        document.write('<script type="text/javascript" src="https://code.jquery.com/jquery-latest.min.js"></'+'script>');
  }

$(document).ready(function() {
   disable_address();
   
       function disable_address(){
           var stat = $('input[value="list"]').is(':checked');
            if(stat){
                $('#sig_city').prop('disabled', 'disabled');
                $('#sig_state').prop('disabled', 'disabled');
                $('#sig_postcode').prop('disabled', 'disabled');
                $('#sig_country').prop('disabled', 'disabled');
                $('#sig_custom').prop('disabled', 'disabled');
                $('#sig_message').prop('disabled', 'disabled');
                $('#sig_date').prop('disabled', 'disabled');
            }
            else{
                $('#sig_city').prop('disabled', false);
                $('#sig_state').prop('disabled', false);
                $('#sig_postcode').prop('disabled', false);
                $('#sig_country').prop('disabled', false);
                $('#sig_custom').prop('disabled', false);
                $('#sig_message').prop('disabled', false);
                $('#sig_date').prop('disabled', false);
            }
       }
   $('input[type=radio]').change(function () { 
       disable_address();
   });
   
   // open or close T and C URL settings
   if ( $( 'input#display-privacypolicy' ).is(":checked") ){
        $( 'div#dk-speakout-privacypolicy-url' ).slideDown();
    }
    else{
        $( 'div#dk-speakout-privacypolicy-url' ).slideUp();
        
    }
    
	$( 'input#display-privacypolicy' ).change( function () {
	    if ( $( this ).is(":checked")) {
			$( 'div#dk-speakout-privacypolicy-url' ).slideDown();
		} else {
			$( 'div#dk-speakout-privacypolicy-url' ).slideUp();
		}
	});

});
</script>
<div class="wrap" id="dk-speakout">

	<div id="icon-dk-speakout" class="icon32"><br /></div>
	<h2><?php _e( 'SpeakOut! Petitions Settings', 'speakout' ); ?></h2>
	<?php if ( $message_update ) echo '<div id="message" class="updated"><p>' . $message_update . '</p></div>'; ?>

	<form action="" method="post" id="dk-speakout-settings">
		<?php wp_nonce_field( $nonce ); ?>
		<input type="hidden" name="action" value="<?php echo $action; ?>" />
		<input type="hidden" name="tab" id="dk-speakout-tab" value="<?php echo $tab; ?>" />

		<ul id="dk-speakout-tabbar">
			<li><a class="dk-speakout-tab-01" rel="dk-speakout-tab-01"><?php _e( 'Petition Form', 'speakout' ); ?></a></li>
			<li><a class="dk-speakout-tab-02" rel="dk-speakout-tab-02"><?php _e( 'Signature List', 'speakout' ); ?></a></li>
			<li><a class="dk-speakout-tab-03" rel="dk-speakout-tab-03"><?php _e( 'Confirmation Emails', 'speakout' ); ?></a></li>
			<li><a class="dk-speakout-tab-04" rel="dk-speakout-tab-04"><?php _e( 'Admin Display', 'speakout' ); ?></a></li>
		</ul>

		<div id="dk-speakout-tab-01" class="dk-speakout-hidden dk-speakout-tabcontent">
			<h3><?php _e( 'Petition Form', 'speakout' ); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Petition Theme', 'speakout' ); ?><br /><span class="description">(shortcode)</span></th>
					<td>
						<label for="theme-default"><input type="radio" name="petition_theme" id="theme-default" value="default" <?php if ( $the_settings->petition_theme == 'default' ) echo 'checked="checked"'; ?> /> <?php _e( 'Default', 'speakout' ); ?></label>
						<label for="petition-theme-basic"><input type="radio" name="petition_theme" id="petition-theme-basic" value="basic" <?php if ( $the_settings->petition_theme == 'basic' ) echo 'checked="checked"'; ?> /> <?php _e( 'Basic', 'speakout' ); ?></label>
						<label for="petition-theme-none"><input type="radio" name="petition_theme" id="petition-theme-none" value="none" <?php if ( $the_settings->petition_theme == 'none' ) echo 'checked="checked"'; ?> /> <?php _e( 'None', 'speakout' ); ?> <span class="description">(<?php _e( 'use', 'speakout' ); ?> petition.css)</span></label>
					</td>
				</tr>
				<th scope="row"><?php _e( 'Widget Theme', 'speakout' ); ?></th>
					<td>
						<label for="widget-theme-default"><input type="radio" name="widget_theme" id="widget-theme-default" value="default" <?php if ( $the_settings->widget_theme == 'default' ) echo 'checked="checked"'; ?> /> <?php _e( 'Default', 'speakout' ); ?></label>
						<label for="widget-theme-none"><input type="radio" name="widget_theme" id="widget-theme-none" value="none" <?php if ( $the_settings->widget_theme == 'none' ) echo 'checked="checked"'; ?> /> <?php _e( 'None', 'speakout' ); ?> <span class="description">(<?php _e( 'use', 'speakout' ); ?> petition-widget.css)</span></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="button_text"><?php _e( 'Submit Button Text', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->button_text ); ?>" name="button_text" id="button_text" type="text" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="success_message"><?php _e( 'Success Message', 'speakout' ); ?></label></th>
					<td>
						<textarea name="success_message" id="success_message" cols="80" rows="2"><?php echo $the_settings->success_message; ?></textarea>
						<br /><strong><?php _e( 'Accepted variables:', 'speakout' ); ?></strong> %first_name% &nbsp; %last_name%
						<br /><strong><?php _e( 'Accepted tags:', 'speakout' ); ?></strong> &lt;a&gt; &lt;em&gt; &lt;strong> &lt;p&gt;
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="share_message"><?php _e( 'Share Message', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->share_message ); ?>" name="share_message" id="share_message" type="text" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="expiration_message"><?php _e( 'Expiration Message', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->expiration_message ); ?>" name="expiration_message" id="expiration_message" type="text" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="already_signed_message"><?php _e( 'Already Signed Message', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->already_signed_message ); ?>" name="already_signed_message" id="already_signed_message" type="text" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Opt-in Default', 'speakout' ); ?></th>
					<td>
						<label for="optin-checked" /><input type="radio" name="optin_default" id="optin-checked" value="checked" <?php if ( $the_settings->optin_default == 'checked' ) echo 'checked="checked"'; ?> /> <?php _e( 'Checked', 'speakout' ); ?></label>
						<label for="optin-unchecked" /><input type="radio" name="optin_default" id="optin-unchecked" value="unchecked" <?php if ( $the_settings->optin_default == 'unchecked' ) echo 'checked="checked"'; ?> /> <?php _e( 'Unchecked', 'speakout' ); ?></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Display signature count', 'speakout' ); ?></th>
					<td>
						<label for="display-count-yes" /><input type="radio" name="display_count" id="display-count-yes" value="1" <?php if ( $the_settings->display_count == '1' ) echo 'checked="checked"'; ?> /> <?php _e( 'Yes', 'speakout' ); ?></label>
						<label for="display-count-no" /><input type="radio" name="display_count" id="display-count-no" value="0" <?php if ( $the_settings->display_count == '0' ) echo 'checked="checked"'; ?> /> <?php _e( 'No', 'speakout' ); ?></label>
					</td>
				</tr>
				                <tr valign="top">
					<th scope="row"><?php _e( 'Display honorific field', 'speakout' ); ?></th>
					<td>
						<label for="display-honorific-yes" /><input type="radio" name="display_honorific" id="display-honorifi-yes" value="enabled" <?php if ( $the_settings->display_honorific == 'enabled' ) echo 'checked="checked"'; ?> /> <?php _e( 'Yes', 'speakout' ); ?></label>
						<label for="display-honorific-no" /><input type="radio" name="display_honorific" id="display-honorific-no" value="disabled" <?php if ( $the_settings->display_honorific == 'disabled' ) echo 'checked="checked"'; ?> /> <?php _e( 'No', 'speakout' ); ?></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Display BCC field', 'speakout' ); ?></th>
					<td>
						<label for="display-bcc-yes" /><input type="radio" name="display_bcc" id="display-bcc-yes" value="enabled" <?php if ( $the_settings->display_bcc == 'enabled' ) echo 'checked="checked"'; ?> /> <?php _e( 'Yes', 'speakout' ); ?></label>
						<label for="display-bcc-no" /><input type="radio" name="display_bcc" id="display-bcc-no" value="disabled" <?php if ( $the_settings->display_bcc == 'disabled' ) echo 'checked="checked"'; ?> /> <?php _e( 'No', 'speakout' ); ?></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Display Privacy Policy accept?', 'speakout' ); ?></th>
					<td>
						<label for="display-privacypolicy" /><input type="checkbox" name="display_privacypolicy" value='enabled' id="display-privacypolicy" <?php if ( $the_settings->display_privacypolicy == 'enabled' ) echo 'checked="checked"'; ?> /> <?php _e( 'Yes', 'speakout' ); ?> (<?php _e( 'GDPR', 'speakout' ); ?>)</label>
						<div id="dk-speakout-privacypolicy-url">Privacy Policy URL: <input type="text"  name="privacypolicy_url" id="privacypolicy-url" class="regular-text" <?php if ( $the_settings->privacypolicy_url > "" )  echo 'value="'. $the_settings->privacypolicy_url . '"';  ?> ></div>
					</td>
				</tr>
			</table>
		</div>

		<div id="dk-speakout-tab-02" class="dk-speakout-hidden dk-speakout-tabcontent">
			<h3><?php _e( 'Signature List', 'speakout' ); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="signaturelist_header"><?php _e( 'Title', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->signaturelist_header ); ?>" name="signaturelist_header" id="signaturelist_header" type="text" class="regular-text" /></td>
				</tr>
				<th scope="row"><?php _e( 'Theme', 'speakout' ); ?></th>
					<td>
						<label for="signaturelist_theme-default"><input type="radio" name="signaturelist_theme" id="signaturelist_theme-default" value="default" <?php if ( $the_settings->signaturelist_theme == 'default' ) echo 'checked="checked"'; ?> /> <?php _e( 'Default', 'speakout' ); ?></label>
						<label for="signaturelist_theme-none"><input type="radio" name="signaturelist_theme" id="signaturelist_theme-none" value="none" <?php if ( $the_settings->signaturelist_theme == 'none' ) echo 'checked="checked"'; ?> /> <?php _e( 'None', 'speakout' ); ?> <span class="description">(<?php _e( 'use', 'speakout' ); ?> petition-signaturelist.css)</span></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="signaturelist_rows"><?php _e( 'Rows', 'speakout' ); ?></span></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->signaturelist_rows ); ?>" name="signaturelist_rows" id="signaturelist_rows" type="text" class="small-text" />  <span class="description"><?php _e( 'leave blank to display all', 'speakout' ); ?></span></td>
				</tr>
                <tr valign="top">
				<th scope="row"><?php _e( 'Display', 'speakout' ); ?></th>
					<td>
						<label for="signaturelist_display-table"><input type="radio" name="signaturelist_display" id="signaturelist_display-table" value="table" <?php if ( $the_settings->signaturelist_display == 'table' ) echo 'checked="checked"'; ?> /> <?php _e( 'table', 'speakout' ); ?></label>
						<label for="signaturelist_display-list"><input type="radio" name="signaturelist_display" id="signaturelist_display-list" value="list" <?php if ( $the_settings->signaturelist_display == 'list' ) echo 'checked="checked"'; ?> /> <?php _e( 'long list (comma separated)', 'speakout' ); ?> </label>
					</td>
				</tr>
                				<tr valign="top">
					<th scope="row"><?php _e( 'Columns', 'speakout' ); ?></th>
					<td>
						<input type="checkbox" id="sig_city" name="sig_city" <?php if ( $the_settings->sig_city == 1 ) echo 'checked="checked"'; ?> /> 
						<label for="sig_city" class="dk-speakout-inline"><?php _e( 'City', 'speakout'); ?></label><br />

						<input type="checkbox" id="sig_state" name="sig_state" <?php if ( $the_settings->sig_state == 1 ) echo 'checked="checked"'; ?> /> 
						<label for="sig_state" class="dk-speakout-inline"><?php _e( 'State / Province', 'speakout'); ?></label><br />

						<input type="checkbox" id="sig_postcode" name="sig_postcode" <?php if ( $the_settings->sig_postcode == 1 ) echo 'checked="checked"'; ?> /> 
						<label for="sig_postcode" class="dk-speakout-inline"><?php _e( 'Postal Code', 'speakout'); ?></label><br />

						<input type="checkbox" id="sig_country" name="sig_country" <?php if ( $the_settings->sig_country == 1 ) echo 'checked="checked"'; ?> /> 
						<label for="sig_country"class="dk-speakout-inline"><?php _e( 'Country', 'speakout'); ?></label><br />
						
						<input type="checkbox" id="sig_custom" name="sig_custom" <?php if ( $the_settings->sig_custom == 1 ) echo 'checked="checked"'; ?> /> 
						<label for="sig_custom" class="dk-speakout-inline"><?php _e( 'Custom Field', 'speakout'); ?></label><br />
                        
                        <input type="checkbox" id="sig_message" name="sig_message" <?php if ( $the_settings->sig_message == 1 ) echo 'checked="checked"'; ?> /> 
						<label for="sig_message" class="dk-speakout-inline"><?php _e( 'Custom Message', 'speakout'); ?></label><br />

						<input type="checkbox" id="sig_date" name="sig_date" <?php if ( $the_settings->sig_date == 1 ) echo 'checked="checked"'; ?> /> 
						<label for="sig_date" class="dk-speakout-inline"><?php _e( 'Date', 'speakout'); ?></label>
					</td>
				</tr>
                <tr valign="top">
					<th scope="row"><?php _e( 'Privacy', 'speakout' ); ?></th>
					<td>
						<label for="signaturelist_privacy-enabled"><input type="radio" name="signaturelist_privacy" id="signaturelist_privacy-enabled" value="enabled" <?php if ( $the_settings->signaturelist_privacy == 'enabled' ) echo 'checked="checked"'; ?> /> <?php _e( 'enabled - only show first letter of surname', 'speakout' ); ?></label>
						<label for="signaturelist_privacy-disabled"><input type="radio" name="signaturelist_privacy" id="signaturelist_privacy-disabled" value="disabled" <?php if ( $the_settings->signaturelist_privacy == 'disabled' ) echo 'checked="checked"'; ?> /> <?php _e( 'disabled', 'speakout' ); ?> </label>
					</td>
				</tr>

			</table>
		</div>

		<div id="dk-speakout-tab-03" class="dk-speakout-hidden dk-speakout-tabcontent">
			<h3><?php _e( 'Confirmation Emails', 'speakout' ); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="confirm_email"><?php _e( 'Email From', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->confirm_email ); ?>" name="confirm_email" id="confirm_email" type="text" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="confirm_subject"><?php _e( 'Email Subject', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->confirm_subject ); ?>" name="confirm_subject" id="confirm_subject" type="text" class="regular-text" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="confirm_message"><?php _e( 'Email Message', 'speakout' ); ?></label></th>
					<td>
						<textarea name="confirm_message" id="confirm_message" cols="80" rows="6"><?php //echo $the_settings->confirm_message; ?><?php echo str_replace("<br />", "", $the_settings->confirm_message); ?></textarea>
						<br /><strong><?php _e( 'Accepted variables:', 'speakout' ); ?></strong> %first_name% &nbsp; %last_name% &nbsp; %petition_title% &nbsp; %confirmation_link%
						<br /><strong><?php _e( 'Accepted tags:', 'speakout' ); ?></strong> &lt;table&gt;, &lt;tr&gt;, &lt;td&gt;, &lt;span&gt;, &lt;hr&gt;, &lt;strong&gt;, &lt;em&gt;
					</td>
				</tr>
			</table>
		</div>

		<div id="dk-speakout-tab-04" class="dk-speakout-hidden dk-speakout-tabcontent">
			<h3><?php _e( 'Admin Display', 'speakout' ); ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="petitions_rows"><?php _e( 'Petitions table shows', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->petitions_rows ); ?>" name="petitions_rows" id="petitions_rows" type="text" class="small-text" /> <?php _e( 'rows - default = 20', 'speakout' ); ?></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="signatures_rows"><?php _e( 'Signatures table shows', 'speakout' ); ?></label></th>
					<td><input value="<?php echo esc_attr( $the_settings->signatures_rows ); ?>" name="signatures_rows" id="signatures_rows" type="text" class="small-text" /> <?php _e( 'rows - default = 50', 'speakout' ); ?></td>
				</tr>
                
				<tr valign="top">
					<th scope="row"><?php _e( 'CSV file includes', 'speakout' ); ?><br /><shortcode></th>
					<td>
						<label for="csv-signatures-all"><input type="radio" name="csv_signatures" id="csv-signatures-all" value="all" <?php if ( $the_settings->csv_signatures == 'all' ) echo 'checked="checked"'; ?> /> <?php _e( 'All signatures', 'speakout' ); ?></label>
						<label for="csv-signatures-single-optin"><input type="radio" name="csv_signatures" id="csv-signatures-single-optin" value="single_optin" <?php if ( $the_settings->csv_signatures == 'single_optin' ) echo 'checked="checked"'; ?> /> <?php _e( 'Only opt-in signatures', 'speakout' ); ?></label>
						<label for="csv-signatures-double-optin"><input type="radio" name="csv_signatures" id="csv-signatures-double-optin" value="double_optin" <?php if ( $the_settings->csv_signatures == 'double_optin' ) echo 'checked="checked"'; ?> /> <?php _e( 'Only double opt-in signatures', 'speakout' ); ?> <span class="description">(<?php _e( 'opt-in + confirmed', 'speakout' ); ?>)</span></label>
					</td>
				</tr>
			</table>
		</div>

		<p><input type="submit" name="submit" value="<?php _e( 'Save Changes' ); ?>" class="button-primary" /></p>

	</form>

</div>