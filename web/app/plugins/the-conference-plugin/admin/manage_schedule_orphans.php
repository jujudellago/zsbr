<?php
	if (!$Bootstrap){
	    die ("You cannot access this file directly");
	}

	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'manage');
	$manage = $Bootstrap->makeAdminURL($Package,'edit_schedule');
	$year = urldecode($_REQUEST[YEAR_PARM]);
	$schedule_id = $_REQUEST['schedule'];
	$returnURL = $manage."&".YEAR_PARM."=".urlencode($year)."&type=".$schedule_id;
	$Bootstrap->addURLToAdminBreadcrumb($returnURL,$Package->admin_pages['edit_schedule']['title']);
	$Bootstrap->addPackagePageToAdminBreadcrumb($Package,'schedule_orphans');
	
	$delete_show = $Bootstrap->makeAdminURL($Package,'delete_show');
	$update_show = $Bootstrap->makeAdminURL($Package,'update_show');

    $ShowContainer = new ShowContainer();
    $ScheduleContainer = new ScheduleContainer();

	$Schedule = $ScheduleContainer->getSchedule($year,$schedule_id);
	if (!$Schedule){
		header('Location:'.$returnURL);
		exit();
	}	

	$SchedulePainter = new SchedulePainter();
	$SchedulePainter->setShowTitleURLCallback("getShowTitleURL");
	add_filter('schedule_painter_options','admin_schedule_page_options');

	global $edit;
	$edit = $Bootstrap->makeAdminURL($Package,'update_show');

	$smarty->assign('Schedule',$Schedule);
	$smarty->assign('SchedulePainter',$SchedulePainter);
	$smarty->assign('ShowListingsArray',$ShowContainer->getShowListingsArray($schedule_id,$year,null));
	$save_template_dir = $smarty->template_dir;
	$smarty->template_dir = dirname(__FILE__).'/smarty/';
	$smarty->display('festivalapp.admin.schedule_orphans.tpl');
	$smarty->template_dir = $save_template_dir;
?>