<?php

	$SchedulesTab = new HTML_Tab("Schedules","Schedules");
	$save_template_dir = $smarty->template_dir;
	$smarty->template_dir = dirname(__FILE__).'/smarty/';
	foreach ($Schedules as $s => $Schedule){
		$Schedules[$s]->setParameter('ScheduleSlug',preg_replace('/[^a-z0-9\-]/','-',strtolower($Schedule->getParameter('ScheduleID'))));
	}
	$FestivalPrettyDays = $Festival->getPrettyDays();
	foreach ($Schedules as $sch => $Schedule){
		$Stages = $Schedule->getParameter('ScheduleStages');
		$PrettyDays = array();
		foreach ($Stages as $s => $Stage){
			foreach ($Stage['Times'] as $d => $Times){
				if ($Times[0] != "" and $Times[1] != ""){
					$PrettyDays[$d] = $FestivalPrettyDays[$d];
				}
			}
		}
		ksort($PrettyDays);
		$Schedules[$sch]->setParameter('SchedulePrettyDays',$PrettyDays);
		if ($orphans = $ShowContainer->SimpleShowContainer->findOrphanShows($sch)){
			$Schedules[$sch]->setParameter('ScheduleOrphanShows',$orphans);
		}
	}
	$smarty->assign('Schedules',$Schedules);
	$smarty->assign('Festival',$Festival);
	$smarty->assign('Year',$Year);
	$SchedulesTab->addPlainText($smarty->fetch('festivalapp.admin.manage_schedules.control.tpl'),$smarty->fetch('festivalapp.admin.manage_schedules.tpl'));
	$smarty->template_dir = $save_template_dir;

    if (!isset($DefaultTab)){
        $DefaultTab = 'SchedulesTab';
    }

	$form->addTab($SchedulesTab);

	$admin_head_extras.= '
	<link rel="stylesheet" type="text/css" href="'.plugins_url('the-conference-plugin/admin/css/festivalapp.admin.new_interface.css').'" />
	<script type="text/javascript" src="'.plugins_url('the-conference-plugin/admin/js/functions.js.php').'" ></script>
	<script type="text/javascript" src="'.plugins_url('the-conference-plugin/admin/js/festivalapp.admin.new_interface.js').'" ></script>
	<script type="text/javascript" src="'.CMS_INSTALL_URL.'lib/js/php_js.js"></script>
	';
?>
