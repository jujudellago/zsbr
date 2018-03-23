<?php

require_once("DB_Object.php");

define( 'SEARCH_NOT', -1);
define( 'SEARCH_FUZZY', 1);
define( 'SEARCH_USE_MASK', 2);
define( 'SEARCH_GREATER_THAN_NULL', 1);
define( 'SEARCH_IS_NULL', 2);
define( 'SEARCH_IS_BLANK', 3);
define( 'SEARCH_LIKE', 4);
define( 'SEARCH_EQUALS', 5);
define( 'SEARCH_SPECIAL_MASK', 6);
define( 'SEARCH_NOT_GREATER_THAN_NULL', -1);
define( 'SEARCH_IS_NOT_NULL', -2);
define( 'SEARCH_IS_NOT_BLANK', -3);
define( 'SEARCH_NOT_LIKE', -4);
define( 'SEARCH_NOT_EQUALS', -5);
define( 'SEARCH_NOT_SPECIAL_MASK', -6);

class ObjectContainer extends DB_Object{
	
	var $where = array();
	var $saved_search_parms = array();
	
	function ObjectContainer(){
		$this->DB_Object();
		if (function_exists('apply_filters')){
			$void = apply_filters('extend_'.get_class($this),array(&$this));
		}
	}

	function tableExists(){
	    if (defined('SKIP_TABLE_EXISTENCE_CHECK')  and SKIP_TABLE_EXISTENCE_CHECK === true){
	        return true;
	    }
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
			
		$result = $dbh->simpleQuery('SHOW TABLES');
		
		if (DB::isError($result)){
			return $result;
		}
		
		$numRows = $dbh->numRows($result);
		
		$tables = array();
		for ($i = 0; $i < $numRows; $i++){
			$arr = array();
			$dbh->fetchInto($result,$arr,DB_FETCHMODE_ASSOC,$i);
			if (strtolower(array_shift($arr)) == strtolower($this->getTableName())){
				return true;
			}
		}		
		
		return false;		
	}
	
	function createTable($_create_query){
		$_create_query = str_replace('TYPE=MyISAM','ENGINE MyISAM',$_create_query);
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
		
		$result = $dbh->simpleQuery($_create_query);
		
		if (DB::isError($result)){
			if ($result->getCode() == DB_ERROR_ALREADY_EXISTS){
				// Everything's fine, table exists
				return true;
			}
			else{
				return PEAR::raiseError("Error: ".$result->getMessage()." Query = $_create_query; Code = ".$result->getCode());
			}
		}
		else{
			return true;
		}
	}
	
	function addObject(&$_object, $setIDParm = false){
		if (!is_a($_object,"Parameterized_Object")) return PEAR::raiseError("Error: Invalid Object");
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
		if (function_exists('apply_filters')){
			$_object = apply_filters('add_'.get_class($_object).'_object',$_object);
		}
		
				
		$_insert_parms = array();
		$_object_parms = $_object->getParameters();
		foreach ($_object_parms as $key => $value){
			if ($key != $_object->getIDParameter() or $setIDParm === true){
			    if(array_key_exists($key,$this->colname)){
					$_insert_parms[$this->getColumnName($key)] = $value;
				}
			}
		}
		$_insert_parms = parent::sanitize_parms($dbh,$this->getTableName(),$_insert_parms);
		
		$sth = $dbh->autoPrepare($this->getTableName(),array_keys($_insert_parms),DB_AUTOQUERY_INSERT);
		//echo $dbh->executeEmulateQuery($sth,$_insert_parms);

		$result = $dbh->autoExecute($this->getTableName(),$_insert_parms,DB_AUTOQUERY_INSERT);
				
		if (DB::isError($result)){
			return PEAR::raiseError($result->getMessage());
		}
		else{
			$_id_parm = $_object->getIDParameter();
			if ($_object->getParameter($_id_parm) == ""){
				$query = "SELECT MAX(".$this->getColumnName($_id_parm).") FROM ".$this->getTableName();
				$result = $dbh->simpleQuery($query);
				$arr = array();
				$dbh->fetchInto($result,$arr,DB_FETCHMODE_ASSOC);
				$object_id = $arr["MAX(".$this->getColumnName($_id_parm).")"];
				$_object->setParameter($_id_parm,$object_id);
				$_object->saveParameters();
				if (function_exists('do_action')){
					do_action('new_'.get_class($_object).'_object_id',$object_id);
				}
				return $object_id;
			}
			else{
				$_object->saveParameters();
				if (function_exists('do_action')){
					do_action('new_'.get_class($_object).'_object_id',$_object->getParameter($_id_parm));
				}
				return $_object->getParameter($_id_parm);
			}
		}
	}
	
