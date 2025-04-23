// Incluir PHPMailer desde Composer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Configura las opciones del mailer para enviar correos electrónicos.
 * 
 * @param PHPMailer $mail La instancia de PHPMailer a configurar.
 * @return void
 */
function setupMailer(PHPMailer $mail) {
    // Configurar el servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';  // Servidor SMTP de Hostinger
    $mail->SMTPAuth   = true;                  // Activar autenticación SMTP
    $mail->Username   = 'no-reply@elagreval.com'; // Correo configurado en Hostinger (¡Cambiar por tu correo real!)
    $mail->Password   = 'Tu_Contraseña_Segura'; // Contraseña del correo (¡Cambiar por tu contraseña real!)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Tipo de encriptación
    $mail->Port       = 587;                   // Puerto SMTP de Hostinger
    
    // Opciones de depuración (0: sin depuración, 1: errores, 2: mensajes)
    // Activar para diagnóstico y desactivar en producción
    // $mail->SMTPDebug = SMTP::DEBUG_OFF; 
    
    // Configuración del remitente
    $mail->setFrom('no-reply@elagreval.com', 'El Agreval - Sistema de Recuperación');
    $mail->CharSet = 'UTF-8';  // Codificación para soportar caracteres especiales
    
    // Responder a (opcional)
    $mail->addReplyTo('no-reply@elagreval.com', 'No Responder - El Agreval');
}

/**
 * Crea una conexión a la base de datos.
 * 
 * @param string $db_host Host de la base de datos.
 * @param string $db_user Usuario de la base de datos.
 * @param string $db_password Contraseña de la base de datos.
 * @param string $db_name Nombre de la base de datos.
 * @return mysqli|null Conexión a la base de datos o null en caso de error.
 */
function connectDatabase($db_host, $db_user, $db_password, $db_name) {
    try {
        $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
        
        if ($conn->connect_error) {
            error_log("Error de conexión a la base de datos: " . $conn->connect_error);
            return null;
        }
        
        $conn->set_charset("utf8");
        return $conn;
    } catch (Exception $e) {
        error_log("Excepción al conectar a la base de datos: " . $e->getMessage());
        return null;
    }
}
