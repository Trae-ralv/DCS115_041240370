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

    if ($product_id <= 0) {
        echo json_encode(["success" => false, "message" => "Valid product ID is required"]);
        exit;
    }

    $sql = "DELETE FROM Product WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $response = ["success" => true, "message" => "Product deleted successfully"];
    } else {
        $response = ["success" => false, "message" => "Error deleting product: " . $stmt->error];
    }

    $stmt->close();
} else {
    $response = ["success" => false, "message" => "Invalid request method"];
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>