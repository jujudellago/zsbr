{*Smarty*}{strip}
{counter assign='fu_count'}

{* Assign Defaults *}
{if $fu_url eq ''}
    {assign var=fu_url value=''}
{/if}
{if $fu_base eq ''}
    {assign var=fu_base value='../'}
{/if}
{if $fu_limit_files eq ''}
    {assign var=fu_limit_files value='null'}
{/if}
{if $fu_files eq ''}
    {if $fu_just_images}
        {assign var=fu_files value="'Images (*.jpg, *.jpeg, *.gif, *.png, *.zip)' : '*.jpg; *.jpeg; *.gif; *.png; *.zip'"}  {* {ldelim}\'Images (*.jpg, *.jpeg, *.gif, *.png, *.zip)\' : \'*.jpg; *.jpeg; *.gif; *.png; *.zip\'{rdelim}'} *}
    {else}
        {assign var=fu_files value=''}    
    {/if}
{/if}
{if $fu_fieldname eq ''}
    {assign var=fu_fieldname value='file_upload'}
{/if}
    
{/strip}

{if $fu_count eq 1}
    {* first time through *}
    {if $fu_include_mootools}
    <script type="text/javascript" src="{$fu_base}lib/js/mootools-1.2.1-core.js"></script>
    <script type="text/javascript" src="{$fu_base}lib/js/mootools-1.2-more.js"></script>
    {/if}
	<script type="text/javascript" src="{$fu_base}lib/js/fancyupload/source/Swiff.Uploader.js"></script>
	<script type="text/javascript" src="{$fu_base}lib/js/fancyupload/source/Fx.ProgressBar.js"></script>
	<script type="text/javascript" src="{$fu_base}lib/js/fancyupload/source/FancyUpload2.js"></script>
    <link rel=StyleSheet href="{$fu_base}lib/js/fancyupload/css/fancyupload.css" type="text/css">
{/if}
<script type='text/javascript'>
var swiffy;
window.addEvent('load', function() {ldelim}
    
	swiffy = new FancyUpload2($('fu_status_{$fu_count}'), $('fu_list_{$fu_count}'), {ldelim}
		'url': '{$fu_url}',
		'fieldName': '{$fu_fieldname}',
		'limitFiles': {$fu_limit_files},
		'path': '{$fu_base}lib/js/fancyupload/source/Swiff.Uploader.swf',
		'typeFilter' : {ldelim}{$fu_files}{rdelim},
		'target': 'fu_browse_{$fu_count}'
	{rdelim});
	
	swiffy.addEvent('onAllComplete',function(e){ldelim}
	    {$fu_on_all_complete_extras}
	    {if $fu_redirect_url ne ''}
    	    window.location = '{$fu_redirect_url}';
        {/if}
    {rdelim});
    
	/**
	 * Various interactions
	 */

	$('fu_browse_{$fu_count}').addEvent('mouseenter', function() {ldelim}
	    {$fu_browse_mouse_enter_extras}
	    swiffy.reposition();
	    return false;
	{rdelim});
	
	$('fu_browse_{$fu_count}').addEvent('click', function() {ldelim}
	    {$fu_browse_click_extras}
	    swiffy.browse();
	    return false;
	{rdelim});
	
	$('fu_clear_{$fu_count}').addEvent('click', function(e) {ldelim}
	    {$fu_clear_click_extras}
		swiffy.removeFile();
		return false;
	{rdelim});

	$('fu_upload_{$fu_count}').addEvent('click', function(e) {ldelim}
	    {$fu_upload_click_extras}
		swiffy.upload();
		return false;
	{rdelim});

});	
</script>

<div class="fu_form">
	<fieldset id="fu_fieldset_{$fu_count}" class="fu_fieldset">
		<legend>File Upload</legend>
		<div id="fu_status_{$fu_count}" class="fu_status">
    		<p>
    			<a href="#" id="fu_browse_{$fu_count}" class="fu_browse">Browse Files</a> |
    			<a href="#" id="fu_clear_{$fu_count}" class="fu_clear">Clear List</a> |
    			<a href="#" id="fu_upload_{$fu_count}" class="fu_upload">Upload</a>
    		</p>
    		<div id="fu_progress_{$fu_count}" class="fu_progress">
        		<div>
        			<strong>Overall progress</strong><br />
        			<img src="{$fu_base}lib/js/fancyupload/assets/progress-bar/bar.gif" class="progress overall-progress" />
        		</div>
        		<div>
        			<strong>File Progress</strong><br />
        			<img src="{$fu_base}lib/js/fancyupload/assets/progress-bar/bar.gif" class="progress current-progress" />
        		</div>
        	</div>
    		<div class="current-text"></div>
	    </div>
	</fieldset>

	<ul id="fu_list_{$fu_count}" class="fu_list"></ul>
</div>