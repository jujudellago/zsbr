<?php

    define ('USER_AUTH_EVERYONE',0);
    define ('USER_AUTH_ADMIN',1);
    define ('USER_AUTH_WEBMASTER',2);
    define ('USER_AUTH_SUPERUSER',3);

    class Package{
	
		var $package_directory;
		var $isImportable;
		var $isExportable;
		var $extraPortables;
        
        function Package(){

        }
        
        function loadUserConf(){
			$userConf = PACKAGE_DIRECTORY.($this->package_directory != "" ? $this->package_directory : $this->package_name)."/conf.user.php";
			if (function_exists('apply_filters')){
				$userConf = apply_filters('set_user_conf_file_'.$this->package_name,$userConf);
			}
            if (file_exists($userConf)){
                include($userConf);
            }
            
			if (function_exists('apply_filters')){
				$void = apply_filters('addition_conf_'.$this->package_name,array(&$this));
			}
            $this->loadUserFunction();
        }
        
        function loadUserFunction(){
			$userFunction = PACKAGE_DIRECTORY.($this->package_directory != "" ? $this->package_directory : $this->package_name)."/user.functions.php";
			if (function_exists('apply_filters')){
				$userFunction = apply_filters('set_user_functions_file_'.$this->package_name,$userFunction);
			}
            if (file_exists($userFunction) and !class_exists($this->package_name."__UserFunctions")){
                include($userFunction);
            }
        }

		function getPackageURL(){
			$return = $this->getRelativeURLToPackageDirectory().($this->package_directory != "" ? $this->package_directory : $this->package_name);
			if (substr($return,-1) != '/'){
				$return.= '/';
			}
			return $return;
		}
		
		function getPackageDirectory(){
			$return = PACKAGE_DIRECTORY.($this->package_directory != "" ? $this->package_directory : $this->package_name);
			if (substr($return,-1) != '/'){
				$return.= '/';
			}
			return $return;
		}
        
        function getRelativeURLToPackageDirectory(){
            return str_replace(DOC_BASE,RELATIVE_BASE_URL,PACKAGE_DIRECTORY);
        }
        
		function writeCachedPage($parms,$content){
			if ($this->enable_cache and (is_dir($this->cache_directory) or $this->mkdir($this->cache_directory))){
				if (function_exists('apply_filters')){
					if (!apply_filters('enable_package_cache_'.get_class($this),true,$parms)){
						return false;
					}
				}
				$file = $this->generateCachedFileName($parms);
				$h = fopen ($this->cache_directory.$file,'a');
				if ($h){
					fwrite($h,$content);
					$timestamp = "\n<!-- Package cached page generated ".date('d M Y H:i:s')." -->\n";
					$timestamp.= "<!-- Parms : \n"; 
					foreach ($parms as $k => $v){
						$timestamp.= "             $k => $v\n";
					}
					$timestamp.= "-->
					";
					fwrite($h,$timestamp);
					fclose($h);
					return true;
				}
			}
			return false;
		}
		
		function mkdir($dir){
			$_dir = $dir;
			if (is_dir($_dir)){
				return true;
			}
			else{
				$_dir = preg_replace('/[\/\\\\]*$/','',$_dir);
				$basename = basename($_dir);
				$test_dir = preg_replace('/'.preg_quote($basename).'$/','',$_dir);
				var_dump($test_dir);
				if (is_dir($test_dir)){
					return mkdir($_dir);
				}
				else{
					return $this->mkdir($test_dir);
				}
			}
		}
		
		function getCachedPage($parms){
			if ($this->enable_cache and is_dir($this->cache_directory)){
				if (function_exists('apply_filters')){
					if (!apply_filters('enable_package_cache_'.get_class($this),true,$parms)){
						return false;
					}
				}
				$file = $this->generateCachedFileName($parms);
				if (file_exists($this->cache_directory.$file)){
					return file_get_contents($this->cache_directory.$file);
				}
			}
			return false;
		}
		
		function removeCachedPage($parms){
			if ($this->enable_cache and is_dir($this->cache_directory)){
				$file = $this->generateCachedFileName($parms);
				if (file_exists($this->cache_directory.$file)){
					return unlink($this->cache_directory.$file);
				}
			}
			return true;
		}
		
		function emptyCache(){
			if ($this->enable_cache and is_dir($this->cache_directory)){
				$cache_files = glob($this->cache_directory.'*.html');
				if (is_array($cache_files)){
					foreach (glob($this->cache_directory.'*.html') as $file){
						unlink($file);
					}
				}
			}
		}
		
		function generateKey($parms){
			ksort($parms);
			$key = md5(implode(',',array_keys($parms)).implode(',',$parms));
			return $key;
		}
		
		function generateCachedFileName($parms){
			return $this->generateKey($parms).'.html';
		}

        function Paint($parms = "",&$smarty){
			if (function_exists('apply_filters')){
				if ($result = apply_filters($this->package_name.'_paint',false,$parms,array(&$smarty))){
					return $result;
				}
			}
            if (!class_exists($this->package_name.'__UserFunctions')){
                return 'A class called '.$this->package_name.'__UserFunctions() must be declared in a file called "user.functions.php" in the '.$this->package_name.' package directory';
            }
            $ClassName = $this->package_name.'__UserFunctions';
            $FunctionName = $this->package_name.'__UserPaint';
            $ClassMethods = get_class_methods($ClassName);
            $Class = new $ClassName();
            foreach ($ClassMethods as $Method){
                if (strtolower($Method) == strtolower($FunctionName)){
                    // Statically call $ClassName::$FunctionName($parms,$this,$smarty);
                    //return call_user_func(array($ClassName,$FunctionName),$parms,$this,$smarty);  
                    return call_user_func_array(array($ClassName,$FunctionName),array($parms,$this,&$smarty));  
                }
            }
            return 'A method called '.$FunctionName.'($parms,$package,&$smarty) must be declared in a file called "user.functions.php" in the '.$this->package_name.' package directory';
        }
        
        function Retrieve($parms = "",&$smarty){
			if (function_exists('apply_filters')){
				if ($result = apply_filters($this->package_name.'_retrieve',false,$parms,array(&$smarty))){
					return $result;
				}
			}
            if (!class_exists($this->package_name.'__UserFunctions')){
                return 'A class called '.$this->package_name.'__UserFunctions() must be declared in a file called "user.functions.php" in the '.$this->package_name.' package directory';
            }
            $ClassName = $this->package_name.'__UserFunctions';
            $FunctionName = $this->package_name.'__UserRetrieve';
            $ClassMethods = get_class_methods($ClassName);
            foreach ($ClassMethods as $Method){
                if (strtolower($Method) == strtolower($FunctionName)){
                    // Statically call $ClassName::$FunctionName($parms,$this,$smarty);
                    return call_user_func_array(array($ClassName,$FunctionName),array($parms,$this,&$smarty));  
                }
            }
            return 'A method called '.$FunctionName.'($parms,$package,&$smarty) must be declared in a file called "user.functions.php" in the '.$this->package_name.' package directory';
        } 

        function Ajax($parms = "",&$smarty){
			if (function_exists('apply_filters')){
				if ($result = apply_filters($this->package_name.'_ajax',false,$parms,array(&$smarty))){
					return $result;
				}
			}
            if (!class_exists($this->package_name.'__UserFunctions')){
                return 'A class called '.$this->package_name.'__UserFunctions() must be declared in a file called "user.functions.php" in the '.$this->package_name.' package directory';
            }
            $ClassName = $this->package_name.'__UserFunctions';
            $FunctionName = $this->package_name.'__UserAjax';
            $ClassMethods = get_class_methods($ClassName);
            foreach ($ClassMethods as $Method){
                if (strtolower($Method) == strtolower($FunctionName)){
                    // Statically call $ClassName::$FunctionName($parms,$this,$smarty);
                    return call_user_func_array(array($ClassName,$FunctionName),array($parms,$this,&$smarty));  
                }
            }
            return 'A method called '.$FunctionName.'($parms,$package,&$smarty) must be declared in a file called "user.functions.php" in the '.$this->package_name.' package directory';
        } 
    }
    
?>