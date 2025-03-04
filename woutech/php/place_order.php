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

// Get data from the frontend
$data = json_decode(file_get_contents('php://input'), true);

// Retrieve user_id and type_id from the incoming data (sent from localStorage via frontend)
$user_id = isset($data['user_id']) ? intval($data['user_id']) : null;
$type_id = isset($data['type_id']) ? intval($data['type_id']) : null;

$subtotal = floatval($data['subtotal']);
$shipping_fee = floatval($data['shipping_fee']);
$grand_total = floatval($data['grand_total']);
$address_1 = $data['address']['address_1'] ?? '';
$address_2 = isset($data['address']['address_2']) && trim($data['address']['address_2']) === '' ? null : trim($data['address']['address_2']);
$postal_code = isset($data['address']['postal_code']) ? intval($data['address']['postal_code']) : null;
$city = $data['address']['city'] ?? '';
$state = $data['address']['state'] ?? '';
$country = $data['address']['country'] ?? '';
$payment_method = $data['payment_method'];
$active_tab = $data['active_tab'];
$order_status = 'Processing';

// Validate user_id
if (!$user_id) {
    die(json_encode(['success' => false, 'message' => 'User ID is required']));
}

// Map payment method to payment_method_id
$payment_method_map = [
    'paypal' => 1,
    'creditCard' => 2,
    'touchNgo' => 3,
    'grabPay' => 4
];
$payment_method_id = $payment_method_map[$payment_method] ?? 1; // Default to 1 if not found

// Map active tab to order_method_id
$order_method_id = ($active_tab === 'delivery') ? 1 : 2; // 1 for Item Delivering, 2 for Self Pickup

$conn->begin_transaction();

try {
    // Insert into order_tracking table
    $sql = "INSERT INTO order_tracking (user_id, order_pay_at, order_status, order_method_id, payment_method_id, address_1, address_2, postal_code, city, state, country, total) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiisissssi", $user_id, $order_status, $order_method_id, $payment_method_id, $address_1, $address_2, $postal_code, $city, $state, $country, $grand_total);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Fetch cart items for the user
    $cart_sql = "SELECT product_id, quantity_added FROM user_cart WHERE user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    // Insert into order_detail and update product quantity_left
    $detail_sql = "INSERT INTO order_detail (order_id, product_id, order_detail_quantity) VALUES (?, ?, ?)";
    $detail_stmt = $conn->prepare($detail_sql);
    $update_product_sql = "UPDATE product SET quantity_left = quantity_left - ? WHERE product_id = ?";
    $update_product_stmt = $conn->prepare($update_product_sql);

    while ($cart_row = $cart_result->fetch_assoc()) {
        $product_id = $cart_row['product_id'];
        $quantity = $cart_row['quantity_added'];

        // Insert into order_detail
        $detail_stmt->bind_param("iii", $order_id, $product_id, $quantity);
        $detail_stmt->execute();

        // Update quantity_left in product table
        $update_product_stmt->bind_param("ii", $quantity, $product_id);
        $update_product_stmt->execute();

        // Check if quantity_left becomes negative
        $check_sql = "SELECT quantity_left FROM product WHERE product_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $new_quantity = $check_result->fetch_assoc()['quantity_left'];
        if ($new_quantity < 0) {
            throw new Exception("Insufficient stock for product ID $product_id");
        }
        $check_stmt->close();
    }

    // Delete cart items
    $delete_sql = "DELETE FROM user_cart WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $order_id]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Order placement failed: ' . $e->getMessage()]);
}

// Close statements
$stmt->close();
$cart_stmt->close();
$detail_stmt->close();
$update_product_stmt->close();
$delete_stmt->close();
$conn->close();
?>