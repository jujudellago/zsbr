{* Smarty *}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
{if $WorkingWith eq 'Gallery'}
    {assign var='GalleryDirectoryObject' value=$Gallery}
{else}
    {assign var='GalleryDirectoryObject' value=$Image}
{/if}
{assign var=thumb_file value=$GalleryDirectoryObject->getGalleryDirectory($smarty.const.IMAGE_DIR_FULL)|cat:$Image->getParameter('GalleryImageThumb')}
{assign var=thumb_info value=$thumb_file|@getimagesize}
<head>
    <title>Edit Image</title>
    <script type="text/javascript" src="{$gallery_base}lib/js/mootools-1.2.1-core.js"></script>
    <script type="text/javascript" src="{$gallery_base}lib/js/mootools-1.2-more.js"></script>
	<script src="{$gallery_base}lib/js/jcrop/js/jquery.min.js" type="text/javascript"></script>
	<script src="{$gallery_base}lib/js/jcrop/js/jquery.Jcrop.js" type="text/javascript"></script>
	<link rel="stylesheet" href="{$gallery_base}lib/js/jcrop/css/jquery.Jcrop.css" type="text/css" />
    <style type="text/css">
    {literal}
    body{
        font-family:Verdana,Arial,sans-serif;
    }
    p{
        margin:3px 0px;
    }
    .Button{
        cursor:pointer;
    }
    img {
        border:0px;
    }
    
    {/literal}
    </style>
    <script type="text/javascript">
    {literal}
    var CaptionSave,CreditSave,jcrop_api;
    
    window.addEvent('domready',function (e){
		$.noConflict();
        $('RotateClockwise').addEvent('click',function(e){
            rotate('clockwise');
        });
        $('RotateCounterClockwise').addEvent('click',function(e){
            rotate('counter_clockwise');
        });
        if ($('Favourite').hasClass('Unmarked')){
            $('Favourite').setStyle('opacity','0.3');
        }
        $('Favourite').addEvent('click',function(e){
            var action;
            if ($('Favourite').hasClass('Unmarked')){
                action = 'mark';
            }
            else{
                action = 'unmark';
            }
            var req = new Request({
               method: 'get',
               url: '{/literal}{$AjaxURL}{literal}',
               data: { 'action' : action },
               onComplete: function(response) { 
                    var json = $H(JSON.decode(response, true));
                    if (json.get('result') != 'success') {
                        alert(response);
                    }
                    else if (json.get('message') != null ) {
                        alert(json.get('message'));
                    }
                    else{
                        if (action == 'mark'){
                            $('Favourite').setStyle('opacity','1');
                            $('Favourite').removeClass('Unmarked');
                            $('Favourite').addClass('Marked');
                        }
                        else{
                            $('Favourite').setStyle('opacity','0.3');
                            $('Favourite').removeClass('Marked');
                            $('Favourite').addClass('Unmarked');
                        }
                    }
               }
            }).send();
        });
        
        {/literal}{if !$SearchResults}{literal}
        if (!$('Primary').hasClass('Primary')){
            $('Primary').setStyle('opacity','0.3');
            $('Primary').addEvent('click',function(e){
                var req = new Request({
                   method: 'get',
                   url: '{/literal}{$AjaxURL}{literal}',
                   data: { 'action' : 'primary' },
                   onComplete: function(response) { 
                        var json = $H(JSON.decode(response, true));
                        if (json.get('result') != 'success') {
                            alert(response);
                        }
                        else if (json.get('message') != null ) {
                            alert(json.get('message'));
                        }
                        else{
                            $('Primary').setStyle('opacity','1');
                            $('Primary').addClass('Primary');
                            $('Primary').removeEvents('click');
                        }
                   }
                }).send();
            });
        }
        {/literal}{/if}{literal}
        
        $('ImageCaption').addEvent('focus',function(e){
            $('CaptionControls').setStyle('display','inline');
            CaptionSave = $('ImageCaption').value;
        });
        $('ImageCaption').addEvent('keydown',function(e){
            if (e.key == 'esc'){
                revertCaption();
                parent.window.focus();
            }
            if (e.key == 'tab'){
                saveCaption();
            }
        });
        
        $('ImageCaption').addEvent('blur',function(e){
            // Delay for just a bit so that if the blurring is because they pressed the OK button
            // there's time for that logic to happen.
            (function (){ revertCaption(); }).delay(200);
        });
        
        $('CaptionOK').addEvent('click',function(e){
            saveCaption();
            parent.window.focus();            
        });
        $('CaptionCancel').addEvent('click',function(e){
            revertCaption();
            parent.window.focus();            
        });
        
{/literal}{if $AllowCreditOnImage}{literal}        
        $('ImageCredit').addEvent('focus',function(e){
            $('CreditControls').setStyle('display','inline');
            CreditSave = $('ImageCredit').value;
        });
        $('ImageCredit').addEvent('keydown',function(e){
            if (e.key == 'esc'){
                revertCredit();
                parent.window.focus();
            }
            if (e.key == 'tab'){
                saveCredit();
            }
        });
        
        $('ImageCredit').addEvent('blur',function(e){
            // Delay for just a bit so that if the blurring is because they pressed the OK button
            // there's time for that logic to happen.
            (function (){ revertCredit(); }).delay(200);
        });
        
        $('CreditOK').addEvent('click',function(e){
            saveCredit();
            parent.window.focus();            
        });
        $('CreditCancel').addEvent('click',function(e){
            revertCredit();
            parent.window.focus();            
        });
{/literal}{/if}{literal}
        $('DeleteImage').addEvent('click',function(e){
            var str,conf;
        	str = 'Are you sure you wish to remove this image from the {/literal}{if $WorkingWith eq "ImageSet"}Image Set{else}Gallery{/if}{literal}?  (You cannot undo this action)';
        	conf = confirm(str);
        	if (conf){
                var req = new Request({
                   method: 'get',
                   url: '{/literal}{$AjaxURL}{literal}',
                   data: { 'action' : 'delete' },
                   onComplete: function(response) { 
                        var json = $H(JSON.decode(response, true));
                        if (json.get('result') != 'success') {
                            alert(response);
                        }
                        else if (json.get('message') != null ) {
                            alert(json.get('message'));
                        }
                        else{
        	                parent.window.$('AdminThumb{/literal}{$Image->getParameter('ImageID')}{literal}').destroy();
        	                parent.window.resetMultiBoxContent();
        	                if (parent.window.initMultiBox.index < parent.window.initMultiBox.currentGallery.length){
        	                    parent.window.initMultiBox.index--    
        	                    parent.window.initMultiBox.next();
        	                }
        	                else{
        	                    parent.window.initMultiBox.close();
        	                }
                        }
                   }
                }).send();
            }
        });
        
        {/literal}{if $smarty.get.browsing and !$AlreadyInImageSet}{literal}
        $('AddToSet').addEvent('click',function(e){
            var req = new Request({
               method: 'get',
               url: '{/literal}{$AjaxURL}{literal}',
               data: { 'action' : 'add_to_set' },
               onComplete: function(response) { 
                    var json = $H(JSON.decode(response, true));
                    if (json.get('result') != 'success') {
                        alert(response);
                    }
                    else if (json.get('message') != null ) {
                        alert(json.get('message'));
                    }
                    else{
                        $('ImageSetMessage').innerHTML = "Added to Image Set";
                    }
               }
            }).send();
        });
        {/literal}{/if}{literal}
        
		if ($('TagEntry')){
	        $('TagEntry').addEvent('keydown',function(e){
	            if (e.key == 'esc'){
	                parent.window.focus();
	            }
	        });
		}

    });
    
    
    function rotate(dir){
        $('CurrentImage').setProperty('width','');
        $('CurrentImage').setStyle('display','block');
		jcrop_api.destroy();
        $('CurrentImage').src = '{/literal}{$gallery_base}lib/js/multiBox/Images/mb_Components/loader.gif{literal}';

        var req = new Request({
           method: 'get',
           url: '{/literal}{$AjaxURL}{literal}',
           data: { 'action' : 'rotate' , 'direction' :  dir },
           onComplete: function(response) { 
                var json = $H(JSON.decode(response, true));
                if (json.get('result') != 'success') {
                    alert(response);
                }
                else if (json.get('message') != null ) {
                    alert(json.get('message'));
                }
                else{
                    $('CurrentImage').src = '{/literal}{$GalleryDirectoryObject->getGalleryDirectory()}{literal}' + json.get('new_resized');
                    $('CurrentImage').setProperty('width','400');
					setupJCrop(jQuery);
					$('CurrentThumb').setProperty('src','{/literal}{$GalleryDirectoryObject->getGalleryDirectory()}{literal}' + json.get('new_thumb'));
					$('preview').setProperty('src','{/literal}{$GalleryDirectoryObject->getGalleryDirectory()}{literal}' + json.get('new_resized'));
					//$$('div.jcrop-holder').setStyle('display','block');
                    //$('OriginalImage').href = '{/literal}{$GalleryDirectoryObject->getGalleryDirectory()}{literal}' + json.get('new_original');
                    parent.window.$('Thumb_{/literal}{$Image->getParameter('ImageID')}{literal}').src = '{/literal}{$GalleryDirectoryObject->getGalleryDirectory()}{literal}' + json.get('new_thumb');
                }
           }
        }).send();
    }
    
    var saveCaption = function(){
        CaptionSave = $('ImageCaption').value;
        var req = new Request({
           method: 'get',
           url: '{/literal}{$AjaxURL}{literal}',
           data: { 'action' : 'caption' , 'caption' :  $('ImageCaption').value },
           onComplete: function(response) { 
                var json = $H(JSON.decode(response, true));
                if (json.get('result') != 'success') {
                    alert(response);
                }
                else if (json.get('message') != null ) {
                    alert(json.get('message'));
                }
           }
        }).send();
        $('CaptionControls').setStyle('display','none');
    }
    
    var revertCaption = function(){
        $('ImageCaption').value = CaptionSave;
        $('CaptionControls').setStyle('display','none');
    }
{/literal}{if $AllowCreditOnImage}{literal}
    var saveCredit = function(){
        CreditSave = $('ImageCredit').value;
        var req = new Request({
           method: 'get',
           url: '{/literal}{$AjaxURL}{literal}',
           data: { 'action' : 'credit' , 'credit' :  $('ImageCredit').value },
           onComplete: function(response) { 
                var json = $H(JSON.decode(response, true));
                if (json.get('result') != 'success') {
                    alert('here' + response);
                }
                else if (json.get('message') != null ) {
                    alert('there' + json.get('message'));
                }
           }
        }).send();
        $('CreditControls').setStyle('display','none');
    }
    
    var revertCredit = function(){
        $('ImageCredit').value = CreditSave;
        $('CreditControls').setStyle('display','none');
    }
{/literal}{/if}{literal}    
    {/literal}
    </script>

	<script type="text/javascript">
	{literal}
