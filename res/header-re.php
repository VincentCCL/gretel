<title>GrETEL 2.0 - RegEx Search</title>

<?php
require("../config/config.php");

$navigation="navigation-re.php"; // progress bar script

// stylesheets

echo '
<link href="http://fonts.googleapis.com/css?family=Oswald:400,300,700|Carrois+Gothic" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="'.$home.'/style/css/gretel.css"></link>
<link rel="stylesheet" type="text/css" href="'.$home.'/style/css/tooltip.css"></link>
<link rel="shortcut icon" type="image/pgn" href="'.$home.'/img/gretel_logo_trans.png" />

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript" src="'.$home.'/js/tooltip.js" ></script>
<script type="text/javascript" src="'.$home.'/js/browserDetection.js" ></script>
<script type="text/javascript" src="'.$home.'/js/TaalPortaal.js" ></script>

<script type="text/javascript" src="'.$home.'/js/jquery-1.2.6.pack.js"></script>

<style type="text/css">
iframe {
width:100%;
height:400px;
border:1;
scrolling:auto;
}
</style>
';

include_once("$root/scripts/AnalyticsTracking.php");
?>