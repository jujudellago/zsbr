<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'serialize');
	$manage = $Bootstrap->makeAdminURL($Package,'manage');
	if ($Bootstrap->packageExists('flamplayer')){
	    $Bootstrap->usePackage('flamplayer');
	}
	
	// this may take a while, best to remove time limit restrictions
	set_time_limit(0);
	
	$FestivalContainer = new FestivalContainer();
	$Festivals = $FestivalContainer->getAllFestivals();
	$FestivalYears = array();
	foreach ($Festivals as $Festival){
	    $FestivalYears[$Festival->getParameter('FestivalYear')] = $Festival->getParameter('FestivalYear');
	}
	
    if ($_REQUEST[YEAR_PARM] != ""){
        $Year = urldecode($_REQUEST[YEAR_PARM]);
    }

	if (!is_dir($Package->etcDirectory) and !mkdir($Package->etcDirectory)){
		die('Unable to create etc directory at '.$Package->etcDirectory);
	}

    if ($Year != ""){

        if ($_REQUEST['action'] == 'unpublish'){
            foreach (glob($Package->etcDirectory."{$Year}*.txt") as $filename) {
                unlink ($filename);
            }
            $Content.= vocabulary('Festival')." Schedule information successfully unpublished.  <a href='javascript:history.go(-1)'>Head back</a>";
			if ($Package->enable_cache){
				// We're using the cache, so we need to remove the cache pages associate with this term
				$Package->emptyCache();
			}
        }
        else{
			// to do....
        }
    }    
        

?>
<div id="serialization_status" style="width:50%;margin:auto;"><?php if ($Content != ''){ echo $Content; }?></div><div id="serialization_feedback"  style="width:50%;margin:auto;"></div>
<?php if ($_REQUEST['action'] != 'unpublish') : ?>
<script type="text/javascript">
	jQuery(function(){		
		var interval;
		runStep = function(step,substep){
		    var AjaxURL = '<?php echo CMS_INSTALL_URL.'lib/packages/Common/ajax.php?package=FestivalApp&'.session_name().'='.htmlspecialchars(session_id()); ?>';
			var parms = {
				'subject':'serialize_festival',
				'_year':'<?php echo $Year; ?>',
				'step':step
			};
			if (substep != undefined){
				parms['substep'] = substep;
			}
			jQuery.get(AjaxURL,parms,
				function(_data){
					var data;
					try{
						data = jQuery.parseJSON(_data)
						jQuery('#serialization_status').html(jQuery('#serialization_status').html()+data.message);
						jQuery('#serialization_feedback').html('');
						switch (data.result){
						case 'stop':
							// all done
							stopTheProcess();
							break;
						case 'next_step':
							launchStep(step + 1);
							break;
						case 'this_step_again':
							launchStep(step,data.substep);
							break;
						}
					}
					catch(e){
						stopTheProcess();
						jQuery('#serialization_feedback').html(_data);
					}
				}
			);
		}
		
		launchStep = function(step,substep){
			// First, we'll just get the info
		    var AjaxURL = '<?php echo CMS_INSTALL_URL.'lib/packages/Common/ajax.php?package=FestivalApp&'.session_name().'='.htmlspecialchars(session_id()); ?>';
			var parms = {
				'subject':'serialize_festival',
				'_year':'<?php echo $Year; ?>',
				'step':step,
				'getinfo':true
			};
			if (substep != undefined){
				parms['substep'] = substep;
			}
			jQuery.get(AjaxURL,parms,
				function(_data){
					var data;
					try{
						data = jQuery.parseJSON(_data);
						switch (data.result){
						case 'stop':
							// all done
							jQuery('#serialization_status').html(jQuery('#serialization_status').html()+data.message);
							stopTheProcess();
							break;
						case 'next_step':
							jQuery('#serialization_status').html(jQuery('#serialization_status').html()+data.message);
							launchStep(step + 1);
							break;
						default:
							if (interval == undefined){
								interval = setInterval(function(){
									var current = jQuery('#serialization_feedback').html();
									current = current.replace('. . . . . . . . . . . . . . . ',' | ');
									jQuery('#serialization_feedback').html(current + '. ');
								},100);
							}
							jQuery('#serialization_feedback').html(data.message);
							runStep(step,substep);
							break;
						}
					}
					catch(e){
						stopTheProcess();
						jQuery('#serialization_feedback').html(_data);
					}
				}
			);
		}
		
		stopTheProcess = function(){
			jQuery('#serialization_feedback').html('');
			clearInterval(interval);
		}
		
		launchStep(1);
		
		
	});
</script>
<?php endif;?>