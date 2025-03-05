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

// Check if verification code or link is provided
if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $code = $_GET['code'];

    // Verify the code against the database
    $stmt = $conn->prepare("SELECT user_id, verification_code, is_verified FROM user_account WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] === $code && $user['is_verified'] == 0) {
        // Update user as verified
        $stmt = $conn->prepare("UPDATE user_account SET is_verified = 1, verification_code = NULL WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
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
                                <h3>Verification Success</h3>
                            </div>
                        </div>
                        <p class="my-3 ms-3">Your email has been verified successfully! You can now log in to your account! </p>
                    </div>
                    <div class="sub-container-2">
                        <p><a href="../login.html">Back to Login</a></p>
                    </div>
                </div>
        </body>

        </html>
        <?php
    } else {
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
                                <h3>Verification Failed</h3>
                            </div>
                        </div>
                        <p class="my-3 ms-3">Invalid or expired verification code. Please Register Again!</p>
                    </div>
                    <div class="sub-container-2">
                        <p><a href="../login.html">Back to Login</a></p>
                    </div>
                </div>
        </body>

        </html>
        <?php
    }
} else {
    // Form to enter verification code
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
        </style>
    </head>

    <body>
        <div class="body-container">
            <div class="container-fluid main-container">
                <div class="sub-container">
                    <img src="../images/logo.webp" height="50px">
                    <div class="row">
                        <div class="col-5">
                            <h3 class="my-3">Email Verification</h3>
                            <p>Enter your email and verification code to activate your account. This account will be
                                available
                                to Wou Tech website.</p>
                        </div>
                        <div class="col-1"></div>
                        <div class="col-6 my-3">
                            <form id="verificationForm">
                                <div class="form-group">
                                    <input class="form-control" type="email" id="email" name="email" placeholder="Email"
                                        required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" type="text" id="verificationCode" name="verificationCode"
                                        placeholder="Verification Code" required>
                                </div>
                                <div class="text-end">
                                    <button type="submit">Verify</button>
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
            $(document).ready(function () {
                $('#verificationForm').on('submit', function (e) {
                    e.preventDefault(); // Prevent default form submission

                    // Get input values
                    const email = encodeURIComponent($('#email').val());
                    const verificationCode = encodeURIComponent($('#verificationCode').val());

                    // Construct the URL
                    const url = `http://localhost/woutech/php/verify.php?email=${email}&code=${verificationCode}`;

                    // Navigate to the URL
                    window.location.href = url;
                });
            });
        </script>
    </body>

    </html>
    <?php
}

// Handle form submission for verification code
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $code = $_POST['verificationCode'];

    $stmt = $conn->prepare("SELECT user_id, verification_code, is_verified FROM user_account WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['verification_code'] === $code && $user['is_verified'] == 0) {
        $stmt = $conn->prepare("UPDATE user_account SET is_verified = 1, verification_code = NULL WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        echo "Your email has been verified successfully! You can now log in.";
    } else {
        echo "Invalid verification code or email already verified.";
    }
}

$conn = null;
?>