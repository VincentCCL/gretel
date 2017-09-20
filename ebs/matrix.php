<?php
/**
 * EBS STEP 3: Shows the user a matrix where they can select word-per-word information
 * that they want queried.
 *
 * The sentence given in the previous step is tokenized. Based on the tokens, a matrix is build.
 * The user can select what information is information for them, e.g. lemma, pos-tag, word,
 * and so on..
 *
 *Also two search-wide options are available:
 * 1. Respect word order;
 * 2. Ignore properties of top node.
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
 $step = 3;

 require "../config.php";
 require ROOT_PATH."/helpers.php";

$continueConstraints = isset($_POST['sid']) && sessionVariablesSet($_POST['sid'], array('example', 'sentence'));

if ($continueConstraints) {
    $treeVisualizer = true;

    define('SID', $_POST['sid']);

    $input = $_SESSION[SID]['example'];

    // Set tokenized input sentence to variable
    $tokinput = $_SESSION[SID]['sentence'];
    $sentence = explode(' ', $tokinput);
}

require ROOT_PATH."/functions.php";
require ROOT_PATH."/front-end-includes/head.php";

?>
</head>
<?php flush(); ?>
<?php
require ROOT_PATH."/front-end-includes/header.php";

if ($continueConstraints):
?>
<form action="ebs/tb-sel.php" method="post">
  <p>
    In the matrix below, you can indicate the relevant parts of the construction, i.e. the lexical items that should be included in the query, as well as their level of abstraction. Your selection is visualized by means of the <em>query tree</em> at the bottom of this page. The syntactic information provided by the parse in the previous step is automatically included.<br/>
    The advanced options offer more possibilities to modify your search instruction. You can also modify the generated <em>XPath query</em>, which will be used to do the actual treebank search.
  </p>
  <div class="flex-content">
    <div class="input-wrapper">
      <div class="advanced-btn-wrapper">
        <button type="button" title="Show advanced options">
          <span>Show advanced options</span>
          <i class="fa fa-cog" aria-hidden="true" style="margin-left: 4px"></i>
        </button>
      </div>
      <div class="table-wrapper">
        <?php buildEbsMatrix(); ?>
      </div>
    </div>

    <div class="xpath-wrapper">
      <div>
        <h3 style="margin-top: 0">Search options</h3>
        <div class="label-wrapper">
          <label>
            <input type="checkbox" name="order" aria-describedby="order-tooltip"> Respect word order
          </label>
          <div class="help-tooltip" id="order-tooltip" role="tooltip" data-title="Only search for patterns that have the same word order as your input example">
            <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
            <span class="sr-only">Only search for patterns that have the same word order as your input example</span>
          </div>
        </div>
        <div class="label-wrapper">
          <label>
            <input type="checkbox" name="ct" value="on" aria-describedby="ct-tooltip"> Include context in results
          </label>
          <div class="help-tooltip" id="ct-tooltip" data-title="In the results, show the sentence before and after the matching sentence to provide a broader context">
            <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
            <span class="sr-only">In the results, show the sentence before and after the matching sentence to provide a broader context</span>
          </div>
        </div>
        <div class="label-wrapper">
          <label>
            <input type="checkbox" name="topcat" aria-describedby="topcat-tooltip"> Ignore properties of the dominating node
          </label>
          <div class="help-tooltip" id="topcat-tooltip" role="tooltip" data-title="Search for more general patterns by ignoring the properties of the top node, e.g. search for both main clauses and subclauses">
            <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
            <span class="sr-only">Search for more general patterns by ignoring the properties of the top node, e.g. search for both main clauses and subclauses</span>
          </div>
        </div>
      </div>

      <div>
        <div class="label-wrapper">
          <label>
            <input type="checkbox" name="sentids" aria-describedby="sentids-tooltip" checked> Include sentence IDs in downloadable results
          </label>
          <div class="help-tooltip" id="sentids-tooltip" role="tooltip" data-title="Add sentence identifiers to downloads. A sentence identifier is a unique identifier given to each sentence in a corpus. Given an identifier you can look up the sentence in the original corpus">
            <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
            <span class="sr-only">Add sentence identifiers to downloads. A sentence identifier is a unique identifier given to each sentence in a corpus. Given an identifier you can look up the sentence in the original corpus</span>
          </div>
        </div>
      </div>

      <div>
        <h3 class="advanced-option">Modify XPath</h3>
        <input type="hidden" name="originalXp" value="">
        <input type="hidden" name="manualMode" value="false">

        <textarea id="xpath" class="advanced-option" name="xpath" spellcheck="false" wrap="soft" required></textarea>
        <div class="open-beautifier-wrapper advanced-option">
          <input type="reset" value="Reset XPath" style="font-size: 88%">

          <a href="http://bramvanroy.be/projects/xpath-beautifier/" title="Open and edit this XPath in the XPath Beautifier" aria-describedby="beautifier-tooltip" target="_blank">
            Open in XPath Beautifier
            <i class="fa fa-external-link" aria-hidden="true"></i>
          </a>

          <div class="help-tooltip" id="beautifier-tooltip" role="tooltip" data-title="The XPath Beautifier allows you to edit an expanded version of the XPath code given here.
          This makes it easier to apply any adjustments. When you're done, copy the XPath code back in the field above.
          Note that you need to accept pop-ups for this website to open the beautifier in another tab!">
            <i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
            <span class="sr-only">The XPath Beautifier allows you to edit an expanded version of the XPath code given here.
              This makes it easier to apply any adjustments. When you're done, copy the XPath code back in the text field above.</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="tree-output">
      <?php include ROOT_PATH."/front-end-includes/tv-wrappers.php"; ?>
  </div>
  <input type="hidden" name="sid" value="<?php echo SID; ?>">
  <?php setContinueNavigation(); ?>
</form>

<?php
else:
  setErrorHeading(); ?>
  <p>No search instruction could be generated, since you did not enter a sentence.
    It is also possible that you came to this page directly without first entering a query.</p>
  <?php setPreviousPageMessage($step-1);
endif;

require ROOT_PATH."/front-end-includes/footer.php";
include ROOT_PATH."/front-end-includes/analytics-tracking.php";
?>

<script>
    var getTreePathScript = <?php echo json_encode(HOME_PATH."/preparatory-scripts/process-input-example.php?sid=".SID); ?>;
</script>

</body>
</html>
