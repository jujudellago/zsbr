<?php
define('IMPORT_ERROR',1);
define('IMPORT_DUPLICATE',2);

class Importer{
	
	var $target_encoding = 'UTF-8';
	var $encodings = array('UTF-8' => 'UTF-8', 'UTF-16' => 'Unicode', 'ISO-8859-1' => 'Latin 1', 'macintosh' => 'MacRoman');
	var $delimiters = array('comma' => 'Comma', 'tab' => 'Tab');
	var $default_encoding = 'UTF-16';
	var $default_delimiter = 'tab';
	var $import_batch_size = 10;
	var $extra_parameters;
	var $ignore_parameters;
	var $import_options;

    function Importer(){
        $this->logger = new MessageList();
		$this->unchanged_ids = array();
    }

    function setContainer($Container,$Prime = true){
		if (is_string($Container) and class_exists($Container)){
            $this->Container = new $Container();
		}
        elseif (is_a($Container,'ObjectContainer') or is_a($Container,'FancyObjectContainer')){
            $this->Container = $Container;
        }
        else{
            return PEAR::raiseError('You must pass in a valid ObjectContainer object');
        }
        if ((is_a($this->Container,'ObjectContainer') or is_a($this->Container,'FancyObjectContainer')) and $Prime){
            $this->primeImporter();
			if ($this->parameterPrefix != ""){
				$ExtraParameters = array();
				foreach ($this->Container->colname as $parm => $col_name){
					$test = str_replace($this->parameterPrefix,'',$parm);
					if (!in_array($test,$this->getColumns()) and !in_array($test,$this->getExtraParameters())){
						$ExtraParameters[] = str_replace($this->parameterPrefix,'',$parm);
					}
				}
				$this->addExtraParameter($ExtraParameters);
			}
        }
        return true;
    }
    
    function getContainer(){
        return $this->Container;
    }
    
    function primeImporter(){
        if (!is_a($this->getContainer(),'ObjectContainer') and !is_a($this->getContainer(),'FancyObjectContainer')){
            return PEAR::raiseError('You must first prime the Importer with an ObjectContainer');
        }
        $this->Columns = $this->Container->colname;
    }
    
    function getColumns(){
        return $this->Columns;
    }
    
    function setUniqueKey($parms){
        // Sets the names of the parameters that define a unique key for the objects in the container
        // This will not necessarily be the ID Key.  It might be a combination of other fields that are enough
        // to say this record already exists, don't import it
        if (!is_array($parms)){
            $parms = array($parms);
        }
        $this->uniqueKeyParms = $parms;
    }
    
    function getUniqueKey(){
        return $this->uniqueKeyParms;
    }
    
    function setDisplayKey($parms){
        // Sets the names of the parameters that define a unique key for the objects in the container
        // This will not necessarily be the ID Key.  It might be a combination of other fields that are enough
        // to say this record already exists, don't import it
        if (!is_array($parms)){
            $parms = array($parms);
        }
        $this->displayKeyParms = $parms;
    }
    
    function getDisplayKey(){
        return $this->displayKeyParms;        
    }
    
    function setSQLKey($parm){
        $this->SQLKeyParm = $parm;
    }
    
    function getSQLKey(){
        return $this->SQLKeyParm;
    }    
    
    
    function getObject($ObjectUniqueKey = array(),$sort_field = "",$sort_dir = ""){
        if (is_a($ObjectUniqueKey,'Parameterized_Object')){
            $UniqueKey = array();
            foreach ($this->uniqueKeyParms as $parm){
                $UniqueKey[] = $ObjectUniqueKey->getParameter($parm);
            }
            $ObjectUniqueKey = $UniqueKey;
        }

        $wc = new whereClause();
        foreach ($this->getUniqueKey() as $key => $parm){
            $wc->addCondition($this->Container->getColumnName($parm)." = ?",$ObjectUniqueKey[$key]);
        }
        
		if (PEAR::isError($Objects = $this->Container->getAllObjects($wc,$sort_field, $sort_dir))){ return $Objects;}
		if ($Objects){
		    // Only return the first Object
		    return current($Objects);
		}
		else{
		    return null;
		}
    }
    
