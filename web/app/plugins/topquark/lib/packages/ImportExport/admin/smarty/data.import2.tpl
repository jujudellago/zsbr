<!-- wide layout -->

{literal}
<style>
.bg1 {
	background-color: #b7cfec;
}

.bg2 {
	background-color: #87addc;
}
</style>
{/literal}
<div id="mainbar">

<div align="center">
	
	<div style="width: 60%">
		<h2>Upload Success</h2>
		<br>
		On this screen, you will match your columns with what the data importer is looking for.
	</div>
	<form method="POST" action="{$thisURL}">
		<br>
		
		{$TotalRecords} records to import.
		<br>
		<input type="submit" name="preview" value="Click to Proceed">
		<input type="submit" name="cancel" value="Cancel">
		<div id="ImportSetupTable">
			{include file='data.import.setuptable.tpl}
		</div>
	
		<br>
		
		{$TotalRecords} records to import.
		<br>
		<input type="submit" name="preview" value="Click to Proceed">
		<input type="submit" name="cancel" value="Cancel">

	</form>
</div>

</div> <!-- end mainbar -->
