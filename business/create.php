<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ✅ Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use POST"]);
    exit;
}

// ✅ Authenticate user
$user = get_authenticated_user(); // from middleware.php
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}


// --- CHECK IF USER ALREADY SELECTED A TEMPLATE ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!empty($userData['selected_template_id'])) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "msg" => "Cannot create a custom vertical after selecting a template"
    ]);
    exit;
}

// --- INPUT ---
$input = json_decode(file_get_contents("php://input"), true);
$name = strtolower(trim($input['name'] ?? ''));
$description = strtolower(trim($input['description'] ?? ''));

// ✅ Input validation
if (!$name) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Vertical name required"]);
    exit;
}

// --- INSERT CUSTOM VERTICAL ---
$stmt = $conn->prepare(
    "INSERT INTO business_types (org_id, name, description, is_system, created_at)
     VALUES (?, ?, ?, 0, NOW())"
);
$stmt->bind_param('iss', $user_id, $name, $description);
$stmt->execute();
$newId = $stmt->insert_id;
$stmt->close();

// --- RESPONSE ---
sendSuccess("Custom vertical created", [
    "business_type_id" => $newId
]);
?>
