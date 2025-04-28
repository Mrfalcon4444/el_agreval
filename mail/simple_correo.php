<?php
// Incluir el archivo de utilidades de correo
require_once __DIR__ . '/mailer.php';

// Para depuración, mostrar solo errores críticos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

$id_empleado = $_GET['id_empleado']; // O el ID que recibas
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT correo, nombre FROM EMPLEADOS WHERE id_empleado = ?");
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $empleado = $result->fetch_assoc();
    $destinatario = $empleado['correo']; // Correo del empleado
    $nombre_empleado = $empleado['nombre']; // Nombre del empleado
} else {
    die("No se encontró el empleado con ID: $id_empleado");
}

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