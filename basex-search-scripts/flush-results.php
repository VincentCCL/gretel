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
$queryIteration = $_SESSION[SID]['endPosIteration'];

$corpus = $_SESSION[SID]['treebank'];
$components = $_SESSION[SID]['subtreebank'];
$databases = $_SESSION[SID]['flushDatabases'];
$already = $_SESSION[SID]['flushAlready'];
if ($corpus == 'sonar') {
    $needRegularSonar = $_SESSION[SID]['needRegularSonar'];
}

$databaseString = $corpus;
$xpath = $_SESSION[SID]['xpath'];

// get context option
$context = $_SESSION[SID]['ct'];

session_write_close();

try {
    if ($corpus == 'sonar') {
        $serverInfo = getServerInfo($corpus, $components[0]);
    } else {
        $serverInfo = getServerInfo($corpus, false);
    }

    $dbhost = $serverInfo{'machine'};
    $dbport = $serverInfo{'port'};
    $session = new Session($dbhost, $dbport, $dbuser, $dbpwd);

    list($sentences, $tblist, $idlist, $beginlist) = getSentences($databases, $already, $queryIteration, $session, SID);
    $session->close();

    if (isset($sentences)) {
        foreach ($sentences as $sid => $sentence) {
            // highlight sentence
            $hlsentence = highlightSentence($sentence, $beginlist[$sid], 'strong');
            // deal with quotes/apos
            $trans = array('"' => '&quot;', "'" => '&apos;');
            $hlsentence = strtr($hlsentence, $trans);

            if ($corpus == 'sonar') {
                $databaseString = $tblist[$sid];
            }

            // remove the added identifier (see getSentences) to use in the link
            $sidString = strstr($sid, '-endPos=', true) ?: $sid;

            // subtreebank where the sentence was found:
            if ($corpus == 'lassy') {
                preg_match('/([^<>]+?)(?:-\d+(?:-|\.).*)/', $sidString, $componentFromRegex);
                $componentFromRegex = preg_replace('/^((?:[a-zA-Z]{3,4})|(?:WR(?:-[a-zA-Z]){3}))-.*/', '$1', $componentFromRegex[1]);

                $componentString = str_replace('-', '', $componentFromRegex);
                $componentString = substr($componentString, 0, 4);
            } elseif ($corpus == 'cgn') {
                preg_match('/([^<>\d]+)/', $sidString, $componentFromRegex);
                $componentFromRegex = substr($componentFromRegex[1], 1);

                $componentString = str_replace('-', '', $componentFromRegex);
            } else {
                preg_match('/^([a-zA-Z]{2}(?:-[a-zA-Z]){3})/', $sidString, $componentFromRegex);
                $componentString = str_replace('-', '', $componentFromRegex[1]);
            }

            $componentString = strtoupper($componentString);

            $sentenceidlink = '<a class="tv-show-fs" href="front-end-includes/show-tree.php'
            . '?sid='.$sidString
            . '&tb='.$corpus
            . '&db='.$databaseString
            . '&id='.$idlist[$sid]
            . '" target="_blank">'.$sidString.'</a>';

            $resultsArray{$sid} = array($sentenceidlink, $hlsentence, $componentString);
        }
        $results = array(
          'error' => false,
          'data' => $resultsArray,
        );
        echo json_encode($results);
    } else {
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
