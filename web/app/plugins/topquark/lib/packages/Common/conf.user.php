<?php

    /**************************************************
    * This file gets included as part of the the object 
    * instantiation (as long as the conf.php file calls $this->loadUserConf();)
    *
    * Just specify additional variables that you want to associate with this package like:
    *
    *   $this->foo = "bar";
    *   $this->blah = array('red', 'green', 'blue');
    *
    **************************************************/
    
    $this->admin_pages['file_browser'] = array('url' => 'admin/file_browser.php', 'title' => 'File Browser');
    $this->admin_pages['media_browser'] = array('url' => 'admin/media_browser.php', 'title' => 'Media Browser');
    $this->admin_pages['link_browser'] = array('url' => 'admin/link_browser.php', 'title' => 'File Link Browser');

?>