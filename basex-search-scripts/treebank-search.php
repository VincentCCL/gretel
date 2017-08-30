<?php

function getMoreIncludes($database, &$databases, &$already, $session)
{
    $xquery = 'db:open("'.$database.'")/treebank/include';
    $query = $session->query($xquery);
    $result = $query->execute();
    $query->close();

    $newIncludes = explode("\n", $result);
    $newIncludes = array_cleaner($newIncludes);

    $pattern = '/file=\"(.+)\"/';
    foreach ($newIncludes as $newInclude) {
        if (preg_match($pattern, $newInclude, $files)) {
            $file = $files[1];
            if (!includeAlreadyExists($file, $already)) {
                $databases[] = $file;
            }
        }
    }
}

function includeAlreadyExists($include, &$already)
{

    if (isset($already{$include})) {
        return true;
    } else {
        $already{$include} = 1;
        return false;
    }
}

function corpusToDatabase($components, $corpus)
{
    $databases = array();

    foreach ($components as $component) {
        $corpus = strtoupper($corpus);
        $component = strtoupper($component);
        $component = $corpus.'_ID_'.$component;
        $databases[] = $component;
    }

    return $databases;
}

function getSentences($databases, $already, $endPosIteration, $session, $sid)
{
    global $flushLimit, $resultsLimit, $needRegularSonar, $corpus;

    $matchesAmount = 0;

    while ($database = array_pop($databases)) {
        if ($corpus == 'sonar' && !$needRegularSonar) {
            getMoreIncludes($database, $databases, $already, $session);
        }
        while (1) {
            if ($endPosIteration !== 'all') {
                ++$endPosIteration;
            }

            $xquery = createXquery($database, $endPosIteration);
            $query = $session->query($xquery);
            $result = $query->execute();
            $query->close();

            if (!$result || $result == 'false') {
                if ($endPosIteration !== 'all') {
                    $endPosIteration = 0;
                }
                break;
            }

            $matches = explode('</match>', $result);
            $matches = array_cleaner($matches);

            while ($match = array_shift($matches)) {
                if ($endPosIteration === 'all' && $matchesAmount >= $resultsLimit) {
                    break 3;
                }
                $match = str_replace('<match>', '', $match);

                if ($corpus == 'sonar') {
                    list($sentid, $sentence, $tb, $ids, $begins) = explode('||', $match);
                } else {
                    list($sentid, $sentence, $ids, $begins) = explode('||', $match);
                }

                if (isset($sentid, $sentence, $ids, $begins)) {
                    ++$matchesAmount;

                    $sentid = trim($sentid);

                    // Add unique identifier to avoid overlapping sentences w/ same ID
                    $sentid .= '-endPos='.$endPosIteration.'+match='.$matchesAmount;

                    $sentences{$sentid} = $sentence;
                    $idlist{$sentid} = $ids;
                    $beginlist{$sentid} = $begins;
                    if ($corpus == 'sonar') {
                        $tblist{$sentid} = $tb;
                    }
                }
            }
            if ($endPosIteration === 'all') {
                break;
            } else if ($matchesAmount >= $flushLimit) {
                // Re-add pop'd database because it is very likely we aren't finished with it
                // More results are still in that database but because of the flushlimit we
                // have to bail out
                $databases[] = $database;
                break 2;
            }
        }
    }


    if (isset($sentences)) {
        if ($endPosIteration !== 'all') {
            session_start();
            $_SESSION[$sid]['endPosIteration'] = $endPosIteration;
            $_SESSION[$sid]['flushDatabases'] = $databases;
            $_SESSION[$sid]['flushAlready'] = $already;
            session_write_close();
        }
        if ($corpus !== 'sonar') {
            $tblist = false;
        }
        return array($sentences, $tblist, $idlist, $beginlist);
    } else {
        // in case there are no results to be found
        return false;
    }
}

