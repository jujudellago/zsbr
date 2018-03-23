<?php

class Parameterized_Object
{
	var $params = array();
	var $params_saved = array();
	var $id_param; 
	var $name_param; 
	var $smarty;

	function Parameterized_Object($params=array()){		
		if (is_array($params) and count($params)){
			$this->setParameters($params);
			$this->saveParameters();
		}			
		if (function_exists('apply_filters')){
			$void = apply_filters('extend_'.get_class($this),array(&$this));
		}
	}

	function setParameter($param,$value){
                if (is_string($value)){
		        $this->params[$param] = stripslashes($value);
                }
                else{
		        $this->params[$param] = $value;
                }
	}
	
	function setParameters($params = array()){
		// HTML Entity Translation Table
		//$trans = get_html_translation_table(HTML_ENTITIES);
		//$trans = array_flip($trans);
		//$trans['&rsquo;'] = "'"; // Had to add this for stupid Netscape
		
		if (is_array($params) and count($params)){
			foreach ($params as $key=>$value){
//				$this->setParameter($key,strtr($value,$trans));
				$this->setParameter($key,$value);
			}
		}
	}
	
	function setIDParameter($param){
		$this->id_param = $param;
	}
	
	function getIDParameter(){
		return $this->id_param;
	}
	
	function setNameParameter($param){
		$this->name_param = $param;
	}
	
	function getNameParameter(){
		return $this->name_param;
	}
	
	function getParameters(){			
		return $this->params;
	}
	
	function getSavedParameters(){			
		return $this->params_saved;
	}
	
	function getParameter($param){	
		if (!array_key_exists($param,$this->params)){
			return null;
		}
		return $this->params[$param];
	}
	
	function getURLEncodedParameter($param){	
		/*
		$ret = rawurlencode($this->getParameter($param));
		$ret = str_replace("%2F","/",$ret);
		$ret = str_replace("%5C","/",$ret);
		return $ret;
		*/
		
		return (str_replace(" ","%20",$this->getParameter($param)));
	}
	
	function getHTMLFormattedParameter($param){
		$str = $this->getParameter($param);

		$str = preg_replace("/(http|https|mailto|ftp)(:\/\/)([^ \n]*)/is","<a href='$1$2$3' target='_blank'>$3</a>",$str);
		return $str;
	}
	
	function getEvaluatedParameter($param,$array){
		// extract the associated array
		extract($array);
		
		$str = $this->getParameter($param);
		$temp = str_replace("\"","\\\"",$str);
		if (@eval("\$str = \"" . $temp . "\";") === false){	
			return $this->getParameter($param);
		}
		
		return $str;
	}
	
	function saveParameters(){
		// Saving memory in this implementation. No need to Keep Parameters everytime.
		// Note, the saving of terms still works.
		// return;
		$parms = $this->getParameters();
		foreach ($parms as $k=>$v){
			$this->params_saved[$k] = $v;
		}
	}

	function getSavedParameter($param){	
		if (!array_key_exists($param,$this->params_saved)){
			//return PEAR::raiseError("Parameter $param does not exist");
			return null;
		}
		return $this->params_saved[$param];
	}
	
	
	function getSmartyEvaluatedParameter($param){
    	if (!class_exists('Smarty_Instance')){
    	    include_once(dirname(__FILE__)."/../../Smarty_Instance.class.php");
    	}
    	if (!$this->smarty){
    	    $this->initializeSmarty();
	    }
        $this->smarty->assign('Content',$this->getParameter($param));
        foreach ($this->getParameters() as $key => $value){
            $this->smarty->assign($key,$value);
        }
	    return $this->smarty->fetch('blank.tpl');
	}
	
	function smartyDecodeParameter($param){
		$pattern1 = "/\{(.*?)\}/s";
		$value = $this->getParameter($param);
		if (preg_match_all($pattern1,$value,$matches,PREG_SET_ORDER)){
			foreach ($matches as $match){
			//  $match[0] = "{....}", $match[1] = "...."
				//$repl = html_entity_decode(str_replace('$',"\\\\$",$match[0]));			
				$repl = html_entity_decode($match[0]);			
				$value = str_replace($match[0],$repl,$value);
			}
		}
		$this->setParameter($param,$value);
		return true;
		
	}
	
	function initializeSmarty(){
	    global $Bootstrap;
        $this->smarty = new Smarty_Instance();
        $this->smarty->assign_by_ref('bootstrap',$Bootstrap);
        $this->smarty->register_function('paint',array($Bootstrap,'Paint'));
        $this->smarty->register_function('retrieve',array($Bootstrap,'Retrieve'));
	}
		
	

}
?>