if (typeof jQuery == 'function'){
    jQuery(function($){
		setupJCrop($);
		$('#ThumbOK').click(function(){
			var s = jcrop_api.tellSelect();
			releaseJCrop();
			$('#CurrentThumb').attr('src','{/literal}{$gallery_base}lib/js/multiBox/Images/mb_Components/loader.gif{literal}');
			$.ajax({
               type: 'GET',
               url: '{/literal}{$AjaxURL}{literal}',
               data: { action : 'new-thumb', x : s.x, y : s.y, w : s.w, h : s.h, dest_w : {/literal}{$thumb_info.0}{literal}, dest_h : {/literal}{$thumb_info.1}{literal} },
               success: function(response) { 
					jcrop_api.destroy();
					$('#CurrentThumb').attr('src',response.new_thumb);
                    parent.window.$('Thumb_{/literal}{$Image->getParameter('ImageID')}{literal}').src = response.new_thumb;
					setupJCrop($);
					return;
               }
			})
		});
		$('#ThumbCancel').click(function(){
			releaseJCrop();
		});
		releaseJCrop = function(){
			jcrop_api.release();
			$('#ThumbControls').css('display','none');
			$('#CurrentThumb').css('display','inline');
			$('#preview').css('display','none');
		}
	});
}

	setupJCrop = function($){

	      // Create variables (in this scope) to hold the API and image size
	      var boundx, boundy;

	      $('#CurrentImage').Jcrop({
	        onChange: updatePreview,
	        onSelect: updatePreview,
	        aspectRatio: {/literal}{$thumb_info.0}/{$thumb_info.1}{literal}
	      },function(){
	        // Use the API to get the real image size
	        var bounds = this.getBounds(); // image [width, height]
	        boundx = bounds[0];
	        boundy = bounds[1];
	        // Store the API in the jcrop_api variable
	        jcrop_api = this;
	      });

	      function updatePreview(c)
	      {
			$('#ThumbControls').css('display','inline');
			// c = coordinates = {h,w,x,x2,y,y2}
			if ($('#CurrentThumb').css('display') == 'inline'){
				$('#CurrentThumb').css('display','none');
				$('#preview').css('display','inline');
			}
	        if (parseInt(c.w) > 0)
	        {
	          var rx = {/literal}{$thumb_info.0}{literal} / c.w;
	          var ry = {/literal}{$thumb_info.1}{literal} / c.h;
	          $('#preview').css({
	            width: Math.round(rx * boundx) + 'px',
	            height: Math.round(ry * boundy) + 'px',
	            marginLeft: '-' + Math.round(rx * c.x) + 'px',
	            marginTop: '-' + Math.round(ry * c.y) + 'px'
	          });
	        }
	      };
	}
	{/literal}
	</script>
