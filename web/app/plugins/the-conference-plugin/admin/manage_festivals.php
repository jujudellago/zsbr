<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	global $manage, $edit, $delete, $reset, $schedule,$update_artist,$serialize;
	$manage = $Bootstrap->makeAdminURL($Package,'manage');
	$edit = $Bootstrap->makeAdminURL($Package,'update_festival');
	$delete = $Bootstrap->makeAdminURL($Package,'delete');
	$reset = $Bootstrap->makeAdminURL($Package,'reset');
	$schedule = $Bootstrap->makeAdminURL($Package,'edit_schedule');
	$update_artist = $Bootstrap->makeAdminURL($Package,'update_artist');
	$serialize = $Bootstrap->makeAdminURL($Package,'serialize');

	include_once(PACKAGE_DIRECTORY."Common/ObjectLister.php");
	
	$ObjectLister = new ObjectLister();

	$FestivalContainer = new FestivalContainer();
	
	if (isset($_GET['action']) and ($_GET['action'] == "publish" or $_GET['action'] == "unpublish") and $_GET[YEAR_PARM] != ""){
	    $Festival = $FestivalContainer->getFestival($_GET[YEAR_PARM]);
	    if (is_a($Festival,'Festival')){
	        if ($_GET['action'] == "publish"){
	            $Festival->setParameter('FestivalLineupIsPublished',1);
	        }
	        else{
	            $Festival->setParameter('FestivalLineupIsPublished',0);
	        }
	        $FestivalContainer->updateFestival($Festival);
	    }
	}
	
	$Festivals = $FestivalContainer->getAllFestivals();
	
	$per_page = 5;
	if (count($Festivals) > $per_page){
		$total_pages = ceil(count($Festivals) / $per_page);
		if ($_GET['p'] >= 1 and $_GET['p'] <= $total_pages){
			$current_page = intval($_GET['p']);
		}
		else{
			$current_page = 1;
		}
		$Festivals = array_slice($Festivals,$per_page * ($current_page - 1),$per_page);
	}
	else{
		$total_pages = 1;
		$current_page = 1;
	}
	
	$ObjectLister->addColumn(vocabulary('Festival').' Year','displayFestivalYear','15%');
	$ObjectLister->addColumn(pluralize('Artist').' (<a href=\''.$edit.'&add=new\'>Add New '.vocabulary('Festival').'</a>)','displayCurrentLineup');
	$ObjectLister->addColumn('&nbsp;','displayDeleteLink','10%');
	$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	$smarty->assign('ObjectList', $ObjectLister->getObjectList($Festivals));
	$smarty->assign('ObjectEmptyString',"There are currently no ".pluralize('Festival')." defined.  <a href='".$edit."'>Click to Add a ".vocabulary('Festival')."</a>");
		 	
	function displayFestivalYear($Object){
                global $edit, $schedule, $serialize,$manage;
	 	if (is_a($Object,'Parameterized_Object')){
                        $ret = "<div class=\"main-action\"><a href='".$edit."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."'>".$Object->getParameter('FestivalYear')."</a></div>";
                        $ret.= "<div class=\"row-actions\">";
						$ret.= "<a href='".$schedule."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."'>edit schedules</a>";
                        if (!$Object->getParameter('FestivalLineupIsPublished')){
                            $ret.= "<a href='".$manage."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."&action=publish'>publish ".strtolower(pluralize('Artist'))."</a>";
                        }
                        else{
                            $ret.= "<a href='".$manage."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."&action=unpublish'>unpublish ".strtolower(pluralize('Artist'))."</a>";
                        }
                        if (!$Object->getParameter('FestivalScheduleIsPublished')){
                            $ret.= "<a href='".$serialize."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."'>publish schedules</a>";
                        }
                        else{
                            $ret.= "<a href='".$serialize."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."&action=unpublish'>unpublish schedules</a>";
                            $ret.= "<a href='".$serialize."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."'>update published schedules</a>";
                        }
						$ret.= "</div>";
                        return $ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
	
	function displayCurrentLineup($Object){
        global $update_artist;
	 	if (is_a($Object,'Parameterized_Object')){
            $FestivalArtistContainer = new FestivalArtistContainer();
            $CurrentLineup = $FestivalArtistContainer->getLineup($Object->getParameter('FestivalYear'));
            if (is_a($CurrentLineup,'FestivalArtist')){
                $CurrentLineup = array($CurrentLineup->getParameter('ArtistID') => $CurrentLineup);
            }
            if (is_array($CurrentLineup)){
					if (($total = count($CurrentLineup)) > 30){
						$CurrentLineup = array_slice($CurrentLineup,0,20);
						$more = " <em>...plus ".($total - 20)." more</em>";
					}
                    $sep = "";
                    $ret = "";
                    foreach ($CurrentLineup as $Artist){
                            $ret.=$sep."<a href='".$update_artist."&id=".$Artist->getParameter('ArtistID')."&from=manage'>".$Artist->getParameter('ArtistFullName')."</a>";
                            $sep = ", ";
                    }
					if ($more != ""){
						$ret.= $sep.$more;
					}
            }
            else{
                    $ret = "<i>Not yet set</i>";
            }
	 		return $ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}	
	
	function displayDeleteLink($Object){
        global $delete, $reset;
	 	if (is_a($Object,'Parameterized_Object')){
	 	    return "<div class=\"row-actions\"><a href='".$reset."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."'>reset</a> <a href='".$delete."&".YEAR_PARM."=".urlencode($Object->getParameter('FestivalYear'))."'>delete</a></div>";
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	    
	}
?>
<?php	$smarty->display('admin_listing.tpl');	?>
<?php
	if ($total_pages > 1){
		echo "<div style='margin:5px auto;text-align:center;font-size:1.5em'>Page: ";
		for($p = 1; $p <= $total_pages; $p++){
			if ($p == $current_page){
				echo " <strong>$p</strong>";
			}
			else{
				echo " <a href='$manage&amp;p=$p'>$p</a>";
			}
		}
		echo "</div>\n";
	}
?>
