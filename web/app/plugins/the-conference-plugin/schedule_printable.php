<?php
	$PossiblePaths = array(dirname(__FILE__)."/../../",dirname(__FILE__)."/../topquark/lib/");
	foreach ($PossiblePaths as $PossiblePath){
		if (file_exists($PossiblePath."Standard.php")){
			define('tqp_pathToStandard',$PossiblePath);
		}
	}
	include_once(tqp_pathToStandard."Standard.php");
    $package = $Bootstrap->usePackage('FestivalApp');
    
    $SchedulePainter = new SchedulePainter();

    if ($_GET[YEAR_PARM] != ""){
        $Year = $_GET[YEAR_PARM];
    }
    else{
        $Year = date("Y");
    }

    $Types = $_GET['type'] != "" ? array($_GET['type']) : "";
    
    if ($_GET['type'] == ""){
        $ScheduleContainer = new ScheduleContainer();
        $Schedules = $ScheduleContainer->getAllSchedules($Year);
        $Types = array();
        foreach ($Schedules as $Type => $Ignore){
            $Types[] = $Type;
        }
        $title = SITE_NAME." $Year Festival Schedule";
    }
    else{
        $Types = array($_GET['type']);
        $title = SITE_NAME." $Year ".$_GET['type']." Schedule";
    }
    
	if (file_exists($package->etcDirectory."{$Year}ScheduleNames.txt")){
        $Serialized = @file_get_contents($package->etcDirectory."{$Year}ScheduleNames.txt");
        $ScheduleNames = unserialize($Serialized);
        $first = true;
        foreach ($Types as $Type){
        	if (file_exists($package->etcDirectory."{$Year}_{$Type}_ListingsArray.txt")){
                $Serialized = file_get_contents($package->etcDirectory."{$Year}_{$Type}_ListingsArray.txt");
                $ShowListingsArray = unserialize($Serialized);
                
                if (!$first){
                    echo "<div style='page-break-before:always'>&nbsp;</div>\n";
                }
                else{
                    $first = false;
                }
        
                $Content.= "<h1 class=\"ScheduleName\">";
                $Content.= $ScheduleNames[$Type];
                $Content.= "</h1>\n";
                
                if ($_GET['tables'] == 'no'){
                    foreach ($ShowListingsArray as $DayArray){
						if (is_array($DayArray)){
	                        foreach ($DayArray['Headings'] as $index => $Heading){
	                            $Content.= "<h3>$Heading (".$DayArray['PrettyHeading'].")";
	                            if (is_array($DayArray['HeadingSponsors']) and $DayArray['HeadingSponsors'][$index] != ""){
	                                $Content.= " - Sponsor: ".$DayArray['HeadingSponsors'][$index];
	                            }
	                            $Content.= "</h3>\n";
								if (is_array($DayArray['Shows'][$index])){
	                            	$Content.= "<ul>\n";
		                            foreach ($DayArray['Shows'][$index] as $Show){
		                                if (is_a($Show,'Show')){
		                                    $Shows = array($Show);
		                                }
		                                elseif (is_array($Show)){
		                                    $Shows = $Show;
		                                }
		                                if (is_array($Shows)){
		                                    foreach ($Shows as $Show){
		                                        $Content.= "<li><p>";
		                                        $Content.= $Show->getParameter('ShowPrettyStartTime'). " - ";
		                                        $Content.= $Show->getParameter('ShowTitle');
		                                        if ($Show->getArtistNames() != "" and $Show->getArtistNames() != $Show->getParameter('ShowTitle')){
		                                            $Content.= " with ".$Show->getArtistNames();
		                                        }
		                                        if ($Show->getParameter('ShowDescription') != ""){
		                                            $Content.= ". ".$Show->getParameter('ShowDescription');
		                                        }
		                                        $Content.= "</p></li>\n";
		                                    }
		                                }
		                            }
		                            $Content.= "</ul>\n";
								}
	                        }
						}
                    }
                }
                else{
                    $Content.= $SchedulePainter->paintSchedule($ShowListingsArray,$_GET['style']);
                }
            }
        }
    }
    else{
        $Content = "No information found for that year";
    }
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK REL=StyleSheet HREF='<?php echo $package->getPackageURL(); ?>/css/schedule.css' TYPE='text/css'>
<style type='text/css'>
.ShowListingTable2{
	border-collapse:separate;
}
</style>

</head>
<body style='margin:15px;'>
    <P>Subject to Change</p>
<div style='width:900px'>
<?php
    echo $Content;
?>
</div>
</body>
</html>