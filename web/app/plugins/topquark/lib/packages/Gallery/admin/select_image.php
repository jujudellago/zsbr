<?php

    if (!$Bootstrap){
        die ("You cannot access this file directly");
    }
    
    $GalleryImageContainer = new GalleryImagecontainer();
    
    if ($_GET['id'] != ""){
        $Image = $GalleryImageContainer->getGalleryImage($_GET['id']);
    }
    
    $smarty->assign('hide_navigation',true);
    
    if (!is_a($Image,'GalleryImage')){
        echo "Sorry, the selected image could not be found.  Please <a href='javascript:window.close();'>close this window</a> and try again.";
    }
    else{
        $GalleryContainer = new GalleryContainer();
        $Gallery = $GalleryContainer->getGallery($Image->getParameter('GalleryID'));
        echo "<center>";
        echo "<img src='".$Gallery->getGalleryDirectory(IMAGE_DIR_RELATIVE).$Image->getParameter('GalleryImageThumb')."'>";
        echo "<p>Please select the image size that you wish to include<br>(file size - width x height):</p>";
        echo "</center>";
        
        echo "<ul style='margin-left:100px'>";
        
        foreach ($GalleryImageContainer->types as $type){
            echo "<li>";
            $tmpType = str_replace('GalleryImage','',$type);
            echo "<a href=\"javascript:selectImage('".addslashes($Image->getParameter($type))."');\">$tmpType</a>";
            //echo "<a href=\"javascript:selectImage('$tmpType');\">$tmpType</a>";
            $image_size = getimagesize($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter($type));
            echo " (".round(filesize($Gallery->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter($type))/1024)."kb - ".$image_size[0]." x ".$image_size[1].")";
            echo "</li>";
        }
        echo "</ul>";
        
        echo "<center><input type='button' name='Cancel' value='Cancel' onclick='javascript:window.close()'></center>";
        
        $admin_head_extras = "
        <script language='javascript' type='text/javascript'>
        <!--
            function selectImage(file_name){
               	window.opener.selectImage('".(function_exists('get_bloginfo') ? get_bloginfo('wpurl') . '/' : BASE_URL).GALLERY_IMAGE_DIR.$Gallery->getDirectoryName()."' + file_name);
                window.close();
            }
        -->
        </script>
        ";
        
        $smarty->assign('admin_head_extras',$admin_head_extras);
    }
?>