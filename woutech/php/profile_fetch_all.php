<?php
// profile_fetch_all.php

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = 'localhost';
$dbname = 'woutech';
$username = 'root';
$password = '';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM user_account ORDER BY created_at DESC");
    $stmt->execute();

    // Fetch all rows as an associative array
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if users exist
    if ($users) {
        // Return JSON response with success
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $users]);
    } else {
        // No users found
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => [], 'message' => 'No users found']);
    }
} catch (PDOException $e) {
    // Error handling
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}

// Close the connection (optional, as PDO closes it automatically when the script ends)
$pdo = null;
?>