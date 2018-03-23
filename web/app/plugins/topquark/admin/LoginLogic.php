<?php
	/***********************************************************
	* 	LoginLogic.php
	*	This is a logs a user into the admin site  
	*
	* 	Required files
	*		- MainMenu.php --> this is the file that gets opened
	*		  upon successful login
	***********************************************************/
		
	session_start();
	session_register('auth');
	session_register('auth_name');
	session_register('auth_level');
	require_once("../lib/Standard.php");
	$Bootstrap->usePackage('Users');
	
	$UserContainer = new UserContainer();

	$message = "";
	switch ($_GET['action']){
	case "login":
		unset($auth);
		unset($auth_name);
		unset($auth_level);
		$User = $UserContainer->getUser($_POST['UserName']);
		if (PEAR::isError($User)){
			$message = $User->getMessage();
		}
		elseif (is_a($User,'User') and $User->getParameter('UserPasswordMD5') == md5($_POST['Password'])){
			$_SESSION['auth'] = "yes";
			$_SESSION['auth_name'] = $User->getParameter('UserName');
			$_SESSION['auth_level'] = $User->getParameter('UserAuthLevel');
			$_SESSION['auth_site'] = md5(SITE_URL); 
			unset($_GET['action']);
		}
		else{
			$message = "Login failed, please try again";
			unset($_GET['action']);
		}
		break;
	case "logout":
		$message = "You have successfully been logged out";
		doLogout();
		break;
 	case "newpass":
 		if ($_POST['Password'] != $_POST['Password_Confirm']){
 			$message = "Passwords don't match.  Please try again";
 		}
 		else{
	 		$User = new User('admin');
	 		$User->setParameter('UserPasswordMD5',md5($_POST['Password']));
	 		$User->setParameter('UserAuthLevel',USER_AUTH_ADMIN);
	 		$result = $UserContainer->addUser($User);
	 		if (PEAR::isError($result)){
	 			$message = $result->getMessage();
	 		}
	 		else{
	 			$message = "Congratulations, the password was successfully set.  You can login now.";
	 		}
	 	}
	case "resetpass":
	    if ($_SESSION['auth_name']){
	        $User = $UserContainer->getUser($_SESSION['auth_name']);
	        $_GET['code'] = md5($User->getParameter('UserName').'asdf');
    	    doLogout(); // Defined in LoginLogic.php
	    }
	    else{
    	    $wc = new whereClause();
    	    $wc->addCondition("MD5(CONCAT(".$UserContainer->getColumnName('UserName').",'asdf')) = ?",($_GET['code'] != "" ? $_GET['code'] : $_POST['code']));
    	    $User = $UserContainer->getAllUsers($wc);
    	    if (is_array($User)){
    	        $User = array_pop($User);
    	    }
    	}
	    if (!$User){
	        $message = "I couldn't find which User you're trying to change the password for.  Please ensure the you are on the same URL as was in the email that was sent to you (including the long code).";
	    }
	    else{
     		if ($_POST['Password'] != $_POST['Password_Confirm']){
     			$message = "Passwords don't match.  Please try again";
     		}
     		else{
     		    if ($_POST['Password'] != ""){
     		        $User->setParameter('UserPasswordMD5',md5($_POST['Password']));
     		        $result = $UserContainer->updateUser($User);
     		        if (PEAR::isError($result)){
     		            $message = $result->getMessage();
     		        }
     		        else{
     		            $PasswordChanged = true;
     		            $message = "Your password has been changed.  Continue to the <a href='index.php'>Login Screen</a>";
     		        }
         		}
         	}
         }
     	break;
	default:
		break;
	}		
	
	function doLogout(){
	#	session_unregister('auth');
		unset($_SESSION['auth']);
	#	session_unregister('auth_name');
		unset($_SESSION['auth_name']);
	#	session_unregister('auth_level');
		unset($_SESSION['auth_level']);
	}
?>