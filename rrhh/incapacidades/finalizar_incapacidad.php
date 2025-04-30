<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_incapacidades.php?mensaje=ID de incapacidad inválido&tipo=error");
    exit();
}

$id_incapacidad = intval($_GET['id']);

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Verificar que la incapacidad existe y está activa
$check_sql = "SELECT i.id_incapacidad, i.id_empleado, e.nickname, e.correo 
              FROM INCAPACIDADES i 
              JOIN EMPLEADOS e ON i.id_empleado = e.id_empleado 
              WHERE i.id_incapacidad = ? AND i.estado = 1";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $id_incapacidad);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    header("Location: gestion_incapacidades.php?mensaje=Incapacidad no encontrada o ya finalizada&tipo=error");
    exit();
}

$incapacidad = $check_result->fetch_assoc();
$check_stmt->close();

// Actualizar el estado de la incapacidad
$update_sql = "UPDATE INCAPACIDADES SET estado = 0 WHERE id_incapacidad = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $id_incapacidad);

if ($update_stmt->execute()) {
    // Enviar correo al empleado
    require_once '../../mail/simple_correo.php'; // Archivo con la configuración de PHPMailer

    // Datos del empleado
    $destinatario = $incapacidad['correo'];
    $nombre_empleado = $incapacidad['nickname'];

    // Asunto y contenido del correo
    $asunto = 'Finalización de tu periodo de incapacidad';
    $contenido = "
    <h3>Hola, $nombre_empleado</h3>
    <p>Te informamos que tu periodo de <strong>incapacidad ha sido finalizado</strong> por Recursos Humanos.</p>
    <p>Si tienes alguna duda, no dudes en contactarnos.</p>
    <p>Saludos,<br>El equipo de Recursos Humanos</p>
    ";

    // Enviar el correo
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP usando la función en simple_correo.php
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
    } catch (Exception $e) {
        // Manejar errores de envío de correo
        error_log("Error al enviar el correo: {$mail->ErrorInfo}");
    }
    
    $mensaje = "Incapacidad finalizada exitosamente";
    $tipo = "success";
} else {
    $mensaje = "Error al finalizar la incapacidad: " . $update_stmt->error;
    $tipo = "error";
}

$update_stmt->close();
$conn->close();

header("Location: gestion_incapacidades.php?mensaje=" . urlencode($mensaje) . "&tipo=" . $tipo);
exit();
?> 