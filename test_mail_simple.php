<?php
// Para depuración, mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar las clases directamente
require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';

// Usar los namespaces correctos
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Función para configurar el mailer (simplificada)
function setupMailer(PHPMailer $mail) {
    // Configurar el servidor SMTP de Hostinger
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP de Hostinger
    $mail->SMTPAuth = true;
    $mail->Username = 'admin@elagreval.icu';  // Tu dirección de correo en Hostinger
    $mail->Password = 'AdminElAgreval123+';  // Tu contraseña de correo en Hostinger
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Configurar el remitente
    $mail->setFrom('admin@elagreval.icu', 'El Agreval');
    $mail->CharSet = 'UTF-8';
}

// Función para enviar un correo de prueba
function enviarCorreoPrueba($destinatario) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurar el servidor SMTP
        setupMailer($mail);
        
        // Habilitar depuración SMTP
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Muestra información detallada de la conexión SMTP
        
        // Destinatarios
        $mail->addAddress($destinatario);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Correo de prueba desde PHPMailer (Simple)';
        $mail->Body = '
        <html>
        <body>
            <h2>¡Prueba exitosa!</h2>
            <p>Este es un correo de prueba enviado desde PHPMailer (versión simple).</p>
            <p>Fecha y hora: ' . date('Y-m-d H:i:s') . '</p>
            <p>Entorno: ' . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'local') . '</p>
        </body>
        </html>';
        $mail->AltBody = 'Este es un correo de prueba enviado desde PHPMailer (versión simple).';
        
        // Enviar el correo
        $mail->send();
        return "El correo ha sido enviado correctamente.";
    } catch (Exception $e) {
        return "Error al enviar el correo: " . $mail->ErrorInfo;
    }
}

// Procesar el formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = enviarCorreoPrueba($email);
    } else {
        $mensaje = "Por favor, introduce una dirección de correo válida.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Simple de PHPMailer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        form {
            margin-top: 20px;
        }
        label, input, button {
            display: block;
            margin-bottom: 10px;
        }
        input, button {
            padding: 8px;
            width: 100%;
        }
        button {
            background-color: #4A6DA7;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prueba Simple de PHPMailer</h1>
        
        <?php if ($mensaje): ?>
            <div class="message <?php echo strpos($mensaje, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <label for="email">Correo electrónico de destino:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Enviar correo de prueba</button>
        </form>
        
        <div style="margin-top: 20px;">
            <h3>Notas importantes:</h3>
            <ol>
                <li>Este script carga PHPMailer directamente sin usar el autoloader de Composer.</li>
                <li>La configuración SMTP está integrada en este archivo para simplificar la prueba.</li>
                <li>El mismo código debería funcionar tanto en local como en Hostinger.</li>
                <li>Si sigue habiendo problemas, verifica las credenciales SMTP y la conectividad al servidor.</li>
            </ol>
        </div>
    </div>
</body>
</html> 