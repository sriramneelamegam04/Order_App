<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php'; // must set $user_id

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
if ($_SERVER['REQUEST_METHOD'] !== "POST" && $_SERVER['REQUEST_METHOD'] !== "PATCH") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// --- Parse input ---
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['category_id'], $input['name'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing fields: category_id or name"]);
    exit;
}

$category_id = (int)$input['category_id'];
$name        = strtolower(trim($input['name']));

// --- Ensure $user_id is set ---
if (!isset($user_id)) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

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

// --- Ensure category belongs to user & vertical ---
$stmt = $conn->prepare("SELECT category_id FROM categories WHERE category_id=? AND user_id=? AND business_type_id=?");
$stmt->bind_param("iii", $category_id, $user_id, $business_type_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    http_response_code(404);
    echo json_encode(["success" => false, "msg" => "Category not found"]);
    exit;
}

// --- Update category name ---
$stmt = $conn->prepare("UPDATE categories SET name=? WHERE category_id=? AND user_id=? AND business_type_id=?");
$stmt->bind_param("siii", $name, $category_id, $user_id, $business_type_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "msg" => "Category updated successfully",
        "category" => [
            "category_id" => $category_id,
            "name" => $name
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to update category"]);
}
$stmt->close();
