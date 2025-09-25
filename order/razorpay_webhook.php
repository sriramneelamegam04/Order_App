<?php
// order_app/orders/razorpay_webhook.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../payments/get_credentials.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}


$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

$data = json_decode($payload, true);
$event = $data['event'] ?? '';
$payment_entity = $data['payload']['payment']['entity'] ?? null;

if (!$payment_entity) { http_response_code(400); echo json_encode(['success'=>false,'msg'=>'No payment entity']); exit; }

$razorpay_order_id   = $payment_entity['order_id'] ?? null;
$razorpay_payment_id = $payment_entity['id'] ?? null;

if (!$razorpay_order_id) { http_response_code(400); echo json_encode(['success'=>false,'msg'=>'No order id']); exit; }

// find corresponding order
$stmt = $conn->prepare("SELECT order_id, user_id FROM orders WHERE razorpay_order_id = ? LIMIT 1");
$stmt->bind_param("s", $razorpay_order_id);
$stmt->execute();
$res = $stmt->get_result();
$orderRow = $res->fetch_assoc();
$stmt->close();
if (!$orderRow) { http_response_code(404); echo json_encode(['success'=>false,'msg'=>'Order not found']); exit; }

$owner_user_id = intval($orderRow['user_id']);
$cred = get_credentials($owner_user_id);
if (!$cred) { http_response_code(500); echo json_encode(['success'=>false,'msg'=>'Owner creds not found']); exit; }

// verify signature using owner's secret
$expected = hash_hmac('sha256', $payload, $cred['secret']);
if (!hash_equals($expected, $sig_header)) {
    http_response_code(400); echo json_encode(['success'=>false,'msg'=>'Invalid signature']); exit;
}

// 🔹 Handle events
switch ($event) {
    case 'payment.captured':
    case 'payment.authorized':
    case 'order.paid':
        $upd = $conn->prepare("UPDATE orders SET status='paid', razorpay_payment_id=? WHERE razorpay_order_id=?");
        $upd->bind_param("ss", $razorpay_payment_id, $razorpay_order_id);
        $upd->execute();
        $upd->close();
        break;

    case 'payment.failed':
        $upd = $conn->prepare("UPDATE orders SET status='failed', razorpay_payment_id=? WHERE razorpay_order_id=?");
        $upd->bind_param("ss", $razorpay_payment_id, $razorpay_order_id);
        $upd->execute();
        $upd->close();
        break;

    case 'refund.processed':
        $upd = $conn->prepare("UPDATE orders SET status='refunded' WHERE razorpay_order_id=?");
        $upd->bind_param("s", $razorpay_order_id);
        $upd->execute();
        $upd->close();
        break;

    case 'refund.failed':
        $upd = $conn->prepare("UPDATE orders SET status='refund_failed' WHERE razorpay_order_id=?");
        $upd->bind_param("s", $razorpay_order_id);
        $upd->execute();
        $upd->close();
        break;

    default:
        // just log
        break;
}

// log webhook
$ins = $conn->prepare("INSERT INTO webhook_logs (event_type, payload, received_at) VALUES (?, ?, NOW())");
$ins->bind_param("ss", $event, $payload);
$ins->execute();
$ins->close();

echo json_encode(['success'=>true, 'handled_event'=>$event]);
