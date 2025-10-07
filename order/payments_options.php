<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../payments/get_credentials.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "msg"     => "THULASI"
    ]);
    exit;   
}

// Read JSON body
$input = json_decode(file_get_contents("php://input"), true) ?? [];

// Extract input values
$qr_slug = $input['qr_slug'] ?? null;
$user_id = intval($input['user_id'] ?? 0);

$target_user_id = 0;

// Lookup user_id using qr_slug (if provided)
if ($qr_slug) {
    $stmt = $conn->prepare("SELECT user_id FROM qr_codes WHERE qr_slug = ? LIMIT 1");
    $stmt->bind_param("s", $qr_slug);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$r) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'msg'     => 'QR not found'
        ]);
        exit;
    }

    $target_user_id = intval($r['user_id']);
} elseif ($user_id > 0) {
    $target_user_id = $user_id;
}

// Validate user_id / qr_slug
if (!$target_user_id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'msg'     => 'user_id or qr_slug required'
    ]);
    exit;
}

// Get public payment credentials
$pub = get_public_credential_info($target_user_id);

// Allowed payment methods
$allowed = ['COD']; // Always allow COD
if ($pub && !empty($pub['key']) && $pub['enabled']) {
    $allowed[] = 'UPI';
}

// Respond with allowed methods
http_response_code(200);
echo json_encode([
    'success'         => true,
    'allowed_methods' => $allowed
]);
