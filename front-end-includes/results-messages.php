<!DOCTYPE html>
<html lang="en">

<?php
require "../config.php";

session_start();
if (!isset($_GET['sid'])) {
  exit;
}

define('SID', $_GET['sid']);

require ROOT_PATH."/functions.php";
?>

<head>
    <title>Messages for the results page</title>
    <meta name="robots" content="noindex">
</head>

<body>
  <?php
  $downloadPath = HOME_PATH."/tmp/".SID."-gretel-results-dl.txt";
  $printPath = HOME_PATH."/tmp/".SID."-gretel-results-print.html";
  ?>

  <div id="results-found">
      <p><strong class="amount-hits">--</strong> result(s) have/has been found!</p>
      <small class="is-still-counting">Still counting the total amount of hits. This can take a while but you can already download the first 500 results by clicking the button below.</small>
      <p class="is-restricted" style="display: none;">We have restricted the output to 500 hits. You can find the reason for this
        <a href='<?php echo HOME_PATH."/documentation.php#faq-1"; ?>' title="Why is the output limited to 500 sentences?" target="_blank">in our FAQ</a>.<br>
      </p>

      <p>
        <a href="<?php echo $downloadPath; ?>" title="Download results" class="download-link" target="_blank" download="gretel-results.txt">
        <i class="fa fa-fw fa-loaded fa-download" aria-hidden="true"></i> Download results</a>
    </p>
    <p>
      <a href="<?php echo $printPath; ?>" title="Download printer-friendly results" class="download-link" target="_blank">
      <i class="fa fa-fw fa-loaded fa-print" aria-hidden="true"></i> Printer-friendly results</a>
    </p>
  </div>
  <div id="no-results-found">
      <strong>No results were found!</strong>
      <?php setPreviousPageMessage(1); ?>
  </div>
  <div id="error">
      <strong>Error! <span class="error-msg"></span></strong>
      <?php setPreviousPageMessage(1); ?>
  </div>
</body>

</html>
