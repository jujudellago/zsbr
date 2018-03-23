<style type="text/css">
{literal}
#ImportStatus{
	display:none;
	background:black;
	color:white;
	-moz-border-radius:10px;
	padding:10px;
	height:1.6em;
	vertical-align:center;
}

#ImportStatus a{
	text-decoration:none;
	color:white;
}

#ImportStatus p{
	padding:0px !important;
	margin:0px !important;
}

#ImportStatus a:hover{
	text-decoration:underline;
}

#ImportStatus.AjaxLoading{
	background:black url({/literal}{$CMS_INSTALL_URL}{literal}lib/packages/ImportExport/admin/images/ajax-loader.gif) 50% 50% no-repeat;
}

#ImportMessages{
	height:500px;
	overflow:auto;
	background:#FFEBE8;
	padding:10px;
	font-size:10pt;
	display:none;
	-moz-border-radius:10px;
	margin-top:10px;
	border:1px solid black;
}
#ImportMessages p{
	font-size:10pt;
}
#ImportWhatToDo{
	display:none;
}
{/literal}
</style>
{*
	// The import process is a communication between this client page and the PHP server script
	
	// The input to the server could be any of these:
	// - limit : an integer to say how many imports to do per batch
	// - offset : an integer to say what record to start this current batch on
	// - dupes : 'update' if program should update dupes, 'ignore' or blank if program should ignore them
	// - stop_on_message : a flag to tell the program to return to the client on Duplicate Found messages (to ask for confirmation of what to do)
	
	// The output back to the client an associative array with the following:
	// - result :  'failure' or 'success'
	// - message : HTML Markup of the logger messages
	// - data[code]   : 0 = IMPORT_OK, 1 = IMPORT_ERROR - i.e. bad record, 2 = IMPORT_DUPLICATE - i.e. record exists
	// - data[valid] : number of valid records imported
	// - data[invalid] : number of invalid records encountered
	// - data[duplicates] : number of duplicate records encountered and ignored or updated (depending on client setting)
	
*}
<script type="text/javascript" src="{$CMS_INSTALL_URL}lib/js/mootools-1.2.1-core.js"></script>
<script type="text/javascript" src="{$CMS_INSTALL_URL}lib/js/mootools-1.2-more.js"></script>
<script type="text/javascript">
{literal}
	var ImportRunning = false;
	var ChoiceAction; 
	var _limit = {/literal}{$limit}{literal};
	var _total_Processed = 0;
	var _total_Valid = 0;
	var _total_Invalid = 0;
	var _total_Duplicates = 0;
	var _current_Offset = 0;
	var _number_to_Import = {/literal}{$Count}{literal}
	var _dupes = '';
	var _stop_on_message = true;
	
	function toggleImport(){
		if (!ImportRunning){
			importShow("all");
		}
		ImportRunning = !ImportRunning;
		$('ImportStatus').toggleClass('AjaxLoading');
		if ($('StartButton').value != 'Pause'){
			$('StartButton').value = 'Pause';
			$('ImportStatus').setStyles({'display':'block'});
		}
		else{
			$('StartButton').value = 'Continue';
		}
		
		performImport(_current_Offset);
	}	
	
	function performImport(Offset){
		if (!ImportRunning){
			return;
		}
		
		if (Offset == null || Offset == 'undefined'){
			Offset = 0;
		}
		
		$('ImportCount').innerHTML = _total_Processed;
		
		var parms;
		parms = {};
		parms['package'] = 'ImportExport';
		parms['subject'] = 'import';
		parms['import_package'] = '{/literal}{$ImportPackageName}{literal}';
		parms['import_package_sub'] = '{/literal}{$ImportPackageSub}{literal}';
		parms['limit'] = _limit;
		parms['dupes'] = _dupes;
		parms['stop_on_message'] = _stop_on_message;
		parms['offset'] = Offset;
		
        var req = new Request({
           method: "get",
           url: "{/literal}{$AjaxURL}{literal}",
           data: parms,
           onComplete: function(response) {
               	var json = $H(JSON.decode(response, true));
				if (response && json.get('result') == null){
					alert(response);
				}
				if (json.get('result') == 'failure' && _stop_on_message){
					if (json.get('data')['code'] == 1){ // Failure, pick up on the next record. 
						_current_Offset = json.get('data')['offset'];
						toggleImport();
					}
					else{  // Duplicate found
						if (_stop_on_message){
							$('ImportKickoff').setStyle('display','none');
							toggleImport();
						}
					}
				}
				else{
					_current_Offset = json.get('data')['offset'];
				}
				_total_Valid+= json.get('data')['valid'];
				_total_Invalid+= json.get('data')['invalid'];
				_total_Duplicates+= json.get('data')['duplicates'];
				if (_dupes == 'ignore'){
					_total_Processed = _total_Valid + _total_Invalid + _total_Duplicates;
				}
				else{
					_total_Processed = _total_Valid + _total_Invalid;
				}
				if (_dupes == 'synchronize'){
					_dupes = 'update';					
				}
				$('ImportCountValid').innerHTML = _total_Valid;
				$('ImportCountInvalid').innerHTML = _total_Invalid;
				$('ImportCountDuplicates').innerHTML = _total_Duplicates;
				$('ImportCount').innerHTML = _total_Processed;

				if (json.get('message') != ""){
					$('ImportMessages').setStyle('display','block');
					var p = new Element('div');
					p.set('html',json.get('message'));
					p.injectTop($('ImportMessages'));
					//$('ImportMessages').innerHTML = "<p>" + json.get('message') + "</p>" + $('ImportMessages').innerHTML;
					if (json.get('result') == 'failure' && _stop_on_message && json.get('data')['code'] == 2){ // Duplicate found
						$('DuplicateAction').innerHTML = '<form action="javascript:ImportMakeChoice()" id="ImportMakeChoiceForm"><input type="hidden" id="thisOffset" value="' + json.get('data')['offset'] + '"><input type="hidden" id="potentialOffset" value="' + json.get('data')['next_offset'] + '">' + $('ImportMakeChoiceFormStub').innerHTML + '</form>';
					}
				}

				if (json.get('data')['offset'] > 0){
					performImport(_current_Offset);
				}
				else{
					$('ImportCount').innerHTML = _number_to_Import;
					$('ImportKickoff').setStyle('display','none');
					toggleImport();
					wrapupImport();
				}
				return;
           }
        }).send();
	}
	
	function wrapupImport(){
		var parms;
		parms = {};
		parms['package'] = 'ImportExport';
		parms['subject'] = 'wrapup';
		parms['import_package'] = '{/literal}{$ImportPackageName}{literal}';
		parms['import_package_sub'] = '{/literal}{$ImportPackageSub}{literal}';
		
        var req = new Request({
           method: "get",
           url: "{/literal}{$AjaxURL}{literal}",
           data: parms,
           onComplete: function(response) {
				if (response && json.get('result') == null){
					alert(response);
				}
           }
        }).send();
	}
	
	function ImportMakeChoice(){
		$('ImportKickoff').setStyle('display','block');
		if (ChoiceAction == 'IgnoreAll' || ChoiceAction == 'UpdateAll' || ChoiceAction == 'Synchronize'){
			_stop_on_message = false;
		}
		if (ChoiceAction == 'UpdateAll' || ChoiceAction == 'Synchronize'){
			if (ChoiceAction == 'Synchronize'){
				_dupes = 'synchronize';	
			}
			else{
				_dupes = 'update';	
			}
			// Decrement the Duplicates counter because we're going to reprocess the one we stopped on.
			_total_Duplicates--;
			_current_Offset = $('thisOffset').value;
		}
		else{
			_dupes = 'ignore';
			_current_Offset = $('potentialOffset').value;
		}
		$('DuplicateAction').destroy();
		toggleImport();
	}
	
	function importShow(type){
		if (ImportRunning){
			alert('You must pause the import to apply the filter');
			return;
		}
		switch(type){
		case 'valid':
			$$('.MessageListError').setStyle('display','none');
			$$('.MessageListWarning').setStyle('display','none');
			$$('.MessageListMessage').setStyle('display','block');
			break;
		case 'invalid':
			$$('.MessageListError').setStyle('display','block');
			$$('.MessageListWarning').setStyle('display','none');
			$$('.MessageListMessage').setStyle('display','none');
			break;
		case 'duplicates':
			$$('.MessageListError').setStyle('display','none');
			$$('.MessageListWarning').setStyle('display','block');
			$$('.MessageListMessage').each(function(el){
				if (el.get('html').indexOf('Updated') >= 0){
					el.setStyle('display','block');
				}
				else{
					el.setStyle('display','none');
				}
			});
			break;
		default:
			$$('.MessageListError').setStyle('display','block');
			$$('.MessageListWarning').setStyle('display','block');
			$$('.MessageListMessage').setStyle('display','block');
			break;
		}
	}
	
	function confirmSynchronize(form){
		var str = 'Are you sure you want to synchronize?  This will DELETE all records from the database that are not included in your uploaded file.'+"\n\n"+'This action cannot be undone.';
		if (confirm(str)){
			ChoiceAction="Synchronize";
			form.submit();		
		}
	}
	/*
	(function(){
		// This function is merely to give some satisfaction to the user, to see a number going up...
		if (ImportRunning){ //} && !_stop_on_message){
			if ($('ImportCount').innerHTML.toInt() < _total_Processed + _limit){
				$('ImportCount').innerHTML = $('ImportCount').innerHTML.toInt() + 10; //Math.floor(_limit / 20);
			}
		}
	}).periodical(300);
	*/
{/literal}
</script>
<div style="width:60%;text-align:left;margin-left:auto;margin-right:auto;">
	<div id='ImportKickoff'>
	<p>Ready to Import <strong>{$Count}</strong> records.  Click start to begin.</p>
	<input type='button' id='StartButton' value='Start' onClick='toggleImport();'>
	</div>
	<div id='ImportStatus'>
		<p style="float:left;width:40%"><a href='javascript:importShow("all")'>Processed</a> <span id='ImportCount'>0</span> of {$Count} records.</p>
		<p style='float:right;width:40%;text-align:right;'>
		<a href='javascript:importShow("valid")'>Valid</a>:   <span id='ImportCountValid'>0</span>
		   <a href='javascript:importShow("invalid")'>Invalid</a>: <span id='ImportCountInvalid'>0</span>
		   <a href='javascript:importShow("duplicates")'>Duplicates</a>: <span id='ImportCountDuplicates'>0</span>
		</p>
	</div>
	<div id='ImportWhatToDo'>
		<!-- Stub used in the Javascript above -->
		<form id='ImportMakeChoiceFormStub'>
			Would you like to: 
			<input type='button' value='Ignore Duplicates' name='ImportChoiceIgnoreAll' id='ImportChoiceIgnoreAll' onclick='ChoiceAction="IgnoreAll";this.form.submit();'>
			<input type='button' value='Update Duplicates' name='ImportChoiceUpdateAll' id='ImportChoiceUpdateAll' onclick='ChoiceAction="UpdateAll";this.form.submit();'>
			<input type='button' value='Synchronize' name='ImportChoiceSynchronize' id='ImportChoiceSynchronize' onclick='confirmSynchronize(this.form)'>
		</form>
	</div>
	<div id='ImportMessages'></div>
</div>