<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get data from POST request
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($user_id <= 0 || $product_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID or product ID."]);
    exit;
}

// Delete the cart item where user_id, product_id, and is_checkout = 0
$sql_delete = "DELETE FROM user_cart WHERE user_id = ? AND product_id = ? AND is_checkout = 0";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("ii", $user_id, $product_id);

if ($stmt_delete->execute()) {
    if ($stmt_delete->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Item deleted successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "No matching cart item found or already checked out."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Error deleting item: " . $conn->error]);
}

$stmt_delete->close();
$conn->close();
?>