<?php
/*
Plugin Name: Logos Showcase
Plugin URI: http://www.cmoreira.net/logos-showcase
Description: This plugin allows you to display images on a responsive grid or carousel. It's perfect to display logos of clients, partners, sponsors or any other group of images that requires this type of layout.
Author: Carlos Moreira
Version: 1.2.2
Author URI: http://cmoreira.net
*/

//Last modified: June 3rd 2013
//Last Edit: Better Preview Screen and settings.php file to control loading of scripts.

//include advanced settings
require_once dirname( __FILE__ ) . '/settings.php';


//Adding the necessary actions to initiate the plugin
add_action('init', 'register_cpt_lshowcase' );
add_action('admin_init', 'register_lshowcase_settings' );
add_action('do_meta_boxes', 'lshowcase_image_box');
add_action('admin_menu' , 'lshowcase_shortcode_page_add');
add_action('admin_menu' , 'lshowcase_admin_page');
add_filter('manage_posts_columns', 'lshowcase_columns_head');
add_action('manage_posts_custom_column', 'lshowcase_columns_content', 10, 2);

$lshowcase_slider_on = false;


//Add support for post-thumbnails in case theme does not
add_action('init' , 'lshowcase_add_thumbnails_for_cpt');

function lshowcase_add_thumbnails_for_cpt() {

    global $_wp_theme_features;

   if($_wp_theme_features['post-thumbnails']==1) {
		return;		
	  }	
	  
	  if(is_array($_wp_theme_features['post-thumbnails'][0]) && count($_wp_theme_features['post-thumbnails'][0]) >= 1) {
		array_push($_wp_theme_features['post-thumbnails'][0],'lshowcase');
		return;
		}
	if( empty($_wp_theme_features['post-thumbnails']) ) {
        $_wp_theme_features['post-thumbnails'] = array( array('lshowcase') );
		return;
	}
}



//Add New Thumbnail Size
$lshowcase_crop = false;
$lshowcase_options = get_option('lshowcase-settings');
if($lshowcase_options['lshowcase_thumb_crop']=="true") {
$lshowcase_crop = true;
}
add_image_size( 'lshowcase-thumb', $lshowcase_options['lshowcase_thumb_width'], $lshowcase_options['lshowcase_thumb_height'], $lshowcase_crop);

