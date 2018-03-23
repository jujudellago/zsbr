<?php
	class MessageList{
		var $_messages = array();
		var $ListHasMessages = false;
		var $ListHasWarnings = false;
		var $ListHasErrors = false;
		
		function MessageList(){
		
		}
		
		function addMessage($_str,$_level = MESSAGE_TYPE_MESSAGE){
			$this->_messages[] = new Message($_str,$_level);
			switch ($_level){
			case MESSAGE_TYPE_ERROR:
				$this->ListHasErrors = true;
				break;
			case MESSAGE_TYPE_WARNING:
				$this->ListHasWarnings = true;
				break;
			}
			$this->ListHasMessages = true;
		}
		
		function addPearError($_pear_error){
			if (!PEAR::isError($_pear_error)){
				$_pear_error = PEAR::raiseError($_pear_error);
			}
			$this->addMessage("An unexpected System Error occurred.  Please contact <a href='mailto:".WEBMASTEREMAIL."?subject=".rawurlencode(SITE_NAME)."%20Error&body=".rawurlencode($_pear_error->getMessage())."'>the webmaster</a> with the following message:<br><center>\"".$_pear_error->getMessage()."\"</center>",MESSAGE_TYPE_ERROR);
		}
		
		function getMessages(){
			return $this->_messages;
		}
		
		function hasMessages(){
			return $this->ListHasMessages;
		}

		function hasWarnings(){
			return $this->ListHasWarnings;
		}

		function hasErrors(){
			return $this->ListHasErrors;
		}

		function clearMessages(){
			$this->_messages = array();
		}
		
		function getSeverestType(){
			if ($this->ListHasErrors) return ucfirst(MESSAGE_TYPE_ERROR);
			if ($this->ListHasWarnings) return ucfirst(MESSAGE_TYPE_WARNING);
			if ($this->ListHasMessages) return ucfirst(MESSAGE_TYPE_MESSAGE);
		}
		
		function toBullettedString($glue = "\n"){
			$_ret = array();
			if ($this->hasMessages()){
				$_ret[] = "<ul>";
				foreach ($this->_messages as $message){
					$tmp = "<li>";
					switch($message->getType()){
					case MESSAGE_TYPE_ERROR:
						$tmp.="<b>Error</b>: ";
						break;
					case MESSAGE_TYPE_WARNING:
						$tmp.="<i>Warning</i>: ";
						break;
					}
					$tmp.= $message->getString();
					$tmp.= "</li>";
					$_ret[] = $tmp;
				}
				$_ret[] = "</ul>";
			}
			
			if (count($_ret)){
				return implode($glue,$_ret);
			}
			else{
				return "";
			}
		}

		function toSimpleString($glue = ""){
			$_ret = array();
			if ($this->hasMessages()){
				foreach ($this->_messages as $message){
					$tmp = "";
					switch($message->getType()){
					case MESSAGE_TYPE_ERROR:
						$tmp.="<p class='MessageListError'><b>Error</b>: ";
						break;
					case MESSAGE_TYPE_WARNING:
						$tmp.="<p class='MessageListWarning'><em>Warning</em>: ";
						break;
					default:
						$tmp.="<p class='MessageListMessage'>";
					}
					$tmp.= $message->getString();
					$tmp.= "</p>\n";
					$_ret[] = $tmp;
				}
				//$_ret[] = "</ul>";
			}
			
			if (count($_ret)){
				return implode($glue,$_ret);
			}
			else{
				return "";
			}
		}
	}
	
	class Message{
		var $_text;
		var $_type;
		
		function Message($_str,$_level = MESSAGE_TYPE_MESSAGE){
			$this->_text = $_str;
			$this->_type = $_level;
		}
		
		function getString(){
			return $this->_text;
		}
		
		function getType(){
			return $this->_type;
		}
	}
?>