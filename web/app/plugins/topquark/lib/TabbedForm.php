<?php
include_once('HTML_Form-1.3.0/HTML/Form.php');

class HTML_TabbedForm extends HTML_Form{
	var $tabs = array();
        var $_active_tab_added;
	
    function HTML_TabbedForm($action, $method = 'get', $name = '', $target = '',
                       $enctype = '', $attr = ''){
		$this->HTML_Form($action,$method,$name,$target,$enctype,$attr);
                $this->_active_tab_added = false;
    }
    
    function setActiveTab($ActiveTab){
            if (!$this->_active_tab_added){
                    $this->_active_tab_added = true;
                    $this->addHidden('active_tab',"group_".HTML_Tab::returnID($ActiveTab));                       
            }
    }
        
	function addTab(&$tab){
		$this->tabs[] = &$tab;
	}
	
	function getTabs(){
		return $this->tabs;
	}

	// This function NFG.	
	function setDefaultTab($tab_id){
		$tabs = $this->getTabs();
		foreach ($this->tabs as $tab){
			if ($tab->getID() == $tab_id){
				$tab->setDefault();
			}
			else{
				$tab->clearDefault();
			}
		}
		
	}
	
	function getName(){
		return $this->name;
	}
	
    /**
     * Prints a complete form with all fields you specified via
     * the add*() methods
     *
     * If you did not specify a field's default value (via the $default
     * parameter to the add*() method in question), this method will
     * automatically insert the user input found in $_GET/$_POST.  This
     * behavior can be disabled via setDefaultFromInput(false).
     *
     * The $_GET/$_POST input is automatically escaped via htmlspecialchars().
     * This behavior can be disabled via setEscapeDefaultFromInput(false).
     *
     * If the $_GET/$_POST superglobal doesn't exist, then
     * $HTTP_GET_VARS/$HTTP_POST_VARS is used.
     *
     * NOTE: can NOT be called statically.
     *
     * @param string $attr     a string of additional attributes to be put
     *                          in the <table> tag (example: 'class="foo"')
     * @param string $caption  if present, a <caption> is added to the table
     * @param string $capattr  a string of additional attributes to be put
     *                          in the <caption> tag (example: 'class="foo"')
     * @return void
     *
     * @access public
     * @see HTML_Form::end(), HTML_Form::returnEnd(),
     *      HTML_Form::setDefaultFromInput(),
     *      HTML_Form::setEscapeDefaultFromInput()
     */
    function display($attr = '')
    {
		if (function_exists('do_action')){
			$action = $_GET['package'].'_'.$_GET[ADMIN_PAGE_PARM].'_display_tabs';
			echo "<!-- action: $action -->\n";
			do_action($action,array(&$this));
		}
        // Set the active_tab hidden variable correctly
		foreach ($this->tabs as $tab){
        	if ($tab->isDefault()){
		        $this->addHidden('active_tab','group_'.$tab->getID());                       
    	    }
        }
        // Determine where to get the user input from.

        if (strtoupper($this->method) == 'POST') {
            if (!empty($_POST)) {
                $input =& $_POST;
            } else {
                if (!empty($HTTP_POST_VARS)) {
                    $input =& $HTTP_POST_VARS;
                } else {
                    $input = array();
                }
            }
        } else {
            if (!empty($_GET)) {
                $input =& $_GET;
            } else {
                if (!empty($HTTP_GET_VARS)) {
                    $input =& $HTTP_GET_VARS;
                } else {
                    $input = array();
                }
            }
        }

        $this->start();
        $this->displayTabs($this->tabs,$attr);
        
        print '<table ' .  $attr . ">\n";

        /*
         * Go through each field created through the add*() methods
         * and pass their arguments on to the display*Row() methods.
         */

        $hidden = array();
        $buttons = array();
        foreach ($this->fields as $field_index => $field) {
            $type = $field[0];
            $name = $field[1];

            switch ($type) {
                case 'hidden':
                    // Deal with these later so they don't mess up layout.
                    $hidden[] = $field_index;
                    continue 2;
            }

            if ($this->_default_from_input
                && $this->_default_params[$type]
                && $field[$this->_default_params[$type]] === null
                && array_key_exists($name, $input))
            {
                // Grab the user input from $_GET/$_POST.
                if ($this->_escape_default_from_input) {
                    $field[$this->_default_params[$type]] =
                            htmlspecialchars($input[$name]);
                } else {
                    $field[$this->_default_params[$type]] = $input[$name];
                }
            }

            array_shift($field);
            if (strtolower($type) == 'submit'){
	        	$buttons[] = call_user_func_array(
		                array(&$this, 'return' . ucfirst($type)),
		                array($field[1],$field[0],$field[2])
		            );
            }
            elseif(strtolower($type) != 'reset'){
	        	$buttons[] = call_user_func_array(
		                array(&$this, 'return' . ucfirst($type)),
		                array($field[1],$field[2])
		            );
		    }else{
	            call_user_func_array(
	                array(&$this, 'display' . ucfirst($type) . 'Row'),
	                $field
	            );
	        }
        }
        if (count($buttons)){
        	$this->displayPlaintextRow('&nbsp;',implode(' ',$buttons),HTML_FORM_TH_ATTR);
        }

        print "</table>\n";

        for ($i = 0; $i < sizeof($hidden); $i++) {
            $this->displayHidden($this->fields[$hidden[$i]][1],
                                 $this->fields[$hidden[$i]][2],
                                 $this->fields[$hidden[$i]][3]);
        }

        $this->end();
    }
    
