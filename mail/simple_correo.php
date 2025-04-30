<?php
// Incluir el archivo de utilidades de correo
require_once __DIR__ . '/mailer.php';

// Para depuración, mostrar solo errores críticos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// Verificar si se envió el ID del empleado
if (!isset($_GET['id_empleado']) || empty($_GET['id_empleado'])) {
    die("El ID del empleado no se ha proporcionado o está vacío.");
}

// Validar que el ID sea un número entero
$id_empleado = filter_var($_GET['id_empleado'], FILTER_VALIDATE_INT);
if (!$id_empleado) {
    die("El ID del empleado no es válido.");
}

// Cargar PHPMailer desde múltiples ubicaciones posibles
$phpmailerLoaded = false;
$pathsToCheck = [
    __DIR__.'/phpmailer/',  
    __DIR__.'/../phpmailer/src/',      
    __DIR__.'/../vendor/phpmailer/phpmailer/src/',
    __DIR__.'/../../vendor/phpmailer/phpmailer/src/' 
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
    $errorMsg = "Error: No se pudo cargar PHPMailer\n\nSe intentó cargar desde las siguientes rutas:\n\n";
    foreach ($pathsToCheck as $path) {
        $errorMsg .= $path . "\n";
    }
    $errorMsg .= "Por favor, instale PHPMailer en la carpeta mail/phpmailer/ o en vendor/phpmailer/phpmailer/src/";
    die($errorMsg);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conexión a la base de datos
require_once __DIR__ . '/../config/config.php';
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta SQL
$stmt = $conn->prepare("SELECT correo, nickname FROM EMPLEADOS WHERE id_empleado = ?");
if (!$stmt) {
    die("Error en la preparación de la consulta: " . $conn->error);
}
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $empleado = $result->fetch_assoc();
    $destinatario = $empleado['correo'];
    $nombre_empleado = $empleado['nickname'];
    echo "Empleado encontrado: " . $nombre_empleado . " (" . $destinatario . ")";
} else {
    die("No se encontró el empleado con ID: $id_empleado");
}

// Enviar el correo
$mail = new PHPMailer(true);

try {
    // Configuración SMTP
    require_once __DIR__ . '/smtp_config.php';
    $mail->isSMTP();
    $mail->Host = $smtp_config['host'];
    $mail->SMTPAuth = $smtp_config['auth'];
    $mail->Username = $smtp_config['username'];
    $mail->Password = $smtp_config['password'];
    $mail->SMTPSecure = $smtp_config['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtp_config['port'];
    $mail->CharSet = 'UTF-8';
    
    // Remitente
    $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
    
    // Desactivar depuración SMTP
    $mail->SMTPDebug = 0; // Sin información de depuración
    
    // Destinatario
    $mail->addAddress($destinatario, $nombre_empleado);
    
    // Contenido
    $mail->isHTML(true);
    $mail->Subject = $asunto ?? "Notificación de El Agreval";
    $mail->Body = $contenido ?? "Este es un mensaje de prueba desde El Agreval.";
    $mail->AltBody = strip_tags($contenido ?? "Este es un mensaje de prueba desde El Agreval.");
    
    // Enviar
    $mail->send();
    echo "<h2 style='color:green;text-align:center;'>Correo enviado correctamente</h2>";
} catch (Exception $e) {
    echo "<h2 style='color:red;text-align:center;'>Error al enviar el correo: {$mail->ErrorInfo}</h2>";
}
?>