//register the custom post type for the logos showcase
function register_cpt_lshowcase() {

	$options = get_option('lshowcase-settings');	
	$name = $options['lshowcase_name_singular'];
	$nameplural = $options['lshowcase_name_plural'];

    $labels = array( 
        'name' => _x( $nameplural, 'lshowcase' ),
        'singular_name' => _x( $name, 'lshowcase' ),
        'add_new' => _x( 'Add New '.$name, 'lshowcase' ),
        'add_new_item' => _x( 'Add New '.$name, 'lshowcase' ),
        'edit_item' => _x( 'Edit '.$name, 'lshowcase' ),
        'new_item' => _x( 'New '.$name, 'lshowcase' ),
        'view_item' => _x( 'View '.$name, 'lshowcase' ),
        'search_items' => _x( 'Search '.$nameplural, 'lshowcase' ),
        'not_found' => _x( 'No '.$nameplural.' found', 'lshowcase' ),
        'not_found_in_trash' => _x( 'No '.$nameplural.' found in Trash', 'lshowcase' ),
        'parent_item_colon' => _x( 'Parent '.$name.':', 'lshowcase' ),
        'menu_name' => _x( $nameplural, 'lshowcase' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,        
        'supports' => array( 'title', 'thumbnail', 'custom-fields' ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,       
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post',
		 'menu_icon' => plugins_url( 'images/icon16.png', __FILE__ ),
    );

    register_post_type( 'lshowcase', $args );
}


//register custom category

// WP Menu Categories
add_action( 'init', 'lshowcase_build_taxonomies', 0 );

function lshowcase_build_taxonomies() {
    register_taxonomy( 'lshowcase-categories', 'lshowcase', array( 'hierarchical' => true, 'label' => 'Categories', 'query_var' => true, 'rewrite' => true ) );
}

 
//move featured image box to top
function lshowcase_image_box() {

	remove_meta_box( 'postimagediv', 'lshowcase', 'side' );
	add_meta_box('postimagediv', __('Logo Image'), 'post_thumbnail_meta_box', 'lshowcase', 'normal', 'high');

}

//change Title Info

function lshowcase_change_default_title( $title ){
     $screen = get_current_screen();
	 $options = get_option('lshowcase-settings');	
	$name = $options['lshowcase_name_singular'];
	$nameplural = $options['lshowcase_name_plural'];
 
     if  ( 'lshowcase' == $screen->post_type ) {
          $title = 'Insert '.$name.' Name Here';
     }
 
     return $title;
}
 
add_filter( 'enter_title_here', 'lshowcase_change_default_title' );


function lshowcase_wps_translation_mangler($translation, $text, $domain) {
    global $post;
	if(isset($post)) {
    if ($post->post_type == 'lshowcase') {
        $translations = &get_translations_for_domain( $domain);
       
        if ( $text == 'Publish') {
            return $translations->translate( 'SAVE' );
        }
	}
		
		
    }
    return $translation;
}
add_filter('gettext', 'lshowcase_wps_translation_mangler', 10, 4);



/**
 * Display the URL metabox
 */
function lshowcase_url_custom_metabox() {
	global $post;
	$urllink = get_post_meta( $post->ID, 'urllink', true );
	$urldesc = get_post_meta( $post->ID, 'urldesc', true );
	
	if ($urllink!="" && !preg_match( "/http(s?):\/\//", $urllink )) {
		$errors = 'Url not valid';
		$urllink = 'http://';
	} 
	
	// output invlid url message and add the http:// to the input field
	if( isset($errors) ) { echo $errors; } ?>
<table cellpadding="10"><tr><td valign="top">	
<p><label for="siteurl">Url:<br>
  <input id="siteurl" size="37" name="siteurl" type="url" value="<?php if( $urllink ) { echo $urllink; } ?>" /></label></p>
        </td><td valign="top">
	<p><label for="urldesc">Description:<br>

		<textarea id="urldesc" name="urldesc" ><?php if( $urldesc ) { echo $urldesc; } ?></textarea></label></p>
        </td>
        <td valign="top"><p>Use this fields to fill out the URL you want your Image Logo to have. <br>
          The description field will be the alt tag (alternative text) for the URL and Image.</p>
        <p>Don't forget to set a featured Image below. </p></td>
</tr>
</table>
<?php
}

/**
 * Process the custom metabox fields
 */
function lshowcase_save_custom_url( $post_id ) {
	global $post;	
	if(isset($post)) {
		if ($post->post_type == 'lshowcase') {	
		if( $_POST ) {
			update_post_meta( $post->ID, 'urllink', $_POST['siteurl'] );
			update_post_meta( $post->ID, 'urldesc', $_POST['urldesc'] );
		}
		}
	}
}

// Add action hooks. Without these we are lost
add_action( 'admin_init', 'lshowcase_add_custom_metabox' );
add_action( 'save_post', 'lshowcase_save_custom_url' );

/**
 * Add meta box
 */
function lshowcase_add_custom_metabox() {
	add_meta_box( 'lshowcase-custom-metabox', __( 'URL &amp; Description' ), 'lshowcase_url_custom_metabox', 'lshowcase', 'normal', 'high' );
}

/**
 * Get and return the values for the URL and description
 */
function lshowcase_get_url_desc_box() {
	global $post;
	$urllink = get_post_meta( $post->ID, 'urllink', true );
	$urldesc = get_post_meta( $post->ID, 'urldesc', true );

	return array( $urllink, $urldesc );
}

// get the array of data
//$urlbox = get_url_desc_box();
//echo $urlbox[0]; // echo out the url of a post
//echo $urlbox[1]; // echo out the url description of a post


//add shortcode generator page
function lshowcase_shortcode_page_add() {
	
	$menu_slug = 'edit.php?post_type=lshowcase';
	$submenu_page_title = 'Shortcode Generator';
    $submenu_title = 'Shortcode Generator';
	$capability = 'manage_options';
    $submenu_slug = 'lshowcase_shortcode';
    $submenu_function = 'lshowcase_shortcode_page';
    $defaultp = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	
	
	add_action($defaultp, 'lshowcase_enqueue_admin_js');
	
   }

function lshowcase_enqueue_admin_js() {
	
	wp_deregister_script('lshowcaseadmin');
	wp_register_script( 'lshowcaseadmin', plugins_url( '/js/shortcode-builder.js' , __FILE__ ), array('jquery') );
	wp_enqueue_script( 'lshowcaseadmin' );
	
	wp_deregister_style( 'lshowcase-main-style' );
	wp_register_style( 'lshowcase-main-style', plugins_url( '/styles.css', __FILE__ ),array(),false,false);
	wp_enqueue_style( 'lshowcase-main-style' );
	
	// in javascript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'lshowcaseadmin', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	
	wp_deregister_script( 'lshowcase-bxslider' );
	wp_register_script( 'lshowcase-bxslider', plugins_url( '/bxslider/jquery.bxslider.js', __FILE__ ),array('jquery'),false,false);
	wp_enqueue_script( 'lshowcase-bxslider' );
	
	wp_deregister_style( 'lshowcase-bxslider-style' );
	wp_register_style( 'lshowcase-bxslider-style', plugins_url( '/bxslider/jquery.bxslider.css', __FILE__ ),array(),false,false);
	wp_enqueue_style( 'lshowcase-bxslider-style' );
	
	
			
	wp_deregister_script( 'ls-jquery-ui' );
	wp_register_script( 'ls-jquery-ui', plugins_url( '/js/jquery-ui-1.10.2.custom.min.js', __FILE__ ),array( 'jquery' ),false,false);
	wp_enqueue_script( 'ls-jquery-ui' );
	
	wp_deregister_script( 'lshowcase-tooltip' );
	wp_register_script( 'lshowcase-tooltip', plugins_url( '/js/tooltip.js', __FILE__ ),array('ls-jquery-ui'),false,false);
	wp_enqueue_script( 'lshowcase-tooltip' );
	
}



add_action('wp_ajax_my_action', 'lshowcase_run_preview');

function lshowcase_run_preview() {	
	
	$orderby = $_POST['porder'];
	$category = $_POST['pcategory'];
	$activeurl =  $_POST['purl'];
	$style = $_POST['pstyle'];
	$interface = $_POST['pinterface']; 
	$tooltip = $_POST['ptooltip']; 	 
	$limit = $_POST['plimit'];
	
	$html = build_lshowcase($orderby,$category,$activeurl,$style,$interface,$tooltip,$limit);
	
	echo $html;
	die(); // this is required to return a proper result
}



function lshowcase_shortcode_page() { ?>
	
<h1>Shortcode Generator</h1>
    
    <script type="text/javascript">
	function  lshowcasegetclass(style) {
		
		var stylearray = [];
		<?php 
		  $stylesarray = lshowcase_styles_array();		  
		  foreach($stylesarray as $option => $key) { 
		  ?>         
		stylearray['<?php echo $option; ?>'] = "<?php echo $key['class']; ?>";
		<?php } ?>
		
		return stylearray[style];
	}
	
	</script>
    
    <table cellpadding="10" cellspacing="10">
      <tr><td valign="top">
    <div class="postbox" style="width:300px;">
    <form id="shortcode_generator" style="padding:20px;">
           
<p>
        <label for="orderby">Order By:<br>
        </label>
        <select id="orderby" name="orderby" onChange="lshowcaseshortcodegenerate()">
            <option value="none">None</option>
             <option value="name">Title</option>
            <option value="ID">ID</option>
            <option value="date">Date</option>
            <option value="modified">Modified</option>
            <option value="rand">Random</option>
        </select></p>
 	 <p><label for="limit">Number of Images to display:</label><br>

        <input size="3" id="limit" name="limit" type="text" value="0" onChange="lshowcaseshortcodegenerate()" /><span class="howto"> (Leave blank or 0 to display all)</span></p>
    
<p><label for="category">Category</label>
     :
       <br>
        <select id="category" name="category" onChange="lshowcaseshortcodegenerate()">
          <option value="0">All</option>
        
  <?php 
		
				 $terms = get_terms("lshowcase-categories");
				 $count = count($terms);
				 if ( $count > 0 ){
					 
					 foreach ( $terms as $term ) {
					    echo "<option value='".$term->slug."'>".$term->name."</option>";
						 }
					 
				 }
		
		?></select></p>
        
        
        

  
  
          <p>
            <label for="activeurl">URL:<br>
            </label>
        <select id="activeurl" name="activeurl" onChange="lshowcaseshortcodegenerate()">
          <option value="inactive">Inactive</option>
          <option value="new">Open in new window</option>
          <option value="same">Open in same window</option>
        </select></p>
         
  
  
   <p>
     <label for="style">Style:</label>
        <br>
        <select id="style" name="style" onChange="lshowcaseshortcodegenerate()">
          <?php 
		  $stylesarray = lshowcase_styles_array();
		  
		  foreach($stylesarray as $option => $key) { 
		  ?>
          
          <option value="<?php echo $option; ?>"><?php echo $key['description']; ?></option>
          <?php }?>
		</select></p>
       
        <p>Layout:
          <br>
          <select id="interface" name="interface" onChange="lshowcaseshortcodegenerate()">
          <option value="hcarousel">Horizontal Carousel</option>
          <option value="grid" >Normal Grid</option>
          <option value="grid12" >Responsive Grid - 12 Columns</option> 
          <option value="grid11" >Responsive Grid - 11 Columns</option>
          <option value="grid10" >Responsive Grid - 10 Columns</option>
          <option value="grid9" >Responsive Grid - 9 Columns</option>
          <option value="grid8" >Responsive Grid - 8 Columns</option>
          <option value="grid7" >Responsive Grid - 7 Columns</option> 
          <option value="grid6" >Responsive Grid - 6 Columns</option> 
          <option value="grid5" >Responsive Grid - 5 Columns</option>  
          <option value="grid4" >Responsive Grid - 4 Columns</option>
          <option value="grid3" >Responsive Grid - 3 Columns</option>
          <option value="grid2" >Responsive Grid - 2 Columns</option>
          <option value="grid1" >Responsive Grid - 1 Columns</option>     
          
</select></p>

<p>
     <label for="tooltip">Show Tooltip:</label>
        <br>
        <select id="tooltip" name="tooltip" onChange="lshowcaseshortcodegenerate()">
          
          <option value="false">No</option> <option value="true">Yes</option> 
          
</select></p>

</form>
    </div>
    </td><td valign="top"><h3>Shortcode</h3> 
    Use this shortcode to display the list of logos in your posts or pages! Just copy this piece of text and place it where you want it to display.
    
      <div id="shortcode" style="padding:10px; background-color:#f5f5f5;"></div>
    
    <h3>PHP Function</h3>
    Use this PHP function to display the list of logos directly in your theme files!
    <div id="phpcode" style="padding:10px; background-color:#f5f5f5;"> </div>
    
    <h3> Preview</h3>
      
	<div id="preview"></div>
    <div id="hcarouselhelp" style="margin-top:50px; display:none;">You can also change the way the Carousel/Slider works in the <a href="edit.php?post_type=lshowcase&page=lshowcase_settings">Settings Page</a></div>
    
      </td></tr></table>
    
    
<?php


	$options = get_option('lshowcase-settings');	
	$mode = "'horizontal'";	
	$speed = $options['lshowcase_carousel_speed'];
	$slidemargin = $options['lshowcase_carousel_slideMargin'];
	$loop = $options['lshowcase_carousel_infiniteLoop'];
	$pager = $options['lshowcase_carousel_pager'];
	$controls = $options['lshowcase_carousel_controls'];
	$minslides = $options['lshowcase_carousel_minSlides'];
	$maxslides = $options['lshowcase_carousel_maxSlides'];
	$moveslides = $options['lshowcase_carousel_moveSlides'];
	$slideWidth = $options['lshowcase_thumb_width'];
	$autoscroll = $options['lshowcase_carousel_autoscroll'];
	$pausetime = $options['lshowcase_carousel_pause']; 
	$autohover = $options['lshowcase_carousel_autohover'];
	$autocontrols = $options['lshowcase_carousel_autocontrols'];
	
	
?>
<script type="text/javascript">

	
	var swidth =  <?php echo $slideWidth; ?>+20;
	function checkslider()
	{
	
	var layout = document.getElementById('interface').value;
	
	if(layout=="hcarousel") {
			
		jQuery('.lshowcase-wrap-carousel').bxSlider({
		<?php if($autoscroll=='true') { ?>
		auto: true,
		pause: <?php echo $pausetime; ?>,
		autoHover: <?php echo $autohover; ?>,
		<?php } ?>
		<?php if($autoscroll=='ticker') { ?>
		ticker: true,
		tickerHover: <?php echo $autohover; ?>,
		<?php if($autohover=='true') { ?>
		useCSS: false,
		<?php }} ?>	
		<?php if($autoscroll=='false') { ?>
		auto: false,
		<?php } ?>	
		autoControls: <?php echo $autocontrols; ?>,
		mode: <?php echo $mode; ?>,
		speed: <?php echo $speed; ?>,
		slideMargin:<?php echo $slidemargin; ?>,
		infiniteLoop: <?php echo $loop; ?>,
		pager: <?php echo $pager; ?>,
		controls: <?php echo $controls; ?>,
		slideWidth: swidth,
		minSlides: <?php echo $minslides; ?>,
		maxSlides: <?php echo $maxslides; ?>,
		moveSlides: <?php echo $moveslides; ?>
			});
		}
	}
	
	
	function checktooltip() {
		
	var tooltip = document.getElementById('tooltip').value;
	
	if(tooltip=="true") {
		
			jQuery('.lshowcase-tooltip').tooltip({
			items: "img[alt]",
			position: {
			my: "center bottom-20",
			at: "center top",
			using: function( position, feedback ) {
			jQuery( this ).css( position );
			jQuery( "<div>" )
			.addClass( "lsarrow" )
			.addClass( feedback.vertical )
			.addClass( feedback.horizontal )
			.appendTo( this );
			}
			}
			});
		}

	}
	
	
	
	 </script>
     <?php


 }




//add options page
function lshowcase_admin_page() {
	
	$menu_slug = 'edit.php?post_type=lshowcase';
	$submenu_page_title = 'Settings';
    $submenu_title = 'Settings';
	$capability = 'manage_options';
    $submenu_slug = 'lshowcase_settings';
    $submenu_function = 'lshowcase_settings_page';
    $defaultp = add_submenu_page($menu_slug, $submenu_page_title, $submenu_title, $capability, $submenu_slug, $submenu_function);
	
   }
   
  
//options page build 
function lshowcase_settings_page () { 
 			


?>
    <div class="wrap">
<h2>Settings</h2>
    <?php if(isset($_GET['settings-updated']) && $_GET['settings-updated']=="true") { 
    $msg = "Settings Updated";
    lshowcase_message($msg);
    } ?>
	<form method="post" action="options.php" id="dsform">
    <?php 
	settings_fields( 'lshowcase-plugin-settings' ); 
    $options = get_option('lshowcase-settings'); 
	
	
	
	
	?>
    <table width="70%" border="0" cellspacing="5" cellpadding="5">
  <tr>
    <td colspan="3"><h2>Logo Showcase Names</h2></td>
    </tr>
  <tr>
    <td align="right">Singular Name:</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_name_singular]" value="<?php echo $options['lshowcase_name_singular']; ?>" /></td>
    <td rowspan="2" valign="top"><p class="howto">What do you want to call this feature?</p>
      <p class="howto">For Administration purposes only.</p></td>
  </tr>
  <tr>
    <td align="right">Plural Name:</td>
    <td>    <input type="text" name="lshowcase-settings[lshowcase_name_plural]" value="<?php echo $options['lshowcase_name_plural']; ?>" />
</td>
    </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><h2>Logo Image Size Settings</h2></td>
    </tr>
  <tr>
    <td align="right">Width</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_thumb_width]" value="<?php echo $options['lshowcase_thumb_width']; ?>" /></td>
    <td rowspan="3" valign="top"><span class="howto">This will be the size of the Images. When they are uploaded they will follow this settings. If you change this settings after the image is uploaded they will show scaled.</span></td>
  </tr>
  <tr>
    <td align="right">Height</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_thumb_height]" value="<?php echo $options['lshowcase_thumb_height']; ?>" /></td>
    </tr>
  <tr>
    <td align="right">Crop</td>
    <td><select name="lshowcase-settings[lshowcase_thumb_crop]">
      <option value="true" <?php selected($options['lshowcase_thumb_crop'], 'true' ); ?>>Yes</option>
      <option value="false" <?php selected($options['lshowcase_thumb_crop'], 'false' ); ?>>No</option>
    </select></td>
    </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="3"><h2>Carousel Settings</h2></td>
    </tr>
  <tr>
    <td align="right" nowrap>Auto Scroll</td>
    <td><select name="lshowcase-settings[lshowcase_carousel_autoscroll]">
      <option value="true"  <?php selected($options['lshowcase_carousel_autoscroll'], 'true' ); ?>>Yes - Auto Scroll With Pause</option>
      <option value="ticker"  <?php selected($options['lshowcase_carousel_autoscroll'], 'ticker' ); ?>>Yes - Auto Scroll Non Stop</option>
      <option value="false" <?php selected($options['lshowcase_carousel_autoscroll'], 'false' ); ?>>No</option>
    </select></td>
    <td><span class="howto">Slides will automatically transition</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Pause Time</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_carousel_pause]" value="<?php echo $options['lshowcase_carousel_pause']; ?>" /></td>
    <td><span class="howto">The amount of time (in ms) between each auto transition (if Auto Scroll with Pause is On)</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Pause on Hover</td>
    <td><select name="lshowcase-settings[lshowcase_carousel_autohover]">
      <option value="true" <?php selected($options['lshowcase_carousel_autohover'], 'true' ); ?>>Yes</option>
      <option value="false" <?php selected($options['lshowcase_carousel_autohover'], 'false' ); ?>>No</option>
    </select></td>
    <td><span class="howto">Auto scroll will pause when mouse hovers over slider</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Auto Controls</td>
    <td><select name="lshowcase-settings[lshowcase_carousel_autocontrols]">
      <option value="true" <?php selected($options['lshowcase_carousel_autocontrols'], 'true' ); ?>>Yes</option>
      <option value="false" <?php selected($options['lshowcase_carousel_autocontrols'], 'false' ); ?>>No</option>
    </select></td>
    <td><span class="howto">If active, "Start" / "Stop" controls will be added (Doesn't work for Auto Scroll Non Stop)</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Transition Speed:</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_carousel_speed]" value="<?php echo $options['lshowcase_carousel_speed']; ?>" /></td>
    <td><span class="howto">Slide transition duration (in ms - intenger) </span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Image Margin:</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_carousel_slideMargin]" value="<?php echo $options['lshowcase_carousel_slideMargin']; ?>" /></td>
    <td><span class="howto">Margin between each image (intenger)</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Infinite Loop:</td>
    <td><select name="lshowcase-settings[lshowcase_carousel_infiniteLoop]">
      <option value="true" <?php selected($options['lshowcase_carousel_infiniteLoop'], 'true' ); ?>>Yes</option>
      <option value="false" <?php selected($options['lshowcase_carousel_infiniteLoop'], 'false' ); ?>>No</option>
    </select></td>
    <td><span class="howto">If Active, clicking "Next" while on the last slide will transition to the first slide and vice-versa</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Show Pager:</td>
    <td><select name="lshowcase-settings[lshowcase_carousel_pager]">
      <option value="true" <?php selected($options['lshowcase_carousel_pager'], 'true' ); ?>>Yes</option>
      <option value="false" <?php selected($options['lshowcase_carousel_pager'], 'false' ); ?>>No</option>
    </select></td>
    <td><span class="howto">If Active, a pager will be added. (Doesn't work for Auto Scroll Non Stop)</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Show Controls:</td>
    <td><select name="lshowcase-settings[lshowcase_carousel_controls]">
      <option value="true" <?php selected($options['lshowcase_carousel_controls'], 'true' ); ?>>Yes</option>
      <option value="false" <?php selected($options['lshowcase_carousel_controls'], 'false' ); ?>>No</option>
    </select></td>
    <td><span class="howto">If Active, "Next" / "Prev" image controls will be added. (Doesn't work for Auto Scroll Non Stop)</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Minimum Slides:</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_carousel_minSlides]" value="<?php echo $options['lshowcase_carousel_minSlides']; ?>" /></td>
    <td><span class="howto">The minimum number of slides to be shown. Slides will be sized down if carousel becomes smaller than the original size.</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Maximum Slides:</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_carousel_maxSlides]" value="<?php echo $options['lshowcase_carousel_maxSlides']; ?>" /></td>
    <td><span class="howto">The maximum number of slides to be shown. Slides will be sized up if carousel becomes larger than the original size.</span></td>
  </tr>
  <tr>
    <td align="right" nowrap>Number of Slides to move:</td>
    <td><input type="text" name="lshowcase-settings[lshowcase_carousel_moveSlides]" value="<?php echo $options['lshowcase_carousel_moveSlides']; ?>" /></td>
    <td><span class="howto">The number of slides to move on transition.  If zero, the number of fully-visible slides will be used.</span></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  
 </table>

    
    
	<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</form>
<?php }

