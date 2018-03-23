<?php
    if (!isset($Bootstrap)){
        die ("You cannot access this file directly");
    }
    
    /******************************
    * This script does a batch resize 
    * on all images in the gallery 
    * to conform to the constants set
    * in the file ImageLibrarian.php
    * (Yes, one day, I'll take them
    * out of there!)
    ******************************/
    
	/*******************************************
	// Set the navigation items
	********************************************/
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'batch_resize');
	$resize = $Bootstrap->makeAdminURL($Package,'batch_resize');

    $GalleryContainer = new GalleryContainer();
    $GalleryImageContainer = new GalleryImageContainer();
    $Galleries = $GalleryContainer->getAllGalleries(array('GalleryName'), array('asc'),array('public','private','system'));
    //$Galleries = array_slice($Galleries,1,2);
?>
<div style="width:90%;margin:auto">    
    <p>This utility will do a batch resize on all images in all galleries.
       The process will take quite a while, depending on the number of images and galleries.</p>
    <p>It will only do the galleries with a check box beside them.  <a href='javascript:UncheckAll();'>Uncheck All</a> <a href='javascript:CheckAll();'>Check All</a>
    <p>Once you're ready, click the start button: <input type='button' id='StartButton' value='Start' onclick='startResizing()'><input type='button' id='StopButton' value='Stop' onclick='stopResizing()' style='display:none'></p>
    
    <ul id="GalleryList">
<?php        
        foreach ($Galleries as $Gallery){
            $GalleryImages = $Gallery->getAllGalleryImages();
            if (!is_array($GalleryImages)){
                $GalleryImages = array();
            }
            echo "<li>";
            echo "<input type='checkbox' class='GalleryCheck' id='GalleryCheck".$Gallery->getParameter('GalleryID')."' checked />";
            echo "<b>".$Gallery->getParameter('GalleryName')."</b> (".count($GalleryImages)." images)";
            echo "<span class='GalleryID' id='GalleryID".$Gallery->getParameter('GalleryID')."' style='display:none'>".$Gallery->getParameter('GalleryID')."</span>\n";
            echo "<span class='GalleryOffset' id='GalleryOffset".$Gallery->getParameter('GalleryID')."' style='display:none'>0</span>\n";
            echo "<span id='Status".$Gallery->getParameter('GalleryID')."'></span>\n";
            echo "</li>\n";
        }
        /*
        foreach ($Images as $GalleryID => $GalleryImages){
            $Gallery = $GalleryContainer->getGallery($GalleryID);
            if (!is_array($GalleryImages)){
                $GalleryImages = array();
            }
            echo "<li>";
            echo "<input type='checkbox' class='GalleryCheck' id='GalleryCheck".$Gallery->getParameter('GalleryID')."' checked />";
            echo "<b>".$Gallery->getParameter('GalleryName')."</b> (".count($GalleryImages)." images)";
            echo "<span id='Status".$Gallery->getParameter('GalleryID')."'></span>";
            echo "</li>\n";
        }
        */
?>        
    <ul>
</div>
<script type="text/javascript" src="<?php echo CMS_INSTALL_URL; ?>lib/js/mootools-1.2.1-core.js"></script>
<script type="text/javascript" src="<?php echo CMS_INSTALL_URL; ?>lib/js/mootools-1.2-more.js"></script>
<script type="text/javascript">
var BatchRunning;
var ListItem,ListItems;
function UncheckAll(){
    $$('.GalleryCheck').each(function(el){
        el.checked = false;
    });
}
function CheckAll(){
    $$('.GalleryCheck').each(function(el){
        el.checked = true;
    });
}

function startResizing(){
    ListItems = $$('.GalleryID').length;
    if (!$chk(ListItem)){
        ListItem = 0;
    }
    BatchRunning = true;
    $('StartButton').setStyle('display','none');
    $('StopButton').setStyle('display','inline');
    
    kickOffResizing(ListItem);
}

function kickOffResizing(item){
    if (item < ListItems){
	    var el = $$('.GalleryID')[item];
	    var GalleryID = el.innerHTML;
        if ($('GalleryCheck' + GalleryID).checked){
            resizeGallery(GalleryID,$('GalleryOffset' + GalleryID).innerHTML,5);
        }
        else{
            ListItem++;
            kickOffResizing(ListItem);
        }
    }
    else{
        stopResizing();
    }
}

function stopResizing(){
    BatchRunning = false;
    $('StartButton').setStyle('display','inline');
    $('StopButton').setStyle('display','none');
}

function resizeGallery(GalleryID,Offset,ImagesPerCall){
    if ($('Status' + GalleryID).innerHTML == ''){
        $('Status' + GalleryID).innerHTML = 'Starting...';
    }
    var AjaxURL = '<?php echo CMS_INSTALL_URL.'lib/packages/Common/ajax.php?package=Gallery&'.session_name().'='.htmlspecialchars(session_id()); ?>';
    Offset = Offset.toInt();
    var req = new Request({
       method: 'get',
       url: AjaxURL,
       data: { 'action' : 'resize' , 'id' : GalleryID , 'offset' : Offset , 'limit' : ImagesPerCall},
       onComplete: function(response) {
            if (!response){
                return;
            }
            var json = $H(JSON.decode(response, true));
            if (json.get('result') != 'success' && false) {
                alert(response);
            }
            else if (json.get('message') != null && false) {
                alert(json.get('message'));
            }
            else{
				var _total;
				if (typeof json.get('data').retain == 'object'){
	                Offset += json.get('data').retain.images_processed;
					_total = json.get('data').retain.total_images;
				}
				else{
	                Offset += json.get('images_processed');
					_total = json.get('total_images');
				}
                $('Status' + GalleryID).innerHTML = "Done " + Offset + " images";
                $('GalleryOffset' + GalleryID).innerHTML = Offset;
                if (Offset >= _total){
                    if (json.get('result') != 'success'){
                        $('Status' + GalleryID).innerHTML += response;
                    }
                    else{
                        $('Status' + GalleryID).innerHTML+= ". All done.";
                    }
                    ListItem++;
                    kickOffResizing(ListItem);
                }
                else{
                    if (BatchRunning){
                        resizeGallery(GalleryID,Offset,ImagesPerCall);
                    }
                }
            }
       }
    }).send();
}

</script>