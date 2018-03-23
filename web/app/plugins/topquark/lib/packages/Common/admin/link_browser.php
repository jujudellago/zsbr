<?php
        $FancyScript = "
            <script language='javascript' type='text/javascript' src='".CMS_INSTALL_URL."lib/js/tinymce/jscripts/tiny_mce/tiny_mce_popup.js'></script>
            <script language='javascript' type='text/javascript'>
            <!--
                function inspectMediaPopup(){
                    var win = tinyMCEPopup.getWindowArg('window');
                    
                    url = win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value;
                    if (results = url.match(/fp_style=(flam-player(-npl)?)(.*)$/i)){
                        window.location = '".$Bootstrap->makeAdminURL('flamplayer','insert_player')."&flash_vars=true&fp_style='+results[1]+'&' + results[3];
                    }
                }
                // Remove the Popup CSS file
                function removePopupCSS(){
                    var allLinks = document.getElementsByTagName('link');
                    for (var i = 0; i < allLinks.length; i++) {
                        if (allLinks[i].href && allLinks[i].href.match(/\/editor_popup.css?$/)){
                            allLinks[i].parentNode.removeChild(allLinks[i]);
                        }
                    }
                }
            
            --> 
            </script>
        ";
        $removePopupCSSIncluded = true;
        $admin_head_extras.= $FancyScript;

        switch ($_GET['subtype']){
        case 'article':
            $Package = $Bootstrap->usePackage('Article');
            $_GET['browsing'] = true;
            include ($Package->getPackageDirectory().$Package->admin_pages['manage']['url']);
            break;
        case 'image':
            if ($Bootstrap->packageExists('AdvancedGallery')){
                $Package = $Bootstrap->usePackage('AdvancedGallery');
            }
            else{
                $Package = $Bootstrap->usePackage('Gallery');
            }
    
            $_GET['browsing'] = true;
            include ($Package->getPackageDirectory().$Package->admin_pages['manage']['url']);
            break;
        case 'file':
            $Package = $Bootstrap->usePackage('PublicFileManager');
            $_GET['browsing'] = true;
            include ($Package->getPackageDirectory().$Package->admin_pages['manage']['url']);
            break;
        case 'secure_file':
            $Package = $Bootstrap->usePackage('SecureFileManager');
            $_GET['browsing'] = true;
            include ($Package->getPackageDirectory().$Package->admin_pages['manage']['url']);
            break;
        default:
            echo "<p style='text-align:center'>Please select what you would like to insert a link to:</p>\n";
            echo "<ul style='margin-left:200px'>\n";
            if ($Bootstrap->packageExists('Article')){
                echo "<li><a href='".$Bootstrap->getAdminURL()."&type=file&subtype=article'>An article</a></li>\n";
            }
            if ($Bootstrap->packageExists('AdvancedGallery') or $Bootstrap->packageExists('Gallery')){
                echo "<li><a href='".$Bootstrap->getAdminURL()."&type=file&subtype=image'>An image</a></li>\n";
            }
            if ($Bootstrap->packageExists('PublicFileManager')){
                echo "<li><a href='".$Bootstrap->getAdminURL()."&type=file&subtype=file'>A public file</a></li>\n";
            }
            if ($Bootstrap->packageExists('SecureFileManager')){
                echo "<li><a href='".$Bootstrap->getAdminURL()."&type=file&subtype=secure_file'>A secure (private) file</a></li>\n";
            }
            if ($Bootstrap->packageExists('flamplayer')){
                echo "<li><a href='".$Bootstrap->makeAdminURL('flamplayer','insert_player')."'>Flamplayer MP3 Player</a></li>\n";
            }
            echo "</ul>\n";
            echo "<center><input type='button' value='Cancel' onclick='javascript:window.close();'></center>";
            $smarty->assign('hide_navigation',true);
            break;
        }
        
        
        $user_start_function[] = 'inspectMediaPopup();';
        $user_start_function[] = 'removePopupCSS();';
    
	    $smarty->assign('admin_start_function',$user_start_function);
	    $smarty->assign('admin_head_extras',$admin_head_extras);
        
        
?>