<?php
require __DIR__ . '/../config/db.php';

header("Content-Type: application/json");

// Method check
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    echo json_encode(["success" => false, "msg" => "Invalid method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$customer_mobile = $data['customer_mobile'] ?? null;

if (!$customer_mobile) {
    echo json_encode(["success" => false, "msg" => "customer_mobile required"]);
    exit;
}

// 1. Get all orders by customer mobile
$stmt = $conn->prepare("
    SELECT order_id, qr_id, total, payment_method, order_type,
           delivery_address, delivery_status, created_at
    FROM orders
    WHERE customer_mobile = ?
    ORDER BY order_id DESC
");
$stmt->bind_param("s", $customer_mobile);
$stmt->execute();
$orders_res = $stmt->get_result();

$order_list = [];

while ($order = $orders_res->fetch_assoc()) {

    $order_id = $order['order_id'];

    // 2. Fetch all order items for this order
    $stmt2 = $conn->prepare("
        SELECT oi.product_id, p.name, oi.qty, oi.subtotal
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();
    $items_res = $stmt2->get_result();

    $items = [];
    while ($item = $items_res->fetch_assoc()) {
        $items[] = $item;
    }

    $order['items'] = $items;  // attach order items
    $order_list[] = $order;
}

echo json_encode([
    "success" => true,
    "customer_mobile" => $customer_mobile,
    "order_history" => $order_list
]);
?>
