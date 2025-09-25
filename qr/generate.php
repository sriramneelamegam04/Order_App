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
require '../libs/phpqrcode/qrlib.php'; // PHP QR Code library

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

$user_id = $user_id; // from middleware
$data = json_decode(file_get_contents("php://input"), true);
$table_no = $data['table_no'] ?? null;

if(!$table_no){
    echo json_encode(["success"=>false,"msg"=>"table_no required"]);
    exit;
}

// unique slug generate
$slug = bin2hex(random_bytes(5));

// Insert QR record in DB
$stmt = $conn->prepare("INSERT INTO qr_codes (user_id, business_type_id, qr_slug, table_no) 
                        VALUES (?, (SELECT selected_template_id FROM users WHERE user_id=?), ?, ?)");
$stmt->bind_param("iiss", $user_id, $user_id, $slug, $table_no);
$stmt->execute();
$qr_id = $stmt->insert_id;

// Generate QR image
$qr_path = '../qr_images/';
if(!file_exists($qr_path)) mkdir($qr_path, 0755, true);

$qr_file = $qr_path . $slug . '.png';
QRcode::png($slug, $qr_file, QR_ECLEVEL_L, 5);

// Return response with QR image URL
echo json_encode([
    "success" => true,
    "msg" => "QR created",
    "qr_id" => $qr_id,
    "slug" => $slug,
    "table_no" => $table_no,
    "qr_image_url" => "http://your-domain.com/qr_images/" . $slug . ".png"
]);
