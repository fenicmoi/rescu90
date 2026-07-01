<?php
header('Content-Type: application/json; charset=utf-8');
$c = @file_get_contents('https://data.go.th/api/3/action/package_show?id=case1');
if($c) {
    echo $c;
} else {
    echo "Failed";
}
?>