	function updateObject(&$_object,$setIDParm = false){
		if (!is_a($_object,"Parameterized_Object")) return PEAR::raiseError("Error: Invalid Object");
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
		if (function_exists('apply_filters')){
			$_object = apply_filters('update_'.get_class($_object).'_object',$_object);
		}

		$update_parms = array();
		$_id_parm = $_object->getIDParameter();
		
		$parms = $_object->getParameters();
		
		foreach ($parms as $k=>$v){
			// Only update if it's not the ID, if the column exists and if the value has changed
			if ($k != $_id_parm or $setIDParm){
			    if(array_key_exists($k,$this->colname) and ($v != $_object->getSavedParameter($k) or $_object->getSavedParameter($k) == "")){
				    $update_parms[$this->getColumnName($k)] = $v;
				}
			}
		}
		
		if (count($update_parms)){
			$update_parms = parent::sanitize_parms($dbh,$this->getTableName(),$update_parms);
		
			$query = $dbh->buildManipSQL($this->getTableName(),array_keys($update_parms),DB_AUTOQUERY_UPDATE);
			$query.= " WHERE ".$this->getColumnName($_id_parm)."= ?";
			if ($setIDParm){
			    $update_parms['WHERE'] = $_object->getSavedParameter($_id_parm);
			}
			else{
			    $update_parms['WHERE'] = $_object->getParameter($_id_parm);
			}
			$sth = $dbh->prepare($query); 
			//echo $dbh->executeEmulateQuery($sth,$update_parms);
			
			$result = $dbh->execute($sth,array_values($update_parms));
			if (DB::isError($result)){
				return $result;
			}
		}
		$_object->saveParameters();
		return true;
	}
	
	function getAllObjects($whereClause = "",$sort_field = "", $sort_dir = 'asc'){
		if (function_exists('apply_filters')){
			$whereClause = apply_filters('get_all_'.get_class($this).'_where_clause',$whereClause);
			$sort_field = apply_filters('get_all_'.get_class($this).'_sort_field',$sort_field);
			$sort_dir = apply_filters('get_all_'.get_class($this).'_sort_dir',$sort_dir);
		}
		if (!is_array($sort_field) and $sort_field != ""){
			$sort_field = array ($sort_field);
		}
		if (!is_array($sort_dir)){
			$sort_dir = array ($sort_dir);
		}

		if (count($sort_field) != count ($sort_dir)){
			return PEAR::raiseError("Sort_Field and Sort_Dir arrays must be of equal size");
		}

		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());

		$users = array();
		
		$query = "SELECT * FROM ".$this->getTableName();
				
		$where_clause = "";
		$where_parms = array();
		if (is_a($whereClause,'whereClause') and count($whereClause->getConditions())){
			$where_clause = " WHERE ".$whereClause->getSafeString();
			$where_parms = $whereClause->getValues();
		}		
		
		$query.=$where_clause;

