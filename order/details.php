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
require '../config/crypto.php'; // for decrypting keys if needed
require_once __DIR__ . '/../payments/crypto_helpers.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}


$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    echo json_encode(["success" => false, "msg" => "order_id required"]);
    exit;
}

// 🔹 fetch order + owner + QR info
$stmt = $conn->prepare("
    SELECT o.order_id, o.qr_id, o.customer_name, o.customer_mobile, o.status, o.total, o.payment_method, o.razorpay_order_id, o.created_at,
           u.name as owner_name,
           q.qr_slug
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    JOIN qr_codes q ON o.qr_id = q.qr_id
    WHERE o.order_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["success" => false, "msg" => "Order not found"]);
    exit;
}

$order = $res->fetch_assoc();

// 🔹 fetch order items
$stmt = $conn->prepare("
    SELECT oi.item_id, oi.product_id, p.name, oi.qty, oi.subtotal
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total_items = 0;
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
    $total_items += $row['qty'];
}

$order['items'] = $items;
$order['total_items'] = $total_items;

// 🔹 if payment is UPI/Razorpay, fetch owner's encrypted keys
if ($order['payment_method'] != "COD" && !empty($order['qr_id'])) {
    $owner_id_stmt = $conn->prepare("SELECT user_id FROM qr_codes WHERE qr_id=? LIMIT 1");
    $owner_id_stmt->bind_param("i", $order['qr_id']);
    $owner_id_stmt->execute();
    $owner_res = $owner_id_stmt->get_result();
    if ($owner_res->num_rows) {
        $owner_id = $owner_res->fetch_assoc()['user_id'];

        $stmt = $conn->prepare("SELECT encrypted_key, encrypted_secret, iv, payments_enabled FROM payment_credentials WHERE user_id=? LIMIT 1");
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $cred_res = $stmt->get_result();

        if ($cred_res->num_rows) {
            $cred = $cred_res->fetch_assoc();
            if ($cred['payments_enabled']) {
                list($iv_key_b64, $iv_secret_b64) = explode("::", $cred['iv']);
                $razorpay_key_id = decrypt_secret_base64($cred['encrypted_key'], $iv_key_b64);
                $order['razorpay_key'] = $razorpay_key_id;
            }
        }
    }
}

// 🔹 respond
echo json_encode([
    "success" => true,
    "order" => $order
]);
