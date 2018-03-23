<?php
/*
Plugin Name: Top Quark Architecture
Plugin URI: http://topquark.com
Description: Though ostensibly this can be used as yet another gallery plugin, under the hood it provides a framework for rapid plugin development.   
Version: 2.1.3
Author: Top Quark
Author URI: http://topquark.com
*/
if (!function_exists('session_register')){
	function session_register($variable){
		$_SESSION[$variable] = null;
	}
}

include_once('lib/Standard.php');

add_action('init','topquark_init',1);
function topquark_init(){
	wp_register_script('topquark_mootools',WP_CONTENT_URL.'/plugins/topquark/lib/js/mootools-1.2.1-core.js');
	wp_register_script('topquark_mootools_more',WP_CONTENT_URL.'/plugins/topquark/lib/js/mootools-1.2-more.js','topquark_mootools');
}

add_action('admin_init','topquark_admin_init');
function topquark_admin_init(){
	// Let's let any WordPress administrator also be an administrator of Top Quark
	if (current_user_can('activate_plugins') and !current_user_can('access_topquark')){
		topquark_activate();
	}
}

add_filter('the_posts', 'topquark_conditionally_add_mootools'); // the_posts gets triggered before wp_head
function topquark_conditionally_add_mootools($posts){
	if (empty($posts)) return $posts;
 
	$shortcode_found = false; // use this flag to see if styles and scripts need to be enqueued
	foreach ($posts as $post) {
		if (preg_match('/\[topquark[^\]]*Gallery/',$post->post_content)) {
			$shortcode_found = true; // bingo!
			break;
		}
	}
 
	if ($shortcode_found) {
		// enqueue here
		wp_enqueue_script('topquark_mootools');
		wp_enqueue_script('topquark_mootools_more');
	}
 
	return $posts;
}

add_action('admin_menu','topquark_admin_menu');
function topquark_admin_menu(){
    add_menu_page('', 'Top Quark', 'access_topquark', 'topquark', 'topquark_admin_page',WP_CONTENT_URL.'/plugins/topquark/admin/themes/wordpress/css/images/top-quark-logo-shadow-16x16.png');    
	$Bootstrap = Bootstrap::getBootstrap();
	$Packages = $Bootstrap->getAllPackages();
	foreach ($Packages as $p){
		if ($p->is_active and isset($p->main_menu_page)){
			$slug = 'admin.php?page=topquark&noheader=true&package='.$p->package_name.'&toppage='.$p->main_menu_page;
			if (current_user_can('administrate_topquark')){
    			add_submenu_page('topquark', $p->package_title, $p->package_title, 'administrate_topquark', $slug);    
			}
			else{
    			add_submenu_page('topquark', $p->package_title, $p->package_title, 'access_topquark_'.$p->package_name, $slug);    
			}
		}
	}
}

register_activation_hook(__FILE__,'topquark_activate');
function topquark_activate(){
	// Whoever is lucky enough to activate TopQuark the first time becomes the administrative user
	// They will have the ability to make other users "superusers"
	$user = wp_get_current_user();
	$user->add_cap('access_topquark');
	$user->add_cap('administrate_topquark');
}

// Make it so .tpl files are editable
add_filter('editable_extensions','topquark_editable_extensions');
function topquark_editable_extensions($e){
	$e[] = 'tpl';
	return $e;
}

function topquark_the_content($content){
	return $content;
}

