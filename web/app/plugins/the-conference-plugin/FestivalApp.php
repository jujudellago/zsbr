<?php
/*
Plugin Name: The Conference Plugin
Plugin URI: http://topquark.com/extend/plugins/the-conference-plugin
Description: A plugin for managing and publishing the schedule for a multi-day, multi-talented event such as a conference or a festival.  Requires the 'topquark' Plugin to be installed and activated.
Version: 1.1.3.2
Author: Top Quark
Author URI: http://topquark.com

    Copyright (C) 2011 Trevor Mills (support@topquark.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
global $edit;
add_action('init','FestivalApp_init');
function FestivalApp_init(){
	if (!class_exists('Bootstrap')){
		add_action('admin_notices','FestivalApp_admin_notices');
	}
	else{
		$Bootstrap = Bootstrap::getBootstrap();
		$Bootstrap->registerPackage('FestivalApp','../../../the-conference-plugin/');
		//$Bootstrap->usePackage('FestivalApp');

		add_action('wp_head', 'topquark_FestivalApp_add_css', 1);
		add_action('admin_head', 'topquark_FestivalApp_add_admin_css', 1);
		wp_enqueue_script( 'jquery-ui-resizable' );
		if (is_admin()){
			switch($_GET[ADMIN_PAGE_PARM]){
			case 'update_festival':
				if (!wp_script_is( 'jquery-ui-datepicker', 'registered')){
					wp_register_script( 'jquery-ui-datepicker', plugins_url('the-conference-plugin/admin/js/jquery.datepicker.min.js'), 'jquery', null, 1);
				}
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_style( 'jquery-ui-datepicker', plugins_url('the-conference-plugin/admin/css/jquery-ui/ui.all.css') );
				break;
			case 'update_artist':
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jcrop-custom', plugins_url('topquark/lib/js/jcrop/js/jquery.Jcrop.js') );
				wp_enqueue_style( 'jquery-ui-dialog-custom', plugins_url('the-conference-plugin/admin/css/jquery-ui/ui.all.css') );
				wp_enqueue_style( 'jcrop' );
				//wp_enqueue_style( 'jquery-ui-dialog' );
				break;
			}
		}
		add_action('wp_ajax_edit_image_markup','topquark_FestivalApp_edit_image_ajax');
		add_action('wp_ajax_get_admin_schedule','topquark_FestivalApp_get_admin_schedule');
		add_action('wp_ajax_admin_schedule_action','topquark_FestivalApp_admin_schedule_action');
		add_action('wp_ajax_admin_show_data','topquark_FestivalApp_admin_show_data');
		
		add_action( 'pre_get_posts', 'tcp_template_redirect' );		
	}
}

function FestivalApp_admin_notices(){
	$notes = array();
	$errors = array();
	if (!class_exists('Bootstrap')){
		$errors[] = sprintf(__('The plugin "The Conference Plugin" requires the "Top Quark Architecture" plugin to be installed and activated.  This plugin can be downloaded from %sWordPress.org%s'),'<a href="http://wordpress.org/extend/plugins/topquark/" target="_blank">','</a>');
	}

    foreach ($errors as $error) {
        echo sprintf('<div class="error"><p>%s</p></div>', $error);
    }
    
    foreach ($notes as $note) {
        echo sprintf('<div class="updated fade"><p>%s</p></div>', $note);
    }
}

// Because there are add-ons to this plugin that might use smarty, I'm going to perform
// a cache-clearing action any time any plugin is activated or deactivated.  
add_action('deactivated_plugin','FestivalApp_clear_Smarty_cache',10,2);
function FestivalApp_clear_Smarty_cache($plugin, $network_wide){
	if (class_exists('Smarty_Instance')){
		$smarty = new Smarty_Instance();
		$smarty->clear_compiled_tpl();
	}
}

function topquark_FestivalApp_add_css(){
	printf('<link rel="stylesheet" type="text/css" href="%s/FestivalApp.css" />'."\n", plugins_url('the-conference-plugin'));
}

function topquark_FestivalApp_add_admin_css(){
	printf('<link rel="stylesheet" type="text/css" href="%s/FestivalApp.admin.css" />'."\n", plugins_url('the-conference-plugin'));
}

function topquark_FestivalApp_get_admin_schedule(){
	$_GET = array_map('urldecode',$_GET);
	$Bootstrap = Bootstrap::getBootstrap();
	$Package = $Bootstrap->usePackage('FestivalApp');
	global $edit;
	$edit = $Bootstrap->makeAdminURL($Package,'update_show');
	$FestivalContainer = new FestivalContainer();
	$ScheduleContainer = new ScheduleContainer();
	$ShowContainer = new ShowContainer();
	$Festival = $FestivalContainer->getFestival($_GET['year']);
	if (!is_a($Festival,'Festival')){
		die("Something went wrong.  I couldn't find a festival for the year '".$_GET['year']."'");
	}
	$Schedule = $ScheduleContainer->getSchedule($_GET['year'],$_GET['schedule']);
	if (!is_a($Schedule,'Schedule')){
		die("Something went wrong.  I couldn't find a schedule for '".$_GET['schedule']."'");
	}
	$ShowListingsArray = $ShowContainer->getShowListingsArray($_GET['schedule'],$_GET['year'],$_GET['day']);
	if (!is_array($ShowListingsArray)){
		// If it's just a string, it means there are no events added yet.  Create a string to tell that to the user
		$ShowListingsArray = "<p class='ShowListingHeading'>".$Festival->getPrettyDay($_GET['day'])."</p><p>There are no ".$Schedule->getParameter('ScheduleID')."s added yet.  <a href='$edit&type=".urlencode($_GET['schedule'])."&".YEAR_PARM."=".urlencode($_GET['year'])."&day=".$_GET['day']."'>Add a ".$Schedule->getParameter('ScheduleID')." to the ".$Schedule->getParameter('ScheduleName')."</a>";
	}
	$SchedulePainter = new SchedulePainter();
	$SchedulePainter->setBlankCellContent("&nbsp;<a href='".$edit."&type=".urlencode($_GET['schedule'])."&".YEAR_PARM."=".urlencode($_GET['year'])."".'&stage=$h&day=$Heading&time=$CanonicalTime\'><img src=\''.$Package->getPackageURL().'admin/images/plus.gif\' border=0></a>');
	$SchedulePainter->setShowTitleURLCallback("getShowTitleURL");
	add_filter('schedule_painter_options','admin_schedule_page_options');
	echo $SchedulePainter->paintSchedule($ShowListingsArray);
	
	die();
}

function topquark_FestivalApp_admin_schedule_action(){
	$Bootstrap = Bootstrap::getBootstrap();
	$Package = $Bootstrap->usePackage('FestivalApp');
	switch ($_POST['do']){
	case 'delete':
		$ScheduleContainer = new ScheduleContainer();
        $ScheduleContainer->deleteSchedule($_POST['year'],$_POST['schedule']);
		break;
	}
	die();
}

function topquark_FestivalApp_admin_show_data(){
	$Bootstrap = Bootstrap::getBootstrap();
	$Package = $Bootstrap->usePackage('FestivalApp');
	$ShowContainer = new SimpleShowContainer();
	$theShow = $ShowContainer->getShow($_GET['show_id']);
	$wc = new whereClause();
	$wc->addCondition($ShowContainer->getColumnName('ShowYear')." = ?",$_GET['_year']);
	$wc->addCondition($ShowContainer->getColumnName('ShowScheduleUID')." = ?",$_GET['schedule']);
    $AllShows = $ShowContainer->getAllShows($wc);
	if (!is_array($AllShows)){
		$AllShows = array();
	}
	$Shows = array();
	foreach ($AllShows as $Show){
		if ($Show->getParameter('ShowID') != $_GET['show_id']){
			if ($_GET['what'] != 'SwitchWith' or $Show->getParameter('ShowStartTime') != ""){
				$Shows[$Show->getParameter('ShowID')] = $Show->getParameter('ShowTitle')." (".$Show->getParameter('ShowPrettyStage').": ".date("D",strtotime($Show->getParameter('ShowPrettyDay'))).", ".$Show->getParameter('ShowPrettyStartTime')." - ".$Show->getParameter('ShowPrettyEndTime').")";
			}
		}
	}
	if (!is_array($Shows)){
		$Shows = array();
	}
	$return = "";
	switch ($_GET['what']){
	case 'SwitchWith':
		$return.= "<select id='ShowSwitchWith' name='ShowSwitchWith' size='1' onchange='javascript:document.Update_Form.submit();'>\n";
		$return.= "<option value=''>&lt;Choose ".vocabulary('Show')." to switch positions&gt;</option>\n";
		break;
	case 'RepeatOfShow':
		$theShow = $ShowContainer->getShow($_GET['show_id']);
		$return.= "<select id='ShowRepeatOfShowID' name='ShowRepeatOfShowID' size='1'>\n";
		$return.= "<option value=''>&lt;Choose the ".vocabulary('Show')." this is a repeat of&gt;</option>\n";
		break;
	}
	foreach ($Shows as $show_id => $show_title){
		if ($_GET['what'] == 'RepeatOfShow' and is_a($theShow,'Show') and $theShow->getParameter('ShowRepeatOfShowID') == $show_id){
			$selected = ' selected="selected"';
		}
		else{
			$selected = '';
		}
		$return.= "<option value='$show_id'$selected>$show_title</option>\n";
	}
	$return.= "</select>\n";
	echo $return;
	die();
}

function admin_schedule_page_options($args){
	list($SchedulePainter,$ShowListings,$Heading) = $args;
	$Columns = count($ShowListings["Headings"]);
	if ($Columns > 5){
		$SchedulePainter->times_position = 'both';
		//$SchedulePainter->show_handle = true;
	}
	else{
		$SchedulePainter->times_position = 'left';
		$SchedulePainter->show_handle = false;
	}
	return null;
}
	
function getShowTitleURL(&$Show){
    global $edit;
 	if (is_a($Show,'Show')){
 	    return $edit."&id=".$Show->getParameter('ShowID');
 	}
 	else{
 		die("Invalid Object Passed: ".get_class($Show));
 	}
}

register_activation_hook(__FILE__,'festivalapp_activate');
function festivalapp_activate(){
	if (!class_exists('Bootstrap')){
		FestivalApp_admin_notices();
		die();
	}
	// Herein lie some updates required as part of making
	$festivalapp_db_version = "1.6";
 	$installed_ver = get_option( "festivalapp_db_version" );
	if ($installed_ver != $festivalapp_db_version){
	    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		if ($installed_ver < '1.2'){
		    require_once('ShowContainer.php');
		    require_once('ScheduleContainer.php');
			$ShowContainer = new SimpleShowContainer();
			maybe_add_column($ShowContainer->getTableName(),'ShowScheduleUID','alter table '.$ShowContainer->getTableName().' add ShowScheduleUID int(6) unsigned default NULL');
			$Shows = $ShowContainer->getAllShows();
			$ScheduleContainer = new ScheduleContainer();
			$Schedules = array();
			if (is_array($Shows)){
				foreach ($Shows as $Show){
					$Year = $Show->getParameter('ShowYear');
					if (!array_key_exists($Year,$Schedules)){
						$_Schedules = $ScheduleContainer->getAllSchedules($Year);
						if (is_array($_Schedules)){
							foreach ($_Schedules as $Schedule){
								$Schedules[$Year][$Schedule->getParameter('ScheduleID')] = $Schedule->getParameter('ScheduleUID');
							}
						}
					}
					$Show->setParameter('ShowScheduleUID',$Schedules[$Year][$Show->getParameter('ShowType')]);
					$ShowContainer->updateShow($Show);
				}
			}
		}
		if ($installed_ver < '1.3'){
		    require_once('ShowContainer.php');
			$ShowContainer = new SimpleShowContainer();
			maybe_add_column($ShowContainer->getTableName(),'ShowEmbeddedScheduleUID','alter table '.$ShowContainer->getTableName().' add ShowEmbeddedScheduleUID int(6) unsigned default NULL');
			maybe_add_column($ShowContainer->getTableName(),'ShowEmbeddedStageID','alter table '.$ShowContainer->getTableName().' add ShowEmbeddedStageID int(2) unsigned default NULL');
		}
		if ($installed_ver < '1.4'){
		    require_once('FestivalArtistContainer.php');
			$FestivalArtistContainer = new FestivalArtistContainer();
			maybe_add_column($FestivalArtistContainer->getTableName(),'ArtistDoNotPublish','alter table '.$FestivalArtistContainer->getTableName().' add ArtistDoNotPublish int(1) unsigned default 0');
		}
		if ($installed_ver < '1.5'){
		    require_once('FestivalContainer.php');
			$FestivalContainer = new FestivalContainer();
			maybe_add_column($FestivalContainer->getTableName(),'FestivalDoNotPublishFeeds','alter table '.$FestivalContainer->getTableName().' add FestivalDoNotPublishFeeds int(1) unsigned default 0');
		}
		if ($installed_ver < '1.6' or true){
		    require_once('ShowContainer.php');
			$ShowContainer = new ShowContainer();
			maybe_add_column($ShowContainer->getTableName('Main'),'ArtistOrder','alter table '.$ShowContainer->getTableName('Main').' add ArtistOrder int(6) unsigned default NULL');
		}
	 	update_option( "festivalapp_db_version", $festivalapp_db_version );
	}
}

/***************************************
* Dimension Callbacks for Artist Images
***************************************/
add_filter('set_gallery_dimension_callbacks','conf_plugin_set_gallery_dimensions_callback',5,2);
function conf_plugin_set_gallery_dimensions_callback($callbacks,$gallery_name){
	if ($gallery_name == 'General Artist Images' or preg_match('/^(.*) Artist Images$/',$gallery_name)){
        $DimensionCallbacks = array();
        // get an exact fit to 160x160 for the artists & keep the original
        $DimensionCallbacks['Thumb'] = create_function('','return array("width" => 160, "height" => 160, "conditions" => '.RESIZE_CROP.');');
        $DimensionCallbacks['Original'] = create_function('','return array("width" => 0, "height" => 0, "conditions" => '.RESIZE_CONDITIONS_DEFAULT.');');
		$callbacks = $DimensionCallbacks;
	}
	return $callbacks;
}

