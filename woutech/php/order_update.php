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

// Get order_id and order_status from POST request
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
$order_status = isset($_POST['order_status']) ? $_POST['order_status'] : null;

if ($order_id && $order_status) {
    // Update order status
    $sql = "UPDATE order_tracking SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]));
    }
    $stmt->bind_param("si", $order_status, $order_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing parameters']);
}

$conn->close();
?>