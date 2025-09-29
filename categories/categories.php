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
$required = ["name"];
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
$name = strtolower(trim($input['name']));

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

// --- Insert Category ---
$stmt = $conn->prepare("
    INSERT INTO categories (user_id, business_type_id, name, created_at) 
    VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("iis", $user_id, $business_type_id, $name);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "msg" => "Category added successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to add category"]);
}
$stmt->close();
