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
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Form data
$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
$phone = $_POST['phone'];
$userType = $_POST['userType'];
$companyName = ($_POST['userType'] == 2) ? $_POST['companyName'] : NULL;

// Check if email already exists
$stmt = $conn->prepare("SELECT email FROM user_account WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $conn->rollBack();
    die("Email already registered. Please use a different email.");
}

// Generate a unique verification code
$verificationCode = bin2hex(random_bytes(16)); // 32-character hex string

// Include PHPMailer files manually
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Send verification email using PHPMailer
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
    $verificationLink = "http://localhost/woutech/php/verify.php?email=" . urlencode($email) . "&code=" . urlencode($verificationCode);
    $mail->Subject = 'Verify Your Email Address';
    $mail->Body = "Hello $username,<br><br>Please verify your email by clicking this link: <a href='$verificationLink'>Verify Email</a><br>Or enter this code on the verification page: <strong>$verificationCode</strong><br><br>Thank you!";
    $mail->AltBody = "Hello $username,\n\nPlease verify your email by visiting this link: $verificationLink\nOr enter this code on the verification page: $verificationCode\n\nThank you!";

    $mail->send();

    // If email is sent successfully, commit the transaction
    $stmt = $conn->prepare("INSERT INTO user_account (user_name, phone_no, email, password, type_id, company_name, verification_code, is_verified, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
    $stmt->execute([$username, $phone, $email, $password, $userType, $companyName, $verificationCode]);

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
                            <h3>Registration Success</h3>
                        </div>
                    </div>
                    <p class="my-3 ms-3">Registration successful! A verification email has been sent to $email. Please
                        verify your account by clicking the link in the email or proceed to Verify Page and enter it
                        manually </p>
                </div>
                <div class="sub-container-2">
                    <p><a href="./verify.php">Procceed to Verify</a></p>
                </div>
            </div>
    </body>

    </html>
    <?php
} catch (Exception $e) {
    // If email fails, roll back the transaction
    $conn->rollBack();
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
                            <h3>Registration Failed</h3>
                        </div>
                    </div>
                    <p class="my-3 ms-3">Registration failed! Verification Email cant be sent!</p>
                </div>
                <div class="sub-container-2">
                    <p><a href="../register.html">Procceed to Register</a></p>
                </div>
            </div>
    </body>

    </html>
    <?php
} catch (PDOException $e) {
    // Handle any database errors and roll back
    $conn->rollBack();
    echo "Database error: " . $e->getMessage() . ". Registration failed.";
}

$conn = null;
?>