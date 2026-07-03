<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Sanitize inputs
$first   = htmlspecialchars(trim($_POST['first']   ?? ''), ENT_QUOTES, 'UTF-8');
$last    = htmlspecialchars(trim($_POST['last']    ?? ''), ENT_QUOTES, 'UTF-8');
$email   = filter_var(trim($_POST['email']  ?? ''), FILTER_VALIDATE_EMAIL);
$phone   = htmlspecialchars(trim($_POST['phone']   ?? ''), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');
$intent  = htmlspecialchars(trim($_POST['intent']  ?? 'inquiry'), ENT_QUOTES, 'UTF-8');

// Validate required fields
if (!$first || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
    exit;
}

// Map intent to readable label
$intent_labels = [
    'offer'    => 'Make an Offer',
    'question' => 'Ask a Question',
    'bundle'   => 'Bundle Inquiry',
];
$intent_label = $intent_labels[$intent] ?? 'General Inquiry';

// Use PHPMailer (already configured on Pixelfast server)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust this path to match your server's PHPMailer location
require '/home/pixelfast/vendor/phpmailer/phpmailer/src/Exception.php';
require '/home/pixelfast/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '/home/pixelfast/vendor/phpmailer/phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Use your server's existing SMTP config — adjust host/credentials as needed
    $mail->isSMTP();
    $mail->Host       = 'localhost'; // or your SMTP host
    $mail->SMTPAuth   = false;
    $mail->Port       = 25;

    $mail->setFrom('noreply@selling.miami', 'selling.miami Inquiry');
    $mail->addAddress('michael@pixelfast.com', 'Michael');
    $mail->addReplyTo($email, "$first $last");

    $mail->Subject = "[$intent_label] selling.miami — $first $last";
    $mail->isHTML(false);
    $mail->Body = "New inquiry from selling.miami\n"
        . "================================\n"
        . "Type:    $intent_label\n"
        . "Name:    $first $last\n"
        . "Email:   $email\n"
        . "Phone:   " . ($phone ?: '—') . "\n"
        . "--------------------------------\n"
        . "Message:\n$message\n";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Inquiry sent.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Mailer error: ' . $mail->ErrorInfo]);
}
