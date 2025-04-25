<?php
ini_set('display_errors', 1); // Muestra errores en pantalla
ini_set('display_startup_errors', 1); // Muestra errores de inicio de PHP
error_reporting(E_ALL); // Reporta todos los tipos de errores y advertencias

// El resto de tu código va aquí...
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'mail/phpmailer/mailer.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si el método de solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: recuperar_password.php");
    exit();
}

// Obtener y sanitizar el correo electrónico
$correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

// Validar el formato del correo electrónico
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header("Location: recuperar_password.php?mensaje=Formato de correo electrónico inválido.&tipo=error");
    exit();
}

// Conectar a la base de datos
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    header("Location: recuperar_password.php?mensaje=Error en el servidor. Intente más tarde.&tipo=error");
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
    exit();
}

$conn->set_charset("utf8");

// Verificar si el correo existe en la base de datos
$sql = "SELECT id_empleado, nickname FROM EMPLEADOS WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    // No revelamos si el correo existe o no por seguridad
    header("Location: recuperar_password.php?mensaje=Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.&tipo=success");
    exit();
}

$empleado = $result->fetch_assoc();
$id_empleado = $empleado['id_empleado'];
$nickname = $empleado['nickname'];

// Generar un token seguro
$token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales
$hash_token = password_hash($token, PASSWORD_DEFAULT); // Almacenar el hash, no el token

// Fecha de expiración (24 horas desde ahora)
$expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Eliminar tokens antiguos para este empleado
$sql_delete = "DELETE FROM password_resets WHERE id_empleado = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $id_empleado);
$stmt_delete->execute();
$stmt_delete->close();

// Insertar el nuevo token en la base de datos
$sql_insert = "INSERT INTO password_resets (id_empleado, token_hash, expires_at) VALUES (?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iss", $id_empleado, $hash_token, $expiracion);

if (!$stmt_insert->execute()) {
    $stmt_insert->close();
    $conn->close();
    header("Location: recuperar_password.php?mensaje=Error en el servidor. Intente más tarde.&tipo=error");
    error_log("Error al insertar token: " . $stmt_insert->error);
    exit();
}

$stmt_insert->close();

// Configurar el correo electrónico
$mail = new PHPMailer(true);
try {
    // Configuración del servidor
    setupMailer($mail); // Función de configuración desde mail_config.php

    // Destinatarios
    $mail->addAddress($correo, $nickname);

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Recuperación de Contraseña - El Agreval';
    
    // URL de restablecimiento de contraseña
    $reset_url = getBaseUrl() . "/resetear_password.php?token=" . urlencode($token) . "&id=" . urlencode($id_empleado);
    
    // Cuerpo del mensaje
    $mail->Body = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4A6DA7; color: white; padding: 10px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .button { display: inline-block; background-color: #4A6DA7; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; margin: 20px 0; }
            .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Recuperación de Contraseña</h2>
            </div>
            <div class="content">
                <p>Hola ' . htmlspecialchars($nickname) . ',</p>
                <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en El Agreval.</p>
                <p>Si no solicitaste un restablecimiento de contraseña, puedes ignorar este correo electrónico.</p>
                <p>Para restablecer tu contraseña, haz clic en el siguiente botón:</p>
                <p style="text-align: center;">
                    <a href="' . $reset_url . '" class="button">Restablecer Contraseña</a>
                </p>
                <p>O copia y pega el siguiente enlace en tu navegador:</p>
                <p>' . $reset_url . '</p>
                <p>Este enlace expirará en 24 horas.</p>
            </div>
            <div class="footer">
                <p>Este es un correo electrónico automático, por favor no respondas a este mensaje.</p>
                <p>&copy; ' . date('Y') . ' El Agreval. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>';
    
    $mail->AltBody = "Hola " . $nickname . ",\n\n" .
                     "Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en El Agreval.\n\n" .
                     "Si no solicitaste un restablecimiento de contraseña, puedes ignorar este correo electrónico.\n\n" .
                     "Para restablecer tu contraseña, copia y pega el siguiente enlace en tu navegador:\n" .
                     $reset_url . "\n\n" .
                     "Este enlace expirará en 24 horas.\n\n" .
                     "El Agreval";

    $mail->send();
    $conn->close();
    
    // Redireccionar con un mensaje de éxito
    header("Location: recuperar_password.php?mensaje=Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.&tipo=success");
    exit();
    
} catch (Exception $e) {
    $conn->close();
    error_log("Error al enviar el correo: " . $mail->ErrorInfo);
    header("Location: recuperar_password.php?mensaje=Error al enviar el correo electrónico. Intente más tarde.&tipo=error");
    exit();
}
