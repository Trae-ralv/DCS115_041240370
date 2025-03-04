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

// Fetch all bookings with joined data
$sql = "SELECT b.booking_id, ua.user_name, st.service_type, sm.service_method, b.booking_date, b.booking_time, b.book_address_1, b.book_address_2, b.book_postal_code, b.book_city, b.book_state, b.book_country, b.book_description
        FROM booking b
        JOIN service_type st ON b.service_type_id = st.service_type_id
        JOIN service_method sm ON b.service_method_id = sm.service_method_id
        JOIN user_account ua ON b.user_id = ua.user_id";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]));
}
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

if ($bookings) {
    echo json_encode(['success' => true, 'bookings' => $bookings]);
} else {
    echo json_encode(['success' => true, 'bookings' => []]);
}

$stmt->close();
$conn->close();
?>