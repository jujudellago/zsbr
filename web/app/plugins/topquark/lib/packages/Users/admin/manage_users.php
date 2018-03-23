<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	global $edit,$delete;
	$edit = $Bootstrap->makeAdminURL($Package,'update');
	$delete = $Bootstrap->makeAdminURL($Package,'delete');
	
	include_once(PACKAGE_DIRECTORY."/Common/ObjectLister.php");
	
	
	$ObjectLister = new ObjectLister();

	$UserContainer = new UserContainer();
	$Users = $UserContainer->getAllUsers();
	
	$ObjectLister->addColumn('Login Name','displayUserLogin','20%');
	$ObjectLister->addColumn('Real Name','displayUserName','35%');
	$ObjectLister->addColumn('Email','displayUserEmail','35%');
	$ObjectLister->addColumn('','displayNavigation','10%');
    $smarty->assign('ObjectListCycleColors',array("#ffffff","#eeeeff"));
	$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	$smarty->assign('ObjectList', $ObjectLister->getObjectList($Users));
	$smarty->assign('ObjectEmptyString',"There are currently no Users defined.");
		 	
	function displayUserLogin($Object){
        global $edit;
	 	if (is_a($Object,'Parameterized_Object')){
	 	    $ret = "<a href='$edit&name=".$Object->getParameter('UserID')."'>".$Object->getParameter('UserName')."</a>";
            return $ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
	
	function displayUserName($Object){
	 	if (is_a($Object,'Parameterized_Object')){
	 	    $ret = $Object->getParameter('UserRealName');
            return $ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}	
	
	function displayUserEmail($Object){
	 	if (is_a($Object,'Parameterized_Object')){
	 	    $ret = $Object->getParameter('UserEmail');
            return $ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}	
	
	function displayNavigation($Object){
        global $edit,$delete;
	 	if (is_a($Object,'Parameterized_Object')){
	 		$_ret =  "<a href='$edit&name=".$Object->getParameter('UserID')."'>permissions</a> ";
	 		return $_ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
	
?>
<?php	$smarty->display('admin_listing.tpl');	?>