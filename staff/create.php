<?php
require __DIR__ . '/../auth/middleware.php';
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow CORS preflight
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ✅ Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed"]);
    exit;
}

// ✅ Only owners can create staff
if (!$is_owner) {
    unauthorized("Only business owners can create staff");
}

// ✅ Read & sanitize input
$input = json_decode(file_get_contents("php://input"), true);
$username = strtolower(trim($input['username'] ?? ''));
$password = $input['password'] ?? '';
$display_name = strtolower(trim($input['display_name'] ?? ''));
$role = strtolower(trim($input['role'] ?? 'waiter'));

// ✅ Validate mandatory fields
if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Username & password are required"]);
    exit;
}

// ✅ Validate password strength
$pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
if (!preg_match($pattern, $password)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "msg" => "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character"
    ]);
    exit;
}

// ✅ Check active subscription
$stmt = $conn->prepare("SELECT * FROM subscriptions 
                        WHERE user_id=? 
                          AND status='active' 
                          AND (start_date<=CURDATE() OR start_date IS NULL) 
                          AND (end_date>=CURDATE() OR end_date IS NULL) 
                        LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$sub = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sub) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No active subscription found"]);
    exit;
}

// ✅ Check for duplicate username
$stmt = $conn->prepare("SELECT staff_id FROM staff WHERE username=? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    $stmt->close();
    http_response_code(409);
    echo json_encode(["success" => false, "msg" => "Username already exists"]);
    exit;
}
$stmt->close();

// ✅ Insert new staff (secure hash)
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO staff (user_id, username, password_hash, display_name, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('issss', $user_id, $username, $hash, $display_name, $role);
if ($stmt->execute()) {
    $new_id = $stmt->insert_id;
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "msg" => "Staff created successfully",
        "staff_id" => $new_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Database error"]);
}
$stmt->close();
