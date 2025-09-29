<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php'; // must set $user_id

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight check ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// --- Params ---
$category_name = isset($_GET['category_name']) ? strtolower(trim($_GET['category_name'])) : "";
$search   = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : "";
$page     = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit    = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$offset   = ($page - 1) * $limit;

// --- Build WHERE conditions ---
$where = "c.user_id=?";
$params = [$user_id];
$types  = "i";

if (!empty($category_name)) {
    $where .= " AND LOWER(c.name) = ?";
    $params[] = $category_name;
    $types   .= "s";
}
if (!empty($search)) {
    $where .= " AND LOWER(s.name) LIKE ?";
    $params[] = "%$search%";
    $types   .= "s";
}

// --- Count total ---
$countSql = "SELECT COUNT(*) as total 
             FROM subcategories s 
             JOIN categories c ON s.category_id = c.category_id 
             WHERE $where";

$stmt = $conn->prepare($countSql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// --- Fetch data ---
$sql = "SELECT s.subcategory_id, s.name, s.created_at, c.name as category_name
        FROM subcategories s
        JOIN categories c ON s.category_id = c.category_id
        WHERE $where
        ORDER BY s.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types   .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    "success" => true,
    "total"   => $total,
    "page"    => $page,
    "limit"   => $limit,
    "data"    => $data
]);
