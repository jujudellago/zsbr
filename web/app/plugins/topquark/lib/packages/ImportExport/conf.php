<?php
    require_once(PACKAGE_DIRECTORY."Common/Package.php");

    class ImportExportPackage extends Package{
        
        function ImportExportPackage(){
            $this->Package();
            
            include_once('Importer.php');

            $this->package_name = 'ImportExport';
            $this->package_title = 'Import/Export';
            $this->package_description = 'Import/Export data to/from your site.  You will be shown choices as to which packages can be imported/exported.';
            $this->auth_level = USER_AUTH_EVERYONE;
            $this->is_active = true;
            
            $this->admin_pages = array();

			if ($this->getPortablePackages()){
	            $this->admin_pages['manage'] = array('url' => 'admin/import_export.php', 'title' => 'Import/Export');
	            $this->admin_pages['import'] = array('url' => 'admin/import.php', 'title' => 'Import');
	            $this->admin_pages['export'] = array('url' => 'admin/export.php', 'title' => 'Export');
	            $this->admin_pages['test'] = array('url' => 'admin/test.php', 'title' => 'Test');
	            $this->main_menu_page = 'manage';
			}
            
            $this->loadUserConf();
            
        }      

  		function getPortablePackages(){
			$Bootstrap = Bootstrap::getBootstrap();
			if (CMS_PLATFORM == 'WordPress'){
				add_filter('get_all_packages_ignore',array(&$this,'_notImportExport'));
				$_Packages = $Bootstrap->getAllPackages();
				remove_filter('get_all_packages_ignore',array(&$this,'_notImportExport'));
				foreach ($_Packages as $key => $item){
					if (!$item->is_active){
						unset($_Packages[$key]);
					}
				}
			}
			else{
				$_Packages = $Bootstrap->getAuthorizedAdminMenuPackages();		
			}
			$PortablePackages = array();
			foreach ($_Packages as $_Package){
			    if ($_Package->isImportable or $_Package->isExportable){
			        $PortablePackages[] = array('package' => $_Package, 'importable' => $_Package->isImportable, 'exportable' => $_Package->isExportable);
			    }
				if (is_array($_Package->extraPortables)){
					foreach ($_Package->extraPortables as $sub => $extraPortable){
				        $PortablePackages[] = array('package' => $_Package, 'sub' => $sub, 'importable' => isset($extraPortable['importer']), 'exportable' => isset($extraPortable['exporter']));
					}
				}
			}
			return $PortablePackages;
		}
		
		function _notImportExport($ignore){
			$ignore[] = 'ImportExport';
			return $ignore;
		}
  }
    
    
?>