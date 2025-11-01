<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use POST"]);
    exit;
}

// ✅ Authenticate user
$user = get_authenticated_user(); // from middleware.php
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

$user_id = $user['user_id'];

// --- FILE UPLOAD ---
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "No photo uploaded or upload error"]);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileTmpPath = $_FILES['photo']['tmp_name'];
$fileName = time() . '_' . basename($_FILES['photo']['name']);
$filePath = $uploadDir . $fileName;

if (!move_uploaded_file($fileTmpPath, $filePath)) {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to move uploaded file"]);
    exit;
}

$photoPath = 'uploads/' . $fileName; // relative path

// --- UPDATE USER TABLE ---
$stmt = $conn->prepare("UPDATE users SET photo=? WHERE user_id=?");
$stmt->bind_param('si', $photoPath, $user_id);
$stmt->execute();
$stmt->close();

// --- RESPONSE ---
echo json_encode([
    "success" => true,
    "msg" => "Photo uploaded successfully",
    "data" => ["photo_url" => $photoPath]
]);
?>










kekka kooda yaarum illaaaaaaa ipdiya adippa oru pombala pillaiya machan vaa namma tea kudikka polam naa samosa vangi thaaren.. i love u machann  

ithuthan aluguratha bava olaikkiraaa vaanga polam iru machan ivainga lam varattum.. mullai akka super ah irukkumm nee pannaley athu