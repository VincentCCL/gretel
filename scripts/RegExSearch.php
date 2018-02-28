<?php

function GetResults($string,$case,$treebank,$subtreebanks,$session,$contextflag) {

  $database=Corpus2DB($subtreebanks,$treebank); // rename corpora to database names

// convert string to Postgres format

  //$string= preg_replace("/'/", "\'",$string);
  //$string= preg_replace('/"/','\"',$string);
  
  $string= preg_replace("/'/",'&apos;',$string);
  $string= preg_replace('/"/','&quot;',$string);
  $string= preg_replace('/\\\\s/',' ',$string);
  $string= preg_replace("/\\\\/i",'\\\\\\\\',$string); // change backslash into double backslash
  $string= preg_replace('/\\\\b/i','\\\\y',$string); 

// count matching sentences
  foreach ($database as $db) {

  if ($case == "CI") {
    $sqlcnt = "select count(*) from $db where sentence ~* '$string';";
  }
  else {
    $sqlcnt = "select count(*) from $db where sentence ~ '$string';";
  }

  $cntms = pg_query($session, $sqlcnt);
  $countms = pg_fetch_row($cntms);
  $MS{$db}=$countms[0];
}

// count sentences in subtreebank

foreach ($database as $db) {
  $sqltotal = "select count(*) from $db;"; 
  $cnttotal = pg_query($session, $sqltotal);
  $counttotal = pg_fetch_row($cnttotal);
  $TOTAL{$db}= $counttotal[0];
}

// get matches


  foreach ($database as $db) {
if ($case == "CI") {
    $sqlmatch = "select file, sentence from $db where sentence ~* '$string';";
  }
  else {
    $sqlmatch = "select file, sentence from $db where sentence ~ '$string';";
  }

$results = pg_query($session, $sqlmatch);
  if (!$results) {
    echo "An error occured.\n";
    exit;
  }

// make a hash of all matching sentences, count hits per sentence and append matching IDs per sentence

  $i=0; // set counter

while ($row = pg_fetch_row($results)) {
    if ($row[1] != "{}") {
      $sentid=$row[0];
      $sentence=$row[1];
      $convertedstring=preg_replace('/\\\\\\\\y/i','\\\\\\\\b',$string); //change word boundary symbol
      $convertedstring= preg_replace("/\\\\\\\\/i",'\\\\',$convertedstring); // change double backslash into single backslash
    if (preg_match("/($convertedstring)/i", $sentence)) {
      preg_match_all("/($convertedstring)/i", $sentence, $hits);

    $chits=count($hits[0]); // count hits in sentence

    if ($contextflag == "1") {
      $context=GetContext($sentid,$db,$treebank,$session);
      $prev=array_shift($context);
      $next=array_shift($context);
      $sentences{$sentid}=$prev.' <i>'.$sentence.'</i> '.$next; // put sentences + context in a hash
    }

  else {
    $sentences{$sentid}=$sentence; // put sentences in a hash
  }

$counthits{$sentid}=$chits; // put hits per sentence in a hash

$i=$i+$chits; // add hit count to total


  // make a list of matching tokens

  // $words=preg_replace('/(\\(.*?\\))|(\\[.*?\\]))/g',' ',$string);
  $words=$string;
  $words=preg_replace('/\\(.*?\\)/i',' ',$words); //remove (.*?)
  $words=preg_replace('/\\[.*?\\]/i',' ',$words); //remove [.*?]
  $words=preg_replace('/\\\\./i',' ',$words); //remove \special characters
  $words=preg_replace('/\\./i',' ',$words); //remove \special characters
  $words=preg_replace('/\\y/i',' ',$words); //remove word boundary
  $words=preg_replace('/[\\?\\*\\!\\+\\.]/i','',$words); //remove .?*+
  $words=preg_replace('/ /i','-',$words);

  $wordlist{$sentid}=$words;
  }
}     

} // end while

$HITS{$db}=$i;

} // end foreach


// total counts
  
  foreach ($database as $db) {      
    $TOTALS{$db}=$TOTAL{$db}; // store total sentences of selected databases
  }
      
  $TOTALCOUNTS{'hits'}=array_sum($HITS); // count total matches
  $TOTALCOUNTS{'ms'}=array_sum($MS); // count total matching sentences 
  $TOTALCOUNTS{'totals'}=array_sum($TOTALS); // count total sentences in corpus
  
  return array($HITS,$MS,$TOTALS,$TOTALCOUNTS,$sentences,$counthits,$wordlist);

  }


