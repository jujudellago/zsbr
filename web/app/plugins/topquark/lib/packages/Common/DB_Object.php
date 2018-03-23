<?php

require_once 'DB.php';

class DB_Object{

	var $dsn; // Database Login Information
	var $tablename; // The table name for this DB_Object
	var $colname = array ();
	var $whereClause;


	function DB_Object(){
		if (function_exists('apply_filters')){
			$filter = 'set_'.get_class($this).'_columns';
			$this->colname = apply_filters($filter,$this->colname);
		}
	}

	function setDSN($dsn){
		$this->dsn = $dsn;
	}
	
	function getDSN(){
		return $this->dsn;
	}
	
	function connectDB(){
		return DB::connect($this->getDSN());
	}
	
	function disconnectDB(){
		return DB::disconnect();
	}
	
	function setColumnName($param,$colname){
		$this->colname[$param] = $colname;
	}
	
	function getColumnName($param){
		if (array_key_exists($param,$this->colname)){
			return $this->colname[$param];
		}
		else{
			return $param;
		}
	}
	
	function getParameterName($column){
		if (in_array($column,$this->colname)){
			return array_search($column,$this->colname);
		}
		else{
			return $column;
		}
	}
	
	function setTableName($tablename){
		$this->tablename = $tablename;
	}

	function getTableName(){
		return $this->tablename;
	}
	
	function sanitize_parms($dbh,$table,$parms){
		// Ran into a problem on one MySQL installation where INT field types cacked if '' was passed in
		$info = $dbh->tableInfo($table);
		foreach ($info as $column){
			switch(strtolower($column['type'])){
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'bigint':
			case 'int':
			case 'integer':
				if (isset($parms[$column['name']]) and $parms[$column['name']] === ''){
					unset($parms[$column['name']]);
				}
				elseif(isset($parms[$column['name']])){
					$parms[$column['name']] = intval($parms[$column['name']]);
				}
				break;
			case 'float':
			case 'double':
			case 'real':
				if ($parms[$column['name']] === ''){
					unset($parms[$column['name']]);
				}
				elseif(isset($parms[$column['name']])){
					$parms[$column['name']] = floatval($parms[$column['name']]);
				}
				break;
			}
		}
		return $parms;
	}
	
}

class whereClause{
	var $_whereCondition = array();
	var $_searchVariableList = array();
	var $_dbh;
	var $_bool;
	
	function whereClause($_clause = "", $_values = array()){
		if ($_clause != ""){
			$this->addCondition($_clause,$_values);
		}
		$this->setConnector("AND");
	}
	
	function setConnector($_bool){
		$this->_bool = $_bool;
	}
	
	function getConnector(){
		return $this->_bool;
	}
	
	function addCondition($_clause,$_values = array()){
		if (is_a($_clause,'whereClause')){
			$_values = $_clause->getValues();
			$_clause = $_clause->getSafeString();
		}
		
		if (!is_array($_values)){
			$_values = array($_values);
		}
		
		$var_placeholders = substr_count($_clause,"?");
		if ($var_placeholders != count($_values)){
			return false;
		}
		
		$_clause = str_replace("LIKE ?","LIKE concat('%',?,'%')",$_clause);
		$_clause = str_replace("like ?","LIKE concat('%',?,'%')",$_clause);
		$_clause = str_replace("Like ?","LIKE concat('%',?,'%')",$_clause);
		
		$this->_whereCondition[] = $_clause;
		if ($var_placeholders > 0){
			$this->_searchVariableList = array_merge($this->_searchVariableList,$_values);
		}
		return true;
	}
	
	function getConditions(){
		return $this->_whereCondition;
	}
	
	function getValues(){
		return $this->_searchVariableList;
	}
	
	function getSafeString($_parens = true){
		$_ret = "";
		$_sep = "";
		$_connector = $this->getConnector();
		$_parens = count($this->_whereCondition) > 1 ? true : false;
		foreach ($this->_whereCondition as $clause){
			if ($_parens){
				$_ret.= $_sep."(".$clause.")";
			}else{
				$_ret.= $_sep.$clause;
			}
			$_sep = " $_connector ";
		}
		return $_ret;
	}
	
	function getRealString(){
		// Let's make use of the DB object to make this happen!
		
		// Why it is that we have to connect to the DB to be able to make this work, I don't know
		// (Well, I do know, it's that the EscapeSimple function calls mysql_real_escape_string()
		// which fails without a connection.
		if (!$this->_dbh){
			$this->_dbh = DB::connect(DSN);
		}
		$sth = $this->_dbh->prepare($this->getSafeString()); 
		return $this->_dbh->executeEmulateQuery($sth,$this->getValues());
	}

}


?>