<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'printables');
	global $edit,$delete;
	$edit = $Bootstrap->makeAdminURL($Package,'edit_printable');
	$delete = $Bootstrap->makeAdminURL($Package,'delete_printable');

	include_once(PACKAGE_DIRECTORY."Common/ObjectLister.php");
	
	$ObjectLister = new ObjectLister();
	
	$LetterContainer = new LetterContainer();
	$AllLetters = $LetterContainer->getAllLetters();

	$ObjectLister->addColumn('Printable Name (<a href=\''.$edit.'\'>Add New Printable</a>)','displayPrintableName','75%');
    $smarty->assign('ObjectListCycleColors',array("#ffffff","#eeeeff"));
	$ObjectLister->addColumn('','displayNavigation','25%');
	$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	$smarty->assign('ObjectList', $ObjectLister->getObjectList($AllLetters));
	$smarty->assign('ObjectEmptyString',"There are currently no printables defined.  <a href='".$edit."'>Click to Add a Printable</a>");
		 	
	function displayPrintableName($Object){
	    global $edit;
	 	if (is_a($Object,'Parameterized_Object')){
 	 		$ret = "<a href='".$edit."&amp;letter=".urlencode($Object->getParameter('LetterID'))."'>".$Object->getParameter('LetterName')."</a>";
                        return $ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}
	
	function displayNavigation($Object){
	    global $delete,$edit;
	 	if (is_a($Object,'Parameterized_Object')){
 	 		$_ret =  "<a href='".$edit."&amp;letter=".urlencode($Object->getParameter('LetterID'))."'>edit</a>";
	 		$_ret.=  " <a href='".$delete."&amp;id=".$Object->getParameter('LetterID')."'>delete</a> ";
	 		return $_ret;
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}


?>
<?php	$smarty->display('admin_listing.tpl');	?>