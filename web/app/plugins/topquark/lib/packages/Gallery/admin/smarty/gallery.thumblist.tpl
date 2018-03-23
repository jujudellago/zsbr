{* Smarty *}
{if $gallery_include_mootools}
<script type="text/javascript" src="{$gallery_base}lib/js/mootools-1.2.1-core.js"></script>
<script type="text/javascript" src="{$gallery_base}lib/js/mootools-1.2-more.js"></script>
{/if}
<script type="text/javascript" src="{$gallery_base}lib/js/multiBox/Scripts/overlay.js"></script>
<script type="text/javascript" src="{$gallery_base}lib/js/multiBox/Scripts/multiBox.js"></script>

<!-- Copy code below -->
<link type="text/css" rel="stylesheet" href="{$gallery_base}lib/js/multiBox/Styles/multiBox.css" />

<!--[if lte IE 6]>
    <link type="text/css" rel="stylesheet" href="{$gallery_base}lib/js/multiBox/Styles/multiBoxIE6.css" />
<![endif]-->


<style type='text/css'>
{literal}
#AdminThumbsLoading{
    display:block;
    background:url({/literal}{$smarty.const.CMS_ADMIN_COM_URL}{literal}images/loader.gif) top center;
    background-repeat:no-repeat;
    height:100px;
}
ul#AdminThumbs{
    display:none;
	list-style: none; 
	list-style-position: outside; 
	padding-left:0px;
	margin:0px;
	text-align:left;
	{/literal}{if $WorkingWith ne 'Search'}position:relative;{/if}{literal}
}

ul#AdminThumbs img{
	display:block;
	border:0px;
}

ul#AdminThumbs img:hover{
	display:block;
}

ul#AdminThumbs li{
	display:block;
	float:left;
	width:{/literal}{$ThumbWidth}{literal}px;
	height:{/literal}{$ThumbHeight}{literal}px;
	margin:5px;
	padding:3px;
	border:0px solid white;
    cursor:pointer;
    overflow:hidden;
}

.AdminThumbID{
    display:none;
}

ul#AdminThumbs li.selected{
	border:3px solid #00d;
	padding:0px;
}

#footer{
	display:none !important;
}

{/literal}
</style>
<script type="text/javascript">
var AjaxURL = "{$AjaxURL}";

{if $WorkingWith ne 'Search'}
{literal}
var ThumbSortables = new Class({
    Extends: Sortables,

	options: {
        handle: '.draggable',
		//This will constrain the list items to the list.
		constrain: false,
		//We'll get to see a nice cloned element when we drag.
		clone: true,
		revert: true,

		/* once an item is selected */
		onStart: function(el) { 
			el.setStyle('background-color','lightblue');
			this.clone.setStyle('z-index','2');
		    this.drag.preventDefault = true; // Done so that the Image can be dragged in IE7
		},
		/* when a drag is complete */
		onComplete: function(el) {
    		el.highlight('#444','#fff');
    		var s = this.serialize();
    		var index, id;
    		for (var i = 0; i< s.length; i++){
    		    if (s[i] == el.get('id')){
    		        index = i + 1;
    		        id = el.get('id');
    		        break;
    		    }
    		}
    		
            var req = new Request({
               method: 'get',
               url: AjaxURL,
               data: { 'action' : 'reorder' {/literal}{if $WorkingWith eq 'Gallery'}, 'id' : {$Gallery->getParameter('GalleryID')}{elseif $WorkingWith eq 'ImageSet'}, 'sid' : {$Gallery->getParameter('ImageSetID')}{/if}{literal}, 'image_id' : $(id + '_ID').innerHTML, 'index' : index },
               onComplete: function(response) { 
                    var json = $H(JSON.decode(response, true));
                    if (json.get('result') != 'success') {
                        alert(response);
                    }
                    else if (json.get('message') != null && json.get('message') != '') {
                        alert(json.get('message'));
                    }
                    
                    resetMultiBoxContent();
               }
           }).send();
		}
	},

	initialize: function(el){
	    this.parent(el,this.options);
	}
});
{/literal}{/if}{literal}
var SortableThumbs, initMultiBox;
window.addEvent('domready', function(){
    {/literal}{if $WorkingWith ne 'Search'}
    SortableThumbs = new ThumbSortables($('AdminThumbs'));
    {/if}{literal}
	//call multiBox
	initMultiBox = new multiBox({
		mbClass: '.multiBox',//class you need to add links that you want to trigger multiBox with (remember and update CSS files)
		container: $(document.body),//where to inject multiBox
		useOverlay: true,//use a semi-transparent background. default: false;
		maxSize: null,//max dimensions (width,height) - set to null to disable resizing
		addDownload: false,//do you want the files to be downloadable?
		addRollover: false,//add rollover fade to each multibox link
		addOverlayIcon: false,//adds overlay icons to images within multibox links
		addChain: false,//cycle through all images fading them out then in
		recalcTop: false,//subtract the height of controls panel from top position
		triggerEvent: 'dblclick',
		addTips: false, //adds MooTools built in 'Tips' class to each element (see: http://mootools.net/docs/Plugins/Tips)
		
		onNext: function(t){
		    var e = {key : 'right', preventDefault : function(){return;}}
            handleKeystroke(e);
        },
		onPrevious: function(t){
		    var e = {key : 'left', preventDefault : function(){return;}}
            handleKeystroke(e);
        }
	});
});

