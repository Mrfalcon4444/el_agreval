<?php
// Incluir el archivo de utilidades de correo
require_once __DIR__ . '/mailer.php';

// Para depuración, mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Destinatario - CAMBIA ESTO
$destinatario = 'tuemail@ejemplo.com';

// Mensaje simple
$asunto = 'Mensaje de prueba';
$contenido = '<h3>Prueba</h3><p>Este es un mensaje de prueba desde El Agreval.</p>';

// Enviar el correo
$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    // Configuración SMTP
    setupMailer($mail);
    
    // Habilitar depuración SMTP
    $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
    
    // Destinatario
    $mail->addAddress($destinatario);
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body = $contenido;
    $mail->AltBody = strip_tags($contenido);
    
    // Enviar
    $mail->send();
    echo "Correo enviado con éxito a $destinatario";
} catch (Exception $e) {
    echo "Error al enviar correo: " . $mail->ErrorInfo;
}
?> 