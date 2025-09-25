<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// ✅ Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ✅ Method validation
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
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
$user_id = $user['user_id'];

// --- Fetch latest subscription ---
$stmt = $conn->prepare("
    SELECT plan, start_date, end_date, status 
    FROM subscriptions 
    WHERE user_id=? 
    ORDER BY sub_id DESC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$sub = $res->fetch_assoc();
$stmt->close();

// --- Fetch user's selected_template_id ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();
$selected_template = $userData['selected_template_id'] ?? null;

// --- Prepare response ---
if (!$sub) {
    sendSuccess("No subscription found", [
        "active" => false,
        "selected_template" => $selected_template
    ]);
} else {
    $active = ($sub['status'] === "active" && strtotime($sub['end_date']) >= time());
    $remaining_days = max(0, ceil((strtotime($sub['end_date']) - time()) / 86400));

    sendSuccess("Subscription status", [
        "active" => $active,
        "remaining_days" => $remaining_days,
        "data" => $sub,
        "selected_template" => $selected_template
    ]);
}
?>
