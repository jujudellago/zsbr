<?php
/*******************************************
*  Standard.php
*  Contains standard functions for error
*  and message handling. 
*  Author:  Trevor Mills (trevor@trevormills.com)
*  Version: 1.0
*******************************************/

	// Let's check if PEAR.php exists on the path
	if ($test = @fopen('PEAR.php','r',true)){
		fclose($test);
		include_once('PEAR.php');
	}
	else{
		// Doesn't look like it's installed, let's include ours
		include_once("packages/Common/PEAR.php");
	}
	
	if (!isset($RedirectPath)) $RedirectPath = "";
	$rp = $RedirectPath;
	
	// I moved this up here as part of the WordPress integration to 
	// allow for Registering of Packages outside of the Package Directory
    global $Bootstrap, $MessageList;
    
	require_once('packages/Common/MessageList.php');
	require_once('packages/Common/Bootstrap.php');
	
	$MessageList = Bootstrap::getMessageList();
	$Bootstrap = Bootstrap::getBootstrap();
	
	include_once(dirname(__FILE__)."/conf.php");
	
	if (!defined('INDEX_URL')){
	    define('INDEX_URL','index.php');
	}
	
	if (!defined('INDEX_PAGE_PARM')){
	    define('INDEX_PAGE_PARM','show');
	}
	
	if (!defined('PAGE_CACHE_DIR')){
	    define('PAGE_CACHE_DIR',DOC_BASE.'cache/');
	}
	
    include_once("packages/Common/extra.functions.php");
    
	function startHTMLDoc(){
	    echo DOCTYPE_AND_HTML_TAG;
	}

	function endHTMLDoc(){
	    echo "</html>";
	}

    function getMaxPostSize($inBytes = false){
    	// Figure out the maximum Post Size
        $post_max_size = ini_get('post_max_size');
        $upload_max_filesize = ini_get('upload_max_filesize');
        if ($post_max_size > $upload_max_filesize){
            $post_max_size = $upload_max_filesize;
        }
        
        if ($inBytes){
            return tq_return_bytes($post_max_size);
        }
        else{
            return $post_max_size;
        }
    }
        
    
    function tq_return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val{strlen($val)-1});
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }    
    



	define('MESSAGE_TYPE_MESSAGE','message');
	define('MESSAGE_TYPE_WARNING','warning');
	define('MESSAGE_TYPE_ERROR','error');

	
    function get_head($parms,&$smarty){
        global $rp;
        $Capture = $smarty->_smarty_vars['capture'];
        ob_start();
        $Bootstrap = Bootstrap::getBootstrap();
        $Bootstrap->addHeadExtra($Capture['head']);
        $rp = BASE_URL; // redirect path
        $ForceMenu = true;
		if (CMS_PLATFORM == 'WordPress'){
			do_action('tqp_get_header');
		}
		else{
	        include_once(dirname(__FILE__)."/../themes/".SITE_THEME."/Head.php");
	        include_once(dirname(__FILE__)."/../themes/".SITE_THEME."/Header.php");
		}
        return ob_get_clean();
    }

    function get_foot($parms,&$smarty){
        global $rp;
        ob_start();
		if (CMS_PLATFORM == 'WordPress'){
			do_action('tqp_get_footer');
		}
		else{
	        include_once(dirname(__FILE__)."/../themes/".SITE_THEME."/Footer.php");
		}
        return ob_get_clean();
    }

	if (CMS_PLATFORM == 'WordPress'){
		function tqp_get_header(){
			if (apply_filters('tqp_do_get_header',true)){
				get_header();
				echo apply_filters('tqp_extra_header_markup','');
			}
		}

		function tqp_get_footer(){
			if (apply_filters('tqp_do_get_footer',true)){
				echo apply_filters('tqp_extra_footer_markup','');
				get_footer();
			}
		}

		add_action('tqp_get_header','tqp_get_header');
		add_action('tqp_get_footer','tqp_get_footer');
	}
    
	

?>