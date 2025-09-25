<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}
require '../config/db.php';
require '../auth/middleware.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$qr_id = $data['qr_id'] ?? null;

if(!$qr_id){
    echo json_encode(["success" => false, "msg" => "qr_id required"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM qr_codes WHERE qr_id=? AND user_id=?");
$stmt->bind_param("ii", $qr_id, $user_id);
$stmt->execute();

if($stmt->affected_rows > 0){
    echo json_encode(["success" => true, "msg" => "QR deleted"]);
}else{
    echo json_encode(["success" => false, "msg" => "QR not found or unauthorized"]);
}
