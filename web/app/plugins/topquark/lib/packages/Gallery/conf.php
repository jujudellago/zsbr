<?php
    require_once(PACKAGE_DIRECTORY."Common/Package.php");

    class GalleryPackage extends Package{
        
        function GalleryPackage(){
            $this->Package();

            $this->is_active = true;
            
            if ($this->is_active){
                include_once("GalleryContainer.php");
                include_once("GalleryImageContainer.php");
                include_once("ImageSetContainer.php");
                include_once("ImageSetImageContainer.php");
        
                $this->package_name = 'Gallery';
                $this->package_title = 'Gallery';
                $this->package_description = 'Upload photos, add captions, create galleries and image sets';
                $this->auth_level = USER_AUTH_EVERYONE;

                $this->admin_pages = array();
                $this->admin_pages['manage'] = array('url' => 'admin/manage_galleries.php', 'title' => 'Photo Galleries');
                $this->admin_pages['delete'] = array('url' => 'admin/delete_gallery.php', 'title' => 'Delete Gallery');
                $this->admin_pages['update'] = array('url' => 'admin/make_gallery.php', 'title' => 'Update Gallery');
                $this->admin_pages['select_image'] = array('url' => 'admin/select_image.php', 'title' => 'Select Image Size');
                $this->admin_pages['export'] = array('url' => 'admin/export.php', 'title' => 'Export Images');
                $this->admin_pages['batch_resize'] = array('url' => 'admin/batch_resize.php', 'title' => 'Batch Resize');
            
                $this->main_menu_page = 'manage';
                
                $this->sizes = array('Original','Resized','Thumb');

              $this->loadUserConf();
            }
        }
        
    }
    
    
?>