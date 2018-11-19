<p>Which treebank do you want to query? Click on the treebank name to see its different components. If you would like to get more information
  on these treebanks, you can find the project websites in <a href="documentation.php#faq-3" target="_blank" title="More information on corpora">our FAQ</a>.</p>

<p>You can search an entire treebank, or select just one or more components. Due to pre-processing difficulties
  some sentences could not be included in the system, so the sentence and word counts may slightly differ from the official treebank counts.</p>

<?php
  $nextPage = ($currentPage == 'ebs') ? 'ebs/query.php' : 'xps/results.php';
?>

<form action="<?php echo $nextPage; ?>" method="post">
  <div class="flex-content">
    <div class="labels-wrapper">
      <?php
      foreach ($databaseGroups as $corpus => $arr) {
        echo "<div class=\"label-wrapper\">
          <label>
            <input type=\"radio\" name=\"treebank\" value=\"{$corpus}\" data-multioption=\"{$arr['multioption']}\">
            <strong>{$arr['fullName']}</strong>: {$arr['production']} {$arr['language']} - version {$arr['version']}
          </label>
        </div>";
      } ?>
    </div>
  </div>
  <div class="corpora-wrapper">
    <?php foreach ($databaseGroups as $corpus => $arr) {
      if (array_key_exists('hasComponents', $arr) && $arr['hasComponents']) {
        echo "<div class=\"{$corpus}\" style=\"display:none\">";
        require ROOT_PATH."/front-end-includes/tb-{$corpus}.php";
        echo '</div>';
      }
    } ?>
  </div>
