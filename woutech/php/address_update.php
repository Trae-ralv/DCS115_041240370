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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data, handle null explicitly for address_2
    $address_1 = $conn->real_escape_string($_POST['address_1'] ?? '');
    $address_2 = isset($_POST['address_2']) ? $_POST['address_2'] : null;
    if ($address_2 === 'null' || $address_2 === '') {
        $address_2 = null;
    } else {
        $address_2 = $conn->real_escape_string($address_2);
    }
    $postal_code = $conn->real_escape_string($_POST['postal_code'] ?? '');
    $state = $conn->real_escape_string($_POST['state'] ?? '');
    $city = $conn->real_escape_string($_POST['city'] ?? '');
    $country = $conn->real_escape_string($_POST['country'] ?? '');

    // Check if address exists for this user
    $check_sql = "SELECT COUNT(*) FROM address WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        // Address exists, perform UPDATE
        $sql = "UPDATE address SET address_1 = ?, address_2 = ?, postal_code = ?, state = ?, city = ?, country = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare update statement']);
            $conn->close();
            exit;
        }
        $stmt->bind_param("ssssssi", $address_1, $address_2, $postal_code, $state, $city, $country, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Address updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made to address']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update address: ' . $stmt->error]);
        }
    } else {
        // Address doesn't exist, perform INSERT
        $sql = "INSERT INTO address (user_id, address_1, address_2, postal_code, state, city, country) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare insert statement']);
            $conn->close();
            exit;
        }
        $stmt->bind_param("issssss", $user_id, $address_1, $address_2, $postal_code, $state, $city, $country);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Address created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create address: ' . $stmt->error]);
        }
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>