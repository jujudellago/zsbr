//update admin settings forms
jQuery('#wp_framework_update_btn').live('click', function(event) {
	event.preventDefault();
	
	var form = jQuery(this).closest('.wpress_form');
	var saving = jQuery(this).closest('.update_btn_process');
	
	jQuery('.saving_msg').addClass('loading').html('Saving in progress...');
	jQuery('#wp_framework_update_btn').attr("disabled", true);
	
	var id = form.attr('id');
	var data = form.serialize();
	
	//alert(data);
	
	jQuery.ajax({
		type: 'POST',
		url: Wpress_framework.ajaxurl,
		data: 'action='+Wpress_framework.action+'&method=update_settings_form&id='+id+'&'+data,
		success: function(msg) {
			jQuery('#wp_framework_update_btn').removeAttr('disabled');
			if(msg=='') {
				jQuery('.saving_msg').removeClass('loading').html('<font color="green"><b>Saved</b></font>');
				var timer = setInterval(function(){
					jQuery('.saving_msg').html('');
					clearInterval(timer);
				},1000);
			}
			else alert(msg);
		}
	});
	
});

jQuery('#csv_export_btn').live('click', function(event) {
	event.preventDefault();
	
	var tn = jQuery(this).attr('tn');
	var fields = jQuery(this).attr('fields');
	//jQuery('#csv_exported_data').html(fields).show();
	
	jQuery.ajax({
		type: 'POST',
		url: Wpress_framework.ajaxurl,
		data: 'action='+Wpress_framework.action+'&method=export_csv&fields='+fields+'&tn='+tn,
		success: function(msg) {
			jQuery('#csv_exported_data').html(msg).show();
		}
	});
});