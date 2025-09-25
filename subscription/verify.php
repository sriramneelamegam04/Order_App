<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// ✅ Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// ✅ Method validation
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "msg" => "Method Not Allowed. Use POST"]);
    exit;
}

// ✅ Authenticate user
$user = get_authenticated_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(["success" => false, "msg" => "Unauthorized"]);
    exit;
}
$user_id = $user['user_id'];

// ✅ Input validation
$input = json_decode(file_get_contents("php://input"), true);
if (empty($input['razorpay_payment_id']) || empty($input['razorpay_order_id']) || empty($input['razorpay_signature'])) {
    sendError("Missing Razorpay payment details", 400);
}

// --- Razorpay secret
$razorpayKeySecret = "your_secret_here";

// --- Signature verification
$generated_signature = hash_hmac(
    'sha256',
    $input['razorpay_order_id'] . "|" . $input['razorpay_payment_id'],
    $razorpayKeySecret
);

if ($generated_signature !== $input['razorpay_signature']) {
    sendError("Invalid payment signature", 400);
}

// --- Mark subscription as active
$stmt = $conn->prepare("
    UPDATE subscriptions 
    SET status='active' 
    WHERE user_id=? AND status='pending' 
    ORDER BY sub_id DESC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// ✅ Response
sendSuccess("Payment verified & subscription activated", ["plan" => "yearly"]);
?>
