<?php

    include_once(PACKAGE_DIRECTORY.'Common/UserFunction.php');
    
    class ImportExport__UserFunctions extends UserFunction{
        
        function ImportExport__UserFunctions(){
            global $Bootstrap;
            
            $this->setPackage('ImportExport');
            $this->setFunctionParameters(); // See below
                        
        }

        function ImportExport__UserPaint($parms,$package,&$smarty){
            global $Bootstrap;

			switch ($parms['subject']){
            default:
                $return = "Don't know how to paint $subject";
            }
        
            return $return;
        }
    
        function ImportExport__UserAjax($parms,$package,&$smarty){
			$Bootstrap = Bootstrap::getBootstrap();
			
			if (!isset($_SESSION)){
				session_start();
			}
			switch ($parms['subject']){
			case 'export':
				if ($Bootstrap->packageExists($parms['export_package'])){
					$ExportPackage = $Bootstrap->usePackage($parms['export_package']);
				}
				else{
					return array('result' => 'failure', 'message' => 'Could not instantiate package "'.$parms['export_package'].'"');
				}
				if ($parms['export_package_sub'] != ""){
					$ExporterName = $ExportPackage->extraPortables[$parms['export_package_sub']]['exporter'];
				}
				else{
			        $ExporterName = $ExportPackage->package_name."Exporter";
				}
			    if (class_exists($ExporterName)){
				    $Exporter = new $ExporterName();
				}
				else{
					return array('result' => 'failure', 'message' => 'No exporter class found for "'.$ExporterName.'"');
				}
				
				$Options = $_SESSION['exportOptions'];
				$Filters = $_SESSION['exportFilters'];
				if (!is_array($Options)){
					return array('result' => 'failure', 'message' => 'Could not find the export options.  Please start again.');
				}
				
				if (isset($parms['limit'])){
					$Options['Limit'] = $parms['limit'];
				}
				if (isset($parms['offset'])){
					$Options['Offset'] = $parms['offset'];
				}

				$RowsExported = $Exporter->performExport($Options,$Filters);
				
				$return = array('data' => $RowsExported);
				
				$return['message'].= $Bootstrap->dump_debug();
				$Bootstrap->clear_debug();
				
				return $return; //, 'message' => memory_get_peak_usage());
				break;
			case 'import':
				//$Bootstrap->start_timer('Batch Import');
				//$Bootstrap->snapshot_memory('Reached Import');
				if ($Bootstrap->packageExists($parms['import_package'])){
					$ImportPackage = $Bootstrap->usePackage($parms['import_package']);
				}
				else{
					return array('result' => 'failure', 'message' => 'Could not instantiate package "'.$parms['import_package'].'"');
				}
				if (!isset($_SESSION['import_options']['synch_start_time'])){
					$_SESSION['import_options']['synch_start_time'] = time();
				}
				if ($parms['import_package_sub'] != ""){
					$ImporterName = $ImportPackage->extraPortables[$parms['import_package_sub']]['importer'];
				}
				else{
			        $ImporterName = $ImportPackage->package_name."Importer";
				}
			    if (class_exists($ImporterName)){
				    $Importer = new $ImporterName();
				}
				else{
					return array('result' => 'failure', 'message' => 'No importer class found for "'.$ImporterName.'"');
				}
				
				if (!isset($_SESSION['import_file'])){
					return array('result' => 'failure', 'message' => 'The Import File is not set.  Please start again');
				}
				
				// The import process is a communication between this program and the client page
				// via AJAX.  
				
				// The input from AJAX could be any of these:
				// - limit : an integer to say how many imports to do per batch
				// - offset : an integer to say what record to start this current batch on
				// - dupes : 'update' if program should update dupes, 'synchronize' if program should entirely replace DB with import file, 'ignore' or blank if program should ignore them
				// - stop_on_message : a flag to tell the program to return to the client on Duplicate Found messages (to ask for confirmation of what to do)
				
				// The output back to the client an associative array with the following:
				// - result :  'failure' or 'success'
				// - message : HTML Markup of the logger messages
				// - data[code]   : 0 = IMPORT_OK, 1 = IMPORT_ERROR - i.e. bad record, 2 = IMPORT_DUPLICATE - i.e. record exists
				// - data[valid] : number of valid records imported
				// - data[invalid] : number of invalid records encountered
				// - data[duplicates] : number of duplicate records encountered and ignored or updated or synchronized (depending on client setting)
				// - data[offset] : line offset to next record
				
				
				$Importer->setImportFile($_SESSION['import_file']);
				if (isset($parms['offset'])){
					$_SESSION['import_options']['offset'] = (isset($parms['offset']) ? $parms['offset'] : 0);
				}
				if (isset($parms['limit'])){
					$Importer->import_batch_size = $parms['limit'];
				}
				$Importer->setImportOptions($_SESSION['import_options']);
				//$Bootstrap->snapshot_memory('Before Prepare File');
				//$Bootstrap->start_timer('Prepare File');
				$csvArray =& $Importer->csvPrepareFile();
				//$Bootstrap->stop_timer('Prepare File');
				//$Bootstrap->snapshot_memory('After Prepare File');

				if ($parms['dupes'] == 'synchronize' and $_SESSION['import_options']['synchronize'] !== true){
					$_SESSION['import_options']['synchronize'] = true;
					$parms['dupes'] = 'update';
				}
				
				if ($parms['dupes'] == 'update_once'){ // not fully implemented
					$IgnoreDuplicates = 'once'; 
				}
				else{
					$IgnoreDuplicates = $parms['dupes'] != 'update';
				}
				//$Bootstrap->start_timer('Perform Import');
		        $Import = $Importer->performImport($csvArray,$_SESSION['import_field_assign'],$IgnoreDuplicates,$parms['stop_on_message'] != 'false'); // Returns on message
				if (!isset($_SESSION['import_unchanged_ids'])){
					session_register('import_unchanged_ids');
					$_SESSION['import_unchanged_ids'] = array();
				}
				$_SESSION['import_unchanged_ids'] = array_merge($_SESSION['import_unchanged_ids'],$Importer->unchanged_ids);
				//$Bootstrap->stop_timer('Perform Import');
				//$Bootstrap->snapshot_memory('After Perform Import');
				$return = array();
				if (!isset($Import['Code'])){
					$Import['Code'] = '';
				}
				switch ($Import['Code']){
				case IMPORT_DUPLICATE:
				case IMPORT_ERROR:
					$return['result'] = 'failure';
					$return['code'] = $Import['Code'];
					break;
				default:
					$return['result'] = 'success';
					$return['code'] = 0;
					break;
				}
				
				$return['valid'] = $Import['Valid'];
				$return['invalid'] = $Import['Invalid'];
				$return['duplicates'] = $Import['Duplicates'];
				if (isset($Import['Offset']) and $Import['Offset'] != ""){
					$return['offset'] = $Import['Offset'];
				}
				elseif (isset($csvArray['recordOffsets']) and is_array($csvArray['recordOffsets']) and count($csvArray['recordOffsets'])){
					$return['offset'] = array_pop($csvArray['recordOffsets']);
				}
				if (isset($Import['NextOffset']) and $Import['NextOffset'] != ""){
					$return['next_offset'] = $Import['NextOffset'];
				}
				
				// Reverse the order of messages in the Logger, so it's last in first out in appearance back on the client.
				$Importer->logger->_messages = array_reverse($Importer->logger->_messages);
				//$Bootstrap->stop_timer('Batch Import');
				$return['message'] = $Bootstrap->dump_timers();
				$return['message'].= $Bootstrap->dump_debug();
				$Bootstrap->clear_debug();
				$return['message'].= $Importer->logger->toSimpleString();
				
				return $return; 
				break;
			case 'update_field_choice':
				if ($parms['parameter'] == 'ignore'){
					$key = array_search(intval($parms['field']),$_SESSION['import_field_assign']);
					unset($_SESSION['import_field_assign'][$key]);
				}
				else{
					$_SESSION['import_field_assign'][$parms['parameter']] = intval($parms['field']);
				}
				break;
			case 'wrapup':
				if ($_SESSION['import_options']['synchronize'] === true){
					if ($Bootstrap->packageExists($parms['import_package'])){
						$ImportPackage = $Bootstrap->usePackage($parms['import_package']);
					}
					else{
						return array('result' => 'failure', 'message' => 'Could not instantiate package "'.$parms['import_package'].'"');
					}
					if ($parms['import_package_sub'] != ""){
						$ImporterName = $ImportPackage->extraPortables[$parms['import_package_sub']]['importer'];
					}
					else{
				        $ImporterName = $ImportPackage->package_name."Importer";
					}
				    if (class_exists($ImporterName)){
					    $Importer = new $ImporterName();
					}
					else{
						return array('result' => 'failure', 'message' => 'No importer class found for "'.$ImporterName.'"');
					}
					$Importer->unchanged_ids = $_SESSION['import_unchanged_ids'];
					$result = $Importer->synchronize($_SESSION['import_options']['synch_start_time']);
				}
				unset($_SESSION['import_field_assign']);
			    unset($_SESSION['import_package']);
				if ($_SESSION['import_file'] != "" and file_exists($_SESSION['import_file'])){
					@unlink($_SESSION['import_file']);
				}
			    unset($_SESSION['import_file']);
				unset($_SESSION['import_page_offsets']);
				unset($_SESSION['import_options']);
				unset($_SESSION['import_total_records']);
				unset($_SESSION['import_unchanged_ids']);
				break;
			default:
				return $package->paint($parms,$smarty);
				break;
			}
		}
		
        function setFunctionParameters(){    
			// I'll do this later if ever implementing in a non-Wordpress CMS
        }
  }

?>