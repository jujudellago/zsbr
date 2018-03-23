<?php
	/*******************************************
	* import_export.php
	*
	* Use this to import or export data.  It deals with 
	* CSV files (comma separated values)
	*
	********************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
	$Package = $Bootstrap->usePackage('ImportExport');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');

	include_once(dirname(__FILE__)."/../../Common/ObjectLister.php");
	
	
	global $import,$export;
	$import = $Bootstrap->makeAdminURL($Package,'import');
	$export = $Bootstrap->makeAdminURL($Package,'export');
	
	$PortablePackages = $Package->getPortablePackages();
	
	$ObjectLister = new ObjectLister();	
	
	$ObjectLister->addColumn('Package','displayPackage','70%');
	$ObjectLister->addColumn('','displayImport','15%');
	$ObjectLister->addColumn('','displayExport','15%');

	$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	$smarty->assign('ObjectList', $ObjectLister->getObjectList($PortablePackages));
	$smarty->assign('ObjectListWidth', "30%");
	$smarty->assign('ObjectListCellPadding',3);
	$smarty->assign('ObjectEmptyString',"We could not find any packages that can be imported to or exported from.  Please make sure this Admin user is authorized for the packages you're looking to work with");
        
	function displayPackage($Object){
	 	if (is_a($Object['package'],'Package')){
			if (isset($Object['sub'])){
				return $Object['package']->package_title. ' - '. $Object['sub'];
			}
			else{
		 	    return $Object['package']->package_title;
			}
	 	}
	 	else{
	 		return "Invalid Object Passed: ".get_class($Object);
	 	}
	}

	function displayImport($Object){
	    global $import;
	    if ($Object['importable']){
			if (isset($Object['sub'])){
				$sub = "&amp;sub=".$Object['sub'];
			}
			else{
				$sub = "";
			}
	        return "<a href='$import&amp;p=".$Object['package']->package_name."$sub'>Import</a>";
	    }
	}

	function displayExport($Object){
	    global $export;
	    if ($Object['exportable']){
			if (isset($Object['sub'])){
				$sub = "&amp;sub=".$Object['sub'];
			}
			else{
				$sub = "";
			}
	        return "<a href='$export&amp;p=".$Object['package']->package_name."$sub'>Export</a>";
	    }
	}
?>
<?php	$smarty->display('admin_listing.tpl');	?>