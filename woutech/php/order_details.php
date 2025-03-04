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

// Get order_id from GET request
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

if ($order_id) {
    // Fetch order details
    $sql = "SELECT ot.order_id, ot.order_pay_at, ot.order_status, ot.total, pm.payment_method, ot.address_1, ot.address_2, ot.postal_code, ot.city, ot.state, ot.country
            FROM order_tracking ot 
            JOIN payment_method pm ON ot.payment_method_id = pm.payment_method_id 
            WHERE ot.order_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]));
    }
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order) {
        // Fetch products for the order
        $product_sql = "SELECT p.product_name, od.order_detail_quantity 
                        FROM order_detail od 
                        JOIN product p ON od.product_id = p.product_id 
                        WHERE od.order_id = ?";
        $product_stmt = $conn->prepare($product_sql);
        if (!$product_stmt) {
            die(json_encode(['success' => false, 'message' => 'Product SQL prepare failed: ' . $conn->error]));
        }
        $product_stmt->bind_param("i", $order_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        $order['products'] = $product_result->fetch_all(MYSQLI_ASSOC);
        $product_stmt->close();

        echo json_encode(['success' => true, 'order' => $order]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No order ID provided']);
}

$stmt->close();
$conn->close();
?>