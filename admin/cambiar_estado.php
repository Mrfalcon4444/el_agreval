<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['accion'])) {
    header("Location: dashboard.php?mensaje=Parámetros inválidos&tipo=error");
    exit();
}

$id_empleado = intval($_GET['id']);
$accion = $_GET['accion'];

if ($accion != 'alta' && $accion != 'baja') {
    header("Location: dashboard.php?mensaje=Acción inválida&tipo=error");
    exit();
}

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$stmt = $conn->prepare("SELECT id_empleado, nickname FROM EMPLEADOS WHERE id_empleado = ?");
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: dashboard.php?mensaje=Empleado no encontrado&tipo=error");
    exit();
}

$stmt->bind_result($id, $nickname);
$stmt->fetch();
$stmt->close();

$nuevo_estado = ($accion == 'alta') ? 1 : 0;
$accion_texto = ($accion == 'alta') ? 'activado' : 'dado de baja';

$update_stmt = $conn->prepare("UPDATE EMPLEADOS SET estado_activo = ? WHERE id_empleado = ?");
$update_stmt->bind_param("ii", $nuevo_estado, $id_empleado);

if ($update_stmt->execute()) {
    $mensaje = "El empleado " . htmlspecialchars($nickname) . " ha sido " . $accion_texto . " exitosamente.";
    $tipo = "success";
} else {
    $mensaje = "Error al cambiar el estado del empleado: " . $update_stmt->error;
    $tipo = "error";
}

$update_stmt->close();
$conn->close();

header("Location: dashboard.php?mensaje=" . urlencode($mensaje) . "&tipo=" . $tipo);
exit();
?>