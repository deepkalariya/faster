<?php
error_reporting(0);
session_start();

//ini_set('max_execution_time', 300);
//ini_set('memory_limit', '1024M');
if (stripos($_SERVER["SCRIPT_NAME"], "/connection.php") > 0)
    die("Restricted access");


date_default_timezone_set('Asia/Calcutta');

// Define variable
define("DB_USERNAME", "root");
define("DB_PASSWORD", "");
define("DB_HOSTNAME", "localhost");
define("DB_DATABASE_1", "faster");

// Database connection
try {
    $db1 = new PDO('mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE_1, DB_USERNAME, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $ex) {
    die(json_encode(array('outcome' => false, 'message' => 'Unable to connect => ' . DB_DATABASE_1)));
}
$currency = "Rs.";
function sanitize_name($name)
{
    $name = str_replace("?", "-", strtolower($name));
    $name = str_replace("!", "-", strtolower($name));
    $name = str_replace("@", "-", strtolower($name));
    $name = str_replace("#", "-", strtolower($name));
    $name = str_replace("$", "-", strtolower($name));
    $name = str_replace("%", "-", strtolower($name));
    $name = str_replace("^", "-", strtolower($name));
    $name = str_replace("&", "-", strtolower($name));
    $name = str_replace("*", "-", strtolower($name));
    $name = str_replace("(", "-", strtolower($name));
    $name = str_replace(")", "-", strtolower($name));
    $name = str_replace("-", "-", strtolower($name));
    $name = str_replace("=", "-", strtolower($name));
    $name = str_replace("+", "-", strtolower($name));
    $name = str_replace("`", "-", strtolower($name));
    $name = str_replace("~", "-", strtolower($name));
    $name = str_replace("|", "-", strtolower($name));
    $name = str_replace(";", "-", strtolower($name));
    $name = str_replace(":", "-", strtolower($name));
    $name = str_replace("'", "-", strtolower($name));
    $name = str_replace("\"", "-", strtolower($name));
    $name = str_replace(",", "-", strtolower($name));
    $name = str_replace("[", "-", strtolower($name));
    $name = str_replace("]", "-", strtolower($name));
    $name = str_replace("{", "-", strtolower($name));
    $name = str_replace("}", "-", strtolower($name));
    $name = str_replace("<", "-", strtolower($name));
    $name = str_replace(">", "-", strtolower($name));
    $name = trim($name);
    return $name;
}

function update_parts_qty($parts_id)
{
    global $db1;
    $stmt = $db1->prepare("SELECT SUM(stock) as added_qty FROM stock_in WHERE parts_id=:parts_id");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":parts_id" => $parts_id));
    $row1 = $stmt->fetch();

    $stmt = $db1->prepare("SELECT SUM(stock) as used_qty FROM stock_out WHERE parts_id=:parts_id");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":parts_id" => $parts_id));
    $row2 = $stmt->fetch();

    $total_qty = $row1['added_qty'] - $row2['used_qty'];

    $stmt = $db1->prepare("UPDATE total_stock SET stock=:stock WHERE id=:id");
    $stmt->execute(array(":stock" => $total_qty, ":id" => $parts_id));

    return $total_qty;
}

function update_pump_qty($item_id)
{
    global $db1;
    $stmt = $db1->prepare("SELECT SUM(qty) as stock FROM complete_items WHERE item_id=:item_id");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":item_id" => $item_id));
    $row1 = $stmt->fetch();

    $stmt = $db1->prepare("SELECT SUM(qty) as sale_qty FROM sale_items WHERE item_id=:item_id");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":item_id" => $item_id));
    $row2 = $stmt->fetch();

    $total_qty = $row1['stock'] - $row2['sale_qty'];

    $stmt = $db1->prepare("INSERT INTO total_items (`item_id`,`qty`) VALUES (:item_id, :qty) ON DUPLICATE KEY UPDATE item_id = :item_id ,qty = :qty");
    $stmt->execute(array(":qty" => $total_qty, ":item_id" => $item_id));

    return $total_qty;
}

function custom_number_formate($value){
    return number_format($value,2);
}
