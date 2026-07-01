<?php
header('Content-Type: application/json; charset=utf-8');

$api_key = 'NvNx9mzEsd0xm43yDgokPxmQqVw20VCf';
$resource_id = '45bd1bec-f64e-4ecc-a5dd-e509d6d9a518';
$url = "https://opend.data.go.th/get-ckan/datastore_search?resource_id={$resource_id}&limit=10";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "api-key: {$api_key}",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
// Add timeout just in case
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo json_encode([
    'http_code' => $http_code,
    'error' => $error,
    'response' => json_decode($response)
]);
?>
