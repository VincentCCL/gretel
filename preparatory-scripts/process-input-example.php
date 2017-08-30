<?php
session_start();

require "../config.php";
require ROOT_PATH."/helpers.php";
require ROOT_PATH."/functions.php";
require ROOT_PATH."/preparatory-scripts/prep-functions.php";

if (!isset($_GET['sid'])) {
  $results = array(
    'error' => true,
    'data' => 'Session ID not provided. Perhaps you have disabled cookies. Please enable them.',
  );
  echo json_encode($results);
  exit;
}

define('SID', $_GET['sid']);

$lpxml = simplexml_load_file(ROOT_PATH."/tmp/".SID."-pt.xml");

// Set tokenized input sentence to variable
$tokinput = $_SESSION[SID]['sentence'];
$sentence = explode(' ', $tokinput);
  // add info annotation matrix to alpino parse
foreach ($sentence as $begin => $word) {
    $postword = preg_replace('/\./', '_', $word);

    if (preg_match("/([_<>\.,\?!\(\)\"\'])|(\&quot;)|(\&apos;)/", $word)) {
        $xp = $lpxml->xpath("//node[@begin='$begin']");
    } else {
        $xp = $lpxml->xpath("//node[@word='$word' and @begin='$begin']");
    }

    if (isset($_POST["$postword--$begin"])) {
        $postvalue = $_POST["$postword--$begin"];
        foreach ($xp as $x) {
            $x->addAttribute('interesting', $postvalue);
            if ($postvalue == 'token' && !isset($_POST["$postword--$begin-case"])) {
              $x->addAttribute('caseinsensitive', 'yes');
            }
        }
    }
}
// save parse with @interesting annotations
$treefileName = ROOT_PATH."/tmp/".SID."-int.xml";
if (file_exists($treefileName)) {
    unlink($treefileName);
}
$tree = $lpxml->asXML();

$treeFh = fopen($treefileName, 'w');
fwrite($treeFh, "$tree\n");
fclose($treeFh);

// Remove top category?
if (isset($_POST['topcat'])) {
    $remove = 'relcat';
} else {
    $remove = 'rel';
}

$subTreefileName = ROOT_PATH."/tmp/".SID."-sub.xml";
$pathToRoot = ROOT_PATH;
`perl -CS $pathToRoot/preparatory-scripts/get-subtree.pl $treefileName $remove > $subTreefileName`;

if (isset($_POST['order'])) {
    $order = 'true';
    $_SESSION[SID]['order'] = 'on';
} else {
    $order = 'false';
}

// generate XPath from sentence
$xpath = `perl -CS $pathToRoot/preparatory-scripts/xpath-generator.pl $subTreefileName $order`;
$xpath = preg_replace('/@cat="\s+"/', '@cat', $xpath);

// Apply case (in)sensitivity where necessary
$xpath = applyCs($xpath);
$_SESSION[SID]['xpath'] = $xpath;
session_write_close();

$results = array(
  'location' => "tmp/".SID."-sub.xml",
  'xpath' => $xpath,
);

echo json_encode($results);
