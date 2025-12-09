<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");

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

// --- Fetch user's vertical ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$business_type_id = $res['selected_template_id'] ?? null;
if ($business_type_id === null) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No vertical assigned"]);
    exit;
}

// --- Filters ---
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// --- Count total ---
$countQuery = "
    SELECT COUNT(*) as total 
    FROM products 
    WHERE user_id=? AND business_type_id=?";
$params = [$user_id, $business_type_id];
$types  = "ii";

if ($search) {
    $countQuery .= " AND products.name LIKE ?";
    $params[] = "%$search%";
    $types   .= "s";
}

$stmt = $conn->prepare($countQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$totalRes = $stmt->get_result()->fetch_assoc();
$total = $totalRes['total'] ?? 0;
$stmt->close();

// --- Fetch products with category + subcategory names ---
$query = "
    SELECT 
        p.*,
        c.name AS category_name,
        s.name AS subcategory_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id
    WHERE p.user_id=? AND p.business_type_id=?";
$params = [$user_id, $business_type_id];
$types  = "ii";

if ($search) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%$search%";
    $types   .= "s";
}

$query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types   .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {

    // Replace IDs with names
    $row['category'] = $row['category_name'] ?? null;
    $row['subcategory'] = $row['subcategory_name'] ?? null;

    // Remove raw id fields (optional)
    unset($row['category_id'], $row['subcategory_id'], $row['category_name'], $row['subcategory_name']);

    $products[] = $row;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "page"    => $page,
    "limit"   => $limit,
    "total"   => $total,
    "data"    => $products
]);
