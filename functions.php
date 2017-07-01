<?php
  // Set page heading and subheading variables
  if (isset($currentPage)) {
      if (isHome()) {
          $pageHeading = 'What is GrETEL?';
      } elseif ($currentPage == 'ebs') {
          $pageHeading = 'Example-based search';
          if (isset($step)) {
              $searchStepHeadings = array(
                  'Give an input example',
                  'Input parse',
                  'Select relevant parts',
                  'Select a treebank',
                  'Query overview',
                  'Results',
              );
          }
      } elseif ($currentPage == 'xps') {
          $pageHeading = 'XPath search';
          if (isset($step)) {
              $searchStepHeadings = array(
                  'Give an XPath expression',
                  'Select a treebank',
                  'Results',
              );
          }
      } elseif (isDocs()) {
          $pageHeading = 'Documentation';
      }
  }

  function setErrorHeading($string = '')
  {
      $message = 'Error';
      if ($string != '') {
          $message .= ": $string";
      }
      $message .= '!';
      echo "<h3 class='error-heading'>$message</h3>";
  }

  function setPreviousPageMessage($goToStep)
  {
      global $currentPage, $ebsPages, $xpsPages;
      $message = '<p>You can ';
      if (isset($currentPage)) {
          $keys = array_keys(${$currentPage.'Pages'});
          $href = $currentPage.'/'.$keys[$goToStep - 1];

          $message .= "go to <a href='$href' title='Go to a previous step'>step $goToStep</a> ";

          if ($goToStep == 1) $message .= "and try a new example ";
          $message .= "or ";
      }
      $message .= "go directly to <a href='index.php' title='Go to the homepage'>the homepage</a>.</p>";
      echo $message;
  }

    // Set page heading variables
    function setPageTitle()
    {
        global $currentPage, $step;
        if (isset($currentPage)) {
            if (isHome()) {
                $pageTitle = 'GrETEL | An example based search engine for corpora';
            } else {
              $pageTitle = '';
                if (isSearch()) {
                    if (isset($step)) {
                        $pageTitle .= "Step $step | ";
                    }
                    if ($currentPage == 'ebs') {
                        $pageTitle .= 'Example-based search';
                    } else {
                        $pageTitle .= 'XPath search';
                    }
                } elseif (isDocs()) {
                    $pageTitle .= 'Documentation';
                }
                $pageTitle .= ' | GrETEL';
            }
        } else {
            $pageTitle = 'GrETEL | An example based search engine for corpora';
        }
        echo $pageTitle;
    }

  function setBodyClasses()
  {
      global $currentPage, $step, $ebsPages, $xpsPages;
      if (isset($currentPage)) {
          $class = 'class="';
          $class .= $currentPage;
          if ($currentPage == 'ebs' || $currentPage == 'xps') {
              if (isset($step)) {
                  $class .= " step-$step";
              }
              if (isset($step) && $step == 1) {
                $class .= ' input';
              }  else {
                $pageType = array_values(${$currentPage.'Pages'});
                $class .= ' ' . strtolower($pageType[$step-1]);
              }
          }
          $class .= '"';
      } else {
          $class = '';
      }
      echo $class;
  }

  function setProgressClasses($index)
  {
      global $step;
      if ($step == $index) {
          return 'class="active no-hover"';
      } elseif ($step > $index) {
          return 'class="done"';
      } else {
          return 'class="no-hover"';
      }
  }

  function setProgressGoBack($index)
  {
      global $step;

      if ($index < $step && $index > 0) {
          $diff = $index - $step;

          return 'onclick="window.history.go('.$diff.'); return false;"';
      }
  }

  function buildProgressList()
  {
      global $step, $ebsPages, $xpsPages, $currentPage;
      $i = 0;

      $output = '<ul class="progressbar">';
      if ($currentPage == 'ebs') {
          foreach ($ebsPages as $uri => $title) {
              ++$i;
              $class = setProgressClasses($i);
              $onclick = setProgressGoBack($i);
              $output .= '<li '.$class.'><a href="ebs/'.$uri.'" '.$onclick.' title="'.$title.'">'.$i.
                '<span> - '.$title.'</span></a></li>';
          }
        }
      else {
          foreach ($xpsPages as $uri => $title) {
              ++$i;
              $class = setProgressClasses($i);
              $onclick = setProgressGoBack($i);
              $output .= '<li '.$class.'><a href="xps/'.$uri.'" '.$onclick.' title="'.$title.'">'.$i.
                '<span> - '.$title.'</span></a></li>';
          }
        }
      $output .= '</ul>';
      echo $output;
  }

  function buildEbsMatrix()
  {
      global $sentence, $matrixOptions;

      $tableHTML = '<table><thead><tr><th>sentence</th>';

      foreach ($sentence as $key => $word) {
          $tableHTML .= "<td>$word</td>";
      }
      $tableHTML .= '</tr></thead><tbody>';

      $i = 0;
      foreach ($matrixOptions as $thValArray) {
        $i++;
          foreach ($thValArray as $th => $val) {
              $csClass = ($val == 'cs') ? 'case-sensitive' : '';
              $advancedClass = ($val == 'postag' || $val == 'not' ||  $val == 'cs') ? 'advanced-option' : '';
              $tableHTML .= "<tr class='row-group-$i $csClass $advancedClass'>";
              $tableHTML .= "<th><span>$th</span>" . addHelpersMatrix($val) ."</th>";
              foreach ($sentence as $key => $word) {
                  $isPunc = preg_match("/[\p{P}|^$=`´<>~]/u", $word);
                  $puncClass = $isPunc ? 'punctuation' : '';
                  $word = htmlspecialchars($word, ENT_QUOTES);
                  if (($val == 'pos' & !$isPunc) || ($val == 'na' && $isPunc)) {
                      $tableHTML .= "<td class='$puncClass'><input type='radio' name='$word--$key' value='$val' checked></td>";
                  }
                  // Case sensitive
                  elseif ($val == 'cs') {
                    $tableHTML .= "<td class='disabled $puncClass'>";
                    if (!$isPunc) {
                      $tableHTML .= "<input type='checkbox' name='$word--$key-case' value='$val' disabled>";
                    }
                    $tableHTML .= "</td>";
                  }
                  else {
                      $tableHTML .= "<td class='$puncClass'><input type='radio' name='$word--$key' value='$val'></td>";
                  }
              }
              $tableHTML .= '</tr>';
          }
      }
      $tableHTML .= '</tbody></table>';

      echo $tableHTML;
  }

  function addHelpersMatrix($wordValue) {

      if ($wordValue == 'token') {
          $description = 'The exact word form (also known as token)';
      } elseif ($wordValue == 'cs') {
          $description = 'If the word option is selected, you can choose to look for case-sensitive occurrences';
      } elseif ($wordValue == 'lemma') {
          $description = 'Word form that generalizes over inflected forms.'
          . ' For example: gaan is the lemma of ga, gaat,'
          . ' gaan, ging, gingen, and gegaan';
      } elseif ($wordValue == 'pos') {
          $description = 'Short Dutch part-of-speech tag. The different tags are:'
          . ' n (noun), ww (verb), adj (adjective),'
          . ' lid (article), vnw (pronoun), vg (conjunction),'
          . ' bw (adverb), tw (numeral), vz (preposition),'
          . ' tsw (interjection), spec (special token), and let (punctuation)';
      } elseif ($wordValue == 'postag') {
          $description = 'Long part-of-speech tag. For example: N(soort,mv,basis),'
          . ' WW(pv,tgw,ev), VNW(pers,pron,nomin,vol,2v,ev)';
      } elseif ($wordValue == 'na') {
          $description = 'The word will be ignored in the search instruction.'
          . ' It may be included in the results, but it is not required that it is present';
      } elseif ($wordValue == 'not') {
          $description = 'The word class and the dependency relation will be explicitly excluded from the results';
      }

      return '<div class="help-tooltip" role="tooltip" data-title="'.$description.'">'
      . '<i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>'
      . '<span class="sr-only">'.$description.'</span></div>';
  }

  function setContinueNavigation()
  {
      global $step, $currentPage, $ebsPages, $xpsPages;
      $navString = '<nav class="continue-btn-wrapper">';
      if (isBigStep()) {
          $navString .= '<button onclick="history.go(-1); return false" title="Go back to the previous page"><i class="fa fa-fw fa-angle-left" aria-hidden="true"></i> Go back</button>';
      }
      if (isset($currentPage, $step) && $step < count(${$currentPage.'Pages'})) {
          $navString .= '<button type="submit" title="Continue to the next page">Continue <i class="fa fa-fw fa-angle-right" aria-hidden="true"></i></button>';
      }
      $navString .= '</nav>';

      echo $navString;
  }

  // Returns associative array containing at least the machine and the port
  function getServerInfo($corpus, $component) {
    global $databaseGroups;
    // Lassy and CGN
    if (!$component && array_key_exists($corpus, $databaseGroups)) {
      return $databaseGroups{$corpus};
    }
    // Sonar components
    elseif (array_key_exists($component, $databaseGroups{$corpus})) {
      return $databaseGroups{$corpus}{$component};
    } elseif (in_array($component, $databaseGroups{$corpus}{'REST'}{'components'})) {
      return $databaseGroups{$corpus}{'REST'};
    }
  }

  // Remove false-y items and spaces-only items from array
  function array_cleaner($array) {
      $array = array_map('trim', $array);
      $array = array_filter($array);
      return $array;
  }
