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
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

$stmt = $conn->prepare("SELECT qr_id, qr_slug, table_no, qr_image_url, created_at ,
                        FROM qr_codes 
                        WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "qr_codes" => $data
]);
