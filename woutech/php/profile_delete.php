<?php
$conn = new mysqli("localhost", "root", "", "woutech");
if ($conn->connect_error) die(json_encode(['success' => false, 'message' => 'Connection failed']));

$user_id = intval($_POST['user_id']);
$sql = "DELETE FROM user_account WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
$stmt->close();
$conn->close();
?>