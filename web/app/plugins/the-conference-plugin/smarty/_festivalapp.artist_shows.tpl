<h1>{$Artist->getParameter('ArtistFullName')}{if $CurrentYear} is appearing in{else} appeared in{/if}</h1>
<ul>
{foreach from=$Shows item=Show}
	<li>
	{if $Show->getParameter('ShowTitle') eq $Show->getArtistNames()}
		{$Show->getParameter('ShowType')} {* Used to be just simply "Concert" *}
	{else}
		<a href="{$ShowDetailPage}sid={$Show->getParameter('ShowID')}#{$Show->getParameter('ShowID')}">{$Show->getParameter('ShowTitle')}</a>
    {/if}
	{assign var=at value="at"}
     - {$Show->getParameter('ShowPrettyDay')} {$at} {$Show->getParameter('ShowPrettyStartTime')} {$Show->getParameter('ShowLocationConjunction')} {$Show->getParameter('ShowPrettyStage')}
	</li>
{/foreach}
</ul>