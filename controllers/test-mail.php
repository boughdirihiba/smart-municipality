<?php
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'replay.smartmunicipality@gmail.com';
    $mail->Password   = 'bcyg pysc feih juwj';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('replay.smartmunicipality@gmail.com', 'Smart Municipality');
    $mail->addAddress('benothmeno80@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Test Smart Municipality';
    $mail->Body    = '<h1>Test</h1><p>If you see this, PHPMailer works!</p>';

    $mail->send();
    echo 'EMAIL SENT!';
} catch (Exception $e) {
    echo "ERROR: " . $mail->ErrorInfo;
}
?>