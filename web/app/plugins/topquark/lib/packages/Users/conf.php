<?php
    require_once(PACKAGE_DIRECTORY."Common/Package.php");

    class UsersPackage extends Package{
        
        function UsersPackage(){
            $this->Package();
            
            include_once("UserContainer.php");
            include_once("UserPackageContainer.php");
        
            $this->package_name = 'Users'; // Must be the same as the directory this package is in
            $this->package_title = 'Permissions';
            $this->package_description = 'Manage the permissions with respect to Top Quark packages';
            $this->auth_level = USER_AUTH_ADMIN;
            $this->is_active = true;
            
            $this->admin_pages = array();
            $this->admin_pages['manage'] = array('url' => 'admin/manage_users.php', 'title' => 'Manage Users');
            $this->admin_pages['update'] = array('url' => 'admin/update_user.php', 'title' => 'Update User');
            
            $this->main_menu_page = 'manage';

            $this->loadUserConf();
        }
    }
    
?>