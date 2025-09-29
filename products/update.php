<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../auth/middleware.php';
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, PATCH, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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

$input = json_decode(file_get_contents("php://input"), true);

// --- Validate required fields ---
if (
    !$input ||
    !isset($input['product_id'], $input['name'], $input['price'], $input['unit'])
) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing fields"]);
    exit;
}

// --- Sanitize & lowercase strings ---
$product_id = (int)$input['product_id'];
$name       = strtolower(trim($input['name']));
$unit       = strtolower(trim($input['unit']));
$price      = (float)$input['price'];
$category_name    = isset($input['category_name']) ? strtolower(trim($input['category_name'])) : null;
$subcategory_name = isset($input['subcategory_name']) ? strtolower(trim($input['subcategory_name'])) : null;

// --- Validate price ---
if ($price < 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid price"]);
    exit;
}

// --- Fetch user's vertical ---
$stmt = $conn->prepare("SELECT selected_template_id FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

$business_type_id = $res['selected_template_id'] ?? null;
if ($business_type_id == null) {
    http_response_code(403);
    echo json_encode(["success" => false, "msg" => "No vertical assigned"]);
    exit;
}

// --- Ensure product belongs to this user & vertical ---
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

// --- Resolve or create category_id ---
$category_id = $product['category_id']; // default
if ($category_name) {
    $stmt = $conn->prepare("SELECT category_id FROM categories WHERE user_id=? AND business_type_id=? AND LOWER(name)=?");
    $stmt->bind_param("iis", $user_id, $business_type_id, $category_name);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res) {
        $category_id = $res['category_id'];
    } else {
        // create new category
        $stmt = $conn->prepare("INSERT INTO categories (user_id, business_type_id, name) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $business_type_id, $category_name);
        $stmt->execute();
        $category_id = $stmt->insert_id;
        $stmt->close();
    }
}

// --- Resolve or create subcategory_id ---
$subcategory_id = $product['subcategory_id']; // default
if ($subcategory_name && $category_id) {
    $stmt = $conn->prepare("SELECT subcategory_id FROM subcategories WHERE category_id=? AND LOWER(name)=?");
    $stmt->bind_param("is", $category_id, $subcategory_name);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res) {
        $subcategory_id = $res['subcategory_id'];
    } else {
        // create new subcategory
        $stmt = $conn->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $category_id, $subcategory_name);
        $stmt->execute();
        $subcategory_id = $stmt->insert_id;
        $stmt->close();
    }
}

// --- Check for duplicate product ---
$dup_query = "SELECT product_id FROM products WHERE user_id=? AND business_type_id=? AND LOWER(name)=? AND product_id<>?";
$params = [$user_id, $business_type_id, $name, $product_id];
$types = "iisi";

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
    http_response_code(409); // Conflict
    echo json_encode(["success" => false, "msg" => "Another product with same name exists"]);
    exit;
}

// --- Update product ---
$stmt = $conn->prepare("UPDATE products SET name=?, price=?, unit=?, category_id=?, subcategory_id=? WHERE product_id=?");
$stmt->bind_param("sdsiis", $name, $price, $unit, $category_id, $subcategory_id, $product_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "msg" => "Product updated successfully",
        "product" => [
            "product_id" => $product_id,
            "name" => $name,
            "price" => number_format($price, 2),
            "unit" => $unit,
            "category_id" => $category_id,
            "subcategory_id" => $subcategory_id,
            "updated_at" => date("Y-m-d H:i:s")
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "DB update failed"]);
}
$stmt->close();
