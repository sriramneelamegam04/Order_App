<?php
require __DIR__ . '/../auth/middleware.php';
require __DIR__ . '/../config/response.php';

// ✅ Handle CORS preflight
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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


// --- Razorpay config – replace with your platform credentials
$razorpayKeyId = "rzp_test_xxxxx";
$razorpayKeySecret = "your_secret_here";

// --- Plan price in paise (₹3999.00)
$amountPaise = 399900;

// --- Create Razorpay order
$orderData = [
    "amount" => $amountPaise,
    "currency" => "INR",
    "receipt" => "sub_" . $user_id . "_" . time(),
    "payment_capture" => 1
];

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_USERPWD, $razorpayKeyId . ':' . $razorpayKeySecret);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
$response = curl_exec($ch);

if (curl_errno($ch)) {
    sendError("Curl error: " . curl_error($ch), 500);
}
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($statusCode !== 200) {
    sendError("Failed to create Razorpay order", 500);
}

$order = json_decode($response, true);

// --- Store a pending subscription until payment verification
$stmt = $conn->prepare(
    "INSERT INTO subscriptions (user_id, plan, start_date, end_date, status) 
     VALUES (?, 'yearly', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'pending')"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// --- Return order info
echo json_encode([
    "success" => true,
    "order" => $order,
    "razorpay_key_id" => $razorpayKeyId
]);
?>
