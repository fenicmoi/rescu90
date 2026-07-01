<?php
$c = curl_init('https://data.go.th/api/3/action/package_show?id=nar_12');
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
$r = curl_exec($c);
$d = json_decode($r, true);
if (isset($d['result'])) {
    echo "Author: " . $d['result']['author'] . "\n";
    echo "Maintainer: " . $d['result']['maintainer'] . "\n";
    echo "Frequency: " . $d['result']['frequency'] . "\n";
    echo "Last Update: " . $d['result']['metadata_modified'] . "\n";
} else {
    echo "No result";
}
?>
