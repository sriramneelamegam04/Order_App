<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== "PATCH") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use PATCH."]);
    exit;
}

// ✅ Only staff can update their own record
if (!$is_staff) {
    unauthorized("Access denied. Staff token required.");
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid JSON body"]);
    exit;
}

// --- Fields ---
$display_name = trim($input['display_name'] ?? "");
$username     = trim($input['username'] ?? "");
$role         = trim($input['role'] ?? "");
$password     = trim($input['password'] ?? "");

// === Basic Validation ===
if (empty($display_name) && empty($username) && empty($role) && empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "No fields provided for update"]);
    exit;
}

if ($username !== "" && strlen($username) < 3) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Username must be at least 3 characters"]);
    exit;
}

if ($display_name !== "" && strlen($display_name) < 2) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Display name must be at least 2 characters"]);
    exit;
}

if ($password !== "" && strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Password must be at least 6 characters"]);
    exit;
}

// === Dynamic query build ===
$fields = [];
$params = [];
$types  = "";

if ($display_name !== "") {
    $fields[] = "display_name = ?";
    $params[] = $display_name;
    $types .= "s";
}

if ($username !== "") {
    $fields[] = "username = ?";
    $params[] = $username;
    $types .= "s";
}

if ($role !== "") {
    $fields[] = "role = ?";
    $params[] = $role;
    $types .= "s";
}

if ($password !== "") {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $fields[] = "password_hash = ?";
    $params[] = $hashed;
    $types .= "s";
}

// === Final Query ===
$params[] = $staff_id; // from middleware
$types .= "i";

$sql = "UPDATE staff SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE staff_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "msg" => "Profile updated successfully" . ($password ? " (password changed)" : "")
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Database error"]);
}
$stmt->close();
?>
