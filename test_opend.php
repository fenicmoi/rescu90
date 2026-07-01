<?php 
$url = "https://opend.data.go.th/get-ckan/datastore_search?resource_id=d94948a3-24c9-44e8-8ad9-978656c446d5&limit=100";
$opts = ["http" => [
    "method" => "GET", 
    "header" => "User-Agent: PHP\r\napi-key: NvNx9mzEsd0xm43yDgokPxmQqVw20VCf\r\n"
]];
$context = stream_context_create($opts);
$result = @file_get_contents($url, false, $context);
if ($result === FALSE) {
    echo "FAILED: ";
    print_r(error_get_last());
} else {
    echo "SUCCESS: " . mb_substr($result, 0, 1000, 'UTF-8');
}
?>