		if ($sort_field != ""){
			$query.= " ORDER BY ";
			$sep = "";
			for ($i = 0 ; $i < count ($sort_field) ; $i++){
				$query.= $sep.$this->getColumnName($sort_field[$i])." ".$sort_dir[$i]." ";
				$sep = ", ";
			}	
		}
			
		$sth = $dbh->prepare($query);
		
		//echo $dbh->executeEmulateQuery($sth,$where_parms);
		
		if (PEAR::isError($result = $dbh->execute($sth,$where_parms))){
			return PEAR::raiseError("Error:  ".$result->getMessage()." <br>Query:  $query");
		}
		$numRows = $result->numRows();

		$objects = array();
		
		for ($i = 0; $i < $numRows; $i++){
			$arr = array();
			$parms = array();
			$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
			foreach($arr as $k => $v){
				$parms[$this->getParameterName($k)] = $v;
			}		
			$object = new Parameterized_Object($parms);
			$object->saveParameters();
			$objects[] = $object;
		}		
		
		if (count($objects)){
			return $objects;
		}
		else{
			return null;
		}
				
	}
	
	function getObject($whereClause = ""){
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
		
		$query = "SELECT * FROM ".$this->getTableName();
		$where_clause = "";
		$where_parms = array();
		if (is_a($whereClause,'whereClause') and count($whereClause->getConditions())){
			$where_clause = " WHERE ".$whereClause->getSafeString();
			$where_parms = $whereClause->getValues();
		}		
		
		$query.=$where_clause;

		$sth = $dbh->prepare($query);
		
		//echo $dbh->executeEmulateQuery($sth,$where_parms);
                
		$result =& $dbh->execute($sth,$where_parms);
		if (PEAR::isError($result)){
			return PEAR::raiseError("Error:  ".$result->getMessage()." <br>Query:  $query");
		}
		if($result->numRows()){
			$arr = array();
			$parms = array();
			$i = null;
			$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
			foreach($arr as $k => $v){
				$parms[$this->getParameterName($k)] = $v;
			}		
			$object = new Parameterized_Object($parms);
			$object->saveParameters();
			return $object;
		}		
		else{
			return null;
		}	
	}
	
	function getAllValues($parm){
	    // Returns an array of all unique values for the column associated with parm
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
	    
		$query = "SELECT DISTINCT(".$this->getColumnName($parm).") FROM ".$this->getTableName();
		$query.= " ORDER BY ".$this->getColumnName($parm)." ASC";

		$sth = $dbh->prepare($query);
		$result =& $dbh->execute($sth);
		if (PEAR::isError($result)){
			return PEAR::raiseError("Error:  ".$result->getMessage()." <br>Query:  $query");
		}
		if($result->numRows()){
		    $numRows = $result->numRows();
		    $values = array();
    		for ($i = 0; $i < $numRows; $i++){
    			$arr = array();
    			$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
    			$values[] = $arr[$this->getColumnName($parm)];
    		}			
    		return $values;
		}		
		else{
			return null;
		}	
	}
	
	function countAllObjects($whereClause){
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());

		$query = "SELECT COUNT(*) count FROM ".$this->getTableName();
		$where_clause = "";
		$where_parms = array();
		if (is_a($whereClause,'whereClause') and count($whereClause->getConditions())){
			$where_clause = " WHERE ".$whereClause->getSafeString();
			$where_parms = $whereClause->getValues();
		}		
		
		$query.=$where_clause;

		$sth = $dbh->prepare($query);
                
		//echo $dbh->executeEmulateQuery($sth,$where_parms);
		
		$result = $dbh->execute($sth,$where_parms);
		if (DB::isError($result)){
			return $result;
		}	
		else{
			$result->fetchInto($arr,DB_FETCHMODE_ASSOC);
			return $arr['count'];
		}
	}
	
	function getLimitedObjects($whereClause = "",$limit = "", $offset = "", $sort_field = "", $sort_dir = 'asc'){
		if (!is_array($sort_field) and $sort_field != ""){
			$sort_field = array ($sort_field);
		}
		if (!is_array($sort_dir)){
			$sort_dir = array ($sort_dir);
		}
		
		if (count($sort_field) != count ($sort_dir)){
			return PEAR::raiseError("Sort_Field and Sort_Dir arrays must be of equal size");
		}

		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());

		$users = array();
		
		$query = "SELECT * FROM ".$this->getTableName();
				
		$where_clause = "";
		$where_parms = array();
		if (is_a($whereClause,'whereClause') and count($whereClause->getConditions())){
			$where_clause = " WHERE ".$whereClause->getSafeString();
			$where_parms = $whereClause->getValues();
		}		
		
		$query.=$where_clause;

		if ($sort_field != ""){
			$query.= " ORDER BY ";
			$sep = "";
			for ($i = 0 ; $i < count ($sort_field) ; $i++){
				$query.= $sep.$this->getColumnName($sort_field[$i])." ".$sort_dir[$i]." ";
				$sep = ", ";
			}	
		}
		
		if ($limit != ""){
			$query.= " LIMIT $limit ";
		}
		if ($offset != ""){
			if ($limit == ""){
				// MySQL needs Limit, so insert a big big number to ensure all rows retrieved
				$query.= " LIMIT 18446744073709551610 ";
			}
			$query.= " OFFSET $offset ";
		}
			
		$sth = $dbh->prepare($query);
		
		//echo $dbh->executeEmulateQuery($sth,$where_parms);
		
		if (PEAR::isError($result = $dbh->execute($sth,$where_parms))){
			return PEAR::raiseError("Error:  ".$result->getMessage()." <br>Query:  $query");
		}
		$numRows = $result->numRows();

		$objects = array();
		
		for ($i = 0; $i < $numRows; $i++){
			$arr = array();
			$parms = array();
			$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
			foreach($arr as $k => $v){
				$parms[$this->getParameterName($k)] = $v;
			}		
			$object = new Parameterized_Object($parms);
			$object->saveParameters();
			$objects[] = $object;
		}		
		
		if (count($objects)){
			return $objects;
		}
		else{
			return null;
		}
				
	}
	
	function deleteObject($whereClause){
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());

		$query = "DELETE FROM ".$this->getTableName();
		$where_clause = "";
		$where_parms = array();
		if (is_a($whereClause,'whereClause') and count($whereClause->getConditions())){
			$where_clause = " WHERE ".$whereClause->getSafeString();
			$where_parms = $whereClause->getValues();
		}		
		
		$query.=$where_clause;

		$sth = $dbh->prepare($query);
                
		//echo $dbh->executeEmulateQuery($sth,$where_parms);
		
		$result = $dbh->execute($sth,$where_parms);
		if (DB::isError($result)){
			return $result;
		}	
		else{
			return true;
		}
	}	
	
	function batchUpdate($column,$value,$whereClause){
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());
		if (!is_a($whereClause,'WhereClause')){
			$whereClause = new WhereClause($whereClause);
		}
		if (!is_array($column)){
			$column = array($column);
		}
		if (!is_array($value)){
			$value = array($value);
		}
		if (count($column) != count($value)){
			return PEAR::raiseError("Number of columns not equal to number of values");
		}	
		
		$update_parms = array();
		foreach ($column as $c){
			$update_parms[$c] = array_shift($value);
		}
		
		$query = $dbh->buildManipSQL($this->getTableName(),array_keys($update_parms),DB_AUTOQUERY_UPDATE);
		$query.= " WHERE ".$whereClause->getSafeString();
		$update_parms = array_merge($update_parms,$whereClause->getValues());
		$sth = $dbh->prepare($query); 
		
		//echo $dbh->executeEmulateQuery($sth,array_values($update_parms));
		$result = $dbh->execute($sth,array_values($update_parms));
		if (DB::isError($result)){
			return $result;
		}
		return true;
	}

}

?>