<?php

    // Site Specific Configuration Variables
    define ('SITE_NAME','WordPress Playground');
    define ('SITE_URL','www.wordpress.org');
    define ('ADMIN_SMARTY_THEME','wordpress');
    
    define ('SITE_THEME','simple');
    
    define ('DEBUG', false);

    define ('DOCTYPE_AND_HTML_TAG',"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" >\n");
    
    if (file_exists(dirname(__FILE__)."/env.php")){
        include_once(dirname(__FILE__)."/env.php");
    }
    elseif (file_exists(dirname(__FILE__)."/env_live.php")){
        include_once(dirname(__FILE__).'/env_live.php');
    }
    
    define('THUMB_WIDTH','133');
    define('THUMB_HEIGHT','100');
    define('RESIZED_WIDTH','400');
    define('RESIZED_HEIGHT','400');
    define('ORIGINAL_WIDTH','1500');
    define('ORIGINAL_HEIGHT','1500');
    
	define ('CMS_PLATFORM','WordPress');
	define ('CMS_DB_ENCODING','UTF-8');
	
	if (CMS_PLATFORM == 'Joomla'){
    	define ('CMS_ADMIN_SCRIPT','index.php?option=com_topquark');
    	define ('CMS_USER_SCRIPT','index.php?option=com_topquark');
    	define ('CMS_ADMIN_PATH','administrator/');
    	define ('CMS_ADMIN_COM_PATH','components/com_topquark/');
    	define ('CMS_INSTALL_PATH','components/com_topquark/');

		define ('CMS_ADMIN_DOC_BASE',DOC_BASE.CMS_ADMIN_PATH);
		define ('CMS_ADMIN_URL',BASE_URL.CMS_ADMIN_PATH);
		define ('CMS_ADMIN_COM_DOC_BASE',DOC_BASE.CMS_ADMIN_PATH.CMS_ADMIN_COM_PATH);
		define ('CMS_ADMIN_COM_URL',BASE_URL.CMS_ADMIN_PATH.CMS_ADMIN_COM_PATH);
		define ('CMS_INSTALL_DOC_BASE',DOC_BASE.CMS_INSTALL_PATH);
		define ('CMS_INSTALL_URL',BASE_URL.CMS_INSTALL_PATH);
		define ('CMS_INSTALL_RELATIVE_URL',RELATIVE_BASE_URL.CMS_INSTALL_PATH);	
    }
	elseif (CMS_PLATFORM == 'WordPress'){
    	define ('CMS_ADMIN_SCRIPT','admin.php?page=topquark&noheader=true');
    	define ('CMS_USER_SCRIPT','index.php?option=com_topquark');
    	define ('CMS_ADMIN_PATH','wp/wp-admin/');
    	define ('CMS_ADMIN_COM_PATH','../app/plugins/topquark/admin/');
    	define ('CMS_INSTALL_PATH','../app/plugins/topquark/');
    	define ('CMS_ADMIN_COM_DOC_BASE',ABSPATH.CMS_ADMIN_COM_PATH);
    	define ('CMS_ADMIN_COM_URL',get_bloginfo('wpurl').'/'.CMS_ADMIN_COM_PATH);
	    define ('ADMIN_PAGE_PARM','toppage');
	
		define ('CMS_ADMIN_DOC_BASE',ABSPATH.CMS_ADMIN_PATH);
		define ('CMS_ADMIN_URL',get_bloginfo('wpurl').'/'.CMS_ADMIN_PATH);
		define ('CMS_INSTALL_DOC_BASE',ABSPATH.CMS_INSTALL_PATH);
		define ('CMS_INSTALL_URL',get_bloginfo('wpurl').'/'.CMS_INSTALL_PATH);
		define ('CMS_INSTALL_RELATIVE_URL',get_bloginfo('wpurl').'/'.CMS_INSTALL_PATH);
	
    }
    else{
    	define ('CMS_ADMIN_SCRIPT','admin.php');
    	define ('CMS_USER_SCRIPT','index.php');
    	define ('CMS_ADMIN_PATH','admin/');
    	define ('CMS_ADMIN_COM_PATH','');
    	define ('CMS_INSTALL_PATH','');

		define ('CMS_ADMIN_DOC_BASE',DOC_BASE.CMS_ADMIN_PATH);
		define ('CMS_ADMIN_URL',BASE_URL.CMS_ADMIN_PATH);
		define ('CMS_ADMIN_COM_DOC_BASE',DOC_BASE.CMS_ADMIN_PATH.CMS_ADMIN_COM_PATH);
		define ('CMS_ADMIN_COM_URL',BASE_URL.CMS_ADMIN_PATH.CMS_ADMIN_COM_PATH);
		define ('CMS_INSTALL_DOC_BASE',DOC_BASE.CMS_INSTALL_PATH);
		define ('CMS_INSTALL_URL',BASE_URL.CMS_INSTALL_PATH);
		define ('CMS_INSTALL_RELATIVE_URL',RELATIVE_BASE_URL.CMS_INSTALL_PATH);
    }
    

	if (CMS_PLATFORM == 'WordPress'){
		if (is_multisite()){
			$upload_dir = wp_upload_dir();		
			define ('CMS_ASSETS_DIRECTORY',str_replace(DOC_BASE,'',$upload_dir['basedir']).'/assets/');
		}
		else{
			define ('CMS_ASSETS_DIRECTORY','app/assets/');
		}
	}
	else{
		define ('CMS_ASSETS_DIRECTORY',CMS_INSTALL_PATH.'assets/');
	}
	if (!file_exists(DOC_BASE.CMS_ASSETS_DIRECTORY) and !mkdir(DOC_BASE.CMS_ASSETS_DIRECTORY)){
		die('Unable to create assets directory at '.DOC_BASE.CMS_ASSETS_DIRECTORY);
	}
	
	define ('GALLERY_IMAGE_DIR',CMS_ASSETS_DIRECTORY.'galleries/');
	if (!file_exists(DOC_BASE.GALLERY_IMAGE_DIR) and !mkdir(DOC_BASE.GALLERY_IMAGE_DIR)){
		die('Unable to create gallery directory at '.DOC_BASE.GALLERY_IMAGE_DIR);
	}
	
	define ('PACKAGE_DIRECTORY',CMS_INSTALL_DOC_BASE.'lib/packages/');
	

?>