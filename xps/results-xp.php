<!DOCTYPE html>
<!-- results-xp.php -->
<!-- display treebank results -->

<!-- version 0.1 date: 10.11.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>
<?php
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
require'header-xp.php';

echo '
<link rel="stylesheet" type="text/css" href="'.$home.'/style/css/datatable.css"></link>
<script type="text/javascript" src="'.$home.'/js/jquery.dataTables.js" ></script>
<script type="text/javascript" src="'.$home.'/js/sorttable.js" ></script>
';
?>
<script>
// show/hide function
$(document).ready(function() {
    // Initialisation code dataTables
	$('#example').dataTable({
        "sScrollY": "400px",
        "bPaginate": false
    });

    // Show and hide required elements
    $(".slidingDiv").hide();
    $(".show_hide").show();
    $("#hide").hide(); // hide message 'hide'
    $("#show").show(); // show message 'show'

    $('.show_hide').click(function(){
        $(".slidingDiv").slideToggle();
        $("#show").toggle();
        $("#hide").toggle();
    });
  });
</script>

</head>

<body>
<div id="container">
<?php
session_cache_limiter('private'); // avoids page reload when going back
session_start();
header('Content-Type:text/html; charset=utf-8');

/***********************************************************/

 /* VARIABLES */
$id=session_id();
$date=date('d-m-Y');
$time=time(); // time stamp

if (getenv('REMOTE_ADDR')){
$user = getenv('REMOTE_ADDR');
}
else {
$user="anonymous";
}

// dir to to external scripts
$basexclient="$scripts/BaseXClient.php";
$treebanksearch="$scripts/TreebankSearch.php";
$formatresults="$scripts/FormatResults.php";

// log files
$oomlog="$log/oom.log";
$xplog="$log/gretel-xps.log";

// navigation and references to scripts
$start="input-xp.php";
$new='<button type="button" onclick="window.location.href=\''.$start.'?'.$time.'\'">New XPath Query</button>';
$back='<button type="button" value="Back" onclick="goBack()">Back</button>';
$step=3;
$title="<h1>Step 3: Results</h1><hr/>";

// messages and documentation
$export="$home/scripts/SaveResults.php?"; // script for downloading the results
$exportxp="$home/scripts/SaveXPath.php"; // script for downloading the XPath expression
$showtree="$home/scripts/ShowTree.php"; // script for displaying syntax trees
$tip="<b>Click on a sentence ID</b> to view the tree structure. The
sentence ID refers to the treebank component in which the sentence occurs, the text number, and the location within the text (page + sentence number).";
/***********************************************************/
/* INCLUDE SCRIPTS */

require("$navigation");
require("$basexclient");
require("$treebanksearch"); // functions to find treebank data in BaseX database and print them
require("$formatresults"); // functions to format the treebank results

/***********************************************************/

