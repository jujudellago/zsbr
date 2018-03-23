<?php

class Exporter{
	
	var $source_encoding = 'UTF-8';
	var $encodings = array('UTF-8' => 'UTF-8', 'UTF-16' => 'Unicode', 'ISO-8859-1' => 'Latin 1', 'macintosh' => 'MacRoman');
	var $delimiters = array('comma' => 'Comma', 'tab' => 'Tab');
	var $default_encoding = 'macintosh';
	var $default_delimiter = 'comma';
	var $ignore_parameters;
	var $extra_parameters;
	
	function Exporter(){
        $this->logger = new MessageList();
		$this->filter_parms = array();
		$this->custom_filter_parms = array();
	}
	
    function setContainer($Container,$Prime = true){
        if (is_a($Container,'ObjectContainer')){
            $this->Container = $Container;
        }
        elseif (class_exists($Container)){
            $this->Container = new $Container();
        }
        else{
            return PEAR::raiseError('You must pass in a valid ObjectContainer object');
        }
        if (is_a($this->Container,'ObjectContainer') and $Prime){
            $this->primeImporter();
        }
        return true;
    }
    
    function getContainer(){
        return $this->Container;
    }
    
    function primeImporter(){
        if (!is_a($this->getContainer(),'ObjectContainer')){
            return PEAR::raiseError('You must first prime the Importer with an ObjectContainer');
        }
        $this->Columns = $this->Container->colname;
    }
    
