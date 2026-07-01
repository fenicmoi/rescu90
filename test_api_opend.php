<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1';
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role_id'] = 1;
require 'api_opend_criminal.php';
?>
