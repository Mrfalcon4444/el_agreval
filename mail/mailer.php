<?php
// Intentar cargar los archivos de PHPMailer desde diferentes ubicaciones posibles
$phpmailer_paths = [
    // Ruta relativa simple desde la carpeta mail
    __DIR__ . '/phpmailer/',
    // Ruta relativa desde la raíz del proyecto
    __DIR__ . '/../vendor/phpmailer/phpmailer/src/',
    // Ruta relativa alternativa (por si vendor está en otro nivel)
    __DIR__ . '/../../vendor/phpmailer/phpmailer/src/'
];

$phpmailer_loaded = false;
foreach ($phpmailer_paths as $base_path) {
    if (file_exists($base_path . 'Exception.php')) {
        require_once $base_path . 'Exception.php';
        require_once $base_path . 'PHPMailer.php';
        require_once $base_path . 'SMTP.php';
        $phpmailer_loaded = true;
        break;
    }
}

if (!$phpmailer_loaded) {
    // Si no se puede cargar PHPMailer, mostramos información de depuración
    echo "<h2>Error: No se pudo cargar PHPMailer</h2>";
    echo "<p>Se intentó cargar desde las siguientes rutas:</p><ul>";
    foreach ($phpmailer_paths as $path) {
        echo "<li>$path</li>";
    }
    echo "</ul>";
    echo "<p>Por favor, instale PHPMailer en la carpeta mail/phpmailer/ o en vendor/phpmailer/phpmailer/src/</p>";
    die();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Configura los parámetros básicos del mailer
 * 
 * @param PHPMailer $mail Instancia de PHPMailer a configurar
 * @return void
 */
function setupMailer(PHPMailer $mail) {
    // Detectar si estamos en entorno local o en producción
    $is_local = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) || 
                strpos($_SERVER['SERVER_NAME'] ?? '', '.local') !== false || 
                strpos($_SERVER['SERVER_NAME'] ?? '', '.test') !== false;
    
    // Configuración SMTP común
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';  // Servidor SMTP de Hostinger
    $mail->SMTPAuth = true;
    $mail->Username = 'admin@elagreval.icu';  // Tu dirección de correo en Hostinger
    $mail->Password = 'AdminElAgreval123+';  // Tu contraseña de correo en Hostinger
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    // Diferenciar el remitente según el entorno
    if ($is_local) {
        $mail->setFrom('admin@elagreval.icu', 'El Agreval (Local)');
    } else {
        $mail->setFrom('admin@elagreval.icu', 'El Agreval');
    }
    
    $mail->CharSet = 'UTF-8';
}

/**
 * Envía un correo electrónico
 * 
 * @param string $to Dirección de correo del destinatario
 * @param string $subject Asunto del correo
 * @param string $htmlBody Cuerpo del correo en formato HTML
 * @param string $textBody Cuerpo del correo en formato texto plano
 * @param array $options Opciones adicionales (cc, bcc, adjuntos, etc.)
 * @return array Resultado del envío con status y mensaje
 */
function sendMail($to, $subject, $htmlBody, $textBody = '', $options = []) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurar el servidor SMTP
        setupMailer($mail);
        
        // Debug (solo para desarrollo)
        if (isset($options['debug']) && $options['debug']) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        
        // Destinatario principal
        $mail->addAddress($to);
        
        // Copia carbón (CC)
        if (isset($options['cc']) && is_array($options['cc'])) {
            foreach ($options['cc'] as $cc) {
                $mail->addCC($cc);
            }
        }
        
        // Copia carbón oculta (BCC)
        if (isset($options['bcc']) && is_array($options['bcc'])) {
            foreach ($options['bcc'] as $bcc) {
                $mail->addBCC($bcc);
            }
        }
        
        // Adjuntos
        if (isset($options['attachments']) && is_array($options['attachments'])) {
            foreach ($options['attachments'] as $attachment) {
                if (is_array($attachment) && isset($attachment['path'])) {
                    $mail->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path']),
                        $attachment['encoding'] ?? 'base64',
                        $attachment['type'] ?? ''
                    );
                } else if (is_string($attachment) && file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));
        
        // Enviar el correo
        $mail->send();
        
        return [
            'status' => 'success',
            'message' => 'Correo enviado correctamente'
        ];
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        
        return [
            'status' => 'error',
            'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo
        ];
    }
}

/**
 * Obtiene la URL base de la aplicación
 * 
 * @return string URL base
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    
    // Si estamos en una subcarpeta dentro de mail, subimos un nivel
    if (strpos($uri, '/mail') !== false) {
        $uri = dirname($uri);
    }
    
    return "$protocol://$host$uri";
} 