    function objectExists($ObjectUniqueKey = array()){
        if (is_a($ObjectUniqueKey,'Parameterized_Object')){
            $UniqueKey = array();
            foreach ($this->uniqueKeyParms as $parm){
                $UniqueKey[] = $ObjectUniqueKey->getParameter($parm);
            }
            $ObjectUniqueKey = $UniqueKey;
        }
        if (is_a($this->getObject($ObjectUniqueKey),'Parameterized_Object')){
            return true;
        }
        else{
            return false;
        }
    }
    
    function ignoreParameter($Parameter){
        if (!is_array($Parameter)){
            $Parameter = array($Parameter);
        }
        
        if (!is_array($this->ignore_parameters)){
            $this->ignore_parameters = array();
        }
        foreach ($Parameter as $parm){
            if (!in_array($parm,$this->ignore_parameters)){
                $this->ignore_parameters[] = $parm;
            }
        }
    }
    
    function getParameters(){
        $return = array();
        foreach ($this->getColumns() as $parm => $column){
            if (!in_array($parm,$this->ignore_parameters)){
                $return[] = $parm;
            }
        }
        
        foreach ($this->getExtraParameters() as $parm){
            $return[] = $parm;
        }
        
        sort($return);
        
        return $return;
    }
    
    function addExtraParameter($Parameter){
        if (!is_array($Parameter)){
            $Parameter = array($Parameter);
        }
        
        if (!is_array($this->extra_parameters)){
            $this->extra_parameters = array();
        }
        foreach ($Parameter as $parm){
            if (!in_array($parm,$this->extra_parameters)){
                $this->extra_parameters[] = $parm;
            }
        }
    }
        
    function getExtraParameters(){
        if (!is_array($this->extra_parameters)){
            $this->extra_parameters = array();
        }
        return $this->extra_parameters;
    }
    
    function setMessageList(& $MessageList){
        $this->logger = & $MessageList;
    }

	function addUploadedImportFile($file_widget){
		if (!isset($this->dir)){ $this->setImportDirectory(); }
		
		// clean up old files in the Import Directory
		if (is_dir($this->dir)){
			$dh = opendir($this->dir);
	        while (($file = readdir($dh)) !== false) {
				if (!in_array($file,array('.','..'))){
					@unlink($this->dir.$file);
				}
	        }
	        closedir($dh);
		}
		if (!is_writable($this->dir)){
			die('The directory '.$this->dir.'must be writable.  Please change the permissions.');
		}
		if (is_uploaded_file($file_widget['tmp_name']) and move_uploaded_file($file_widget['tmp_name'],$this->dir.$file_widget['name'])){
			@chmod($this->dir.$file_widget['name'],0666);
			$this->setImportFile($this->dir.$file_widget['name']);
			return $this->getImportFile();
		}
		return false;
	}
	
	function setImportDirectory($dir = ""){
		if ($dir == ""){
			$this->dir = dirname(__FILE__).'/imports/';
		}
		else{
			$this->dir = $dir;
		}		
	}	
	
	function setImportFile($filename){
		$this->import_file = $filename;
	}
	
	function getImportFile(){
		return $this->import_file;
	}

	function setImportOptions($options){
		if (!$this->import_options){
			$this->import_options = array();
		}
		$this->import_options = array_merge($this->import_options,$options);
	}
	
	function getImportOptions(){
		return $this->import_options;
	}
	
	function getCSVFileInfo(){
		// Returns an array of Page Offsets and the total number of records to be imported
		return $this->csvPrepareFile(true);
	}