// register settings
function register_lshowcase_settings() {
	register_setting( 'lshowcase-plugin-settings', 'lshowcase-settings');
}

//register default values
register_activation_hook(__FILE__, 'lshowcase_defaults');
function lshowcase_defaults() {
	$tmp = get_option('lshowcase-settings');
	
	//check for settings version
    if((!is_array($tmp)) || !isset($tmp['lshowcase_carousel_autoscroll'])) {
		delete_option('lshowcase-settings'); 
		$arr = array(	"lshowcase_name_singular" => "Logo",
						"lshowcase_name_plural" => "Logos",
						"lshowcase_thumb_width" => "200",
						"lshowcase_thumb_height" => "200",
						"lshowcase_thumb_crop" => "false",
						"lshowcase_carousel_autoscroll" => "false",
						"lshowcase_carousel_pause" => "4000",
						"lshowcase_carousel_autohover" => "false",
						"lshowcase_carousel_autocontrols" => "false",
						"lshowcase_carousel_speed" => "500",
						"lshowcase_carousel_slideMargin" => "10",
						"lshowcase_carousel_infiniteLoop" => "true",
						"lshowcase_carousel_pager" => "false",
						"lshowcase_carousel_controls" => "true",
						"lshowcase_carousel_minSlides" => "1",
						"lshowcase_carousel_maxSlides" => "8",
						"lshowcase_carousel_moveSlides" => "1",
						"lshowcase_capability_type_settings" => "manage_options",
						"lshowcase_capability_type_manage" => "manage_options",
						"lshowcase_empty" => "2",
						
							
		);
		
		update_option('lshowcase-settings', $arr);
	}
}

