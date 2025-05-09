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
$check_sql = "SELECT id_incapacidad, id_empleado FROM INCAPACIDADES WHERE id_incapacidad = ? AND estado = 1";
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