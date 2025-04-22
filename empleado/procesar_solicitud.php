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
    $tipo = $_POST['tipo'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_finalizacion = $_POST['fecha_finalizacion'];
    $descripcion = $_POST['descripcion'];

    // Procesar el archivo subido
    $documento_path = '';
    if (isset($_FILES['documento_medico']) && $_FILES['documento_medico']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['documento_medico']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Crear directorio si no existe
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/el_Agreval/Untitled/uploads/incapacidades/';
            
            // Asegurarse de que el directorio existe
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    header("Location: solicitar_incapacidad.php?error=No se pudo crear el directorio de subida");
                    exit();
                }
            }

            // Verificar permisos del directorio
            if (!is_writable($upload_dir)) {
                chmod($upload_dir, 0777);
            }

            // Generar nombre único para el archivo
            $new_filename = uniqid() . '_' . $id_empleado . '.' . $ext;
            $full_path = $upload_dir . $new_filename;
            $relative_path = 'uploads/incapacidades/' . $new_filename;

            // Mover el archivo
            if (move_uploaded_file($_FILES['documento_medico']['tmp_name'], $full_path)) {
                $documento_path = $relative_path;
            } else {
                $error = error_get_last();
                header("Location: solicitar_incapacidad.php?error=Error al subir el archivo: " . $error['message']);
                exit();
            }
        } else {
            header("Location: solicitar_incapacidad.php?error=Formato de archivo no permitido");
            exit();
        }
    }

    // Insertar la incapacidad en la base de datos
    $sql = "INSERT INTO INCAPACIDADES (id_empleado, fecha_inicio, fecha_finalizacion, tipo, documento_justificativo, estado, estado_aprobacion) 
            VALUES (?, ?, ?, ?, ?, 1, 'pendiente')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $id_empleado, $fecha_inicio, $fecha_finalizacion, $tipo, $documento_path);

    if ($stmt->execute()) {
        header("Location: mis_incapacidades.php?mensaje=Incapacidad solicitada correctamente");
    } else {
        header("Location: solicitar_incapacidad.php?error=Error al registrar la incapacidad: " . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?> 