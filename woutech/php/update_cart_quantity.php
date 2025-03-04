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
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if ($user_id <= 0 || $product_id <= 0 || $quantity < 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID, product ID, or quantity."]);
    exit;
}

// Get the available quantity_left for the product
$sql_product = "SELECT quantity_left, product_name FROM product WHERE product_id = ?";
$stmt_product = $conn->prepare($sql_product);
$stmt_product->bind_param("i", $product_id);
$stmt_product->execute();
$result_product = $stmt_product->get_result();

if ($result_product->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Product not found."]);
    $stmt_product->close();
    $conn->close();
    exit;
}

$product = $result_product->fetch_assoc();
$quantity_left = $product['quantity_left'];
$product_name = $product['product_name'];

$stmt_product->close();

// Check if the cart entry exists for this user and product (where is_checkout = 0)
$sql_check = "SELECT quantity_added FROM user_cart WHERE user_id = ? AND product_id = ? AND is_checkout = 0";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $product_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Cart entry exists, validate the new quantity
    if ($quantity > $quantity_left) {
        echo json_encode([
            "success" => false, 
            "message" => "The quantity added to the cart has reached max! You can only add $quantity_left for $product_name"
        ]);
    } else {
        // Update the quantity_added in the existing cart entry
        $sql_update = "UPDATE user_cart SET quantity_added = ? WHERE user_id = ? AND product_id = ? AND is_checkout = 0";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $quantity, $user_id, $product_id);
        
        if ($stmt_update->execute()) {
            echo json_encode(["success" => true, "message" => "Quantity updated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error updating quantity: " . $conn->error]);
        }
        $stmt_update->close();
    }
} else {
    echo json_encode(["success" => false, "message" => "Cart entry not found for this user and product."]);
}

$stmt_check->close();
$conn->close();
?>