function Corpus2DB($tblist,$treebank) {
  // rename corpora to database names
  $databases=array();

  if ($treebank) {
    foreach ($tblist as $tb) {
      array_push($databases, "$tb");
    }
    return $databases;
  }

}

function SetDB2Corpus($corpus) {
  if ($corpus == 'lassy') {
  $CORPUS['dpc']='DPC';
  $CORPUS['wiki']='Wikipedia';
  $CORPUS['wrpe']='WR-P-E';
  $CORPUS['wrpp']='WR-P-P';
  $CORPUS['wsu']='WS-U';
  } 

  elseif ($corpus == 'cgn') {
    $CORPUS['na']="NA";
    $CORPUS['nb']="NB";
    $CORPUS['nc']="NC";
    $CORPUS['ne']="NE";
    $CORPUS['nf']="NF";
    $CORPUS['ng']="NG";
    $CORPUS['nh']="NH";
    $CORPUS['ni']="NI";
    $CORPUS['nj']="NJ";
    $CORPUS['nk']="NK";
    $CORPUS['nl']="NL";
    $CORPUS['nm']="NM";
    $CORPUS['nn']="NN";
    $CORPUS['va']="VA";
    $CORPUS['vb']="VB";
    $CORPUS['vc']="VC";
    $CORPUS['vd']="VD";
    $CORPUS['vf']="VF";
    $CORPUS['vg']="VG";
    $CORPUS['vh']="VH";
    $CORPUS['vi']="VI";
    $CORPUS['vj']="VJ";
    $CORPUS['vk']="VK";
    $CORPUS['vl']="VL";
    $CORPUS['vm']="VM";
    $CORPUS['vn']="VN";
    $CORPUS['vo']="VO";
  }
  
  else {
    return ("ERROR!");
  }

  return ($CORPUS);
}


