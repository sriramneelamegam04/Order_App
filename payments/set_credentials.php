<?php
// payments/set_credential.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Include DB and your auth/middleware.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/middleware.php'; // your existing session/JWT/subscription checks
require_once __DIR__ . '/get_credentials.php';  // functions only


// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}


// 🔒 Auth handled in middleware.php
// $user_id is exposed by middleware.php
if (!isset($user_id)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'msg' => 'Not authenticated']);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
if ($data === null) {
    http_response_code(400);
    echo json_encode(['success' => false, 'msg' => 'Invalid JSON']);
    exit;
}

// Only allow admin to set another user's credentials
$owner_id = $user_id;
if (isset($data['owner_id']) && ($user['role'] ?? '') === 'admin') {
    $owner_id = intval($data['owner_id']);
}

// Get credentials from JSON
$key = trim($data['key'] ?? '');
$secret = trim($data['secret'] ?? '');
$enabled = isset($data['enabled']) ? intval($data['enabled']) : 1;

if (!$key || !$secret) {
    http_response_code(400);
    echo json_encode(['success' => false, 'msg' => 'Provide key and secret']);
    exit;
}

// Store credentials
try {
    $ok = store_credentials($owner_id, $key, $secret, $enabled);
    if ($ok) {
        echo json_encode(['success' => true, 'msg' => 'Credentials saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'msg' => 'Failed to save credentials']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'msg' => 'Error', 'error' => $e->getMessage()]);
}
