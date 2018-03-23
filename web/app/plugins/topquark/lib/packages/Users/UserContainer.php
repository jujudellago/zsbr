<?php
    
class UserContainer{

	function UserContainer(){
	}
	
	function userExists($user_id){
		$user = $this->getUser($user_id);
		if (PEAR::isError($user)){
			echo $user->getMessage();
			return $user;
		}
		else{
			if ($user) return true; else return false;
		}
		
	}
	
	function getAllUsers(){
		$users = get_users();
		$return = array();
		foreach ($users as $user){
			$User = new Parameterized_Object();
			$User->setParameter('UserID',$user->ID);
			$User->setParameter('UserName',$user->user_login);
			$User->setParameter('UserRealName',$user->display_name);
			$User->setParameter('UserEmail',$user->user_email);
			$return[$user->user_login] = $User;
		}
		return $return;
	}
	
	function getUser($user_id){
		if (is_numeric($user_id)){
			$user = get_user_by('id', $user_id);
		}
		else{
			$user = get_user_by('login', $user_id);
		}
		return $this->manufactureUser($user);
	}
	
	function getUserFromEmail($EmailAddress){
		$user = get_user_by('email', $EmailAddress);
		return $this->manufactureUser($user);
	}
	
	function manufactureUser($user){
		// Just in case I have to do something with it
		return $user;
	}
	
	function getUserName($user_id){
		$User = $this->getUser($user_id);
		
		if (!$User){
			return null;
		}
		else{
			return $User->display_name;
		}
	}
	
}

?>