function tcp_template_redirect(){
	if (isset($_GET['conf_plugin_preview']) and $_GET['conf_plugin_preview'] == 'true' and isset($_GET['sc'])){
		require_once('lib/fakepage.php');
		add_filter('topquark_FestivalApp_permalink','tcp_shortcode_preview_permalink',10,2);
		$sc = stripslashes(urldecode($_GET['sc']));
		$Spoof = new FakePage;
		$Spoof->page_slug = 'preview';
		$Spoof->page_title = 'Shortcode Preview';
		$Spoof->content = "Use this shortcode in a page or post:<br/><code>&#91;".preg_replace('/[\[\]]/','',$sc)."&#93;</code>";
		$Spoof->content.= "<p><em><strong>Admin Note</strong>: If you're not seeing something you expected to see, try Updating Published Schedules back on the admin ".pluralize('Festival')." page.</em></p>";
		$Spoof->content.= $sc;
		$Spoof->force_injection = true;
		return;
	}
}

function tcp_shortcode_preview_permalink($permalink,$what){
	return trailingslashit(get_bloginfo('url')).'?'.$_SERVER['QUERY_STRING'].'&subject='.$what;
}

function topquark_FestivalApp_edit_image_ajax(){
	switch($_GET['action']){
	case 'edit_image_markup':
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('Gallery');
		$GalleryImageContainer = new GalleryImageContainer();
		$Image = $GalleryImageContainer->getGalleryImage($_GET['id']);
		if (!class_exists('Smarty_Instance')){
			include_once(ABSPATH.'wp-content/plugins/topquark/lib/Smarty_Instance.class.php');
		}
		$smarty = new Smarty_Instance();
		$smarty->template_dir = dirname(__FILE__).'/admin/smarty/';
		$smarty->assign('Image',$Image);
		$resized_info = getimagesize($Image->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImageResized'));
		$thumb_info = getimagesize($Image->getGalleryDirectory(IMAGE_DIR_FULL).$Image->getParameter('GalleryImageThumb'));
		$smarty->assign('resized_info',$resized_info);
		$smarty->assign('thumb_info',$thumb_info);

		$width = $resized_info[0] + $thumb_info[0] + 60;
		$markup = $smarty->fetch('festivalapp.admin.edit-image.tpl');
		echo json_encode(array('width' => $width, 'markup' => $markup));
		break;
	}
	die(1);
}

//add_action('init','blah',100);
function blah(){
	$Bootstrap = Bootstrap::getBootstrap();
	$Bootstrap->usePackage('FestivalApp');
	$ShowContainer = new ShowContainer();
	$blah = $ShowContainer->getShowListingsArray(13,"2013 Test",0);
	var_dump($blah);
	die();
}


include_once('the-conference-plugin.settings.php');
include_once('the-conference-plugin.shortcodes.php');
include_once('the-conference-plugin.feeds.php');

?><?php //BEGIN::SELF_HOSTED_PLUGIN_MOD
					
	/**********************************************
	* The following was added by Self Hosted Plugin
	* to enable automatic updates
	* See http://wordpress.org/extend/plugins/self-hosted-plugins
	**********************************************/
	require "__plugin-updates/plugin-update-checker.class.php";
	$__UpdateChecker = new PluginUpdateChecker('http://topquark.com/extend/plugins/the-conference-plugin/update', __FILE__,'the-conference-plugin');			
	

	include_once("__plugin-updates/topquark.settings.php");
	add_action('init',create_function('$a','do_action("register_topquark_plugin","The Conference Plugin");'));
//END::SELF_HOSTED_PLUGIN_MOD ?>