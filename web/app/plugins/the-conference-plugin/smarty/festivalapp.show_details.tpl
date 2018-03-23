[raw]

	<div id="all-show-details" >
{assign var='oldday' value=''}
{assign var='cntr' value='0'}

{foreach from=$Shows key=ShowID item=Show} 
 	{if $Show->getParameter('ShowType') ne "Concert" and $Show->getParameter('ShowTitle') ne "Concert" and $Show->getParameter('ShowRepeatOfShowID') eq ""}
	
		{if $Show->getParameter('ShowPrettyDay') ne $oldday}
		
		
			{if $oldday eq ''}
			
			{else}
				{assign var='cntr' value=$cntr+1}
					</tbody>
					</table>
					</div>
			{/if}
			<div id="program_table_container-{$cntr}">
			<table class="program_table" cellspacing="0" cellpadding="2">
				<tbody>				
					<tr>
								<th colspan="3"><h3>{$Show->getParameter('ShowSponsor')}</h3>
									{$Show->getParameter('ShowNotesToArtist')}
									</th>
							</tr>
		{/if}
		{if $ShowID eq $smarty.get.sid}
						<tr class="highlight_row">
					{else}
					<tr>
				            {/if}
			<td class="program_time"> {$Show->getParameter('ShowPrettyStartTime24')} - {$Show->getParameter('ShowPrettyEndTime24')}</td>
			<td class="program_description"><a name='{$Show->getParameter('ShowID')}'></a><strong>{$Show->getParameter('ShowTitle')}</strong>{if $Show->getParameter('ShowRepeats')|is_array}{foreach from=$Show->getParameter('ShowRepeats') item=tmpShowID}	{assign var=tmpShow value=$Shows.$tmpShowID}              <br />{$tmpShow->getParameter('ShowPrettyDay')} at {$tmpShow->getParameter('ShowPrettyStartTime')} {$tmpShow->getParameter('ShowLocationConjunction')} {$tmpShow->getParameter('ShowPrettyStage')}{/foreach}{/if}{if $Show->getSponsor() ne ''}
			
					{/if}{if $Show->getParameter('ShowDescription') ne ""}<span class="ShowDescription">{$Show->getParameter('ShowDescription')}</span>{/if} {if $Show->getParameter('ShowTitle') ne $Show->getArtistNames() and $Show->getArtistNames() ne ""}<p class="noprint">With: {$Show->getArtistNames($ArtistURL)}</p>{/if}	{if $TagPackage|is_a:'Package'}
							{assign var=Tags value=$TaggedObjectContainer->getTagsForObject($Show)}
							{if $Tags|is_array and $Tags|@count}
								{assign var=Tag value=$Tags|@current}
								{assign var=Images value=$TaggedObjectContainer->getAllTaggedObjects($Tag->getParameter('TagID'),'GalleryImage')}
								{if $Images|is_array and $Images|@count}
									{if $smarty.get.sid eq $Show->getParameter('ShowID')}
						            	<p style='margin-top:10px;margin-bottom:0px;'><strong>Photos:</strong></p>
							            {assign var=Displaying value=Images}
							            {assign var=Thumbnails value=$Images}
							            {assign var=ThumbnailsCount value=$Images|@count}
										{assign var=gallery_base value=$smarty.const.CMS_INSTALL_URL}
										{include file=$GalleryDirectory|cat:'/smarty/gallery.thumbnails.tpl'}
									{else}
										<p><a href='{$GalleryURL}&amp;tags={$Tag->getParameter('TagText')|urlencode}'>View Pictures</a></p>
									{/if}
								{/if}
							{/if}
						{/if}
			</td>
           <td class="program_room">{$Show->getParameter('ShowPrettyStage')}


		</td>
           </tr>         
		{/if}
       



		{assign var='oldday' value=$Show->getParameter('ShowPrettyDay')}

    {/foreach}
		</tbody>
		</table>
			</div>
	</div>
	<!-- all-show-details ends -->
	[/raw]