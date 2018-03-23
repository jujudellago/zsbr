{* Smarty *}
{if !$ThumbnailsCount}
    {if $smarty.get.gid eq ''}
        <p>There are currently no galleries loaded on the system.  Please try again later</p>
    {else}
        <p>The selected gallery has no images loaded into it</p>
    {/if}
{else}
    {if $Displaying eq 'Images' and $parms.link_to_original ne 'true'}
<!-- Thanks to Luca Reghellin - http://reghellin.com/milkbox/ -->
<script type="text/javascript" src="{$gallery_base}lib/js/milkbox/js/milkbox.js"></script>
<link href="{$gallery_base}lib/js/milkbox/css/milkbox/milkbox.css" rel="stylesheet" type="text/css" media="screen" />
<script type='text/javascript'>    
{literal}
    window.addEvent('domready', function(){
        // Change what happens when people click on the thumbs from the degraded behaviour
        var items = $$('.thumb_link'); // All of the images are in a list
		
        items.each(function(item,index){
            item.setProperty('href',item.getProperty('alt'));
            
            item.addEvent('click',function(e){
                e.stop();
                if (Milkbox.slideshow == 'playing'){
    			    Milkbox.autoPlay({
    			        gallery: Milkbox.galleries[0],
    			        index: index
    			    });
                }
                else{
			        Milkbox.openMilkbox(Milkbox.galleries[0],index);
			    }
                Milkbox.updatePausePlay();
            });
		
            
        });
            

        // Get the Milkbox ready
        Milkbox = new Milkbox({
    		overlayOpacity:0.9,
    		topPosition:20,
    		initialWidth:250,
    		initialHeight:250,
    		resizeDuration:500,
    		resizeTransition:'sine:in:out',/*function (ex. Transitions.Sine.easeIn) or string (ex. 'quint:out')*/
    		hoverBackgroundPosition:'0 -23px',
    		autoPlay:true,
    		autoPlayDelay:5,
    		removeTitle:false,
    		openOnClick: false,
    		blockContextMenu: false,
            imageAttribute: 'longdesc', /* the attribute in the <img> tag that contains the URL to the full image */
            imageTransition: 'fade',
    		constrainSizeToWindow: true
        });

    });
{/literal}
</script>  
    {/if}
<style type="text/css">   
{literal}   
.thumb_list_ul{
	list-style:none;
	padding-left:0px;
	margin-left:0px
}
.thumb_list_ul li{
	float:left;
	margin:0px 10px 10px 0px;
}
{/literal}
</style>
<div id="thumb_wrapper" class="thumb_wrapper">
<div id="thumb_list" class="thumb_list">
<ul id="thumb_list_ul" class="thumb_list_ul">
{foreach from=$Thumbnails item=_Image}
    {if $_Image->getParameter('ImageGalleryDirectory') eq ''}
        {$_Image->setParameter('ImageGalleryDirectory',$_Image->getGalleryDirectory())}
    {/if}
    {if $parms.link_to_original eq 'true'}
        {assign var='ImageLink' value=$_Image->getParameter('ImageGalleryDirectory')|cat:$_Image->getParameter('GalleryImageOriginal')}
        {$_Image->setParameter('ImageLink',$ImageLink)}
    {/if}
    {if $parms.thumb_size eq 'Resized'}
        {* Display the Caption *}
        <a href="{$_Image->getParameter('ImageLink')}" ><img src="{$_Image->getParameter('ImageGalleryDirectory')|cat:$_Image->getParameter('GalleryImageResized')}"></a>
        {if $_Image->getParameter('ImageCaption') ne ""}
            <p>{$_Image->getParameter('ImageCaption')}</p>
        {/if}
        {if $_Image->getParameter('ImageCredit') ne ""}
            <p>Photo by: {$_Image->getParameter('ImageCredit')}</p>
        {/if}
        <br /><br/>
    {else}
        {if $Displaying eq 'Galleries'}
            <li class="gallery_thumb">
        {else}
            <li>
        {/if}

        <div class="image_wrapper">
        {assign var='ImageCaption' value=$_Image->getParameter('ImageCaption')}
        {if $_Image->getParameter('ImageCredit') ne "" and $_Image->getParameter('ImageCredit') ne "Various"}
            {assign var='ImageCaption' value=$ImageCaption|cat:' (Credit: '}
            {assign var='ImageCaption' value=$ImageCaption|cat:$_Image->getParameter('ImageCredit')}
            {assign var='ImageCaption' value=$ImageCaption|cat:')'}
        {/if}
        <a href="{$_Image->getParameter('ImageLink')}" alt="{$_Image->getParameter('ImageGalleryDirectory')|cat:$_Image->getParameter('GalleryImageResized')}" longdesc="{$_Image->getParameter('ImageGalleryDirectory')|cat:$_Image->getParameter('GalleryImageOriginal')}" rel="milkbox:gallery" title="{$ImageCaption|htmlspecialchars}" class="thumb_link"><img src="{$_Image->getParameter('ImageGalleryDirectory')|cat:$_Image->getParameter('GalleryImageThumb')}" class="thumb"></a>
        {if $Displaying eq 'Galleries'}
            <p class='gallery_title'>{$_Image->getParameter('ImageName')}</p>
        {/if}
        </div></li>
    {/if}
{/foreach}
</ul>
</div>
</div>
<div style='clear:both'></div>                 
{/if}