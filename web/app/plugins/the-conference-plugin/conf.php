<?php
    require_once(PACKAGE_DIRECTORY."Common/Package.php");

    class FestivalAppPackage extends Package{
        
        function FestivalAppPackage(){
            $this->Package();

            $this->is_active = true;
            
            
            if ($this->is_active){
                $Bootstrap = Bootstrap::getBootstrap();
                if ($Bootstrap->packageExists('AdvancedGallery')){
                    $this->galleryPackage = 'AdvancedGallery';
                }
                else{
                    $this->galleryPackage = 'Gallery';
                }
                $Bootstrap->usePackage($this->galleryPackage);
                
                include_once("FestivalContainer.php");
                include_once("ArtistContainer.php");
                include_once("FestivalArtistContainer.php");
                include_once("LetterContainer.php");
                include_once("MediaContainer.php");
                include_once("ScheduleContainer.php");
                include_once("SchedulePainter.php");
                include_once("ShowContainer.php");
        
                $this->package_name = 'FestivalApp';
                $this->package_title = pluralize('Festival');
                $this->auth_level = USER_AUTH_EVERYONE;
                
                $this->version_code = '60acb48879c5d8670dcf66b3efb2c2a1';
            
                $this->admin_pages = array();
                $this->admin_pages['manage'] = array('url' => 'admin/manage_festivals.php', 'title' => pluralize('Festival'));
                $this->admin_pages['delete'] = array('url' => 'admin/delete_festival.php', 'title' => 'Delete '.vocabulary('Festival'));
                $this->admin_pages['reset'] = array('url' => 'admin/reset_festival.php', 'title' => 'Reset '.vocabulary('Festival'));
                $this->admin_pages['update_festival'] = array('url' => 'admin/update_festival.php', 'title' => 'Update '.vocabulary('Festival'));
                
                $this->admin_pages['edit_schedule'] = array('url' => 'admin/manage_schedules.php', 'title' => 'Manage Schedules');
                $this->admin_pages['schedule_settings'] = array('url' => 'admin/manage_schedule_settings.php', 'title' => 'Schedule Settings');
                $this->admin_pages['schedule_orphans'] = array('url' => 'admin/manage_schedule_orphans.php', 'title' => 'Orphaned '.pluralize('Show'));
                $this->admin_pages['delete_show'] = array('url' => 'admin/delete_show.php', 'title' => 'Delete '.vocabulary('Show'));
                $this->admin_pages['update_show'] = array('url' => 'admin/update_show.php', 'title' => 'Update '.vocabulary('Show'));
                $this->admin_pages['serialize'] = array('url' => 'admin/SerializeFestival.php', 'title' => 'Publish Schedules');
            
                $this->admin_pages['manage_artists'] = array('url' => 'admin/manage_artists.php', 'title' => pluralize('Artist'));
                $this->admin_pages['delete_artist'] = array('url' => 'admin/delete_artist.php', 'title' => 'Delete '.vocabulary('Artist'));
                $this->admin_pages['update_artist'] = array('url' => 'admin/update_artist.php', 'title' => 'Update '.vocabulary('Artist'));
                
                $this->admin_pages['printables'] = array('url' => 'admin/printables.php', 'title' => 'Printables');
                $this->admin_pages['delete_printable'] = array('url' => 'admin/delete_printable.php', 'title' => 'Delete Printable');

                $this->admin_pages['edit_printable'] = array('url' => 'admin/edit_printable.php', 'title' => 'Edit Printable');

                $you_can_also = array('manage','manage_artists','printables');
                
                if ($Bootstrap->packageExists('Tags')){
                    $this->admin_pages['make_tags'] = array('url' => 'admin/make_tags.php', 'title' => 'Make Tags');
                }
                
                $this->package_description.= "  Within this application you can add, edit, delete and manage:\n<ul style='margin-top:0px'>";
                $Bootstrap->primeAdminPage();
                $this->package_description.= "<li><a href='".$Bootstrap->makeAdminURL($this,'manage')."'>".$this->admin_pages['manage']['title']."</a> - Add ".pluralize('Artist')." to the lineup; edit and publish the ".vocabulary('Festival')." schedule</li>\n";
                $this->package_description.= "<li><a href='".$Bootstrap->makeAdminURL($this,'manage_artists')."'>".$this->admin_pages['manage_artists']['title']."</a> - Upload ".vocabulary('Artist')." photos; edit their bios; delete ".pluralize('Artist')."</li>\n";
                if ($this->isFullVersion()){
                    $this->package_description.= "<li><a href='".$Bootstrap->makeAdminURL($this,'printables')."'>".$this->admin_pages['printables']['title']."</a> - Accreditation letters; ".vocabulary('Festival')." passes; and more</li>\n";
                }
                $this->package_description.= "</ul>";
                
                $this->main_menu_page = 'manage';

				$this->etcDirectory = CMS_INSTALL_URL.'/etc/';
				
				//$this->isExportable = true;
				//$this->isImportable = true;
                include_once("FestivalArtistImporter.php");
                include_once("FestivalShowImporter.php");
                include_once("FestivalArtistExporter.php");
                include_once("FestivalShowExporter.php");
				
				$this->extraPortables = array();
				$this->extraPortables[pluralize('Artist')] = array('importer' => 'ArtistImporter','exporter' => 'ArtistExporter');
				$this->extraPortables[pluralize('Show')] = array('importer' => 'ShowImporter','exporter' => 'ShowExporter');

                $this->loadUserConf();
            }
        }
        
        function isFullVersion(){
            switch (md5($this->version_code)){
            case 'b243d89a729e33e66fa6c01392378304':
                return true;
                break;
            default:
                return false;
            }
        }

		function getPublished($what,$year){
	        if (file_exists($this->etcDirectory."{$year}{$what}.txt")){
	            $Serialized = file_get_contents($this->etcDirectory."{$year}{$what}.txt");
	            return unserialize($Serialized);
			}
			else{
				return array();
			}
		}
		
		function publishArray($what,$year,$array){
		    $Serialized = serialize($array);
			if (is_writable($this->etcDirectory)){
			    $handle = fopen($this->etcDirectory."{$year}{$what}.txt","w");
			    if ($handle){
		            fwrite($handle,$Serialized);
		            fclose($handle);
					return true;
			    }
				else{
					return false;
				}
			}
			else{
				return false;
			}
		}
        
    }
    
    
?>