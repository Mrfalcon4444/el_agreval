<?php
// Para depuración, mostrar solo errores críticos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

// Incluir el archivo de utilidades de correo
require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/smtp_config.php'; // Incluir configuración SMTP segura

// Carga flexible de PHPMailer (siguiendo el esquema de procesar_recuperacion.php)
$phpmailerLoaded = false;
$pathsToCheck = [
    __DIR__ . '/phpmailer/',  
    __DIR__ . '/../phpmailer/src/',      
    __DIR__ . '/../vendor/phpmailer/phpmailer/src/',
    dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/' 
];

foreach ($pathsToCheck as $path) {
    if (file_exists($path . 'PHPMailer.php')) {
        require_once $path . 'PHPMailer.php';
        require_once $path . 'SMTP.php';
        require_once $path . 'Exception.php';
        $phpmailerLoaded = true;
        break;
    }
}

if (!$phpmailerLoaded) {
    error_log("Error: PHPMailer no encontrado en: " . implode(", ", $pathsToCheck));
    echo "<h2>Error: No se pudo cargar PHPMailer</h2>";
    echo "<p>Se intentó cargar desde las siguientes rutas:</p><ul>";
    foreach ($pathsToCheck as $path) {
        echo "<li>$path</li>";
    }
    echo "</ul>";
    echo "<p>Por favor, instale PHPMailer en la carpeta mail/phpmailer/ o en vendor/phpmailer/phpmailer/src/</p>";
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Este archivo debería ser incluido desde otros archivos, no ejecutado directamente.
// Solo ejecutar el código de abajo si se accede directamente para pruebas.
if (basename($_SERVER['PHP_SELF']) == 'simple_correo.php') {
    // Verificar si se envió el ID del empleado
    if (!isset($_GET['id_empleado']) || empty($_GET['id_empleado'])) {
        die("El ID del empleado no se ha proporcionado o está vacío.");
    }

    // Validar que el ID sea un número entero
    $id_empleado = filter_var($_GET['id_empleado'], FILTER_VALIDATE_INT);
    if (!$id_empleado) {
        die("El ID del empleado no es válido.");
    }

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

    // Configurar asunto y contenido de prueba
    $asunto = "Correo de prueba";
    $contenido = "<h1>Este es un correo de prueba</h1><p>Hola, $nombre_empleado.</p>";

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

        // Destinatario y contenido
        $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
        $mail->addAddress($destinatario, $nombre_empleado);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $contenido;
        $mail->AltBody = strip_tags($contenido);
        
        // Enviar
        $mail->send();
        echo "<h2 style='color:green;text-align:center;'>Correo enviado correctamente</h2>";
    } catch (Exception $e) {
        echo "<h2 style='color:red;text-align:center;'>Error al enviar el correo: {$mail->ErrorInfo}</h2>";
    }
}

// Función para configurar PHPMailer con los datos SMTP seguros
function setupMailer($mail) {
    global $smtp_config;
    
    $mail->isSMTP();
    $mail->Host = $smtp_config['host'];
    $mail->SMTPAuth = $smtp_config['auth'];
    $mail->Username = $smtp_config['username'];
    $mail->Password = $smtp_config['password'];
    $mail->SMTPSecure = $smtp_config['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtp_config['port'];
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
}
?>