<div id="mainbar">

{if $ExistingExports|@count}
<div style="float:right;width:20%;border:1px solid black;padding:5px;margin:10px 5% 0px 0px">
    <h3><u>Existing Exports</u></h3>
    <ul>
		{foreach from=$ExistingExports key=Checksum item=ExistingExport}
		<li><a href='{$ExportDirectory}{$ExistingExport}'>{$ExistingExport}</a> - <a href='{$thisURL}&amp;del={$Checksum}'>delete</a></li>
		{/foreach}
	</ul>
    </ul>
</div>
{/if}
<h1>Export {$ExportType}</h1>

<p>
This system supports exporting data to <strong>CSV</strong> (Comma Separated Values) files. You will be able to open your CSV file in popular programs such as Microsoft Excel and <a href="http://www.openoffice.org">Open Office</a>.
</p>




<form action="" method="POST">

	<div style="width:60%;text-align:left;margin-left:auto;margin-right:auto;">
		<table cellpadding="10" border="1">
		{assign var='FirstPass' value='true'} 
		{foreach from=$FilterParms key=Parm item=Values}
			{if $FirstPass eq 'true'}
				<tr><td colspan="2"><h2>Filters:</h2><p>Optionally set values here to limit which data you export</p></td></tr>
			{/if}
			{assign var='FirstPass' value='false'} 
			<tr>
				<th>{$Exporter->getPrettyName($Parm)}:</th>
				<td>
				<select name='{$Parm}'>
					<option value=''>&lt;Choose Value&gt;</option>
					{foreach from=$Values item=Value}
					<option value='{$Value|escape}' {if $smarty.get.$Parm eq $Value}selected{/if}>{$Value}</option>
					{/foreach}
				</select>
				</td>
			</tr>
		{/foreach}

		<tr><td colspan="2"><h2>Options:</h2></td></tr>
		<tr>
			<th>Filename:</th>
			<td><input name="file_name" type="text" value="{$ExportType}_{'Y-d-m'|date}" /></td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td><input type="checkbox" checked name="headerRow" value="true"> First row contains field names
		</tr>
		<tr>
			<th>Encoding:</th>
			<td>
				<select name="encoding">
				{foreach from=$encodings key=encoding item=EncodingName}
				<option {if $encoding eq $default_encoding}selected{/if} value="{$encoding}">{$EncodingName}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>Delimiter:</th>
			<td>
				<select name="delimiter">
				{foreach from=$delimiters key=delimiter item=DelimiterName}
				<option {if $delimiter eq $default_delimiter}selected{/if} value="{$delimiter}">{$DelimiterName}</option>
				{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>&nbsp;</th>
			<td><input type="submit" name="Export" value="Export" /></td>
		</tr>
		</table>
		</div>

</form>

{if $messages}
    <div class="msgdisplay">
   	 <div>{$messages}</div>
    </div>
 {/if}
 
	 
 
 </div>
<!-- end mainbar -->
