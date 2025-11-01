<?php
require '../config/db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success"=>false, "msg"=>"Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$mobile = trim($data['mobile'] ?? '');
$slug = trim($data['slug'] ?? '');

if (!$mobile || !$slug) {
    http_response_code(400);
    echo json_encode(["success"=>false, "msg"=>"Mobile and slug required"]);
    exit;
}

// ✅ Validate QR
$stmt = $conn->prepare("SELECT qr_id FROM qr_codes WHERE qr_slug=? LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$q = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$q) {
    http_response_code(404);
    echo json_encode(["success"=>false, "msg"=>"Invalid QR"]);
    exit;
}

// ✅ Generate OTP (valid 5 mins)
$otp = rand(1000, 9999);
$expires_at = date('Y-m-d H:i:s', time() + 300);

// ✅ Store OTP
$stmt2 = $conn->prepare("
    INSERT INTO otp_sessions (mobile, slug, otp, expires_at)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE otp=?, expires_at=?");
$stmt2->bind_param('ssisss', $mobile, $slug, $otp, $expires_at, $otp, $expires_at);
$stmt2->execute();
$stmt2->close();

echo json_encode([
    "success" => true,
    "msg" => "OTP sent successfully",
    "demo_otp" => $otp // remove this in production
]);
?>
