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
$user = get_authenticated_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

// --- INPUT ---
$input = json_decode(file_get_contents("php://input"), true);
$business_type_id = isset($input['business_type_id']) ? (int)$input['business_type_id'] : 0;
$field_name = strtolower(trim($input['field_name'] ?? ''));
$field_type = strtolower(trim($input['field_type'] ?? 'text'));

// ✅ Input validation
$missing = [];
if ($business_type_id <= 0) $missing[] = 'business_type_id';
if (!$field_name) $missing[] = 'field_name';

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "Missing or invalid fields: " . implode(', ', $missing)
    ]);
    exit;
}

// --- VERIFY ACCESS ---
$stmt = $conn->prepare(
    "SELECT COUNT(*) as cnt 
     FROM business_types 
     WHERE business_type_id=? AND (is_system=1 OR org_id=?)"
);
$stmt->bind_param('ii', $business_type_id, $user_id);
$stmt->execute();
$cnt = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

if ($cnt == 0) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "Access denied for this vertical"]);
    exit;
}

// --- INSERT NEW FIELD ---
$stmt = $conn->prepare(
    "INSERT INTO template_fields (business_type_id, field_name, field_type) 
     VALUES (?, ?, ?)"
);
$stmt->bind_param('iss', $business_type_id, $field_name, $field_type);
$stmt->execute();
$newId = $stmt->insert_id;
$stmt->close();

// --- RESPONSE ---
sendSuccess("Field added successfully", [
    "field_id" => $newId
]);
?>
