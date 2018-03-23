	<div id="all-show-details">
{assign var='oldday' value=''}

<table class="program_table" cellspacing="0" cellpadding="2">
	<tbody>
{foreach from=$Shows key=ShowID item=Show} 

 
 	{if $Show->getParameter('ShowType') ne "Concert" and $Show->getParameter('ShowTitle') ne "Concert" and $Show->getParameter('ShowRepeatOfShowID') eq ""}
	


		{if $Show->getParameter('ShowPrettyDay') ne $oldday}
							<tr>
								<td colspan="3"><h2>{$Show->getParameter('ShowSponsor')}</h2>
									{$Show->getParameter('ShowNotesToArtist')|Markdown}
									</th>
							</tr>
		{/if}

           <tr>
			<td class="program_time"> {$Show->getParameter('ShowPrettyStartTime24')} - {$Show->getParameter('ShowPrettyEndTime24')}</td>
			<td class="program_description">
				<a name='{$Show->getParameter('ShowID')}'></a><strong>{$Show->getParameter('ShowTitle')}</strong> 
				 {if $Show->getParameter('ShowRepeats')|is_array}
		                {foreach from=$Show->getParameter('ShowRepeats') item=tmpShowID}
							{assign var=tmpShow value=$Shows.$tmpShowID}
		                    <br />{$tmpShow->getParameter('ShowPrettyDay')} at {$tmpShow->getParameter('ShowPrettyStartTime')} {$tmpShow->getParameter('ShowLocationConjunction')} {$tmpShow->getParameter('ShowPrettyStage')}
		                {/foreach}
		            {/if}
		
					{if $Show->getSponsor() ne ''}
					<p class="show-sponsor">
						{$Show->getSponsorDisplay()}
					</p>
					{/if}
					  {if $Show->getParameter('ShowDescription') ne ""}
			                <span class="ShowDescription">{$Show->getParameter('ShowDescription')|Markdown}</span>
			            {/if}  
			          {if $Show->getParameter('ShowTitle') ne $Show->getArtistNames() and $Show->getArtistNames() ne ""}
			                <p>With: {$Show->getArtistNames($ArtistURL)}</p>
			            {/if}
						{if $TagPackage|is_a:'Package'}
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
	<!-- all-show-details ends -->