function topquark_admin_page($include_wp_header = true){
    $Bootstrap = Bootstrap::getBootstrap();
    $MessageList = Bootstrap::getMessageList();
    $AuthorizationNotNecessary = true;
    include_once("admin/AdminStandard.php");

    if (isset($_GET['package']) and $_GET['package'] != ""){
        $Package = $Bootstrap->usePackage($_GET['package']);
    }
    
    ob_start();
	if ($include_wp_header !== false){
		require_once(ABSPATH . 'wp-admin/admin-header.php');
	}
	
	$user = wp_get_current_user();
    if (!isset($Package) or !$Package or !isset($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]) or !UserPackageContainer::user_can_access($user,$Package->package_name)){
	    // display main menu
		$smarty->assign('title',SITE_NAME.' :: Admin Main Menu');
		$_SESSION['auth_name'] = $user->user_login;
		if ($user->wp_capabilities['administrator']){
			$_SESSION['auth_level'] = USER_AUTH_ADMIN;
		}
		else{
			$_SESSION['auth_level'] = USER_AUTH_EVERYONE;
		}
	    $MenuItems = $Bootstrap->getAllPackages();
		foreach ($MenuItems as $key => $item){
			if (!$item->is_active or !is_array($item->admin_pages) or !isset($item->main_menu_page)){
				unset($MenuItems[$key]);
			}
			if (!UserPackageContainer::user_can_access($user,$item->package_name)){
				unset($MenuItems[$key]);
			}
		}
		$smarty->assign('menu_items',$MenuItems);
		$smarty->assign('admin_page_parm',ADMIN_PAGE_PARM);
		$smarty->assign('display','MAIN_MENU');
		
		$smarty->display('admin_index.tpl');	
    }
    else{
        if (!isset($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url'])){
            echo "Error: you must define a 'url' as part of the admin_pages['".$_GET[ADMIN_PAGE_PARM]."'] array";
            exit();
        }
        
        if (isset($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]) and isset($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url_is_complete']) and $Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url_is_complete']){
            if (strpos($Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url'],'?') !== false){
                $sep = '&';
            }
            else{
                $sep = '?';
            }
            
            $additional_get_vars = "";
            foreach ($_GET as $k => $v){
                if ($k != 'package' and $k != ADMIN_PAGE_PARM){
                    $additional_get_vars.= $sep."$k=$v";
                    $sep = "&";
                }
            }
            
            // don't use this wrapper, just redirect to the actual URL
            header("Location:".$Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url'].$additional_get_vars);
            exit();
        }
        else{
		    $smarty->assign('title',SITE_NAME." :: ".$Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['title']);
        
            // This bit of code puts the results from the include in to a string variable
            ob_start();
            include(PACKAGE_DIRECTORY.($Package->package_directory != "" ? $Package->package_directory : $Package->package_name)."/".$Package->admin_pages[$_GET[ADMIN_PAGE_PARM]]['url']);
            $IncludeContent = ob_get_contents();
            ob_end_clean();
        
			if(!isset($tqpDisplayHead) or $tqpDisplayHead !== false){
	            $smarty->display('admin_head.tpl');
			}
            echo $IncludeContent;
			if(!isset($tqpDisplayHead) or $tqpDisplayHead !== false){
            	//$smarty->display('admin_foot.tpl');		
			}
        }
    }
    echo ob_get_clean();
}

add_shortcode('topquark','topquark_handler');
function topquark_handler($atts, $content=null, $code=""){
	$Bootstrap = Bootstrap::getBootstrap();
	switch($code){
	case 'topquark':
		$Bootstrap->stop_timer('Overall');
		$Bootstrap->snapshot_memory('Stop Overall');
		break;
	default:
		$atts['package'] = ucwords($code);
		$atts['subject'] = $code;
		$atts['action'] = 'paint';
		break;
	}
	
	$Bootstrap = Bootstrap::getBootstrap();
	include_once('lib/Smarty_Instance.class.php');
	if (isset($atts['package']) and $Bootstrap->packageExists($atts['package'])){
		$Package = $Bootstrap->usePackage($atts['package']);
		$smarty = new Smarty_Instance();
		$Bootstrap->snapshot_memory('Instantiated Smarty');
		$parms = array();
		foreach ($atts as $key => $value){
			if (!in_array($key,array('package','action'))){
				$parms[$key] = $value;
			}
		}
		if ($content != ""){
			$parms['content'] = $content;
		}
		switch ($atts['action']){
		case 'paint':
			$Bootstrap->snapshot_memory('Calling Paint');
			return $Package->Paint($parms,$smarty);
			break;
		case 'retrieve':	
			return $Package->Retrieve($parms,$smarty);
			break;
		case 'ajax':
			return $Package->Ajax($parms,$smarty);
			break;
		default:
			
		}
	}
	elseif($atts['subject'] == 'dump_timers'){
		return $Bootstrap->Paint($atts,new Smarty_Instance());
	}
	else{
		return $content;
	}
}

add_shortcode('capture','capture');
function capture($atts,$content="",$code=null){
	extract(shortcode_atts(array('var' => 'var'),$atts));
	
	global $captured_vars;
	if (!isset($captured_vars)){
		$captured_vars = array();
	}
	$captured_vars[$var] = str_replace("\n","\n\n",html_entity_decode($content));
	return '';
}

function get_captured($var){
	global $captured_vars;
	return $captured_vars[$var];
}



/*****************************************************
* Get the Top Quark Gallery showing up as a Media tab
*****************************************************/
function topquark_wp_upload_tabs ($tabs) {
	$newtab = array('topquark_gallery' => __('Top Quark Gallery','topquark_gallery'));
 
    return array_merge($tabs,$newtab);
}
	
add_filter('media_upload_tabs', 'topquark_wp_upload_tabs');

function media_upload_topquark_gallery(){
	$Bootstrap = Bootstrap::getBootstrap();
	$Bootstrap->usePackage('Gallery');
	
	if ( isset($_POST['send']) ) {
		$keys = array_keys($_POST['send']);
		$send_id = (int) array_shift($keys);
		$image = $_POST['image'][$send_id];
		$alttext = stripslashes( htmlspecialchars ($image['alttext'], ENT_QUOTES));
		$caption = stripslashes (htmlspecialchars($image['caption'], ENT_QUOTES));
		
		// here is no new line allowed
		$clean_caption = preg_replace("/\n|\r\n|\r$/", " ", $caption);
		$GalleryImageContainer = new GalleryImageContainer();
		$Image = $GalleryImageContainer->getGalleryImage($send_id);
		$class = $image['align'];
		
		// Build output
		$html = "<img src='".$Image->getGalleryDirectory().$Image->getParameter('GalleryImage'.$image['size'])."' alt='$alttext' class='$class' />";
		
		// Wrap the link to the fullsize image around	
		if ($image['size'] != 'Original'){
			$html = "<a href='".$Image->getGalleryDirectory().$Image->getParameter('GalleryImageOriginal')."' title='$clean_caption'>$html</a>";
		}

		// Return it to TinyMCE
		return media_send_to_editor($html);
	}
	
	return wp_iframe('media_upload_topquark_gallery_frame',$errors);
}

function media_upload_topquark_gallery_frame($errors){

	media_upload_header();

	$GalleryContainer = new GalleryContainer();
	$ImageSetContainer = new ImageSetContainer();
	$AllGalleries = $GalleryContainer->getAllGalleries(array('GalleryStatus','GalleryIndex','GalleryCreationDate'), array('asc','asc','asc'), $types = array());
	$AllImageSets = $ImageSetContainer->getAllImageSets(array('ImageSetStatus','ImageSetIndex','ImageSetCreationDate'), array('asc','asc','asc'), $types = array());
	
	if ($_GET['select_gal'] != ""){
		list($type,$galleryID) = array(substr($_GET['select_gal'],0,1),substr($_GET['select_gal'],1));
		if ($type == 'g'){
			$ChosenGallery = $AllGalleries[$galleryID];
		}
		else{
			$ChosenGallery = $AllImageSets[$galleryID];
		}
	}
	if(is_a($ChosenGallery,'Gallery') or is_a($ChosenGallery,'ImageSet')) {
		$Images = $ChosenGallery->getAllGalleryImages();
	}
	else{
		$Images = array();
	}
	
	// Build navigation
	$per_page = 25;
	$_GET['paged'] = intval($_GET['paged']);
	if ( $_GET['paged'] < 1 )
		$_GET['paged'] = 1;
	$start = ( $_GET['paged'] - 1 ) * $per_page;
	if ( $start < 1 )
		$start = 0;
		
	$total = count($Images);
	$Images = array_slice($Images,$start,$per_page);

	?>
	
	<script type="text/javascript">
	<!--
	jQuery(function($){
		var preloaded = $(".media-item.preloaded");
		if ( preloaded.length > 0 ) {
			preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
			updateMediaForm();
		}
	});
	-->
	</script>
	<form id="filter" action="" method="get">
	<input type="hidden" name="type" value="<?php echo esc_attr( $GLOBALS['type'] ); ?>" />
	<input type="hidden" name="tab" value="<?php echo esc_attr( $GLOBALS['tab'] ); ?>" />
	<input type="hidden" name="post_id" value="<?php echo (int) $post_id; ?>" />

	<div class="tablenav">
		<?php
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'total' => ceil($total / $per_page),
			'current' => $_GET['paged']
		));
	
		if ( $page_links )
			echo "<div class='tablenav-pages'>$page_links</div>";
		?>
		<div class="alignleft actions">
			<select id="select_gal" name="select_gal" style="width:120px;" onchange="this.form.submit();">;
				<option value="0" ><?php esc_attr( _e('No gallery',"nggallery") ); ?></option>
				<?php
				// Show gallery selection
				$whats = array('Galleries' => 'Gallery','ImageSets' => 'ImageSet');
				foreach ($whats as $what => $parm){
					$All = 'All'.$what;
					if (count($$All)){
						$current_status = "";
						$g = strtolower(substr($what,0,1));
						foreach ($$All as $Gallery){
							if ($Gallery->getParameter($parm.'Status') != $current_status){
								echo "<option value=''></option>\n";
								echo "<option value=''>".strtoupper($Gallery->getParameter($parm.'Status')." $what")."</option>\n";
								//echo "<option value=''>------------------</option>\n";
								$current_status = $Gallery->getParameter($parm.'Status');
							}
							$selected = ($Gallery->getParameter($parm.'ID') == $galleryID and $g == $type )?	' selected="selected"' : "";
							echo "<option value='$g".$Gallery->getParameter($parm.'ID')."' $selected>".$Gallery->getParameter($parm.'Name')."</option>\n";
						}
					}
				}
				?>
			</select>
			<input type="submit" id="show-gallery" value="<?php esc_attr( _e('Select &#187;','topquark') ); ?>" class="button-secondary" />
		</div>
		<br style="clear:both;" />
	</div>
	</form>

	<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="media-upload-form" id="library-form">

		<div id="media-items">
		<?php
		if (is_array($Images) and count($Images)){
			foreach ($Images as $Image) {
				?>
				<div id='media-item-<?php echo $Image->getParameter('ImageID') ?>' class='media-item preloaded'>
				  <div class='filename'></div>
				  <a class='toggle describe-toggle-on' href='#'><?php esc_attr( _e('Show', "topquark") ); ?></a>
				  <a class='toggle describe-toggle-off' href='#'><?php esc_attr( _e('Hide', "topquark") );?></a>
				  <div class='filename new'><?php echo ($Image->getParameter('ImageCaption') != "" ? $Image->getParameter('ImageCaption') : $Image->getParameter('GalleryImageOriginal')); ?></div>
				  <table class='slidetoggle describe startclosed'><tbody>
					  <tr>
						<td rowspan='4'><img class='thumbnail' alt='' src='<?php echo esc_attr($Image->getGalleryDirectory().$Image->getParameter("GalleryImageThumb")); ?>'/></td>
						<td><?php esc_attr( _e('Image ID:', "topquark") ); ?><?php echo $Image->getParameter('ImageID') ?></td>
					  </tr>
					  <tr><td><?php echo esc_attr( $Image->getParameter('GalleryImageOriginal') ); ?></td></tr>
					  <tr><td><?php echo esc_attr( $Image->getParameter('ImageCredit') ); ?></td></tr>
					  <tr><td>&nbsp;</td></tr>
					  <tr>
						<td class="label"><label for="image[<?php echo $Image->getParameter('ImageID'); ?>][alttext]"><?php esc_attr( _e('Alt/Title text', "topquark") );?></label></td>
						<td class="field"><input id="image[<?php echo $Image->getParameter('ImageID'); ?>][alttext]" name="image[<?php echo $Image->getParameter('ImageID'); ?>][alttext]" value="<?php echo esc_attr( $Image->getParameter('ImageCaption') ); ?>" type="text"/></td>
					  </tr>	
					  <tr>
						<td class="label"><label for="image[<?php echo $Image->getParameter('ImageID'); ?>][caption]"><?php esc_attr( _e("Caption","topquark") ); ?></label></td>
							<td class="field"><textarea name="image[<?php echo $Image->getParameter('ImageID'); ?>][caption]" id="image[<?php echo $Image->getParameter('ImageID'); ?>][caption]"><?php echo esc_attr( $Image->getParameter('ImageCaption') ); ?></textarea></td>
					  </tr>
						<tr class="align">
							<td class="label"><label for="image[<?php echo $Image->getParameter('ImageID'); ?>][align]"><?php esc_attr( _e("Alignment") ); ?></label></td>
							<td class="field">
								<input name="image[<?php echo $Image->getParameter('ImageID'); ?>][align]" id="image-align-none-<?php echo $Image->getParameter('ImageID'); ?>" checked="checked" value="" type="radio" />
								<label for="image-align-none-<?php echo $Image->getParameter('ImageID'); ?>" class="align image-align-none-label"><?php esc_attr( _e("None") );?></label>
								<input name="image[<?php echo $Image->getParameter('ImageID'); ?>][align]" id="image-align-left-<?php echo $Image->getParameter('ImageID'); ?>" value="alignleft" type="radio" />
								<label for="image-align-left-<?php echo $Image->getParameter('ImageID'); ?>" class="align image-align-left-label"><?php esc_attr(  _e("Left") );?></label>
								<input name="image[<?php echo $Image->getParameter('ImageID'); ?>][align]" id="image-align-center-<?php echo $Image->getParameter('ImageID'); ?>" value="centered" type="radio" />
								<label for="image-align-center-<?php echo $Image->getParameter('ImageID'); ?>" class="align image-align-center-label"><?php esc_attr( _e("Center") );?></label>
								<input name="image[<?php echo $Image->getParameter('ImageID'); ?>][align]" id="image-align-right-<?php echo $Image->getParameter('ImageID'); ?>" value="alignright" type="radio" />
								<label for="image-align-right-<?php echo $Image->getParameter('ImageID'); ?>" class="align image-align-right-label"><?php esc_attr( _e("Right") );?></label>
							</td>
						</tr>
						<tr class="image-size">
							<th class="label"><label for="image[<?php echo $Image->getParameter('ImageID'); ?>][size]"><span class="alignleft"><?php esc_attr( _e("Size") ); ?></span></label>
							</th>
							<td class="field">
								<?php
									$ImageLibrarian = new ImageLibrarian();
									$Types = $ImageLibrarian->getTypes();
									foreach ($Types as $Type){ 
										$image_size = getimagesize($Image->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImage'.$Type));
										if (!is_array($image_size)){
											$image_size = array();
										}
										?>
										<input name="image[<?php echo $Image->getParameter('ImageID'); ?>][size]" id="image-size-<?php echo strtolower($Type); ?>-<?php echo $Image->getParameter('ImageID'); ?>" type="radio" checked="checked" value="<?php echo $Type; ?>" />
										<label for="image-size-<?php echo strtolower($Type); ?>-<?php echo $Image->getParameter('ImageID'); ?>"><?php esc_attr( _e($Type) ); echo ' ('.$image_size[0].'x'.$image_size[1].')'; ?></label>
									<?php }
								?>
							</td>
						</tr>
					   <tr class="submit">
							<td>
								<input type="hidden"  name="image[<?php echo $Image->getParameter('ImageID'); ?>][thumb]" value="<?php echo $Image->getGalleryDirectory().$Image->getParameter('GalleryImageThumb'); ?>" />
								<input type="hidden"  name="image[<?php echo $Image->getParameter('ImageID'); ?>][url]" value="<?php echo $Image->getGalleryDirectory().$Image->getParameter('GalleryImageOriginal'); ?>" />
							</td>
							<td class="savesend"><button type="submit" class="button" value="1" name="send[<?php echo $Image->getParameter('ImageID'); ?>]"><?php echo esc_attr( __('Insert into Post') ); ?></button></td>
					   </tr>
				  </tbody></table>
				</div>
			<?php		  
			}
		}
		?>
		</div>
		<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
		<input type="hidden" name="select_gal" id="select_gal" value="<?php echo (int) $galleryID; ?>" />
	</form>
	<?php
}

add_action('media_upload_topquark_gallery', 'media_upload_topquark_gallery');

add_action('wp_head','topquark_scripts');
function topquark_scripts(){
	echo '<script type="text/javascript" src="'.get_bloginfo('wpurl').'/../app/plugins/topquark/lib/js/topquark.stuffis.js" ></script>'."\n";
}

add_filter('additional_printable_print_styles','topquark_help_a_fella_out');
// when upgrading the look of Top Quark to match WordPress, I realized I missed these in the FestivalApp.  I'm trying
// to work encapsulatedly, so didn't want to get into changing that plugin.  I'll get it on a later release.
function topquark_help_a_fella_out($styles){
	return '.update-nag{display:none;} #tqp_admin_wrapper{margin:0px}';
}

?>