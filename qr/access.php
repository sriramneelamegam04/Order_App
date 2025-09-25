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

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// get input data
$data = json_decode(file_get_contents("php://input"), true);
$slug   = $data['slug']   ?? null;
$name   = isset($data['name']) ? strtolower(trim($data['name'])) : null;
$mobile = $data['mobile'] ?? null;

if (!$slug || !$name || !$mobile) {
    echo json_encode(["success" => false, "msg" => "slug, name, mobile required"]);
    exit;
}

// validate QR
$stmt = $conn->prepare("
    SELECT q.qr_id, q.table_no, q.business_type_id, u.name as owner_name, b.name as business_type
    FROM qr_codes q
    JOIN users u ON q.user_id = u.user_id
    JOIN business_types b ON q.business_type_id = b.business_type_id
    WHERE q.qr_slug = ?
");
$stmt->bind_param("s", $slug);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["success" => false, "msg" => "Invalid QR"]);
    exit;
}

$qr = $res->fetch_assoc();

// generate unique session_id for cart
$session_id = uniqid('sess_', true);

// QR image URL
$qr_image_url = "http://your-domain.com/qr_images/" . $slug . ".png";

// create pending order
$stmt2 = $conn->prepare("
    INSERT INTO orders (user_id, qr_id, customer_name, customer_mobile, status, total) 
    VALUES ((SELECT user_id FROM qr_codes WHERE qr_id = ?), ?, ?, ?, 'pending', 0)
");
$stmt2->bind_param("iiss", $qr['qr_id'], $qr['qr_id'], $name, $mobile);
$stmt2->execute();
$order_id = $stmt2->insert_id;

// fetch menu
$stmt3 = $conn->prepare("
    SELECT product_id, name, price, unit 
    FROM products 
    WHERE business_type_id = ?
");
$stmt3->bind_param("i", $qr['business_type_id']);
$stmt3->execute();
$res3 = $stmt3->get_result();
$products = [];
while ($row = $res3->fetch_assoc()) {
    $products[] = $row;
}

// return response with unique session_id
echo json_encode([
    "success" => true,
    "session_id" => $session_id,
    "qr_info" => [
        "qr_id" => $qr['qr_id'],
        "table_no" => $qr['table_no'],
        "owner_name" => $qr['owner_name'],
        "business_type" => $qr['business_type'],
        "qr_image_url" => $qr_image_url
    ],
    "order" => [
        "order_id" => $order_id,
        "status" => "pending",
        "customer_name" => $name,
        "customer_mobile" => $mobile
    ],
    "menu" => $products
]);
