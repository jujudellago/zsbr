<?php
    /*************************************************************
    *
    * This is the semaphor file that displays the correct page
    * based on what package we're using and what page in that package
    * we want to display.  
    *
    *************************************************************/

    //include_once("../lib/Standard.php");
	if (!defined('ADMIN_PAGE_PARM')){
	    define('ADMIN_PAGE_PARM','page');
	}
    include_once("AdminStandard.php");
    
    if ($_GET['package'] != ""){
        $Package = $Bootstrap->usePackage($_GET['package']);
    }
    
    if (!$Package or !isset($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]])){
	    // display main menu
		$smarty->assign('title',SITE_NAME.' :: Admin Main Menu');
		$MenuItems = $Bootstrap->getAuthorizedAdminMenuPackages();
		$smarty->assign('menu_items',$MenuItems);
		$smarty->assign('display','MAIN_MENU');
		$smarty->assign('admin_page_parm',ADMIN_PAGE_PARM);
		$smarty->display('admin_index.tpl');	
    }
    else{
        if (!isset($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url'])){
            echo "Error: you must define a 'url' as part of the admin_pages['".$_GET[ADMIN_PAGE_PARM]."'] array";
            exit();
        }
        
        if ($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url_is_complete']){
            if (strpos($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url'],'?') !== false){
                $sep = '&';
            }
            else{
                $sep = '?';
            }
            
            $additional_get_vars = "";
            foreach ($_GET as $k => $v){
                if ($k != 'package' and $k != ADMIN_PAGE_PARM){
                    $additional_get_vars.= $sep."$k=$v";
                    $sep = "&";
                }
            }
            
            // don't use this wrapper, just redirect to the actual URL
            header("Location:".$Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url'].$additional_get_vars);
            exit();
        }
        else{
		    $smarty->assign('title',SITE_NAME." :: ".$Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['title']);
        
            // This bit of code puts the results from the include in to a string variable
            ob_start();
            include(PACKAGE_DIRECTORY.$Package->package_name."/".$Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url']);
            $IncludeContent = ob_get_contents();
            ob_end_clean();
        
            $smarty->display('admin_head.tpl');
            echo $IncludeContent;
            $smarty->display('admin_foot.tpl');
            
        }
    }
        
?>