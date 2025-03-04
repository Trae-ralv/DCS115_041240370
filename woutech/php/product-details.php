<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$sql = "SELECT product_id, product_image, product_name, price, quantity_left, product_category, operating_system, processor, graphic_card, memory, display_resolution, description FROM Product WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$product = $result->fetch_assoc();

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($product ? $product : []);
?>