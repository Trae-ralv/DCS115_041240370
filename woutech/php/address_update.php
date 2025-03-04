<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "woutech";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Get user_id from POST
$user_id = intval($_POST['user_id'] ?? 0);
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    $conn->close();
    exit;
}

// Fetch type_id from the database
$sql = "SELECT type_id FROM user_account WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $conn->close();
    exit;
}
$type_id = intval($user['type_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data, handle null explicitly for address_2
    $address_1 = $conn->real_escape_string($_POST['address_1'] ?? '');
    $address_2 = isset($_POST['address_2']) ? $_POST['address_2'] : null; // Avoid escaping initially
    if ($address_2 === 'null' || $address_2 === '') { // Convert string 'null' or empty to NULL
        $address_2 = null;
    } else {
        $address_2 = $conn->real_escape_string($address_2); // Escape only if not null
    }
    $postal_code = $conn->real_escape_string($_POST['postal_code'] ?? '');
    $state = $conn->real_escape_string($_POST['state'] ?? '');
    $city = $conn->real_escape_string($_POST['city'] ?? '');
    $country = $conn->real_escape_string($_POST['country'] ?? '');

    if ($type_id === 1) {
        $sql = "UPDATE address SET address_1 = ?, address_2 = ?, postal_code = ?, state = ?, city = ?, country = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
            $conn->close();
            exit;
        }
        // Bind parameters, allowing null for address_2
        $stmt->bind_param("ssssssi", $address_1, $address_2, $postal_code, $state, $city, $country, $user_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid type_id']);
        $conn->close();
        exit;
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>