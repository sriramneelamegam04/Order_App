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
require '../auth/middleware.php'; // Auth token check, $user_id available
require '../config/crypto.php';
require_once __DIR__ . '/../payments/crypto_helpers.php';

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}


// 🔹 authenticated user
$owner_id = $user_id;

// 🔹 query params
$search = $_GET['search'] ?? null;         // search by customer_name / mobile
$status = $_GET['status'] ?? null;         // filter by order status
$page = max(1, (int)($_GET['page'] ?? 1)); // pagination
$limit = max(1, min(50, (int)($_GET['limit'] ?? 10))); // items per page
$offset = ($page - 1) * $limit;

// 🔹 base query
$query = "
    SELECT o.order_id, o.qr_id, o.customer_name, o.customer_mobile, o.status, o.total, o.payment_method, o.razorpay_order_id, o.created_at,
           q.qr_slug
    FROM orders o
    JOIN qr_codes q ON o.qr_id = q.qr_id
    WHERE o.user_id = ?
";

$params = [$owner_id];
$types = "i";

// 🔹 search
if ($search) {
    $query .= " AND (o.customer_name LIKE ? OR o.customer_mobile LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

// 🔹 status filter
if ($status) {
    $query .= " AND o.status = ?";
    $params[] = $status;
    $types .= "s";
}

// 🔹 order + pagination
$query .= " ORDER BY o.order_id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// 🔹 prepare statement
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$orders = [];
while ($order = $res->fetch_assoc()) {

    // fetch items
    $stmt_items = $conn->prepare("
        SELECT oi.item_id, oi.product_id, p.name, oi.qty, oi.subtotal
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt_items->bind_param("i", $order['order_id']);
    $stmt_items->execute();
    $res_items = $stmt_items->get_result();

    $items = [];
    $total_items = 0;
    while ($row = $res_items->fetch_assoc()) {
        $items[] = $row;
        $total_items += $row['qty'];
    }

    $order['items'] = $items;
    $order['total_items'] = $total_items;

    // fetch Razorpay key if UPI
    if ($order['payment_method'] != "COD") {
        $stmt_cred = $conn->prepare("
            SELECT encrypted_key, iv, payments_enabled
            FROM payment_credentials
            WHERE user_id = ?
            LIMIT 1
        ");
        $stmt_cred->bind_param("i", $owner_id);
        $stmt_cred->execute();
        $res_cred = $stmt_cred->get_result();
        if ($res_cred->num_rows) {
            $cred = $res_cred->fetch_assoc();
            if ($cred['payments_enabled']) {
                list($iv_key_b64, $iv_secret_b64) = explode("::", $cred['iv']);
                $order['razorpay_key'] = decrypt_secret_base64($cred['encrypted_key'], $iv_key_b64);
            }
        }
    }

    $orders[] = $order;
}

// 🔹 total count for pagination
$countQuery = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$countParams = [$owner_id];
$countTypes = "i";

if ($search) {
    $countQuery .= " AND (customer_name LIKE ? OR customer_mobile LIKE ?)";
    $countParams[] = $searchTerm;
    $countParams[] = $searchTerm;
    $countTypes .= "ss";
}
if ($status) {
    $countQuery .= " AND status = ?";
    $countParams[] = $status;
    $countTypes .= "s";
}

$stmt_count = $conn->prepare($countQuery);
$stmt_count->bind_param($countTypes, ...$countParams);
$stmt_count->execute();
$total_orders = $stmt_count->get_result()->fetch_assoc()['total'];

// 🔹 respond
echo json_encode([
    "success" => true,
    "page" => $page,
    "limit" => $limit,
    "total_orders" => (int)$total_orders,
    "orders" => $orders
]);
