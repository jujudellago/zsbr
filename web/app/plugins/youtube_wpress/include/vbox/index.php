<?php
include_once("include/webzone.php");

// views
if(@$_GET['q']=='displayEmbedCode') include("views/ajax_displayEmbedCode.php");
elseif (@$_GET['q']=='displayVideosList') include("views/ajax_displayVideosList.php");

else echo 'Silence is golden.';
?>