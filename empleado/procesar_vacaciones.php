<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_empleado = $_SESSION['id_empleado'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_finalizacion = $_POST['fecha_finalizacion'];
    $comentarios = isset($_POST['comentarios']) ? $_POST['comentarios'] : '';
    
    // Calcular días totales (diferencia entre fecha_finalizacion y fecha_inicio en días)
    $fecha_inicio_obj = new DateTime($fecha_inicio);
    $fecha_fin_obj = new DateTime($fecha_finalizacion);
    $interval = $fecha_inicio_obj->diff($fecha_fin_obj);
    $dias_totales = $interval->days + 1; // +1 para incluir el día final
    
    // Comprobar que la fecha de inicio no sea anterior a la fecha actual
    $fecha_actual = new DateTime();
    $fecha_actual->setTime(0, 0, 0); // Establecer la hora a 00:00:00 para comparar solo fechas
    if ($fecha_inicio_obj < $fecha_actual) {
        header("Location: solicitar_vacaciones.php?error=La fecha de inicio no puede ser anterior a hoy");
        exit();
    }
    
    // Comprobar que la fecha de finalización sea posterior a la fecha de inicio
    if ($fecha_fin_obj < $fecha_inicio_obj) {
        header("Location: solicitar_vacaciones.php?error=La fecha de finalización debe ser posterior a la fecha de inicio");
        exit();
    }
    
    // Insertar la solicitud de vacaciones en la base de datos
    // Primero modificamos la tabla VACACIONES para añadir los campos necesarios
    $sql_alter_table = "ALTER TABLE VACACIONES 
                        ADD COLUMN IF NOT EXISTS comentarios TEXT DEFAULT NULL,
                        ADD COLUMN IF NOT EXISTS estado_aprobacion ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
                        ADD COLUMN IF NOT EXISTS fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        ADD COLUMN IF NOT EXISTS comentario_rrhh TEXT DEFAULT NULL,
                        MODIFY COLUMN dias_totales INT DEFAULT NULL";
                        
    if (!$conn->query($sql_alter_table)) {
        error_log("Error al modificar la tabla VACACIONES: " . $conn->error);
        header("Location: solicitar_vacaciones.php?error=Error al procesar la solicitud. Contacte al administrador.");
        exit();
    }
    
    // Ahora insertamos la solicitud
    $sql = "INSERT INTO VACACIONES (id_empleado, fecha_inicio, fecha_finalizacion, estado, dias_totales, comentarios, estado_aprobacion) 
            VALUES (?, ?, ?, 1, ?, ?, 'pendiente')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $id_empleado, $fecha_inicio, $fecha_finalizacion, $dias_totales, $comentarios);

    if ($stmt->execute()) {
        header("Location: mis_vacaciones.php?mensaje=Solicitud de vacaciones enviada correctamente&tipo=success");
    } else {
        error_log("Error al insertar en la base de datos: " . $stmt->error);
        header("Location: solicitar_vacaciones.php?error=Error al registrar la solicitud: " . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?> 