window.addEvent('domready',function(e){
    if ($('ImageSet')){
        $('ImageSetID').addEvent('change',function(e){
            $('ImageSetID').blur();
        });
    }
});

window.addEvent('load',function(e){
    $('AdminThumbs').setStyle('display','block');
    $('AdminThumbsLoading').setStyle('display','none');
});


function resetMultiBoxContent(){
    {/literal}{if $smarty.get.browsing eq '1'}
	return;
	{/if}{literal}
    // Need to reset the order for the multibox
    initMultiBox.content[0] = [];

    $$('.multiBox').each(function(el){
        initMultiBox.content[0].push(el);
    });
    prepareSelectEvents();
    initMultiBox.setContentOrder();    
    initMultiBox.currentGallery = initMultiBox.content[0];
    $('TotalImages').innerHTML = initMultiBox.currentGallery.length;
}

var LastSelected;

function prepareSelectEvents(){
    $$('.AdminThumb').each(function(el){
        {/literal}{if $smarty.get.browsing eq '1'}
		return;
		{/if}{literal}
        el.removeEvents('click');
        el.addEvent('click',function(e){
            e.stop();
            if (e.shift && LastSelected){
                if (!e.meta && !e.control){
                    $$('.selected').removeClass('selected');
                }
                var Selecting = false;
                $$('.AdminThumb').each(function(t){
                    if (t.get('id') == el.get('id') || t.get('id') == LastSelected.get('id')){
                        if (Selecting){
                            t.addClass('selected');
                        }
                        Selecting = !Selecting;
                    }
                    if (Selecting){
                        t.addClass('selected');
                    }
                });
            }
            else if(e.meta || e.control){
                el.hasClass('selected') ? el.removeClass('selected') : el.addClass('selected');
            }
            else{
                $$('.selected').removeClass('selected');
                el.addClass('selected');
            }
            LastSelected = el;
            updateSelectButtons();
        });
    });
}

function updateSelectButtons(){
    if ($$('.selected').length){
        $('DeleteImage').value = 'Delete Selected Images';
        $('AddToImageSet').value = 'Add Selected To Image Set';
    }
    else{
        $('DeleteImage').value = 'Delete All Images';
        $('AddToImageSet').value = 'Add All To Image Set';
    }
}

