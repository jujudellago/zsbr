<?php
    require_once(PACKAGE_DIRECTORY."Common/Package.php");

    class CommonPackage extends Package{
        
        function CommonPackage(){
            $this->Package();

            $this->package_name = 'Common';
            $this->package_title = 'Common Files';
            $this->package_description = 'Files that can be used across the website.';
            $this->auth_level = USER_AUTH_EVERYONE;
            $this->is_active = false;
            
            $this->admin_pages = array();
            
            $this->loadUserConf();
            
        }
    }
    
    
?>