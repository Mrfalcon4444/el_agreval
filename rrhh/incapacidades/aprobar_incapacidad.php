<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Aprobar Incapacidad - El Agreval";

include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_incapacidades.php?mensaje=ID de incapacidad inválido&tipo=error");
    exit();
}

$id_incapacidad = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Verificar que la incapacidad existe y está pendiente
$sql = "SELECT i.*, e.nickname, e.correo FROM INCAPACIDADES i 
        JOIN EMPLEADOS e ON i.id_empleado = e.id_empleado 
        WHERE i.id_incapacidad = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_incapacidad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: gestion_incapacidades.php?mensaje=Incapacidad no encontrada&tipo=error");
    exit();
}

$incapacidad = $result->fetch_assoc();
if ($incapacidad['estado_aprobacion'] != 'pendiente') {
    header("Location: gestion_incapacidades.php?mensaje=La incapacidad ya ha sido procesada&tipo=error");
    exit();
}

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comentario = filter_var($_POST['comentario'], FILTER_SANITIZE_STRING);
    
    // Actualizar el estado de la incapacidad
    $sql = "UPDATE INCAPACIDADES SET estado_aprobacion = 'aprobada', estado = 1, comentario_rrhh = ? WHERE id_incapacidad = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $comentario, $id_incapacidad);
    
    if ($stmt->execute()) {
        // Enviar correo al empleado
        require_once '../../mail/simple_correo.php'; // Archivo con la configuración de PHPMailer

        // Datos del empleado
        $destinatario = $incapacidad['correo'];
        $nombre_empleado = $incapacidad['nickname'];

        // Asunto y contenido del correo
        $asunto = 'Estado de tu solicitud de incapacidad';
        $contenido = "
        <h3>Hola, $nombre_empleado</h3>
        <p>Tu solicitud de incapacidad ha sido <strong>aprobada</strong> por Recursos Humanos.</p>
        <p>Comentarios adicionales: $comentario</p>
        <p>Si tienes alguna duda, no dudes en contactarnos.</p>
        <p>Saludos,<br>El equipo de Recursos Humanos</p>
        ";

        // Enviar el correo
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP usando la función en simple_correo.php
            setupMailer($mail);
            
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

        // Redirigir con mensaje de éxito
        header("Location: gestion_incapacidades.php?mensaje=Incapacidad aprobada exitosamente&tipo=success");
        exit();
    } else {
        $error = "Error al aprobar la incapacidad: " . $stmt->error;
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Aprobar Solicitud de Incapacidad</h1>
        <a href="gestion_incapacidades.php" class="btn btn-ghost">
            Volver a Incapacidades
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
                    <p><span class="font-bold">Empleado:</span> <?php echo htmlspecialchars($incapacidad['nickname']); ?></p>
                    <p><span class="font-bold">Tipo:</span> <?php echo htmlspecialchars($incapacidad['tipo']); ?></p>
                </div>
                <div>
                    <p><span class="font-bold">Fecha inicio:</span> <?php echo date('d/m/Y', strtotime($incapacidad['fecha_inicio'])); ?></p>
                    <p><span class="font-bold">Fecha fin:</span> <?php echo date('d/m/Y', strtotime($incapacidad['fecha_finalizacion'])); ?></p>
                </div>
            </div>
            <div class="mt-4">
                <p><span class="font-bold">Justificación:</span></p>
                <div class="p-2 bg-base-200 rounded-lg mt-1">
                    <?php echo nl2br(htmlspecialchars($incapacidad['documento_justificativo'])); ?>
                </div>
            </div>
        </div>

        <form method="POST" class="mt-8">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Comentarios (opcional)</span>
                </label>
                <textarea name="comentario" class="textarea textarea-bordered h-24" placeholder="Añada comentarios o instrucciones adicionales relacionadas con esta incapacidad..."></textarea>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <a href="gestion_incapacidades.php" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-success" onclick="return confirm('¿Está seguro de aprobar esta solicitud de incapacidad?');">
                    Aprobar Incapacidad
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