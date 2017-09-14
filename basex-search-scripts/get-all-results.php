<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require "../config.php";
require ROOT_PATH."/functions.php";

require ROOT_PATH."/basex-search-scripts/basex-client.php";
require ROOT_PATH."/basex-search-scripts/treebank-search.php";

session_start();
set_time_limit(0);

if (!isset($_GET['sid'])) {
  $results = array(
    'error' => true,
    'data' => 'Session ID not provided. Perhaps you have disabled cookies. Please enable them.',
  );
  echo json_encode($results);
  exit;
}

define('SID', $_GET['sid']);

$corpus = $_SESSION[SID]['treebank'];
$components = $_SESSION[SID]['subtreebank'];
$componentsString = implode('-', $components);
$databaseString = $corpus;
$already = $databases = $_SESSION[SID]['startDatabases'];

if ($corpus == 'sonar') {
    $needRegularSonar = $_SESSION[SID]['needRegularSonar'];
}

$xpath = $_SESSION[SID]['xpath'];
$ebsxps = $_SESSION[SID]['ebsxps'];

if ($ebsxps == 'ebs') {
    $example = $_SESSION[SID]['example'];
    $xpChanged = $_SESSION[SID]['xpChanged'];
    $originalXp = $_SESSION[SID]['originalXp'];
}

// get context option
$context = $_SESSION[SID]['ct'];
$id = session_id();
session_write_close();

$date = date('d-m-Y');
$time = time();

$user = (getenv('REMOTE_ADDR')) ? getenv('REMOTE_ADDR') : 'anonymous';

if ($ebsxps == 'ebs') {
    $xplog = fopen(ROOT_PATH."/log/gretel-ebq.log", 'a');
    // fwrite($xplog, "Date\tIP.address\tUnique.ID\tInput.example\tTreebank\tComponent\tXPath.changed\tXPath.searched\tOriginal.xpath\n");
    fwrite($xplog, "$date\t$user\t$id-$time\t$example\t$corpus\t$componentsString\t$xpChanged\t$xpath\t$originalXp\n");
    fclose($xplog);
} else {
    $xplog = fopen(ROOT_PATH."/log/gretel-xps.log", 'a');
    fwrite($xplog, "$date\t$user\t$id-$time\t$corpus\t$componentsString\t$xpath\n");
    fclose($xplog);
}

