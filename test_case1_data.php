<?php
$url = 'https://opend.data.go.th/get-ckan/datastore_search?resource_id=9ff07b2c-0d0d-41ae-a98f-e6d317711ae1&limit=5';
$opts = ["http" => ["method" => "GET", "header" => "api-key: NvNx9mzEsd0xm43yDgokPxmQqVw20VCf\r\n"]];
$context = stream_context_create($opts);
$result = @file_get_contents($url, false, $context);
echo $result;
?>
