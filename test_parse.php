<?php
$data = json_decode(file_get_contents('opend_nar12.json'), true);
if (isset($data['response']['result']['fields'])) {
    print_r($data['response']['result']['fields']);
} else {
    echo "No fields found";
}
?>
