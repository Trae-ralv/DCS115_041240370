<?php
$conn = new mysqli("localhost", "root", "", "woutech");
if ($conn->connect_error) die(json_encode(['success' => false, 'message' => 'Connection failed']));

$user_name = $conn->real_escape_string($_POST['user_name']);
$email = $conn->real_escape_string($_POST['email']);
$password = $conn->real_escape_string($_POST['password']);
$phone_no = $conn->real_escape_string($_POST['phone_no']);
$type_id = intval($_POST['type_id']);
$company_name = $type_id == 2 ? $conn->real_escape_string($_POST['company_name']) : null;

$sql = "INSERT INTO user_account (user_name, email, password, phone_no, type_id, company_name) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssiss", $user_name, $email, $password, $phone_no, $type_id, $company_name);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
$stmt->close();
$conn->close();
?>