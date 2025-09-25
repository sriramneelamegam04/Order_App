<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

// --- Validate required fields ---
if (
    !$input ||
    !isset($input['product_id'], $input['name'], $input['price'], $input['unit'])
) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing fields"]);
    exit;
}

// --- Sanitize & lowercase strings ---
$product_id = (int)$input['product_id'];
$name       = strtolower(trim($input['name']));
$unit       = strtolower(trim($input['unit']));
$price      = (float)$input['price'];

// --- Validate price ---
if ($price < 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid price"]);
    exit;
}

// --- Fetch user's vertical ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$business_type_id = $res['selected_template_id'] ?? null;
if ($business_type_id == null) {
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

// --- Update product ---
$stmt = $conn->prepare("UPDATE products SET name=?, price=?, unit=? WHERE product_id=?");
$stmt->bind_param("sdsi", $name, $price, $unit, $product_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "msg" => "Product updated successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "DB update failed"]);
}
$stmt->close();
