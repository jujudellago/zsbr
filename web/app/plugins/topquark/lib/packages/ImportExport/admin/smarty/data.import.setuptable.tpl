<script type="text/javascript" src="{$CMS_INSTALL_URL}lib/js/mootools-1.2.1-core.js"></script>
<script type="text/javascript" src="{$CMS_INSTALL_URL}lib/js/mootools-1.2-more.js"></script>
<script type="text/javascript">
{literal}
	function updateFieldChoice(n){
		var parms;
		parms = {};
		parms['package'] = 'ImportExport';
		parms['subject'] = 'update_field_choice';
		parms['field'] = n;  // should be the assigned value
		parms['parameter'] = document.getElementById('field_' + n).value; // should be the key
		
        var req = new Request({
           method: "get",
           url: "{/literal}{$AjaxURL}{literal}",
           data: parms
        }).send();
	}
{/literal}
</script>
{strip}
<p style='font-size:1.2em'>
	<strong>Page: </strong>
	{assign var=Ellipse value='...'}
	{foreach from=$smarty.session.import_page_offsets key=Page item=Offset}
		{assign var=Difference value=$Page+1-$OffsetPage}
		{if $Page <= 2 or $Page+3 >= $smarty.session.import_page_offsets|@count or $Difference|abs <=2}
			{assign var=Ellipse value='...'}
			{if $Page+1 eq $OffsetPage}
				{$Page+1}&nbsp;
			{else}
				<a href='{$thisURL}&amp;offset={$Page+1}'>{$Page+1}</a>&nbsp;
			{/if}
		{else}
			{$Ellipse}
			{assign var=Ellipse value=''}
		{/if}
	{/foreach}
	<br/>
	{if $OffsetPage > 1}
		<a href='{$thisURL}&amp;offset={$OffsetPage-1}'>Previous</a>&nbsp;
	{/if}
	{if $OffsetPage < $smarty.session.import_page_offsets|@count}
		<a href='{$thisURL}&amp;offset={$OffsetPage+1}'>Next</a> 
	{/if}
</p>
{/strip}

<table cellspacing="0" cellpadding="7" >
	<tr>
		<td></td>
	{section name="fieldloop" start=0 loop=$numFields}
		<td class="{cycle values="bg1,bg2"}">
			Field #{$smarty.section.fieldloop.index+1}
		</td>
	{/section}
	</tr>
		<td>
			line #
		</td>
	{section name="field" start=0 loop=$numFields}
		<td class="{cycle values="bg1,bg2"}" valign="top">
			<SELECT name="field[{$smarty.section.field.index}]" id="field_{$smarty.section.field.index}" onchange="javascript:updateFieldChoice({$smarty.section.field.index});">
				<option value="ignore">Ignore Field</option>
				<option value="ignore">----------------</option>
			{foreach from=$fields key=key item=item}
				<option value="{$key}" {if $csvArray.fieldAssign.$key === $smarty.section.field.index}selected{/if}>{$item}</option>
			{/foreach}
			</SELECT>
		</td>
	{/section}
	</tr>
	{* output from file now... *}
                {foreach from=$csvArray.csvRecords key=key item=item}
	<tr>
		<td style="border-right: thin dotted #000000;" valign="top">
			{$key+1}
		</td>
		{section name="field" start=0 loop=$numFields}
			<td style="border-right: thin dotted #000000;padding:5px" valign="top">
				{$item[$smarty.section.field.index]|nl2br}&nbsp;
			</td>
		{/section}
	</tr>
                {/foreach}
                
	<tr style="height: 2px;">
		<td style="border-right: thin dotted #000000; height: 2px;"></td>
		<td colspan="*" bgcolor="#000000" style="height: 2px;"></td>
	</tr>
</table>

