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
    
    include_once(dirname(__FILE__)."/../../Standard.php");
    
	if (function_exists('apply_filters')){
	    $this->base_url = apply_filters('topquark_gallery_base_url',trailingslashit(get_permalink()).'?show=photos');
	}
	else{
	    $this->base_url = BASE_URL.'festival/photos/?show=photos';
	}
    $this->base_title = 'Photo Galleries';
    $this->gallery_types = array('public','private','system');
    $this->public_gallery_types = array('public');
    $this->galleries_to_keep_original = array('Media Photos');
    $this->allow_credit_on_image = true;
    $this->use_gallery_year = false;
    
?>