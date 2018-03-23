<?php
$videoCode = $_POST['videoCode'];
$videoProviderId = $_POST['videoProviderId'];
$width = $_POST['width'];
$height = $_POST['height'];
$autoplay = $_POST['autoplay'];
$url = $_POST['url'];

$criteria3['videoCode'] = $videoCode;
$criteria3['videoProviderId'] = $videoProviderId;
$criteria3['width'] = $width;
$criteria3['height'] = $height;
$criteria3['autoplay'] = $autoplay;
$criteria3['url'] = $url;
$d1 = new Vbox_class();
$embedCode = $d1->getEmbedVideoCode($criteria3);

if($GLOBALS['social_sharing']) {
	$vc1 = new Vbox_display_class();
	$embedCode .= $vc1->getSocialShareButtons($url);
}

echo $embedCode;
?>