<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use DELETE."]);
    exit;
}

// ✅ Only owners can delete
if (!$is_owner) {
    unauthorized("Access denied. Owner token required.");
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid JSON body"]);
    exit;
}

$staff_id = intval($input['staff_id'] ?? 0);
if ($staff_id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid or missing staff_id"]);
    exit;
}

// === Check if staff belongs to owner ===
$stmt = $conn->prepare("SELECT staff_id FROM staff WHERE staff_id=? AND user_id=?");
$stmt->bind_param("ii", $staff_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "msg" => "Staff not found or not owned by you"]);
    $stmt->close();
    exit;
}
$stmt->close();

// === Delete staff sessions first ===
$stmt = $conn->prepare("DELETE FROM staff_sessions WHERE staff_id=?");
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$stmt->close();

// === Delete staff ===
$stmt = $conn->prepare("DELETE FROM staff WHERE staff_id=? AND user_id=?");
$stmt->bind_param("ii", $staff_id, $user_id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(["success" => true, "msg" => "Staff deleted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Database error"]);
}
?>
