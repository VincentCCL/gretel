<?php
session_cache_limiter('private');
session_start();
header('Content-Type:text/html; charset=utf-8');

$currentPage = 'xps';
$step = 3;

require "../config.php";
require ROOT_PATH."/helpers.php";

$noTbFlag = 0;

$continueConstraints = isset($_POST['sid']);

if ($continueConstraints) {
  define('SID', $_POST['sid']);
  $_SESSION[SID]['ebsxps'] = $currentPage;
  if (isset($_POST['treebank'])) {
      $corpus = $_POST['treebank'];
      $_SESSION[SID]['treebank'] = $corpus;
  } elseif (isset($_SESSION[SID]['treebank'])) {
      $corpus = $_SESSION[SID]['treebank'];
  } else {
      $noTbFlag = 1;
      $corpus = '';
  }

  if (isset($_POST['subtreebank'])) {
      $components = $_POST['subtreebank'];
      $_SESSION[SID]['subtreebank'] = $components;
  } elseif (isset($_SESSION[SID]['subtreebank'])) {
      $components = $_SESSION[SID]['subtreebank'];
  } else {
      $noTbFlag = 1;
  }

  $continueConstraints = !$noTbFlag && sessionVariablesSet(SID, array('treebank', 'subtreebank', 'xpath'));
}

if ($continueConstraints) {
    require ROOT_PATH."/preparatory-scripts/prep-functions.php";
    $treeVisualizer = true;
    $onlyFullscreenTv = true;

    $xpath = $_SESSION[SID]['xpath'];
    // Need to clean in case the user goes back in history, otherwise the
    // prepended slashes below would keep stacking on each back-and-forward
    // in history
    $xpath = cleanXpath($xpath);

    $_SESSION[SID]['ct'] = isset($_POST['ct']) ? true : false;
    $_SESSION[SID]['endPosIteration'] = 0;
    $_SESSION[SID]['startDatabases'] = array();

    if ($corpus == 'sonar') {
        $databaseExists = false;
    }

    $needRegularSonar = false;
}

session_write_close();

require ROOT_PATH."/functions.php";
require ROOT_PATH."/front-end-includes/head.php";

if ($continueConstraints) {
  require ROOT_PATH."/basex-search-scripts/treebank-search.php";
  require ROOT_PATH."/basex-search-scripts/basex-client.php";
  session_start();
  if ($corpus == 'sonar') {
    $bf = xpathToBreadthFirst($xpath);
    // Get correct databases to start search with, sets to
    // $_SESSION[SID]['startDatabases']
    checkBfPattern($bf, SID);

    // When looking in the regular version we need the double slash to go through
    // all descendants
    if ($needRegularSonar) {
      $xpath = "//$xpath";
    } else {
      $xpath = "/$xpath";
    }
  } else {
    $xpath = "//$xpath";
    $_SESSION[SID]['startDatabases'] = corpusToDatabase($components, $corpus);
  }

  // When flushing we update the databases on each iteration, not so in counting
  // or when fetching all results
  $_SESSION[SID]['flushAlready'] = $_SESSION[SID]['flushDatabases'] = $_SESSION[SID]['startDatabases'];
  $_SESSION[SID]['xpath'] = $xpath;
  $_SESSION[SID]['needRegularSonar'] = $needRegularSonar;
  session_write_close();
}
?>
</head>
<?php flush(); ?>
<?php
require ROOT_PATH."/front-end-includes/header.php";

if ($continueConstraints):
    require ROOT_PATH."/front-end-includes/results-shared-content.php";
    setContinueNavigation();
else: // $continueConstraints
    setErrorHeading();
    ?>
    <p>You did not select a treebank, or something went wrong when determining the XPath for your request. It is also
        possible that you came to this page directly without first entering an input example.</p>
    <?php
    setPreviousPageMessage(2);

endif;
require ROOT_PATH."/front-end-includes/footer.php";
include ROOT_PATH."/front-end-includes/analytics-tracking.php";

if ($continueConstraints):
  ?>
    <?php include ROOT_PATH."/front-end-includes/notifications.php"; ?>
    <?php // Variables for JS
    $jsVars = array(
        'fetchResultsPath' => HOME_PATH."/basex-search-scripts/flush-results.php?sid=".SID,
        'getAllResultsPath' => HOME_PATH."/basex-search-scripts/get-all-results.php?sid=".SID,
        'fetchCountsPath' => HOME_PATH."/basex-search-scripts/get-counts.php?sid=".SID,
        'downloadPath' => HOME_PATH."/tmp/".SID."-gretel-results.txt",
        'resultsLimit' => $resultsLimit,
        'fetchHomePath' => HOME_PATH,
        'sid' => SID,
    );
    ?>
    <script>
        var phpVars = <?php echo json_encode($jsVars); ?>;
    </script>
    <script src="js/min/results.min.js"></script>
<?php endif; ?>
</body>
</html>
