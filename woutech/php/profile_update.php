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

// Since type_id isn’t sent via POST, fetch it from the database
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
    // Sanitize POST data
    $user_name = $conn->real_escape_string($_POST['user_name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $conn->real_escape_string($_POST['password'] ?? '');
    $phone_no = $conn->real_escape_string($_POST['phone_no'] ?? '');

    if ($type_id === 1) {
        $sql = "UPDATE user_account SET user_name = ?, email = ?, password = ?, phone_no = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
            $conn->close();
            exit;
        }
        $stmt->bind_param("ssssi", $user_name, $email, $password, $phone_no, $user_id);
    } elseif ($type_id === 2) {
        $company_name = $conn->real_escape_string($_POST['company_name'] ?? '');
        $sql = "UPDATE user_account SET user_name = ?, company_name = ?, email = ?, password = ?, phone_no = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement']);
            $conn->close();
            exit;
        }
        $stmt->bind_param("sssssi", $user_name, $company_name, $email, $password, $phone_no, $user_id);
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