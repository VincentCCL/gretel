<?php

function getCounts($databases, $already, $session)
{
    global $corpus, $needRegularSonar;

    $sum = 0;
    $counts = array();

    while ($database = array_pop($databases)) {
        if ($corpus == 'sonar' && !$needRegularSonar) {
            getMoreIncludes($database, $databases, $already, $session);
        }
        $xquery = createXqueryCount($database);
        $query = $session->query($xquery);

        if ($corpus == 'sonar') {
            $sum += $query->execute();
        } else {
            $counts{$database} = $query->execute();
        }

        $query->close();
    }

    if ($corpus !== 'sonar') {
        $sum = array_sum($counts);
    }

    return array($sum, $counts);
}

function createCsvCounts($sum, $counts, $sid)
{
    if (isset($counts) && count($counts) > 0 && $sum > 0) {
        $fileName = ROOT_PATH."/tmp/$sid-gretel-distribution.csv";
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $fh = fopen($fileName, 'w');
        fputcsv($fh, array('Treebank', 'Hits', '# sentences in treebank'));

	foreach ($counts as $database => $countsArray) {
        if ($corpus == 'cgn' || $corpus == 'lassy') {	
            $databaseStringArray = explode('_', $database);
            $databaseString = $databaseStringArray[2];
            fputcsv($fh, array($databaseString, $countsArray[0], $countsArray[1]));
        }

	else {   
            $databaseStringArray = explode('_', $database);
            $databaseString = $databaseStringArray[1];
            fputcsv($fh, array($databaseString, $countsArray[0], $countsArray[1]));
        }


	}
        fclose($fh);
    }
}

function createXqueryCount($database)
{
    global $needRegularSonar, $xpath, $corpus;
    $for = 'count(for $node in db:open("'.$database.'")/treebank';
    if ($corpus == 'sonar' && !$needRegularSonar) {
        $for .= '/tree';
    }
    $return = ' return $node)';
    $xquery = $for.$xpath.$return;

    return $xquery;
}

function getTotalSentences()
{
    global $corpus;
    if ($corpus == 'lassy') {
        $total['LASSY_ID_DPC'] = '11716';
        $total['LASSY_ID_WIKI'] = '7341';
        $total['LASSY_ID_WRPE'] = '14420';
        $total['LASSY_ID_WRPP'] = '17691';
        $total['LASSY_ID_WSU'] = '14032';
        $total['TOTAL'] = '65200';
    } elseif ($corpus == 'cgn') {
        $total['CGN_ID_NA'] = '50239';
        $total['CGN_ID_NB'] = '2484';
        $total['CGN_ID_NC'] = '11649';
        $total['CGN_ID_NE'] = '3123';
        $total['CGN_ID_NF'] = '6290';
        $total['CGN_ID_NG'] = '1166';
        $total['CGN_ID_NH'] = '3064';
        $total['CGN_ID_NI'] = '2251';
        $total['CGN_ID_NJ'] = '2259';
        $total['CGN_ID_NK'] = '1923';
        $total['CGN_ID_NL'] = '1857';
        $total['CGN_ID_NM'] = '444';
        $total['CGN_ID_NN'] = '593';
        $total['CGN_ID_VA'] = '22881';
        $total['CGN_ID_VB'] = '4289';
        $total['CGN_ID_VC'] = '3142';
        $total['CGN_ID_VD'] = '929';
        $total['CGN_ID_VF'] = '2617';
        $total['CGN_ID_VG'] = '543';
        $total['CGN_ID_VH'] = '1395';
        $total['CGN_ID_VI'] = '1026';
        $total['CGN_ID_VJ'] = '536';
        $total['CGN_ID_VK'] = '558';
        $total['CGN_ID_VL'] = '601';
        $total['CGN_ID_VM'] = '107';
        $total['CGN_ID_VN'] = '701';
        $total['CGN_ID_VO'] = '3256';
        $total['TOTAL'] = '129923';
    } elseif ($corpus == 'wablieft') {
       $total['wablieft_bi'] = '58560';
       $total['wablieft_blog'] = '5464';
       $total['wablieft_bu'] = '40953';
       $total['wablieft_cijfer'] = '1595';
       $total['wablieft_idk'] = '49438';
       $total['wablieft_jo'] = '301';
       $total['wablieft_mening'] = '6117';
       $total['wablieft_sam'] = '23660';
       $total['wablieft_sp'] = '24747';
       $total['wablieft_tip'] = '12869';
       $total['wablieft_verh'] = '2529';
       $total['wablieft_vp'] = '5121';
       $total['wablieft_we'] = '19927';
       $total['wablieft_zo'] = '5448';
       $total['TOTAL'] = '2074491';
    } else {
        return false;
    }

    return $total;
}
