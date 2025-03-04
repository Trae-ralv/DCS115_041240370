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

$booking_date = isset($_GET['booking_date']) ? $_GET['booking_date'] : '';

if ($booking_date) {
    $sql = "SELECT booking_time FROM booking WHERE booking_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $booking_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode($bookings);
} else {
    echo json_encode([]);
}

$stmt->close();
$conn->close();
?>