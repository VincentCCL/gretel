<!DOCTYPE html>
<!-- input.php -->
<!-- form to get an input sentence and send it to another script -->

<!-- version 0.5 date: 14.10.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>
<?php
require 'header.php';
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
?>

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
</head>

<body>
<div id="container">
<?php
session_start();
header('Content-Type:text/html; charset=utf-8');

/************************************************************/
 /* VARIABLES */
$id=session_id();
$next="parse.php";
$input="Dit is een zin."; // default example
$step= 1; // for progress bar
$title="<h1>Step 1: Give an example</h1>
<hr/>";
$continue='<div style="float:right"><button type="Submit" value="Continue" class="colour">Continue</button></div>';

/***********************************************************/
/* INCLUDES */
require("$navigation");
/***********************************************************/

echo "$title";

echo '<form action="'.$next.'" method="post" enctype="multipart/form-data" >
<p> Enter a <strong>sentence</strong> containing the (syntactic) characteristics you are looking for:</p>';

if (isset($_SESSION['example'])) { // find previous input example
  $input=$_SESSION['example'];
}

echo '
<input type="text" name="input" size=50 value="'.$input.'" id="example" />
<button type="button" onClick="copyText();" id="clear" title="This button clears the input box above, allowing you to enter new input without having to delete the current input character by character">Clear</button>';

echo '
<br/><br/>
<p>Select the <b>search mode</b> you want to use:</p>
<input type="radio" name="search" value="basic" checked />Basic search  <a href="#" class="clickMe" tooltip="Without formal query language and less search options."> <sup>[?]</sup></a>
<br/>
<input type="radio" name="search" value="advanced" />Advanced search <a href="#" class="clickMe" tooltip="Allows a more specific search and has the possibility to adapt the automatically generated XPath query."> <sup>[?]</sup></a>
<br/><br/>
';

echo $continue;
?>
</div>
</body>
</html>
