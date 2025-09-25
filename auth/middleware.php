<?php
require __DIR__ . '/../config/db.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH , GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

function unauthorized($msg = "Unauthorized") {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => $msg]);
    exit;
}

function getAuthHeader() {
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
    }
    return $headers['Authorization'] ?? $headers['authorization'] ?? null;
}

function get_authenticated_user() {
    global $conn;

    // --- Extract token ---
    $authHeader = getAuthHeader();
    if (!$authHeader) return null;

    if (!preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) {
        return null;
    }
    $token = $matches[1];

    // --- Fetch session ---
    $stmt = $conn->prepare("SELECT user_id, last_activity FROM sessions WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $session = $result->fetch_assoc();
    $stmt->close();
    if (!$session) return null;

    // --- Check inactivity timeout (5 min) ---
    $lastActivity = new DateTime($session['last_activity']);
    if ((time() - $lastActivity->getTimestamp()) > 300) {
        $stmt = $conn->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->close();
        return null;
    }

    // --- Refresh last_activity ---
    $stmt = $conn->prepare("UPDATE sessions SET last_activity = NOW() WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();

    // --- Fetch user details ---
    $user_id = (int)$session['user_id'];
    $stmt = $conn->prepare("SELECT user_id, name, mobile, created_at FROM users WHERE user_id=?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

// --- ✅ Authenticate immediately when middleware is required ---
$user = get_authenticated_user();
if (!$user) {
    unauthorized("Invalid or expired session");
}

// --- Make $user_id available globally ---
$user_id = (int)$user['user_id'];
