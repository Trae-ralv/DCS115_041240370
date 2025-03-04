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

// Fetch all orders with joined data
$sql = "SELECT ot.order_id,p.product_name,od.order_detail_quantity, ua.user_name, ot.order_pay_at, ot.order_status, ot.total, pm.payment_method, ot.address_1, ot.address_2, ot.postal_code, ot.city, ot.state, ot.country, om.order_method
        FROM order_detail od 
        JOIN order_tracking ot ON od.order_id = ot.order_id
        JOIN payment_method pm ON ot.payment_method_id = pm.payment_method_id
        JOIN product p ON od.product_id = p.product_id
        JOIN user_account ua ON ot.user_id = ua.user_id
        JOIN order_method om ON ot.order_method_id = om.order_method_id";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]));
}
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

if ($orders) {
    echo json_encode(['success' => true, 'orders' => $orders]);
} else {
    echo json_encode(['success' => true, 'orders' => []]);
}

$stmt->close();
$conn->close();
?>