//To Show styled messages
function lshowcase_message($msg) { ?>
  <div id="message" class="updated"><p><?php echo $msg; ?></p></div>
<?php	
}


//Add new column 
function lshowcase_columns_head($defaults) {
	global $post;
    if ($post->post_type == 'lshowcase') {
	$defaults['featured_image'] = 'Image';
	}
	return $defaults;
}

// SHOW THE FEATURED IMAGE in admin
function lshowcase_columns_content($column_name, $post_ID) {
	
	global $post;
    if ($post->post_type == 'lshowcase') {
		if ($column_name == 'featured_image') {		
			echo get_the_post_thumbnail($post_ID, 'lshowcase-thumb');		
		}
	}
}


//Shortcode

//Add shortcode functionality
add_shortcode('show-logos', 'shortcode_lshowcase');
add_filter('widget_text', 'do_shortcode');
add_filter( 'the_excerpt', 'do_shortcode');




function shortcode_lshowcase( $atts ) {	


	$orderby = (array_key_exists('orderby', $atts) ? $atts['orderby'] : "none");
	$category = (array_key_exists('category', $atts) ? $atts['category'] : "0");
	$style = (array_key_exists('style', $atts) ? $atts['style'] : "normal");
	$interface = (array_key_exists('interface', $atts) ? $atts['interface'] : "grid");
    $activeurl =  (array_key_exists('activeurl', $atts) ? $atts['activeurl'] : "inactive");
	$tooltip = (array_key_exists('tooltip', $atts) ? $atts['tooltip'] : "false");
	$limit = (array_key_exists('limit', $atts) ? $atts['limit'] : 0);
		
	$html = build_lshowcase($orderby,$category,$activeurl,$style,$interface,$tooltip,$limit);
	return $html;	
	
}


