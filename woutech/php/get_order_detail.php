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

// Get user_id from GET request (sent from frontend localStorage)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($user_id) {
    // Fetch all orders for the user from order_tracking
    $sql = "SELECT ot.order_id, ot.order_pay_at, ot.order_status, ot.total, pm.payment_method
            FROM order_tracking ot 
            JOIN payment_method pm ON ot.payment_method_id = pm.payment_method_id 
            WHERE ot.user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]));
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $orders = $result->fetch_all(MYSQLI_ASSOC);

    if ($orders) {
        // Fetch products for each order
        $ordersWithProducts = [];
        foreach ($orders as &$order) {
            $product_sql = "SELECT p.product_image, p.product_name, od.order_detail_quantity 
                            FROM order_detail od 
                            JOIN product p ON od.product_id = p.product_id 
                            WHERE od.order_id = ?";
            $product_stmt = $conn->prepare($product_sql);
            if (!$product_stmt) {
                die(json_encode(['success' => false, 'message' => 'Product SQL prepare failed: ' . $conn->error]));
            }
            $product_stmt->bind_param("i", $order['order_id']);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            $order['products'] = $product_result->fetch_all(MYSQLI_ASSOC);
            $ordersWithProducts[] = $order;
            $product_stmt->close();
        }

        echo json_encode(['success' => true, 'orders' => $ordersWithProducts]);
    } else {
        echo json_encode(['success' => true, 'orders' => []]); // Changed to success with empty array for consistency with frontend
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No user ID provided']);
}

$stmt->close();
$conn->close();
?>