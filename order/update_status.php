<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}
require '../config/db.php';
require '../auth/middleware.php'; // token auth, $user_id available

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$order_id = $data['order_id'] ?? null;
$status = $data['status'] ?? null; // manual override, optional if marking paid

if (!$order_id) {
    echo json_encode(["success" => false, "msg" => "order_id required"]);
    exit;
}

// 🔹 fetch order and validate ownership
$stmt = $conn->prepare("SELECT user_id FROM orders WHERE order_id=? LIMIT 1");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();

if (!$order || $order['user_id'] != $user_id) {
    echo json_encode(["success" => false, "msg" => "Unauthorized or order not found"]);
    exit;
}

// 🔹 validate status
$allowed = ['pending', 'paid', 'cod'];
if (!$status || !in_array($status, $allowed)) {
    echo json_encode(["success" => false, "msg" => "Invalid or missing status"]);
    exit;
}

// 🔹 update order
$stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();

// 🔹 respond
echo json_encode([
    "success" => true,
    "msg" => "Order status updated",
    "order_id" => $order_id,
    "new_status" => $status
]);
