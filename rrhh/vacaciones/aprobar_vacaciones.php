<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

// Cargar PHPMailer
require_once '../../mail/smtp_config.php';

// Cargar PHPMailer desde la carpeta correcta
$phpmailerPath = dirname(dirname(dirname(__FILE__))) . '/mail/phpmailer/';
if (file_exists($phpmailerPath.'PHPMailer.php')) {
    require_once $phpmailerPath.'PHPMailer.php';
    require_once $phpmailerPath.'SMTP.php';
    require_once $phpmailerPath.'Exception.php';
} else {
    error_log("PHPMailer no encontrado en: " . $phpmailerPath);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pageTitle = "Aprobar Vacaciones - El Agreval";

include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?mensaje=ID de vacaciones inválido&tipo=error");
    exit();
}

$id_vacaciones = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Verificar que las vacaciones existen y están pendientes
$sql = "SELECT v.*, e.nickname, e.correo FROM VACACIONES v 
        JOIN EMPLEADOS e ON v.id_empleado = e.id_empleado 
        WHERE v.id_vacaciones = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_vacaciones);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php?mensaje=Solicitud de vacaciones no encontrada&tipo=error");
    exit();
}

$vacaciones = $result->fetch_assoc();
if ($vacaciones['estado_aprobacion'] != 'pendiente') {
    header("Location: index.php?mensaje=La solicitud de vacaciones ya ha sido procesada&tipo=error");
    exit();
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comentario = filter_var($_POST['comentario'], FILTER_SANITIZE_STRING);
    
    // Actualizar el estado de las vacaciones
    $sql = "UPDATE VACACIONES SET estado_aprobacion = 'aprobada', estado = 1, comentario_rrhh = ? WHERE id_vacaciones = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $comentario, $id_vacaciones);
    
    if ($stmt->execute()) {
        // Datos del empleado
        $destinatario = $vacaciones['correo'];
        $nombre_empleado = $vacaciones['nickname'];
        $fecha_inicio = date('d/m/Y', strtotime($vacaciones['fecha_inicio']));
        $fecha_fin = date('d/m/Y', strtotime($vacaciones['fecha_finalizacion']));
        $dias = $vacaciones['dias_totales'];

        // Asunto y contenido del correo
        $asunto = 'Aprobación de solicitud de vacaciones';
        $contenido = "
        <h3>Hola, $nombre_empleado</h3>
        <p>Nos complace informarte que tu solicitud de vacaciones ha sido <strong>aprobada</strong> por Recursos Humanos.</p>
        <p><strong>Detalles:</strong></p>
        <ul>
            <li>Fecha de inicio: $fecha_inicio</li>
            <li>Fecha de finalización: $fecha_fin</li>
            <li>Días totales: $dias</li>
        </ul>
        ";
        
        if (!empty($comentario)) {
            $contenido .= "<p><strong>Comentarios adicionales:</strong> $comentario</p>";
        }
        
        $contenido .= "
        <p>Si tienes alguna duda, no dudes en contactarnos.</p>
        <p>Saludos,<br>El equipo de Recursos Humanos</p>
        ";

        // Enviar el correo
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host = $smtp_config['host'];
            $mail->SMTPAuth = $smtp_config['auth'];
            $mail->Username = $smtp_config['username'];
            $mail->Password = $smtp_config['password'];
            $mail->SMTPSecure = $smtp_config['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $smtp_config['port'];
            $mail->CharSet = 'UTF-8';
            
            // Remitente
            $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
            
            // Desactivar depuración SMTP
            $mail->SMTPDebug = 0; // Sin información de depuración
            
            // Destinatario
            $mail->addAddress($destinatario, $nombre_empleado);
            
            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $contenido;
            $mail->AltBody = strip_tags($contenido);
            
            // Enviar
            $mail->send();
        } catch (Exception $e) {
            // Manejar errores de envío de correo
            error_log("Error al enviar el correo: {$mail->ErrorInfo}");
        }

        header("Location: index.php?mensaje=Solicitud de vacaciones aprobada exitosamente&tipo=success");
        exit();
    } else {
        $error = "Error al aprobar la solicitud de vacaciones: " . $stmt->error;
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Aprobar Solicitud de Vacaciones</h1>
        <a href="index.php" class="btn btn-ghost">
            Volver a Vacaciones
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-error mb-6">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="bg-base-100 shadow-xl rounded-lg p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">Detalles de la solicitud</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p><span class="font-bold">Empleado:</span> <?php echo htmlspecialchars($vacaciones['nickname']); ?></p>
                    <p><span class="font-bold">Días totales:</span> <?php echo $vacaciones['dias_totales']; ?></p>
                </div>
                <div>
                    <p><span class="font-bold">Fecha inicio:</span> <?php echo date('d/m/Y', strtotime($vacaciones['fecha_inicio'])); ?></p>
                    <p><span class="font-bold">Fecha fin:</span> <?php echo date('d/m/Y', strtotime($vacaciones['fecha_finalizacion'])); ?></p>
                </div>
            </div>
            <?php if (isset($vacaciones['comentarios']) && !empty($vacaciones['comentarios'])): ?>
            <div class="mt-4">
                <p><span class="font-bold">Comentarios del empleado:</span></p>
                <div class="p-2 bg-base-200 rounded-lg mt-1">
                    <?php echo nl2br(htmlspecialchars($vacaciones['comentarios'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="mt-8">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Comentarios (opcional)</span>
                </label>
                <textarea name="comentario" class="textarea textarea-bordered h-24" placeholder="Añada comentarios o instrucciones adicionales relacionadas con estas vacaciones..."></textarea>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <a href="index.php" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-success" onclick="return confirm('¿Está seguro de aprobar esta solicitud de vacaciones?');">
                    Aprobar Vacaciones
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include '../../includes/footer.php';
?> 