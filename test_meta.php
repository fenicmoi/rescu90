<?php
$resource_id = 'd94948a3-24c9-44e8-8ad9-978656c446d5';
$c = curl_init("https://data.go.th/api/3/action/resource_show?id={$resource_id}");
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
$r = curl_exec($c);
$d = json_decode($r, true);
if (isset($d['result'])) {
    echo "Package ID: " . $d['result']['package_id'] . "\n";
    $pkg_id = $d['result']['package_id'];
    
    $c2 = curl_init("https://data.go.th/api/3/action/package_show?id={$pkg_id}");
    curl_setopt($c2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c2, CURLOPT_SSL_VERIFYPEER, false);
    $r2 = curl_exec($c2);
    $d2 = json_decode($r2, true);
    
    if (isset($d2['result'])) {
        echo "Author: " . $d2['result']['author'] . "\n";
        echo "Frequency: " . $d2['result']['frequency'] . "\n";
        echo "Modified: " . $d2['result']['metadata_modified'] . "\n";
    }
}
?>
