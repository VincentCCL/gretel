<!DOCTYPE html>
<!-- matrix.php -->
<!-- script returns the parsed input construction in a matrix -->

<!-- version 0.7 date: 12.12.2014  bug fix -->
<!-- version 0.6 date: 14.10.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>
<?php
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require 'header.php';?>

<style>
.matrix {
 border: solid 1px black;
}
</style>
</head>

<body>
<div id="container">
<?php
session_cache_limiter('private'); // avoids page reload when going back
session_start();
header('Content-Type:text/html; charset=utf-8');

/***********************************************************/

 /* VARIABLES */
$id= session_id();
$date=date('d-m-Y');
$time=time(); // time stamp
$sm=$_SESSION['search'];

if (getenv('REMOTE_ADDR')){
$user = getenv('REMOTE_ADDR');
}
else {
$user="anonymous";
}

// log files
$grtllog="$log/gretel-ebq.log";

// navigation
$start="input.php";
$next="tb-sel.php";
$new='<button type="button" onclick="window.location.href=\''.$start.'?'.$time.'\'">New Input Example</button>';
$back='<button type="button" value="Back" onclick="goBack()">Back</button>';
$continue='<div style="float:right"><button type="Submit" value="Continue" class="colour">Continue</button></div>';
$step= 3; // for progress bar
$title="<h1>Step 3: Select relevant parts</h1>
<hr/>";

// messages
$captcha="This is a suspicious input example. GrETEL does not accept URL's or HTML as input.";

// LINK TO INPUT PARSE
$inputzoom=$home."/tmp/$id-style-zoom.xml?$time";

/***********************************************************/
/* INFO + OPTIONS*/
$info='<p>Indicate the relevant<a href="#" class="clickMe" tooltip="In the matrix below, the elements of the sentence you entered are divided in obligatory ones and optional ones. The latter do not need to be represented in the search results.<br/> The obligatory elements have to be included in the results, be it as an element of the same word class, any form of a specific lemma, or a specific word form."><sup>[?]</sup></a>  parts of the sentence, i.e. the parts you are interested in. [<a href="'.$inputzoom.'" target="_blank">view input parse</a>]</p>';

$basicguidelines='<p><b>GUIDELINES</b></p>
<ul>
<li><b>word</b>: The exact word form. This is a case sensitive feature.</li>
<li><b>lemma</b>: Word form that generalizes over inflected forms. For example: <i>zin</i> is the lemma of <i>zin, zinnen</i>, and <i>zinnetje</i>; <i>gaan</i> is the lemma of <i>ga, gaat, gaan, ging, gingen</i>, and <i>gegaan</i>. Lemma is case insensitive (except for proper names).</li>
<li><b>word class</b>: Short Dutch part-of-speech tag. The different tags are: <tt>n</tt> (noun), <tt>ww</tt> (verb), <tt>adj</tt> (adjective), <tt>lid</tt> (article), <tt>vnw</tt> (pronoun), <tt>vg</tt> (conjunction), <tt>bw</tt> (adverb), <tt>tw</tt> (numeral), <tt>vz</tt> (preposition), <tt>tsw</tt> (interjection), <tt>spec</tt> (special token)</tt>, and <tt>let</tt> (punctuation).</li>
<li><b>optional in search</b>: The word will be ignored in the search instruction. It may be included in the results, but it is not necessary.</li>
</ul>';

$advancedguidelines='<p><b>GUIDELINES</b></p>
<ul>
<li><b>word</b>: The exact word form. This is a case sensitive feature.</li>
<li><b>lemma</b>: Word form that generalizes over inflected forms. For example: <i>zin</i> is the lemma of <i>zin, zinnen</i>, and <i>zinnetje</i>; <i>gaan</i> is the lemma of <i>ga, gaat, gaan, ging, gingen</i>, and <i>gegaan</i>. Lemma is case insensitive (except for proper names).</li>
<li><b>detailed word class</b>: Long part-of-speech tag. For example: <tt>N(soort,mv,basis), WW(pv,tgw,ev),  VNW(pers,pron,nomin,vol,2v,ev)</tt>.</li>
<li><b>word class</b>: Short Dutch part-of-speech tag. The different tags are: <tt>n</tt> (noun), <tt>ww</tt> (verb), <tt>adj</tt> (adjective), <tt>lid</tt> (article), <tt>vnw</tt> (pronoun), <tt>vg</tt> (conjunction), <tt>bw</tt> (adverb), <tt>tw</tt> (numeral), <tt>vz</tt> (preposition), <tt>tsw</tt> (interjection), <tt>spec</tt> (special token)</tt>, and <tt>let</tt> (punctuation).</li>
<li><b>optional in search</b>: The word will be ignored in the search instruction. It may be included in the results, but it is not necessary.</li>
<li><b>NOT in search</b>: The word class and the dependency relation will be excluded from the search instruction. They will not be included in the results.</li>
</ul>';

