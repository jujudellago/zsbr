<style type="text/css">
{literal}
#ExportStatus{
	display:none;
}
#ExportDownloadLink{
	display:none;
}
{/literal}
</style>
<script type="text/javascript" src="{$CMS_INSTALL_URL}lib/js/mootools-1.2.1-core.js"></script>
<script type="text/javascript" src="{$CMS_INSTALL_URL}lib/js/mootools-1.2-more.js"></script>
<script type="text/javascript">
{literal}
	var ExportRunning;
	var _limit = 100;
	var _total_exported = 0;
	var _number_to_export = {/literal}{$Count}{literal}
	
	function toggleExport(){
		if ($('StartButton').value == 'Start'){
			$('StartButton').value = 'Pause';
			$('ExportStatus').setStyles({'display':'block'});
			ExportRunning = true;
		}
		else{
			$('StartButton').value = 'Start';
			ExportRunning = false;
		}
		
		performExport(_total_exported);
	}	
	
	function performExport(Offset){
		if (!ExportRunning){
			return;
		}
		
		if (Offset == null || Offset == 'undefined'){
			Offset = 0;
		}
		
		$('ExportCount').innerHTML = Offset;
		
		var parms;
		parms = {};
		parms['package'] = 'ImportExport';
		parms['subject'] = 'export';
		parms['export_package'] = '{/literal}{$ExportPackageName}{literal}';
		parms['export_package_sub'] = '{/literal}{$ExportPackageSub}{literal}';
		parms['limit'] = _limit;
		parms['offset'] = Offset;
		
        var req = new Request({
           method: "get",
           url: "{/literal}{$AjaxURL}{literal}",
           data: parms,
           onComplete: function(response) {
               var json = $H(JSON.decode(response, true));
               if (json.get('result') == 'success') {
					if (json.get('message') != null ){
						$('ExportMessages').innerHTML = json.get('message');
					}
					_total_exported+= json.get('data');
					if (_total_exported < _number_to_export){
						performExport(_total_exported);
					}
					else{
						$('ExportCount').innerHTML = _number_to_export;
						$('ExportKickoff').setStyle('display','none');
						$('ExportDownloadLink').setStyle('display','block');
						ExportRunning = false;
					}
               }
               else if (json.get('message') != null ) {
                   alert(json.get('message'));
               }
			else if (true){ // testing
				alert(response);
			}
           }
        }).send();
	}
	
	(function(){
		// This function is merely to give some satisfaction to the user, to see a number going up...
		if (ExportRunning){
			if ($('ExportCount').innerHTML.toInt() < _total_exported + _limit){
				$('ExportCount').innerHTML = $('ExportCount').innerHTML.toInt() + Math.floor(_limit / 20);
				if ($('ExportCount').innerHTML > _number_to_export){
					$('ExportCount').innerHTML = _number_to_export;
				}
			}
		}
	}).periodical(_limit);
{/literal}
</script>
<div style="width:60%;text-align:left;margin-left:auto;margin-right:auto;">
	<div id='ExportKickoff'>
	<p>Ready to export <strong>{$Count}</strong> records.  Click start to begin.</p>
	<input type='button' id='StartButton' value='Start' onClick='toggleExport();'>
	</div>
	<div id='ExportStatus'><p>Exported <span id='ExportCount'>0</span> of {$Count} records.</p></div>
	<div id='ExportDownloadLink'><p>Your file is ready to be downloaded.  Right-click and save-as:</p><p><a href='{$ExportDirectory}{$FileName|rawurlencode}'>{$FileName}</a></p></div>
	<div id='ExportMessages'></div>
</div>