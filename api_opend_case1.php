<?php
require_once 'auth.php'; // Ensure only logged-in backend users can access this API

header('Content-Type: application/json; charset=utf-8');

// OpenD API Key
$api_key = 'NvNx9mzEsd0xm43yDgokPxmQqVw20VCf';
$resource_id = '9ff07b2c-0d0d-41ae-a98f-e6d317711ae1';

// Parameters
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$query = isset($_GET['q']) ? urlencode(trim($_GET['q'])) : '';
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build URL
$url = "https://opend.data.go.th/get-ckan/datastore_search?resource_id={$resource_id}&limit={$limit}&offset={$offset}";
if (!empty($query)) {
    $url .= "&q={$query}";
}

// cURL Request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "api-key: {$api_key}",
    "Accept: application/json"
]);

// Ignore SSL verification for local dev if needed (WAMP), but better to keep it secure
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code == 200 && $response) {
    $data = json_decode($response, true);
    
    // Fetch Metadata
    $meta = [
        'author' => 'ไม่ระบุ',
        'frequency' => 'ไม่ระบุ',
        'last_update' => 'ไม่ระบุ'
    ];
    
    $c = curl_init("https://data.go.th/api/3/action/resource_show?id={$resource_id}");
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    $r = curl_exec($c);
    curl_close($c);
    $d = json_decode($r, true);
    
    if (isset($d['result']['package_id'])) {
        $pkg_id = $d['result']['package_id'];
        $c2 = curl_init("https://data.go.th/api/3/action/package_show?id={$pkg_id}");
        curl_setopt($c2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c2, CURLOPT_SSL_VERIFYPEER, false);
        $r2 = curl_exec($c2);
        curl_close($c2);
        $d2 = json_decode($r2, true);
        if (isset($d2['result'])) {
            $meta['author'] = !empty($d2['result']['author']) ? $d2['result']['author'] : (!empty($d2['result']['maintainer']) ? $d2['result']['maintainer'] : 'ไม่ระบุ');
            $meta['frequency'] = !empty($d2['result']['frequency']) ? $d2['result']['frequency'] : 'ไม่ระบุ (อัปเดตเมื่อมีการเปลี่ยนแปลง)';
            $meta['last_update'] = !empty($d2['result']['metadata_modified']) ? date('d/m/Y H:i', strtotime($d2['result']['metadata_modified'])) : 'ไม่ระบุ';
        }
    }
    
    $data['metadata'] = $meta;
    echo json_encode($data);
} else {
    http_response_code(500);
    error_log("OpenD API Error: HTTP Code {$http_code}, cURL Error: {$error}");
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch data from OpenD API',
        'error' => $error,
        'http_code' => $http_code
    ]);
}
?>
