<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start with a base query
$sql = "SELECT product_id, product_image, product_name, price, quantity_left FROM Product WHERE 1=1";
$params = [];
$types = "";

// Debug: Log received parameters
error_log("Received GET: " . print_r($_GET, true));

// Handle search parameter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $sql .= " AND (product_name LIKE ? OR product_category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
    error_log("Added search condition: $search");
}

// Handle price range
if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
    $sql .= " AND price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
    error_log("Added min_price: " . $_GET['min_price']);
}

if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
    $sql .= " AND price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
    error_log("Added max_price: " . $_GET['max_price']);
}

// Handle product_type parameter (using LIKE for partial match)
if (isset($_GET['product_type']) && !empty($_GET['product_type'])) {
    $productType = $_GET['product_type'];
    $sql .= " AND product_category LIKE ?"; // Changed to LIKE with wildcard
    $params[] = "%$productType%"; // Add wildcards for partial match
    $types .= "s";
    error_log("Added product_type (LIKE): $productType");
}

// Handle brand parameter (using LIKE for partial match)
if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $brand = $_GET['brand'];
    $sql .= " AND product_category LIKE ?"; // Changed to LIKE with wildcard
    $params[] = "%$brand%"; // Add wildcards for partial match
    $types .= "s";
    error_log("Added brand (LIKE): $brand");
}

// Debug: Log the final SQL query
error_log("Final SQL: $sql with types: $types and params: " . print_r($params, true));

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    die(json_encode(['error' => 'Query preparation failed: ' . $conn->error]));
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
    error_log("Binding parameters: $types with " . print_r($params, true));
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Debug: Log the number of results
error_log("Found " . count($products) . " products");

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($products);
?>