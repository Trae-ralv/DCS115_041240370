<?php
header('Content-Type: application/json');

// Include PHPMailer
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($toEmail, $toName, $subject, $htmlBody, $textBody) {
    $mail = new PHPMailer(true);

    try {
        // Enable SMTP debugging for troubleshooting
        $mail->SMTPDebug = 2; // 2 = verbose debug output
        $mail->Debugoutput = 'error_log'; // Output debug info to PHP error log

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'smtptestingwp@gmail.com'; // SMTP username
        $mail->Password = 'sypb kqkp ewlh wuhw'; // SMTP password (App Password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender and recipient
        $mail->setFrom('smtptestingwp@gmail.com', 'WOU Tech');
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;

        // Send email
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        error_log('PHPMailer Exception: ' . $e->getMessage()); // Log the exception
        return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
    }
}

// Handle request (e.g., via POST or function call)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $toEmail = $data['toEmail'] ?? '';
    $toName = $data['toName'] ?? '';
    $subject = $data['subject'] ?? '';
    $htmlBody = $data['htmlBody'] ?? '';
    $textBody = $data['textBody'] ?? '';

    if ($toEmail && $subject && $htmlBody && $textBody) {
        $result = sendEmail($toEmail, $toName, $subject, $htmlBody, $textBody);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing email parameters']);
    }
}
?>