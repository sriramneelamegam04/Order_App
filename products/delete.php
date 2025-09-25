<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight check ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST" && $_SERVER['REQUEST_METHOD'] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// --- Input parse ---
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid or missing JSON body"]);
    exit;
}

// --- Required field validation ---
if (!isset($input['product_id']) || $input['product_id'] === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing field: product_id"]);
    exit;
}

// --- Type validation ---
if (!is_numeric($input['product_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid product_id"]);
    exit;
}

$product_id = (int)$input['product_id'];

// --- Fetch user's vertical ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$business_type_id = $res['selected_template_id'] ?? null;
if ($business_type_id === null) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No vertical assigned"]);
    exit;
}

// --- Ensure product belongs to this user & vertical ---
$stmt = $conn->prepare("SELECT product_id FROM products WHERE product_id=? AND user_id=? AND business_type_id=?");
$stmt->bind_param("iii", $product_id, $user_id, $business_type_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    http_response_code(404);
    echo json_encode(["success" => false, "msg" => "Product not found"]);
    exit;
}

// --- Delete product ---
$stmt = $conn->prepare("DELETE FROM products WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->close();

echo json_encode(["success" => true, "msg" => "Product deleted successfully"]);
