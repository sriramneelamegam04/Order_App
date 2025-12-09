<?php
require realpath(__DIR__ . '/../auth/middleware.php');
require realpath(__DIR__ . '/../config/db.php');

header("Content-Type: application/json");

// 1. AUTH CHECK (owner or staff)
$user = get_authenticated_user();
if (!$user) {
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

// Identify owner_id
if ($user['type'] === "owner") {
    $owner_id = (int)$user['id'];   // Owner logged in
} else {
    $owner_id = (int)$user['owner_id']; // Staff logged in → Their owner
}

// 2. Read input
$data = json_decode(file_get_contents("php://input"), true);

$order_id = $data['order_id'] ?? null;
$new_status = $data['status'] ?? null;

$allowed = [
    "placed", "accepted", "preparing", "ready",
    "on_the_way", "delivered", "cancelled"
];

if (!$order_id || !$new_status) {
    echo json_encode(["success" => false, "msg" => "order_id and status required"]);
    exit;
}

if (!in_array($new_status, $allowed)) {
    echo json_encode(["success" => false, "msg" => "Invalid status"]);
    exit;
}

// 3. Get qr_id from orders
$stmt = $conn->prepare("
    SELECT qr_id 
    FROM orders 
    WHERE order_id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "msg" => "Order not found"]);
    exit;
}

$order_data = $res->fetch_assoc();
$qr_id = (int)$order_data['qr_id'];

// 4. Check owner of QR code
$stmt = $conn->prepare("
    SELECT user_id 
    FROM qr_codes 
    WHERE qr_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $qr_id);
$stmt->execute();
$res2 = $stmt->get_result();

if ($res2->num_rows === 0) {
    echo json_encode(["success" => false, "msg" => "QR not found"]);
    exit;
}

$qr_data = $res2->fetch_assoc();
$order_owner = (int)$qr_data['user_id'];

// 5. Compare owner
if ($order_owner !== $owner_id) {
    echo json_encode([
        "success" => false,
        "msg" => "Access denied: You cannot update other owner's orders"
    ]);
    exit;
}

// 6. Update order status
$stmt = $conn->prepare("
    UPDATE orders 
    SET delivery_status = ?
    WHERE order_id = ?
");
$stmt->bind_param("si", $new_status, $order_id);
$stmt->execute();

// 7. Return success
echo json_encode([
    "success" => true,
    "msg" => "Delivery status updated successfully",
    "order_id" => $order_id,
    "new_status" => $new_status
]);
?>
