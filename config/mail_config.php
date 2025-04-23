<?php
// Archivo: config/mail_config.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Configura las opciones del mailer para enviar correos electrónicos.
 *
 * @param PHPMailer $mail La instancia de PHPMailer a configurar.
 * @return void
 */
function setupMailer(PHPMailer $mail) {
    // Configurar el servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com'; 
    $mail->SMTPAuth = true;
    $mail->Username = 'tu_correo@gamil.com'; 
    $mail->Password = 'tu_contraseña'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Configurar el remitente
    $mail->setFrom('tu_correo@gnmail.com', 'El Agreval'); // Mismo correo que arriba
    $mail->CharSet = 'UTF-8';
}

/**
 * Obtiene la URL base del sitio.
 *
 * @return string La URL base del sitio.
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    
    return "$protocol://$host$uri";
}
?>