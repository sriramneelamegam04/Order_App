<?php
require __DIR__ . '/../auth/middleware.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    echo json_encode(["success"=>false,"msg"=>"Invalid method"]);
    exit;
}

if (!$is_owner) {
    unauthorized("Only business owners can view this report");
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$from_date = trim($_GET['from'] ?? '');
$to_date = trim($_GET['to'] ?? '');

// ✅ Base SQL
$sql = "
    SELECT 
        q.table_no,
        s.customer_name,
        s.mobile,
        s.slug,
        s.session_id,
        s.created_at
    FROM qr_sessions s
    JOIN qr_codes q ON s.slug = q.qr_slug
    WHERE q.user_id = ?
";

$params = [$user_id];
$types = "i";

// ✅ Apply filters
if ($search !== '') {
    $sql .= " AND (s.customer_name LIKE ? OR s.mobile LIKE ? OR q.table_no LIKE ?)";
    $like = "%{$search}%";
    array_push($params, $like, $like, $like);
    $types .= "sss";
}

if ($from_date !== '' && $to_date !== '') {
    $sql .= " AND DATE(s.created_at) BETWEEN ? AND ?";
    array_push($params, $from_date, $to_date);
    $types .= "ss";
} elseif ($from_date !== '') {
    $sql .= " AND DATE(s.created_at) >= ?";
    $params[] = $from_date;
    $types .= "s";
} elseif ($to_date !== '') {
    $sql .= " AND DATE(s.created_at) <= ?";
    $params[] = $to_date;
    $types .= "s";
}

$sql .= " ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
array_push($params, $limit, $offset);
$types .= "ii";

// ✅ Execute
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$records = [];
while ($r = $res->fetch_assoc()) {
    $records[] = $r;
}
$stmt->close();

// ✅ Total count
$count_sql = "
    SELECT COUNT(*) AS total
    FROM qr_sessions s
    JOIN qr_codes q ON s.slug = q.qr_slug
    WHERE q.user_id = ?
";
$count_params = [$user_id];
$count_types = "i";

if ($search !== '') {
    $count_sql .= " AND (s.customer_name LIKE ? OR s.mobile LIKE ? OR q.table_no LIKE ?)";
    array_push($count_params, $like, $like, $like);
    $count_types .= "sss";
}
if ($from_date !== '' && $to_date !== '') {
    $count_sql .= " AND DATE(s.created_at) BETWEEN ? AND ?";
    array_push($count_params, $from_date, $to_date);
    $count_types .= "ss";
} elseif ($from_date !== '') {
    $count_sql .= " AND DATE(s.created_at) >= ?";
    $count_params[] = $from_date;
    $count_types .= "s";
} elseif ($to_date !== '') {
    $count_sql .= " AND DATE(s.created_at) <= ?";
    $count_params[] = $to_date;
    $count_types .= "s";
}

$stmt2 = $conn->prepare($count_sql);
$stmt2->bind_param($count_types, ...$count_params);
$stmt2->execute();
$total = $stmt2->get_result()->fetch_assoc()['total'] ?? 0;
$stmt2->close();

// ✅ Response
echo json_encode([
    "success" => true,
    "filters" => [
        "search" => $search,
        "from" => $from_date,
        "to" => $to_date,
        "page" => $page,
        "limit" => $limit
    ],
    "pagination" => [
        "total" => (int)$total,
        "pages" => ceil($total / $limit)
    ],
    "data" => $records
]);
?>
