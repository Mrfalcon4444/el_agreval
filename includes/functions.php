<?php
/**
 * Funciones auxiliares para el sistema de recuperación de contraseña
 */

/**
 * Obtiene la URL base del sitio.
 * 
 * @return string La URL base del sitio.
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtener el directorio base
    $base_dir = dirname($_SERVER['SCRIPT_NAME']);
    
    // Si estamos en el directorio raíz, no añadir subdirectorios
    if ($base_dir == '/' || $base_dir == '\\') {
        $base_dir = '';
    }
    
    return $protocol . "://" . $host . $base_dir;
}

/**
 * Genera un token aleatorio seguro.
 * 
 * @param int $length Longitud del token en bytes (antes de la conversión a hex).
 * @return string Token hexadecimal.
 */
function generateSecureToken($length = 32) {
    try {
        // Intentar usar random_bytes() para generar un token seguro
        return bin2hex(random_bytes($length));
    } catch (Exception $e) {
        // Fallback usando openssl_random_pseudo_bytes
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

/**
 * Verifica si un token ha expirado.
 * 
 * @param string $expiration_date Fecha de expiración en formato Y-m-d H:i:s.
 * @return bool True si el token ha expirado, false en caso contrario.
 */
function isTokenExpired($expiration_date) {
    $expiration = new DateTime($expiration_date);
    $now = new DateTime();
    return $now > $expiration;
}

/**
 * Verifica la fortaleza de una contraseña.
 * 
 * @param string $password La contraseña a verificar.
 * @return array Un array con 'valid' => bool y 'message' => string.
 */
function validatePasswordStrength($password) {
    $result = [
        'valid' => true,
        'message' => ''
    ];
    
    // Verificar longitud mínima
    if (strlen($password) < 8) {
        $result['valid'] = false;
        $result['message'] = "La contraseña debe tener al menos 8 caracteres.";
        return $result;
    }
    
    // Verificar que contenga al menos una letra mayúscula
    if (!preg_match('/[A-Z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = "La contraseña debe contener al menos una letra mayúscula.";
        return $result;
    }
    
    // Verificar que contenga al menos una letra minúscula
    if (!preg_match('/[a-z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = "La contraseña debe contener al menos una letra minúscula.";
        return $result;
    }
    
    // Verificar que contenga al menos un número
    if (!preg_match('/\d/', $password)) {
        $result['valid'] = false;
        $result['message'] = "La contraseña debe contener al menos un número.";
        return $result;
    }
    
    return $result;
}

/**
 * Sanitiza y valida una dirección de correo electrónico.
 * 
 * @param string $email La dirección de correo electrónico.
 * @return array Un array con 'valid' => bool y 'email' => string sanitizado.
 */
function validateEmail($email) {
    $sanitized_email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    return [
        'valid' => filter_var($sanitized_email, FILTER_VALIDATE_EMAIL) !== false,
        'email' => $sanitized_email
    ];
}

/**
 * Registra actividad de recuperación de contraseña para prevenir abusos.
 * 
 * @param mysqli $conn Conexión a la base de datos.
 * @param string $ip Dirección IP del usuario.
 * @param string $action Acción realizada (solicitud, reset).
 * @param bool $success Si la acción fue exitosa o no.
 * @param int $id_empleado ID del empleado (opcional).
 * @return void
 */
function logPasswordRecoveryActivity($conn, $ip, $action, $success, $id_empleado = null) {
    $sql = "INSERT INTO actividad_recuperacion (ip, accion, exito, id_empleado, fecha) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    $exito_int = $success ? 1 : 0;
    
    if ($id_empleado === null) {
        $stmt->bind_param("ssi", $ip, $action, $exito_int);
    } else {
        $stmt->bind_param("ssii", $ip, $action, $exito_int, $id_empleado);
    }
    
    $stmt->execute();
    $stmt->close();
}

/**
 * Verifica si una IP ha realizado demasiados intentos de recuperación de contraseña.
 * 
 * @param mysqli $conn Conexión a la base de datos.
 * @param string $ip Dirección IP del usuario.
 * @param int $max_attempts Número máximo de intentos permitidos (por defecto 5).
 * @param int $time_window Ventana de tiempo en minutos (por defecto 30).
 * @return bool True si ha excedido el límite, false en caso contrario.
 */
function checkRateLimiting($conn, $ip, $max_attempts = 5, $time_window = 30) {
    $sql = "SELECT COUNT(*) as intentos FROM actividad_recuperacion 
            WHERE ip = ? AND fecha > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $ip, $time_window);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['intentos'] >= $max_attempts;
}