    function getColumns(){
        return $this->Columns;
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

	function addFilterParameter($parms){
		if (!is_array($parms)){
			$parms = array($parms);
		}
		foreach ($parms as $parm){
			if (!in_array($parm,$this->filter_parms)){
				$this->filter_parms[] = $parm;
			}
		}
	}
	
	function getFilterParameters(){
		if (!count($this->filter_parms)){
			return $this->getCustomFilterParameters();
		}
		$return = array();
		foreach ($this->filter_parms as $parm){
			$return[$parm] = $this->Container->getAllValues($parm);
		}
		return $return;
	}
	
	function addCustomFilterParameter($parms){
		if (!is_array($parms)){
			$parms = array($parms);
		}
		foreach ($parms as $parm_key => $parm){
			if (!in_array($parm,$this->custom_filter_parms)){
				$this->custom_filter_parms[$parm_key] = $parm;
			}
		}
	}
	
	function getCustomFilterParameters(){
		return $this->custom_filter_parms;
	}
	
	function getPrettyName($parm){
		if ($this->parameterPrefix != "" and ($pretty_parm = str_replace($this->parameterPrefix,'',$parm)) != ""){
			$return = $pretty_parm;
		}
		else{
			$return = $parm;
		}
		
		return trim(preg_replace("/([A-Z])/",' $1',$return));
	}
	
	function setExportDirectory($dir = ""){
		if ($dir == ""){
			if (defined('CMS_ASSETS_DIRECTORY')){
				$this->dir = DOC_BASE.CMS_ASSETS_DIRECTORY.'exports/';
				if (!file_exists($this->dir) and !mkdir($this->dir)){
					die('Unable to create export directory at '.$this->dir);
				}
			}
			else{
				$this->dir = dirname(__FILE__).'/exports/';
			}
		}
		else{
			$this->dir = $dir;
		}		
	}	
	
	function getExportDirectory($dir = ""){
		if (!isset($this->dir)){ $this->setExportDirectory(); }
		return $this->dir;
	}	
	
	function getExportDirectoryURL($dir = ""){
		if (!isset($this->dir)){ $this->setExportDirectory(); }
		if (function_exists('get_bloginfo')){
			return str_replace(DOC_BASE,get_bloginfo('wpurl').'/',$this->dir);
		}
		else{
			// Assume the directory is somewhere in the /lib structure
			return preg_replace("/^.*\/lib\//",CMS_INSTALL_URL."lib/",$this->dir);
		}
	}	
	
	function getExistingExports(){
		if (!isset($this->dir)){ $this->setExportDirectory(); }
		
		// clean up old files in the Import Directory
		$return = array();
		if (is_dir($this->dir)){
			$dh = opendir($this->dir);
	        while (($file = readdir($dh)) !== false) {
				if (!in_array($file,array('.','..'))){
					$return[substr(md5($file),3,6)] = $file;
				}
	        }
	        closedir($dh);
		}
		return $return;
	}
	
	function performExport($Options = array(),$Filters = array()){
		// Options expects:
		// ['FileName'], ['Encoding'], ['Delimiter'], ['ExportFieldNames']
		// optional: ['Limit'], ['Offset']
		
		// Filters expects:
		// [ParmName] => FilterValue
		$wc = $this->createWhereClause($Filters);
		if ($Options['Limit'] != ""){
			$Objects = $this->Container->getLimitedObjects($wc,$Options['Limit'],$Options['Offset'],$this->sort_parms,$this->sort_dir);
			if ($Options['Offset'] > 0){
				$Options['ExportFieldNames'] = false;
				$FileMode = "a";
			}
			else{
				$FileMode = "w";
			}
		}
		else{
			$Objects = $this->Container->getAllObjects($wc,$this->sort_parms,$this->sort_dir);
			$FileMode = "w";
		}
		if ($this->manufacturer != ""){
		    $MethodName = $this->manufacturer;			
		}
		else{
		    $MethodName = 'manufacture'.$this->parameterPrefix;
		}
		if(method_exists(get_class($this->Container),$MethodName)){
			$Objects = $this->Container->$MethodName($Objects);
		}
		
		if (!isset($this->dir)){ $this->setExportDirectory(); }
		$handle = fopen($this->dir.$Options['FileName'],$FileMode);
		
		$First = true;
		$RowsExported = 0;
		foreach ($Objects as $Object){
			if (is_a($Object,'Parameterized_Object')){
				$this->massageData($Object);
				if ($First and $Options['ExportFieldNames']){
					$keys = array_keys($Object->getParameters());
					$FieldNames = array();
					foreach ($keys as $key){
						if (!in_array($key,$this->ignore_parameters)){
							if (($tmp = str_replace($this->parameterPrefix,'',$key)) != "ID"){
								$key = $tmp;
							}
							$FieldNames[$key] = $key;
						}
					}
					fwrite($handle,$this->createCSVLine($FieldNames,$Options['Delimiter'],$Options['Encoding']));
				}
				$First = false;
				fwrite($handle,$this->createCSVLine($Object->getParameters(),$Options['Delimiter'],$Options['Encoding']));
			}
			$RowsExported++;
		}
		fclose($handle);
		return $RowsExported;
	}
	
	function getCount($Filters){
		$wc = $this->createWhereClause($Filters);
		$count = $this->Container->countAllObjects($wc);
		return $count;
	}
	
	function createWhereClause($Filters){
		$wc = new whereClause();
		if (is_a($this->Container,'FancyObjectContainer')){
			$wc->addCondition($this->Container->getLinkingWhereClause());
		}
		if (count($Filters)){
			foreach ($Filters as $key => $value){
				$wc->addCondition($this->Container->getColumnName($key).' = ?',$value);
			}
		}
		if (method_exists($this,'customizeWhereClause')){
			$this->customizeWhereClause($wc);
		}
		return $wc;
	}
	
	function createCSVLine($parms,$delimiter,$encoding){
		foreach ($parms as $key => $parm){
			if (in_array($key,$this->ignore_parameters)){
				unset($parms[$key]);
			}
		}
		if (is_array($this->parameter_order)){
			$new_parms = array();
			foreach ($this->parameter_order as $new_parm){
				if (array_key_exists($new_parm,$parms)){
					$new_parms[$new_parm] = $parms[$new_parm];
					unset($parms[$new_parm]);
				}
			}
			$parms = array_merge($new_parms,$parms);
		}
		$line = $this->quotejoin($parms,$delimiter);
		// We're adopting the "\r\n" protocol for line endings. 
		$line = preg_replace("/[\\\r]([^\\\n])/","\r\n".'$1',$line);
		return $this->encodeLine($line,$encoding)."\r\n";
	}
	
	function quotejoin($a,$delimiter = ','){
		// Creates a properly escaped string for a CSV export
		$encaser = '"';
		$encasable_chars = ",\"\n\r";
		$return = "";
		$first = true;
		foreach ($a as $v){
			if (!$first){
				$return.= $delimiter;
			}
			$first = false;
			if (is_array($v)){
				$v = implode(',',$v);
			}
			if ($v != "" and preg_match("/[".preg_quote($encasable_chars)."]/",$v)){
				$return.= $encaser.str_replace($encaser,$encaser.$encaser,$v).$encaser;
			}
			else{
				$return.= $v;
			}
		}
		return $return;
	}
	
	function encodeLine($line,$encoding){
		
		switch($encoding){
		case 'UTF-8':
		case 'UTF-16':
		case 'UTF-16LE':
		case 'UTF-16BE':
			break;
		case 'macintosh':
		// this map was derived from the differences between the MacRoman and UTF-8 Charsets 
		// Reference: 
		//   - http://www.alanwood.net/demos/macroman.html
			$convmap = array(
				256, 304, 0, 0xffff,
				306, 337, 0, 0xffff,
				340, 375, 0, 0xffff,
				377, 401, 0, 0xffff,
				403, 709, 0, 0xffff,
				712, 727, 0, 0xffff,
				734, 936, 0, 0xffff,
				938, 959, 0, 0xffff,
				961, 8210, 0, 0xffff,
				8213, 8215, 0, 0xffff,
				8219, 8219, 0, 0xffff,
				8227, 8229, 0, 0xffff,
				8231, 8239, 0, 0xffff,
				8241, 8248, 0, 0xffff,
				8251, 8259, 0, 0xffff,
				8261, 8363, 0, 0xffff,
				8365, 8481, 0, 0xffff,
				8483, 8705, 0, 0xffff,
				8707, 8709, 0, 0xffff,
				8711, 8718, 0, 0xffff,
				8720, 8720, 0, 0xffff,
				8722, 8729, 0, 0xffff,
				8731, 8733, 0, 0xffff,
				8735, 8746, 0, 0xffff,
				8748, 8775, 0, 0xffff,
				8777, 8799, 0, 0xffff,
				8801, 8803, 0, 0xffff,
				8806, 9673, 0, 0xffff,
				9675, 63742, 0, 0xffff,
				63744, 64256, 0, 0xffff,
				);
			break;
		case 'ISO-8859-1':
			$convmap = array(256,10000,0,0xffff);
			break;
		}
		if (is_array($convmap) and function_exists('mb_encode_numericentity')){
			$line = mb_encode_numericentity($line,$convmap,$this->source_encoding);
		}
		if ($encoding != $this->source_encoding){
			return iconv($this->source_encoding,$encoding.'//IGNORE',$line);
		}
		else{
			return $line;
		}
	}
	
	function setParameterOrder($order){
		$this->parameter_order = $order;
	}
	
	function getParameterOrder(){
		return $this->parameter_order; 
	}
	
}

?>