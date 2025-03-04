<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Fetch cart data for the given user_id
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 1; // Default to 1, replace with session data
$sql = "SELECT p.product_id, p.product_image, p.product_name, p.price, p.quantity_left, uc.quantity_added 
        FROM user_cart uc
        JOIN product p ON uc.product_id = p.product_id
        WHERE uc.user_id = ? AND uc.is_checkout = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

echo json_encode($cart_items);

$stmt->close();
$conn->close();
?>