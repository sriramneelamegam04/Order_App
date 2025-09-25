<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method. Only GET allowed"]);
    exit;
}

require '../config/db.php';

// --- Input validation ---
$session_id = $_GET['session_id'] ?? null;
if (!$session_id || trim($session_id) === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "session_id required"]);
    exit;
}

// --- Check cart by session_id ---
$stmt = $conn->prepare("SELECT cart_id FROM carts WHERE session_id=?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode([
        "success" => true,
        "cart" => [
            "items" => [],
            "total" => 0
        ]
    ]);
    exit;
}
$cart_id = $res->fetch_assoc()['cart_id'];
$stmt->close();

// --- Get cart items ---
$stmt = $conn->prepare("
    SELECT ci.cart_item_id, ci.product_id, p.name, ci.qty, ci.subtotal 
    FROM cart_items ci 
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_id=?
");
$stmt->bind_param("i", $cart_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $res->fetch_assoc()) {
    $row['qty'] = (int)$row['qty'];           // ensure integer
    $row['subtotal'] = (float)$row['subtotal']; // ensure float
    $items[] = $row;
    $total += $row['subtotal'];
}
$stmt->close();

// --- Response ---
echo json_encode([
    "success" => true,
    "cart" => [
        "cart_id" => $cart_id,
        "items" => $items,
        "total" => (float)$total
    ]
]);
