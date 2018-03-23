<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");

class UserPackageContainer extends ObjectContainer{

	function UserPackageContainer(){
	}
	
	function addUserPackage($UserName,$Package){
		$User = $this->getUser($UserName);
		if (!$User) return false;
		return $User->add_cap('access_topquark_'.$Package);
	}
	
	function getUser($UserName){
		if (is_a($UserName,'WP_User')){
			return $UserName;
		}
		$User = new WP_User($UserName);
		if (!$User){ 
			return false;
		}
		return $User;
	}
	
	function userIsAuthorized($UserName,$Package){
		$User = $this->getUser($UserName);
		if (!$User) return false;
		return $this->user_can_access($User,$Package);
	}
	
	function getAllUserPackages($UserName){
		$User = $this->getUser($UserName);
		if (!$User) return false;

		$Bootstrap = Bootstrap::getBootstrap();
		$Packages = $Bootstrap->getAllPackages();
		$package_names = array();
		foreach($Packages as $p){
			$package_names[] = $p->package_name;
		}
		
		if ($User->has_cap('administrate_topquark')){
			$return = $package_names;
		}
		else{
			$auth_package_names = array();
			foreach ($package_names as $p){
				$cap = 'access_topquark_'.$p;
				if ($User->has_cap($cap)){
					$auth_package_names[] = $p;
				}
			}
			$return = $auth_package_names;
		}
		
		if (!is_array($return)){
			return array();
		}
		else{
			return $return;
		}
	}
	
	function deleteUserPackage($UserName,$Package){
		$User = $this->getUser($UserName);
		if (!$User) return false;
		return $User->remove_cap('access_topquark_'.$Package);
	}
	
	function deleteAllUserPackages($UserName){
		$Packages = $this->getAllUserPackages($UserName);
		if (!is_array($Packages)){
			return true;
		}
		$User = $this->getUser($UserName);
		if (!$User) return false;
		foreach ($Packages as $p){
			$User->remove_cap('access_topquark_'.$p);
		}
	}

	function user_can_access($User,$Package){
		if ($Package == 'Common'){
			return true;
		}
		return ($User->has_cap('administrate_topquark') or $User->has_cap('access_topquark_'.$Package));
	}
}

?>