    // Reads a CSV file and returns an array. Returns false if the file is invalid.
    //  Output array {  array ('numFields' => {maximum_number_of_field} 'csvRecords' => array([field1],[field2],[...]), 'fieldAssign' => array(...) { [0]=>  string(5) "email" [1]=>  string(2) "13" ...} }
    function & csvPrepareFile($JustInfo = false) {  //& $uploadFile, $headerRow = false, $ignoreRows = 0, $encoding = 'UTF-8', $delimiter = 'comma') {
		extract($this->getImportOptions());
		
		switch ($delimiter){
		case 'tab':
			$delim = "\t";
			break;
		case 'comma':
		default:
			$delim = ",";
			break;
		}

    	// the array which will be returned
		if (!$JustInfo){
	    	$outArray = array ('numFields' => 0, 'csvRecords' => array());			
		}
		else{
	    	$outArray = array ('recordOffsets' => array(0), 'totalRecords' => 0);			
		}
        
        if ($headerRow){         
            $validFields = $this->getParameters();
        }

		$Bootstrap = Bootstrap::getBootstrap();
		ini_set('auto_detect_line_endings',true); // This is so that files created on a MAC (using just '/r' as the line feed) get read properly
		//$Bootstrap->snapshot_memory('Before file_get_contents');
		// To handle Multi-Byte, the file() function doesn't work, so, we'll have to split it ourselves
		$fh = fopen($this->getImportFile(), 'r'); 
		
		if ($encoding != $this->target_encoding){
			stream_filter_append($fh, 'convert.iconv.'.$encoding.'/'.$this->target_encoding);
		}
		$lines_read = 0;
		//$Bootstrap->snapshot_memory('Before seek');
		if ($offset){
			while ($fh and !feof($fh) and $lines_read < $offset){
				fgets($fh);
				//stream_get_line($fh,10000,"\n");
				$lines_read++;
			}
		}
		//$Bootstrap->snapshot_memory('After seek');
    	$fail = 0;
		$line_num = 0;
		while ($fh and !feof($fh) and (!isset($outArray['csvRecords']) or count($outArray['csvRecords']) < $this->import_batch_size)){
			$line = fgets($fh);
			//$line = stream_get_line($fh,10000,"\n");
			$lines_read++;

			if ($line !== false){
				
				if (trim($line) == ""){
					continue;
				}
			}
			else{
				continue;
			}

			// Step 1 is to convert it into the encoding for this Importer
			// (note, this is now done with the stream_filter_append above)
			//$line = iconv($encoding,$this->target_encoding,$line);

			// Step 2 is to decode any numerics  (i.e. &#8623;)
			// Turns out there are some installs of PHP that don't have MultiByte installed.  
			if (function_exists('mb_decode_numericentity')){
				$convmap = array(256,10000,0,0xffff);
				$line = mb_decode_numericentity($line,$convmap,$this->target_encoding);
			}
			
			// Next, see if we're in an open quotes situation
			if(isset($previous_line) and $previous_line != ''){
				$line = $previous_line.$line;
			}
			else{
				$record_line_num = $lines_read;
			}
		    
    	    if ($offset == 0 and $line_num < $ignoreRows){
    	        continue;
    	    }
            elseif ($offset == 0 and ($line_num == $ignoreRows && $headerRow)){         
				if (!$JustInfo){
	                $fields = $this->quotesplit($line,$delim);
	                // Try to find field names, if any exist
					$outArray['fieldAssign'] = array();
	                foreach($fields as $fieldNumber => $field){
	                    if (($key = array_search($field,$validFields)) !== false){
	                        $outArray['fieldAssign'][$key] = $fieldNumber;
	                    }
	                }
				}
            }
            else{
        		if ($fail > 3) {
        			$this->logger->addMessage('Maximum failures reached. CSV processing aborted.',MESSAGE_TYPE_ERROR);
        			break;
        		}
        		
        		// Only do the import if there is something on the line
        		if (preg_match("/[A-Za-z0-9\PL]+/",$line)){
                    // Allow line breaks to appear (this should only affect 'bigtext' fields)
                    // Note, they get properly escaped later in the import process.  this just stores
                    // them in a benign session variable
                    $line = str_replace('\\n',"\n",$line);
                    $line = str_replace('\\r',"\r",$line);
                    
    		        $fields = $this->quotesplit($line,$delim);
					if ($fields == 'quote_open'){
						// This line ended with a quote being open.  Try again with the next line
						$previous_line = $line;
						continue;
					}
    		        $numFields = count($fields);

            		// check to see if any fields were read in
            		if (!$numFields || $numFields < 1) {
            			$this->logger->addMessage(sprintf('Line #%s could not be processed.',$line_num +1),MESSAGE_TYPE_ERROR);
            			$fail++;
            			continue; // skip this line, as it has failed sanity check.
            		}
        		
            		if (!$JustInfo and $numFields > $outArray['numFields']){
            		    $outArray['numFields'] = $numFields;
            		}

					if (!$JustInfo){
						// Note: the array key is 0-based elsewhere in the program from legacy code, so I'll keep it 0-based.
		    		    $outArray['csvRecords'][$record_line_num - 1] = $fields;
					}
					else{
						$outArray['totalRecords']++;
					}
					if (!isset($outArray['totalRecords']) or $outArray['totalRecords'] % $this->import_batch_size === 0){
						$outArray['recordOffsets'][] = $lines_read;
					}
    		    }
             }

			 $previous_line = "";
			 $line_num++;
			
		}
		
		 if ($JustInfo and $outArray['totalRecords'] % $this->import_batch_size === 0){
			if (is_array($outArray['recordOffsets']) and count($outArray['recordOffsets'])){
				array_pop($outArray['recordOffsets']);
			}
		 }
		fclose($fh);		
		//$Bootstrap->snapshot_memory('After file_get_contents');
        
    	// return false if there were errors
    	if ($fail){
            $this->logger->addMessage('Could not make sense of the CSV file.  Please check the formatting and try again',MESSAGE_TYPE_ERROR);
    		return false;
    	}

    	return $outArray;
    }

