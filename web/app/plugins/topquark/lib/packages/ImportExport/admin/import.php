<?php
	// To Do: get the field assignments to persist through page navigation

    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }

	$Bootstrap->start_timer('Overall');
	$Bootstrap->snapshot_memory('Enter Import');
	if (!is_array($_SESSION)){
	    session_start();
	}

	$Bootstrap->addPackagePageToAdminBreadcrumb($Bootstrap->usePackage('ImportExport'),'manage');
	$ImportPackage_name = ($_GET['p'] != "" ? urldecode($_GET['p']) : $_POST['p']);
	$ImportPackage_sub = ($_GET['sub'] != "" ? urldecode($_GET['sub']) : $_POST['sub']);
	
	if (!$Bootstrap->packageExists($ImportPackage_name)){
		resetSessionVariables();
	    header("Location:".$Bootstrap->makeAdminURL($Package,'manage'));
	    exit();
	}
	$thisURL = $Bootstrap->makeAdminURL($Package,'import')."&amp;p=".$ImportPackage_name;
	if ($ImportPackage_sub != ""){
		$thisURL.= "&amp;sub=".$ImportPackage_sub;
	}
	$ImportPackage = $Bootstrap->usePackage($ImportPackage_name);
	$ImportWhat = ($ImportPackage_sub != "" ? $ImportPackage_sub : $ImportPackage->package_title);
	$Bootstrap->addURLToAdminBreadcrumb($thisURL,'Import '.$ImportWhat);
	
	$tmp_smarty_dir = $smarty->template_dir;
	$smarty->template_dir = dirname(__FILE__)."/smarty/";
    $smarty->assign('pathToCommon',CMS_INSTALL_URL.'lib/packages/Common/');
    $smarty->assign('thisURL',$thisURL);
    
    if ($ImportPackage_sub != ""){
		$ImporterName = $ImportPackage->extraPortables[$ImportPackage_sub]['importer'];
	}
	else{
        $ImporterName = $ImportPackage->package_name."Importer";
	}
    if (class_exists($ImporterName)){
	    $Importer = new $ImporterName();
	}
	else{
		die('I cannot instantiate the importer for '.$ImporterName);
	}
	
	$Importer->setMessageList($MessageList);
	
	$fname = 'csvfile';
	
	// Okay, we need to address how this is done.  
	// This is a state machine.  Step 1 will be to figure out what state we're in.
	$State = 1;
	if (!empty($_FILES[$fname]['tmp_name'])){
		// They've uploaded a file - move to state 2
		$State = 2;
	}
	elseif (isset($_GET['offset']) and $_GET['offset'] != ""){
		if (is_array($_SESSION['import_page_offsets'])){
			$_SESSION['import_options']['offset'] = $_SESSION['import_page_offsets'][$_GET['offset'] - 1];
		} 
		$State = 3;
	}
	elseif (isset($_REQUEST['preview'])){
		$State = 4;
	}
	if ($State > 2 and (!isset($_SESSION['import_file']) or !file_exists($_SESSION['import_file']))){
		$State = 1;
	}
	if (isset($_REQUEST['cancel'])){
		// They hit the cancel button
		$State = 1;
		unset($_GET['offset']);
	}
	
	
	switch ($State){
	case 1:
		// Initial state - display upload file screen
		
		// Unset some session variables

		resetSessionVariables();

		// Smarty Variables
    	$smarty->assign('ImportType',$ImportWhat);
    	$smarty->assign('maxSize',getMaxPostSize(true));
    	$smarty->assign('messages',$MessageList->toSimpleString());
		$smarty->assign('encodings',$Importer->encodings);
		$smarty->assign('delimiters',$Importer->delimiters);
		$smarty->assign('default_encoding',$Importer->default_encoding);
		$smarty->assign('default_delimiter',$Importer->default_delimiter);
    	$smarty->display("data.import.tpl");
		break;
	case 2:
		// To account for a BIG file to be uploaded, we'll essentially have to set up transactions
		// Unset some session variables
		resetSessionVariables();
		
		$Importer->addUploadedImportFile($_FILES[$fname]);
		
		$Options = array();
		$Options['headerRow'] = $_REQUEST['headerRow'];
		$Options['ignoreRows'] = ($_REQUEST['ignoreRows'] == 'true' ? $_REQUEST['ignoreRowsValue'] : 0);
		$Options['encoding'] = $_REQUEST['encoding'];
		$Options['delimiter'] = $_REQUEST['delimiter'];
		$Importer->setImportOptions($Options);
		session_register('import_file');
		
		session_register('import_options');
		$_SESSION['import_file'] = $Importer->getImportFile();
		$_SESSION['import_options'] = $Importer->getImportOptions();
		$_SESSION['import_options']['offset'] = 0;
		$State = 3;
	case 3:
		$Importer->setImportFile($_SESSION['import_file']);
		$Importer->setImportOptions($_SESSION['import_options']);
		
		$Bootstrap->snapshot_memory('Before Prepare');
		$Bootstrap->start_timer('Prepare File');
		
    	$csvArray =& $Importer->csvPrepareFile();
		if (isset($csvArray['fieldAssign']) and !isset($_SESSION['import_field_assign'])){
			session_register('import_field_assign');
			$_SESSION['import_field_assign'] = $csvArray['fieldAssign'];
		}
		else{
			$csvArray['fieldAssign'] = $_SESSION['import_field_assign'];
		}
		
		session_register('import_page_offsets');
		session_register('import_total_records');
		if (!is_array($_SESSION['import_page_offsets']) and !isset($_SESSION['import_total_records'])){
			$info = $Importer->getCSVFileInfo();
			if (is_array($info['recordOffsets'])){
				$_SESSION['import_page_offsets'] = $info['recordOffsets'];
			}
			$_SESSION['import_total_records'] = $info['totalRecords'];
		}
    	if (is_array($csvArray)) {
            $smarty->assign('numFields',$csvArray['numFields']);
			if (is_array($_SESSION['import_page_offsets']) and count($_SESSION['import_page_offsets'])){
				$smarty->assign('OffsetPage',((isset($_GET['offset']) and $_GET['offset'] <= count($_SESSION['import_page_offsets'])) ? $_GET['offset'] : 1));
			}
            $smarty->assign('csvArray',$csvArray);
            $smarty->assign('fields',$Importer->getParameters());
            $smarty->assign('TotalRecords',$_SESSION['import_total_records']);

			$smarty->assign('AjaxURL',CMS_INSTALL_URL.'lib/packages/Common/ajax.php?'.session_name()."=".htmlspecialchars(session_id()));
			$smarty->assign('CMS_INSTALL_URL',CMS_INSTALL_URL);
    	    $smarty->display("data.import2.tpl");
    	}
    	else{
    	    $smarty->assign('messages',$Importer->logger->toSimpleString());
    	    $smarty->display("data.import.tpl");
    	}
		break;
	case 4:
		$_SESSION['import_field_assign'] = $_POST['field'];
		$Importer->setImportFile($_SESSION['import_file']);
		$Importer->setImportOptions($_SESSION['import_options']);
		if (isset($Importer->limit)){
			$smarty->assign('limit',$Importer->limit);
		}
		else{
			$smarty->assign('limit',100);
		}
		$smarty->assign('Count',$_SESSION['import_total_records']);
		$smarty->assign('ImportPackageName',$ImportPackage->package_name);
		$smarty->assign('ImportPackageSub',$ImportPackage_sub);
		$smarty->assign('Package',$Package);
		$smarty->assign('AjaxURL',CMS_INSTALL_URL.'lib/packages/Common/ajax.php?'.session_name()."=".htmlspecialchars(session_id()));
		$smarty->assign('CMS_INSTALL_URL',CMS_INSTALL_URL);
	    $smarty->display("data.import3.tpl");
		break;
	}
	
	
	function resetSessionVariables(){
		unset($_SESSION['import_field_assign']);
		if (isset($_SESSION['import_file']) and $_SESSION['import_file'] != "" and file_exists($_SESSION['import_file'])){
			@unlink($_SESSION['import_file']);
		}
	    unset($_SESSION['import_file']);
		unset($_SESSION['import_page_offsets']);
		unset($_SESSION['import_options']);
		unset($_SESSION['import_total_records']);
		unset($_SESSION['import_unchanged_ids']);
	}
	
	$Bootstrap->stop_timer('Overall');
	echo $Bootstrap->dump_timers();
	
	$smarty->template_dir = $tmp_smarty_dir;
	
?>
