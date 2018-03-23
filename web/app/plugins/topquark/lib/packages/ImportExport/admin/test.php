<?php
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }

	$ExportPackage = $Bootstrap->usePackage('FestivalApp');
	$ExporterName = 'ScheduleExporter';
	$Exporter = new $ExporterName();
	$Filters = $_SESSION['exportFilters'];
	$Options = $_SESSION['exportOptions'];
	$Options['Limit'] = 100;
	$Options['Offset'] = 0;
	$Exporter->performExport($Options,$Filters);
	return;
    
    session_start();

	// First, do a little clean up.
	if (empty($_POST['file_name'])){
	#	session_unregister('exportOptions');
	#	session_unregister('exportFilters');
		unset($_SESSION['exportOptions']);
		unset($_SESSION['exportFilters']);
	}
	
	$Bootstrap->addPackagePageToAdminBreadcrumb($Bootstrap->usePackage('ImportExport'),'manage');
	$ExportPackage_name = ($_GET['p'] != "" ? urldecode($_GET['p']) : $_POST['p']);
	$ExportPackage_sub = ($_GET['sub'] != "" ? urldecode($_GET['sub']) : $_POST['sub']);
	
	if (!$Bootstrap->packageExists($ExportPackage_name)){
	    header("Location:".$Bootstrap->makeAdminURL($Package,'manage'));
	    exit();
	}
	else{
		$ExportPackage = $Bootstrap->usePackage($ExportPackage_name);
	}
	$tmp_smarty_dir = $smarty->template_dir;
	$smarty->template_dir = dirname(__FILE__)."/smarty/";
	$Bootstrap->addURLToAdminBreadcrumb(null,'Export '.$ExportPackage->package_title);
	
    $smarty->assign('pathToCommon',CMS_INSTALL_URL.'lib/packages/Common/');

    if ($ExportPackage_sub != ""){
		$ExporterName = $ExportPackage->extraPortables[$ExportPackage_sub]['exporter'];
	}
	else{
		$ExporterName = $ExportPackage->package_name."Exporter";
	}
    if (class_exists($ExporterName)){
	    $Exporter = new $ExporterName();
	}
	else{
		die('I cannot instantiate the exporter for '.$ExporterName);
	}
	
	$Exporter->setMessageList($MessageList);
	
	if ($_GET['del'] != ""){
		// Trying to delete an existing export
		$ExistingExports = $Exporter->getExistingExports();
		if (array_key_exists($_GET['del'],$ExistingExports)){
			@unlink($Exporter->dir.$ExistingExports[$_GET['del']]);
		}
	}
		
	if (!empty($_POST['file_name'])){
		// Okay, ready to do the export
		$Filters = array();
		$FilterParms = $Exporter->getFilterParameters();
		foreach (array_keys($FilterParms) as $Parm){
			if ($_POST[$Parm] != ""){
				$Filters[$Parm] = $_POST[$Parm];
			}
		}
		
		$smarty->assign('Count',$Exporter->getCount($Filters));
		$Options = array();
		$FileName = stripslashes($_POST['file_name']).'.csv';
		$Options['FileName'] = $FileName;
		$Options['Encoding'] = $_POST['encoding'];
		$Options['ExportFieldNames'] = ($_POST['headerRow'] == 'true');
		$Options['Delimiter'] = ($_POST['delimiter'] == 'tab' ? "\t" : ",");
		session_register('exportOptions');
		session_register('exportFilters');
		$_SESSION['exportOptions'] = $Options;
		$_SESSION['exportFilters'] = $Filters;
		$smarty->assign('ExportPackageName',$ExportPackage_name);
		$smarty->assign('ExportPackageSub',$ExportPackage_sub);
		$smarty->assign('Package',$Package);
		$smarty->assign('AjaxURL',CMS_INSTALL_URL.'lib/packages/Common/ajax.php?'.session_name()."=".htmlspecialchars(session_id()));
		$smarty->assign('CMS_INSTALL_URL',CMS_INSTALL_URL);
		$smarty->assign('FileName',$FileName);
    	$smarty->display("data.export2.tpl");		
	}
	else{
		// in the first phase
		
    	$smarty->assign('ExportType',($ExportPackage_sub != "" ? $ExportPackage_sub : $ExportPackage->package_title));
    	$smarty->assign('Exporter',$Exporter);
    	$smarty->assign('FilterParms',$Exporter->getFilterParameters());
    	$smarty->assign('messages',$MessageList->toSimpleString());
		$smarty->assign('encodings',$Exporter->encodings);
		$smarty->assign('delimiters',$Exporter->delimiters);
		$smarty->assign('default_encoding',$Exporter->default_encoding);
		$smarty->assign('default_delimiter',$Exporter->default_delimiter);
		$smarty->assign('ExistingExports',$Exporter->getExistingExports());
		$smarty->assign('ExportDirectory',$Exporter->getExportDirectoryURL());
		$smarty->assign('thisURL',$Bootstrap->makeAdminURL($Package,'export').'&amp;p='.$ExportPackage_name.($ExportPackage_sub != "" ? "&amp;sub=$ExportPackage_sub" : ""));
    	$smarty->display("data.export.tpl");		
	}

	$smarty->template_dir = $tmp_smarty_dir;
?>