$options='<p><b>OPTIONS</b></p>
<input type="checkbox" name="order" value="on" />Respect word order
<br/>
<input type="checkbox" name="topcat" value="on" />Ignore properties of the dominating node <a href="#" class="clickMe" tooltip="Clicking this option allows you to search for more general patterns, e.g. search for both main clauses and subclauses"><sup>[?]</sup></a>
<br/>
<br/>';


/***********************************************************/
/* INCLUDES */
require("$navigation");
/***********************************************************/
// get input example

if (isset($_SESSION['example'])) { // find previous input example
  $input=$_SESSION['example'];
}

if(!isset($input) || !isset($_SESSION['example'])) {
  echo "<h3>Error</h3>";
  echo "GrETEL was unable to retrieve the input example. \n<br/><br/>";
  echo $back;
  exit;
}

//GET INPUT
$tokinput = $_SESSION['sentence'];

// INFO
echo "$title";

echo '<form action="'.$next.'" method="post">'."\n";
echo "$info\n";

// DRAW MATRIX
$sentence = explode(" ", $tokinput);
echo "<table class=\"matrix\">";
echo "<tr bgcolor=\"#DCDCDC\"><th>sentence</th>";
foreach ($sentence as $key=>$word) {
  echo "<td>" . $word . "</td>";
}

echo "</tr>\n<tr><th>word</th>";
foreach ($sentence as $key=>$word) {
  $word=preg_replace('/\"/','&quot;' , $word); // deal with quotes
  $word=preg_replace('/\'/','&apos;' , $word); // deal with apostrophes
  echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"token\"/></td>";
}

echo "</tr>\n<tr><th>lemma</th>";
foreach ($sentence as $key=>$word) {
  $word=preg_replace('/\"/','&quot;' , $word); // deal with quotes
  $word=preg_replace('/\'/','&apos;' , $word); // deal with apostrophes
  echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"lemma\"/></td>";
}

if ($_SESSION['search']=="advanced") { // print detailed pos in advanced search mode

  echo "</tr>\n<tr><th>detailed word class</th>";
  foreach ($sentence as $key=>$word) {
    $word=preg_replace('/\"/','&quot;' , $word); // deal with quotes
    $word=preg_replace('/\'/','&apos;' , $word); // deal with apostrophes
    echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"postag\"/></td>";
  }
}

echo "</tr>\n<tr><th>word class</th>";
foreach ($sentence as $key=>$word) {
  $word=preg_replace('/\"/','&quot;' , $word); // deal with quotes
  $word=preg_replace('/\'/','&apos;' , $word); // deal with apostrophes

  // do not check punctuation marks

 if (preg_match("/[\.\,\?\!\:\]\[\(\)\-]/", $word) )  {
    echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"pos\"/></td>";
  }
  else {
    echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"pos\" checked /></td>";
  }

}

echo "</tr>\n<tr bgcolor=\"#DCDCDC\"><th>optional in search</th>";
foreach ($sentence as $key=>$word) {
  $word=preg_replace('/\"/','&quot;' , $word); // deal with quotes
  $word=preg_replace('/\'/','&apos;' , $word); // deal with apostrophes

 // check punctuation marks
  if (preg_match("/[\.\,\?\!\:\]\[\(\)\-]/", $word))  {
    echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"na\" checked /></td>";
  }
  else {
    echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"na\" /></td>";
  }
}

if ($_SESSION['search']=="advanced") { // print 'not' in advanced search mode

  echo "</tr>\n<tr bgcolor=\"#A9A9A9\" ><th><font color=\"red\">NOT in search</font></th>";
  foreach ($sentence as $key=>$word) {
    $word=preg_replace('/\"/','&quot;' , $word); // deal with quotes
    $word=preg_replace('/\'/','&apos;' , $word); // deal with apostrophes
    echo "<td><input type=\"radio\" name=\"$word--$key\" value=\"not\"/></td>";
  }
}

echo "</tr>";
echo "</table><br/>";

if ($_SESSION['search']=="advanced") {
  echo $options;
  echo $advancedguidelines;
}

else {
  echo $options;
  echo $basicguidelines;
}

echo "<br/>";

echo $new.$back.$continue;
echo "</form>";
?>
</div>
</body>
</html>