function createXquery($database, $endPosIteration)
{
    global $flushLimit, $resultsLimit, $needRegularSonar, $corpus, $components, $context, $xpath;

    $for = 'for $node in db:open("'.$database.'")/treebank';
    if ($corpus == 'sonar' && !$needRegularSonar) {
        $for .= '/tree';
        $sentid = 'let $sentid := ($node/ancestor::tree/@id)';
        $sentence = '
    return
    for $sentence in (db:open("'.$components[0].'sentence2treebank")/sentence2treebank/sentence[@nr=$sentid])
        let $tb := ($sentence/@part)';
    } else {
        $sentid = 'let $sentid := ($node/ancestor::alpino_ds/@id)';
        $sentence = 'let $sentence := ($node/ancestor::alpino_ds/sentence)';
    }

    $regulartb = $needRegularSonar ? "let \$tb := '$database'" : '';
    $returnTb = ($corpus == 'sonar') ? '||{data($tb)}' : '';

    $ids = 'let $ids := ($node//@id)';
    $begins = 'let $begins := ($node//@begin)';
    $beginlist = 'let $beginlist := (distinct-values($begins))';
    if ($context && !$needRegularSonar) {
        if ($corpus == 'sonar') {
            $databases = $component[0].'sentence2treebank';
        } else {
            $databases = strtoupper($corpus).'_ID_S';
        }

        $text = 'let $text := fn:replace($sentid[1], \'(.+?)(\d+)$\', \'$1\')';
        $snr = 'let $snr := fn:replace($sentid[1], \'(.+?)(\d+)$\', \'$2\')';
        $prev = 'let $prev := (number($snr)-1)';
        $next = 'let $next := (number($snr)+1)';
        $previd = 'let $previd := concat($text, $prev)';
        $nextid = 'let $nextid := concat($text, $next)';

        $prevs = 'let $prevs := (db:open("'.$databases.'")';
        $nexts = 'let $nexts := (db:open("'.$databases.'")';

        if ($corpus != 'sonar') {
            $prevs .= '//s[id=$previd]/sentence)';
            $nexts .= '//s[id=$nextid]/sentence)';
        } else {
            $prevs .= '/sentence2treebank/sentence[@nr=$previd])';
            $nexts .= '/sentence2treebank/sentence[@nr=$nextid])';
        }

        $return = ' return <match>{data($sentid)}||{data($prevs)} <em>{data($sentence)}</em> {data($nexts)}'
            .$returnTb.'||{string-join($ids, \'-\')}||{string-join($beginlist, \'-\')}</match>';

        $xquery = $for.$xpath.$sentid.$sentence.$ids.$begins.$beginlist.$text.$snr.$prev.$next.$previd.$nextid.$prevs.$nexts.$return;
    } else {
        $return = ' return <match>{data($sentid)}||{data($sentence)}'.$returnTb
            .'||{string-join($ids, \'-\')}||{string-join($beginlist, \'-\')}</match>';
        $xquery = $for.$xpath.$sentid.$sentence.$regulartb.$ids.$begins.$beginlist.$return;
    }

    // Adds positioning values:; limits possible output
    $openPosition = '(';
    // Never fetch more than the resultsLimit, not even with all
    if ($endPosIteration == 'all') {
        $closePosition = ')[position() = 1 to '.$resultsLimit.']';
    }
    // Only fetch the given flushLimit, and increment on each iteration
    else {
        $endPosition = $endPosIteration * $flushLimit;
        $startPosition = $endPosition - $flushLimit + 1;
        $closePosition = ')[position() = '.$startPosition.' to '.$endPosition.']';
    }

    $xquery = $openPosition.$xquery.$closePosition;

    return $xquery;
}

function highlightSentence($sentence, $beginlist, $tag)
{
    if (strpos($sentence, '<em>') !== false) {
        preg_match("/(.*<em>)(.*?)(<\/em>.*)/", $sentence, $groups);
        $s = $groups[2];
        $prev = $groups[1];
        $next = $groups[3];
    } else {
        $s = $sentence;
    }
    $words = explode(' ', $s);
    $begins = explode('-', $beginlist);

    $i = 0;
    // Instead of wrapping each individual word in a tag, merge sequences
    // of words in one <tag>...</tag>
    foreach ($words as $word) {
        if (in_array($i, $begins)) {
            $val = '';
            if (!in_array($i - 1, $begins)) {
                $val .= "<$tag>";
            }
            $val .= $words[$i];
            if (!in_array($i + 1, $begins)) {
                $val .= "</$tag>";
            }
            $words[$i] = $val;
        }
        ++$i;
    }
    $hlsentence = implode(' ', $words);
    if (isset($prev) || isset($next)) {
        $hlsentence = $prev.' '.$hlsentence.' '.$next;
    }

    return $hlsentence;
}

function getRegularSonar($component)
{
    $databases = file("treebank-parts/$component.lst", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    return $databases;
}
