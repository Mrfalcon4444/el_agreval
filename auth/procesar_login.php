<?php
// Iniciar sesión para poder usar variables de sesión
session_start();

// Incluir el archivo de configuración de la base de datos
require_once 'config.php';

// Verificar si se recibieron datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y limpiar los datos del formulario
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['contraseña']; // No sanitizamos la contraseña para mantener caracteres especiales
    
    // Verificar que no estén vacíos
    if (empty($correo) || empty($password)) {
        header("Location: login.php?error=campos_vacios");
        exit();
    }
    
    // Crear conexión a la base de datos
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
    
    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Preparar la consulta SQL (usamos prepared statements para evitar inyección SQL)
    $stmt = $conn->prepare("SELECT id_empleado, contraseña, cargo, nickname, estado_activo FROM EMPLEADOS WHERE correo = ?");
    
    // Vincular parámetros
    $stmt->bind_param("s", $correo);
    
    // Ejecutar la consulta
    $stmt->execute();
    
    // Vincular resultados
    $stmt->bind_result($id_empleado, $hash_contraseña, $cargo, $nickname, $estado_activo);
    
    // Verificar si se encontró un usuario con ese correo
    if ($stmt->fetch()) {
        // Verificar si la cuenta está activa
        if ($estado_activo != 1) {
            $stmt->close();
            $conn->close();
            header("Location: login.php?error=inactivo");
            exit();
        }
        
        // Verificar la contraseña
        // Nota: Asumiendo que la contraseña está almacenada con password_hash()
        // Si está en texto plano o con otro método, ajusta esta parte
        if (password_verify($password, $hash_contraseña)) {
            // Contraseña correcta - Iniciar sesión
            $_SESSION['id_empleado'] = $id_empleado;
            $_SESSION['correo'] = $correo;
            $_SESSION['cargo'] = $cargo;
            $_SESSION['nickname'] = $nickname;
            $_SESSION['loggedin'] = true;
            
            // Si el usuario marcó "Recordarme", establecer una cookie
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                $token = bin2hex(random_bytes(16)); // Generar token aleatorio
                
                // Guardar token en la base de datos (requeriría una tabla adicional de tokens)
                // Por ahora, simplemente establecemos la cookie
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 días
            }
            
            // Redireccionar al dashboard o página principal según el cargo
            if ($cargo == 'Administrador') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: login.php?error=credenciales");
            exit();
        }
    } else {
        // No se encontró usuario con ese correo
        header("Location: login.php?error=credenciales");
        exit();
    }
    
    // Cerrar conexiones
    $stmt->close();
    $conn->close();
} else {
    // Si no se envió por POST, redirigir al formulario de login
    header("Location: login.php");
    exit();
}