try {
  $start = microtime(true); // start timing

  // get XPath
  if (empty($_SESSION['xpath'])) {
    echo "<h1>XPath Error</h1><hr/>";
    echo "Please enter an XPath query\n<br/><br/>";
    echo $back;
    exit;
  }

  else {
    $xpath=$_SESSION['xpath'];
  }

  echo "$title";
  chop($xpath);

  // get treebank
  if ($_POST['treebank']) {
    $treebank=$_POST['treebank'];
    $_SESSION['treebank']="$treebank";
  }
  else {
    echo "<h1>Error</h1>";
    echo "Please select at least one treebank\n<br/><br/>";
    echo $back;
    exit;
  }

  // log XPath
  $log=fopen($xplog, "a"); //concatenate
  fwrite($log, "$date\t$user\t$id-$time\t$treebank\t$xpath\n");
  fclose($log);

  // get subtreebanks
  $subtb=$treebank.'tb';

  if ($_POST[$subtb]) {
    $components=$_POST[$subtb];
    $subtreebanks=array_keys($components);
    $subtbs=implode('-', $subtreebanks);
    $components=implode( ', ' , $subtreebanks); // get string of components
    $export .= "subtb=".$subtbs;
  }

  else {
    echo "<h1>Error</h1>";
    echo "Please select at least one subtreebank\n<br/><br/>";
    echo $back;
  exit;
  }

  // get context option
  if (isset($_POST["ct"])) {
    $context=1;
    $_SESSION["ct"]="on";
  }
  else {
    $context=0;
    $_SESSION["ct"]="off";
  }


  // create session
  $session = new Session($dbhost, $dbport, $dbuser, $dbpwd);


  // get results
  try {
    // get counts
    list($HITS,$MS,$TOTALS,$TOTALCOUNTS)=GetCounts($xpath,$treebank,$subtreebanks,$session);

    // get sentences
    if ($TOTALCOUNTS['hits'] > 20000 || $TOTALCOUNTS['ms'] > 5000 ) { // display subset if too many hits
      $limit=500;

      list($sentences,$counthits,$idlist,$beginlist)=GetSentences($xpath,$treebank,$subtreebanks,$session,$limit,$context);
    }

     elseif ($TOTALCOUNTS['hits'] !=0 ) {
      list($sentences,$counthits,$idlist,$beginlist)=GetSentences($xpath,$treebank,$subtreebanks,$session,"none",$context);
    }

    else {
      // do nothing
    }
    // print query

    echo
'<div align="right"><button type="button" value="Printer-friendly version" onclick="window.open(\''.$export.'&print=html\')">Printer-friendly version</button></div>';

    echo '
<table>
<tr>
<th colspan="2" align="left">QUERY</th>
</tr>
<tr>
<th  align="left">XPath</th><td>'.$xpath.' <button type="button" value="Download XPath" onclick="window.open(\''.$exportxp.'\')">Download XPath</button> <!-- [<a href="'.$exportxp.'">Download XPath</a>]--> <a href="#" class="clickMe" tooltip="You can save the query to use it as input for the XPath search mode. This allows you to use the same query for another (part of a) treebank or for a slightly modified search without having to start completely from scratch."><sup>[?]</sup></a></td>
</tr>
<tr>
<th  align="left">Treebank</th><td>'.strtoupper($treebank).' ['.$components.']</td>
</tr>
</table><br/>
';

    if ($TOTALCOUNTS['hits']==0) {
      print "<b>RESULTS: </b>NO MATCHES FOUND<br/><br/>\n";
    }

    else {
      // format counts
      list($HITS)=NumFormatHash($HITS);
      list($MS)=NumFormatHash($MS);
      list($TOTALS)=NumFormatHash($TOTALS);
      list($TOTALCOUNTS)=NumFormatHash($TOTALCOUNTS);

      echo '
<table>
<tr>
<th colspan="3" align="left">RESULTS</th>
</tr>
<tr>
<th align="left" width="200px">Hits</th><td>'.$TOTALCOUNTS['hits'].'</td>
<td><a href="#restable" class="show_hide" id="restable"><div id="show" class="showhide">Show hits distribution </div><div id="hide" class="showhide">Hide hits distribution</div> </a> </td>
</tr>
<tr>
<th align="left" width="200px">Matching sentences</th><td>'.$TOTALCOUNTS['ms'].'</td>
<td><button  onclick="window.open(\''.$export.'&print=txt\')" >Download</button> <a href="#" class="clickMe" tooltip="Tab-separated file of sentence IDs, matching sentences, and hits per sentence"><sup>[?]</sup></a> </td>
<!-- td><a href="'.$export.'&print=txt" >Download</a> <a href="#" class="clickMe" tooltip="Tab-separated file of sentence IDs, matching sentences, and hits per sentence"><sup>[?]</sup></a> </td -->
</tr>
<tr>
<th align="left" width="200px">Sentences in treebank</th><td>'.$TOTALCOUNTS['totals'].'</td><td></td>
</tr>
</table><br/>
';

    print '<div class="slidingDiv">';  // show/hide div pt 1
    printCounts($treebank,$HITS,$MS,$TOTALS,$TOTALCOUNTS); // print table hit distribution
    echo '<button onclick="window.open(\''.$export.'&print=csv\')" title="Comma-separated file of detailed search results (counts per treebank)">Download table</button><br/><br/>';

    print "</div>"; // show/hide div pt 2

    if (isset($limit)) {
      print "Since there are too many results to display, a sample of $limit hits per treebank component is presented.<br/><br/>\n";
      $export.="&limit=".$limit;
    }

    echo $tip;

    printMatches($sentences,$counthits,$idlist,$beginlist,$treebank,$showtree); // print matching sentence and hits per sentence
    }

    echo $new.$back;
    // close session
    $session->close();
  }

  catch (Exception $e) {
    // print $e->getMessage();
    $error=$e->getMessage();

    if (preg_match('/\[XPST0003\]/', $error)) { // catch error to return useful error message
      print "XPATH ERROR<br/>";
      print "$error<br/><br/>";
      echo $back;
    }

    elseif (preg_match('/\[XPTY0004\]/', $error)) { // catch error to return useful error message
      print "OUT OF MEMORY<br/>";
      echo $back;

      $oom = fopen($oomlog, "a");
      fwrite($oom, "$date\t$user\t$id-$time\t$xpath\t$error\n");
      fclose($oom);
    }

    else {
      print "ERROR<br/>";
      print "$error<br/>";
      echo $back;
    }
  }

}


catch (Exception $e) {
  // print exception
  print $e->getMessage();
}

?>
</div>
</body>
</html>
