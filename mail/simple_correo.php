<?php
// Incluir el archivo de utilidades de correo
require_once __DIR__ . '/mailer.php';

// Para depuración, mostrar solo errores críticos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);

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
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Configuración SMTP
        setupMailer($mail);
        
        // Desactivar depuración SMTP
        $mail->SMTPDebug = 0; // Sin información de depuración
        
        // Destinatario
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
?>