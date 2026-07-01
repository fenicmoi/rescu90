<?php
require 'db_config.php';
try {
    $res = $pdo->query("SHOW TABLES LIKE '%cctv%'");
    $tables = $res->fetchAll(PDO::FETCH_COLUMN);
    print_r($tables);
} catch (Exception $e) {
    echo $e->getMessage();
}
