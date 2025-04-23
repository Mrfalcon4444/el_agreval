<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function setupMailer(PHPMailer $mail) {
    // Configurar el servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';      // Servidor SMTP de Gmail
    $mail->SMTPAuth = true;
    $mail->Username = 'tu_correo@gmail.com';  // Tu dirección de Gmail
    $mail->Password = 'abcd efgh ijkl mnop';  // Tu contraseña de aplicación de Gmail
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Configurar el remitente
    $mail->setFrom('tu_correo@gmail.com', 'El Agreval');
    $mail->CharSet = 'UTF-8';
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    
    return "$protocol://$host$uri";
}
?>