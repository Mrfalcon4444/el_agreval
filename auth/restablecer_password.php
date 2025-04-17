<!-- filepath: c:\Users\juana\Desktop\semestre 7\Inge software\el_agreval\auth\restablecer_password.php -->
<?php
require_once '../config/config.php';

$token = $_GET['token'] ?? '';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("El enlace de recuperación es inválido o ha expirado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_contraseña = $_POST['nueva_contraseña'];
    $confirmar_contraseña = $_POST['confirmar_contraseña'];

    if ($nueva_contraseña === $confirmar_contraseña) {
        $row = $result->fetch_assoc();
        $correo = $row['email'];
        $contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE EMPLEADOS SET contraseña = ? WHERE correo = ?");
        $stmt->bind_param("ss", $contraseña_hash, $correo);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo "Contraseña restablecida exitosamente.";
    } else {
        echo "Las contraseñas no coinciden.";
    }
}
?>

<form method="POST">
    <label>Nueva Contraseña:</label>
    <input type="password" name="nueva_contraseña" required>
    <label>Confirmar Contraseña:</label>
    <input type="password" name="confirmar_contraseña" required>
    <button type="submit">Restablecer Contraseña</button>
</form>