<?php
require __DIR__ . '/../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use POST"]);
    exit;
}

$dev_mode = true;

// --- INPUT ---
$input  = json_decode(file_get_contents("php://input"), true);
$mobile = strtolower(trim($input['mobile'] ?? ''));
$otp    = trim($input['otp'] ?? '');
$name   = strtolower(trim($input['name'] ?? ''));

// --- VALIDATION ---
$missing = [];
if (!$mobile) $missing[] = 'mobile';
if (!$otp) $missing[] = 'otp';
if (!$name) $missing[] = 'name';

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "Missing fields: " . implode(', ', $missing)
    ]);
    exit;
}

// --- FETCH LATEST OTP ENTRY ---
$stmt = $conn->prepare("
    SELECT otp_hash, name, expires_at 
    FROM otps 
    WHERE mobile = ? 
    ORDER BY created_at DESC 
    LIMIT 1
");
$stmt->bind_param('s', $mobile);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "OTP expired or invalid"]);
    exit;
}

// --- VERIFY NAME ---
if (strtolower(trim($result['name'])) !== strtolower(trim($name))) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Name does not match our records"]);
    exit;
}

// --- VERIFY OTP ---
$otp_valid = false;
if ($dev_mode && $otp === 'DEV123') {
    $otp_valid = true;
} else {
    $otp_valid = password_verify($otp, $result['otp_hash']);
}

if (!$otp_valid) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Incorrect OTP"]);
    exit;
}

// --- CHECK EXPIRATION ---
if (new DateTime() > new DateTime($result['expires_at'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "OTP expired"]);
    exit;
}

// --- CREATE OR GET USER ---
$stmt = $conn->prepare("SELECT user_id FROM users WHERE mobile=?");
$stmt->bind_param('s', $mobile);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user) {
    $user_id = $user['user_id'];
} else {
    $stmt = $conn->prepare("INSERT INTO users (mobile, name, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param('ss', $mobile, $name);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();
}

// --- CREATE SESSION TOKEN ---
$token = bin2hex(random_bytes(32));
$stmt = $conn->prepare("INSERT INTO sessions (user_id, token, last_activity) VALUES (?, ?, NOW())");
$stmt->bind_param('is', $user_id, $token);
$stmt->execute();
$stmt->close();

// --- RESPONSE ---
echo json_encode([
    "success" => true,
    "token" => $token,
    "expires_in" => 300 // 5 minutes
]);
?>
