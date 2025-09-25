<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php'; // ensures $user_id + $token

// Delete session
$stmt = $conn->prepare("DELETE FROM sessions WHERE token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->close();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
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

echo json_encode(["success"=>true,"msg"=>"Logged out"]);