    function massageData($Object){
        // overridden by inheriting class
    }
    
    function postImport($Object){
        // overridden by inheriting class
    }

	function postPerformImport(){
        // overridden by inheriting class
	}
	
	function synchronize($StartTime){
				
        // can be overridden by inheriting class
		// But, this will look for a column with a parameter including 'ModifiedTimestamp'
		// and then delete all records with the timestamp before then. 
		$this->primeImporter();
		$timestamp = date("Y-m-d H:i:s",$StartTime);
		
		// Sanity check (no security hole here)
		if (!$timestamp or $timestamp >= time()){
			// invalid timestampe or one after right now (duh)
			return false;
		}
		
		$parms = array_keys($this->getColumns());
		foreach ($parms as $parm){
			if (strpos($parm,'ModifiedTimestamp') !== false){
				$wc = new whereClause();
				$wc->addCondition($this->Columns[$parm].' < ?',date("Y-m-d H:i:s",$StartTime));
				break;
			}
		}
		
		if (is_a($wc,'whereClause')){
			if (is_array($this->unchanged_ids)){
				$wc->addCondition($this->Columns[$this->getSQLKey()].' NOT IN (?'.str_repeat(',?',count($this->unchanged_ids) - 1).')',$this->unchanged_ids);
			}
			return $this->Container->deleteObject($wc);
		}
	}
	
