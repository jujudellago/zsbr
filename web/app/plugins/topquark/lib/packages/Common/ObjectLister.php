<?php

class ObjectLister{
	
	var $_columns = array();
	var $_object_list = array();
        var $_page_x;
        var $_of_y;
	
	function ObjectLister(){
	}
	
	function addColumn($ColumnName,$Function,$Width = ''){
		$this->_columns[] = array("ColumnName" => $ColumnName,"Width" => $Width, "Function" => $Function);
	}
	
	function getColumns(){
		return $this->_columns;
	}

	function getObjectList($ObjectList){
		$this->_object_list = array();
		if (!is_array($ObjectList) and $ObjectList){	
			$ObjectList = array($ObjectList);
		}
		
		$columns = $this->getColumns();
		
		if (is_array($ObjectList)){
			$i = 0;
			foreach ($ObjectList as $Object){
				foreach ($columns as $array){
					if (function_exists($array['Function'])){
						$this->_object_list[$i][] = array("Data" => $array['Function']($Object),"Width" => $array['Width']);
					}
					else{
						$this->_object_list[$i][] = array("Data" => "Invalid Function: ".$array['Function'],"Width" => $array['Width']);
					}
				}
				$i++;
			}
			
			return $this->_object_list;
		}
		return null;
	}
	
	function getObjectListHeader(){
		$ret = array();
		foreach ($this->_columns as $array){
			$ret[] = array("Data" => $array['ColumnName'], "Width" => $array['Width']);
		}
		return $ret;
	}
        
        function setPageXOfY($x,$y){
                $this->_page_x = $x;
                $this->_of_y = $y;
        }
        
        function getPageNumber(){
                return $this->_page_x;
        }
	
        function getTotalPages(){
                return $this->_of_y;
        }
        
        function setTotalObjects($TotalObjects){
                $this->_total_objects = $TotalObjects;
        }
        
        function getTotalObjects(){
                return $this->_total_objects;
        }
        
        function getPageNavigation($ObjectListURL){
                $ObjectListCurrentPage = $this->getPageNumber();
                $ObjectListTotalPages  = $this->getTotalPages();
                        
                $ret = "";
                
                if ($ObjectListTotalPages > 1){
                        $ret.= "<p>Page: ";
                        if ($ObjectListCurrentPage <= 5){
                                $page_from=1;
                        }
                        else{
                                $page_from=$ObjectListCurrentPage-5;
                        }
                        if ($ObjectListCurrentPage >= $ObjectListTotalPages-5){
                                $page_to = $ObjectListTotalPages;
                        }
                        else{                                
                                $page_to = $ObjectListCurrentPage+5;
                        }
                        if ($ObjectListCurrentPage > 1){
                                $ret.="<b><a href='".$ObjectListURL."page=".($ObjectListCurrentPage-1)."'>Previous</a></b> ";
                        }
                        if ($page_from > 1){
                                $ret.= "<a href='{$ObjectListURL}page=1'>1</a> ... ";
                        }
                        for ($index = $page_from; $index <= $page_to; $index++){
                                if ($index == $ObjectListCurrentPage){
                                        $ret.= " $index ";
                                }
                                else{
                                        $ret.= " <a href='{$ObjectListURL}page={$index}'>{$index}</a> ";
                                }
                        }
                        if ($page_to < $ObjectListTotalPages){
                                $ret.= "... <a href='{$ObjectListURL}page={$ObjectListTotalPages}'>{$ObjectListTotalPages}</a> ";
                        }
                        if ($ObjectListCurrentPage < $ObjectListTotalPages){
                                $ret.= "<b><a href='{$ObjectListURL}page=".($ObjectListCurrentPage+1)."'>Next</a></b> ";
                        }
                        $ret.= "(".$this->getTotalObjects()." found)</p>\n";
                        //$ret.= "<p>Page {$ObjectListCurrentPage} of {$ObjectListTotalPages}</p>\n";
                }
                else{
                        $ret.= "<p>".$this->getTotalObjects()." found</p>\n";
                }
                
                return $ret;
        
        }
	
}
