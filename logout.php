<?php
include_once './connection.php';
unset($_SESSION['invoice_login']);
header('location: login.php');
die;
?>
