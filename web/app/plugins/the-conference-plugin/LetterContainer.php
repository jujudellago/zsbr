<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once("Letter.php");

if (!defined ('LETTER_TABLE')){
    define('LETTER_TABLE',DATABASE_PREFIX."Letters");
}

class LetterContainer extends ObjectContainer{

	var $tablename;
	
	function LetterContainer(){
		$this->ObjectContainer();
		$this->setDSN(DSN);
		$this->setTableName(LETTER_TABLE);
		$this->setColumnName('LetterID','LetterID');
		$this->setColumnName('LetterName','LetterName');
		$this->setColumnName('LetterContent','LetterContent');
		$this->setColumnName('LetterType','LetterType');
		$this->setColumnName('LetterPageBreak','LetterPageBreak');
		$this->setColumnName('LetterLastPrintedSet','LetterLastPrintedSet');
		$this->setColumnName('LetterDataYear','LetterDataYear');
		$this->setColumnName('LetterDataSource','LetterDataSource');
		$this->setColumnName('LetterIgnoreAlreadyPrinted','LetterIgnoreAlreadyPrinted');
		$this->setColumnName('LetterAlreadyPrinted','LetterAlreadyPrinted');
		$this->setColumnName('LetterLabelDetails','LetterLabelDetails');
		$this->setColumnName('LetterPrintChoice','LetterPrintChoice');
		
		if (!$this->tableExists()){
			$this->initializeTable();
		}
	}
	
	function initializeTable(){
		$this->ensureTableExists();
		
	}
	
	function ensureTableExists(){
		$create_query="
			CREATE TABLE `".$this->getTableName()."` ( 
			  `LetterID` int(7) NOT NULL auto_increment,
			  `LetterName` varchar(50) NOT NULL,
			  `LetterContent` text Default NULL,
			  `LetterType` enum('letter','label') default NULL,
			  `LetterPageBreak` int(1) default 1,
			  `LetterLastPrintedSet` text default null,
			  `LetterDataYear` varchar(25) default NULL,
			  `LetterDataSource` varchar(25) default NULL,
			  `LetterIgnoreAlreadyPrinted` int(1) default 1,
			  `LetterAlreadyPrinted` text default null,
			  `LetterLabelDetails` text default null,
			  `LetterPrintChoice` varchar(25) default NULL,
			  PRIMARY KEY  (`LetterID`)
			) ENGINE MyISAM 
		";

		if ($this->tableExists()){
			return true;
		}
		else{
			$result = $this->createTable($create_query);
			if (PEAR::isError($result)){
				return $result;
			}
			else{
				return true;
			}
		}
	}
	
	function addLetter(&$Letter){
		return $this->addObject($Letter);
	}
	
	function updateLetter($Letter){
		return $this->updateObject($Letter);
	}
	
	function getAllLetters($whereClause = "",$_sort_field = 'LetterName', $_sort_dir = 'asc'){
		if (PEAR::isError($Objects = $this->getAllObjects($whereClause,$_sort_field, $_sort_dir))) return $Objects;
		
		if ($Objects){
            return $this->manufactureLetter($Objects);
		}
		else{
			return null;
		}
	}

    function manufactureLetter($Object){
            if (!is_array($Object)){
                    $_Objects = array($Object);
            }
            else{
                    $_Objects = $Object;
            }
            
            $Letters = array();
            foreach ($_Objects as $_Object){
                    $_tmp_Letter = new Letter();
                    $_parms = $_Object->getParameters();
                    foreach ($_parms as $key=>$value){
                            $_tmp_Letter->setParameter($key,$value);
                    }
                    $_tmp_Letter->saveParameters();
                    $Letters[$_tmp_Letter->getParameter($_tmp_Letter->getIDParameter())] = $_tmp_Letter;
            }
            
            if (!is_array($Object)){
                    return array_shift($Letters);
            }
            else{
                    return $Letters;
            }
    }	
    
	function getLetter($Letter_id){
		
		$wc = new whereClause($this->getColumnName('LetterID')." = ?",$Letter_id);
		
		if (PEAR::isError($Object = $this->getObject($wc))) return $Object;
		
		if ($Object){
                        return $this->manufactureLetter($Object);
		}
		else{
			return null;
		}
	}
        
	function deleteLetter($LetterID){
		$wc = new whereClause($this->getColumnName('LetterID')." = ?",$LetterID);
		
		return $this->deleteObject($wc);
	
	}
	
}

?>