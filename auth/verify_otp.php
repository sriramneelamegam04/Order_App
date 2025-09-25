<?php
require __DIR__ . '/../config/db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH, GET, OPTIONS");
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

$dev_mode = true; // Set false in production

// --- INPUT ---
$input = json_decode(file_get_contents("php://input"), true);
$mobile = strtolower(trim($input['mobile'] ?? ''));
$otp    = trim($input['otp'] ?? '');
$name   = strtolower(trim($input['name'] ?? ''));

// --- VALIDATE INPUT ---
$missing = [];
if (!$mobile) $missing[] = 'mobile';
if (!$otp) $missing[] = 'otp';
if (!$name) $missing[] = 'name';

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing fields: " . implode(', ', $missing)]);
    exit;
}

// --- GET LATEST OTP ---
$stmt = $conn->prepare("SELECT otp_hash, expires_at FROM otps WHERE mobile=? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param('s', $mobile);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "OTP expired or invalid"]);
    exit;
}

// --- CHECK OTP ---
if ($dev_mode && $otp === 'DEV123') {
    $otp_valid = true;
} else {
    $otp_valid = password_verify($otp, $result['otp_hash']);
}

if (!$otp_valid || new DateTime() > new DateTime($result['expires_at'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Incorrect or expired OTP"]);
    exit;
}

// --- CREATE USER IF NEW / UPDATE NAME ---
$stmt = $conn->prepare("SELECT user_id FROM users WHERE mobile=?");
$stmt->bind_param('s', $mobile);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $stmt = $conn->prepare("INSERT INTO users (mobile, name, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('ss', $mobile, $name);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();
} else {
    $user_id = $user['user_id'];
    if ($name) { // update only if name provided
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE user_id=?");
        $stmt->bind_param('si', $name, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// --- CREATE SESSION ---
$token = bin2hex(random_bytes(32));
$stmt = $conn->prepare("INSERT INTO sessions (user_id, token, last_activity) VALUES (?, ?, NOW())");
$stmt->bind_param('is', $user_id, $token);
$stmt->execute();
$stmt->close();

// --- RESPONSE ---
echo json_encode([
    "success" => true,
    "token" => $token,
    "expires_in" => 300
]);
?>
