<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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


// --- INPUT ---
$input = json_decode(file_get_contents("php://input"), true);
$templateId = isset($input['business_type_id']) ? (int)$input['business_type_id'] : 0;

// ✅ Input validation
if ($templateId <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Template ID required"]);
    exit;
}

// --- Validate template exists ---
$stmt = $conn->prepare("SELECT business_type_id FROM business_types WHERE business_type_id=?");
$stmt->bind_param("i", $templateId);
$stmt->execute();
$templateExists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$templateExists) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid template"]);
    exit;
}

// --- Fetch user's current selection ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ✅ Rule enforcement: one subscription = one template/custom vertical
if (!empty($userData['selected_template_id']) && $userData['selected_template_id'] != $templateId) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "You already selected another template. One subscription = one template or custom vertical only."
    ]);
    exit;
}

// --- Save template to user ---
$stmt = $conn->prepare("UPDATE users SET selected_template_id=? WHERE user_id=?");
$stmt->bind_param("ii", $templateId, $user_id);
$stmt->execute();
$stmt->close();

// --- RESPONSE ---
echo json_encode([
    "success" => true,
    "msg" => "Template selected successfully",
    "data" => ["template_id" => $templateId]
]);
?>