/**
 * Widget
 */
class Lshowcase_Widget extends WP_Widget {
	
	
	
	
    public function __construct() {
		
		$options = get_option('lshowcase-settings');	
	$name = $options['lshowcase_name_singular'];
	$nameplural = $options['lshowcase_name_plural'];
		
        $widget_ops = array( 'classname' => 'lshowcase_widget', 'description' => 'Display '.$name.' images on your website' );
        parent::__construct( 'lshowcase_widget', $nameplural, $widget_ops );
    }
 
    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
        $orderby = strip_tags( $instance['orderby'] );
		$category = $instance['category'];
		$style = strip_tags( $instance['style'] );
		$interface = strip_tags( $instance['interface'] );
        $activeurl =  $instance['activeurl'];
		$tooltip = $instance['tooltip'];
		$limit = $instance['limit'];
		
 
        echo $before_widget;
 
        if ( ! empty( $title ) )
            echo $before_title . $title . $after_title;
			
		echo build_lshowcase($orderby,$category,$activeurl,$style,$interface,$tooltip,$limit);
      	
 
        echo $after_widget;
    }
 
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['orderby'] = strip_tags( $new_instance['orderby'] );
        $instance['category'] = $new_instance['category'];
		$instance['style'] = strip_tags( $new_instance['style'] );
		$instance['interface'] = strip_tags( $new_instance['interface'] );
 		$instance['activeurl'] = $new_instance['activeurl'];
		$instance['tooltip'] = $new_instance['tooltip'];
		$instance['limit'] = $new_instance['limit'];


        return $instance;
    }
 
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'orderby' => 'none', 'category' => '0', 'style'=>'normal', 'interface'=>'grid','activeurl' => '1' ) );
        $title = strip_tags( $instance['title'] );
        $orderby = strip_tags( $instance['orderby'] );
		$category =  $instance['category'];
		$style = strip_tags( $instance['style'] );
		$interface = strip_tags( $instance['interface'] );
		$activeurl = $instance['activeurl'];
		$tooltip = $instance['tooltip'];
		$limit = $instance['limit'];
		      
        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
 
        
