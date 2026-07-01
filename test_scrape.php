<?php
$html = file_get_contents('https://data.go.th/dataset/nar_12');
preg_match_all('/resource_id=([a-zA-Z0-9\-]+)/', $html, $matches);
print_r(array_unique($matches[1]));
?>
