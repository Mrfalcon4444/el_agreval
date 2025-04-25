<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?mensaje=ID de vacaciones inválido&tipo=error");
    exit();
}

$id_vacaciones = intval($_GET['id']);

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Verificar que las vacaciones existen y están activas
$check_sql = "SELECT id_vacaciones, id_empleado FROM VACACIONES WHERE id_vacaciones = ? AND estado = 1";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $id_vacaciones);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    $conn->close();
    header("Location: index.php?mensaje=Solicitud de vacaciones no encontrada o ya finalizada&tipo=error");
    exit();
}

$vacaciones = $check_result->fetch_assoc();
$check_stmt->close();

// Actualizar el estado de las vacaciones
$update_sql = "UPDATE VACACIONES SET estado = 0 WHERE id_vacaciones = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $id_vacaciones);

if ($update_stmt->execute()) {
    $mensaje = "Vacaciones finalizadas exitosamente";
    $tipo = "success";
} else {
    $mensaje = "Error al finalizar las vacaciones: " . $update_stmt->error;
    $tipo = "error";
}

$update_stmt->close();
$conn->close();

header("Location: index.php?mensaje=" . urlencode($mensaje) . "&tipo=" . $tipo);
exit();
?> 