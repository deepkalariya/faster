<?php
if (!isset($_SESSION['invoice_login'])) {
    unset($_SESSION['invoice_login']);
    header('location: login.php');
    die;
}
if (!is_dir('invoice/')) {
    mkdir('invoice/', 0755);
}
?>