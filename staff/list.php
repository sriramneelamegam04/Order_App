<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ✅ Only owner can access
if (!$is_owner) {
    unauthorized("Access denied. Owners only.");
}

$user_id = (int)$auth_user['user_id']; // from middleware

// === INPUTS ===
$search_id   = isset($_GET['staff_id']) ? trim($_GET['staff_id']) : null;
$search_name = isset($_GET['name']) ? trim($_GET['name']) : null;
$page        = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit       = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$offset      = ($page - 1) * $limit;

// === BASE QUERY ===
$query = "SELECT staff_id, username, display_name, role, created_at 
          FROM staff 
          WHERE user_id = ?";
$params = [$user_id];
$types  = "i";

// === SEARCH FILTERS ===
if ($search_id !== null && $search_id !== "") {
    $query .= " AND staff_id = ?";
    $params[] = $search_id;
    $types .= "i";
}
if ($search_name !== null && $search_name !== "") {
    $query .= " AND (display_name LIKE ? OR username LIKE ?)";
    $params[] = "%$search_name%";
    $params[] = "%$search_name%";
    $types .= "ss";
}

// === COUNT FOR PAGINATION ===
$count_sql = "SELECT COUNT(*) AS total FROM ($query) AS count_table";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// === FINAL QUERY WITH LIMIT/OFFSET ===
$query .= " ORDER BY staff_id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$staff = [];
while ($row = $result->fetch_assoc()) {
    $staff[] = $row;
}
$stmt->close();

// === PAGINATION INFO ===
$total_pages = ceil($total / $limit);

echo json_encode([
    "success" => true,
    "page" => $page,
    "limit" => $limit,
    "total_records" => $total,
    "total_pages" => $total_pages,
    "data" => $staff
]);
?>