    function displayTabs($Tabs,$attr = '',$switch_function = 'switchto'){
        echo "<table " .  $attr . " cellspacing=0><tr>\n";
        $display_spacer = false;
        foreach ($Tabs as $tab){
        	if ($tab->isDefault()) $hi = '-hi'; else $hi = '';
        	
        	if ($display_spacer){
				echo "<td class='tabspacer'>&nbsp;</td>\n";
        	}
 	       	$display_spacer = true;
        	echo "<td class='tab$hi' id='tab_group_" . $tab->getID() . "' onClick=\"$switch_function('group_" . $tab->getID() . "')\">" . $tab->getName() . "</td>\n";
        }
        echo "</tr></table>\n";

		echo "<div class='tab-content-wrapper'>\n";
		foreach ($Tabs as $tab){
        	if ($tab->isDefault()) $display = 'inline'; else $display = 'none';

			echo "<div id='group_" . $tab->getID() . "' class='tab-content' style='display: $display'>\n";
 	        echo "<table " .  $attr . ">\n";
 	        $items = $tab->getItems();
			if (function_exists('apply_filters')){
				$filter = $_GET['package'].'_'.$_GET[ADMIN_PAGE_PARM].'_'.$tab->getID().'_items';
				echo "<!-- filter: $filter -->\n";
				$items = apply_filters($filter,$items,$tab->getID());
			}
 	        
 	        if (is_array($items)){
 	        	foreach ($items as $item){
 	        		echo $item."\n";
 	        	}
 	        }
 	        echo "</table>\n";
			echo "</div>\n";			
		}
		echo "</div>\n";
    }
        

    // }}}

}

class HTML_Tab{

	var $_name;
	var $_id;
	var $_item_rows = array();
	var $_default = false;
	
	function HTML_Tab($id,$name){
		$this->_id = $id;	
		$this->_name = $name;	
	}
	
	function addItem($item_row_string){
		$this->_item_rows[] = $item_row_string;
	}
	
	function getItems(){
		return $this->_item_rows;
	}

	function getID(){
		return $this->returnID($this->_id);
	}
	
	function returnID($id){
	    return preg_replace("/[^A-Za-z0-9_]/","_",$id);
	}
	
	function getName(){
		return $this->_name;
	}
	
	function setDefault(){
		$this->_default = true;
	}
	
	function clearDefault(){
		$this->_default = false;
	}
	
	function isDefault(){
		return $this->_default;
	}
	
    function addText($name, $title, $default = null,
                     $size = HTML_FORM_TEXT_SIZE, $maxlength = 0,
                     $attr = '', $thattr = HTML_FORM_TH_ATTR,
                     $tdattr = HTML_FORM_TD_ATTR)
    {
    	$this->addItem(HTML_Form::returnTextRow($name, $title, $default, $size,
                                $maxlength, $attr, $thattr, $tdattr));
    }

