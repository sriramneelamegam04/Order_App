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

if (!in_array($_SERVER['REQUEST_METHOD'], ["POST"])) {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method. Use POST"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$session_id = $data['session_id'] ?? null;
$product_id = $data['product_id'] ?? null;
$qty        = $data['qty'] ?? null;

if(!$session_id || !$product_id || !$qty){
    echo json_encode(["success"=>false,"msg"=>"session_id, product_id, qty required"]);
    exit;
}

// fetch cart by session_id
$stmt = $conn->prepare("SELECT cart_id FROM carts WHERE session_id=?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows==0){
    echo json_encode(["success"=>false,"msg"=>"Cart not found"]);
    exit;
}
$cart_id = $res->fetch_assoc()['cart_id'];

// fetch product price
$stmt = $conn->prepare("SELECT price FROM products WHERE product_id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows==0){
    echo json_encode(["success"=>false,"msg"=>"Invalid product"]);
    exit;
}
$product = $res->fetch_assoc();
$subtotal = $product['price'] * $qty;

// update cart item
$stmt = $conn->prepare("UPDATE cart_items SET qty=?, subtotal=? WHERE cart_id=? AND product_id=?");
$stmt->bind_param("idii", $qty, $subtotal, $cart_id, $product_id);
$stmt->execute();

echo json_encode([
    "success"=>true,
    "msg"=>"Cart item updated",
    "cart_item" => [
        "product_id" => $product_id,
        "qty" => $qty,
        "subtotal" => $subtotal
    ]
]);
