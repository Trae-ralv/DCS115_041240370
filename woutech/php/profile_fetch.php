<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die(json_encode(['success' => false, 'message' => 'Database connection failed']));

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$sql = "SELECT user_name, email, password, phone_no, company_name FROM user_account WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$conn->close();
echo json_encode($user ? $user : []);
?>