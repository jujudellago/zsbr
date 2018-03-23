<?php
/************************************************************
*
*
*************************************************************/
    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'update');
    $returnURL = $Bootstrap->makeAdminURL($Package,'manage');
	
	include_once(PACKAGE_DIRECTORY."Common/ObjectLister.php");
	define ('HTML_FORM_TH_ATTR',"valign=top align=left width='15%'");
	define ('HTML_FORM_TD_ATTR',"valign=top align=left");
	include_once(dirname(__FILE__)."/../../../TabbedForm.php");
        
    $name    = isset($_GET['name']) ? $_GET['name'] : (isset($_POST['name']) ? $_POST['name'] : ""); 
    
	$UserPackageContainer = new UserPackageContainer();
	
	$UserContainer = new UserContainer();
	global $User,$current_user;
	if ($name){
	    $User = new WP_User($name);
	}
	
	$Applications = $Bootstrap->getAllPackages();
		
	// Avoid hacking situation - don't let the editting continue if the logged-in user doesn't have authority
	if (!current_user_can('administrate_topquark')){
		unset($User);
	}

    if (!$User){
        header("Location:$returnURL");
        exit();
    }
	$current_user = wp_get_current_user();
        	
	/******************************************************************
	*  Field Level Validation
	*  Only performed if they've submitted the form
	******************************************************************/
	if (isset($_POST['form_submitted']) and $_POST['form_submitted'] == 'true'){
	
		// They hit the cancel button, return to the Manage Pages page
		if (isset($_POST['cancel'])){
			header("Location:$returnURL");
			exit();
		}

		/******************************************************************
		*  BEGIN EDITS
		*  If an edit fails, it adds an error to the message list.  
		******************************************************************/
		if ($current_user->data->ID != $User->data->ID){
			// Only pay attention here if we're not editting our own
			if (isset($_POST['UserIsSuperUser'])){
				$User->add_cap('administrate_topquark');
			}
			else{
				$User->remove_cap('administrate_topquark');
			}
			// reget the user
	    	$User = new WP_User($name);
		}
		/******************************************************************
		*  END EDITS
		******************************************************************/
				




						
		/******************************************************************
		*  BEGIN Set Parameters
		******************************************************************/
		
		/******************************************************************
		*  END Set Parameters
		******************************************************************/
		
		// If there are no messages/errors, then go ahead and do the update (or add)
		// Note: if they were deleting a version, then there will be a message, so
		// this section won't get performed
		if (!$MessageList->hasMessages()){
		    // Now, we're going to update all of the permissions.
		    // First, we'll wipe the slate clean for this user, then we'll add in all of the packages they were given permission for
		    $UserPackageContainer->deleteAllUserPackages($User);
			$User->remove_cap('access_topquark');
			if ($User->has_cap('administrate_topquark')){
				// No need to set individual permissions.  they get permission to everything
				$User->add_cap('access_topquark');
			}
			else{
				$at_least_one = false;
			    foreach ($Applications as $Application){
			        if (isset($_POST['Auth'.$Application->package_name])){
			            $UserPackageContainer->addUserPackage($User,$Application->package_name);
						$at_least_one = true;
			        }
			    }
				if ($at_least_one){
					$User->add_cap('access_topquark');
				}
			}
			if (!$MessageList->hasMessages()){
				$MessageList->addMessage("User Successfully Updated. ".'('.date("Y-m-d g:i:s").')');
			}
		}
	}
	
	/****************************************************************************
	*
	* BEGIN Display Code
	*    The following code sets how the page will actually display.  
	*
	****************************************************************************/
	// Declaration of the Form	
	$form = new HTML_TabbedForm($Bootstrap->getAdminURL().($name != "" ? "&name=$name" : ""),'post','Update_Form');
    if (isset($_POST['active_tab']) and $_POST['active_tab'] != ""){
            $DefaultTab = substr($_POST['active_tab'],6);
    }
    else{
        $DefaultTab = 'PermissionsTab';
    }

	/***********************************************************************
	*
	*	Permissions Tab
	*
	***********************************************************************/
	$PermissionsTab = new HTML_Tab('PermissionsTab','Permissions');

	$PermissionsTab->addPlainText('User:',$User->data->user_login);
    global $UserPackages;
	if ($User->data->ID != ""){
	    $UserPackages = $UserPackageContainer->getAllUserPackages($User->data->ID);
	}
	else{
	    $UserPackages = array();
	}
	
	$ObjectLister = new ObjectLister();
	$ObjectLister->addColumn('Application','displayApplication','80%');
	$ObjectLister->addColumn('Authorize','displayAuthorization','20%');
	$smarty->assign('ObjectListHeader', $ObjectLister->getObjectListHeader());
	$smarty->assign('ObjectList', $ObjectLister->getObjectList($Applications));
	$smarty->assign('ObjectListAlign', "left");
	$smarty->assign('ObjectEmptyString',"");
	$PermissionsTab->addPlainText('&nbsp;',$smarty->fetch('admin_listing.tpl'));

	if ($current_user->data->ID != $User->data->ID){
		if ($User->has_cap('administrate_topquark')){
		    $checked = true;
		}
		else{
		    $checked = false;
		}
	    $AdminText = HTML_Form::returnCheckbox('UserIsSuperUser',$checked);
	    $AdminText.= "(check this box to make this user a 'Super User')<br/>Super Users: <br/>- can authorize other users for Top Quark Applications<br/>- are automatically authorized for all Applications)";
		$PermissionsTab->addPlainText('&nbsp;','Super User: '.$AdminText);
	}
	
	function displayAuthorization($Object){
	    global $UserPackages;
		global $User,$current_user;
		if ($User->has_cap('administrate_topquark')){
			return '{&bull;}';
		}
		$checked = in_array($Object->package_name,$UserPackages);
	    $ret = HTML_Form::returnCheckbox('Auth'.$Object->package_name,$checked);
	    return $ret;
	}

	function displayApplication($Object){
	    $ret = "<b>".$Object->package_title."</b><br />";
	    $ret.= $Object->package_description;
	    return $ret;
	}

	/***********************************************************************
	*
	*	Add the Tabs
	*
	***********************************************************************/
	$form->addTab($PermissionsTab);

	/***********************************************************************
	*
	*	Message Tab
	*
	***********************************************************************/
	// We display messages on a new tab.  this will be the default tab that displays when the page gets redisplayed	
	if ($MessageList->hasMessages()){
		$$DefaultTab->addPlainText('&nbsp;','&nbsp;');
		$$DefaultTab->addPlainText('Messages',$MessageList->toBullettedString());
	}
	
	$$DefaultTab->setDefault();
	
	// Here are the buttons
	if ($current_user->data->ID != $User->ID){
		$form->addSubmit('save','Save Changes');
	}
	else{
		$PermissionsTab->addPlainText('&nbsp','{&bull;} - You are a Top Quark Administrator - you are automatically given permissions for all Applications.');
	}
	$form->addSubmit('cancel','Cancel');
	
	// Some hidden fields to help us out 
	$form->addHidden('form_submitted','true');
	$form->addHidden('name',$name);
	
	// Finally, we set the Smarty variables as needed.
	if (!isset($start_functions)) $start_functions = array();
	if (!isset($admin_head_extras)) $admin_head_extras = '';
	$smarty->assign('includes_tabbed_form',true);
	$smarty->assign('admin_start_function',$start_functions);
	$smarty->assign('form',$form);
	$smarty->assign('form_attr','width=90% align=center');
	$smarty->assign('Tabs',$form->getTabs());
	$smarty->assign('admin_head_extras',$admin_head_extras);
	
?>
<?php   $smarty->display('admin_form.tpl'); ?>
