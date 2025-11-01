<?php
require __DIR__ . '/../auth/middleware.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Handle preflight ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Allow only GET requests ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed"]);
    exit;
}

// --- Must be a staff user ---
if (!$is_staff) {
    unauthorized("Staff login required");
}

// --- Validate pagination params ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// --- Prepare SQL with proper validation ---
try {
    $sql = "SELECT 
                o.order_id,
                q.table_no,
                o.customer_name,
                o.customer_mobile,
                o.total,
                o.payment_method,
                o.status,
                o.is_received,
                o.received_by_staff_id,
                o.received_at,
                o.created_at
            FROM orders o
            LEFT JOIN qr_codes q ON o.qr_id = q.qr_id
            WHERE o.user_id = ? 
              AND o.status = 'pending'
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query prepare failed: " . $conn->error);
    }

    $stmt->bind_param('iii', $owner_id, $limit, $offset);
    $stmt->execute();
    $res = $stmt->get_result();

    $orders = [];
    while ($r = $res->fetch_assoc()) {
        $oid = (int)$r['order_id'];

        // --- Fetch order items safely ---
        $stmt2 = $conn->prepare("
            SELECT 
                p.name AS product_name, 
                oi.qty, 
                (oi.subtotal / oi.qty) AS price
            FROM order_items oi
            JOIN products p ON p.product_id = oi.product_id
            WHERE oi.order_id = ?");
        if ($stmt2) {
            $stmt2->bind_param('i', $oid);
            $stmt2->execute();
            $r['items'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt2->close();
        } else {
            $r['items'] = [];
        }

        $orders[] = $r;
    }
    $stmt->close();

    // --- Return response ---
    if (empty($orders)) {
        echo json_encode(["success" => true, "orders" => [], "msg" => "No pending orders found"]);
    } else {
        echo json_encode(["success" => true, "orders" => $orders]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "msg" => "Internal server error",
        "error" => $e->getMessage()
    ]);
}
