<?php
    if ($_GET['package'] == ""){
        $Step = 1;
    }
    else{
        $Step = 2;
    }
    
    include_once('../../../../../../Standard.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{$lang_smartycode_title}</title>
	<script language="javascript" type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="jscripts/functions.js"></script>
	<base target="_self" />
</head>
<body onload="tinyMCEPopup.executeOnLoad('init();');" style="display: none">
	<div class="title" align="center">{$lang_smartycode_step<?php echo $Step; ?>_title}:<br /></div>
<?php
    switch ($Step){
        case 1:
        // Show the available packages
            $AllPackages = $Bootstrap->getAllPackages();
            $functions = array('paint','retrieve');
            foreach ($functions as $function){
                echo "<p>Packages with <b>$function</b> available:</p>\n";
                echo "<ul>\n";
                foreach ($AllPackages as $Package){
                    $dir = $file = PACKAGE_DIRECTORY.$Package->package_name;
                    $pattern = "$dir/user.*.php";
                    foreach (glob($pattern) as $file){
                        include_once($file);
                    
                        // figure out what kind of user.*.php file this is
                        $filename_pattern = "/user\.([^\.]*)\.php/";
                        preg_match($filename_pattern,$file,$matches);
                    
                        if (class_exists($Package->package_name."__UserFunctions")){
                            $FunctionName = $Package->package_name.'__User'.ucwords($function);
                            $ClassName = $Package->package_name."__UserFunctions";
                            $ClassMethods = get_class_methods($ClassName);
                            foreach ($ClassMethods as $Method){
                                if (strtolower($Method) == strtolower($FunctionName)){
                                    echo "<li><a href='".$_SERVER['PHP_SELF']."?package=".$Package->package_name."&function=$function'>".$Package->package_title."</a></li>\n";
                                }
                            }
                        }
                    }
                }
                echo "</ul>\n";
            }
            echo "<p><input type='button' onclick='javascript:tinyMCEPopup.close();' value='Cancel'></p>";
            break;
        case 2:
            $Package = $Bootstrap->usePackage($_GET['package']);
            $ClassName = $Package->package_name."__UserFunctions";
            $UserFunctions = new $ClassName();
            $Parameters = $UserFunctions->getFunctionParameters($_GET['function']);
            
            // todo finish
            $TableDecl = "<table cellpadding='5' border='1' width='100%' style='border-collapse:collapse'>\n";
            echo "<form action=\"javascript:insertSmartyCode();\" name='SmartyCodeForm'>\n";
            echo "<input type='hidden' name='function' id='function' value='".$_GET['function']."'>\n";
            echo "<input type='hidden' name='package' value='".$_GET['package']."'>\n";
            echo "<p style='margin-bottom:3px'><b>Regular Parameters</b></p>";
            echo $TableDecl;
            $DependentParms = array();
            foreach ($Parameters as $key => $Parameter){
                echo outputParameter($key,$Parameter);

                $AllDependents = $Parameter->getDependentParameters();

                // Here's a tricky part.  We'll make some hideable DIVS for all dependent parameters
                if (is_array($AllDependents) and count($AllDependents)){
                    foreach ($AllDependents as $required_value => $Dependents){
                        foreach ($Dependents as $Dependent){
                            $dkey = $Dependent->getParameterID();
                            if (!is_array($DependentParms[$dkey])){
                                $DependentParms[$dkey] = array();
                                $DependentParms[$dkey]['Parameter'] = $Dependent;
                                $DependentParms[$dkey]['Classes'] = array();
                            }
                            $DependentParms[$dkey]['Classes'][] = 'Dependent_'.$key.'_'.$required_value;
                        }
                    }
                }
            }
            echo "</table>\n";
            if (count($DependentParms)){
                echo "<p style='margin-bottom:3px'><b>Dependent Parameters</b></p>";

                echo $TableDecl;
                foreach ($DependentParms as $key => $DependentParm){
                    echo outputParameter($key,$DependentParm['Parameter'],implode(' ',$DependentParm['Classes']));
                }
                echo "</table>\n";
            }
            echo "</form>\n";
            
            echo "<p>";
            echo "<input type='button' onclick='javascript:(document.location.href=\"smartycode.php\");' id='CancelButton' value='Cancel'> ";
            echo "<input type='button' onclick='javascript:insertSmartyCode();' value='Insert Code'> ";
            echo "</p>";
            break;
        default:
    }
    
    function outputParameter($key,$Parameter,$Class = ""){
        ob_start();
        echo "<tr ".($Class != "" ? "class='$Class'" : "") .">\n";
        echo "<td width='25%' valign='top'><b>".$Parameter->getParameterName().":</b><br>".$Parameter->getParameterDescription()."</td>\n";
        echo "<td width='75%' valign='top'>\n";
        
        $Dependents = $Parameter->getDependentParameters();
        
        if (is_array($Dependents) and count($Dependents)){
            $OnChange = "onchange=\"javascript:updateDependents('$key');\"";
        }
        else{
            $OnChange = "";
        }
        
        $Values = $Parameter->getParameterValues();
        if (is_array($Values)){
            echo "<select  ".($Class != "" ? "class='$Class'" : "") ." name='".$key."' id='".$key."' $OnChange>\n";
            foreach ($Values as $index => $Value){
                echo "<option value='$index'";
                if ($index == $Parameter->getParameterDefaultValue()){
                    echo " selected";
                }
                echo ">";
                if (is_a($Value,'FunctionParameter')){
                    echo $Value->getParameterName();
                }
                else{
                    echo $Value;
                }
                echo "</option>\n";
            }
            echo "</select>\n";
        }
        else{
            echo "<input  ".($Class != "" ? "class='$Class'" : "") ." type='text' name='".$key."' id='".$key."' value='".$Parameter->getParameterDefaultValue()."' $OnChange>\n";
        }
        echo "</td>\n";
        echo "</tr>\n";
        return ob_get_clean();
    }
?>		
</body>
</html>
