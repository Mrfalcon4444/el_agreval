<?php
// Incluir el archivo de utilidades de correo
require_once __DIR__ . '/mailer.php';

// Para depuración, mostrar solo errores críticos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

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
    
    // Desactivar depuración SMTP
    $mail->SMTPDebug = 0; // Sin información de depuración
    
    // Destinatario
    $mail->addAddress($destinatario);
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body = $contenido;
    $mail->AltBody = strip_tags($contenido);
    
    // Enviar
    $mail->send();
    echo "<h2 style='color:green;text-align:center;'>Enviado</h2>";
} catch (Exception $e) {
    echo "<h2 style='color:red;text-align:center;'>Error</h2>";
}
?> 