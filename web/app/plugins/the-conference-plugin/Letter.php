<?php

require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

class Letter extends Parameterized_Object
{
    
	function Letter($LetterID = '',$params=array()){
		$this->Parameterized_Object($params);
		$this->setParameter('LetterID',$LetterID); 

		$this->setIDParameter('LetterID');
		$this->setNameParameter('LetterID');
	}

	function smartyEncodeParameter($param){
		$pattern1 = "/\{(.*?)\}/s";
		$value = $this->getParameter($param);
		if (preg_match_all($pattern1,$value,$matches,PREG_SET_ORDER)){
			foreach ($matches as $match){
			//  $match[0] = "{....}", $match[1] = "...."
				$repl = html_entity_decode($match[0]);			
				$value = str_replace($match[0],$repl,$value);
			}
		}
		$this->setParameter($param,$value);
		return true;
		
	}
	
	function getSmartyEvaluatedParameter($param,$array){
		$saved_parm_value = $this->getParameter($param);
		$this->smartyEncodeParameter($param);
		$ret = $this->getEvaluatedParameter($param,$array);
		
		// Restore altered Parameter
		$this->setParameter($param,$saved_parm_value);
		return $ret;
	}
		
}
?>