    function addPassword($name, $title, $default = null,
                         $size = HTML_FORM_PASSWD_SIZE,
                         $maxlength = 0, $attr = '', $thattr = HTML_FORM_TH_ATTR,
                         $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnPasswordRow($name, $title, $default, $size,
                                $maxlength, $attr, $thattr, $tdattr));
    }
	
    function addPasswordOne($name, $title, $default = null,
                            $size = HTML_FORM_PASSWD_SIZE,
                            $maxlength = 0, $attr = '', $thattr = HTML_FORM_TH_ATTR,
                            $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnPasswordOneRow($name, $title, $default, $size,
                                $maxlength, $attr, $thattr, $tdattr));
    }

    function addCheckbox($name, $title, $default = false, $attr = '',
                         $thattr = HTML_FORM_TH_ATTR,
                         $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnCheckboxRow($name, $title, $default, $attr,
                                $thattr, $tdattr));
    }

    function addTextarea($name, $title, $default = null,
                         $width = HTML_FORM_TEXTAREA_WT,
                         $height = HTML_FORM_TEXTAREA_HT, $maxlength = 0,
                         $attr = '', $thattr = HTML_FORM_TH_ATTR,
                         $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnTextareaRow($name, $title, $default, $width,
                                $height, $maxlength, $attr, $thattr, $tdattr));
    }

    function addSubmit($name = 'submit', $title = 'Submit Changes',
                       $attr = '', $thattr = HTML_FORM_TH_ATTR,
                       $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnSubmitRow($name, $title, $attr, $thattr,
                                $tdattr));
    }

    function addReset($title = 'Discard Changes', $attr = '',
                      $thattr = HTML_FORM_TH_ATTR,
                      $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnResetRow($title, $attr, $thattr, $tdattr));
    }

    function addSelect($name, $title, $entries, $default = null, $size = 1,
                       $blank = '', $multiple = false, $attr = '',
                       $thattr = HTML_FORM_TH_ATTR,
                       $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnSelectRow($name, $title, $entries, $default,
                                $size, $blank, $multiple, $attr, $thattr,
                                $tdattr));
    }

    function addRadio($name, $title, $value, $default = false, $attr = '',
                      $thattr = HTML_FORM_TH_ATTR,
                      $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnRadioRow($name, $title, $value, $default,
                                $attr, $thattr, $tdattr));
    }

    function addImage($name, $title, $src, $attr = '',
                      $thattr = HTML_FORM_TH_ATTR,
                      $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnImageRow($name, $title, $src, $attr, $thattr,
                                $tdattr));
    }

    function addHidden($name, $value, $attr = '')
    {
        $this->addItem(HTML_Form::returnHidden($name, $value, $attr));
    }

    function addBlank($i, $title = '', $thattr = HTML_FORM_TH_ATTR,
                      $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnBlankRow($i, $title, $thattr, $tdattr));
    }

    function addFile($name, $title, $maxsize = HTML_FORM_MAX_FILE_SIZE,
                     $size = HTML_FORM_TEXT_SIZE, $accept = '', $attr = '',
                     $thattr = HTML_FORM_TH_ATTR,
                     $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnFileRow($name, $title, $maxsize, $size,
                                $accept, $attr, $thattr, $tdattr));
    }

    function addPlaintext($title, $text = '&nbsp;',
                          $thattr = HTML_FORM_TH_ATTR,
                          $tdattr = HTML_FORM_TD_ATTR)
    {
        $this->addItem(HTML_Form::returnPlainTextRow($title, $text, $thattr, $tdattr));
    }
    
    function addTabSet($TabSet = array(),$attr = ''){
        ob_start();
        HTML_TabbedForm::displayTabs($TabSet,$attr,'subswitchto');
        $tmp = ob_get_clean();
        $this->addItem($tmp);
    }
    
    function returnTabSetScript($TabSet){
        $return = "";
        $return.= "<script language='javascript' type='text/javascript'>\n";
        $return.= "<!-- Script to help switching between subtabs of the ".$this->getName()." tab\n";
        $return.= "\t\tvar ".$this->getID()."_Sections = new Array();\n";
        $default = "";
        foreach ($TabSet as $i => $Tab){
            $return.= "\t\t".$this->getID()."_Sections[{$i}] = 'group_".$Tab->getID()."';\n";
            if ($default == "" or $Tab->isDefault()){
                $default = $Tab->getID();
            }
        }
        $return.= "\t\tgroup_".$this->getID()."_section_tabs = new configSection('group_{$default}','".$this->getID()."_Sections');\n";
        $return.= "-->\n";
        $return.= "</script>\n\n";
        return $return;        
    }

}
?>