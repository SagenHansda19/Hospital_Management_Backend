<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "hospital_management";

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Test connection immediately
    $conn->query("SELECT 1");
} catch(PDOException $e) {
    die(json_encode([
        'success' => false,
        'error' => "Database connection failed",
        'message' => $e->getMessage()
    ]));
}
?>