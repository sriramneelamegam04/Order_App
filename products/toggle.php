<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight check ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Method validation ---
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PATCH'])) {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// --- Read input ---
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['product_id'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing product_id"]);
    exit;
}

$product_id = (int)$data['product_id'];
$new_status = isset($data['is_active']) ? (int)$data['is_active'] : null;

if ($new_status !== 0 && $new_status !== 1) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid is_active value (use 0 or 1)"]);
    exit;
}

// --- Validate product ownership ---
$stmt = $conn->prepare("SELECT product_id FROM products WHERE product_id=? AND user_id=?");
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "msg" => "Product not found or access denied"]);
    exit;
}
$stmt->close();

// --- Update status ---
$update = $conn->prepare("UPDATE products SET is_active=?, updated_at=NOW() WHERE product_id=? AND user_id=?");
$update->bind_param("iii", $new_status, $product_id, $user_id);
$success = $update->execute();
$update->close();

if ($success) {
    echo json_encode([
        "success" => true,
        "msg" => "Product " . ($new_status ? "activated" : "deactivated") . " successfully",
        "product_id" => $product_id,
        "is_active" => $new_status
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to update product status"]);
}
