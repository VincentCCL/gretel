<?php
/**
 * EBS STEP 2: Displays a parse of the input sentence.
 *
 *
 *
 * @author Liesbeth Augustinus
 * @author Bram Vanroy
 *
 * @see /functions.php  buildEbsMatrix(), isSpam()
 */
session_cache_limiter('private');

session_start();
header('Content-Type:text/html; charset=utf-8');

$currentPage = 'ebs';
$step = 2;

require "../config.php";
require ROOT_PATH."/helpers.php";
require ROOT_PATH."/preparatory-scripts/prep-functions.php";
require ROOT_PATH."/functions.php";
require ROOT_PATH."/preparatory-scripts/alpino-parser.php";
require ROOT_PATH."/preparatory-scripts/simple-dom.php";

$continueConstraints = postVariablesSet(array('sid', 'input'));
$isSpam = false;

if ($continueConstraints) {
    $treeVisualizer = true;
    define('SID', $_POST['sid']);
    $input = $_POST['input'];
    // Check if $input contains email addresses or website URLs
    $isSpam = isSpam($input);

    if (!$isSpam) {
      $_SESSION[SID]['example'] = $input;

      // Prepare/clean up input to be tokenized in next step
      $tokinput = tokenize($input);
      $_SESSION[SID]['sentence'] = $tokinput;
      $parse = alpino($tokinput, SID);
      modifyLemma($parse, SID);
    }
}

require ROOT_PATH."/front-end-includes/head.php";
?>
</head>
<?php flush(); ?>
<?php require ROOT_PATH."/front-end-includes/header.php"; ?>
<?php if ($continueConstraints && !$isSpam) { ?>

  <p>You find the structure of the POS-tagged and parsed sentence below.
    POS-tagging refers to the annotation of <em>word classes</em>, such as <code>n</code> (noun),
    and parsing refers to the annotation of <em>dependency relations</em>,
     such as <code>su</code> (subject), and <em>constituents</em>,
     such as <code>np</code> (noun phrase).
  </p>

  <p>Your input sentence was: <em><?php echo $tokinput; ?></em></p>

  <div id="tree-output">
      <?php include ROOT_PATH."/front-end-includes/tv-wrappers.php"; ?>
  </div>

  <form action="ebs/matrix.php" method="post" enctype="multipart/form-data">
    <p>If the analysis is different from what you expected, you can enter
      <a href="ebs/input.php" title="Example-based search">a new input example</a>.
    </p>
    <input type="hidden" name="sid" value="<?php echo SID; ?>">
    <?php setContinueNavigation(); ?>
  </form>
<?php
} else {
    if ($isSpam):
        setErrorHeading("spam detected"); ?>
        <p>Your input example contained a hyperlink or email address and is seen as spam. Therefore we will not allow you to continue. </p>
    <?php else:
        setErrorHeading('variables undefined'); ?>
        <p>It seems that you did not enter an input sentence. It is also
        possible that you came to this page directly without first entering an input example.</p>
    <?php endif;
    setPreviousPageMessage($step-1);
}

require ROOT_PATH."/front-end-includes/footer.php";
if ($continueConstraints) : ?>
  <script>
  $(function() {
      $("#tree-output").treeVisualizer('<?php echo "tmp/".SID."-pt.xml"; ?>', {
        sentence: "<?php echo htmlspecialchars($input, ENT_COMPAT | ENT_HTML5); ?>"
    });
  });
  </script>
<?php endif;
include ROOT_PATH."/front-end-includes/analytics-tracking.php";
?>
</body>
</html>
