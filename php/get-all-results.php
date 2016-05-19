<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../config/config.php';

session_start();
header('Content-Type:text/html; charset=utf-8');

$treebank = $_SESSION['treebank'];
$component = $_SESSION['subtreebank'];
$componentString = implode('-', $component);

if ($treebank == 'sonar') {
    $includes = $_SESSION['includes'];
    $bf = $_SESSION['bf'];
}

$databaseString = $treebank;

$xpath = $_SESSION['xpath'];
if ($_SESSION['ebsxps'] == 'ebs') {
    $sm = $_SESSION['search'];
    $example = $_SESSION['example'];
    if ($sm == "advanced" && $treebank != "sonar") {
        $xpChanged = $_SESSION['xpChanged'];
        $originalXp = $_SESSION['originalXp'];
    }
}

// get context option
$context = isset($_SESSION['ct']) ? true : false;
if ($treebank == 'sonar') $context = false;

$id = session_id();
$date = date('d-m-Y');
$time = time();

$user = (getenv('REMOTE_ADDR')) ? getenv('REMOTE_ADDR') : 'anonymous';

session_write_close();

if ($_SESSION['ebsxps'] == 'ebs') {
    $xplog = fopen("$log/gretel-ebq.log", 'a');
    if ($sm == "advanced" && $treebank != "sonar") {
      // fwrite($xplog, "Date\tIP.address\tUnique.ID\tInput.example\tSearch.mode\tTreebank\tComponent\tXPath.changed\tXPath.searched\tOriginal.xpath\n");
        fwrite($xplog, "$date\t$user\t$id-$time\t$example\t$sm\t$treebank\t$componentString\t$xpChanged\t$xpath\t$originalXp\n");
    }
    else {
        // fwrite($xplog, "Date\tIP.address\tUnique.ID\tInput.example\tSearch.mode\tTreebank\tComponent\tXPath.searched\n");
        fwrite($xplog, "$date\t$user\t$id-$time\t$example\t$sm\t$treebank\t$componentString\t$xpath\n");
    }
    fclose($xplog);
}
else {
    $xplog = fopen("$log/gretel-xps.log", 'a');
    fwrite($xplog, "$date\t$user\t$id-$time\t$treebank\t$componentString\t$xpath\n");
    fclose($xplog);
}

require "$scripts/BaseXClient.php";
require "$scripts/TreebankSearch.php";
require "$scripts/FormatResults.php";


  try {
      if ($treebank == 'sonar') {
          $dbhost = $dbnameServerSonar[$component[0]];
          $session = new Session($dbhost, $dbportSonar, $dbuserSonar, $dbpwdSonar);
          list($sentences, $tblist, $idlist, $beginlist) = GetSentencesSonar($xpath, $treebank, $component, $includes, $context, array(0 , 'all'), $session);
      }
      else {
          $session = new Session($dbhost, $dbport, $dbuser, $dbpwd);
          list($sentences, $idlist, $beginlist) = GetSentences($xpath, $treebank, $component, $context, array(0 , 'all'), $session);
      }
      $session->close();

    if (isset($sentences)) {
      array_filter($sentences);
      // Write results to file so that they can be downloaded later on
      // Only do this if the file does not already exist (e.g. when user refreshes)
      $fileName = "$tmp/${id}gretel-results.txt";
      if (file_exists($fileName)) {
          unlink($fileName);
      }

      $fh = fopen($fileName, 'a');
      fwrite($fh, "$xpath\n");

      foreach ($sentences as $sid => $sentence) {
          // highlight sentence
          $hlsentence = HighlightSentence($sentence, $beginlist[$sid], 'strong');
          $hlsentenceDownload = HighlightSentence($sentence, $beginlist[$sid], 'hit');
          // deal with quotes/apos
          $transformQuotes = array('"' => '&quot;', "'" => "&apos;");
          $hlsentence = strtr($hlsentence, $transformQuotes);

          // In the file to save the <em>-tags are not necessary
        $removeEm = array('<em>' => '', '</em>' => '');
        $hlsentenceDownload = strtr($hlsentenceDownload, $removeEm);

          if ($treebank == 'sonar') $databaseString = $tblist[$sid];

          // remove the added identifier (see GetSentences) to use in the link
          $sidString = strstr($sid, '-dbIter=', true) ?: $sid;

          $sentenceidlink = '<a class="tv-show-fs" href="'.$home. '/scripts/ShowTree.php'.
            '?sid='.$sidString.
            '&tb='.$treebank.
            '&db='.$databaseString.
            '&id='.$idlist[$sid].
            '&opt=tv-xml" target="_blank">'.$sidString.'</a>';

          $resultsArray{$sid} = array($sentenceidlink, $hlsentence);

          fwrite($fh, "$treebank\t$componentString\t$hlsentenceDownload\n");
      }
      fclose($fh);

        $results = array(
          'error' => false,
          'data' => $resultsArray,
        );
        echo json_encode($results);
      }
      else {
        $results = array(
          'error' => false,
          'data' => '',
        );
        echo json_encode($results);
      }
  } catch (Exception $e) {
    $results = array(
      'error' => true,
      'data' => $e->getMessage(),
    );
    echo json_encode($results);
  }