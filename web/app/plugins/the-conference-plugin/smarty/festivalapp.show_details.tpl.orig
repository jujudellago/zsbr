	<div id="all-show-details">
    {if $parms.show_top_navigation ne 'false'}
        <span style='float:left'><p>Go to <a href='{$SchedulePage}'>Complete Schedule</a></p></span>
        <span style='float:right'><p>Sort by: 
        {if $smarty.get.sort eq ''}
            Day/Time or <a href='{$ShowDetailPage}sort=alpha'>Alphabetic</a>
        {else}
            <a href='{$ShowDetailPage}sort=day'>Day/Time</a> or Alphabetic
        {/if}
        </p></span>
        <p>&nbsp;</p>
    {/if}
    {foreach from=$Shows key=ShowID item=Show} 
        {if $Show->getParameter('ShowType') ne "Concert" and $Show->getParameter('ShowTitle') ne "Concert" and $Show->getParameter('ShowRepeatOfShowID') eq ""}
            {if $ShowID eq $smarty.get.sid}
				<LINK REL=StyleSheet HREF='{$packageURL}css/schedule.css' TYPE='text/css'>
                <div class='ShowDetailHighlighted' style='margin-top:30px'>
            {/if}
            <h4 style='margin-bottom:0px;{if $Show->getParameter('ShowID') ne $smarty.get.sid}margin-top: 30px{/if}'><a name='{$Show->getParameter('ShowID')}'></a><strong>{$Show->getParameter('ShowTitle')}</strong> 
			<font size='2'>- </font><a href='{$SchedulePage}type={$Show->getParameter('ShowScheduleUID')|urlencode}&amp;sid={$Show->getParameter('ShowID')}'><span style='font-size:8pt;font-weight:normal'>See on schedule</span> </a></h4>
            <p>
            {$Show->getParameter('ShowPrettyDay')} at {$Show->getParameter('ShowPrettyStartTime')} {$Show->getParameter('ShowLocationConjunction')} {$Show->getParameter('ShowPrettyStage')}
            {if $Show->getParameter('ShowRepeats')|is_array}
                {foreach from=$Show->getParameter('ShowRepeats') item=tmpShowID}
					{assign var=tmpShow value=$Shows.$tmpShowID}
                    <br />{$tmpShow->getParameter('ShowPrettyDay')} at {$tmpShow->getParameter('ShowPrettyStartTime')} {$tmpShow->getParameter('ShowLocationConjunction')} {$tmpShow->getParameter('ShowPrettyStage')}
                {/foreach}
            {/if}
            </p>
			{if $Show->getSponsor() ne ''}
			<p class="show-sponsor">
				{$Show->getSponsorDisplay()}
			</p>
			{/if}
            {if $Show->getParameter('ShowTitle') ne $Show->getArtistNames() and $Show->getArtistNames() ne ""}
                <p>With: {$Show->getArtistNames($ArtistURL)}</p>
            {/if}
            {if $Show->getParameter('ShowDescription') ne ""}
                <p><em>{$Show->getParameter('ShowDescription')}</em></p>
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
            {if $Show->getParameter('ShowID') eq $smarty.get.sid}
                {* Highlight it *}
                </div>
            {/if}
        {/if}
    {/foreach}
	</div>