function handleKeystroke(e){
    var current_i,new_i;
    if ($('active_tab') && $('active_tab').value != 'group_ThumbnailsTab'){
        return;
    }
    current_i = $$('.AdminThumb').indexOf(LastSelected);
    if (e.key == 'a' && (e.meta || e.control)){
        $$('.AdminThumb').addClass('selected');
        e.preventDefault();
    }
    else if (e.key == 'd' && (e.meta || e.control)){
        $$('.selected').removeClass('selected');
        LastSelected = null;
        e.preventDefault();
    }
    else if (e.key == 'delete' || e.key == 'backspace'){
		if ($$('.selected').length){
	        deleteImages();
	        e.preventDefault();
		}
    }
    else if (e.key == 'right' || e.key == 'left'){
        new_i = current_i;
        if (!LastSelected){
            new_i = e.key == 'right' ? 0 : $$('.AdminThumb').length - 1;
        }
        else{
            new_i += e.key == 'right' ? 1 : -1;
            if (new_i < 0){
                new_i = 0;
            }
            else if(new_i > $$('.AdminThumb').length - 1){
                new_i = $$('.AdminThumb').length - 1;
            }
            LastSelected = $$('.AdminThumb')[new_i];
        }
        LastSelected = $$('.AdminThumb')[new_i];
        if (!e.shift){
            $$('.selected').removeClass('selected');
        }
        LastSelected.addClass('selected');
        if(LastSelected.getPosition().y > window.getSize().y + window.getScroll().y){
            window.scrollTo(window.getScroll().x, LastSelected.getPosition().y + LastSelected.getSize().y - window.getSize().y);
        }
        else if(LastSelected.getPosition().y < window.getScroll().y){
            window.scrollTo(window.getScroll().x, LastSelected.getPosition().y);
        }
        e.preventDefault();
    }
    else if (e.key == 'down' || e.key == 'up'){
        var new_i,old_i;
        if (!LastSelected){
            old_i = new_i = e.key == 'down' ? 0 : $$('.AdminThumb').length - 1;
            LastSelected = $$('.AdminThumb')[new_i];
        }
        else{
            var width_cell = LastSelected.getSize().x + LastSelected.getStyle('margin-left').toInt() + LastSelected.getStyle('margin-right').toInt() + LastSelected.getStyle('border-left').toInt() + LastSelected.getStyle('border-right').toInt();
            var width_row = $('AdminThumbs').getSize().x;
            var thumbs_per_row = (width_row/width_cell).floor();
            
            var looking = true;
            $$('.AdminThumb').each(function(el,i){
                if (looking && el.get('id') == LastSelected.get('id')){
                    old_i = i;
                    new_i = i + (e.key == 'down' ? thumbs_per_row : -1*thumbs_per_row);
                    looking = false;
                }
            });
            if (new_i < 0){
                new_i+= thumbs_per_row;
            }
            else if(new_i > $$('.AdminThumb').length - 1){
                new_i-= thumbs_per_row;
            }
            LastSelected = $$('.AdminThumb')[new_i];
        }
        if (!e.shift){
            $$('.selected').removeClass('selected');
            LastSelected.addClass('selected');
        }
        else{
            $$('.AdminThumb').each(function(el,j){
                if ((j >= old_i && j <= new_i) || j >= new_i && j <= old_i){
                    // Above allows for both directions of movement
                    el.addClass('selected');
                }
            });
        }
        if(LastSelected.getPosition().y > window.getSize().y + window.getScroll().y){
            window.scrollTo(window.getScroll().x, LastSelected.getPosition().y + LastSelected.getSize().y - window.getSize().y);
        }
        else if(LastSelected.getPosition().y < window.getScroll().y){
            window.scrollTo(window.getScroll().x, LastSelected.getPosition().y);
        }
        e.preventDefault();
    }
    else if (e.key == 'enter'){
        if (initMultiBox && LastSelected){
            var el = initMultiBox.content[0];
            var myTarget = $('mb_' + $(LastSelected.get('id') + '_ID').innerHTML);
			initMultiBox.open(el.indexOf(myTarget),el);
            e.preventDefault();
        }
    }
    else if (e.key == '+'){
        addImages();
    }    
    updateSelectButtons();
    
    
}

window.addEvent('domready',function(){
    prepareSelectEvents();
    $(window.document).addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'keydown' : 'keypress',function(e){
        if (!initMultiBox || !initMultiBox.opened){
            handleKeystroke(e);
        }
    })
})

function deleteImages(){
	var str;
	if ($$('.selected').length){
        str = 'Are you sure you wish to remove the selected image(s) from the {/literal}{if $WorkingWith eq "ImageSet"}Image Set{else}Gallery{/if}{literal}?  (You cannot undo this action)';
    }
    else{
        str = 'Warning: You are about to delete ALL images from the {/literal}{if $WorkingWith eq "ImageSet"}Image Set{else}Gallery{/if}{literal}.  Continue?  (You cannot undo this action)';
    }
    
	var conf = confirm(str);
	if (conf){
        callEditImageAJAX('delete');
	    return true;
    }
}

function addImages(){
    if ($('ImageSetID') && $('ImageSetID').value == ''){
        alert('You must select an Image Set first');
    }
    else{
        callEditImageAJAX('add_to_set');
    }
}

