<?php
	include_once('../lib/Standard.php');
	
	// DB connection test
	/*
	include_once('../lib/packages/Common/DB.php');
	var_dump(DB::connect(DSN,array('debug' => 2)));
	exit();
	*/

	include_once('../lib/HTML_Form-1.3.0/HTML/Form.php');
	
	$Bootstrap->usePackage('Users');
	$Bootstrap->primeAdminPage();
	
	include('LoginLogic.php');

    // By setting this, the check for authorized users will be skipped in AdminStandard.php
    // It's not necessary to authorize the login page, and 
    // $AuthorizationNotNecessary = true; 
	include_once('AdminStandard.php');
	$UserContainer = new UserContainer();

	$smarty->assign('return_to_url',BASE_URL);
	$smarty->assign('display_url',SITE_URL);

	if ($_GET['action'] == 'forgotpass'){
	    $display = "RETRIEVE_PASSWORD";
	}
	elseif ($_GET['action'] == 'lookup_username'){
	    if ($_POST['UserName'] != ""){
	        $User = $UserContainer->getUser($_POST['UserName']);
	    }
	    elseif ($_POST['Email'] != ""){
	        $User = $UserContainer->getUserFromEmail($_POST['Email']);
	    }
	    else{
	        $display = 'RETRIEVE_PASSWORD';
	    }
	    
	    if ($display == ""){
	        if (!$User){
	            $message = "<p>No username could be found for those credentials.  Please try again.";
	            $display = 'RETRIEVE_PASSWORD';
	        }
	        else{
	            $to = $User->getParameter('UserRealName')." <".$User->getParameter('UserEmail').">";
	            $from = "no_reply@".str_replace("www.","",SITE_URL);
	            $subject = SITE_NAME." - Password Reset Information";
	            $body = "You have requested to reset your password.  In order to do that, please follow this link:\n\n";
	            $body.= "\t".BASE_URL."admin/index.php?action=resetpass&code=".md5($User->getParameter('UserName').'asdf')."\n\n";
	            $body.= "Your Login Name is ".$User->getParameter('UserName')."\n\n";
	            $body.= "Good luck,\n".SITE_URL."\n\n";
	            $body.= "(Note: this is an auto-generated message and should not be replied to)\n\n\n";
	            
        		$headers['From'] = "From: $from";
        		$headers['To']   = $to;
        		$headers['Subject'] = $subject;
                
                if (ENV == 'DEV'){
	                include_once("Mail.php");
                    $params = eval(SMTP_INFO);
		            $mail_object =& Mail::factory('smtp',$params);
		            
        		    $headers['From'] = "$from";
                    $result = $mail_object->send($to,$headers,$body);
                    if (PEAR::isError($result)){
                        echo $result->getMessage();
                        $result = false;
                    }
                    else{
                        $result = true;
                    }
                }
                else{
	                $result = mail($headers['To'],$headers['Subject'],$body,$headers['From']);
	            }
                if ($result){
                    $message = "An email has been sent to you with information on how to reset your password. Go to <a href='index.php'>the login screen</a>";
                    $display = 'DISPLAY_MESSAGE_ONLY';
                }
                else{
                    $message = "<p>There was an server error sending the email to you.  Please try again.";
                    var_dump($result);
                    $display = 'RETRIEVE_PASSWORD';
                }
	        }
	    }
	}
	elseif ($_GET['action'] == 'resetpass'){
	    $display = 'RESET_PASSWORD';
	    if ($_POST['Password'] == ""){
    	    if ($message == ""){
    	        $message = "Please specify your new password below:";
    	    }
    	}
	    if (!$User or $PasswordChanged){
	        $display = 'DISPLAY_MESSAGE_ONLY';
	    }
	}
	else{	
    	if ($_SESSION['auth'] != "yes" or $_SESSION['auth_site'] != md5(SITE_URL)){
    		$display = 'LOGIN';
    	}
    	else{
    	    if ($_POST['redirect'] != ""){
    	        header("Location:".urldecode($_POST['redirect']));
    	        exit();
    	    }
    	    else{
    	        header("Location:".$Bootstrap->getAdminURL());
    	        exit();
    	    }
    	}
    }
    
	switch ($display){
	case "LOGIN":
		$smarty->assign('title',SITE_NAME.' :: Admin Login');

		$form = new HTML_Form('index.php?action=login','post','login_form');
		
		$Redirect = ($_GET['redirect'] != "" ? $_GET['redirect'] : $_POST['redirect']);

		$form->addText('UserName','Login Name',$_POST['UserName'],HTML_FORM_TEXT_SIZE,100);
		$form->addHidden('redirect',$Redirect);
		$form->addPasswordOne('Password','Password',null,HTML_FORM_TEXT_SIZE,25);
		$form->addSubmit('Login','Login');
		$form->addPlainText('&nbsp;',"<font size='-1'>(Have you <a href='index.php?action=forgotpass'>forgotten your login name or password</a>?)</font>");
		
		$smarty->assign('form',$form);
		if ($_GET['redirect'] != ""){
		    $message = "You must login to view that page";
		}
		$smarty->assign('message',$message);
		$smarty->display('admin_login.tpl');
		break;
	case "RETRIEVE_PASSWORD":
		$smarty->assign('title',SITE_NAME.' :: Lookup Username');
		$message = "Please enter your login name or email address.  Information on how to change your password will be emailed to you.".$message; 

		$form = new HTML_Form('index.php?action=lookup_username','post','login_form');

		$form->addText('UserName','Login Name','',HTML_FORM_TEXT_SIZE,100);
		$form->addPlainText('&nbsp;','or');
		$form->addText('Email','Email Address','',HTML_FORM_TEXT_SIZE,100);
		$form->addSubmit('Lookup','Look it up');
		
		$smarty->assign('form',$form);
		$smarty->assign('message',$message);
		$smarty->display('admin_login.tpl');
		break;
	case "DISPLAY_MESSAGE_ONLY":
		$smarty->assign('title',SITE_NAME.' :: Email Sent');

		$form = new HTML_Form('index.php','post','null_form');

		$smarty->assign('form',$form);
		$smarty->assign('message',$message);
		$smarty->display('admin_login.tpl');
		break;
	case "RESET_PASSWORD":
		$smarty->assign('title',SITE_NAME.' :: Reset Admin Password');

		$form = new HTML_Form('index.php?action=resetpass','post','newpass_form');
		$form->addHidden('code',($_GET['code'] != "" ? $_GET['code'] : $_POST['code']));
		if ($User){
		    $form->addPlainText('UserName',$User->getParameter('UserName'));
		}
		$form->addPasswordOne('Password','Password',null,HTML_FORM_TEXT_SIZE,25);
		$form->addPasswordOne('Password_Confirm','Confirm Password',null,HTML_FORM_TEXT_SIZE,25);
		$form->addSubmit('Set Password','Set Password');
		
		$smarty->assign('form',$form);
		$smarty->assign('message',$message);
		$smarty->display('admin_login.tpl');
		break;
	default:
		break;
	}
?>