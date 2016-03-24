<?php
function NumFormatHash($hash) {
  foreach ($hash as $key => $value) {
    $NUMFOR{$key}=number_format($value);
  }
  return array($NUMFOR);
}

function printCounts($treebank,$HITS,$MS,$TOTALS,$TOTALCOUNTS) { // print table with results
  
  $CORPUS=SetDB2CorpusDetailed($treebank);
   print "<table id=\"results\" border=\"1\">
             <thead>
              <tr><th>TREEBANK</th><th>HITS</th><th>MATCHING SENTENCE</th><th>SENTENCES IN TREEBANK</th></tr>
             </thead>
             ";
  foreach ($HITS as $dbname => $h) {
    print "<tr><td>$CORPUS[$dbname]</td><td>$h<td>$MS[$dbname]</td><td>$TOTALS[$dbname]</td></tr>\n";
  }
  print "<tr><td><b>TOTAL</b></td><td><b>$TOTALCOUNTS[hits]</b><td><b>$TOTALCOUNTS[ms]</b></td><td><b>$TOTALCOUNTS[totals]</b></td></tr>\n"; 
  print "</table>\n<br/>\n";
}

function printCountsCsv($treebank,$HITS,$MS,$TOTALS,$TOTALCOUNTS) { // print table with results
  
  $CORPUS=SetDB2Corpus($treebank);
  
  print "treebank,hits,matching-sentences,sentences-in-treebank\n"; 
  
  foreach ($HITS as $dbname => $h) {
    print "$CORPUS[$dbname],$h,$MS[$dbname],$TOTALS[$dbname]\n";
  }
  print "TOTAL,$TOTALCOUNTS[hits],$TOTALCOUNTS[ms],$TOTALCOUNTS[totals]\n"; 

}

function printCountsPF($treebank,$HITS,$MS,$TOTALS,$TOTALCOUNTS) { // print table with results
  
  $CORPUS=SetDB2Corpus($treebank);
  print "<table id=\"results\" border=\"1\">
             <thead>
              <tr><th>TREEBANK</th><th>HITS</th><th>MATCHING SENTENCE</th><th>SENTENCES IN TREEBANK</th></tr>
             </thead>
             ";
  foreach ($HITS as $dbname => $h) {
    print "<tr><td>$CORPUS[$dbname]</td><td>$h<td>$MS[$dbname]</td><td>$TOTALS[$dbname]</td></tr>\n";
  }
  print "<tr><td><b>TOTAL</b></td><td><b>$TOTALCOUNTS[hits]</b><td><b>$TOTALCOUNTS[ms]</b></td><td><b>$TOTALCOUNTS[totals]</b></td></tr>\n"; 
  print "</table>\n<br/>\n";
}

function printMatches($sentences,$counthits,$idlist,$beginlist,$treebank,$showtree) { // NOTE: sentence IDs are the keys of all hashes
     print '<div class="tableWrapper"><table id="example" class="sortable" border="1">
             <thead>
<tr><th class="pointer">SENTENCE ID</th><th class="pointer">MATCHING SENTENCES</th><th class="pointer">HITS</th></tr>
             </thead>
             <tfoot>
             <tr><th colspan="3"></th></tr>
             </tfoot>
             ';
      
      print '           
             <tbody>
             ';
 
      foreach ($sentences as $id => $sentence) { // print matching sentence and hits per sentence
	$sid=trim($id);
	$hlsentence=HighlightSentence($sentence,$beginlist[$id]); // highlight sentence
  
	$trans = array('"' => '&quot;', "'" => "&apos;"); // deal with quotes/apos
	$hls=strtr("$hlsentence", $trans);

	$sentenceidlink='<a class="match" href="'.$showtree.'?sid='.$sid.'&id='.$idlist[$id].'&tb='.$treebank.'&db='.$treebank.'&opt=xml" target="_blank" >'.$sid.'</a>';

	print '<tr><td width="150px">'.$sentenceidlink.'</td><td>'.$hlsentence.'</td><td width="100px" >'.$counthits[$id].'</td></tr>'."\n";
      }
      print "</tbody></table></div>\n<br/><br/>\n";
      
}

function printMatchesTxt($sentences,$counthits,$idlist,$beginlist) { // NOTE: sentence IDs are the keys of all hashes
      
      foreach ($sentences as $id => $sentence) { // print matching sentence and hits per sentence
	print "$id\t$sentence\t[$counthits[$id]]\n";
      }

}

function printMatchesPF($sentences,$counthits,$idlist,$beginlist) { // NOTE: sentence IDs are the keys of all hashes
      print '<table id="matches" border="1">
             <thead>
              <tr><th>SENTENCE ID</th><th>MATCHING SENTENCE</th><th>HITS</th></tr>
             </thead>
             <tfoot>
              <tr><th>SENTENCE ID</th><th>MATCHING SENTENCE</th><th>HITS</th></tr>
             </tfoot>
             <tbody>
             ';
      
      foreach ($sentences as $id => $sentence) { // print matching sentence and hits per sentence
	$hlsentence=HighlightSentence($sentence,$beginlist[$id]); // highlight sentence

	$trans = array('"' => '&quot;', "'" => "&apos;"); // deal with quotes/apos
	$hls=strtr("$hlsentence", $trans);

	print '<tr><td>'.$id.'</td><td>'.$hlsentence.'</td><td>'.$counthits[$id].'</td></tr>';
      }
      print "</tbody></table>\n<br/><br/>\n";

}


function HighlightSentence($sentence,$beginlist) {
  if (preg_match('/<i>/', $sentence)) {
    $s=preg_replace("/(.*<i>)(.*?)(<\/i>.*)/", "$2", $sentence);
    $prev=preg_replace("/(.*<i>)(.*?)(<\/i>.*)/", "$1", $sentence);
    $next=preg_replace("/(.*<i>)(.*?)(<\/i>.*)/", "$3", $sentence);
  }
  else {
    $s=$sentence;
  }
  $words=explode(' ', $s);
  $begins=explode('-',$beginlist);
  foreach ($begins as $begin) {
    $words[$begin]="<b>$words[$begin]</b>";
  }	
  $hlsentence= implode(' ', $words);
  if (isset($prev)||isset($next)) {
    $hlsentence=$prev.' '.$hlsentence.' '.$next;
  }
  return $hlsentence;
}
?>
