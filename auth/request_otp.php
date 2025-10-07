<?php
require __DIR__ . '/../config/db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "msg" => "Method Not Allowed. Use POST"
    ]);
    exit;
}

// --- CONFIG ---
$dev_mode = true; // Set false in production

// --- INPUT ---
$input = json_decode(file_get_contents("php://input"), true);

$mobile = trim($input['mobile'] ?? '');
$name = trim($input['name'] ?? '');

// --- VALIDATION ---

if (!$mobile) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "Mobile is required"
    ]);
    exit;
}

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "Invalid mobile number"
    ]);
    exit;
}

if (!$name) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "Name is required"
    ]);
    exit;
}

// --- RATE LIMIT ---
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM otps 
    WHERE mobile = ? 
      AND created_at > (NOW() - INTERVAL 5 MINUTE)
");
$stmt->bind_param('s', $mobile);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count >= 3) {
    http_response_code(429);
    echo json_encode([
        "success" => false,
        "msg" => "Too many OTP requests. Try again after 5 minutes"
    ]);
    exit;
}

// --- GENERATE OTP ---
$otp = random_int(100000, 999999);
$hashedOtp = password_hash((string)$otp, PASSWORD_DEFAULT);

// --- STORE OTP ---
$stmt = $conn->prepare("
    INSERT INTO otps (mobile, otp_hash, expires_at, created_at)
    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), NOW())
");
$stmt->bind_param('ss', $mobile, $hashedOtp);
$stmt->execute();
$stmt->close();

// --- SEND OTP ---
$response = [
    "success" => true,
    "msg" => $dev_mode ? "OTP sent (dev mode)" : "OTP sent"
];

if ($dev_mode) {
    $response['otp_dev'] = $otp;
}

echo json_encode($response);
?>