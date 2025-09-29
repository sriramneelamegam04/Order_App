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

// --- Parse input ---
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid or missing JSON body"]);
    exit;
}

// --- Required fields ---
$required = ["name", "price", "unit"];
$missing = [];
foreach ($required as $field) {
    if (!isset($input[$field]) || $input[$field] === "") {
        $missing[] = $field;
    }
}
if (!empty($missing)) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Missing fields: " . implode(", ", $missing)]);
    exit;
}

// --- Sanitize ---
$name  = strtolower(trim($input['name']));
$unit  = strtolower(trim($input['unit']));
$price = (float)$input['price'];
$category_name    = isset($input['category_name']) ? strtolower(trim($input['category_name'])) : null;
$subcategory_name = isset($input['subcategory_name']) ? strtolower(trim($input['subcategory_name'])) : null;

if ($price < 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "msg" => "Invalid price"]);
    exit;
}

if (!isset($user_id)) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}

// --- Fetch business_type_id ---
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

// --- Resolve category_id ---
$category_id = null;
if ($category_name) {
    $stmt = $conn->prepare("SELECT category_id FROM categories WHERE user_id=? AND business_type_id=? AND LOWER(name)=?");
    $stmt->bind_param("iis", $user_id, $business_type_id, $category_name);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $category_id = $res['category_id'] ?? null;
}

// --- Resolve subcategory_id ---
$subcategory_id = null;
if ($subcategory_name && $category_id) {
    $stmt = $conn->prepare("SELECT subcategory_id FROM subcategories WHERE category_id=? AND LOWER(name)=?");
    $stmt->bind_param("is", $category_id, $subcategory_name);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $subcategory_id = $res['subcategory_id'] ?? null;
}

// --- Check for duplicate product ---
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
    http_response_code(409); // Conflict
    echo json_encode(["success" => false, "msg" => "Product already exists"]);
    exit;
}

// --- Insert product ---
$stmt = $conn->prepare("
    INSERT INTO products (user_id, business_type_id, name, price, unit, created_at, category_id, subcategory_id) 
    VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)
");
$stmt->bind_param("iisdsii", $user_id, $business_type_id, $name, $price, $unit, $category_id, $subcategory_id);

if ($stmt->execute()) {
    $product_id = $stmt->insert_id;

    echo json_encode([
        "success" => true,
        "msg" => "Product added successfully",
        "product" => [
            "product_id" => $product_id,
            "user_id" => $user_id,
            "business_type_id" => $business_type_id,
            "name" => $name,
            "price" => number_format($price, 2),
            "unit" => $unit,
            "created_at" => date("Y-m-d H:i:s"),
            "category_id" => $category_id,
            "subcategory_id" => $subcategory_id
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "msg" => "Failed to add product"]);
}
$stmt->close();
