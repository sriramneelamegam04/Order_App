<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method. Only POST allowed"]);
    exit;
}

require '../config/db.php';

// --- Parse body ---
$data = json_decode(file_get_contents("php://input"), true);
$session_id = $data['session_id'] ?? null;
$products   = $data['products'] ?? null; // expected: [{"product_id":1,"qty":2}, ...]

// --- Validate input ---
if (!$session_id || !$products || !is_array($products)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "session_id and products array required"]);
    exit;
}

// --- Ensure cart exists ---
$stmt = $conn->prepare("SELECT cart_id FROM carts WHERE session_id=?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    $stmt2 = $conn->prepare("INSERT INTO carts (session_id) VALUES (?)");
    $stmt2->bind_param("s", $session_id);
    $stmt2->execute();
    $cart_id = $stmt2->insert_id;
    $stmt2->close();
} else {
    $cart_id = $res->fetch_assoc()['cart_id'];
}
$stmt->close();

// --- Loop through products ---
$added_products = [];
foreach ($products as $item) {
    $product_id = $item['product_id'] ?? null;
    $qty        = $item['qty'] ?? null;

    // --- Validate each product ---
    if (!$product_id || !$qty || !is_numeric($qty) || $qty <= 0) {
        continue; // skip invalid entry
    }

    // --- Get product details ---
    $stmt = $conn->prepare("SELECT name, price FROM products WHERE product_id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows == 0) {
        $stmt->close();
        continue; // product not found
    }
    $product = $res->fetch_assoc();
    $stmt->close();

    $subtotal = $product['price'] * $qty;

    // --- Insert / update cart_items ---
    $stmt = $conn->prepare("SELECT cart_item_id FROM cart_items WHERE cart_id=? AND product_id=?");
    $stmt->bind_param("ii", $cart_id, $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        $stmt2 = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, qty, subtotal) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("iiid", $cart_id, $product_id, $qty, $subtotal);
        $stmt2->execute();
        $stmt2->close();
    } else {
        $stmt2 = $conn->prepare("UPDATE cart_items SET qty=?, subtotal=? WHERE cart_id=? AND product_id=?");
        $stmt2->bind_param("idii", $qty, $subtotal, $cart_id, $product_id);
        $stmt2->execute();
        $stmt2->close();
    }
    $stmt->close();

    $added_products[] = [
        "product_id" => $product_id,
        "name"       => $product['name'],
        "qty"        => (int)$qty,
        "subtotal"   => (float)$subtotal
    ];
}

// --- Response ---
echo json_encode([
    "success" => true,
    "cart_id" => $cart_id,
    "added_products" => $added_products
]);
