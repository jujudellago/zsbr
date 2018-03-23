<?php

    // Environment Specific configuration variables
    define ('ENV','DEV');
    #define ('DOC_BASE',realpath(dirname(__FILE__).'/../../../../').'/');
 	define ('DOC_BASE',realpath(dirname(__FILE__).'/../../../../../web/').'/');
	if (!function_exists('get_bloginfo')){
		require_once( DOC_BASE . '/wp/wp-load.php' );
	}
    define ('BASE_URL',get_bloginfo('url').'/');
    define ('RELATIVE_BASE_URL',preg_replace('/^.*'.$_SERVER['HTTP_HOST'].'\//','/',get_bloginfo('wpurl').'/../'));
	$path_to_config = apply_filters('topquark_path_to_wp_config',ABSPATH.'../wp-config.php');
	include_once($path_to_config);
	define ('DSN', "mysql://".DB_USER.":".DB_PASSWORD."@".DB_HOST."/".DB_NAME);    
    define ('TEMP_DIR','/tmp/');
	global $wpdb;
	define ('DATABASE_PREFIX',$wpdb->get_blog_prefix().'topquark_');
	define ('ENABLE_TIMER',false);
?>