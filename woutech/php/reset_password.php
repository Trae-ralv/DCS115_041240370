<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "woutech";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->beginTransaction(); // Start transaction
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Form data
$email = $_POST['email'] ?? '';

// Since email is pre-checked, fetch user details directly
$stmt = $conn->prepare("SELECT user_id, user_name FROM user_account WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // This shouldn't happen due to pre-check, but kept as a fallback
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
    exit;
}

// Generate a unique reset token
$token = bin2hex(random_bytes(16)); // 32-character hex string

// Include PHPMailer files
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Send reset token email using PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host (e.g., smtp.gmail.com for Gmail)
    $mail->SMTPAuth = true;
    $mail->Username = 'smtptestingwp@gmail.com'; // Replace with your SMTP email
    $mail->Password = 'sypb kqkp ewlh wuhw'; // Use an App Password for Gmail (see Gmail 2FA setup)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    //$mail->SMTPDebug = 2;

    // Sender and recipient
    $mail->setFrom('smtptestingwp@gmail.com', 'Woutech Registration');
    $mail->addAddress($email, $username);

    // Email content
    $mail->isHTML(true);
    $resetLink = "http://localhost/woutech/php/verify_reset.php?email=" . urlencode($email) . "&token=" . urlencode($token);
    $mail->Subject = 'Reset Your Password';
    $mail->Body = "Hello {$user['user_name']},<br><br>You requested a password reset. Please use this link to reset your password: <a href='$resetLink'>Reset Password</a><br>Or enter this code on the reset page: <strong>$token</strong><br><br>This link expires in 24 hours.<br><br>Thank you!";
    $mail->AltBody = "Hello {$user['user_name']},\n\nYou requested a password reset. Please visit this link to reset your password: $resetLink\nOr enter this code on the reset page: $token\n\nThis link expires in 24 hours.\n\nThank you!";

    $mail->send();

    // Insert reset token into the reset table
    $stmt = $conn->prepare("INSERT INTO password_reset (user_id, token, expiry_date, is_used) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), 0)");
    $stmt->execute([$user['user_id'], $token]);

    $conn->commit();
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Verification</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            body {
                background-color: rgb(30 31 32 / 1);
                color: rgb(227 227 227 / 1);
            }

            .main-container {
                width: 60%;
                padding: 16.4% 0;
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
        </style>
    </head>

    <body>
        <div class="body-container">
            <div class="container-fluid main-container">
                <div class="sub-container">
                    <div class="row align-items-center">
                        <div class="col-1">
                            <img src="../images/logo.webp" height="50px">
                        </div>
                        <div class="col-8">
                            <h3>Reset Password Email Sent!</h3>
                        </div>
                    </div>
                    <p class="my-3 ms-3">A password reset link has been sent to your email. Please click on the link to verify to reset password</p>
                </div>
                <div class="sub-container-2">
                    <p><a href="../login.html">Back to Login</a></p>
                </div>
            </div>
    </body>

    </html>
    <?php
} catch (Exception $e) {
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
} catch (PDOException $e) {
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>