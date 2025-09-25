<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ✅ Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use GET"]);
    exit;
}

// ✅ Authenticate user
$user = get_authenticated_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

// --- FETCH BUSINESS TYPES ---
// system templates first, custom created last
$sql = "SELECT business_type_id, name, description 
        FROM business_types 
        ORDER BY is_system DESC, created_at DESC";
$result = $conn->query($sql);

$templates = [];
while($row = $result->fetch_assoc()){
    $templates[] = $row;
}

// --- RESPONSE ---
sendSuccess("Templates fetched", $templates);
?>
