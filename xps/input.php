<?php
/**
 * Takes XPath input from a user to query in a BaseX Client.
 *
 * A user inputs a valid XPath structure that will be queried in Basex. The XPath is
 * briefly validated before submitting (in js/scripts.js) check opening/closing tags match),
 * and fully parsed in the next step(s).
 *
 * @author Liesbeth Augustinus
 * @author Bram Vanroy
 */

 session_start();
 header('Content-Type:text/html; charset=utf-8');

$currentPage = 'xps';
$step = 1;

require "../config.php";
require ROOT_PATH."/helpers.php";

$xpath = '//node[@cat="smain" and node[@rel="su" and @pt="vnw"] and node[@rel="hd" and @pt="ww"] and node[@rel="predc" and @cat="np" and node[@rel="det" and @pt="lid"] and node[@rel="hd" and @pt="n"]]]';

define('SID', session_id() . '-' . time());
$_SESSION[SID] = array();
$_SESSION[SID]['queryid'] = SID;

require ROOT_PATH."/functions.php";
require ROOT_PATH."/front-end-includes/head.php";
?>
</head>
<?php flush(); ?>
<?php require ROOT_PATH."/front-end-includes/header.php"; ?>
    <form action="xps/tb-sel.php" method="post" enctype="multipart/form-data">
        <p>Enter an <strong>XPath expression</strong> containing the (syntactic) characteristics you are looking for:</p>
        <div class="input-wrapper">
            <textarea name="xpath" id="xpath" wrap="soft" required spellcheck="false"><?php echo $xpath; ?></textarea>
            <button type="reset" name="clear" title="Empty the input field">
              <i class="fa fa-fw fa-times"></i>
              <span class="sr-only">Empty the input field</span>
            </button>
            <div class="open-beautifier-wrapper">
            <a href="http://bramvanroy.be/projects/xpath-beautifier/" title="Open and edit this XPath in the XPath Beautifier" aria-describedby="beautifier-tooltip" target="_blank">
              Open in XPath Beautifier
              <i class="fa fa-external-link" aria-hidden="true"></i>
            </a>

            <div class="help-tooltip" id="beautifier-tooltip" role="tooltip" data-title="The XPath Beautifier allows you to edit an expanded version of the XPath code given here.
            This makes it easier to apply any adjustments. When you're done, copy the XPath code back in the field.
            Note that you need to accept pop-ups for this website to open the beautifier in another tab!">
              <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
              <span class="sr-only">The XPath Beautifier allows you to edit an expanded version of the XPath code given here.
                This makes it easier to apply any adjustments. When you're done, copy the XPath code back in the text field.
                Note that you need to accept pop-ups for this website to open the beautifier in another tab!</span>
            </div>
          </div>
        </div>
         <input type="hidden" name="sid" value="<?php echo SID; ?>">
        <?php setContinueNavigation(); ?>
    </form>

    <?php
    require ROOT_PATH."/front-end-includes/footer.php";
    include ROOT_PATH."/front-end-includes/analytics-tracking.php";
    ?>
</body>
</html>