    function previewImport($csvArray,$FieldAssignments,$DoTheImport = false,$IgnoreDuplicates = true,$ReturnOnMessage = false){
		$Bootstrap = Bootstrap::getBootstrap();
        // csvArray is in the same format as that returned from csvPrepareFile above
        $Fields = $this->getParameters();

		$return = array('Valid' => 0, 'Invalid' => 0, 'Duplicates' => 0);
        $Duplicates =& $return['Duplicates'];
        $Valid =& $return['Valid'];
        $Invalid =& $return['Invalid'];

        foreach ($csvArray['csvRecords'] as $row_num => $row){
            $Object = new Parameterized_Object();
            foreach ($row as $col => $data){
                if ($FieldAssignments[$col] != 'ignore'){
                    $Object->setParameter($Fields[$FieldAssignments[$col]],$data);
                }
            }
			if ($this->parameterPrefix != ""){
		        foreach ($this->getExtraParameters() as $parm){
					if ($Object->getParameter($parm) != "" and $Object->getParameter($this->parameterPrefix.$parm) == ""){
						$Object->setParameter($this->parameterPrefix.$parm,$Object->getParameter($parm));
					}
		        }
			}
            if (PEAR::isError($r = $this->massageData($Object)) or $r === false){
                $Invalid++;
                $this->logger->addMessage('The record on line '.($row_num+1).' could not be imported.  '.(PEAR::isError($r) ? $r->getMessage() : ''),MESSAGE_TYPE_ERROR);
				if (true or $ReturnOnMessage){ // Always return on Error
					// Find the key to the next record
					$return['Offset'] = key($csvArray['csvRecords']);
					$return['Code'] = IMPORT_ERROR;
					return $return;
				}
            }
            elseif (is_a($_Object = $this->getObject($Object),'Parameterized_Object')){ // $this->objectExists($Object)){
                $Duplicates++;
                if (!$DoTheImport or $IgnoreDuplicates === true){
				    $UniqueKey = array();
				    if (is_array($this->getDisplayKey())){
				        $UseUniqueKey = $this->getDisplayKey();
				    }
				    else{
				        $UseUniqueKey = $this->getUniqueKey();
				    }
				    foreach ($UseUniqueKey as $key => $parm){
				        $UniqueKey[] = $Object->getParameter($parm);
				    }
                    $this->logger->addMessage('The record on line '.($row_num+1).' ('.implode(", ",$UniqueKey).') already exists in the database.<div id="DuplicateAction"></div>',MESSAGE_TYPE_WARNING);
					if ($ReturnOnMessage){
						$return['Offset'] = $row_num;
						$return['NextOffset'] = key($csvArray['csvRecords']);
						$return['Code'] = IMPORT_DUPLICATE;
						return $return;
					}
                }
                else{
					// In case someone is maintaining a master list
					// from an excel file, then we want to have
					// a way of knowing if an object has _actually_
					// changed.  This is the code that does that.  
					// First, we'll check the imported object agains
					// the database object.  If nothing there has changed
					// we'll offer a way for an subclassing importer to
					// define a "hasChanged" method to allow for more
					// complicated changes to be caught
					$objectHasChanged = false;
					foreach ($_Object->params as $key => $value){
						if (isset($Object->params[$key]) and $value != $Object->params[$key]){
							$objectHasChanged = true;
							break;
						}
					}
					if (!$objectHasChanged and method_exists($this,'objectHasChanged')){
						$objectHasChanged = $this->objectHasChanged($Object,$_Object);
					}
					if (!$objectHasChanged){
					    $UniqueKey = array();
					    if (is_array($this->getDisplayKey())){
					        $UseUniqueKey = $this->getDisplayKey();
					    }
					    else{
					        $UseUniqueKey = $this->getUniqueKey();
					    }
					    foreach ($UseUniqueKey as $key => $parm){
					        $UniqueKey[] = $_Object->getParameter($parm);
					    }
					    $this->logger->addMessage('The record on line '.($row_num+1).' ('.implode(", ",$UniqueKey).') has not changed from what is in the database.  It was ignored.',MESSAGE_TYPE_WARNING);
						$this->unchanged_ids[] = $_Object->getParameter($this->getSQLKey());
						$this->postImport($_Object);
						continue;
					}
					//$Bootstrap->start_timer('Updating Object (sans getObject)');
					// Get the object and update it
					//$_Object = $this->getObject($Object);
					$_Object->params_saved = $Object->params_saved;
					//$Bootstrap->start_timer('saveParameters');
					$_Object->saveParameters();
					//$Bootstrap->stop_timer('saveParameters');
					//$Bootstrap->start_timer('setParameters');
					$_Object->setIDParameter($this->getSQLKey());
					foreach ($Object->getParameters() as $key => $value){
					    $_Object->setParameter($key,$value);
					}
					//$Bootstrap->stop_timer('setParameters');
					//$Bootstrap->start_timer('sanitizeObject');
					$this->sanitizeObject($_Object);
					//$Bootstrap->stop_timer('sanitizeObject');
					//$Bootstrap->start_timer('MySQL Upate Object');
					if (method_exists($this->Container,'setTimestamp')){
						call_user_func_array(array($this->Container,'setTimestamp'),array(&$_Object));
					}
					if (method_exists($this,'updateObject')){
	                    $r = $this->updateObject($_Object);
					}
					else{
	                    $r = $this->Container->updateObject($_Object);
					}
					//$Bootstrap->stop_timer('MySQL Upate Object');
					if (PEAR::isError($r)){
					    $this->logger->addMessage('The record on line '.($row_num+1).' was not imported: '.$r->getMessage(),MESSAGE_TYPE_ERROR);
					    $Duplicates--;
					    $Invalid++;
						if (true or $ReturnOnMessage){ // Always return on Error
							// Find the key to the next record
							$return['Offset'] = key($csvArray['csvRecords']);
							$return['Code'] = IMPORT_ERROR;
							return $return;
						}
					}
					else{
						//$Bootstrap->start_timer('postImport');
						$this->postImport($_Object);
						//$Bootstrap->stop_timer('postImport');
					    $UniqueKey = array();
					    if (is_array($this->getDisplayKey())){
					        $UseUniqueKey = $this->getDisplayKey();
					    }
					    else{
					        $UseUniqueKey = $this->getUniqueKey();
					    }
					    foreach ($UseUniqueKey as $key => $parm){
					        $UniqueKey[] = $_Object->getParameter($parm);
					    }
						//$Duplicates--;
						$Valid++;
					    $this->logger->addMessage('<strong>Updated</strong>: '.implode(", ",$UniqueKey).' - <a href="'.$this->viewImportedRecordURL.$_Object->getParameter($this->getSQLKey()).'" target="_blank">view</a> <a href="'.$this->editImportedRecordURL.$_Object->getParameter($this->getSQLKey()).'" target="_blank">edit</a>');
					}
					//$Bootstrap->stop_timer('Updating Object (sans getObject)');
                }
            }
            else{
                $Valid++;
                if ($DoTheImport){
                    $this->sanitizeObject($Object);
		            $Object->setIDParameter($this->getSQLKey());
					if (method_exists($this->Container,'setTimestamp')){
						call_user_func_array(array($this->Container,'setTimestamp'),array(&$Object));
					}
					if (method_exists($this,'addObject')){
	                    $r = $this->addObject($Object);
					}
					else{
	                    $r = $this->Container->addObject($Object);
					}
                    if (PEAR::isError($r)){
                        $this->logger->addMessage('The record on line '.($row_num+1).' was not imported: '.$r->getMessage(),MESSAGE_TYPE_ERROR);
                        $Valid--;
                        $Invalid++;
						if (true or $ReturnOnMessage){ // Always return on Error
							// Find the key to the next record
							$return['Offset'] = key($csvArray['csvRecords']);
							$return['Code'] = IMPORT_ERROR;
							return $return;
						}
                    }
                    else{
						$this->postImport($Object);
                        $UniqueKey = array();
                        if (is_array($this->getDisplayKey())){
                            $UseUniqueKey = $this->getDisplayKey();
                        }
                        else{
                            $UseUniqueKey = $this->getUniqueKey();
                        }
                        foreach ($UseUniqueKey as $key => $parm){
                            $UniqueKey[] = $Object->getParameter($parm);
                        }
                        $this->logger->addMessage('<strong>Imported</strong>: '.implode(", ",$UniqueKey).' <a href="'.$this->viewImportedRecordURL.$Object->getParameter($this->getSQLKey()).'" target="_blank">view</a> <a href="'.$this->editImportedRecordURL.$Object->getParameter($this->getSQLKey()).'" target="_blank">edit</a>');
                    }
                }
            }
            
			if ($IgnoreDuplicates == 'once'){
				$IgnoreDuplicates = true;
			}
        }
        return array('Valid' => $Valid, 'Invalid' => $Invalid, 'Duplicates' => $Duplicates);
    }
    
