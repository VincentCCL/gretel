<!DOCTYPE html>
<!-- results.php -->
<!-- display treebank results -->

<!-- version 0.5 date: 15.10.2014  RELEASED WITH GrETEL2.0 -->
<!-- written by Liesbeth Augustinus (c) 2014 -->
<!-- for the GrETEL2.0 project -->

<head>
<?php
/* Display errors*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

include 'header.php';
echo '
<link rel="stylesheet" type="text/css" href="'.$home.'/style/css/datatable.css"></link>
<script type="text/javascript" src="'.$home.'/js/jquery.dataTables.js" ></script>
<script type="text/javascript" src="'.$home.'/js/sorttable.js" ></script>
';
?>

<link rel="stylesheet" href="<?php echo $home; ?>/style/css/tree-visualizer.css">
<script>
$(document).ready(function() {
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
$treebank=$_SESSION['treebank']; // get treebank
$sm=$_SESSION['search'];

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
$xplog="$log/gretel-ebq.log";

// navigation and references to scripts
$start="input.php";
$new='<button type="button" onclick="window.location.href=\''.$start.'?'.$time.'\'">New Input Example</button>';
$back='<button type="button" value="Back" onclick="goBack()">Back</button>';
$step=6;
$title="<h1>Step 6: Results</h1><hr/>";

// messages and documentation
$span="<span id=\"span_ID\">no sentence selected</span>";
$current="<div><p><b>CURRENT SENTENCE: </b>".$span."</p></div>";
$export="$home/scripts/SaveResults.php?"; // script for downloading the results
$exportxp="$home/scripts/SaveXPath.php"; // script for downloading the XPath expression
$showtree="$home/scripts/ShowTree.php"; // script for displaying syntax trees
$captcha="This is a suspicious input example. GrETEL does not accept URL's or HTML as input.";
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

  // get input example
  $exid=$_SESSION['sentid']; // get example identifier
  $example=$_SESSION['example'];

  // get search mode
  $sm=$_SESSION['search'];

  // get XPath
if ($_SESSION['search']=="basic") {
  $xpath=$_SESSION['xpath'];
}

else {
  if (isset($_POST["xp"]) && $_POST["xp"]!="") {
    $xpath=$_POST["xp"];
    $_SESSION['xpath']=$xpath;
  }

  else {
    echo "<h1>XPath Error</h1><hr/>";
    echo "Please enter an XPath query\n<br/><br/>";
    echo $back;
    exit;
  }
}

  echo "$title";
  chop($xpath);
  $xpath = preg_replace("/[\\n\\r]+/", " ", $xpath); // remove newlines and tabs
  $trans = array("='" => '="', "'\s" => '"\s', "']" => '"]' ); // deal with quotes/apos
  $xpath=strtr("$xpath", $trans);

  if (preg_match('/(http:\/\/.*)|(<\W+>)/', $xpath)==1) { // check for spam
  echo "<h3>Error</h3>";
  echo $captcha."\n<br/><br/>";
  echo $back;
  exit;
  }

  // log XPath
  $xplog=fopen($xplog, "a"); //concatenate
  fwrite($xplog, "$date\t$user\t$id-$time\t$sm\t$treebank\tUSERXP\t$xpath\n");
  fclose($xplog);

  // get context option
  if ($_SESSION["ct"]=="on") {
    $context=1;
  }
  else {
    $context=0;
  }

  // get subtreebanks
  if (empty($_SESSION['subtb'])) {
    echo "<h1>Error</h1>";
    echo "Please select a treebank\n<br/><br/>";
    echo $back;
    exit;
  }

  elseif (is_array($_SESSION['subtb'])) {
    $subtreebanks=array_keys($_SESSION['subtb']);
    $subtb=implode('-', $subtreebanks);
    $components=implode( ', ' , $subtreebanks); // get string of components
    $export .= "subtb=".$subtb;
  }
  else {
    $subtb=$_SESSION['subtb'];
  }

   /* FOR SMALL TREEBANKS */
  if ($treebank=='lassy' || $treebank=='cgn') {

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

    elseif ($TOTALCOUNTS['hits'] !=0 ){
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
<th align="left" width="200px">Input example</th><td>'.$example.'<td/>
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

  /* FOR SONAR */
  elseif ( $treebank=='sonar') {
    $subtreebank=$_SESSION['subtb'];

    // print query
    echo '
<table>
<tr>
<th colspan="2" align="left">QUERY</th>
</tr>
<tr>
<th align="left" width="200px">Input example</th><td>'.$example.'<td/>
</tr>
<tr>
<th  align="left">XPath</th><td>'.$xpath.' <button type="button" value="Download XPath" onclick="window.open(\''.$exportxp.'\')">Download XPath</button> <!-- [<a href="'.$exportxp.'">Download XPath</a>]--> <a href="#" class="clickMe" tooltip="You can save the query to use it as input for the XPath search mode. This allows you to use the same query for another (part of a) treebank or for a slightly modified search without having to start completely from scratch."><sup>[?]</sup></a></td>
</tr>
<tr>
<th  align="left">Treebank</th><td>'.strtoupper($treebank).' ['.$subtreebank.']</td>
</tr>
</table><br/>
';

    try {
      $xpath = preg_replace("/^\//", "", $xpath); // remove first slash
      // XPath2BreathFirst
      $bf= `perl $scripts/Alpino2BF.pl "$tmp/$id-sub-style.xml"`;
      $basexdb= $subtreebank.$bf;

      flush();
      ob_flush();

      // Query SoNaR
      $limit=100;
      $endtime = microtime(true) + $limit;
      $cmd = "perl $scripts/QuerySonar.pl '".$xpath."' $basexdb";

      $pipes = array();
      $descriptorspec = array(
	0 => array("pipe", "r"), // stdin is a pipe that the child will read from
        1 => array("pipe", "w"), // stdout is a pipe that the child will write to
        2 => array("pipe", "w"), // stderr is a file to write to
    );
      $process = proc_open($cmd, $descriptorspec, $pipes) or die("Can't open process $cmd!");

      echo '<p><b>RESULTS</b></p>';
      echo '<p id="prog"><b>Search status: </b>Processing...    <button type="button" onclick="window.stop();">Stop searching</button></p>';
      echo '<p id="hits"><b>Hits: </b>Still counting...</p>';
      echo '<p id="sample"></p>';
      echo $tip;

     // print matching sentences sample
      // begin table
      print '<div class="tableWrapper"><table id="example" class="sortable" border="1">
             <thead>
<tr><th class="pointer">SENTENCE ID</th><th class="pointer">MATCHING SENTENCES</th><th class="pointer">HITS</th></tr>
             </thead>
             <tfoot>
             <tr><th colspan="3"></th></tr>
             </tfoot><tbody>
             ';

      $range = range(0, 100);
      foreach ($range as $times) {
	if (ob_get_level() == 0) {
	  ob_start();
	  flush();
	  $match = fgets($pipes[2]);
	  if (!empty($match)) {
	    $match = str_replace("\n", "", $match);
	    if (preg_match('/SAMPLE/',$match))   {
	      $sample=preg_split("/\t/", $match);
	      // end table
	      echo "</tbody></table></div>\n<br/><br/>\n";
	      echo "<script>document.getElementById('sample').innerHTML ='The corpus sample displayed below contains ".$sample[1]." hits in ".$sample[2]." matching sentences <!-- (".$sample[3]." hit(s) per sentence on average) -->';</script>";
	    }
	    else {
	      $match=preg_split("/\t/", $match);
	      $sid=$match[0];
	      $matchsent=$match[1];
	      $counthits=$match[2];
	      $db=$match[3];
	      $idlist=$match[4];
	      $beginlist=$match[5];

	      $hlsentence=HighlightSentence($matchsent,$beginlist);
	      $sentenceidlink='<a class="match" href="'.$showtree.'?sid='.$sid.'&tb='.$treebank.'&db='.$db.'&id='.$idlist.'&opt=xml" target="_blank" >'.$sid.'</a>';
	      echo "<tr><td>".$sentenceidlink."</td><td>".$hlsentence."</td><td width=\"100px\" >".$counthits."</td></tr>\n";
	    }
	  }
          else {echo "</tbody></table></div>";}
	}
	ob_flush();
	flush();
	ob_end_flush();
      }

   // print number of hits
   do {
     $t=microtime(true);

     if (ob_get_level() == 0) ob_start();

     if (($output = fgets($pipes[1])) !== false) {

       $output = str_replace("\n", "", $output);
       ob_flush();
       flush();

      if ($t < $endtime && $output != "__END__") {
	ob_flush();
	flush();
	echo "<script>document.getElementById('hits').innerHTML ='<b>Hits: </b>".$output."';</script>";
	ob_flush();
	flush();
	ob_end_flush();
       }

      elseif ($t > $endtime && $output != '__END__') {
	echo "<script>document.getElementById('prog').innerHTML ='<b>Search status: </b>processing stopped';</script>";
	ob_flush();
	flush();
	ob_end_flush();

	echo "<script>document.getElementById('hits').innerHTML ='<b>Hits: </b>".$output." counted so far';</script>";
	fclose($pipes[0]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	proc_terminate($process); // terminate the process and continue with other tasks
	session_write_close();
      }

       elseif ($output == "__END__") {
	 echo "<script>document.getElementById('prog').innerHTML ='<b>Search status: </b>finished!';</script>";
	 ob_flush();
	 flush();
	 break; // exit do-while loop before end time
       }

       else {
	 echo "<script>document.getElementById('prog').innerHTML ='ERROR';</script>";
	 echo "TIME: $t<br/>";
	 ob_flush();
	 flush();
       }
     }

   } while  ($t <= $endtime);

   proc_close($process);

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

  else {
    print "ERROR<br/>";
    echo "An unknown treebank was selected.<br/>";
    echo $back;
  }

}

catch (Exception $e) {
  // print exception
  print $e->getMessage();
}

?>
</div>
<script src="<?php echo $home; ?>/js/min/tree-visualizer.min.js"></script>
<script>
$(document).ready(function(){
    $('a[href$="xml"]').click(function(e) {
        $("#tree-visualizer, #tree-visualizer-fs").remove();
        $.treeVisualizer($(this).attr("href"), {
            normalView: false,
            initFSOnClick: true
        });
        e.preventDefault();
    });
});
</script>
</body>
</html>
