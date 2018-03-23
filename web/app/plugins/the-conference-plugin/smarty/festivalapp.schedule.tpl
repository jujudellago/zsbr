{if $NotFound}
	<p>Information for the {$Year} {'Festival'|vocabulary} is coming soon...</p>
{else}
	{if !$ShowListingsArray|is_array and !$ShowListingsArray|is_string}
    	<p>Which schedule do you want to view: 
		{assign var=sep value=''}
		{foreach from=$ScheduleNames key=schType item=ScheduleName}
			{$sep}<a href='{$SchedulePage}type={$schType}{if $smarty.get.id ne ''}&amp;id={$smarty.get.id}{/if}'>{$ScheduleName}</a>
			{assign var=sep value=', '}
		{/foreach}
		</p>
	{else}
		<LINK REL=StyleSheet HREF='{$packageURL}css/schedule.css' TYPE='text/css'>
		<h2 style='text-align:center'><b>{$Year}</b> {$ScheduleNames.$Type}</h2>
		<p style='text-align:center' class="subject-to-change">Subject to change</p>
		{if $ScheduleNames|@count > 1}
			<p class='ScheduleList'>
			{assign var=sep value=''}
			{foreach from=$ScheduleNames key=schType item=ScheduleName}
				{$sep}{if $schType eq $Type}{$ScheduleName}{else}<a href='{$SchedulePage}type={$schType}{if $smarty.get.id ne ''}&amp;id={$smarty.get.id}{/if}'>{$ScheduleName}</a>{/if}
				{assign var=sep value=' - '}
			{/foreach}
			</p>
		{/if}
	    <p class='PrintableList'>
	     <a href='{$packageURL}schedule_printable.php?{$smarty.const.YEAR_PARM}={$Year}&amp;type={$Type}{if $show_listings_style ne ''}&amp;style={$show_listings_style}{/if}'>Printable Version</a> - <a href='{$packageURL}schedule_printable.php?{$smarty.const.YEAR_PARM}={$Year}&amp;type={$Type}&amp;tables=no'>Printable Version (no tables)</a>
	    </p>
		    <table id="schedule-artist-switcher" width=100% cellpadding=10 border=0>
		        <tr><td valign=top width=50%>
		    <p style='margin:0 0 3px 0'><b>Highlight:</b></p>
		    <form method='get' name='HighlightForm' action=''>
				{assign var=INDEX_PAGE_PARM value=$smarty.const.INDEX_PAGE_PARM}
		        <input type='hidden' name='{$smarty.const.INDEX_PAGE_PARM}' value='{$smarty.get.$INDEX_PAGE_PARM}'>
		        <input type='hidden' name={$smarty.const.YEAR_PARM} value='{$Year}'>
		        <input type='hidden' name='type' value='{$Type}'>
		        <input type='hidden' name='subject' value='schedule'>
		        <select name='id' onChange='form.submit();'>
		            <option value="">&lt;Choose the {'Artist'|vocabulary} to highlight their {'Show'|pluralize}&gt;</option>
					{foreach from=$FestivalArtists item=Artist}
		                {if !$Artist->getParameter('ArtistBand')}
		                    <option value='{$Artist->getParameter('ArtistID')}' {if $Artist->getParameter('ArtistID') eq $smarty.get.id}selected{/if}>{$Artist->getParameter('ArtistFullName')}</option>
		                {/if}
		            {/foreach}
		        </select>
		    </form>
		    </td>
		    <td valign=top  width=50%>
			{if $smarty.get.id ne '' or $smarty.get.sid != ''}
				<p style='margin:0 0 3px 0'><b>Legend:</b></p>
		        <table border=1 class='ShowListingTable' style='width:100%;'>
				{if $smarty.get.sid ne ''}
		            <tr><td class='HighlightedShowCell' align='center'><p>Your Selected Show</p></td></tr>
		        {/if}
				{if $smarty.get.id ne '' and $smarty.get.id|array_key_exists:$FestivalArtists	}
					{assign var=FestivalArtistID value=$smarty.get.id}
					{assign var=FestivalArtist value=$FestivalArtists.$FestivalArtistID}
		            <tr><td class='MainActShowCell' align='center'><p>{'Show'|pluralize} with {$FestivalArtist->getParameter('ArtistFullName')}</p></td></tr>
					{if $Year|user__BandMembersHaveGigs:$FestivalArtistID:$Type}
	                	<tr><td class='BandMemberShowCell' align='center'><p>{'Show'|pluralize} with members of {$FestivalArtist->getParameter('ArtistFullName')}</p></td></tr>
					{/if}
				{/if}
				{*
		            if ($BandMembersHaveGigs){
		                echo "<tr><td class='BandMemberShowCell' align='center'><p>{'Show'|pluralize} with members of ".$FestivalArtists[$_GET['id']]->getParameter('ArtistFullName')."</p></td></tr>\n";
		            }
				*}
		        </tr></table>
		    {/if}
		    </td>
		    </tr></table>
		{$SchedulePainter->paintSchedule($ShowListingsArray,'')}
	{/if}
{/if}