<?php
require __DIR__ . '/../auth/middleware.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Handle preflight ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Allow only POST ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed"]);
    exit;
}

// --- Must be logged in as staff ---
if (!$is_staff) {
    unauthorized("Staff access only");
}

// --- Validate content type ---
if (stripos($_SERVER["CONTENT_TYPE"] ?? '', "application/json") === false) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Content-Type must be application/json"]);
    exit;
}

// --- Parse and sanitize input ---
$input = json_decode(file_get_contents("php://input"), true);
$order_id = isset($input['order_id']) ? (int)$input['order_id'] : 0;
$is_received = isset($input['is_received']) ? (int)$input['is_received'] : 1;

// --- Basic validation ---
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Valid order_id required"]);
    exit;
}

// --- Verify order belongs to same restaurant (owner) ---
$stmt = $conn->prepare("SELECT order_id, is_received FROM orders WHERE order_id=? AND user_id=? LIMIT 1");
$stmt->bind_param('ii', $order_id, $owner_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    echo json_encode(["success" => false, "msg" => "Order not found or does not belong to this restaurant"]);
    exit;
}

// --- Prevent redundant updates ---
if ($is_received && (int)$order['is_received'] === 1) {
    echo json_encode(["success" => true, "msg" => "Already marked as received"]);
    exit;
}
if (!$is_received && (int)$order['is_received'] === 0) {
    echo json_encode(["success" => true, "msg" => "Already marked as not received"]);
    exit;
}

// --- Update logic ---
if ($is_received === 1) {
    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE orders SET is_received=1, received_by_staff_id=?, received_at=? WHERE order_id=?");
    $stmt->bind_param('isi', $staff_id, $now, $order_id);
    $msg = "Order marked as received successfully";
} else {
    $stmt = $conn->prepare("UPDATE orders SET is_received=0, received_by_staff_id=NULL, received_at=NULL WHERE order_id=?");
    $stmt->bind_param('i', $order_id);
    $msg = "Order marked as not received successfully";
}

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["success" => true, "msg" => $msg]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Database update failed"]);
}
$stmt->close();
