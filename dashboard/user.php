<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../auth/middleware.php";

// Validate method
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// Get authenticated user (owner or staff)
$user = get_authenticated_user();

if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

/* 
-------------------------------------
 FIX AUTH LOGIC
-------------------------------------
*/
if ($user["type"] === "owner") {
    $user_id = $user["id"];  // correct key for owner
} else {
    // staff → use owner_id
    $user_id = $user["owner_id"];
}


/* ---------------------------
   PROFILE INFO
--------------------------- */
$q1 = $conn->prepare("SELECT user_id, name, mobile, created_at FROM users WHERE user_id=?");
$q1->bind_param("i", $user_id);
$q1->execute();
$profile = $q1->get_result()->fetch_assoc();


/* ---------------------------
   SUBSCRIPTION INFO
--------------------------- */
$q2 = $conn->prepare("SELECT plan, status, start_date, end_date 
                      FROM subscriptions 
                      WHERE user_id=? 
                      ORDER BY sub_id DESC LIMIT 1");
$q2->bind_param("i", $user_id);
$q2->execute();
$subscription = $q2->get_result()->fetch_assoc() ?: null;


/* ---------------------------
   ORDER STATS
--------------------------- */
$q3 = $conn->prepare("SELECT 
                        COUNT(*) AS total_orders,
                        SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) AS paid_orders,
                        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending_orders,
                        IFNULL(SUM(CASE WHEN status='paid' THEN total ELSE 0 END),0) AS total_spend
                      FROM orders 
                      WHERE user_id=?");
$q3->bind_param("i", $user_id);
$q3->execute();
$order_stats = $q3->get_result()->fetch_assoc();


/* ---------------------------
   LAST LOGIN
--------------------------- */
$q4 = $conn->prepare("SELECT MAX(last_activity) AS last_login FROM sessions WHERE user_id=?");
$q4->bind_param("i", $user_id);
$q4->execute();
$last_login = $q4->get_result()->fetch_assoc()['last_login'] ?? null;


/* ---------------------------
   PRODUCT COUNTS
--------------------------- */
$qA = $conn->prepare("
    SELECT 
        COUNT(*) AS total_products,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_products,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) AS inactive_products
    FROM products 
    WHERE user_id=?");
$qA->bind_param("i", $user_id);
$qA->execute();
$product_stats = $qA->get_result()->fetch_assoc();


/* ---------------------------
   STAFF DETAILS (FIXED)
--------------------------- */
$qB = $conn->prepare("SELECT 
                        staff_id, 
                        display_name AS staff_name,  
                        role, 
                        is_active, 
                        created_at 
                      FROM staff 
                      WHERE user_id=?");
$qB->bind_param("i", $user_id);
$qB->execute();
$resB = $qB->get_result();

$staff_list = [];
while ($row = $resB->fetch_assoc()) {
    $staff_list[] = [
        "staff_id"   => $row["staff_id"],
        "name"       => $row["staff_name"],
        "role"       => $row["role"],
        "is_active"  => $row["is_active"],
        "created_at" => $row["created_at"]
    ];
}

$staff_count = count($staff_list);


/* ---------------------------
   PENDING ORDERS
--------------------------- */
$qC = $conn->prepare("
    SELECT 
        o.order_id,
        qr.table_no,
        o.total,
        o.status,
        o.created_at,
        o.received_by_staff_id AS received_by,
        s.display_name AS received_by_name
    FROM orders o
    LEFT JOIN staff s ON o.received_by_staff_id = s.staff_id
    LEFT JOIN qr_codes qr ON o.qr_id = qr.qr_id
    WHERE o.user_id=? AND o.status='pending'
    ORDER BY o.created_at DESC
    ");
$qC->bind_param("i", $user_id);
$qC->execute();
$resC = $qC->get_result();

$pending_orders = [];
while ($row = $resC->fetch_assoc()) {
    $pending_orders[] = $row;
}


/* ---------------------------
   REVENUE REPORTS
--------------------------- */
$q5 = $conn->prepare("SELECT 
                        IFNULL(SUM(CASE WHEN DATE(created_at)=CURDATE() AND status='paid' THEN total ELSE 0 END),0) AS daily_revenue,
                        IFNULL(SUM(CASE WHEN MONTH(created_at)=MONTH(CURDATE()) 
                                        AND YEAR(created_at)=YEAR(CURDATE()) 
                                        AND status='paid' THEN total ELSE 0 END),0) AS monthly_revenue,
                        IFNULL(SUM(CASE WHEN status='paid' THEN total ELSE 0 END),0) AS total_revenue
                      FROM orders 
                      WHERE user_id=?");
$q5->bind_param("i", $user_id);
$q5->execute();
$revenue = $q5->get_result()->fetch_assoc();


/* ---------------------------
   7 DAYS TREND
--------------------------- */
$q6 = $conn->prepare("SELECT DATE(created_at) as day, 
                             IFNULL(SUM(total),0) as revenue
                      FROM orders 
                      WHERE user_id=? AND status='paid' 
                      AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                      GROUP BY DATE(created_at)
                      ORDER BY DATE(created_at) ASC");
$q6->bind_param("i", $user_id);
$q6->execute();
$res6 = $q6->get_result();
$daily_trend = [];
while($row = $res6->fetch_assoc()){
    $daily_trend[$row['day']] = (float)$row['revenue'];
}


/* ---------------------------
   12 MONTHS TREND
--------------------------- */
$q7 = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                             IFNULL(SUM(total),0) as revenue
                      FROM orders 
                      WHERE user_id=? AND status='paid' 
                      AND created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                      GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                      ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC");
$q7->bind_param("i", $user_id);
$q7->execute();
$res7 = $q7->get_result();
$monthly_trend = [];
while($row = $res7->fetch_assoc()){
    $monthly_trend[$row['month']] = (float)$row['revenue'];
}


/* ---------------------------
   FINAL RESPONSE
--------------------------- */
echo json_encode([
    "success" => true,
    "profile" => $profile,
    "subscription" => $subscription,
    "order_stats" => $order_stats,
    "product_stats" => $product_stats,
    "staff_count" => $staff_count,
    "staff_list" => $staff_list,
    "pending_orders" => $pending_orders,
    "last_login" => $last_login,
    "revenue" => $revenue,
    "daily_trend" => $daily_trend,
    "monthly_trend" => $monthly_trend
], JSON_PRETTY_PRINT);
