<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file for debugging (optional, remove in production)
ini_set('log_errors', 1);
$logFile = dirname(__FILE__) . '/error.log';
ini_set('error_log', $logFile);

// Check if log file is writable
if (!is_writable(dirname($logFile))) {
    error_log("Error log directory is not writable");
    die(json_encode(['success' => false, 'message' => 'Server configuration error']));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $errorMsg = 'Database connection failed: ' . $conn->connect_error;
    error_log($errorMsg);
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit();
}

try {
    // Current date
    $today = date('Y-m-d');

    // Product Distribution
    $productStmt = $conn->prepare("
        SELECT p.product_name, SUM(od.order_detail_quantity) as total 
        FROM order_detail od 
        JOIN product p ON od.product_id = p.product_id 
        GROUP BY p.product_name
    ");
    if ($productStmt === false) {
        throw new Exception("Prepare failed (Product): " . $conn->error);
    }
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    if ($productResult === false) {
        throw new Exception("Get result failed (Product): " . $conn->error);
    }
    $productData = $productResult->fetch_all(MYSQLI_ASSOC);
    error_log("Product Data Debug: " . json_encode($productData));
    $productLabels = array_column($productData, 'product_name');
    $productValues = array_column($productData, 'total');

    // Processing Orders
    $processingStmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM order_tracking 
        WHERE order_status = 'Processing'
    ");
    if ($processingStmt === false) {
        throw new Exception("Prepare failed (Processing): " . $conn->error);
    }
    $processingStmt->execute();
    $processingResult = $processingStmt->get_result();
    if ($processingResult === false) {
        throw new Exception("Get result failed (Processing): " . $conn->error);
    }
    $processingOrders = $processingResult->fetch_assoc();
    error_log("Processing Orders Debug: " . json_encode($processingOrders));

    // Latest Order Tracking
    $latestOrdersStmt = $conn->prepare("
        SELECT order_id, ua.user_name, order_pay_at, order_status, om.order_method, total, pm.payment_method FROM order_tracking ot
        JOIN user_account ua ON ot.user_id = ua.user_id
        JOIN order_method om ON ot.order_method_id = om.order_method_id 
        JOIN payment_method pm ON ot.payment_method_id = pm.payment_method_id
        ORDER BY order_pay_at DESC 
        LIMIT 3
    ");
    if ($latestOrdersStmt === false) {
        throw new Exception("Prepare failed (Latest Orders): " . $conn->error);
    }
    $latestOrdersStmt->execute();
    $latestOrdersResult = $latestOrdersStmt->get_result();
    if ($latestOrdersResult === false) {
        throw new Exception("Get result failed (Latest Orders): " . $conn->error);
    }
    $latestOrders = $latestOrdersResult->fetch_all(MYSQLI_ASSOC);
    error_log("Latest Orders Debug: " . json_encode($latestOrders));

    // Today's and Upcoming Bookings
    $bookingStmt = $conn->prepare("
        SELECT b.booking_id, ua.user_name, st.service_type, sm.service_method, b.booking_date, b.booking_time, b.book_address_1,b.book_address_2,b.book_postal_code,b.book_city,b.book_state,b.book_country FROM booking b
        JOIN user_account ua ON b.user_id = ua.user_id
		JOIN service_type st ON b.service_type_id = st.service_type_id
        JOIN service_method sm ON b.service_type_id = sm.service_method_id
		WHERE booking_date >= ?
        ORDER BY booking_date ASC 
        LIMIT 4
    ");
    if ($bookingStmt === false) {
        throw new Exception("Prepare failed (Bookings): " . $conn->error);
    }
    $bookingStmt->bind_param("s", $today);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    if ($bookingResult === false) {
        throw new Exception("Get result failed (Bookings): " . $conn->error);
    }
    $bookings = $bookingResult->fetch_all(MYSQLI_ASSOC);
    error_log("Bookings Debug: " . json_encode($bookings));

    // Latest Registered Users
    $usersStmt = $conn->prepare("
        SELECT user_id, user_name, email, phone_no, created_at 
        FROM user_account 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    if ($usersStmt === false) {
        throw new Exception("Prepare failed (Users): " . $conn->error);
    }
    $usersStmt->execute();
    $usersResult = $usersStmt->get_result();
    if ($usersResult === false) {
        throw new Exception("Get result failed (Users): " . $conn->error);
    }
    $latestUsers = $usersResult->fetch_all(MYSQLI_ASSOC);
    error_log("Latest Users Debug: " . json_encode($latestUsers));

    // Return JSON response
    echo json_encode([
        'success' => true,
        'productData' => ['labels' => $productLabels, 'values' => $productValues],
        'processingOrders' => $processingOrders,
        'latestOrders' => $latestOrders,
        'bookings' => $bookings,
        'latestUsers' => $latestUsers
    ]);

} catch (Exception $e) {
    $errorMsg = 'Error: ' . $e->getMessage();
    error_log($errorMsg);
    echo json_encode(['success' => false, 'error' => $errorMsg]);
} finally {
    $conn->close();
}
?>