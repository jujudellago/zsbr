{foreach from=$Lineup item=Artist key=ArtistID}
	<div class="artist-teaser">
		<h1><a href="{$ArtistURL}{$ArtistID}">{$Artist->getParameter('ArtistFullName')}</a></h1>
     	{$Artist->parameterizeAssociatedMedia()} 
		{assign var=Images value=$Artist->getParameter("ArtistAssociatedImages")}
		{assign var=Thumb value="Thumb"}
		<p class="artist-description">
			{if $Images ne ""}
				{assign var=Image value=$Images[0]}
				<a href="{$ArtistURL}{$ArtistID}"><img border="0" src="{$Image.$Thumb}" /></a>
			{/if}
			{$Artist->getParameter("ArtistDescription")}
		</p>
		<div class="clear"></div>
	</div>
{/foreach}
