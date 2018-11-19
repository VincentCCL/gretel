<?php
session_cache_limiter('private');
session_start();
header('Content-Type:text/html; charset=utf-8');

$currentPage = 'ebs';
$step = 5;

require "../config.php";
require ROOT_PATH."/helpers.php";

$noTbFlag = 0;

$continueConstraints = isset($_POST['sid']);

if ($continueConstraints) {
    define('SID', $_POST['sid']);
    if (isset($_POST['treebank'])) {
        $corpus = $_POST['treebank'];
        $_SESSION[SID]['treebank'] = $corpus;
    } elseif (isset($_SESSION[SID]['treebank'])) {
        $corpus = $_SESSION[SID]['treebank'];
    } else {
        $noTbFlag = true;
        $corpus = '';
    }
  
    if (!$noTbFlag) {
        $hasComponents = $databaseGroups[$corpus]['hasComponents'];
        if ($hasComponents) {
            if (isset($_POST['subtreebank'])) {
                $components = $_POST['subtreebank'];
                $_SESSION[SID]['subtreebank'] = $components;
            } elseif (isset($_SESSION[SID]['subtreebank'])) {
                $components = $_SESSION[SID]['subtreebank'];
            } else {
                $noTbFlag = true;
            }
        } else {
            $components = array();
            $_SESSION[SID]['subtreebank'] = $components;
        }
    }
    
    $continueConstraints = !$noTbFlag && sessionVariablesSet(SID, array('sentence', 'treebank', 'xpath'));
}

require ROOT_PATH."/functions.php";

if ($continueConstraints) {
    require ROOT_PATH."/preparatory-scripts/prep-functions.php";

    $queryId = $_SESSION[SID]['queryid'];
    $treeVisualizer = true;

    $tokinput = $_SESSION[SID]['sentence'];

    $xpath = $_SESSION[SID]['xpath'];
    $originalXp = $_SESSION[SID]['originalXp'];

    $xpath = cleanXpath($xpath);
    $originalXp = cleanXpath($originalXp);

    // Check if the XPath was edited by the user or not
    $xpChanged = ($xpath == $originalXp) ? false : true;

    $_SESSION[SID]['xpath'] = $xpath;
    $_SESSION[SID]['originalXp'] = $originalXp;
    $_SESSION[SID]['xpChanged'] = $xpChanged;

    // Temporarily change XPath to show it to user
    if ($databaseGroups[$corpus]['isGrinded']) {
      $xpath = "/$xpath";
    } else {
      $xpath = "//$xpath";
    }
}

session_write_close();
require ROOT_PATH."/front-end-includes/head.php";
?>

<link rel="prefetch" href="js/min/results.min.js">
</head>
<?php flush(); ?>
<?php require ROOT_PATH."/front-end-includes/header.php"; ?>
<?php if ($continueConstraints):
  $component = implode(', ', $component);

  // Log query tree
  $qTree = file_get_contents(ROOT_PATH."/tmp/".SID."-sub.xml");
  $treeLog = fopen(ROOT_PATH."/log/gretel-querytrees.log", 'a');
  fwrite($treeLog, "<alpino_ds id=\"$queryId\">\n$qTree\n</alpino_ds>\n");
  fclose($treeLog);
?>

<h3>Input</h3>
<ul>
<li>Input example: <em><?php echo $tokinput; ?></em></li>
<li>Treebank: <?php echo strtoupper($corpus); ?></li>
<?php if ($databaseGroups[$corpus]['hasComponents']): ?>
<li>Components: <?php echo strtoupper($component); ?></li>
<?php endif; ?>
</ul>

<?php if (!$xpChanged): ?>
  <h3>Query tree</h3>
<div id="tree-output">
    <?php include ROOT_PATH."/front-end-includes/tv-wrappers.php"; ?>
</div>
<?php endif; ?>

  <h3>XPath expression<?php if (!$xpChanged): ?> generated from the query tree<?php endif; ?></h3>
  <div class="generated-xpath"><code><?php echo $xpath; ?></code></div>
    <form action="ebs/results.php" method="post">
    <input type="hidden" name="sid" value="<?php echo SID; ?>">
    <?php setContinueNavigation(); ?>
    </form>
<?php else: // $continueConstraints
    setErrorHeading();
?>
    <p>You did not select a treebank, or something went wrong when determining the XPath for your request. It is also
    possible that you came to this page directly without first entering an input example.</p>

<?php
    setPreviousPageMessage(4);
endif;  // $continueConstraints
require ROOT_PATH."/front-end-includes/footer.php";
include ROOT_PATH."/front-end-includes/analytics-tracking.php";

if ($continueConstraints && !$xpChanged) : ?>
    <script src="js/tree-visualizer.js"></script>
    <script>
    $(function(){
      $("#tree-output").treeVisualizer('<?php echo "tmp/".SID."-sub.xml" ?>', {extendedPOS: true});
    });
    </script>
<?php endif; ?>
</body>
</html>
