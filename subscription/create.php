<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ✅ Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use POST"]);
    exit;
}

// ✅ Authenticate user
$user = get_authenticated_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}


// --- Check if already active ---
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id=? AND status='active'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    sendError("Already have an active subscription");
}

// --- Free trial already used? ---
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM subscriptions WHERE user_id=? AND plan='free'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if ($row['c'] > 0) {
    sendError("Free trial already used");
}

// --- Insert free trial ---
$stmt = $conn->prepare(
    "INSERT INTO subscriptions(user_id, plan, start_date, end_date, status) 
     VALUES (?, 'free', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'active')"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// --- Fetch user's selected_template_id ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();
$selected_template = $userData['selected_template_id'] ?? null;

// --- Response ---
sendSuccess("Free trial activated", [
    "plan" => "free",
    "days" => 7,
    "selected_template" => $selected_template
]);
?>
