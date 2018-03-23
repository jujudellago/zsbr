{$Artist->parameterizeAssociatedMedia()}
<div class="artist-detail">
	<h2>{$Artist->getParameter('ArtistFullName')}</h2>
	{if $Artist->getParameter('ArtistShows') ne ''}
		<div class="simple-info schedule_box">
			<strong>{'Artist'|vocabulary} Schedule</strong>
			<div class="artist-shows">{$Artist->getParameter('ArtistShows')}</div>
		</div>
	{/if}
	{assign var=Images value=$Artist->getParameter("ArtistAssociatedImages")}
	{assign var=MP3s value=$Artist->getParameter("ArtistAssociatedMedia")}
	{assign var=Thumb value="Resized"}
	{if $Images ne ""}
		{assign var=Image value=$Images[0]}
		<img border="0" src="{$Image.$Thumb}"  class="portfolio-img speaker_bio"/>
	{/if}

	<p class="artist-short-description">
		{* $Artist->getParameter("ArtistDescription") *}
	</p>
	<p class="artist-description">
		{$Artist->getParameter("ArtistLongDescription")}
	</p>
	{if $Artist->getParameter('ArtistWebsite') ne ''}
	<p class="artist-website">
		<ul class="slinks">
		<li class="pro"><a href="{$Artist->getParameter('ArtistWebsiteURL')}" >{$Artist->getParameter('ArtistWebsite')}</a></li>
		</ul>
	</p>
	{/if}
	{if $Artist->getParameter('ArtistVideo') ne ''}
	<div class="horizontal-line"></div>
	<h3>Interview</h3>
	<p class="artist-video">
		<iframe width="620" height="320" src="http://www.youtube.com/embed/{$Artist->getParameter('ArtistVideo')}" frameborder="0" allowfullscreen></iframe>
	</p>
	{/if}
	
	{if $MP3s|is_array and $MP3s|@count}
	    <p style='margin-bottom:0px;text-align:center'><b>Listen to music by {$Artist->getParameter('ArtistFullName')}</b></p>
	    <div align='center'>
	        <!-- ********************************************************************************************************** -->
	        <!-- *  FLAM PLAYER BLOCK                                                                                     * -->
	        <!-- ********************************************************************************************************** -->
	        <object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000'
	        	codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0'
	        	width='300' 
	        	height='125'>
	        		<param name=movie value='{$flamPackage->getPackageURL()}flam-player-npl.swf'>
	        		<param name=flashVars value='fp_root_url={$flamPackage->getPackageURL()}&ovr_color=0x{$package->flamplayer_colour}&ovr_langage=en&ovr_playlist=default_playlist&ovr_author={$Artist->getParameter('ArtistID')}&ovr_order=date_music&ovr_order_direction=DESC&ovr_autoplay=0&ovr_loop_playlist=1&ovr_loop_tracks=0&ovr_shuffle=0'>
	        		<param name=menu value=false>
	        		<param name=quality value=best>
	        		<param name=wmode value=transparent>
	        		<param name=bgcolor value=#FFFFFF>

	        	<embed src='{$flamPackage->getPackageURL()}flam-player-npl.swf'
	        		flashVars='fp_root_url={$flamPackage->getPackageURL()}&ovr_color=0x{$package->flamplayer_colour}&ovr_langage=en&ovr_playlist=default_playlist&ovr_author={$Artist->getParameter('ArtistID')}&ovr_order=date_music&ovr_order_direction=DESC&ovr_autoplay=0&ovr_loop_playlist=1&ovr_loop_tracks=0&ovr_shuffle=0'
	        		menu=false
	        		quality=best
	        		wmode=transparent
	        		bgcolor=#FFFFFF
	        		width='300'
	        		height='125'
	        		type='application/x-shockwave-flash'
	        		pluginspage='http://www.macromedia.com/go/getflashplayer'>
	        	</embed>
	        </object>
	        <!-- ********************************************************************************************************** -->
	        <!-- *  FLAM PLAYER BLOCK END                                                                                 * -->
	        <!-- ********************************************************************************************************** -->
	        <p style='text-align:center'><a href="#" onclick="openPopup('{$flamPackage->getPackageURL()}admin/return_player.php?fp_style=flam-player&fp_root_url={$flamPackage->getPackageURL()}&ovr_color=0x1a6018&ovr_langage=en&ovr_playlist=default_playlist&ovr_author={$Artist->getParameter('ArtistID')}&ovr_order=date_music&ovr_order_direction=DESC&ovr_autoplay=1&ovr_loop_playlist=1&ovr_loop_tracks=0&ovr_shuffle=0&width=300&height=315','flamPlayer','300','315');">Open player in new window</a> and listen while you browse</p>
	    </div>
	{/if}

	{if $Artist->getParameter('BandMemberShows')|@is_array and $Artist->getParameter('BandMemberShows')|@count}
		<div class="artist-shows">
			<p>Members of {$Artist->getParameter('ArtistFullName')} appearing in {'Show'|pluralize} of their own</p>
			{foreach from=$Artist->getParameter('BandMemberShows') item=BandMemberShows}
				{$BandMemberShows}
			{/foreach}
		</div>
	{/if}
	{if $TaggedImages|@is_array and $TaggedImages|@count}
		<h3><a href="{$TaggedImagesURL}">Tagged Images</a></h3>
		<div class="artist-shows">
			<h1>Images of {$Artist->getParameter('ArtistFullName')}</h1>
            {assign var=Displaying value=Images}
            {assign var=Thumbnails value=$TaggedImages}
            {assign var=ThumbnailsCount value=$TaggedImages|@count}
			{assign var=gallery_base value=$smarty.const.CMS_INSTALL_URL}
			{include file=$GalleryDirectory|cat:'/smarty/gallery.thumbnails.tpl'}
		</div>
	{/if}
</div>