    function performImport($csvArray,$FieldAssignments,$IgnoreDuplicates = true,$ReturnOnMessage = false){
        $return = $this->previewImport($csvArray,$FieldAssignments,true,$IgnoreDuplicates,$ReturnOnMessage);
		$this->postPerformImport();
		return $return;
    }
    
    function sanitizeObject(& $object){
        $ValidColumns = $this->getColumns();
        foreach ($object->getParameters() as $key => $value){
            if (!array_key_exists($key,$ValidColumns)){
                unset($object->params[$key]);
            }
        }
    }
    
    /**
     * quotesplit: for putting CSV-Like data into an array --> author: moritz @ php.net 
     * 
     * ie:
     * 1 , 3, 4
     * -> [1,3,4]
     * 
     * one; two;three
     * -> ['one','two','three']
     * 
     * "this is a string", "this is a string with , and ;", 'this is a string with quotes like " these', "this is a string with escaped quotes \" and \'.", 3
     * -> ['this is a string','this is a string with , and ;','this is a string with quotes like " these','this is a string with escaped quotes " and '.',3]
     */

    function quotesplit($s,$delim = ",") {
    	$r = Array ();
    	$p = 0;
    	$l = strlen($s);
    	while ($p < $l) {
			$quote_open = false;
    		while (($p < $l) && (strpos(" \r\n", $s[$p]) !== false))
    			$p ++;
    		if ($s[$p] == '"') {
				$quote_open = true;
    			$p ++;
    			$q = $p;
    			while (($p < $l) && ($s[$p] != '"' or $s[$p+1] == '"')) {
    				if ($s[$p] == '\\' or $s[$p] == '"') {
    					$p += 2;
    					continue;
    				}
    				$p ++;
    			}
				if ($p < $l){
					$quote_open = false;
				}
    			$r[] = stripslashes(str_replace('""','"',substr($s, $q, $p - $q)));
    			$p ++;
    			while (($p < $l) && (strpos(" \r\n", $s[$p]) !== false))
    				$p ++;
    			$p ++;
    		} else
    			if ($s[$p] == "'") {
					$quote_open = true;
    				$p ++;
    				$q = $p;
    				while (($p < $l) && ($s[$p] != "'" or $s[$p+1] == "'")) {
    					if ($s[$p] == '\\' or $s[$p] == "'") {
    						$p += 2;
    						continue;
    					}
    					$p ++;
    				}
					if ($p < $l){
						$quote_open = false;
					}
    				$r[] = stripslashes(str_replace("''","'",substr($s, $q, $p - $q)));
    				$p ++;
    				while (($p < $l) && (strpos(" \r\n", $s[$p]) !== false))
    					$p ++;
    				$p ++;
    			} else {
    				$q = $p;
    				while (($p < $l) && (strpos($delim, $s[$p]) === false)) {
    					$p ++;
    				}
    				$r[] = stripslashes(trim(substr($s, $q, $p - $q)));
    				while (($p < $l) && (strpos(" \r\n", $s[$p]) !== false))
    					$p ++;
    				$p ++;
    			}
    	}
		if ($quote_open){
			return "quote_open";
		}
    	return $r;
    }
	    
}
?>