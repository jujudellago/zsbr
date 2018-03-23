<div id="mainbar">

<h1>Import {$ImportType}</h1>

<p>
This system supports importing data from <strong>CSV</strong> files. Your CSV file should have one record per line with field information seperated by commas(,).
</p>
<p>
Popular programs such as Microsoft Excel and <a href="http://www.openoffice.org">Open Office</a> support saving files in Comma-Seperated-Value format.
</p>


<br>

<form enctype="multipart/form-data" action="{$thisURL}" method="POST">

	<input type="hidden" name="MAX_FILE_SIZE" value="{$maxSize}" />
	
	<div style="width:60%;text-align:left;margin-left:auto;margin-right:auto;">
	 Your CSV file:<input name="csvfile" type="file" />
	 &nbsp;&nbsp; <input type="submit" value="Upload" />
	    <br /><input type="checkbox" checked name="ignoreRows" value="true"> Ignore first <select name="ignoreRowsValue">
	        {section name=ignore loop=10 max=10}
	        <option value="{$smarty.section.ignore.index}">{$smarty.section.ignore.index}</option>
	        {/section}
        </select> rows
	        
        <br /><input type="checkbox" checked name="headerRow" value="true"> First (non-ignored) row contains field names
        <br />Encoding: <select name="encoding">
			{foreach from=$encodings key=encoding item=EncodingName}
			<option {if $encoding eq $default_encoding}selected{/if} value="{$encoding}">{$EncodingName}</option>
			{/foreach}
			</select>
	    <br />Delimiter: <select name="delimiter">
			{foreach from=$delimiters key=delimiter item=DelimiterName}
			<option {if $delimiter eq $default_delimiter}selected{/if} value="{$delimiter}">{$DelimiterName}</option>
			{/foreach}
				</select>
		</div>

</form>

{if $messages}
    <div class="msgdisplay">
   	 <div>{$messages}</div>
    </div>
 {/if}
 
	 
 
 </div>
<!-- end mainbar -->