<p>
        <label for="<?php echo $this->get_field_id( 'orderby' ); ?>">Order By:<br>
        </label>
        <select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
            <option value="none" <?php selected( $orderby, 'none' ); ?>>None</option>
            <option value="name" <?php selected( $orderby, 'name' ); ?>>Title</option>
            <option value="ID" <?php selected( $orderby, 'ID' ); ?>>ID</option>
            <option value="date" <?php selected( $orderby, 'date' ); ?>>Date</option>
            <option value="modified" <?php selected( $orderby, 'modified' ); ?>>Modified</option>
            <option value="rand" <?php selected( $orderby, 'rand' ); ?>>Random</option>
        </select></p>
        
              <p><label for="<?php echo $this->get_field_id( 'limit' ); ?>">Number of Images to display:</label><br>

        <input size="3" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" /><span class="howto"> (Leave blank or 0 to display all)</span></p>
 	
    
<p><label for="<?php echo $this->get_field_id( 'category' ); ?>">Category</label>
     :
       <br>
        <select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
          <option value="0" <?php selected( $category, '0' ); ?>>All</option>
        
  <?php 
		
				 $terms = get_terms("lshowcase-categories");
				 $count = count($terms);
				 if ( $count > 0 ){
					 
					 foreach ( $terms as $term ) {
					    echo "<option value='".$term->slug."'".selected( $category, $term->slug ).">".$term->name."</option>";
						 }
					 
				 }
		
		?></select></p>
        
        
        

  
  
          <p>
            <label for="<?php echo $this->get_field_id( 'activeurl' ); ?>">URL:<br>
            </label>
        <select id="<?php echo $this->get_field_id( 'activeurl' ); ?>" name="<?php echo $this->get_field_name( 'activeurl' ); ?>">
          <option value="inactive" <?php selected( $activeurl, 'inactive' ); ?>>Inactive</option>
          <option value="new" <?php selected( $activeurl, 'new' ); ?>>Open in new window</option>
          <option value="same" <?php selected( $activeurl, 'same' ); ?>>Open in same window</option>
        </select></p>
         
  
  
   <p>
     <label for="<?php echo $this->get_field_id( 'style' ); ?>">Style:</label>
        <br>
        <select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
          
          <?php 
		  $stylesarray = lshowcase_styles_array();
		  
		  foreach($stylesarray as $option => $key) { 
		  ?>
          
          <option value="<?php echo $option; ?>" <?php selected($style, $option); ?>><?php echo $key['description']; ?></option>
          <?php }?>
          
</select></p>
       
        <p>Layout:
          <br>
          <select id="<?php echo $this->get_field_id( 'interface' ); ?>" name="<?php echo $this->get_field_name( 'interface' ); ?>">
          <option value="hcarousel" <?php selected( $interface, 'hcarousel' ); ?>>Horizontal Carousel</option>
          <option value="grid" <?php selected( $interface, 'grid' ); ?>>Normal Grid</option>
          <option value="grid12" <?php selected( $interface, 'grid12' ); ?>>Responsive Grid - 12 Columns</option> 
          <option value="grid8" <?php selected( $interface, 'grid11' ); ?>>Responsive Grid - 11 Columns</option>
          <option value="grid8" <?php selected( $interface, 'grid10' ); ?>>Responsive Grid - 10 Columns</option>
          <option value="grid8" <?php selected( $interface, 'grid9' ); ?>>Responsive Grid - 9 Columns</option>
          <option value="grid8" <?php selected( $interface, 'grid8' ); ?>>Responsive Grid - 8 Columns</option> 
          <option value="grid8" <?php selected( $interface, 'grid7' ); ?>>Responsive Grid - 7 Columns</option>
          <option value="grid6" <?php selected( $interface, 'grid6' ); ?>>Responsive Grid - 6 Columns</option> 
          <option value="grid5" <?php selected( $interface, 'grid5' ); ?>>Responsive Grid - 5 Columns</option>  
          <option value="grid4" <?php selected( $interface, 'grid4' ); ?>>Responsive Grid - 4 Columns</option>
          <option value="grid3" <?php selected( $interface, 'grid3' ); ?>>Responsive Grid - 3 Columns</option>
          <option value="grid2" <?php selected( $interface, 'grid2' ); ?>>Responsive Grid - 2 Columns</option>
          <option value="grid1" <?php selected( $interface, 'grid1' ); ?>>Responsive Grid - 1 Columns</option>     
          
