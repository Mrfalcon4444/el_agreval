<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Solicitar Incapacidad - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Obtener datos del empleado actual
$id_empleado = $_SESSION['id_empleado'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_finalizacion = $_POST['fecha_finalizacion'];
    $tipo = filter_var($_POST['tipo'], FILTER_SANITIZE_STRING);
    $documento_justificativo = filter_var($_POST['documento_justificativo'], FILTER_SANITIZE_STRING);
    $estado = 1; // Por defecto, la incapacidad se crea como activa
    $estado_aprobacion = 'pendiente'; // Por defecto, la solicitud está pendiente

    $stmt = $conn->prepare("INSERT INTO INCAPACIDADES (id_empleado, fecha_inicio, fecha_finalizacion, tipo, documento_justificativo, estado, estado_aprobacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $id_empleado, $fecha_inicio, $fecha_finalizacion, $tipo, $documento_justificativo, $estado, $estado_aprobacion);

    if ($stmt->execute()) {
        header("Location: mis_incapacidades.php?mensaje=Solicitud de incapacidad enviada exitosamente. Espera la aprobación de RRHH.&tipo=success");
        exit();
    } else {
        $error = "Error al solicitar la incapacidad: " . $stmt->error;
    }

    $stmt->close();
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Solicitar Incapacidad</h1>
        <a href="dashboard.php" class="btn btn-ghost">
            Volver al Dashboard
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-error mb-6">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Formulario de solicitud -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6">
        <form method="POST" action="procesar_solicitud.php" enctype="multipart/form-data" class="space-y-6">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Tipo de Incapacidad</span>
                </label>
                <select name="tipo" class="select select-bordered w-full" required>
                    <option value="">Seleccione un tipo</option>
                    <option value="Enfermedad General">Enfermedad General</option>
                    <option value="Accidente de Trabajo">Accidente de Trabajo</option>
                    <option value="Maternidad">Maternidad</option>
                    <option value="Riesgo de Trabajo">Riesgo de Trabajo</option>
                </select>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Fecha de Inicio</span>
                </label>
                <input type="date" name="fecha_inicio" class="input input-bordered w-full" required>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Fecha de Finalización</span>
                </label>
                <input type="date" name="fecha_finalizacion" class="input input-bordered w-full" required>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Documento Médico (PDF o Imagen)</span>
                </label>
                <input type="file" name="documento_medico" class="file-input file-input-bordered w-full" accept=".pdf,.jpg,.jpeg,.png" required>
                <label class="label">
                    <span class="label-text-alt">Formatos aceptados: PDF, JPG, JPEG, PNG</span>
                </label>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Descripción de la Incapacidad</span>
                </label>
                <textarea name="descripcion" class="textarea textarea-bordered h-24" placeholder="Describa brevemente el motivo de su incapacidad" required></textarea>
            </div>

            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary">Solicitar Incapacidad</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?> 