<?php

$currentPage = 'xps';
$step = 2;

require "../config.php";
require ROOT_PATH."/helpers.php";
require ROOT_PATH."/preparatory-scripts/prep-functions.php";

session_cache_limiter('private');
session_start();
header('Content-Type:text/html; charset=utf-8');

$continueConstraints = isset($_POST['sid']) && (isset($_POST['xpath']) || isset($_SESSION[$_POST['sid']]['xpath']));
$isSpam = false;

if ($continueConstraints) {
  define('SID', $_POST['sid']);
  $xpath = (isset($_POST['xpath'])) ? $_POST['xpath'] : $_SESSION[SID]['xpath'];
  $isSpam = isSpam($xpath);
  if (!$isSpam) {
    $xpath = htmlspecialchars($xpath, ENT_COMPAT | ENT_HTML5);
    $_SESSION[SID]['xpath'] = $xpath;
  }
}

require ROOT_PATH."/functions.php";
require ROOT_PATH."/front-end-includes/head.php";
?>
<link rel="prefetch" href="js/min/results.min.js">
</head>
<?php flush(); ?>
<?php
require ROOT_PATH."/front-end-includes/header.php";

if ($continueConstraints && !$isSpam) {
    require ROOT_PATH."/front-end-includes/tb-sel-shared-content.php"; ?>
    <input type="hidden" name="sid" value="<?php echo SID; ?>">
    <?php setContinueNavigation(); ?>
  </form>

<?php } // $continueConstraints
else {
    if ($isSpam):
        setErrorHeading("Spam detected"); ?>
        <p>Your input example contained a hyperlink or email address and is seen as spam. Therefore we will not allow you to continue. </p>
    <?php else:
        setErrorHeading(); ?>
        <p>No search instruction could be generated, since you did not enter a valid XPath query.
            It is also possible that you came to this page directly without first entering a query.</p>
    <?php
    endif;

    setPreviousPageMessage($step-1);
}
?>

<?php
require ROOT_PATH."/front-end-includes/footer.php";
include ROOT_PATH."/front-end-includes/analytics-tracking.php";
?>
</body>
</html>