</head>
<body>
<div style="float:left;width:50%;text-align:center">
    <img src="{$GalleryDirectoryObject->getGalleryDirectory()}{$Image->getParameter('GalleryImageResized')}" id="CurrentImage" width="{$ImageWidth}"></a>
</div>
<div style="float:right;width:46%;padding-left:10px">
    {if $smarty.get.browsing}
	<fieldset>
		<legend>Image Set</legend>
		{if $AlreadyInImageSet}
            <p id="ImageSetMessage">Image is already in Image Set</p>
        {else}
            <p id="ImageSetMessage"><b>Add to Image Set:</b> <img src="{$gallery_base}admin/images/icons/add.png" align="absmiddle" class="Button" id="AddToSet"></p>
        {/if}
	</fieldset>
    {/if}
	<fieldset>
		<legend>Options</legend>
        <p><b>Caption:</b><span id="CaptionControls" style="display:none"><img src="{$gallery_base}admin/images/icons/ok.png" align="absmiddle" id="CaptionOK" class="Button"><img src="{$gallery_base}admin/images/icons/close.png" align="absmiddle" id="CaptionCancel" class="Button"></span></p>
        <textarea cols="40" rows="4" id="ImageCaption">{$Image->getParameter('ImageCaption')}</textarea>
        {if $AllowCreditOnImage}
        <p><b>Credit:</b><span id="CreditControls" style="display:none"><img src="{$gallery_base}admin/images/icons/ok.png" align="absmiddle" id="CreditOK" class="Button"><img src="{$gallery_base}admin/images/icons/close.png" align="absmiddle" id="CreditCancel" class="Button"></span></p>
        <textarea cols="40" rows="1" id="ImageCredit">{$Image->getParameter('ImageCredit')}</textarea>
        {/if}
        <p>{if !$SearchResults}<b>Primary:</b> <img src="{$gallery_base}admin/images/icons/ok.png" align="absmiddle" id="Primary" class="Button {if $Image->getParameter('PrimaryThumb')}Primary{/if}">{/if}
           <b>Favourite:</b> <img src="{$gallery_base}admin/images/icons/ok.png" align="absmiddle" id="Favourite" class="Button {if $Image->getParameter('ImageIsMarked')}Marked{else}Unmarked{/if}"></p>
        <p><b>Rotate:</b> <img src="{$gallery_base}admin/images/icons/Repeat_01.png" align="absmiddle" class="Button" id="RotateClockwise"> <img src="{$gallery_base}admin/images/icons/Repeat.png" align="absmiddle" class="Button" id="RotateCounterClockwise">
           <b>Delete Image:</b> <img src="{$gallery_base}admin/images/icons/close.png" align="absmiddle" class="Button" id="DeleteImage"></p>
		<p>Thumbnail: To edit drag mask on bigger image</p>
		<span id="ThumbControls" style="display:none;float:right"><img src="{$gallery_base}admin/images/icons/ok.png" align="absmiddle" id="ThumbOK" class="Button"><img src="{$gallery_base}admin/images/icons/close.png" align="absmiddle" id="ThumbCancel" class="Button"></span>
		<div style="width:{$thumb_info.0}px;height:{$thumb_info.1}px;overflow:hidden;">
			<img src="{$GalleryDirectoryObject->getGalleryDirectory()}{$Image->getParameter('GalleryImageThumb')}" id="CurrentThumb" style="display:inline">
			<img src="{$GalleryDirectoryObject->getGalleryDirectory()}{$Image->getParameter('GalleryImageResized')}" id="preview" alt="Preview" style="display:none"/>
		</div>
    </fieldset>
    {if $TagWidget}
        {$TagWidget}
    {/if}

</div>
</body>
</html>