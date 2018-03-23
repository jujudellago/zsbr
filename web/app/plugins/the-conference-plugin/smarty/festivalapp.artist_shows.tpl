<ul>
{foreach from=$Shows item=Show}
	<li>
			{assign var=at value="at"}
	{if $Show->getParameter('ShowTitle') eq $Show->getArtistNames()}
		{$Show->getParameter('ShowType')} {* Used to be just simply "Concert" *}
	{else}
		<a href="{$ShowDetailPage}sid={$Show->getParameter('ShowID')}#{$Show->getParameter('ShowID')}">{$Show->getParameter('ShowTitle')}</a> {/if} 
		- {$Show->getParameter('ShowPrettyDay')} {$at} {$Show->getParameter('ShowPrettyStartTime24')} {*$Show->getParameter('ShowLocationConjunction')*} {*$Show->getParameter('ShowPrettyStage')*}
	</li>
{/foreach}
</ul>