<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../login.php");
    exit();
}

// Configuración para Hostinger
$id_empleado = $_SESSION['id_empleado'];
$upload_dir = __DIR__ . '/../../imagenes/perfil/'; // Ruta absoluta en el servidor
$web_path = '/imagenes/perfil/'; // Ruta accesible desde la web

// Verificar y crear directorio con permisos
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        error_log("Error al crear directorio: " . print_r(error_get_last(), true));
        die("No se pudo crear el directorio para fotos");
    }
}

// Verificar permisos de escritura
if (!is_writable($upload_dir)) {
    die("El directorio no tiene permisos de escritura. Contacta al administrador");
}

// Procesar la imagen solo si se envió correctamente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto'])) {
    // Validar tipo de archivo
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $detected_type = mime_content_type($_FILES['foto']['tmp_name']);
    
    if (!in_array($detected_type, $allowed_types)) {
        die("Solo se permiten imágenes JPG, PNG o GIF");
    }

    // Validar tamaño (2MB máximo)
    if ($_FILES['foto']['size'] > 2097152) {
        die("El archivo no debe exceder 2MB");
    }

    // Generar nombre único
    $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $filename = 'emp_' . $id_empleado . '_' . uniqid() . '.' . $extension;
    $target_file = $upload_dir . $filename;

    // Mover el archivo subido
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        // Actualizar base de datos
        try {
            $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
            $stmt = $conn->prepare("UPDATE EMPLEADOS SET foto_de_perfil = ? WHERE id_empleado = ?");
            $relative_path = $web_path . $filename;
            $stmt->bind_param("si", $relative_path, $id_empleado);
            
            if ($stmt->execute()) {
                // Actualizar sesión y redirigir
                $_SESSION['foto_de_perfil'] = $relative_path;
                header("Location: dashboard.php?success=1");
                exit();
            } else {
                unlink($target_file); // Eliminar archivo si falla la BD
                throw new Exception("Error al actualizar la base de datos");
            }
        } catch (Exception $e) {
            error_log("Error DB: " . $e->getMessage());
            die("Ocurrió un error al guardar. Intenta nuevamente");
        }
    } else {
        $error = error_get_last();
        error_log("Error al mover archivo: " . print_r($error, true));
        die("Error al subir la imagen. Código: " . $_FILES['foto']['error']);
    }
} else {
    die("No se recibió ninguna imagen válida");
}