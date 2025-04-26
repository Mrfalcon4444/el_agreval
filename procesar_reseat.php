<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar si el método de solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: recuperar_password.php");
    exit();
}

// Obtener y validar los datos del formulario
$token = isset($_POST['token']) ? $_POST['token'] : '';
$id_empleado = isset($_POST['id_empleado']) ? filter_var($_POST['id_empleado'], FILTER_SANITIZE_NUMBER_INT) : 0;
$nueva_contraseña = isset($_POST['nueva_contraseña']) ? $_POST['nueva_contraseña'] : '';
$confirmar_contraseña = isset($_POST['confirmar_contraseña']) ? $_POST['confirmar_contraseña'] : '';

// Validar que se hayan proporcionado todos los datos necesarios
if (empty($token) || empty($id_empleado) || empty($nueva_contraseña) || empty($confirmar_contraseña)) {
    header("Location: resetear_password.php?token=" . urlencode($token) . "&id=" . urlencode($id_empleado) . "&error=" . urlencode("Todos los campos son obligatorios."));
    exit();
}

// Validar que las contraseñas coincidan
if ($nueva_contraseña !== $confirmar_contraseña) {
    header("Location: resetear_password.php?token=" . urlencode($token) . "&id=" . urlencode($id_empleado) . "&error=" . urlencode("Las contraseñas no coinciden."));
    exit();
}

// Validar requisitos de la contraseña
if (strlen($nueva_contraseña) < 8 || !preg_match('/[A-Z]/', $nueva_contraseña) || !preg_match('/[a-z]/', $nueva_contraseña) || !preg_match('/\d/', $nueva_contraseña)) {
    header("Location: resetear_password.php?token=" . urlencode($token) . "&id=" . urlencode($id_empleado) . "&error=" . urlencode("La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una minúscula y un número."));
    exit();
}

// Conectar a la base de datos
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    header("Location: recuperar_password.php?mensaje=Error en el servidor. Intente más tarde.&tipo=error");
    exit();
}

$conn->set_charset("utf8");

// Verificar la validez del token
$sql = "SELECT token, fecha_expiracion FROM recuperacion_password WHERE id_empleado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si el token existe y no ha expirado
$token_valido = false;

if ($result->num_rows === 0) {
    $mensaje_error = "El enlace de recuperación es inválido o ha expirado.";
} else {
    $row = $result->fetch_assoc();
    $fecha_expiracion = new DateTime($row['fecha_expiracion']);
    $ahora = new DateTime();
    
    // Verificar si el token ha expirado
    if ($ahora > $fecha_expiracion) {
        $mensaje_error = "El enlace de recuperación ha expirado. Solicita uno nuevo.";
    } 
    // Verificar si el token coincide
    elseif (!password_verify($token, $row['token'])) {
        $mensaje_error = "El enlace de recuperación es inválido.";
    } else {
        $token_valido = true;
    }
}

$stmt->close();

// Si el token no es válido, redireccionar con error
if (!$token_valido) {
    $conn->close();
    header("Location: recuperar_password.php?mensaje=" . urlencode($mensaje_error) . "&tipo=error");
    exit();
}

// Actualizar la contraseña del empleado
$contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

$sql_update = "UPDATE EMPLEADOS SET contraseña = ? WHERE id_empleado = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("si", $contraseña_hash, $id_empleado);

if (!$stmt_update->execute()) {
    $stmt_update->close();
    $conn->close();
    header("Location: resetear_password.php?token=" . urlencode($token) . "&id=" . urlencode($id_empleado) . "&error=" . urlencode("Error al actualizar la contraseña. Intente nuevamente."));
    exit();
}

$stmt_update->close();

// Eliminar todos los tokens de recuperación para este empleado
$sql_delete = "DELETE FROM recuperacion_password WHERE id_empleado = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $id_empleado);
$stmt_delete->execute();
$stmt_delete->close();

$conn->close();

// Redireccionar al login con mensaje de éxito
header("Location: login.php?mensaje=Tu contraseña ha sido actualizada con éxito. Ya puedes iniciar sesión.&tipo=success");
exit();
