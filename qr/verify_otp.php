<?php
require '../config/db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success"=>false, "msg"=>"Invalid method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$slug = trim($data['slug'] ?? '');
$mobile = trim($data['mobile'] ?? '');
$otp = trim($data['otp'] ?? '');
$name = strtolower(trim($data['name'] ?? ''));

if (!$slug || !$mobile || !$otp || !$name) {
    http_response_code(400);
    echo json_encode(["success"=>false,"msg"=>"slug, mobile, name, otp required"]);
    exit;
}

// ✅ Validate OTP
$stmt = $conn->prepare("SELECT * FROM otp_sessions WHERE mobile=? AND slug=? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $mobile, $slug);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || $row['otp'] != $otp) {
    http_response_code(401);
    echo json_encode(["success"=>false,"msg"=>"Invalid OTP"]);
    exit;
}
if (strtotime($row['expires_at']) < time()) {
    http_response_code(410);
    echo json_encode(["success"=>false,"msg"=>"OTP expired"]);
    exit;
}

// ✅ Create session
$session_id = uniqid('sess_', true);
$stmt2 = $conn->prepare("INSERT INTO qr_sessions (session_id, slug, mobile, customer_name) VALUES (?, ?, ?, ?)");
$stmt2->bind_param("ssss", $session_id, $slug, $mobile, $name);
$stmt2->execute();
$stmt2->close();

echo json_encode([
    "success" => true,
    "msg" => "OTP verified successfully",
    "session_id" => $session_id
]);
?>
