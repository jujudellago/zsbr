<?php
        $InspectMediaPopup = true;
        
        // First, we'll define some functions that will help us in this process
        $FancyScript = "
            <script language='javascript' type='text/javascript' src='".CMS_INSTALL_URL."lib/js/tinymce/jscripts/tiny_mce/tiny_mce_popup.js'></script>
            <script language='javascript' type='text/javascript'>
            <!--
                function inspectMediaPopup(){
                    var win = tinyMCE.getWindowArg('window');
                    
                    url = win.document.getElementById(tinyMCE.getWindowArg('input')).value;
                    if (results = url.match(/(flam-player(-npl)?)\.swf/i)){
                        window.location = '".$Bootstrap->makeAdminURL('flamplayer','insert_player')."&flash_vars=true&fp_style='+results[1]+'&' + win.document.getElementById('flash_flashvars').value;
                    }
                    if (results = url.match(/http:\/\/www.youtube.com\/(watch\?)?v.(.*)$/i)){
                        window.location = '".$Bootstrap->getAdminURL()."&type=media&subtype=youtube'; //&video=' + results[2];
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
        $admin_head_extras.= $FancyScript;
        
        switch ($_GET['subtype']){
        case 'youtube':
            $YouTubeURL = "http://www.youtube.com/watch?v=";
            if ($_GET['video'] != ""){
                $YouTubeURL.= $_GET['video'];
                $VideoID = $_GET['video'];
            }
            elseif ($_POST['url'] != ""){
                $YouTubeURL = urldecode($_GET['url']);
                preg_match("/http:\/\/www.youtube.com\/(watch\?)?v.(.*)$/i",$YouTubeURL,$matches);
                $VideoID = $matches[2];
            }
            else{
                $YouTubeURL.= "{video_id}";
                $VideoID = "";
            }
                
            echo "<p style='text-align:center'>Please enter the URL to the YouTube Video you wish to embed</p>";
            echo "
                <form action='javascript:void();' method='get' style='text-align:center'>
                <input type='hidden' name='package' value='".$_GET['package']."'>
                <input type='hidden' name='page' value='".$_GET['page']."'>
                <input type='hidden' name='type' value='".$_GET['type']."'>
                <input type='hidden' name='subtype' value='".$_GET['subtype']."'>
                <input type='hidden' name='default_width' value='425'>
                <input type='hidden' name='default_height' value='350'>
                <table align='center' cellpadding='3'>
                    <tr>
                        <td valign='top'>YouTube URL: </td>
                        <td><input type='text' name='url' value=''></td>
                    </tr>
                    <tr>
                        <td valign='top'>Width: </td>
                        <td><input type='text' name='width' value=''><br>(height will be set automatically to constrain proportions)</td>
                    </tr>
                    <tr>
                        <td valign='top'>Autostart: </td>
                        <td>
                            <input type='checkbox' name='autoplay'>
                        </td>
                    </tr>
                </table>
                <input type='button' value='Update Preview' onclick='javascript:updatePreview();'>
                <input type='button' value='Insert Video' onclick='javascript:insertVideo();'>
                <input type='button' value='Cancel' id='CancelButton' onclick=''>
                </form>
            ";
            
            echo "<center>
                Preview
                <div id='YouTubePreview' style='border:1px solid black;width:450px;height:375px;padding:5px;'>
                </div>
                </center>
            ";
            $UpdatePreviewScript="
                <script language='javascript' type='text/javascript'>
                <!--
                    function updatePreview(){
                        if ((video_id = getVideoIDFromURL())){
                            var f = document.forms[0];
                            var width = f.default_width.value, height = f.default_height.value, autoplay = '';
                            
                            p = document.getElementById('YouTubePreview');
                        
                            if (!isNaN(f.width.value)){
                                width = f.width.value;
                                height = setHeightBasedOnWidth(width);
                            }
                            if (f.autoplay.checked){
                                autoplay = '&autoplay=1';
                            }
                            p.innerHTML = \"<object width='\" + width + \"' height='\" + height + \"'>\"
                              + \"<param name='movie' value='http://www.youtube.com/v/\" + video_id + autoplay + \"'></param>\"
                              + \"<param name='wmode' value='transparent'></param>\"
                              + \"<embed src='http://www.youtube.com/v/\" + video_id + autoplay + \"' type='application/x-shockwave-flash' wmode='transparent' width='\" + width + \"' height='\" + height + \"'></embed>\"
                              + \"</object>\";
                        }
                    }
                    
                    function updatePreviewFromParent(){
                        var f = document.forms[0];
                        var win = tinyMCE.getWindowArg('window');
                        url = win.document.getElementById('src').value;
                        f.width.value = f.default_width.value;
                        
                        if (url != ''){
                            f.width.value = win.document.getElementById('width').value;
                            if (results = url.match(/\&autoplay=(.)/i)){
                                if (results[1] == '1'){
                                    f.autoplay.checked = true;
                                }
                                else{
                                    f.autoplay.checked = false;
                                }
                                // Now, remove any autoplay from the URL, cause we're going to set it ourselves
                                url = url.replace(results[0],'');
                            }
                            f.url.value = url;
                        }
                        
                        updatePreview();
                    }
                    
                    function setHeightBasedOnWidth(width){
                        var f = document.forms[0];
                        var default_width = parseInt(f.default_width.value);
                        var default_height = parseInt(f.default_height.value);
                        return Math.round((parseInt(width) / default_width) * default_height);
                    }
                    
                    function getVideoIDFromURL(){
                        url = document.forms[0].url.value;
                        if (results = url.match(/http:\/\/[^\.]*.youtube.com\/(watch\?)?v.(.*)$/i)){
                            return results[2];
                        }
                        else{
                            if (url != ''){
                                alert(url + \"doesn't seem to be a valid YouTube URL\");
                                return false;
                            }
                        }
                    }
                    
                    function insertVideo(){
                        if ((video_id = getVideoIDFromURL())){
                            var f = document.forms[0];
                            var win = tinyMCE.getWindowArg('window');


                            // Okay, we want to set a bunch of variables and then get the hell out of dodge.  Here goes nothing.
                
                            var URL = 'http://www.youtube.com/v/' + video_id;
                            if (f.autoplay.checked){
                                URL += '&autoplay=1';
                            }

                            // insert information now
                            win.document.getElementById('media_type').value = 'flash';
                            win.document.getElementById('src').value = URL;
                            if (f.width.value == '' || isNaN(f.width.value)){
                                win.document.getElementById('width').value = f.default_width.value;
                                win.document.getElementById('height').value = f.default_height.value;
                            }
                            else{
                                win.document.getElementById('width').value = f.width.value;
                                win.document.getElementById('height').value = setHeightBasedOnWidth(f.width.value);
                            }
                
                            // Flash Variables...
                            var flash_flashvars = '';
                                
                            win.document.getElementById('flash_flashvars').value = flash_flashvars;
                
                            win.generatePreview();
                
                            // close popup window
                            tinyMCEPopup.close();
                        }
                    }
                -->
                </script>
            ";
            $admin_head_extras.= $UpdatePreviewScript;
            if ($VideoID != ""){
                $user_start_function[] = "updatePreview('$VideoID');";
            }
            
            $user_start_function[] = "updatePreviewFromParent();";
                    
            $InspectMediaPopup = false;
            $SetCancelScript = "
                <script language='javascript' type='text/javascript'>
                <!--
                    function setCancelButtonOnClick(){
                        var win = tinyMCE.getWindowArg('window');
                
                        if (win.document.getElementById(tinyMCE.getWindowArg('input')).value != ''){
                            document.getElementById('CancelButton').attributes[\"onclick\"].value = 'javascript:window.close();';
                        }
                        else{
                            document.getElementById('CancelButton').attributes[\"onclick\"].value = \"javascript:window.location='".CMS_ADMIN_URL.$Bootstrap->makeAdminURL('Common','file_browser')."&type=media';\";
                        }
                    }
                -->
                </script>
            ";
            $admin_head_extras.= $SetCancelScript;
            $user_start_function[] = "setCancelButtonOnClick();";
            
            $smarty->assign('hide_navigation',true);
            break;
        default:
            echo "<p style='text-align:center'>Please select what you would like to insert:</p>\n";
            echo "<ul style='margin-left:200px'>\n";
            if ($Bootstrap->packageExists('flamplayer')){
                echo "<li><a href='".$Bootstrap->makeAdminURL('flamplayer','insert_player')."'>Flamplayer MP3 Player</a></li>\n";
            }
            echo "<li><a href='$thisURL&subtype=youtube'>YouTube Video</a></li>\n";
            echo "</ul>\n";
            echo "<center><input type='button' value='Cancel' onclick='javascript:window.close();'></center>";
            $smarty->assign('hide_navigation',true);
            break;
        }
        
        
        if ($InspectMediaPopup){
            $user_start_function[] = 'inspectMediaPopup();';
        }
        $user_start_function[] = 'removePopupCSS();';
    
	    $smarty->assign('admin_start_function',$user_start_function);
	    $smarty->assign('admin_head_extras',$admin_head_extras);
        
        
?>