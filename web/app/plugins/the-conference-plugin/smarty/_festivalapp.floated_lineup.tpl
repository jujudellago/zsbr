

{foreach from=$Lineup item=Artist key=ArtistID} 
    {$Artist->parameterizeAssociatedMedia()} 
    {assign var=Images value=$Artist->getParameter("ArtistAssociatedImages")}
    {assign var=Thumb value="Thumb"}
	<div class="artist-float">
		<p class="artist-name">
			<a href="{$ArtistURL}{$ArtistID}">{$Artist->getParameter("ArtistFullName")}</a>
		</p>
		{if $Images ne ""}
			{assign var=Image value=$Images[0]}
    		<a href="{$ArtistURL}{$ArtistID}"><img border="0" src="{$Image.$Thumb}" /></a>
		{/if}
	</div>
{/foreach}
