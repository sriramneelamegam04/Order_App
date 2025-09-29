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

if ($fileInfo['size'] > 5 * 1024 * 1024) { // 5MB limit
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "File too large (max 5MB)"]);
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

    // Required headers
    $requiredHeaders = ['name', 'price', 'unit'];
    $missingHeaders = array_diff($requiredHeaders, $header);
    if (!empty($missingHeaders)) {
        http_response_code(400);
        echo json_encode(["success" => false, "msg" => "Missing columns: " . implode(", ", $missingHeaders)]);
        exit;
    }

    // Optional headers
    $hasCategory    = in_array('category_name', $header);
    $hasSubcategory = in_array('subcategory_name', $header);

    $inserted   = 0;
    $skipped    = 0;
    $duplicates = 0;

    while (($row = fgetcsv($handle)) !== FALSE) {
        $data = array_combine($header, $row);

        if (!isset($data['name'], $data['price'], $data['unit']) ||
            $data['name'] === "" || $data['price'] === "" || $data['unit'] === "") {
            $skipped++;
            continue;
        }

        // --- Normalize ---
        $name  = strtolower(trim($data['name']));
        $price = trim($data['price']);
        $unit  = strtolower(trim($data['unit']));
        $category_name    = $hasCategory ? strtolower(trim($data['category_name'])) : null;
        $subcategory_name = $hasSubcategory ? strtolower(trim($data['subcategory_name'])) : null;

        if (!is_numeric($price)) {
            $skipped++;
            continue;
        }

        // --- Resolve or create category ---
        $category_id = null;
        if ($category_name) {
            $stmt = $conn->prepare("SELECT category_id FROM categories WHERE user_id=? AND business_type_id=? AND LOWER(name)=?");
            $stmt->bind_param("iis", $user_id, $business_type_id, $category_name);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res) {
                $category_id = $res['category_id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (user_id, business_type_id, name) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $user_id, $business_type_id, $category_name);
                $stmt->execute();
                $category_id = $stmt->insert_id;
                $stmt->close();
            }
        }

        // --- Resolve or create subcategory ---
        $subcategory_id = null;
        if ($subcategory_name && $category_id) {
            $stmt = $conn->prepare("SELECT subcategory_id FROM subcategories WHERE category_id=? AND LOWER(name)=?");
            $stmt->bind_param("is", $category_id, $subcategory_name);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res) {
                $subcategory_id = $res['subcategory_id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
                $stmt->bind_param("is", $category_id, $subcategory_name);
                $stmt->execute();
                $subcategory_id = $stmt->insert_id;
                $stmt->close();
            }
        }

        // --- Duplicate check ---
        $dup_query = "SELECT product_id FROM products WHERE user_id=? AND business_type_id=? AND LOWER(name)=?";
        $params = [$user_id, $business_type_id, $name];
        $types = "iis";

        if ($category_id) {
            $dup_query .= " AND category_id=?";
            $types .= "i";
            $params[] = $category_id;
        }

        if ($subcategory_id) {
            $dup_query .= " AND subcategory_id=?";
            $types .= "i";
            $params[] = $subcategory_id;
        }

        $stmt = $conn->prepare($dup_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($res) {
            $duplicates++;
            continue;
        }

        // --- Insert product ---
        $stmt = $conn->prepare("INSERT INTO products (user_id, business_type_id, name, price, unit, created_at, category_id, subcategory_id) 
                                VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param("iisssii", $user_id, $business_type_id, $name, $price, $unit, $category_id, $subcategory_id);
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
