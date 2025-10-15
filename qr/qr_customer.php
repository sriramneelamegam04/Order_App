<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require '../config/db.php';
require '../auth/middleware.php'; // gives $user_id

// --- Method check ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// -----------------------------------------------------------------------------
// 🔒 STEP 1: Check subscription status
// -----------------------------------------------------------------------------
$sub_stmt = $conn->prepare("
    SELECT plan, start_date, end_date, status 
    FROM subscriptions 
    WHERE user_id = ? 
    AND status = 'active' 
    AND end_date >= CURDATE()
    LIMIT 1
");
$sub_stmt->bind_param("i", $user_id);
$sub_stmt->execute();
$sub_res = $sub_stmt->get_result();

if ($sub_res->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "msg" => "Please subscribe to access QR data",
        "subscription_required" => true
    ]);
    exit;
}

// -----------------------------------------------------------------------------
// ✅ STEP 2: Continue only if subscription active
// -----------------------------------------------------------------------------
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit  = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// --- Build query ---
$sql = "
    SELECT 
        qc.qr_id,
        qc.qr_slug,
        qc.table_no,
        qc.created_at AS qr_created_at,
        COUNT(DISTINCT qs.scan_id) AS total_scans,
        MAX(qs.scanned_at) AS last_scanned_at
    FROM qr_codes qc
    LEFT JOIN qr_scans qs ON qs.qr_id = qc.qr_id
    WHERE qc.user_id = ?
";

$params = ["i", $user_id];

// --- Apply search filter (table_no) ---
if ($search !== '') {
    $sql .= " AND (qc.table_no LIKE CONCAT('%', ?, '%'))";
    $params[0] .= "s";
    $params[] = $search;
}

$sql .= " GROUP BY qc.qr_id ORDER BY qc.created_at DESC LIMIT ? OFFSET ?";
$params[0] .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
$stmt->bind_param(...$params);
$stmt->execute();
$res = $stmt->get_result();

$qr_data = [];

while ($qr = $res->fetch_assoc()) {
    // --- Get recent customers for this QR ---
    $sub_stmt = $conn->prepare("
        SELECT DISTINCT 
            o.customer_name,
            o.customer_mobile,
            o.created_at AS order_time
        FROM orders o
        WHERE o.qr_id = ?
        AND o.customer_mobile IS NOT NULL
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $sub_stmt->bind_param("i", $qr['qr_id']);
    $sub_stmt->execute();
    $sub_res = $sub_stmt->get_result();

    $customers = [];
    while ($cust = $sub_res->fetch_assoc()) {
        $customers[] = $cust;
    }

    $qr['customers'] = $customers;
    $qr_data[] = $qr;
}

// --- Get total count for pagination ---
$count_sql = "
    SELECT COUNT(*) AS total 
    FROM qr_codes qc 
    WHERE qc.user_id = ?
";
if ($search !== '') {
    $count_sql .= " AND (qc.table_no LIKE CONCAT('%', ?, '%'))";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("is", $user_id, $search);
} else {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $user_id);
}
$count_stmt->execute();
$total_res = $count_stmt->get_result()->fetch_assoc();
$total_rows = $total_res['total'];
$total_pages = ceil($total_rows / $limit);

echo json_encode([
    "success" => true,
    "page" => $page,
    "limit" => $limit,
    "total_pages" => $total_pages,
    "total_rows" => $total_rows,
    "qr_codes" => $qr_data
]);
