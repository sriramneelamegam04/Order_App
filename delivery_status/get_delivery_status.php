<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require '../config/db.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid method"]);
    exit;
}

// INPUT
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(["success" => false, "msg" => "order_id required"]);
    exit;
}

// 🔹 Fetch ONLY delivery_status
$stmt = $conn->prepare("
    SELECT delivery_status 
    FROM orders 
    WHERE order_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "msg" => "Invalid order_id"]);
    exit;
}

$row = $res->fetch_assoc();

// OUTPUT → only status
echo json_encode([
    "success" => true,
    "delivery_status" => $row['delivery_status']
]);
?>
