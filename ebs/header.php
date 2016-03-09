<title>GrETEL 2.0 - Example-based Search</title>

<?php
require("../config/config.php");

$navigation="navigation.php"; // progress bar script

// stylesheets

echo '
<link href="http://fonts.googleapis.com/css?family=Oswald:400,300,700|Carrois+Gothic" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="'.$home.'/style/css/gretel.css"></link>
<link rel="stylesheet" type="text/css" href="'.$home.'/style/css/tooltip.css"></link>
<link rel="shortcut icon" type="image/png" href="'.$home.'/img/gretel_logo_trans.png" />

<script src="https://code.jquery.com/jquery-2.2.1.min.js"></script>
<script type="text/javascript" src="'.$home.'/js/tooltip.js" ></script>
<script type="text/javascript" src="'.$home.'/js/browserDetection.js" ></script>
<script type="text/javascript" src="'.$home.'/js/TaalPortaal.js" ></script>

';

include_once("$root/scripts/AnalyticsTracking.php");
?>