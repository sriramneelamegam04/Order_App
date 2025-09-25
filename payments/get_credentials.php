<?php
// payments/get_credentials.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/crypto_helpers.php';


// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST , GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

/**
 * Store or update credentials for a user
 */
function store_credentials($user_id, $rzp_key, $rzp_secret, $enabled = 1) {
    global $conn;
    $enc1 = encrypt_secret_base64($rzp_key);
    $enc2 = encrypt_secret_base64($rzp_secret);

    $iv_combined_b64 = $enc1['iv_b64'] . '::' . $enc2['iv_b64'];
    $owner_key_hash = hash('sha256', $rzp_key);

    $sql = "INSERT INTO payment_credentials (user_id, encrypted_key, encrypted_secret, iv, owner_key_hash, payments_enabled, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE encrypted_key = VALUES(encrypted_key),
                                    encrypted_secret = VALUES(encrypted_secret),
                                    iv = VALUES(iv),
                                    owner_key_hash = VALUES(owner_key_hash),
                                    payments_enabled = VALUES(payments_enabled),
                                    created_at = VALUES(created_at)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("DB prepare failed: " . $conn->error);
    $stmt->bind_param("isssis", $user_id, $enc1['ciphertext_b64'], $enc2['ciphertext_b64'], $iv_combined_b64, $owner_key_hash, $enabled);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/**
 * Get decrypted credentials for a user (server-side only)
 */
function get_credentials($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT encrypted_key, encrypted_secret, iv, payments_enabled FROM payment_credentials WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($enc_key_b64, $enc_secret_b64, $iv_combined_b64, $payments_enabled);
    if (!$stmt->fetch()) { $stmt->close(); return null; }
    $stmt->close();

    $parts = explode('::', $iv_combined_b64);
    $iv_key_b64 = $parts[0] ?? null;
    $iv_secret_b64 = $parts[1] ?? null;

    $key_plain = decrypt_secret_base64($enc_key_b64, $iv_key_b64);
    $secret_plain = decrypt_secret_base64($enc_secret_b64, $iv_secret_b64);

    return ['key' => $key_plain, 'secret' => $secret_plain, 'enabled' => (bool)$payments_enabled];
}

/**
 * Public info for frontend (never return secret)
 */
function get_public_credential_info($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT encrypted_key, iv, payments_enabled FROM payment_credentials WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($enc_key_b64, $iv_combined_b64, $payments_enabled);
    if (!$stmt->fetch()) { $stmt->close(); return null; }
    $stmt->close();

    $parts = explode('::', $iv_combined_b64);
    $iv_key_b64 = $parts[0] ?? null;
    $key_plain = decrypt_secret_base64($enc_key_b64, $iv_key_b64);

    return ['key' => $key_plain, 'enabled' => (bool)$payments_enabled];
}

/**
 * -------------------------------
 * API Request Handler
 * -------------------------------
 */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? null;
    $mode = $_GET['mode'] ?? 'public'; // "public" or "full"

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing user_id']);
        exit;
    }

    if ($mode === 'full') {
        $data = get_credentials((int)$user_id);
    } else {
        $data = get_public_credential_info((int)$user_id);
    }

    if (!$data) {
        http_response_code(404);
        echo json_encode(['error' => 'No credentials found']);
    } else {
        echo json_encode($data);
    }
}
