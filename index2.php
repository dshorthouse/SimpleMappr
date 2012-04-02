<?php

function dms_to_deg($dms) {
    $dms = stripslashes($dms);
    $neg = (preg_match('/[SW]/', $dms) == 0) ? 1 : -1;
    $dms = preg_replace('/(^\s?-)|(\s?[NSEW]\s?)/i','', $dms);
    $parts = preg_split('/(\d{1,3})[,°d ]?(\d{0,2})(?:[,°d ])[.,\'m ]?(\d{0,2})(?:[.,\'m ])[,"s ]?(\d{0,})(?:[,"s ])?/i', $dms, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE); //TODO: check this for minutes or seconds wih decimals
    if (!$parts) { return; }
    // parts: 0 = degree, 1 = minutes, 2 = seconds
    $d = isset($parts[0]) ? (float)$parts[0] : 0;
    $m = isset($parts[1]) ? (float)$parts[1] : 0;
    if(strpos($dms, ".") > 1 && isset($parts[2])) {
      $m = (float)($parts[1] . '.' . $parts[2]);
      unset($parts[2]);
    }
    $s = isset($parts[2]) ? (float)$parts[2] : 0;
    $dec = ($d + ($m/60) + ($s/3600))*$neg; 
    return $dec;
  }


$coords = array(
  '03° 43\' 02"S',
  '38° 32\' 35"W'
);

foreach($coords as $coord) {
  echo dms_to_deg($coord) . "\n";
}

?>