function SetDB2CorpusDetailed($corpus) {
  if ($corpus == 'lassy') {
  $CORPUS['dpc']='<a href="#" class="hovertip">DPC<span>Dutch Parallel Corpus</span></a>';
  $CORPUS['wiki']='<a href="#" class="hovertip">Wikipedia<span>Dutch Wikipedia pages</span></a>';
  $CORPUS['wrpe']='<a href="#" class="hovertip">WR-P-E<span>E-magazines, newsletters, teletext pages, web sites, Wikipedia</span></a>';
  $CORPUS['wrpp']='<a href="#" class="hovertip">WR-P-P<span>Books, brochures, guides and manuals, legal texts, newspapers, periodicals and magazines, policy documents, proceedings, reports, surveys</span></a>';
  $CORPUS['wsu']='<a href="#" class="hovertip">WS-U<span>Auto cues, news scripts, texts for the visually impaired</span></a>';
  } 

  elseif ($corpus == 'cgn') {
    $CORPUS['na']='<a href="#" class="hovertip">NA<span>Spontaneous conversations (\'face-to-face\')</span></a>';
    $CORPUS['nb']='<a href="#" class="hovertip">NB<span>Interviews with teachers of Dutch</span></a>';
    $CORPUS['nc']='<a href="#" class="hovertip">NC<span>Telephone conversations (recorded via a switchboard)</span></a>';
    $CORPUS['ne']='<a href="#" class="hovertip">NE<span>Simulated business negotiations</span></a>';
    $CORPUS['nf']='<a href="#" class="hovertip">NF<span>Interviews/discussions/debates (broadcast)</span></a>';
    $CORPUS['ng']='<a href="#" class="hovertip">NG<span>(Political) discussions/debates/meetings (non-broadcast)</span></a>';
    $CORPUS['nh']='<a href="#" class="hovertip">NH<span>Lessons recorded in the classroom</span></a>';
    $CORPUS['ni']='<a href="#" class="hovertip">NI<span>Live (sports) commentaries (broadcast)</span></a>';
    $CORPUS['nj']='<a href="#" class="hovertip">NJ<span>Newsreports (broadcast)</span></a>';
    $CORPUS['nk']='<a href="#" class="hovertip">NK<span>News (broadcast)</span></a>';
    $CORPUS['nl']='<a href="#" class="hovertip">NL<span>Commentaries/columns/reviews (broadcast)</span></a>';
    $CORPUS['nm']='<a href="#" class="hovertip">NM<span>Ceremonious speeches/sermons</span></a>';
    $CORPUS['nn']='<a href="#" class="hovertip">NN<span>Lectures/seminars</span></a>';
    $CORPUS['va']='<a href="#" class="hovertip">VA<span>Spontaneous conversations (\'face-to-face\')</span></a>';
    $CORPUS['vb']='<a href="#" class="hovertip">VB<span>Interviews with teachers of Dutch</span></a>';
    $CORPUS['vc']='<a href="#" class="hovertip">VC<span>Telephone conversations (recorded via a switchboard)</span></a>';
    $CORPUS['vd']='<a href="#" class="hovertip">VD<span>Telephone conversations (recorded on MD)</span></a>';
    $CORPUS['vf']='<a href="#" class="hovertip">VF<span>Interviews/discussions/debates (broadcast)</span></a>';
    $CORPUS['vg']='<a href="#" class="hovertip">VG<span>(Political) discussions/debates/meetings (non-broadcast)</span></a>';
    $CORPUS['vh']='<a href="#" class="hovertip">VH<span>Lessons recorded in the classroom</span></a>';
    $CORPUS['vi']='<a href="#" class="hovertip">VI<span>Live (sports) commentaries (broadcast)</span></a>';
    $CORPUS['vj']='<a href="#" class="hovertip">VJ<span>Newsreports (broadcast)</span></a>';
    $CORPUS['vk']='<a href="#" class="hovertip">VK<span>News (broadcast)</span></a>';
    $CORPUS['vl']='<a href="#" class="hovertip">VL<span>Commentaries/columns/reviews (broadcast)</span></a>';
    $CORPUS['vm']='<a href="#" class="hovertip">VM<span>Ceremonious speeches/sermons</span></a>';
    $CORPUS['vn']='<a href="#" class="hovertip">VN<span>Lectures/seminars</span></a>';
    $CORPUS['vo']='<a href="#" class="hovertip">VO<span>Read speech</span></a>';
  }
  
  else {
    return ("ERROR!");
  }
  
  return ($CORPUS);
}

function GetContext($sentid,$subtb,$treebank,$session) {


  if ($treebank=='cgn') {
  preg_match('/(f..\d+)\_\_(\d+)/',$sentid, $match); 
  }
  
  else {
  preg_match('/(.*?s\.)(\d+)/',$sentid, $match);
  }

  $text=$match[1];
  $snr=$match[2];
  $prev=$snr-1;
  $next=$snr+1;

  if ($treebank == 'cgn') {
  $previd=$text.'__'.$prev;
  $nextid=$text.'__'.$next;
  }

  else {
  $previd=$text.$prev;
  $nextid=$text.$next; 
  }
  $prevsql = "select sentence from $subtb where file = '$previd';";
  $nextsql = "select sentence from $subtb where file = '$nextid';";
  $prevresult = pg_query($session, $prevsql);
  $nextresult = pg_query($session, $nextsql);

  $nextcontext = pg_fetch_row($nextresult);
  $prevcontext = pg_fetch_row($prevresult);

  $context=array();
  array_push($context, "$prevcontext[0]");
  array_push($context, "$nextcontext[0]");
  return $context;
}

?>
