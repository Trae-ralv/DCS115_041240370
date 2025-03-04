<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "woutech";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get email and token from URL
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

// Validate input
if (empty($email) || empty($token)) {
    die("Invalid request. Please provide both email and token.");
}

// Check if the token is valid and not expired
$stmt = $conn->prepare("SELECT user_id, expiry_date, is_used FROM password_reset WHERE token = ? AND user_id = (SELECT user_id FROM user_account WHERE email = ?)");
$stmt->execute([$token, $email]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("Invalid or expired token.");
}

$currentDate = new DateTime();
$expiryDate = new DateTime($reset['expiry_date']);
if ($currentDate > $expiryDate) {
    die("This reset link has expired. Please request a new one.");
}

if ($reset['is_used'] == 1) {
    die("This reset link has already been used.");
}

// Handle form submission for new password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $newPassword = $_POST['new_password'] ?? '';

    // Validate password (e.g., minimum length of 8 characters)
    if (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // Hash the new password (using password_hash for security)
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the user_account table
        $stmt = $conn->prepare("UPDATE user_account SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);

        // Mark the reset token as used
        $stmt = $conn->prepare("UPDATE password_reset SET is_used = 1 WHERE token = ?");
        $stmt->execute([$token]);

        // Redirect to login page or show success message
        header("Location: ../login.html?message=Password reset successful. Please log in.");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: rgb(30 31 32 / 1);
            color: rgb(227 227 227 / 1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: rgb(227 227 227 / 1) !important;
            border-color: rgb(118, 118, 118) !important;
            background-color: rgb(30 31 32 / 1) !important;
        }


        .form-control::placeholder {
            color: rgb(227 227 227 / 1) !important;
        }

        button {
            background-color: #00AC6C;
            color: white;
            padding: 5px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px 0 0 0;
        }

        button:hover {
            background-color: rgb(0, 137, 87);
        }

        .main-container {
            width: 60%;
            padding: 12.5% 0;
        }

        .sub-container {
            background-color: rgb(14 14 14 / 1);
            border-radius: 20px;
            padding: 28px;
            width: 100%;
        }

        .sub-container-2 {
            margin: 15px 0 0 20px;
        }

        p {
            font-size: 11px !important;
        }

        .body-container {
            justify-items: center;
            display: flex;
        }

        .sub-container-2 p a {
            text-decoration: none;
            color: rgb(227 227 227 / 1);
            padding: 10px;
            border-radius: 8px;
        }

        .sub-container-2 p a:hover {
            background-color: rgb(46, 47, 49);
        }

        .error {
            color: #dc3545;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="body-container">
        <div class="container-fluid main-container">
            <div class="sub-container">
                <img src="../images/logo.webp" height="50px">
                <div class="row">
                    <div class="col-5">
                        <h3 class="my-3">Password Reset</h3>
                        <p>Enter your new password, password are required to have at least 8 characteres</p>
                    </div>
                    <div class="col-1"></div>
                    <div class="col-6 my-3">
                        <form method="POST" action="">
                            <div class="form-group">
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                    placeholder="Password" pattern=".{8,}"  required>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword"
                                    placeholder="Confirm Password" required>
                            </div>
                            <div class="text-end">
                                <button type="submit">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="sub-container-2">
                <p><a href="../login.html">Back to Login</a></p>
            </div>
        </div>
    </div>
<script>
        function validateForm() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const errorMessage = document.querySelector('.error');

            if (newPassword !== confirmPassword) {
                errorMessage.textContent = 'Passwords do not match!';
                return false;
            }
            errorMessage.textContent = ''; // Clear error if valid
            return true;
        }
    </script>
</body>

</html>

<?php
$conn = null;
?>