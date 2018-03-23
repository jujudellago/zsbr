<?php
    $LibDir = dirname(__FILE__)."/../lib";
	include_once ("$LibDir/Standard.php");
	if (!class_exists('Smarty')){
	    include_once ("$LibDir/Smarty_Instance.class.php");
	}
	$Bootstrap->usePackage('Users');
	
	if (!isset($smarty)){
	    $smarty = new Smarty_Instance();
	}
	$smarty->assign('theme',ADMIN_SMARTY_THEME);
    $Bootstrap->primeAdminPage();
	$smarty->assign_by_ref('bootstrap',$Bootstrap);
	
	// Start the session
	@session_start();
	session_register('SessionMessage');
		
	if (!$AuthorizationNotNecessary){
		$Redirect = $_SERVER['REQUEST_URI'];

	    // Check that the User is (a) logged in and (b) authorized for this page
		if (!isset($_SESSION['auth_name'])          /* no user stored in the session */
		or $_SESSION['auth_site'] != md5(SITE_URL)){  /* the site authorized isn't equal to the current site */
			header("Location:".RELATIVE_BASE_URL."admin/index.php?redirect=".urlencode($Redirect));
			exit();
		}
		else{
		    if ($_GET['package'] != ""){
		        session_register('current_package');
		        $_SESSION['current_package'] = $_GET['package'];
		    }
		    
		    $Authorized = true;
		    if ($_SESSION['current_package'] != "" and $_SESSION['current_package'] != 'Common'){ // authorize the common package always
		        $Package = $Bootstrap->usePackage($_SESSION['current_package']);
		        if ($Package->auth_level > USER_AUTH_EVERYONE){
		            if ($_SESSION['auth_level'] < $Package->auth_level){
		                $Authorized = false;
		            }
		        }
		        else{
    		        $UserPackageContainer = new UserPackageContainer();
    		        if (!$UserPackageContainer->userIsAuthorized($_SESSION['auth_name'],$Package->package_name)){
    		            $Authorized = false;
    		        }
    		    }
    		}
    		
    		if (!$Authorized){
		       # session_unregister('current_package');
    		    unset($_SESSION['current_package']);
    		    unset($_GET['package']);
    		    unset($_GET[ADMIN_PAGE_PARM]);
    		    header("Location:".RELATIVE_BASE_URL."admin/".$Bootstrap->getAdminURL()); // Will display the main menu
    		    exit();
    		}
    	}
	}

?>