function callEditImageAJAX(action){
    var href = '{/literal}{$EditImageURL}{literal}';
    if (action == 'add_to_set' && $('ImageSetID')){
        href+= '&set_id=' + $('ImageSetID').value;
    }
    $('ActionMessage').innerHTML = "<img src='{/literal}{$smarty.const.CMS_ADMIN_COM_URL}{literal}images/loader.gif' style='height:1.2em;width;1.2em' align='absmiddle'>";
    var req = new Request({
       method: 'get',
       url: href,
       data: { 'ajax' : 'true', 'action' : action, 'image_id' : getImageIDString() },
       onComplete: function(response) { 
            var json = $H(JSON.decode(response, true));
            if (json.get('result') != 'success') {
                alert(response);
            }
            else if (json.get('message') != null ) {
                alert(json.get('message'));
            }
            else{
                var Elements;
                if ($$('.selected').length){
                    Elements = $$('.selected');
                }
                else{
                    Elements = $$('.AdminThumb');
                }
                var numElements = Elements.length;
                if (action == 'delete'){
                    var current_i = $$('.AdminThumb').indexOf(LastSelected);
                    Elements.each(function(el){
                        el.destroy();
                    });
                    resetMultiBoxContent();
                    $('ActionMessage').innerHTML = 'Deleted ' + numElements + ' images';
                    if (current_i < $$('.AdminThumb').length){
                        LastSelected = $$('.AdminThumb')[current_i];
                    }
                    LastSelected.addClass('selected');
                }
                else{
                    $('ActionMessage').innerHTML = 'Added ' + numElements + ' images to the Image Set';
                }
                $('ActionMessage').removeClass('AjaxWaiting');
            }
       }
    }).send();
}

function getImageIDString(){
    var Elements;
    if ($$('.selected').length){
        Elements = $$('.selected');
    }
    else{
        Elements = $$('.AdminThumb');
    }
    
    var ImageIDString = "";
    var sep = "";
    Elements.each(function(el){
       ImageIDString+= sep + $(el.get('id') + '_ID').innerHTML;
       sep = ",";
    });
    return ImageIDString;
}

{/literal}

</script>
<p>Total Images: <span id='TotalImages'>{$TotalImages}</span>
<span id='ActionsForSelected' style=''>    
    <input type='button' id='DeleteImage' value='Delete All Images' onclick='deleteImages();' {if $smarty.get.browsing == 'set'}style="display:none"{/if}>
    <input type='button' id='AddToImageSet' value='Add All To Image Set' onclick='addImages();'>
    {if $smarty.get.browsing != 'set'}
        <select id='ImageSetID'><option value=''>&lt;Choose an Image Set&gt;</option>
        {foreach from=$ImageSets item='ImageSet'}
            <option value='{$ImageSet->getParameter('ImageSetID')}'>{$ImageSet->getParameter('ImageSetName')}</option>
        {/foreach}
        </select>
    {/if}
    <span id='ActionMessage'>&nbsp;</span>
</span>
</p>    
<div id="AdminThumbsLoading">&nbsp;</div>
<ul id="AdminThumbs">
{foreach from=$AllGalleryImages item='Image'}
    {if $WorkingWith eq 'Gallery'}
        {assign var='GalleryDirectoryObject' value=$Gallery}
    {else}
        {assign var='GalleryDirectoryObject' value=$Image}
    {/if}
    <li class="AdminThumb draggable" id="AdminThumb{$Image->getParameter('ImageID')}">
        <span id="AdminThumb{$Image->getParameter('ImageID')}_ID" class="AdminThumbID">{$Image->getParameter('ImageID')}</span>

        {if $smarty.get.browsing eq '1'}
        <a href="javascript:openSelectSizePopup({$Image->getParameter('ImageID')})">
        {else}
        <a href="{$EditImageURL}&amp;image_id={$Image->getParameter('ImageID')}{if $WorkingWith eq 'Search'}&amp;id={$Image->getParameter('GalleryID')}{/if}" class="multiBox" rel="width:800,height:400,[image]" id="mb_{$Image->getParameter('ImageID')}">
        {/if}
        <img src="{$GalleryDirectoryObject->getGalleryDirectory()}{$Image->getParameter('GalleryImageThumb')}" id="Thumb_{$Image->getParameter('ImageID')}"></a>
    </li>
{/foreach}
</ul>
