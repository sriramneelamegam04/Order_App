<?php
// orders/payments_option.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../payments/get_credentials.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}


// Accept qr_slug OR user_id
$qr_slug = $_GET['qr_slug'] ?? null;
$target_user_id = intval($_GET['user_id'] ?? 0);

// 🔹 lookup user by qr_slug if provided
if ($qr_slug) {
    $stmt = $conn->prepare("SELECT user_id FROM qr_codes WHERE qr_slug = ? LIMIT 1");
    $stmt->bind_param("s", $qr_slug);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$r) {
        http_response_code(404);
        echo json_encode(['success' => false, 'msg' => 'QR not found']);
        exit;
    }

    $target_user_id = intval($r['user_id']);
}

// 🔹 require at least user_id
if (!$target_user_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'msg' => 'user_id or qr_slug required']);
    exit;
}

// 🔹 get public payment credentials
$pub = get_public_credential_info($target_user_id);

// 🔹 allowed payment methods
$allowed = ['COD']; // always allow COD
if ($pub && !empty($pub['key']) && $pub['enabled']) {
    $allowed[] = 'UPI';
}

// 🔹 respond
echo json_encode([
    'success' => true,
    'allowed_methods' => $allowed
]);
