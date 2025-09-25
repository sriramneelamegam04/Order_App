<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight check ---
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// --- Method validation ---
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// --- File validation ---
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "CSV file required"]);
    exit;
}

$fileInfo = $_FILES['file'];
if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "File upload error"]);
    exit;
}

$allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/csv'];
if (!in_array($fileInfo['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid file type. Only CSV allowed"]);
    exit;
}

if ($fileInfo['size'] > 2 * 1024 * 1024) { // 2MB limit
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "File too large (max 2MB)"]);
    exit;
}

// --- Fetch user's vertical ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$business_type_id = $res['selected_template_id'] ?? null;
if ($business_type_id === null) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No vertical assigned"]);
    exit;
}

// --- Handle CSV ---
$file = $fileInfo['tmp_name'];
if (($handle = fopen($file, "r")) !== FALSE) {
    $header = fgetcsv($handle);

    if (!$header) {
        http_response_code(400);
        echo json_encode(["success" => false, "msg" => "CSV file is empty"]);
        exit;
    }

    $header = array_map('strtolower', $header);

    // ✅ Required headers check
    $requiredHeaders = ['name', 'price', 'unit'];
    $missingHeaders = array_diff($requiredHeaders, $header);

    if (!empty($missingHeaders)) {
        http_response_code(400);
        echo json_encode(["success" => false, "msg" => "Missing columns: " . implode(", ", $missingHeaders)]);
        exit;
    }

    $inserted = 0;
    $skipped = 0;
    $duplicates = 0;

    while (($row = fgetcsv($handle)) !== FALSE) {
        $data = array_combine($header, $row);

        if (!isset($data['name'], $data['price'], $data['unit']) ||
            $data['name'] === "" || $data['price'] === "" || $data['unit'] === "") {
            $skipped++;
            continue;
        }

        // --- Normalize / lowercase ---
        $name  = strtolower(trim($data['name']));
        $price = trim($data['price']); // keep numeric as-is
        $unit  = strtolower(trim($data['unit']));

        // --- Type validation ---
        if (!is_numeric($price)) {
            $skipped++;
            continue;
        }

        // --- Duplicate check ---
        $check = $conn->prepare("SELECT product_id FROM products WHERE user_id=? AND business_type_id=? AND name=? LIMIT 1");
        $check->bind_param("iis", $user_id, $business_type_id, $name);
        $check->execute();
        $result = $check->get_result();
        $exists = $result->num_rows > 0;
        $check->close();

        if ($exists) {
            $duplicates++;
            continue;
        }

        $stmt = $conn->prepare("INSERT INTO products (user_id, business_type_id, name, price, unit, created_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss", $user_id, $business_type_id, $name, $price, $unit);
        if ($stmt->execute()) {
            $inserted++;
        } else {
            $skipped++;
        }
        $stmt->close();
    }
    fclose($handle);

    echo json_encode([
        "success" => true,
        "msg" => "$inserted products imported successfully, $duplicates duplicates skipped, $skipped rows skipped"
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Unable to open file"]);
}
