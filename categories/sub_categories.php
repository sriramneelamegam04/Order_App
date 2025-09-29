<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php'; // must set $user_id

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight check ---
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

// --- Input parse ---
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid or missing JSON body"]);
    exit;
}

// --- Required fields ---
$required = ["category_id", "name"];
$missing = [];

foreach ($required as $field) {
    if (!isset($input[$field]) || trim($input[$field]) === "") {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "Missing fields: " . implode(", ", $missing)
    ]);
    exit;
}

// --- Sanitize ---
$category_id = (int)$input['category_id'];
$name = strtolower(trim($input['name']));

// --- Ensure $user_id is set ---
if (!isset($user_id)) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

// --- Validate Category belongs to user ---
$stmt = $conn->prepare("SELECT category_id FROM categories WHERE category_id=? AND user_id=?");
$stmt->bind_param("ii", $category_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "Invalid category or not yours"]);
    exit;
}

// --- Insert Subcategory ---
$stmt = $conn->prepare("
    INSERT INTO subcategories (category_id, name, created_at) 
    VALUES (?, ?, NOW())
");
$stmt->bind_param("is", $category_id, $name);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "msg" => "Subcategory added successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to add subcategory"]);
}
$stmt->close();
