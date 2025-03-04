<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die(json_encode(['success' => false, 'message' => 'Database connection failed']));

// Get data from POST request
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0; // Use user_id from localStorage

if ($product_id <= 0 || $quantity <= 0 || $user_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product ID, quantity, or user ID."]);
    exit;
}

// Get product details (quantity_left and product_name) from the product table
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

// Check if the product and user already exist in the cart (where is_checkout = 0)
$sql_check = "SELECT quantity_added FROM user_cart WHERE user_id = ? AND product_id = ? AND is_checkout = 0";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $product_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Existing cart entry found, get the current quantity_added
    $current_quantity = $result_check->fetch_assoc()['quantity_added'];
    $new_quantity = $current_quantity + $quantity;

    // Check if the new quantity exceeds the available quantity_left
    if ($new_quantity > $quantity_left) {
        echo json_encode([
            "success" => false, 
            "message" => "The quantity added to the cart has reached max! You can only add $quantity_left for $product_name"
        ]);
    } else {
        // Update the existing cart entry with the new quantity
        $sql_update = "UPDATE user_cart SET quantity_added = ? WHERE user_id = ? AND product_id = ? AND is_checkout = 0";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $new_quantity, $user_id, $product_id);
        
        if ($stmt_update->execute()) {
            echo json_encode(["success" => true, "message" => "Item quantity updated in cart successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error updating cart: " . $conn->error]);
        }
        $stmt_update->close();
    }
} else {
    // No existing cart entry found, insert a new record
    if ($quantity > $quantity_left) {
        echo json_encode([
            "success" => false, 
            "message" => "The quantity added to the cart has reached max! You can only add $quantity_left for $product_name"
        ]);
    } else {
        $sql_insert = "INSERT INTO user_cart (user_id, product_id, quantity_added, added_at, is_checkout) VALUES (?, ?, ?, NOW(), 0)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iii", $user_id, $product_id, $quantity);
        
        if ($stmt_insert->execute()) {
            echo json_encode(["success" => true, "message" => "Item added to cart successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error adding to cart: " . $conn->error]);
        }
        $stmt_insert->close();
    }
}

$stmt_check->close();
$conn->close();
?>