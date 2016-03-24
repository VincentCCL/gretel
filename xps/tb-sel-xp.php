<!DOCTYPE html>
<!-- tb-sel.php -->
<!-- script for treebank selection -->

<!-- version 0.2 date: 12.12.2014  page loader added -->
<!-- version 0.1 date: 10.11.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>

<?php
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require 'header-xp.php';?>

<style type="text/css">
.box{
 padding: 20px;
 display: none;
 margin-top: 20px;
 }
fieldset {
 border: none;
}
</style>

<script>
$(document).ready(function() {
    $('.checkall').click(function () {
	var checked = $(this).prop("checked");
        $("input[name^='lassytb']").prop("checked", checked);
    });
    $('.checkvlfunction').click(function () {
	var checked = $(this).prop("checked");
        $("input[name^='cgntb[v']").prop("checked", checked);
    });
    $('.checknlfunction').click(function () {
	var checked = $(this).prop("checked");
        $("input[name^='cgntb[n']").prop("checked", checked);
    });

    $('input[name="treebank"]').click(function(){
        var val = $(this).val();
        $(".box").hide();
        $("."+val).show();
    });
    $("#cgn").click();

     $('button[type="submit"]').click(function(){
         $(".loading").show();
     });
});
</script>
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

if (getenv('REMOTE_ADDR')){
$user = getenv('REMOTE_ADDR');
}
else {
$user="anonymous";
}

// dir to treebank tables
$lassytable="$scripts/lassy-tb.html";
$cgntable="$scripts/cgn-tb.html";

// log files
$grtllog="$log/gretel-xps.log";

// navigation
$start="input-xp.php?time()";
$next="results-xp.php";
$new='<button type="button" onclick="window.location.href=\''.$start.'?'.$time.'\'">New XPath query</button>';
$back='<button type="button" value="Back" onclick="goBack()">Back</button>';
$continue='<div style="float:right"><button type="Submit" value="Continue" class="colour">Continue</button></div>';
$step=2; // for progress bar
$title="<h1>Step 2: Select a treebank</h1><hr/>";

$info='<p>You can search an entire treebank (default), or select just one or more components.</p>';
$info2='<p>Due to pre-processing difficulties some sentences could not be included in the system, so the sentence and word counts may slightly differ from the official treebank counts.</p>';
$captcha="<p>This is a suspicious input example. GrETEL does not accept URL's or HTML as input.</p>";

/***********************************************************/
/* INCLUDES */
require("$navigation");
/***********************************************************/

// get XPath
if (is_uploaded_file($_FILES['xpfile']['tmp_name'])) { // check whether a file is submitted; check input text field if not
  if (($_FILES["xpfile"]["error"]) > 0) {
    echo "<h1>XPath Error</h1><hr/>";
    echo "Error:".$_FILES["xpfile"]["error"]."<br/>";
    echo $back;
    exit;
  }
  elseif ($_FILES["xpfile"]["size"] > 20000) {
    echo "<h1>XPath Error</h1><hr/>";
    echo "Error: The file you are trying to upload is too large.<br/>";
    echo $back;
    exit;
  }
  else {
    $file=$_FILES["xpfile"]["tmp_name"];
    $xpath=file_get_contents($file);
  }
}

else {
  if (isset($_POST["xpinput"]) && $_POST["xpinput"]!="") {
    $xpath=$_POST['xpinput']; // get xpath
  }

  else {
    echo "<h1>XPath Error</h1><hr/>";
    echo "Please enter or upload an XPath query\n<br/><br/>";
    echo $back;
    exit;
  }
}

$_SESSION['search']="xpsearch"; // set search mode
chop($xpath);
$xpath = preg_replace("/[\\n\\r]+/", " ", $xpath); // remove newlines and tabs
$trans = array("='" => '="', "'\s" => '"\s', "']" => '"]' ); // deal with quotes/apos
$xpath=strtr("$xpath", $trans);

if (preg_match('/(http:\/\/.*)|(<\W+>)/', $xpath)==1) { // check for spam
  echo "<h1>XPath Error</h1><hr/>";
  echo $captcha."\n<br/><br/>";
  echo $back;
  exit;
}

else {
  echo "$title";
  $_SESSION['xpath']=$xpath;
}

// print treebank table
echo 'Which <b>treebank</b> do you want to query? Click on the treebank name to see its different <b>components</b>.<br/><br/>';
echo '<form action="'.$next.'" method="post" >';
echo '<div>
        <label><input type="radio" name="treebank" value="cgn" id="cgn" >CGN</label><br/>
        <label><input type="radio" name="treebank" value="lassy" id="lassy"  checked>LASSY</label><br/>
        </div>';

echo "$info";
echo "$info2";
echo '<div class="cgn box">';
require("$cgntable");
echo '</div>';
echo '<div class="lassy box">';
require("$lassytable");
echo '</div>';

// include context
echo '<p><b>OPTION</b></p>
  <input type="checkbox" name="ct" value="on" />Include context (one sentence before and after the matching sentence)';
echo '<br/><br/>';

echo $new.$continue;

echo '</form>';

?>
</div>
</body>
</html>
