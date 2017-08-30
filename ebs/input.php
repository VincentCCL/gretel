<?php
/**
 * EBS STEP 1: Takes a sentence as input from a user, which is processed in the next steps.
 *
 * A user inputs a valid XPath structure that will be queried in Basex. The XPath is
 * briefly validated before submitting (in js/scripts.js) check opening/closing tags match),
 * and fully parsed in the next step(s). A user can choose for a basic search, or an
 * adcanced search which will provide more options.
 *
 * @author Liesbeth Augustinus
 * @author Bram Vanroy
 */

session_start();
header('Content-Type:text/html; charset=utf-8');

$currentPage = 'ebs';
$step = 1;

require "../config.php";
require ROOT_PATH."/helpers.php";

define('SID', session_id() . '-' . time());
$_SESSION[SID] = array();
$_SESSION[SID]['queryid'] = SID;

require ROOT_PATH."/functions.php";
require ROOT_PATH."/front-end-includes/head.php";
?>
</head>
<?php flush(); ?>
<?php require ROOT_PATH."/front-end-includes/header.php"; ?>
  <form action="ebs/parse.php" method="post" enctype="multipart/form-data">
      <p>Enter a sentence, phrase, or constituent containing the (syntactic) characteristics you are looking for.</p>
      <div class="input-wrapper">
        <input type="text" name="input" placeholder="Dit is een voorbeeldzin." value="" required>
        <button type="reset" name="clear" title="Empty the input field">
          <i class="fa fa-fw fa-times"></i>
          <span class="sr-only">Empty the input field</span>
        </button>
      </div>
      <input type="hidden" name="sid" value="<?php echo SID; ?>">
      <p class="notice">
      <small>Do not try to run two or more queries in the same browser instance (i.e. multiple queries in multiple browsers tabs).
      This will cause issues. If you want to run multiple queries simultaneously, open a new <strong>browser window</strong> (<em>not a tab</em>).
      You can find the reason for this <a href='<?php echo HOME_PATH."/documentation.php#faq-7"; ?>' title="Why can't I open multiple tabs with GrETEL queries?" target="_blank">in our FAQ</a></small>
     </p>
      <?php setContinueNavigation(); ?>
  </form>

  <?php
  require ROOT_PATH."/front-end-includes/footer.php";
  include ROOT_PATH."/front-end-includes/analytics-tracking.php";
  ?>
</body>
</html>
