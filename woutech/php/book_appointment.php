<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$service_type_id = isset($data['service_type_id']) ? intval($data['service_type_id']) : 0;
$service_method_id = isset($data['service_method_id']) ? intval($data['service_method_id']) : 0;
$book_address_1 = isset($data['book_address_1']) ? $conn->real_escape_string($data['book_address_1']) : '';
$book_address_2 = isset($data['book_address_2']) ? $conn->real_escape_string($data['book_address_2']) : '';
$book_postal_code = isset($data['book_postal_code']) ? intval($data['book_postal_code']) : 0;
$book_city = isset($data['book_city']) ? $conn->real_escape_string($data['book_city']) : '';
$book_state = isset($data['book_state']) ? $conn->real_escape_string($data['book_state']) : '';
$book_country = isset($data['book_country']) ? $conn->real_escape_string($data['book_country']) : '';
$book_description = isset($data['book_description']) ? $conn->real_escape_string($data['book_description']) : '';
$booking_date = isset($data['booking_date']) ? $data['booking_date'] : '';
$booking_time = isset($data['booking_time']) ? $data['booking_time'] : '';

// Validate required fields
if (!$user_id || !$service_type_id || !$service_method_id || !$book_address_1 || !$book_postal_code || !$book_city || !$book_state || !$book_country || !$book_description || !$booking_date || !$booking_time) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    $conn->close();
    exit();
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO booking (user_id, service_type_id, service_method_id, booking_date, booking_time, book_address_1, book_address_2, book_postal_code, book_city, book_state, book_country, book_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiissssissss", $user_id, $service_type_id, $service_method_id, $booking_date, $booking_time, $book_address_1, $book_address_2, $book_postal_code, $book_city, $book_state, $book_country, $book_description);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment booked successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error booking appointment: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>