try {
    if ($corpus == 'sonar') {
        $serverInfo = getServerInfo($corpus, $components[0]);
    } else {
        $serverInfo = getServerInfo($corpus, false);
    }

    $dbhost = $serverInfo{'machine'};
    $dbport = $serverInfo{'port'};
    $session = new Session($dbhost, $dbport, $dbuser, $dbpwd);

    list($sentences, $tblist, $idlist, $beginlist) = getSentences($databases, $already, 'all', $session, SID);

    $session->close();

    if (isset($sentences)) {
        // Write results to file so that they can be downloaded later on
        // If the file already exists, remove it and re-create it (just to be sure)
      
        // Create seperate HTML file for printing
        $dlFileName = ROOT_PATH."/tmp/".SID."-gretel-results-dl.txt";
        $printFileName = ROOT_PATH."/tmp/".SID."-gretel-results-print.html";

        if (file_exists($dlFileName)) {
            unlink($dlFileName);
        }

        if (file_exists($printFileName)) {
            unlink($printFileName);
        }

        // Download file
        $dlFh = fopen($dlFileName, 'a');
        fwrite($dlFh, "Corpus: $corpus\nComponents: $componentsString\n");
        if ($ebsxps == 'ebs') {
          fwrite($dlFh, "Input example: $example\n");
        }
        $timezone  = 1; //(GMT +1)
        $date = gmdate("j/m/Y H:i:s", time() + 3600*($timezone+date("I")));
        fwrite($dlFh, "XPath: $xpath\nDate: $date\n\n");

        // Print file
        $printFh = fopen($printFileName, 'a');
        $printHead = '<!DOCTYPE html><html lang="en"><meta content="IE=edge"http-equiv=X-UA-Compatible><title>GrETEL results</title><meta content="noindex, nofollow"name=robots><link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600"rel=stylesheet><style>body,html{width:100%;height:100%;margin:0;background-color:#fff;font-family:"Open Sans",sans-serif;font-size:12px;color:#000}strong{font-weight:600}h1{font-size:18px;font-weight:600}table{border-collapse:collapse;page-break-inside:auto}td{border:1px solid #000;padding:2px 6px}tr{page-break-inside:avoid;page-break-after:auto}</style></head><body><h1>Your GrETEL results</h1>';
        fwrite($printFh, $printHead);

        fwrite($printFh, "<ul><li>Corpus: $corpus</li><li>Components: $componentsString</li>");
        if ($ebsxps == 'ebs') {
          fwrite($printFh, "<li>Input example: $example</li>");
        }
        fwrite($printFh, "<li>XPath: $xpath</li><li>Date: $date</li></ul><table>");

        $counter = 0;
        foreach ($sentences as $sid => $sentence) {
            $counter++;
            // highlight sentence
            $hlsentence = highlightSentence($sentence, $beginlist[$sid], 'strong');

            $changeTags = array('<em>' => '', '</em>' => '', '<strong>' => '<hit>', '</strong>' => '</hit>');
            $hlsentenceDownload = strtr($hlsentence, $changeTags);

            $transformQuotes = array('"' => '&quot;', "'" => '&apos;');
            $hlsentence = strtr($hlsentence, $transformQuotes);

            if ($corpus == 'sonar') {
                // E.g. WRPEC0000019treebank
                $databaseString = $tblist[$sid];
            }

            // remove the added identifier (see GetSentences) to use in the link
            $sidString = strstr($sid, '-endPos=', true) ?: $sid;

            // subtreebank where the sentence was found:
            if ($corpus == 'lassy') {
                preg_match('/([^<>]+?)(?:-\d+(?:-|\.).*)/', $sidString, $componentsFromRegex);
                $componentsFromRegex = preg_replace('/^((?:[a-zA-Z]{3,4})|(?:WR(?:-[a-zA-Z]){3}))-.*/', '$1', $componentsFromRegex[1]);

                $componentsString = str_replace('-', '', $componentsFromRegex);
                $componentsString = substr($componentsString, 0, 4);
            } elseif ($corpus == 'cgn') {
                preg_match('/([^<>\d]+)/', $sidString, $componentsFromRegex);
                $componentsFromRegex = substr($componentsFromRegex[1], 1);

                $componentsString = str_replace('-', '', $componentsFromRegex);
            } elseif ($corpus == 'sonar') {
                preg_match('/^([a-zA-Z]{2}(?:-[a-zA-Z]){3})/', $sidString, $componentsFromRegex);
                $componentsString = str_replace('-', '', $componentsFromRegex[1]);
            }

            $componentsString = strtoupper($componentsString);

            // For Lassy and CGN tb and db are identical (i.e. lassy & lassy, or cgn & cgn).
            // For Sonar tb is sonar, and db something like WRPEC0000019treebank
            $sentenceidlink = '<a class="tv-show-fs" href="front-end-includes/show-tree.php'
            . '?sid='.$sidString
            . '&tb='.$corpus
            . '&db='.$databaseString
            . '&id='.$idlist[$sid]
            . '" target="_blank">'.$sidString.'</a>';

            $resultsArray{$sid} = array($sentenceidlink, $hlsentence, $componentsString);
            fwrite($dlFh, "$corpus\t$componentsString\t$hlsentenceDownload\n");
            fwrite($printFh, "<tr><td>$counter</td><td>$sidString</td><td>$componentsString</td><td>$hlsentence</td></tr>");
        }
        fwrite($printFh, '</table></body></html>');
        fclose($dlFh);
        fclose($printFh);

        $results = array(
          'error' => false,
          'data' => $resultsArray,
        );
        // Chrome will keep all cookies and flush 'em at the end which in turn
        // will results in a HEADERS_TOO_BIG error -> unset cookies
        header_remove('Set-Cookie');
        echo json_encode($results);
    } else {
        $results = array(
          'error' => false,
          'data' => '',
        );
        header_remove('Set-Cookie');
        echo json_encode($results);
    }
} catch (Exception $e) {
    $results = array(
      'error' => true,
      'data' => $e->getMessage(),
    );
    header_remove('Set-Cookie');
    echo json_encode($results);
}
