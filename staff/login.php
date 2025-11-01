<?php
require __DIR__ . '/../config/db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Handle preflight ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Allow only POST ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed"]);
    exit;
}

// --- Parse & clean input ---
$input = json_decode(file_get_contents("php://input"), true);
$username = strtolower(trim($input['username'] ?? ''));
$password = $input['password'] ?? '';

// --- Validate fields ---
if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Username and password are required"]);
    exit;
}

// --- Optional password format check (login time hint only) ---
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid credentials format"]);
    exit;
}

// --- Fetch staff record ---
$stmt = $conn->prepare("SELECT * FROM staff WHERE username=? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$staff = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- Validate account & password ---
if (!$staff) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Invalid username or password"]);
    exit;
}

if (!$staff['is_active']) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "Account is inactive. Contact owner."]);
    exit;
}

if (!password_verify($password, $staff['password_hash'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Invalid username or password"]);
    exit;
}

// --- Create session token ---
$token = bin2hex(random_bytes(32));
$now = date('Y-m-d H:i:s');

// Clear old sessions for same staff (optional hygiene)
$conn->query("DELETE FROM staff_sessions WHERE staff_id = " . (int)$staff['staff_id']);

$stmt = $conn->prepare("INSERT INTO staff_sessions (staff_id, token, last_activity) VALUES (?,?,?)");
$stmt->bind_param('iss', $staff['staff_id'], $token, $now);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Unable to create session"]);
    exit;
}
$stmt->close();

// --- Success ---
http_response_code(200);
echo json_encode([
    "success" => true,
    "msg" => "Login successful",
    "token" => $token,
    "staff_id" => (int)$staff['staff_id'],
    "owner_id" => (int)$staff['user_id'],
    "display_name" => $staff['display_name'],
    "role" => $staff['role']
]);
