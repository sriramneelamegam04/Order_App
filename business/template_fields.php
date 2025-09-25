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
$business_type_id = $input['business_type_id'] ?? null;

// ✅ Input validation
if (empty($business_type_id) || !is_numeric($business_type_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Valid Template ID (business_type_id) required"]);
    exit;
}

// --- FETCH TEMPLATE FIELDS ---
$stmt = $conn->prepare("SELECT field_id, field_name, field_type 
                        FROM template_fields 
                        WHERE business_type_id=?");
$stmt->bind_param("i", $business_type_id);
$stmt->execute();
$res = $stmt->get_result();

$fields = [];
while($row = $res->fetch_assoc()){
    $fields[] = $row;
}

// --- RESPONSE ---
sendSuccess("Template fields fetched", $fields);
?>
