<?php

function xpathToBreadthFirst($xpath)
{
    $bfresult;
    // Divide XPath in top-most level, and the rest (its "descendants")
    if (preg_match("/^\/*node\[([^\[]*?)((?:node\[|count\().*)\]$/", $xpath, $items)) {
        list(, $topattrs, $descendants) = $items;
        $topcat;
         // Find category of top node
         if ($topattrs && preg_match_all("/\@cat=\"([^\"]+)\"/", $topattrs, $toppattrsArray)) {
           // If we are dealing with more than one topcat, use ALL instead
           if (count($toppattrsArray[1]) == 1) {
             $topcat = $toppattrsArray[1][0];
           } else {
              $topcat = 'ALL';
            }
         }
         // If the top node doesn't have any attributes
         // or if value is not specified, return ALL
         else {
             $topcat = 'ALL';
         }

         $descendants = trim($descendants);
         // Only continue if there is more than one level
         if ($descendants) {
             // Remove fixed-order nodes, not relevant
             $descendants = preg_replace("/(?:and )?number\(\@begin\).*?\@begin\)/", '', $descendants);

             $depth = 0;
             $children = '';
             $charlength = strlen($descendants);

             // Goes through each character of the string and keeps track of the
             // depth we're in. If the depth is two or more, we don't take those
             // characters into account. Output is $children that contains only
             // nodes from the second level (i.e. "children" of top node)
             for ($pos = 0; $pos < $charlength; ++$pos) {
                 $char = substr($descendants, $pos, 1);

                 if ($char == '[') {
                     ++$depth;
                 }

                 // If we're less than 2 levels deep: keep characters in string
                 if ($depth < 2) {
                     $children .= $char;
                 }

                 // If we're deeper: don't include string, and possibly remove
                 // trailing start node of a deeper level
                 else {
                     $children = preg_replace('/(and )?node$/', '', $children);
                 }

               // Only decrement depth after operations to ensure closing brackets
               // of nodes that are too deep are excluded
                 if ($char == ']') {
                     --$depth;
                 }
             }

             if ($depth != 0) {
                 // warn("XPath not correct");
                 return false;
             }

            // Check if there is a count present and manipulate the string
            // accordingly, i.e. multiply when necessary
            // e.g. count(node[@pt="n"]) > 1 -> node[@pt="n"] and node[@pt="n"]
             $children = preg_replace_callback("/(count\((.*)\) *> *([1-9]+))/",
              function ($matches) {
                  return $matches[2].str_repeat(' and '.$matches[2], $matches[3]);
              }, $children);

             $dfpatterns = array();

             // Loop through all remaining node[...] matches and extract rel
             // and cat values
             preg_match_all("/node\[([^\]]*)/", $children, $childrenArray);

             foreach ($childrenArray[0] as $childNode) {
               // Check if rel-attribute exists, and if there is only one
               // If there isn't, or if there is more than one we don't parse
               // this one
                preg_match_all("/\@rel=\"([^\"]+)\"/", $childNode, $relArray);
                $relArray = array_filter($relArray);

                if (count($relArray[1]) == 1) {
                  $rel = $relArray[1][0];

                  preg_match_all("/\@cat=\"([^\"]+)\"/", $childNode, $catArray);
                  $catArray = array_filter($catArray);

                  if (empty($catArray)) {
                    preg_match_all("/\@pt=\"([^\"]+)\"/", $childNode, $catArray);
                    $catArray = array_filter($catArray);
                  }

                  if (!empty($catArray)) {
                    if (count($catArray[1]) == 1) {
                      $cat = $catArray[1][0];
                    } else {
                      $cat = '';
                    }
                  } else {
                    $cat = '';
                  }

                  $dfpattern = "$rel%$cat";
                  $dfpatterns[] = $dfpattern;
                }
             }  // end foreach

             if ($dfpatterns) {
                 // Sort array alphabetically
                 sort($dfpatterns);
                 $dfpatternjoin = implode('_', $dfpatterns);
                 $bfresult = $topcat.$dfpatternjoin;
             } else {
                 $bfresult = $topcat;
             }
         }    // end if ($descendants)
         else {
             $bfresult = $topcat;
         }
    } else {
        $bfresult = false;
    }

    return $bfresult;
}

function cleanXpath($xpath, $trimSlash = true) {
  // Clean up XPath and original XPath
  $trans = array("='" => '="', "' " => '" ', "']" => '"]', "\r" => ' ', "\n" => ' ', "\t" => ' ');

  $xpath = strtr($xpath, $trans);
  $xpath = trim($xpath);

  if ($trimSlash) $xpath = preg_replace('/^\/+/', '', $xpath);

  return $xpath;
}

