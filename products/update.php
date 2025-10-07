<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH, OPTIONS");
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

if ($sub_end && strtotime($sub_end) < strtotime(date('Y-m-d'))) {
    $sub_status = 'expired';
    $conn->query("UPDATE subscriptions SET status='expired' WHERE user_id=$user_id");
}

if ($sub_status !== 'active') {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "Subscription inactive. Please renew to update products."]);
    exit;
}

if ($business_type_id === null) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No vertical assigned"]);
    exit;
}

// --- Detect input type (FormData or JSON) ---
if (isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $name = strtolower(trim($_POST['name'] ?? ""));
    $unit = strtolower(trim($_POST['unit'] ?? ""));
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $category_name = isset($_POST['category_name']) ? strtolower(trim($_POST['category_name'])) : null;
    $subcategory_name = isset($_POST['subcategory_name']) ? strtolower(trim($_POST['subcategory_name'])) : null;
    $new_image = $_FILES['image'] ?? null;
    $image_value = $_POST['image'] ?? null; // for URL or local path
} else {
    $input = json_decode(file_get_contents("php://input"), true);
    if (
        !$input ||
        !isset($input['product_id'], $input['name'], $input['price'], $input['unit'])
    ) {
        http_response_code(400);
        echo json_encode(["success" => false, "msg" => "Missing fields"]);
        exit;
    }
    $product_id = (int)$input['product_id'];
    $name = strtolower(trim($input['name']));
    $unit = strtolower(trim($input['unit']));
    $price = (float)$input['price'];
    $category_name = isset($input['category_name']) ? strtolower(trim($input['category_name'])) : null;
    $subcategory_name = isset($input['subcategory_name']) ? strtolower(trim($input['subcategory_name'])) : null;
    $new_image = null;
    $image_value = $input['image'] ?? null;
}

if ($price < 0 || !$name || !$unit) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid or missing fields"]);
    exit;
}

// --- Verify product ownership ---
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id=? AND user_id=? AND business_type_id=?");
$stmt->bind_param("iii", $product_id, $user_id, $business_type_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    echo json_encode(["success" => false, "msg" => "Product not found"]);
    exit;
}

// --- Category & Subcategory handling ---
$category_id = $product['category_id'];
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

$subcategory_id = $product['subcategory_id'];
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
$dup_query = "SELECT product_id FROM products WHERE user_id=? AND business_type_id=? AND LOWER(name)=? AND product_id<>?";
$params = [$user_id, $business_type_id, $name, $product_id];
$types = "iisi";

if ($category_id) { $dup_query .= " AND category_id=?"; $types .= "i"; $params[] = $category_id; }
if ($subcategory_id) { $dup_query .= " AND subcategory_id=?"; $types .= "i"; $params[] = $subcategory_id; }

$stmt = $conn->prepare($dup_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($res) {
    http_response_code(409);
    echo json_encode(["success" => false, "msg" => "Another product with same name exists"]);
    exit;
}

// --- Image handling (FormData / URL / Local Path) ---
$image_path = $product['image']; // default to old image
$dateFolder = date('Y-m-d');
$upload_dir = __DIR__ . "/../products/uploads/$dateFolder/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if ($new_image && $new_image['error'] === UPLOAD_ERR_OK) {
    // Case 1: File upload
    $ext = strtolower(pathinfo($new_image['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "msg" => "Invalid image format"]);
        exit;
    }
    $new_name = uniqid("prod_") . "." . $ext;
    $target_path = $upload_dir . $new_name;

    if (move_uploaded_file($new_image['tmp_name'], $target_path)) {
        if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
            unlink(__DIR__ . '/../' . $image_path);
        }
        $image_path = "products/uploads/$dateFolder/" . $new_name;
    }
} elseif (!empty($image_value)) {
    // Case 2: URL or local path
    $ext = strtolower(pathinfo($image_value, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $new_name = uniqid("prod_") . "." . $ext;
        $target_path = $upload_dir . $new_name;

        if (filter_var($image_value, FILTER_VALIDATE_URL)) {
            // Download from URL
            $img_data = @file_get_contents($image_value);
            if ($img_data !== false) {
                file_put_contents($target_path, $img_data);
                if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                    unlink(__DIR__ . '/../' . $image_path);
                }
                $image_path = "products/uploads/$dateFolder/" . $new_name;
            }
        } else {
            // Local path
            $local_path = __DIR__ . "/../products/source_images/" . basename($image_value);
            if (!file_exists($local_path)) $local_path = $image_value;
            if (file_exists($local_path)) {
                if (@copy($local_path, $target_path)) {
                    if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                        unlink(__DIR__ . '/../' . $image_path);
                    }
                    $image_path = "products/uploads/$dateFolder/" . $new_name;
                }
            }
        }
    }
}

// --- Update product ---
$stmt = $conn->prepare("
    UPDATE products
    SET name=?, price=?, unit=?, category_id=?, subcategory_id=?, image=?, updated_at=NOW()
    WHERE product_id=? AND user_id=? AND business_type_id=?
");
$stmt->bind_param("sdsiissii", $name, $price, $unit, $category_id, $subcategory_id, $image_path, $product_id, $user_id, $business_type_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "msg" => "Product updated successfully",
        "product" => [
            "product_id" => $product_id,
            "name" => $name,
            "price" => number_format($price, 2),
            "unit" => $unit,
            "image" => $image_path,
            "category_id" => $category_id,
            "subcategory_id" => $subcategory_id,
            "updated_at" => date("Y-m-d H:i:s")
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to update product"]);
}
$stmt->close();
?>
