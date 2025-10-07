<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php'; // must set $user_id

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Invalid request method"]);
    exit;
}

// --- Validate user ---
if (!isset($user_id)) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
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

// --- Auto-expire check ---
if ($sub_end && strtotime($sub_end) < strtotime(date('Y-m-d'))) {
    $sub_status = 'expired';
    $conn->query("UPDATE subscriptions SET status='expired' WHERE user_id=$user_id");
}

if ($sub_status !== 'active') {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "Subscription inactive. Please renew to add products."]);
    exit;
}

if ($business_type_id === null) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No vertical assigned"]);
    exit;
}

// --- Read fields (can be JSON or FormData) ---
$name  = strtolower(trim($_POST['name'] ?? ''));
$price = isset($_POST['price']) ? floatval($_POST['price']) : null;
$unit  = strtolower(trim($_POST['unit'] ?? ''));
$category_name    = isset($_POST['category_name']) ? strtolower(trim($_POST['category_name'])) : null;
$subcategory_name = isset($_POST['subcategory_name']) ? strtolower(trim($_POST['subcategory_name'])) : null;
$image_value      = $_POST['image'] ?? null; // for URL or local path

if ($name === '' || $unit === '' || $price === null) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing required fields: name, price, unit"]);
    exit;
}

// --- Upload dir ---
$dateFolder = date('Y-m-d');
$upload_dir = __DIR__ . "/../products/uploads/$dateFolder/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// --- Handle image (upload file / URL / local path) ---
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    // Case 1: File upload from FormData
    $tmp = $_FILES['image']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $new_name = uniqid("prod_") . "." . $ext;
        $target_path = $upload_dir . $new_name;
        if (move_uploaded_file($tmp, $target_path)) {
            $image_path = "products/uploads/$dateFolder/" . $new_name;
        }
    }
} elseif (!empty($image_value)) {
    // Case 2: URL or local path provided
    $ext = strtolower(pathinfo($image_value, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $new_name = uniqid("prod_") . "." . $ext;
        $target_path = $upload_dir . $new_name;

        if (filter_var($image_value, FILTER_VALIDATE_URL)) {
            // Download from URL
            $img_data = @file_get_contents($image_value);
            if ($img_data !== false) {
                file_put_contents($target_path, $img_data);
                $image_path = "products/uploads/$dateFolder/" . $new_name;
            }
        } else {
            // Local server file path
            $local_path = __DIR__ . "/../products/source_images/" . basename($image_value);
            if (!file_exists($local_path)) $local_path = $image_value;
            if (file_exists($local_path)) {
                if (@copy($local_path, $target_path)) {
                    $image_path = "products/uploads/$dateFolder/" . $new_name;
                }
            }
        }
    }
}

// --- Resolve category ---
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

// --- Resolve subcategory ---
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

// --- Duplicate check ---
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

if ($res) {
    http_response_code(409);
    echo json_encode(["success" => false, "msg" => "Product already exists"]);
    exit;
}

// --- Insert product ---
$stmt = $conn->prepare("
    INSERT INTO products (user_id, business_type_id, name, price, unit, image, created_at, category_id, subcategory_id)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)
");
$stmt->bind_param("iisdssii", $user_id, $business_type_id, $name, $price, $unit, $image_path, $category_id, $subcategory_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "msg" => "Product added successfully",
        "product" => [
            "product_id" => $stmt->insert_id,
            "name" => $name,
            "price" => number_format($price, 2),
            "unit" => $unit,
            "image" => $image_path,
            "category_id" => $category_id,
            "subcategory_id" => $subcategory_id,
            "created_at" => date("Y-m-d H:i:s")
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to add product"]);
}
$stmt->close();
?>
