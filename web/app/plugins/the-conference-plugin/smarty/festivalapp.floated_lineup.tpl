<p style="line-height:40px; float:right; display:none">
<a class="button blue big normal" href="#" title="Search a speaker">Search a speaker</a>
</p>
<div class="clearboth"></div>
<div id="search-speakers" >
<form method="get">
		<div>
			<h4>Search a speaker</h4>	
			<input type="text" value="" name="q" id="q" /><span id="filter-count">Enter text to filter the speakers list 
			</span>
		</div>
	</form>
	<div class="horizontal-line">
		
	</div>
</div>
<ul id="speakers-list"> 	
{foreach from=$Lineup item=Artist key=ArtistID} 
    {$Artist->parameterizeAssociatedMedia()} 
    {assign var=Images value=$Artist->getParameter("ArtistAssociatedImages")}
    {assign var=Thumb value="Thumb"}
[raw]
	<li class="artist-float">
		<span class="artist-content">
		{if $Images ne ""}
			{assign var=Image value=$Images[0]}
    		<a href="{$ArtistURL}{$ArtistID}"><img border="0" src="{$Image.$Thumb}" /></a>
		{/if}
		<span class="artist-name">
			<a href="{$ArtistURL}{$ArtistID}">{$Artist->getParameter("ArtistFullName")}</a>
		</span>
		</span>
	</li>
[/raw]	
{/foreach}
</ul>
