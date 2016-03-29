<!DOCTYPE html>
<!-- query.php -->
<!-- query summary -->

<!-- version 0.7 date: 01.03.2013  log subtrees -->
<!-- version 0.6 date: 12.12.2014  page loader added -->
<!-- version 0.5 date: 15.10.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>
<?php
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require 'header.php';?>

<link rel="stylesheet" href="<?php echo $home; ?>/style/css/tree-visualizer.css" >

</head>

<body>
<div class="loading" style="display:none;">
<p>Loading... Please wait</p>
</div>

<div id="container">
<?php
session_cache_limiter('private'); // avoids page reload when going back
session_start();
header('Content-Type:text/html; charset=utf-8');

/***********************************************************/
 /* VARIABLES */
$id= session_id();
$date=date('d-m-Y');
$time=time();
$sm=$_SESSION['search'];

// navigation
$start="input.php";
$next="results.php";
$step=5;
$title="<h1>Step 5: Query Overview</h1><hr/>";
$new='<button type="button" onclick="window.location.href=\''.$start.'?'.$time.'\'">New Input Example</button>';
$back='<button type="button" value="Back" onclick="goBack()">Back</button>';
$continue='<div style="float:right"><button type="Submit" value="Continue" class="colour">Continue</button></div>';

// log files
$grtllog="$log/gretel-ebq.log";
$treelog="$log/gretel-querytrees.log";

if ($_POST['treebank']) {
  $treebank=$_POST['treebank'];
  $_SESSION['treebank']="$treebank";
}
else {
  $treebank=$_SESSION['treebank'];
}

// get subtreebanks
$subtb=$treebank.'tb';

if ($_POST[$subtb]) {
  $components=$_POST[$subtb];
  $_SESSION['subtb']=$components;
}
elseif (isset($_SESSION['subtb'])) {
  $components=$_SESSION['subtb'];
}

else {
  echo "<h1>Error</h1>";
  echo "Please select at least one subtreebank\n<br/><br/>";
  echo $back;
  exit;
}

// get context option

if (isset($_POST["ct"])) {
  $_SESSION["ct"]="on";
}
else {
  $_SESSION["ct"]="off";
}

if (getenv('REMOTE_ADDR')){
$user = getenv('REMOTE_ADDR');
}
else {
$user="anonymous";
}

if ($treebank!=="sonar") {
   $components=implode( ', ' , array_keys($components)); // get string of components
}


/***********************************************************/
/* INCLUDES */
require("$navigation");
/***********************************************************/

// get input sentence
$sentence=$_SESSION['sentence'];

// get search mode
$sm=$_SESSION['search'];

// get XPath
$xpath=$_SESSION['xpath'];

if ($xpath) {
chop($xpath);

// log XPath
$log = fopen($grtllog, "a");  //concatenate
fwrite($log, "$date\t$user\t$id-$time\t$sm\t$treebank\tAUTOXP\t$xpath");
fclose($log);

  //log query tree
$qtree = file_get_contents("$tmp/$id-sub.xml");
$tlog = fopen($treelog, "a");  //concatenate
fwrite($tlog, "<alpino_ds id=\"$id-$time\">$qtree\n</alpino_ds>\n");
fclose($tlog);


//print input sentence and treebank
echo "$title";
echo "<b>Input example:</b> <i>$sentence</i><br/><br/>\n";

echo "<b>Treebank:</b> ".strtoupper($treebank)."<br/>\n";
echo "<b>Components:</b> ".strtoupper($components)."<br/><br/>\n";
echo '<div id="tree-output"></div>';
if ($_SESSION['search']=="advanced") { // print XPath expression in advanced search mode

  if ($treebank=="sonar") {
    echo '<br/><b>XPath expression </b>generated from the query tree.<br/>';
  }

  else {
    echo '<br/><b>XPath expression </b>generated from the query tree. You can adapt it if necessary. If you are dealing with a long query the <a href="http://bramvanroy.be/projects/xpath-beautifier" target="_blank">XPath beautifier</a> might come in handy. <br/>';
  }

  echo '<form action="'.$next.'" method="post">';
  if ($treebank == "sonar") {
    echo '<textarea rows="4" cols="100" name="xp" wrap="soft" readonly>'.$xpath.'</textarea><br/>';
}
  else {
    echo '<textarea rows="4" cols="100" name="xp" wrap="soft">'.$xpath.'</textarea>';
  }

  echo '<input type="reset" value="Reset XPath"/> ';
}
echo '
<form action="'.$next.'" method="post">
';

echo '<br/><br/>';

echo $new.$back.$continue;
echo '</form>';
}

?>
</div>
<script src="<?php echo $home; ?>/js/tree-visualizer.js"></script>
<script>
$(document).ready(function(){
    $('button[type="submit"]').click(function(){
        $(".loading").show();
    });
    <?php if ($_SESSION['search']=="advanced"): ?>
    $.treeVisualizer('<?php echo "$home/tmp/$id"; ?>-sub.xml', {
        container: "#tree-output",
        extendedPOS: true
    });
    <?php else: ?>
    $.treeVisualizer('<?php echo "$home/tmp/$id"; ?>-sub.xml', {
        container: "#tree-output"
    });
    <?php endif; ?>
});
</script>
</script>
</body>
</html>
