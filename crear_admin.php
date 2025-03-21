<?php
/*
 * Este script crea un usuario administrador inicial
 * Ejecutarlo UNA SOLA VEZ y luego eliminar del servidor
 */

// Incluir archivo de configuración
require_once 'config.php';

// Crear conexión
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8");

// Datos del administrador
$cargo = "Administrador";
$correo = "admin@elagreval.com";
$contraseña = password_hash("Admin123", PASSWORD_DEFAULT); // Contraseña hasheada
$nickname = "Admin";
$estado_activo = 1;
$fecha_ingreso = date("Y-m-d"); // Fecha actual

// También necesitamos un departamento para el administrador (debido a la restricción de llave foránea)
// Primero, verificar si ya existe algún departamento
$dept_check = $conn->query("SELECT id_departamento FROM DEPARTAMENTO LIMIT 1");

if ($dept_check->num_rows == 0) {
    // No hay departamentos, necesitamos crear uno
    $conn->query("INSERT INTO DEPARTAMENTO (tipo, nombre_departamento) VALUES ('Administración', 'Administración General')");
    $id_departamento = $conn->insert_id;
    echo "Departamento de Administración creado con ID: $id_departamento<br>";
} else {
    // Usar el primer departamento existente
    $dept_row = $dept_check->fetch_assoc();
    $id_departamento = $dept_row['id_departamento'];
    echo "Usando departamento existente con ID: $id_departamento<br>";
}

// Verificar si ya existe un usuario con este correo
$check_user = $conn->prepare("SELECT id_empleado FROM EMPLEADOS WHERE correo = ?");
$check_user->bind_param("s", $correo);
$check_user->execute();
$check_user->store_result();

if ($check_user->num_rows > 0) {
    echo "<p>ERROR: Ya existe un usuario con el correo $correo</p>";
    $check_user->close();
    $conn->close();
    exit();
}
$check_user->close();

// Preparar consulta para insertar el administrador
$stmt = $conn->prepare("INSERT INTO EMPLEADOS 
                       (cargo, fecha_ingreso_escuela, estado_activo, id_departamento, correo, contraseña, nickname) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssiisss", $cargo, $fecha_ingreso, $estado_activo, $id_departamento, $correo, $contraseña, $nickname);

// Ejecutar consulta
if ($stmt->execute()) {
    $id_empleado = $conn->insert_id;
    echo "<h2>¡Usuario administrador creado exitosamente!</h2>";
    echo "<p><strong>ID:</strong> $id_empleado</p>";
    echo "<p><strong>Correo:</strong> $correo</p>";
    echo "<p><strong>Contraseña:</strong> Admin123</p>";
    echo "<p><strong>Cargo:</strong> $cargo</p>";
} else {
    echo "<h2>Error al crear el usuario administrador</h2>";
    echo "<p>" . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();

echo "<p><strong>IMPORTANTE: Cambia la contraseña después de iniciar sesión por primera vez.</strong></p>";
echo "<p><strong>IMPORTANTE: Elimina este archivo del servidor por seguridad.</strong></p>";