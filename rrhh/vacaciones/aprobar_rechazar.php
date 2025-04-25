<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Gestionar Solicitud de Vacaciones - El Agreval";

include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?mensaje=ID de solicitud inválido&tipo=error");
    exit();
}

$id_vacaciones = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Obtener detalles de la solicitud
$sql = "SELECT v.*, e.nickname, e.cargo, d.nombre_departamento 
        FROM VACACIONES v 
        JOIN EMPLEADOS e ON v.id_empleado = e.id_empleado 
        JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento 
        WHERE v.id_vacaciones = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_vacaciones);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php?mensaje=Solicitud no encontrada&tipo=error");
    exit();
}

$vacaciones = $result->fetch_assoc();
$stmt->close();

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];
    $comentario_rrhh = $_POST['comentario_rrhh'];
    
    if ($accion === 'aprobar') {
        $estado_aprobacion = 'aprobada';
    } else {
        $estado_aprobacion = 'rechazada';
    }
    
    $sql_update = "UPDATE VACACIONES SET estado_aprobacion = ?, comentario_rrhh = ? WHERE id_vacaciones = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssi", $estado_aprobacion, $comentario_rrhh, $id_vacaciones);
    
    if ($stmt_update->execute()) {
        $mensaje = "La solicitud de vacaciones ha sido " . ($estado_aprobacion == 'aprobada' ? 'aprobada' : 'rechazada') . " correctamente.";
        header("Location: index.php?mensaje=" . urlencode($mensaje) . "&tipo=success");
        exit();
    } else {
        $error = "Error al procesar la solicitud: " . $stmt_update->error;
    }
    
    $stmt_update->close();
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestionar Solicitud de Vacaciones</h1>
        <a href="index.php" class="btn btn-ghost">
            Volver a Gestión de Vacaciones
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-error mb-6">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Información de la solicitud -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Información de la Solicitud</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">ID de Solicitud</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo $vacaciones['id_vacaciones']; ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Empleado</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo htmlspecialchars($vacaciones['nickname']); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Cargo</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo htmlspecialchars($vacaciones['cargo']); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Departamento</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo htmlspecialchars($vacaciones['nombre_departamento']); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Fecha de Solicitud</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo isset($vacaciones['fecha_solicitud']) ? date('d/m/Y H:i', strtotime($vacaciones['fecha_solicitud'])) : 'N/A'; ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Fecha de Inicio</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo date('d/m/Y', strtotime($vacaciones['fecha_inicio'])); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Fecha de Finalización</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo date('d/m/Y', strtotime($vacaciones['fecha_finalizacion'])); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Días Totales</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo $vacaciones['dias_totales']; ?>
                </div>
            </div>

            <?php if (isset($vacaciones['comentarios']) && !empty($vacaciones['comentarios'])): ?>
            <div class="form-control md:col-span-2">
                <label class="label">
                    <span class="label-text font-bold">Comentarios del Empleado</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo nl2br(htmlspecialchars($vacaciones['comentarios'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulario de aprobación -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6">
        <h2 class="text-xl font-bold mb-4">Decisión de RRHH</h2>
        <form method="POST" class="space-y-6">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Comentarios de RRHH</span>
                </label>
                <textarea name="comentario_rrhh" class="textarea textarea-bordered h-24" placeholder="Ingrese sus comentarios sobre esta solicitud"></textarea>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button type="submit" name="accion" value="rechazar" class="btn btn-error">
                    Rechazar Solicitud
                </button>
                <button type="submit" name="accion" value="aprobar" class="btn btn-success">
                    Aprobar Solicitud
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
include '../../includes/footer.php';
?> 