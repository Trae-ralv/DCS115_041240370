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

$data = json_decode(file_get_contents('php://input'), true);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;
$user_id = isset($data['user_id']) ? intval($data['user_id']) : null; // Get user_id from frontend
$order_status = 'Received'; // Explicitly set to 'Received'

if ($order_id && $user_id) {
    // Verify the order belongs to the user
    $check_sql = "SELECT COUNT(*) FROM order_tracking WHERE order_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        die(json_encode(['success' => false, 'message' => 'Check SQL prepare failed: ' . $conn->error]));
    }
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $order_exists = $check_result->fetch_row()[0] > 0;
    $check_stmt->close();

    if (!$order_exists) {
        echo json_encode(['success' => false, 'message' => 'Order not found or not owned by this user']);
        $conn->close();
        exit;
    }

    error_log("Updating order_id: $order_id to status: $order_status for user_id: $user_id");
    $sql = "UPDATE order_tracking SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $order_status, $order_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order status updated to Received']);
        } else {
            error_log("Execute failed: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Failed to update order status: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID or user ID']);
}

$conn->close();
?>