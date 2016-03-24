<!DOCTYPE html>
<!-- parse.php -->
<!-- show Alpino input parse -->

<!-- version 0.4 date: 14.10.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>

<?php
require 'header.php';
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
?>
<link rel="stylesheet" href="<?php echo $home; ?>/style/css/tree-visualizer.css">
</head>

<body>
<div id="container">
<?php session_cache_limiter('private'); //  avoids page reload when going back
session_start();
header('Content-Type:text/html; charset=utf-8');

/***********************************************************/
/* VARIABLES */
$id= session_id();
$input = $_POST['input'];
$date=date('d-m-Y');
$time=time(); // time stamp

if (getenv('REMOTE_ADDR')){
$user = getenv('REMOTE_ADDR');
}
else {
$user="anonymous";
}

// dir to includes
$alpino="$scripts/AlpinoParser.php";
$tokenizer="$scripts/Tokenizer.php";
$simpledom="$scripts/SimpleDOM.php";
$modlem="$scripts/ModifyLemma.php";

// log files
$grtllog="$log/gretel-ebq.log";

// navigation
$start="input.php";
$next="matrix.php";
$new='<button type="button" onclick="window.location.href=\''.$start.'?'.$time.'\'">New Input Example</button>';
$back='<button type="button" value="Back" onclick="goBack()">Back</button>';
$continue='<div style="float:right"><button type="Submit" value="Continue" class="colour">Continue</button></div>';
$step=2; // for progress bar
$title="<h1>Step 2: Input Parse</h1><hr>";

// messages
$captcha="This is a suspicious input example. GrETEL does not accept URL's or HTML as input.";
$info='The structure of the <strong>tagged</strong><a href="#" class="clickMe" tooltip="indicating <em>word classes</em>, like <strong>n</strong> (noun)"> <sup>[?]</sup></a> and <strong>parsed</strong><a href="#" class="clickMe" tooltip="indicating <em>dependencies</em> (or relations), like <strong>su</strong> (subject) and <em>constituents</em>, like <strong>np</strong> (noun phrase)"> <sup>[?]</sup></a> sentence: ';

$info2='<p>If the analysis is different from what you expected, you can enter <a href="#" class="clickMe" tooltip="Use the <strong>New</strong> button (top right hand side)"> another input example</a>.</p>';


// set search mode
if ($_POST['search']=="basic") {
  $_SESSION['search']="basic";
}
else {
  $_SESSION['search']="advanced";
}

$sm=$_SESSION['search'];

/***********************************************************/
/* INCLUDES */
require("$navigation");
require("$tokenizer");
require("$simpledom");
require("$alpino");
require("$modlem");
/***********************************************************/

if (preg_match('/(http:\/\/.*)|(<.*)|(.*>)/', $input)==1) { // check for spam
  echo "<h3>Error</h3>";
  echo "<p>".$captcha."</p>";
  echo $back;
  exit;
}

elseif(!$input || empty($input)) {
  echo "<h3>Error</h3>";
  echo "<p>Please provide an <b>input example</b>.</p>";
  echo $back;
  exit;
}

else { // LOG INPUT
  $log = fopen($grtllog, "a");  //concatenate sentences
  fwrite($log, "\n$date\t$user\t$id-$time\t$sm\t$input\n");
  fclose($log);
  $_SESSION['sentid']="$id-$time";
  $_SESSION['example']="$input";  // for retrieving input example on other pages
}

//ALPINO
$tokinput = Tokenize($input);
$_SESSION['sentence']="$tokinput";

$parse = Alpino($tokinput,$id); // $parse = parse location

//MODIFY LEMMA
$parseloc=ModifyLemma($parse,$id,$tmp); // returns parse location

// INFO
echo "$title";
echo "<p>$info";
echo "<em>$tokinput</em></p>";

echo '<div id="tree-output"></div>';

echo '
<form action="'.$next.'" method="post" enctype="multipart/form-data" >
';
echo "<br>";
echo $info2;
echo $new;
echo $back;
echo $continue;

echo "</form>";
?>

</div>
<script src="<?php echo $home; ?>/js/min/tree-visualizer.min.js"></script>
<script>
$(document).ready(function(){
$.treeVisualizer('<?php echo "$home/tmp/$id"; ?>-pt.xml', {
    container: "#tree-output"
});
});
</script>
</body>
</html>
