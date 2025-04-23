<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function setupMailer(PHPMailer $mail) {
    // Detectar si estamos en entorno local o en producción
    $is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) || 
                strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false || 
                strpos($_SERVER['SERVER_NAME'] ?? '', '.test') !== false;
    
    if ($is_local) {
        // Configuración para entorno local
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP de Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@elagreval.icu';  // Tu dirección de correo en Hostinger
        $mail->Password = 'AdminElAgreval123+';  // Tu contraseña de correo en Hostinger
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('admin@elagreval.icu', 'El Agreval (Local)');
    } else {
        // Configuración para entorno de producción (Hostinger)
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP de Hostinger
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@elagreval.icu';  // Tu dirección de correo en Hostinger
        $mail->Password = 'AdminElAgreval123+';  // Tu contraseña de correo en Hostinger
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('admin@elagreval.icu', 'El Agreval');
    }
    
    $mail->CharSet = 'UTF-8';
}

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    
    return "$protocol://$host$uri";
}
?>