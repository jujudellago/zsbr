<?php
	// NOTE: Smarty has a capital 'S'
	if (!class_exists('Smarty')){
	    require_once('smarty/libs/Smarty.class.php');
	}
	include_once(dirname(__FILE__).'/conf.php');
	
	class Smarty_Instance extends Smarty {
	
		function Smarty_Instance() {
		
			// Class Constructor
			$this->Smarty();
			
			$this->template_dir = CMS_ADMIN_COM_DOC_BASE.'smarty/templates/';
			$this->compile_dir = DOC_BASE.CMS_ASSETS_DIRECTORY.'smarty/templates_c/';
			$this->config_dir = CMS_ADMIN_COM_DOC_BASE.'smarty/configs/';
			$this->cache_dir = DOC_BASE.CMS_ASSETS_DIRECTORY.'smarty/cache/';
			
			if (!file_exists(DOC_BASE.CMS_ASSETS_DIRECTORY.'smarty') and !mkdir(DOC_BASE.CMS_ASSETS_DIRECTORY.'smarty')){
				die('Unable to create smarty directory at '.DOC_BASE.CMS_ASSETS_DIRECTORY.'smarty');
			}
			if (!file_exists($this->compile_dir) and !mkdir($this->compile_dir)){
				die('Unable to create smarty compile directory at '.$this->compile_dir);
			}
			if (!file_exists($this->cache_dir) and !mkdir($this->cache_dir)){
				die('Unable to create smarty compile directory at '.$this->cache_dir);
			}
			
			
			$Bootstrap = Bootstrap::getBootstrap();
            $this->assign_by_ref('bootstrap',$Bootstrap);
            $this->register_function('paint',array($Bootstrap,'Paint'));
            $this->register_function('retrieve',array($Bootstrap,'Retrieve'));
            $this->register_function('retrieve',array($Bootstrap,'Retrieve'));
	        $this->register_function('apply_filters',array(&$this,'apply_filters'));
		}	
		
	    function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false){
			if (function_exists('apply_filters')){
				// The most common use of this filter is to change the template directory (thereby overriding
				// the chosen template in one fell swoop).  To play nice, we'll save the template_directory
				// and then restore it at the end.
				$save_dir = $this->template_dir;
				if (has_filter("Smarty_Instance_resource_name_$resource_name")){
					$resource_name = apply_filters("Smarty_Instance_resource_name_$resource_name",$resource_name,array(&$this));
				}
				else{
					$resource_name = apply_filters('Smarty_Instance_resource_name',$resource_name,array(&$this));
				}
			}
			$return = parent::fetch($resource_name,$cache_id,$compile_id,$display);
			$this->template_dir = $save_dir;
			return $return;
		}
		
		function _smarty_include($params){
			if (function_exists('apply_filters')){
				$save_dir = $this->template_dir;
				$params = apply_filters('Smarty_Instance_smarty_include',$params,array(&$this));
			}
			$return  = parent::_smarty_include($params);
			$this->template_dir = $save_dir;
			return $return;
		}
		
		function apply_filters($params,&$smarty){
			// We'll call the WordPress apply_filters function, making the first parameter the $smarty object (so the filter can access Smarty variables)
			extract($params);
			if ($filter == ''){
				return '';
			}
			if (!isset($default_value)){
				$default_value = '';
			}
			return apply_filters($filter,$default_value,array(&$this,$params));			
		}
		
		function no_cache($content){
			return preg_replace_callback('/\<\!\-\- no_cache\(([^\)]*)\) \-\-\>/',array(&$this,'no_cache_callback'),$content);
		}
		
		function no_cache_callback($matches){
			return $this->fetch($matches[1]);
		}
	}
?>