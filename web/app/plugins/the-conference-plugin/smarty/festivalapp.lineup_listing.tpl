<ul class="lineup-listing">
    {foreach from=$Lineup item=Artist key=ArtistID} 
    <li><a href="{$ArtistURL}{$ArtistID}">{$Artist->getParameter("ArtistFullName")}</a><br />
		{$Artist->getParameter("ArtistDescription")}</li>
    {/foreach}
</ul>