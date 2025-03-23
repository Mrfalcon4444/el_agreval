<?php
// Iniciar sesión 
session_start();

require_once '../config/config.php';

// Verificar si se recibieron datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar los datos del formulario
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['contraseña']; // No sanitizamos la contraseña para mantener caracteres especiales
    
    // Verificar que no estén vacíos
    if (empty($correo) || empty($password)) {
        header("Location: ../login.php?error=campos_vacios");
        exit();
    }
    
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
    
    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("SELECT id_empleado, contraseña, cargo, nickname, estado_activo FROM EMPLEADOS WHERE correo = ?");
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->bind_result($id_empleado, $hash_contraseña, $cargo, $nickname, $estado_activo);
    
    // Verificar si se encontró un usuario con ese correo
    if ($stmt->fetch()) {
        // Verificar si la cuenta está activa
        if ($estado_activo != 1) {
            $stmt->close();
            $conn->close();
            header("Location: ../login.php?error=inactivo");
            exit();
        }
        
        if (password_verify($password, $hash_contraseña)) {
            // Contraseña correcta - Iniciar sesión
            $_SESSION['id_empleado'] = $id_empleado;
            $_SESSION['correo'] = $correo;
            $_SESSION['cargo'] = $cargo;
            $_SESSION['nickname'] = $nickname;
            $_SESSION['loggedin'] = true;
            
            // Manejo de "Recordarme"
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $token = bin2hex(random_bytes(16)); 
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); 
            }
            
            // Redirección según el cargo
            if ($cargo == 'Administrador') {
                header("Location: ../admin/dashboard.php");
            } elseif ($cargo == 'RRHH') { 
                header("Location: ../rrhh/dashboard.php"); // Redirige a RRHH
            } else {
                header("Location: ../dashboard.php"); // Para otros usuarios
            }
            exit();
        } else {
            header("Location: ../login.php?error=credenciales");
            exit();
        }
    } else {
        header("Location: ../login.php?error=credenciales");
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../login.php");
    exit();
}
?>
