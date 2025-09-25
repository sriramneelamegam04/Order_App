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

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// --- Subscription Reports ---

// 1. Total Users
$q1 = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$total_users = $q1->fetch_assoc()['total_users'] ?? 0;

// 2. Active Free Trials
$q2 = $conn->query("SELECT COUNT(*) AS free_trials 
                    FROM subscriptions 
                    WHERE plan='free' AND status='active'");
$free_trials = $q2->fetch_assoc()['free_trials'] ?? 0;

// 3. Active Yearly Plans
$q3 = $conn->query("SELECT COUNT(*) AS yearly_plans 
                    FROM subscriptions 
                    WHERE plan='yearly' AND status='active'");
$yearly_plans = $q3->fetch_assoc()['yearly_plans'] ?? 0;

// 4. Expired Subscriptions
$q4 = $conn->query("SELECT COUNT(*) AS expired 
                    FROM subscriptions 
                    WHERE status='expired'");
$expired = $q4->fetch_assoc()['expired'] ?? 0;

// 5. Expiring Soon (within 7 days)
$q5 = $conn->query("SELECT COUNT(*) AS expiring_soon 
                    FROM subscriptions 
                    WHERE status='active' 
                      AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$expiring_soon = $q5->fetch_assoc()['expiring_soon'] ?? 0;

// 6. Trend - last 7 days new subscriptions
$q6 = $conn->query("SELECT DATE(start_date) as date, COUNT(*) as count
                    FROM subscriptions
                    GROUP BY DATE(start_date)
                    ORDER BY DATE(start_date) DESC
                    LIMIT 7");
$trend = [];
while ($row = $q6->fetch_assoc()) {
    $trend[] = [
        "date" => $row['date'],
        "subscriptions" => (int)$row['count']
    ];
}
$trend = array_reverse($trend);

// --- User Details Lists ---
function fetchUsers($conn, $where) {
    $sql = "SELECT s.sub_id, s.plan, s.start_date, s.end_date, s.status,
                   u.user_id, u.name, u.mobile
            FROM subscriptions s
            JOIN users u ON s.user_id = u.user_id
            WHERE $where
            ORDER BY s.end_date ASC";
    $res = $conn->query($sql);
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $list[] = $row;
    }
    return $list;
}

$details = [
    "free_trials_users"   => fetchUsers($conn, "s.plan='free' AND s.status='active'"),
    "yearly_plan_users"   => fetchUsers($conn, "s.plan='yearly' AND s.status='active'"),
    "expired_users"       => fetchUsers($conn, "s.status='expired'"),
    "expiring_soon_users" => fetchUsers($conn, "s.status='active' AND s.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")
];

// --- OTP Verified / Logged-in User Reports ---

// Total users who have logged in at least once
$q7 = $conn->query("SELECT COUNT(DISTINCT user_id) AS logged_in_users FROM sessions");
$logged_in_users = $q7->fetch_assoc()['logged_in_users'] ?? 0;

// Active sessions in last 24 hrs
$q8 = $conn->query("SELECT COUNT(DISTINCT user_id) AS active_24h 
                    FROM sessions 
                    WHERE last_activity >= NOW() - INTERVAL 1 DAY");
$active_24h = $q8->fetch_assoc()['active_24h'] ?? 0;

// Currently active (last 5 mins)
$q9 = $conn->query("SELECT COUNT(DISTINCT user_id) AS active_now 
                    FROM sessions 
                    WHERE last_activity >= NOW() - INTERVAL 5 MINUTE");
$active_now = $q9->fetch_assoc()['active_now'] ?? 0;

// Last login per user
$q10 = $conn->query("SELECT u.user_id, u.name, u.mobile, MAX(s.last_activity) as last_login
                     FROM users u
                     JOIN sessions s ON u.user_id=s.user_id
                     GROUP BY u.user_id, u.name, u.mobile
                     ORDER BY last_login DESC");
$last_logins = [];
while ($row = $q10->fetch_assoc()) {
    $last_logins[] = $row;
}

// ✅ Final JSON response
echo json_encode([
    "success" => true,
    "summary" => [
        "total_users" => (int)$total_users,
        "free_trials" => (int)$free_trials,
        "yearly_plans" => (int)$yearly_plans,
        "expired" => (int)$expired,
        "expiring_soon" => (int)$expiring_soon,
        "trend" => $trend
    ],
    "details" => $details,
    "otp_verified_users" => [
        "total_logged_in" => (int)$logged_in_users,
        "active_last_24h" => (int)$active_24h,
        "active_now" => (int)$active_now,
        "last_logins" => $last_logins
    ]
], JSON_PRETTY_PRINT);
