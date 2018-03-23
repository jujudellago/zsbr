<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage_artists');
	global $delete,$edit;
	$delete = $Bootstrap->makeAdminURL($Package,'delete_artist');
	$edit = $Bootstrap->makeAdminURL($Package,'update_artist');

	include_once(PACKAGE_DIRECTORY."Common/ObjectLister.php");
	$ObjectLister = new ObjectLister();

	$ArtistContainer = new ArtistContainer();
	$Artists = $ArtistContainer->getAllArtists();
	
	$ObjectLister->addColumn(vocabulary('Artist').' Name (<a href=\''.$edit.'&from=manage_artists\'>Add New '.vocabulary('Artist').'</a>)','displayArtistName','75%');
    $smarty->assign('ObjectListCycleColors',array("#ffffff","#eeeeff"));
	$ObjectLister->addColumn('','displayNavigation','25%');
	$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	$smarty->assign('ObjectList', $ObjectLister->getObjectList($Artists));
	$smarty->assign('ObjectEmptyString',"There are currently no ".pluralize('Artist')." defined.  <a href='".$edit."'>Click to Add a ".vocabulary('Artist')."</a>");
		 	
	function displayArtistName($Object){
                global $edit;
	 	if (is_a($Object,'Parameterized_Object')){
 	 		$ret = "<a href='".$edit."&id=".$Object->getParameter('ArtistID')."&from=manage_artists'>".$Object->getParameter('ArtistFullName')."</a>";
                        return $ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
	
	function displayNavigation($Object){
		global $edit, $delete;
	 	if (is_a($Object,'Parameterized_Object')){
	 		$_ret =  "<a href='".$edit."&id=".$Object->getParameter('ArtistID')."&from=manage_artists'>edit</a> ";
	 		$_ret.=  "<a href='".$delete."&id=".$Object->getParameter('ArtistID')."'>delete</a> ";
	 		return $_ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
?>
<br>
<?php	$smarty->display('admin_listing.tpl');	?>
