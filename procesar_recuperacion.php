<?php
// Configuración extendida de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Registrar todos los errores en un archivo log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

require_once 'config/config.php';
require_once 'includes/functions.php';

// Carga flexible de PHPMailer (versión mejorada)
$phpmailerLoaded = false;
$pathsToCheck = [
    __DIR__.'/mail/phpmailer/src/',  // Estructura estándar recomendada
    __DIR__.'/phpmailer/src/',       // Ubicación alternativa
    __DIR__.'/vendor/phpmailer/phpmailer/src/' // Si usas Composer
];

foreach ($pathsToCheck as $path) {
    if (file_exists($path.'PHPMailer.php')) {
        require_once $path.'PHPMailer.php';
        require_once $path.'SMTP.php';
        require_once $path.'Exception.php';
        $phpmailerLoaded = true;
        break;
    }
}

if (!$phpmailerLoaded) {
    error_log("Error: PHPMailer no encontrado en: ".implode(", ", $pathsToCheck));
    header("Location: recuperar_password.php?mensaje=Error en el servidor. Intente más tarde.&tipo=error");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: recuperar_password.php");
    exit();
}

// Sanitización y validación mejorada
$correo = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header("Location: recuperar_password.php?mensaje=Formato de correo electrónico inválido.&tipo=error");
    exit();
}

// Conexión a BD con manejo de errores mejorado
try {
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: ".$conn->connect_error);
    }
    $conn->set_charset("utf8mb4"); // Mejor que utf8 para soporte completo

    // Verificar empleado
    $stmt = $conn->prepare("SELECT id_empleado, nickname FROM EMPLEADOS WHERE correo = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar consulta: ".$conn->error);
    }
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Mensaje genérico por seguridad
        header("Location: recuperar_password.php?mensaje=Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.&tipo=success");
        exit();
    }

    $empleado = $result->fetch_assoc();
    $id_empleado = $empleado['id_empleado'];
    $nickname = htmlspecialchars($empleado['nickname']);

    // Generar token seguro
    $token = bin2hex(random_bytes(32));
    $hash_token = password_hash($token, PASSWORD_DEFAULT);
    $expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Eliminar tokens antiguos
    $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE id_empleado = ?");
    $stmt_delete->bind_param("i", $id_empleado);
    $stmt_delete->execute();

    // Insertar nuevo token
    $stmt_insert = $conn->prepare("INSERT INTO password_resets (id_empleado, token_hash, expires_at) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iss", $id_empleado, $hash_token, $expiracion);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Error al insertar token: ".$stmt_insert->error);
    }

    // Configurar y enviar correo
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP (debería estar en tu config/mail_config.php)
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'admin@elagreval.icu';
        $mail->Password = 'tu_contraseña_smtp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Destinatario y contenido
        $mail->setFrom('admin@elagreval.icu', 'El Agreval');
        $mail->addAddress($correo, $nickname);
        
        // URL segura para resetear contraseña
        $reset_url = getBaseUrl()."/resetear_password.php?token=".urlencode($token)."&id=".$id_empleado;
        
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - El Agreval';
        
        // Plantilla HTML mejorada
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #2c3e50;'>Hola $nickname,</h2>
            <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
            <p>Por favor haz clic en el siguiente enlace:</p>
            <p><a href='$reset_url' style='background-color: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a></p>
            <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
            <p><small>Este enlace expirará en 24 horas.</small></p>
        </body>
        </html>";
        
        $mail->AltBody = "Hola $nickname,\n\nPara restablecer tu contraseña, visita:\n$reset_url\n\nEste enlace expira en 24 horas.";

        $mail->send();
        
        header("Location: recuperar_password.php?mensaje=Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.&tipo=success");
        
    } catch (Exception $e) {
        error_log("Error al enviar correo: ".$mail->ErrorInfo);
        header("Location: recuperar_password.php?mensaje=Error al enviar el correo. Intente más tarde.&tipo=error");
    }

} catch (Exception $e) {
    error_log("Error en recuperación: ".$e->getMessage());
    header("Location: recuperar_password.php?mensaje=Error en el servidor. Intente más tarde.&tipo=error");
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>