</select></p>
       
       <p>
     <label for="<?php echo $this->get_field_id( 'tooltip' ); ?>">Show Tooltip:</label>
        <br>
        <select id="<?php echo $this->get_field_id( 'tooltip' ); ?>" name="<?php echo $this->get_field_name( 'tooltip' ); ?>">
          <option value="true" <?php selected( $tooltip, 'true' ); ?>>Yes</option>
          <option value="false" <?php selected( $tooltip, 'false' ); ?>>No</option>  
          
</select></p>
       
        <?php
    }
}
 
add_action( 'widgets_init', 'register_lshowcase_widget' );
/**
 * Register widget
 *
 * This functions is attached to the 'widgets_init' action hook.
 */
function register_lshowcase_widget() {
    register_widget( 'Lshowcase_Widget' );
}

/*
 *
 * /////////////////////////////
 * FUNCTION TO DISPLAY THE LOGOS
 * /////////////////////////////
 *
 */
 
 
function build_lshowcase($order="none",$category="",$activeurl="new",$style="normal",$interface="grid",$tooltip="false",$limit=-1) {
	
	global $lshowcase_slider_on;
	global $post;
	
	$html ="";
	$thumbsize ="lshowcase-thumb";
	$class ="lshowcase-thumb";
	$divwrap = "lshowcase-wrap-normal";
	$divwrapextra = "";	
	$divboxclass = "lshowcase-box-normal";
	$divboxinnerclass = "lshowcase-boxInner-normal";
	
	
	if($interface!="grid" && $interface!="hcarousel" && $interface!="vcarousel" ) {
		$columncount = substr($interface,4);	
		$divboxclass = "lshowcase-box-".$columncount;
		$divboxinnerclass = "lshowcase-boxInner";
		$divwrap = "lshowcase-wrap-responsive";
	}
	
	if($interface=="hcarousel") {
		
		$lshowcase_slider_on = true;
		
		$divwrapextra = "class='lshowcase-wrap-carousel'";
		$class ="lshowcase-thumb";
		$divwrap = "lshowcase-wrap-normal";
		$divboxclass = "lshowcase-box-normal";
		$divboxinnerclass = "lshowcase-slide";
		lshowcase_add_carousel_js();
		
			
	}
	
	$stylearray = lshowcase_styles_array();
	$class = $stylearray[$style]["class"];
	
	
	if($tooltip == 'true') {
		
		$class .= " lshowcase-tooltip";
		lshowcase_add_tooltip_js();
	}
	
	$postsperpage = -1;
	$nopaging=true;
	if($limit >= 1) { 
	$postsperpage = $limit;
	$nopaging = false;
	}
	$ascdesc = 'DESC';
	if($order=='name') {
		$ascdesc = 'ASC';
		};
		
	if($order=='none') {	
	$args = array( 'post_type' => 'lshowcase', 'lshowcase-categories' => $category, 'posts_per_page'=> $postsperpage, 'nopaging'=> $nopaging);
	}
	else {
	$args = array( 'post_type' => 'lshowcase', 'lshowcase-categories' => $category, 'orderby' => $order, 'order' => $ascdesc, 'posts_per_page'=> $postsperpage, 'nopaging'=> $nopaging);
	}

	$loop = new WP_Query( $args );
	
	//to force random again - uncomment in case random is not working
	//if($order=='rand') {
	//shuffle( $loop->posts );
	//}
	
	$html .= '<div class="lshowcase-clear-both">&nbsp;</div>';
		
	$html .= '<div class="lshowcase-logos"><div '.$divwrapextra.' >';

	
	while ( $loop->have_posts() ) : $loop->the_post(); 

    
    if ( has_post_thumbnail()) : 
    
	$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), $thumbsize ); 
	
	$lshowcase_options = get_option('lshowcase-settings');
   	$width = $image[1];	
	$dwidth = $lshowcase_options['lshowcase_thumb_width'];
	
	if($interface!="hcarousel") {	
		
	$html .= "<div class='".$divwrap."'>";
	$html .= '<div class="'.$divboxclass.'">';
	}
	
	if($interface=="grid") {	
	$html .= '<div class="'.$divboxinnerclass.'" style="width:'.$dwidth.'px; align:center; text-align:center;">';
	} else {
	$html .= '<div class="'.$divboxinnerclass.'">';	
		}
	
	
	
	$url = get_post_meta(get_the_ID(), 'urllink', true);
	$alt = get_post_meta(get_the_ID(), 'urldesc', true);
	$title = the_title_attribute( 'echo=0' );
	
		
		
	if($activeurl!="inactive" && $url != "") { 
	$target = "";
	if($activeurl=="new") { $target="target='_blank'";}			
	$html .= "<a href='".$url."' alt='".$alt."' ".$target.">";
	$html .= "<img src='".$image[0]."' width='".$width."' alt='".$alt."' title='".$title."' class='lsshowcase_img ".$class."' />";
	//$html .= get_the_post_thumbnail($post->ID,$thumbsize,array('class' => $class, 'alt'	=> $alt, 'title' => $title)); 
	$html .= "</a>";
	}
	else {
	$html .= "<img src='".$image[0]."' width='".$width."' alt='".$alt."' title='".$title."' class='lsshowcase_img ".$class."' />";
	//$html .= get_the_post_thumbnail($post->ID,$thumbsize,array('class' => $class, 'alt'	=> $alt, 'title' => $title));	
	}
	
	if($interface!="hcarousel") {
	$html .= "</div></div>";	
	}
	$html .= "</div>";
	
   
    endif;
	
	endwhile; 
	// Restore original Post Data 
 	wp_reset_postdata();
	
	$html .= '&nbsp;</div></div><div class="lshowcase-clear-both">&nbsp;</div>';
	
	lshowcase_add_main_css();		
	return $html;
	
}


