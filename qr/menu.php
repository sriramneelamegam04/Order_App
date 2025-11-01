<?php
require '../config/db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success"=>false,"msg"=>"Invalid method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$session_id = trim($data['session_id'] ?? '');
$filter_category = trim($data['category'] ?? '');
$filter_subcategory = trim($data['subcategory'] ?? '');

if (!$session_id) {
    http_response_code(400);
    echo json_encode(["success"=>false,"msg"=>"session_id required"]);
    exit;
}

// ✅ Validate session
$stmt = $conn->prepare("SELECT * FROM qr_sessions WHERE session_id=? LIMIT 1");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$session) {
    http_response_code(401);
    echo json_encode(["success"=>false,"msg"=>"Invalid session"]);
    exit;
}

// ✅ Check session expiry (1 hour)
$created = strtotime($session['created_at']);
if ((time() - $created) > 3600) {
    $stmt_del = $conn->prepare("DELETE FROM qr_sessions WHERE session_id=?");
    $stmt_del->bind_param("s", $session_id);
    $stmt_del->execute();
    $stmt_del->close();

    http_response_code(440);
    echo json_encode(["success" => false, "msg" => "Session expired. Please re-login via OTP."]);
    exit;
}

$slug = $session['slug'];
$name = $session['customer_name'];
$mobile = $session['mobile'];

// ✅ Fetch QR info
$stmt = $conn->prepare("
    SELECT q.qr_id, q.table_no, q.business_type_id, q.user_id,
           u.name AS owner_name, b.name AS business_type
    FROM qr_codes q
    JOIN users u ON q.user_id = u.user_id
    JOIN business_types b ON q.business_type_id = b.business_type_id
    WHERE q.qr_slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$qr = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$qr) {
    http_response_code(404);
    echo json_encode(["success"=>false,"msg"=>"Invalid QR"]);
    exit;
}

// ✅ Create order if not exists
$stmt2 = $conn->prepare("SELECT order_id FROM orders WHERE qr_id=? AND customer_mobile=? AND status='pending' ORDER BY order_id DESC LIMIT 1");
$stmt2->bind_param("is", $qr['qr_id'], $mobile);
$stmt2->execute();
$existing = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

if ($existing) {
    $order_id = $existing['order_id'];
} else {
    $stmt3 = $conn->prepare("
        INSERT INTO orders (user_id, qr_id, customer_name, customer_mobile, status, total)
        VALUES (?, ?, ?, ?, 'pending', 0)");
    $stmt3->bind_param("iiss", $qr['user_id'], $qr['qr_id'], $name, $mobile);
    $stmt3->execute();
    $order_id = $stmt3->insert_id;
    $stmt3->close();
}

// ✅ Build dynamic WHERE filters
$where = "WHERE p.business_type_id=? AND p.user_id=? AND p.is_active=1";
$params = [$qr['business_type_id'], $qr['user_id']];
$types = "ii";

if ($filter_category !== '') {
    $where .= " AND c.name = ?";
    $params[] = $filter_category;
    $types .= "s";
}

if ($filter_subcategory !== '') {
    $where .= " AND s.name = ?";
    $params[] = $filter_subcategory;
    $types .= "s";
}

// ✅ Fetch active products (with joins)
$sql = "
    SELECT 
        p.product_id, 
        p.name, 
        p.price, 
        p.unit, 
        p.image, 
        p.description,
        c.name AS category_name,
        s.name AS subcategory_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id
    $where
";

$stmt4 = $conn->prepare($sql);
$stmt4->bind_param($types, ...$params);
$stmt4->execute();
$res4 = $stmt4->get_result();

$products = [];
while ($row = $res4->fetch_assoc()) {
    if (!empty($row['image'])) {
        $row['image'] = "http://your-domain.com/uploads/products/" . $row['image'];
    }
    $products[] = $row;
}
$stmt4->close();

// ✅ Fetch category & subcategory filters dynamically
if ($filter_category !== '') {
    // if category selected → fetch only its subcategories
    $stmt5 = $conn->prepare("
        SELECT DISTINCT s.name AS subcategory
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id
        WHERE p.business_type_id=? AND p.user_id=? AND c.name=? AND p.is_active=1
    ");
    $stmt5->bind_param("iis", $qr['business_type_id'], $qr['user_id'], $filter_category);
} else {
    // if no category → fetch all subcategories
    $stmt5 = $conn->prepare("
        SELECT DISTINCT c.name AS category, s.name AS subcategory
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id
        WHERE p.business_type_id=? AND p.user_id=? AND p.is_active=1
    ");
    $stmt5->bind_param("ii", $qr['business_type_id'], $qr['user_id']);
}
$stmt5->execute();
$res5 = $stmt5->get_result();

$filter_data = [];
while ($r = $res5->fetch_assoc()) {
    $filter_data[] = $r;
}
$stmt5->close();

// ✅ Extract distinct categories and subcategories
$categories = [];
$subcategories = [];

foreach ($filter_data as $r) {
    if (!empty($r['category'])) $categories[] = $r['category'];
    if (!empty($r['subcategory'])) $subcategories[] = $r['subcategory'];
}

$categories = array_values(array_unique($categories));
$subcategories = array_values(array_unique($subcategories));

// ✅ Final response
echo json_encode([
    "success" => true,
    "session_active" => true,
    "filters" => [
        "available_categories" => $categories,
        "available_subcategories" => $subcategories
    ],
    "applied_filters" => [
        "category" => $filter_category ?: null,
        "subcategory" => $filter_subcategory ?: null
    ],
    "qr_info" => [
        "qr_id" => $qr['qr_id'],
        "table_no" => $qr['table_no'],
        "owner_name" => $qr['owner_name'],
        "business_type" => $qr['business_type']
    ],
    "order" => [
        "order_id" => $order_id,
        "status" => "pending",
        "customer_name" => $name,
        "customer_mobile" => $mobile
    ],
    "menu" => $products
]);
?>
