<?php

    class UserFunction{
        
        function UserFunction($Package = ""){
            if ($Package != ""){
                $this->setPackage($Package);
            }
            
            $this->parms = array();
            
        }
        
        function setPackage($Package){
            if (is_a($Package,'Package')){
                $this->package = $Package->package_name;
            }
            else{
                $this->package = $Package;
            }
        }
        
        function getPackage(){
            return $this->package;
        }
        
        function addFunctionParameter($parm){
            if (!is_array($this->parms[$parm->getParameterFunction()])){
                $this->parms[$parm->getParameterFunction()] = array();
            }
            $this->parms[$parm->getParameterFunction()][$parm->getParameterID()] = $parm;
        }
        
        function getFunctionParameters($function = ""){
            if ($function  == ""){
                return $this->parms;
            }
            else{
                return $this->parms[$function];
            }
        }
        
        function addParameter($Parm,$Name,$Description = "",$Values = "",$DefaultValue = ""){
            $this->parms[$Parm] = array('Name' => $Name, 'Description' => $Description, 'Values' => $Values, 'DefaultValue' => $DefaultValue);
        }
        
        function addDependentParameter($ParentParm,$RequiredValue,$Parm,$Name,$Description = "",$Values = "",$DefaultValue = ""){
            // $Condition should be of the form array('parm' => required_value(s));
            if (!is_array($this->parms[$ParentParm]['Dependents'])){
                $this->parms[$ParentParm]['Dependents'] = array();
            }
            if (!is_array($RequiredValue)){
                $RequiredValue = array($RequiredValue);
            }
            $this->parms[$ParentParm]['Dependents'][$Parm] = array('RequiredValue' => $RequiredValue, 'Name' => $Name, 'Description' => $Description, 'Values' => $Values, 'DefaultValue' => $DefaultValue);
        }
        
        function getParameters(){
            return $this->parms;
        }
        
    }
    
    class FunctionParameter {
        
        function FunctionParameter($function = null, $id = null){
            if ($function !== null){
                $this->setParameterFunction($function);
            }
            if ($id !== null){
                $this->setParameterID($id);
            }
        }
        
        function setParameterID($id){
            $this->id = $id;
        }
        
        function getParameterID(){
            return $this->id;
        }
        
        function setParameterFunction($function){
            $this->function = $function;
        }
        
        function getParameterFunction(){
            return $this->function;
        }
        
        function setParameterName($name){
            $this->name = $name;
        }
        
        function getParameterName(){
            return $this->name;
        }
        
        function setParameterDescription($description){
            $this->description = $description;
        }
        
        function getParameterDescription(){
            return $this->description;
        }
        
        function addParameterValues($values){
            // Can either be an associative array or a simple array
            if (!is_array($this->values)){
                $this->values = array();
            }
            if (is_array($values)){
                if (!$this->array_is_associative($values)){
                    $new_values = array();
                    foreach ($values as $value){
                        $new_values[$value] = $value;
                    }
                    $values = $new_values;
                }
                $this->values = $this->values + $values;
            }
        }
        
        function getParameterValues(){
            return $this->values;
        }
        
        function setParameterDefaultValue($default){
            $this->default = $default;
        }
        
        function getParameterDefaultValue(){
            return $this->default;
        }
        
        function setParameterValueDescriptions($descriptions){
            // Can either be an associative array or a simple array
            if (!is_array($this->descriptions)){
                $this->descriptions = array();
            }
            if (is_array($descriptions)){
                $this->descriptions = array_merge($this->descriptions,$descriptions);
            }
        }
        
        function getParameterValueDescriptions(){
            return $this->value_descriptions;
        }
        
        function addDependentParameter($parm,$required_values = null){
            if (!is_array($this->dependentParameters)){
                $this->dependentParameters = array();
            }
            if ($required_values !== null){
                if (!is_array($required_values)){
                    $required_values = array($required_values);
                }
                foreach ($required_values as $required_value){
                    if (!is_array($this->dependentParameters[$required_value])){
                        $this->dependentParameters[$required_value] = array();
                    }
                    $this->dependentParameters[$required_value][] = $parm;
                }
            }
        }
        
        function getDependentParameters(){
            return $this->dependentParameters;
        }
        
        function array_is_associative ($array)
        {
            if ( is_array($array) && ! empty($array) )
            {
                for ( $iterator = count($array) - 1; $iterator; $iterator-- )
                {
                    if ( ! array_key_exists($iterator, $array) ) { return true; }
                }
                return ! array_key_exists(0, $array);
            }
            return false;
        }
        
    
    }
                
        