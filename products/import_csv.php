<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';
global $user_id;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// --- Preflight ---
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

// --- Validate auth ---
if (!isset($user_id) || !$user_id) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "Unauthorized user"]);
    exit;
}

// --- Check subscription ---
$stmt = $conn->prepare("
    SELECT u.selected_template_id, s.status, s.end_date
    FROM users u
    LEFT JOIN subscriptions s ON u.user_id = s.user_id
    WHERE u.user_id = ?
    ORDER BY s.end_date DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "User or subscription not found"]);
    exit;
}

$business_type_id = $res['selected_template_id'] ?? null;
$sub_status = $res['status'] ?? 'expired';
$sub_end = $res['end_date'] ?? null;

if ($sub_end && strtotime($sub_end) < strtotime(date('Y-m-d'))) {
    $sub_status = 'expired';
    $conn->query("UPDATE subscriptions SET status='expired' WHERE user_id=$user_id");
}

if ($sub_status !== 'active') {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "Subscription inactive. Please renew to add or import products."]);
    exit;
}

if ($business_type_id === null) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No vertical assigned"]);
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

// --- Upload folder ---
$dateFolder = date('Y-m-d');
$upload_dir = __DIR__ . "/../products/uploads/$dateFolder/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// --- Process CSV ---
$file = $fileInfo['tmp_name'];
if (($handle = fopen($file, "r")) !== FALSE) {
    $header = fgetcsv($handle);
    if (!$header) {
        http_response_code(400);
        echo json_encode(["success" => false, "msg" => "CSV file is empty"]);
        exit;
    }

    $header = array_map('strtolower', $header);
    $requiredHeaders = ['name', 'price', 'field']; // 'field' = unit
    $missingHeaders = array_diff($requiredHeaders, $header);
    if (!empty($missingHeaders)) {
        http_response_code(400);
        echo json_encode(["success" => false, "msg" => "Missing columns: " . implode(", ", $missingHeaders)]);
        exit;
    }

    $hasCategory    = in_array('category', $header) || in_array('category_name', $header);
    $hasSubcategory = in_array('subcategory', $header) || in_array('subcategory_name', $header);
    $hasImage       = in_array('image', $header);
    $hasDescription = in_array('description', $header);

    $inserted = $skipped = $duplicates = 0;

    while (($row = fgetcsv($handle)) !== FALSE) {
        $data = array_combine($header, $row);

        if (!isset($data['name'], $data['price'], $data['field']) ||
            $data['name'] === "" || $data['price'] === "" || $data['field'] === "") {
            $skipped++;
            continue;
        }

        $name  = strtolower(trim($data['name']));
        $price = trim($data['price']);
        $unit  = strtolower(trim($data['field']));
        $description = $hasDescription ? trim($data['description']) : null;
        $category_name    = $hasCategory ? strtolower(trim($data['category'] ?? $data['category_name'] ?? '')) : null;
        $subcategory_name = $hasSubcategory ? strtolower(trim($data['subcategory'] ?? $data['subcategory_name'] ?? '')) : null;
        $image_value      = $hasImage ? trim($data['image']) : null;

        if (!is_numeric($price)) {
            $skipped++;
            continue;
        }

        // --- Resolve Unit (auto-create if missing) ---
        $stmt = $conn->prepare("SELECT field_id FROM template_fields WHERE LOWER(field_name)=? AND business_type_id=?");
        $stmt->bind_param("si", $unit, $business_type_id);
        $stmt->execute();
        $resfield = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($resfield) {
            $field_id = $resfield['field_id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO template_fields (business_type_id, field_name) VALUES (?, ?)");
            $stmt->bind_param("is", $business_type_id, $unit);
            $stmt->execute();
            $field_id = $stmt->insert_id;
            $stmt->close();
        }

        // --- Resolve Category ---
        $category_id = null;
        if ($category_name) {
            $stmt = $conn->prepare("SELECT category_id FROM categories WHERE user_id=? AND business_type_id=? AND LOWER(name)=?");
            $stmt->bind_param("iis", $user_id, $business_type_id, $category_name);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res) $category_id = $res['category_id'];
            else {
                $stmt = $conn->prepare("INSERT INTO categories (user_id, business_type_id, name) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $user_id, $business_type_id, $category_name);
                $stmt->execute();
                $category_id = $stmt->insert_id;
                $stmt->close();
            }
        }

        // --- Resolve Subcategory ---
        $subcategory_id = null;
        if ($subcategory_name && $category_id) {
            $stmt = $conn->prepare("SELECT subcategory_id FROM subcategories WHERE category_id=? AND LOWER(name)=?");
            $stmt->bind_param("is", $category_id, $subcategory_name);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res) $subcategory_id = $res['subcategory_id'];
            else {
                $stmt = $conn->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
                $stmt->bind_param("is", $category_id, $subcategory_name);
                $stmt->execute();
                $subcategory_id = $stmt->insert_id;
                $stmt->close();
            }
        }

        // --- Duplicate Check ---
        $dup_query = "SELECT product_id FROM products WHERE user_id=? AND business_type_id=? AND LOWER(name)=?";
        $params = [$user_id, $business_type_id, $name];
        $types = "iis";
        if ($category_id) { $dup_query .= " AND category_id=?"; $types .= "i"; $params[] = $category_id; }
        if ($subcategory_id) { $dup_query .= " AND subcategory_id=?"; $types .= "i"; $params[] = $subcategory_id; }

        $stmt = $conn->prepare($dup_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($res) { $duplicates++; continue; }

        // --- Image Handling (strict: throw error if image invalid) ---
        $image_path = null;
        if ($image_value) {
            $image_ext = pathinfo(parse_url($image_value, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (!$image_ext) $image_ext = 'jpg';
            $new_name = uniqid("prod_") . "." . $image_ext;
            $target_path = $upload_dir . $new_name;

            $image_downloaded = false;

            if (filter_var($image_value, FILTER_VALIDATE_URL)) {
                // Try remote image download
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'header' => "User-Agent: Mozilla/5.0\r\n"
                    ]
                ]);
                $image_data = @file_get_contents($image_value, false, $context);

                if ($image_data !== false && strlen($image_data) > 500) { // ensure not empty
                    file_put_contents($target_path, $image_data);
                    $image_path = "products/uploads/$dateFolder/" . $new_name;
                    $image_downloaded = true;
                }
            } else {
                // Try local image copy
                $local_path = __DIR__ . "/../products/source_images/" . basename($image_value);
                if (!file_exists($local_path)) $local_path = $image_value;
                if (file_exists($local_path) && @copy($local_path, $target_path)) {
                    $image_path = "products/uploads/$dateFolder/" . $new_name;
                    $image_downloaded = true;
                }
            }

            // --- If failed, return error immediately ---
            if (!$image_downloaded) {
                http_response_code(400);
                echo json_encode([
                    "success" => false,
                    "msg" => "Image missing or invalid for product: {$name} ({$image_value})"
                ]);
                exit;
            }
        }

        // --- Insert Product ---
        $stmt = $conn->prepare("INSERT INTO products 
            (user_id, business_type_id, name, price, unit, image, description, created_at, category_id, subcategory_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");
        $stmt->bind_param("iisdsssii", $user_id, $business_type_id, $name, $price, $unit, $image_path, $description, $category_id, $subcategory_id);

        if ($stmt->execute()) $inserted++;
        else {
            $skipped++;
            error_log("Insert error: " . $stmt->error);
        }
        $stmt->close();
    }

    fclose($handle);
    echo json_encode([
        "success" => true,
        "msg" => "$inserted products imported, $duplicates duplicates, $skipped skipped"
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Unable to open file"]);
}
?>
