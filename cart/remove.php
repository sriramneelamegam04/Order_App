<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: DELETE, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require '../config/db.php';

// --- Method validation (allow DELETE or POST for flexibility) ---
if (!in_array($_SERVER['REQUEST_METHOD'], ["DELETE"])) {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method. Use DELETE"]);
    exit;
}

// --- Parse input ---
$data = json_decode(file_get_contents("php://input"), true);
$session_id = $data['session_id'] ?? null;
$product_id = $data['product_id'] ?? null; // optional

// --- Input validation ---
if (!$session_id || trim($session_id) === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "session_id required"]);
    exit;
}
if ($product_id !== null && !filter_var($product_id, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid product_id"]);
    exit;
}

// --- Fetch cart ---
$stmt = $conn->prepare("SELECT cart_id FROM carts WHERE session_id=?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    echo json_encode(["success" => false, "msg" => "Cart not found"]);
    exit;
}
$cart_id = (int)$res->fetch_assoc()['cart_id'];
$stmt->close();

// --- Remove item(s) ---
if ($product_id) {
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id=? AND product_id=?");
    $stmt->bind_param("ii", $cart_id, $product_id);
    $stmt->execute();
    $msg = "Product removed from cart";
} else {
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id=?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $msg = "All products removed from cart";
}
$stmt->close();

// --- Response ---
echo json_encode([
    "success" => true,
    "msg" => $msg
]);
