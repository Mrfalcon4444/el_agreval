<?php
// Para depuración, mostrar solo errores críticos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR | E_WARNING); // Incluir warnings para detectar problemas de configuración

// Verificar si se envió el ID del empleado
if (!isset($_GET['id_empleado']) || empty($_GET['id_empleado'])) {
    die("El ID del empleado no se ha proporcionado o está vacío.");
}

// Validar que el ID sea un número entero
$id_empleado = filter_var($_GET['id_empleado'], FILTER_VALIDATE_INT);
if (!$id_empleado) {
    die("El ID del empleado no es válido.");
}

// Requerir archivos de configuración
require_once dirname(__DIR__) . '/config/config.php';
require_once __DIR__ . '/smtp_config.php'; // Esta ruta es correcta ya que smtp_config.php está en la carpeta mail/

// Cargar PHPMailer desde la ubicación conocida
$phpmailerPath = dirname(__DIR__) . '/mail/phpmailer/';

if (file_exists($phpmailerPath.'PHPMailer.php')) {
    require_once $phpmailerPath.'PHPMailer.php';
    require_once $phpmailerPath.'SMTP.php';
    require_once $phpmailerPath.'Exception.php';
    $phpmailerLoaded = true;
} else {
    die("Error: PHPMailer no se encuentra en {$phpmailerPath}. Por favor, verifique la instalación.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conexión a la base de datos
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
    
    // Activar modo debug para diagnóstico
    $mail->SMTPDebug = 2; // Mostrar información detallada
    
    // Destinatario
    $mail->addAddress($destinatario, $nombre_empleado);
    
    // Contenido
    $mail->isHTML(true);
    // Verificar si $asunto y $contenido están definidos
    if (!isset($asunto) || empty($asunto)) {
        $asunto = "Notificación de El Agreval";
    }
    if (!isset($contenido) || empty($contenido)) {
        $contenido = "<p>Este es un mensaje de prueba desde El Agreval.</p>";
    }
    
    $mail->Subject = $asunto;
    $mail->Body = $contenido;
    $mail->AltBody = strip_tags($contenido);
    
    // Enviar
    $mail->send();
    echo "<h2 style='color:green;text-align:center;'>Correo enviado correctamente</h2>";
} catch (Exception $e) {
    echo "<h2 style='color:red;text-align:center;'>Error al enviar el correo: {$mail->ErrorInfo}</h2>";
    echo "<pre>Detalles del error: " . print_r($e, true) . "</pre>";
}
?>