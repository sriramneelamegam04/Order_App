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
require '../config/crypto.php'; 
require_once __DIR__ . '/../payments/crypto_helpers.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$qr_slug        = $data['qr_slug'] ?? null;
$session_id     = $data['session_id'] ?? null;
$payment_method = $data['payment_method'] ?? 'COD';

// NEW FIELDS
$order_type       = $data['order_type'] ?? "indoor";  // indoor / outdoor
$delivery_address = $data['delivery_address'] ?? null;

// Outdoor requires address
if ($order_type === "outdoor" && (!$delivery_address || trim($delivery_address) == "")) {
    echo json_encode(["success"=>false,"msg"=>"Delivery address required for outdoor orders"]);
    exit;
}

if(!$qr_slug || !$session_id){
    echo json_encode(["success"=>false,"msg"=>"qr_slug and session_id required"]);
    exit;
}

// 🔹 find qr + owner
$stmt = $conn->prepare("SELECT q.qr_id, q.user_id FROM qr_codes q WHERE q.qr_slug=? LIMIT 1");
$stmt->bind_param("s", $qr_slug);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){
    echo json_encode(["success"=>false,"msg"=>"Invalid QR"]);
    exit;
}
$qr = $res->fetch_assoc();
$owner_id = $qr['user_id'];
$qr_id    = $qr['qr_id'];

// 🔹 get pending order created at access.php
$stmt = $conn->prepare("SELECT order_id, customer_name, customer_mobile FROM orders 
                        WHERE qr_id=? AND status='pending' 
                        ORDER BY order_id DESC LIMIT 1");
$stmt->bind_param("i", $qr_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){
    echo json_encode(["success"=>false,"msg"=>"No pending order found"]);
    exit;
}
$order = $res->fetch_assoc();
$order_id = $order['order_id'];

// 🔹 get cart
$stmt = $conn->prepare("SELECT cart_id FROM carts WHERE session_id=? LIMIT 1");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){
    echo json_encode(["success"=>false,"msg"=>"Cart empty"]);
    exit;
}
$cart_id = $res->fetch_assoc()['cart_id'];

// 🔹 fetch cart items
$stmt = $conn->prepare("SELECT ci.product_id,p.name,ci.qty,ci.subtotal 
                        FROM cart_items ci 
                        JOIN products p ON ci.product_id=p.product_id 
                        WHERE ci.cart_id=?");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$res = $stmt->get_result();

$items=[]; 
$total=0;

while($row=$res->fetch_assoc()){
    $items[]=$row;
    $total += $row['subtotal'];
}

if(empty($items)){
    echo json_encode(["success"=>false,"msg"=>"Cart empty"]);
    exit;
}

// 🔹 update order (now includes order_type + delivery_address)
$stmt = $conn->prepare("UPDATE orders 
    SET total=?, payment_method=?, order_type=?, delivery_address=?
    WHERE order_id=?");

$stmt->bind_param("dsssi", 
    $total, 
    $payment_method,
    $order_type,
    $delivery_address,
    $order_id
);
$stmt->execute();

// 🔹 insert order items
foreach($items as $it){
    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id,product_id,qty,subtotal) VALUES (?,?,?,?)");
    $stmt2->bind_param("iiid", $order_id, $it['product_id'], $it['qty'], $it['subtotal']);
    $stmt2->execute();
}

// 🔹 clear cart
$conn->query("DELETE FROM cart_items WHERE cart_id=$cart_id");

// 🔹 COD handling
if($payment_method == "COD"){
    echo json_encode([
        "success"=>true,
        "order_id"=>$order_id,
        "total"=>$total,
        "payment_method"=>"COD",
        "order_type"=>$order_type,
        "delivery_address"=>$delivery_address,
        "customer_name"=>$order['customer_name'],
        "customer_mobile"=>$order['customer_mobile']
    ]);
    exit;
}

// 🔹 handle UPI / Razorpay
$stmt = $conn->prepare("SELECT encrypted_key, encrypted_secret, iv, payments_enabled 
                        FROM payment_credentials WHERE user_id=? LIMIT 1");
$stmt->bind_param("i",$owner_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows==0){
    echo json_encode(["success"=>false,"msg"=>"Owner has not enabled UPI"]);
    exit;
}

$cred = $res->fetch_assoc();

if(!$cred['payments_enabled']){
    echo json_encode(["success"=>false,"msg"=>"Owner has disabled payments"]);
    exit;
}

// 🔹 decrypt Razorpay key
list($iv_key_b64, $iv_secret_b64) = explode("::", $cred['iv']);

$razorpay_key_id = decrypt_secret_base64($cred['encrypted_key'], $iv_key_b64);
$razorpay_secret = decrypt_secret_base64($cred['encrypted_secret'], $iv_secret_b64);

if(!$razorpay_key_id || !$razorpay_secret){
    echo json_encode(["success"=>false,"msg"=>"Failed to decrypt Razorpay key"]);
    exit;
}

// 🔹 create Razorpay order ID
$razorpay_order_id = "order_".uniqid();
$stmt = $conn->prepare("UPDATE orders SET razorpay_order_id=? WHERE order_id=?");
$stmt->bind_param("si",$razorpay_order_id,$order_id);
$stmt->execute();

// 🔹 send response
echo json_encode([
    "success"=>true,
    "order_id"=>$order_id,
    "total"=>$total,
    "payment_method"=>"UPI",
    "razorpay_order_id"=>$razorpay_order_id,
    "razorpay_key"=>$razorpay_key_id,
    "order_type"=>$order_type,
    "delivery_address"=>$delivery_address,
    "customer_name"=>$order['customer_name'],
    "customer_mobile"=>$order['customer_mobile']
]);
