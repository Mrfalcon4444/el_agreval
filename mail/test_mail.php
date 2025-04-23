<?php
// Para depuración, mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de utilidades de correo
require_once __DIR__ . '/mailer.php';

// Función para enviar un correo de prueba
function enviarCorreoPrueba($destinatario) {
    // Construir el cuerpo del mensaje HTML
    $htmlBody = '
    <html>
    <body>
        <h2>¡Prueba exitosa!</h2>
        <p>Este es un correo de prueba enviado desde PHPMailer.</p>
        <p>Fecha y hora: ' . date('Y-m-d H:i:s') . '</p>
        <p>Entorno: ' . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'local') . '</p>
    </body>
    </html>';
    
    // Texto plano alternativo
    $textBody = "¡Prueba exitosa!\n\n" .
                "Este es un correo de prueba enviado desde PHPMailer.\n" .
                "Fecha y hora: " . date('Y-m-d H:i:s') . "\n" .
                "Entorno: " . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'local');
    
    // Enviar el correo usando la función de utilidad
    $resultado = sendMail(
        $destinatario,
        'Correo de prueba desde PHPMailer',
        $htmlBody,
        $textBody,
        ['debug' => true] // Habilitar depuración para ver detalles SMTP
    );
    
    return $resultado['message'];
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
    <title>Prueba de PHPMailer</title>
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
        <h1>Prueba de PHPMailer</h1>
        
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
                <li>Este script usa las funciones en el archivo mailer.php</li>
                <li>La misma configuración funciona tanto en local como en Hostinger</li>
                <li>Se detecta automáticamente el entorno y se ajusta el remitente</li>
                <li>Si tienes problemas, verifica las credenciales SMTP y la conectividad al servidor</li>
            </ol>
        </div>
    </div>
</body>
</html> 