/* CSS enqueue functions */ 


	function lshowcase_add_main_css() {
       		wp_deregister_style( 'lshowcase-main-style' );
		    wp_register_style( 'lshowcase-main-style', plugins_url( '/styles.css', __FILE__ ),array(),false,false);
			wp_enqueue_style( 'lshowcase-main-style' );
						

    }
	
/*   JS for Slider */

function lshowcase_add_carousel_js() {
			//wp_enqueue_script( 'jquery' );	
			
			wp_deregister_script( 'lshowcase-bxslider' );
		    wp_register_script( 'lshowcase-bxslider', plugins_url( '/bxslider/jquery.bxslider.js', __FILE__ ),array('jquery'),false,false);
			wp_enqueue_script( 'lshowcase-bxslider' );
			
			wp_deregister_style( 'lshowcase-bxslider-style' );
		    wp_register_style( 'lshowcase-bxslider-style', plugins_url( '/bxslider/jquery.bxslider.css', __FILE__ ),array(),false,false);
			wp_enqueue_style( 'lshowcase-bxslider-style' );
			
			global $lshowcase_carousel_load_footer;
			if($lshowcase_carousel_load_footer) {
			add_action( 'wp_print_footer_scripts', 'lshowcase_bxslider_options_js' );
			}
			
			
}


/* Tooltip Scripts */

function lshowcase_add_tooltip_js() {
		
			
			
			wp_deregister_script( 'ls-jquery-ui' );
		    wp_register_script( 'ls-jquery-ui', plugins_url( '/js/jquery-ui-1.10.2.custom.min.js', __FILE__ ),array( 'jquery' ),false,false);
			wp_enqueue_script( 'ls-jquery-ui' );
			
			wp_deregister_script( 'lshowcase-tooltip' );
		    wp_register_script( 'lshowcase-tooltip', plugins_url( '/js/tooltip.js', __FILE__ ),array('ls-jquery-ui'),false,false);
			wp_enqueue_script( 'lshowcase-tooltip' );
			
			
			
}

/* Styles Function */
/* YOU CAN ADD NEW STYLES. 
   ADD ITEMS TO ARRAY
   */ 

function lshowcase_styles_array() {
	
	$styles = array(
	"normal" => array (
							"class" => "lshowcase-normal",
							"description" => "Normal - No Styles",
							),
	"boxhighlight" => array (
							"class" => "lshowcase-boxhighlight",
							"description" => "Box Highlight on hover",
							),
	"grayscale" => array (
							"class" => "lshowcase-grayscale",
							"description" => "Always Grayscale",
							),
	"hgrayscale" => array (
							"class" => "lshowcase-hover-grayscale",
							"description" => "Grayscale and Color on hover",
							)	
	);
	
	return $styles;
	
}


function lshowcase_bxslider_options_js() { 
	
	global $lshowcase_jquery_noconflict;
	
	$options = get_option('lshowcase-settings');	
	$mode = "'horizontal'";	
	$speed = $options['lshowcase_carousel_speed'];
	$slidemargin = $options['lshowcase_carousel_slideMargin'];
	$loop = $options['lshowcase_carousel_infiniteLoop'];
	$pager = $options['lshowcase_carousel_pager'];
	$controls = $options['lshowcase_carousel_controls'];
	$minslides = $options['lshowcase_carousel_minSlides'];
	$maxslides = $options['lshowcase_carousel_maxSlides'];
	$moveslides = $options['lshowcase_carousel_moveSlides'];
	$slideWidth = $options['lshowcase_thumb_width'];
	$autoscroll = $options['lshowcase_carousel_autoscroll'];
	$pausetime = $options['lshowcase_carousel_pause']; 
	$autohover = $options['lshowcase_carousel_autohover'];
	$autocontrols = $options['lshowcase_carousel_autocontrols'];
	
	
?>
<script type="text/javascript">
	
	<?php if($lshowcase_jquery_noconflict) { ?>
	jQuery.noConflict();
	var swidth =  <?php echo $slideWidth; ?>+20;
	jQuery(document).ready(function(){
    jQuery('.lshowcase-wrap-carousel').bxSlider({		
    <?php 
	} 
	else { ?>
	
	var swidth =  <?php echo $slideWidth; ?>+20;
	$(document).ready(function(){
    $('.lshowcase-wrap-carousel').bxSlider({	
	<?php }
	
	if($autoscroll=='true') { ?>
	auto: true,
	pause: <?php echo $pausetime; ?>,
	autoHover: <?php echo $autohover; ?>,
	<?php } ?>
	<?php if($autoscroll=='ticker') { ?>
	ticker: true,
	tickerHover: <?php echo $autohover; ?>,
	<?php if($autohover=='true') { ?>
	useCSS: false,
	<?php }} ?>	
	<?php if($autoscroll=='false') { ?>
	auto: false,
	<?php } ?>	
	autoControls: <?php echo $autocontrols; ?>,
	mode: <?php echo $mode; ?>,
	speed: <?php echo $speed; ?>,
	slideMargin:<?php echo $slidemargin; ?>,
	infiniteLoop: <?php echo $loop; ?>,
    pager: <?php echo $pager; ?>,
	controls: <?php echo $controls; ?>,
    slideWidth: swidth,
    minSlides: <?php echo $minslides; ?>,
    maxSlides: <?php echo $maxslides; ?>,
    moveSlides: <?php echo $moveslides; ?>
  		});
	});
	 </script>
<?php 
	

}

	

			
			if(!$lshowcase_carousel_load_footer) {
			add_action( 'wp_head', 'lshowcase_bxslider_options_js' );
			}
			
	
 
?>
