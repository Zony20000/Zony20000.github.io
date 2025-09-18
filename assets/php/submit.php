<?php
// submit.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request";
    exit;
}

// Simple validation + sanitization
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$email   = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($name === '' || $email === '' || $subject === '' || $comment === '') {
    echo "Por favor completa todos los campos.";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Email inválido.";
    exit;
}

// Sanea para el HTML (no para el correo real, solo para mostrar/cuerpo)
$esc_name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$esc_email   = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$esc_subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$esc_comment = nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'));

$html = "
<table>
<tr><td><strong>Nombre:</strong></td><td>{$esc_name}</td></tr>
<tr><td><strong>Email:</strong></td><td>{$esc_email}</td></tr>
<tr><td><strong>Asunto:</strong></td><td>{$esc_subject}</td></tr>
<tr><td><strong>Mensaje:</strong></td><td>{$esc_comment}</td></tr>
</table>
";

// ---------- CONFIG -----------
// Mejor: carga estas desde variables de entorno o config fuera del webroot
$smtpUser = getenv('SMTP_USER') ?: 'davidstivenpinuela@gmail.com';
$smtpPass = getenv('SMTP_PASS') ?: 'rufe fung knit pfce';
$recipientEmail = getenv('RECIPIENT_EMAIL') ?: 'your_email@gmail.com';
$recipientName  = getenv('RECIPIENT_NAME') ?: 'Tu Nombre';
// -----------------------------

require 'smtp/PHPMailerAutoload.php'; // o vendor/autoload.php si usas composer

$mail = new PHPMailer(true);

try {
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // Security: evita desactivar verificación en producción
    // $mail->SMTPOptions = ['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false,'allow_self_signed'=>true]];

    // From debe ser la cuenta autenticada
    $mail->setFrom($smtpUser, 'Contacto - Mi Sitio'); 
    // Responder al usuario
    $mail->addReplyTo($email, $name);
    // Destinatario que recibirá el formulario
    $mail->addAddress($recipientEmail, $recipientName);

    $mail->isHTML(true);
    $mail->Subject = '[Web] ' . $subject;
    $mail->Body    = $html;
    $mail->AltBody = "Nombre: $name\nEmail: $email\nAsunto: $subject\nMensaje: $comment";

    if ($mail->send()) {
        echo "Message Sent";
    } else {
        // En teoría PHPMailer lanza excepción en try/catch si hay problema, pero por si acaso:
        echo "Error Occur: " . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    // Dev: muestra el error en ambiente local; en producción guarda en log y muestra mensaje genérico.
    error_log("Mail error: " . $mail->getMessage());
    echo "No se pudo enviar el mensaje. Error: " . $mail->ErrorInfo;
}
?>
