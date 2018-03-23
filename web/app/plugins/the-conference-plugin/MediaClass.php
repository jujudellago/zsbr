<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

class Media extends Parameterized_Object
{
	function Media($MediaID = '',$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('MediaID',$MediaID);	

		$this->setIDParameter('MediaID');
		$this->setNameParameter('MediaLocation');
	}
}
?>