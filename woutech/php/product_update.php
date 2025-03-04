<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id'] ?? 0);
    $product_image = $_POST['product_image'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $quantity_left = intval($_POST['quantity_left'] ?? 0);
    $product_category = $_POST['product_category'] ?? '';
    $operating_system = $_POST['operating_system'] ?? '';
    $processor = $_POST['processor'] ?? '';
    $graphic_card = $_POST['graphic_card'] ?? '';
    $memory = $_POST['memory'] ?? '';
    $display_resolution = $_POST['display_resolution'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($product_id <= 0 || empty($product_name) || $price <= 0) {
        echo json_encode(["success" => false, "message" => "Valid product ID, name, and price are required"]);
        exit;
    }

    $sql = "UPDATE Product SET product_image = ?, product_name = ?, price = ?, quantity_left = ?, product_category = ?, operating_system = ?, processor = ?, graphic_card = ?, memory = ?, display_resolution = ?, description = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdssssssssi", $product_image, $product_name, $price, $quantity_left, $product_category, $operating_system, $processor, $graphic_card, $memory, $display_resolution, $description, $product_id);

    if ($stmt->execute()) {
        $response = ["success" => true, "message" => "Product updated successfully"];
    } else {
        $response = ["success" => false, "message" => "Error updating product: " . $stmt->error];
    }

    $stmt->close();
} else {
    $response = ["success" => false, "message" => "Invalid request method"];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>