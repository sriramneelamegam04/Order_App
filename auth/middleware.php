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

/* ==========================
   COMMON FUNCTIONS
========================== */
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

/* ==========================
   OWNER / BUSINESS USER AUTH
========================== */
function get_authenticated_owner() {
    global $conn;
    $authHeader = getAuthHeader();
    if (!$authHeader) return null;

    if (!preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) return null;
    $token = $matches[1];

    $stmt = $conn->prepare("SELECT user_id, last_activity FROM sessions WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$session) return null;

    $lastActivity = new DateTime($session['last_activity']);
    if ((time() - $lastActivity->getTimestamp()) > 300) {
        $stmt = $conn->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->close();
        return null;
    }

    $stmt = $conn->prepare("UPDATE sessions SET last_activity = NOW() WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();

    $uid = (int)$session['user_id'];
    $stmt = $conn->prepare("SELECT user_id, name, mobile, created_at FROM users WHERE user_id=?");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

/* ==========================
   STAFF / WAITER AUTH
========================== */
function get_authenticated_staff() {
    global $conn;
    $authHeader = getAuthHeader();
    if (!$authHeader) return null;

    if (!preg_match('/^Bearer\s+(\S+)$/i', $authHeader, $matches)) return null;
    $token = $matches[1];

    $stmt = $conn->prepare("SELECT staff_id, last_activity FROM staff_sessions WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$session) return null;

    $lastActivity = new DateTime($session['last_activity']);
    if ((time() - $lastActivity->getTimestamp()) > 300) {
        $stmt = $conn->prepare("DELETE FROM staff_sessions WHERE token = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->close();
        return null;
    }

    $stmt = $conn->prepare("UPDATE staff_sessions SET last_activity = NOW() WHERE token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->close();

    $sid = (int)$session['staff_id'];
    $stmt = $conn->prepare("SELECT staff_id, user_id AS owner_id, username, display_name, role FROM staff WHERE staff_id=?");
    $stmt->bind_param('i', $sid);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $staff ?: null;
}

/* ==========================
   AUTO-DETECT ROLE
========================== */
$auth_user = get_authenticated_owner();
$auth_staff = null;

if (!$auth_user) {
    // owner not found → maybe staff token
    $auth_staff = get_authenticated_staff();
    if (!$auth_staff) {
        unauthorized("Invalid or expired session");
    }
}

/* ==========================
   EXPORT GLOBALS
========================== */
// Owner session
if ($auth_user) {
    $user_id = (int)$auth_user['user_id'];
    $is_staff = false;
    $is_owner = true;
} 
// Staff session
elseif ($auth_staff) {
    $staff_id = (int)$auth_staff['staff_id'];
    $owner_id = (int)$auth_staff['owner_id'];
    $staff_role = $auth_staff['role'];
    $is_staff = true;
    $is_owner = false;
}
?>