function checkBfPattern($bf, $sid) {
  global $cats, $components, $dbuser, $dbpwd,
    $continueConstraints, $databaseExists, $needRegularSonar;

    $component = $components[0];

  // If bf-pattern == ALL, we're faster searching through regular version
  if ($bf && $bf != 'ALL') {
      $tempDatabases = array();
      // If is substring (eg. ALLnp%det)
      if (strpos($bf, 'ALL') !== false) {
          foreach ($cats as $cat) {
            $bfcopy = $component . str_replace('ALL', $cat, $bf);
            $tempDatabases[] = $bfcopy;
          }
      } else {
        $tempDatabases[] = $component . $bf;
      }

      try {
        $serverInfo = getServerInfo('sonar', $component);

        $dbhost = $serverInfo{'machine'};
        $dbport = $serverInfo{'port'};
        $session = new Session($dbhost, $dbport, $dbuser, $dbpwd);

        foreach ($tempDatabases as $database) {
          // db:exists returns a string 'false', still need to convert to bool
          $databaseExists = $session->query("db:exists('$database')")->execute();

          if ($databaseExists != 'false') {
            $databaseExists = true;
            $_SESSION[$sid]['startDatabases'][] = $database;
          }
        }
        $continueConstraints = $databaseExists ? true : false;
        $session->close();
      } catch (Exception $e) {
        error_log($e);
      }
  } else {
    $_SESSION[$sid]['startDatabases'] = getRegularSonar($component);
    $needRegularSonar = true;
  }
}

function tokenize($sentence)
{
  // Add space before and after punctuation marks
  $sentence = preg_replace('/([<>\.\,\:\;\?!\(\)\"])/', ' $1 ', $sentence);
  // Deal wth ...
  $sentence = preg_replace("/(\.\s+\.\s+\.)/", ' ... ', $sentence);
  // Delete first and last space(s)
  $sentence = preg_replace('/^\s*(.*?)\s*$/', '$1', $sentence);
  // Change multiple spaces to single space
  $sentence = preg_replace('/\s+/', ' ', $sentence);
  return $sentence;
}

function modifyLemma($parse, $id)
{
    $parseLocation = ROOT_PATH."/tmp/$id-pt.xml";
    $output = fopen($parseLocation, 'w');
    // Read alpino parse
    $xml = simpledom_load_file($parse);
    // Sort terminal nodes by 'begin' attribute
    $pts = $xml->sortedXPath('//node[@begin and @postag]', '@begin');

    foreach ($pts as $pt) {
        if ($pt != 'let') {
            $lemma = $pt->getAttribute('lemma');
            // Remove _ from lemmas (for compounds) & remove _DIM from lemmas (for diminutives)
            $lemma = preg_replace('/_(DIM)?/', '', $lemma);
            // Add lemma
            $pt->setAttribute('lemma', $lemma);
        }
    }

    if ($xml->xpath('/treebank')) {
      $tree = $xml->alpino_ds->asXML();
    } else {
      $tree = $xml->asXML();
    }

    fwrite($output, $tree);
    fclose($output);
}

function applyCs($xpath) {
  if (strpos($xpath, '@caseinsensitive="yes"') !== false) {
    preg_match_all("/(?<=node\[).*?(?=node\[|\])/", $xpath, $matches);
    foreach ($matches[0] as $match) {
      if (strpos($match, '@caseinsensitive="yes"') !== false) {
        $dummyMatch = preg_replace('/(?: and )?@caseinsensitive="yes"/', '', $match);
          if (strpos($dummyMatch, '@word') !== false || strpos($dummyMatch, '@lemma') !== false) {
            // Wrap attribute in lower-case(), and lower-case the value
            $dummyMatch = preg_replace_callback('/@(word|lemma)="([^"]+)"/', function($matches) {
               return 'lower-case(@'. $matches[1]. ')="'.strtolower($matches[2]).'"';
             }, $dummyMatch);
          }

          $xpath = preg_replace('/'.preg_quote($match, '/').'/', $dummyMatch, $xpath, 1);
      }
    }
  }
  return $xpath;
}

function isSpam($string)
{
  $websiteRegex = '/(?:https?\:\/\/)?[a-zA-Z0-9-.+&@#%?=~_|!:,.;\/\\\]+(?:\.[a-zA-Z]{2,3}){1,2}(\/\S*)?/';

  if (preg_match($websiteRegex, $string) || filter_var($string, FILTER_VALIDATE_EMAIL)) {
    return true;
  }
  return false;
}
