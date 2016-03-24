<!DOCTYPE html>
<!-- input-xp.php -->
<!-- form to get an XPath expression -->

<!-- version 0.1 date: 10.11.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>
<?php
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require 'header-xp.php';?>

</head>

<body>
<div id="container">
<?php
session_start();
header('Content-Type:text/html; charset=utf-8');

/************************************************************/
 /* VARIABLES */
$id=session_id();
$next="tb-sel-xp.php";
$inputxp='//node[@rel="su" and @cat="np" and node[@pt="n" and @ntype="eigen"]]'; // default example
$step=1; // for progress bar
$title="<h1>Step 1: Give an XPath expression</h1>
<hr/>";
$continue='<div style="float:right"><button type="Submit" value="Continue" class="colour">Continue</button></div>';

/***********************************************************/
/* INCLUDES */
require("$navigation");
/***********************************************************/

echo "$title";

echo '<form action="'.$next.'" method="post" enctype="multipart/form-data" >
<p> Enter an <b>XPath expression</b> :</p>';

if (isset($_SESSION['xpath'])) { // find previous input example
  $inputxp=$_SESSION['xpath'];
}

  echo '
<textarea rows="5" cols="100" name="xpinput" wrap="soft" id="xpath">'.$inputxp.'</textarea><button type="button" onClick="copyText();" id="clear" title="This button clears the input box above, allowing you to enter a new query without having to delete the current input character by character">Clear</button><br/><p><b>OR</b> upload a file containing an XPath expression</p><input type="file" name="xpfile"/><br/><br/>';

echo $continue;
?>
</div>

<script>
// clear function
function copyText() {
    $("#example").val($("#clear").val());
}

// back function
function goBack() {
    window.history.back()
}
</script>
</body>
</html>
