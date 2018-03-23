<?php

    /**************************************************
    * This file gets included as part of the the object 
    * instantiation (as long as the conf.php file calls $this->loadUserConf();)
    *
    * Just specify additional variables that you want to associate with this package like:
    *
    *   $this->foo = "bar";
    *   $this->blah = array('red', 'green', 'blue');
    *
    **************************************************/
    
    $this->useDatabases = array();
	$this->useDatabases = apply_filters('festivalapp_use_databases',array());

	if (function_exists('the_conference_plugin_option')){
		$this->enable_cache = (the_conference_plugin_option('enable_cache') ? true : false);
	}
	else{
		$this->enable_cache = false;
	}
	$this->empty_cache_on_publish_only = false;
	if (CMS_PLATFORM == 'WordPress'){
		$this->cache_directory = DOC_BASE.CMS_ASSETS_DIRECTORY.'/cache/';
		if (!file_exists($this->cache_directory) and !mkdir($this->cache_directory)){
			die('Unable to create cache directory at '.$this->cache_directory);
		}
		$this->cache_directory = DOC_BASE.CMS_ASSETS_DIRECTORY.'/cache/the-conference-plugin/';
	}
	else{
		$this->cache_directory = dirname(__FILE__)."/cache/";
	}
    
	if (!file_exists($this->cache_directory) and !mkdir($this->cache_directory)){
		die('Unable to create cache directory at '.$this->cache_directory);
	}

    $this->flamplayer_colour = "8F2424";
	if (CMS_PLATFORM == 'WordPress'){
	    $this->etcDirectory = DOC_BASE.CMS_ASSETS_DIRECTORY.'/etc/'; 
	}
	else{
	    $this->etcDirectory = dirname(__FILE__).'/etc/'; 
	}

	if (!file_exists($this->etcDirectory) and !mkdir($this->etcDirectory)){
		die('Unable to create etc directory at '.$this->etcDirectory);
	}
	
	if (!defined('YEAR_PARM')){
		define('YEAR_PARM','_year');
	}
?>