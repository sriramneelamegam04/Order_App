<?php
// config/db.php
// MySQL connection using mysqli. Adjust constants as needed.

if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'order_web');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', 3306);
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($conn->connect_errno) {
    error_log("DB Connect error: " . $conn->connect_error);
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Database connection failed"]);
    exit;
}

// ensure UTF8
$conn->set_charset('utf8mb4');
