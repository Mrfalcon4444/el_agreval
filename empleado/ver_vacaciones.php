<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Detalles de Vacaciones - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: mis_vacaciones.php");
    exit();
}

$id_vacaciones = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$id_empleado = $_SESSION['id_empleado'];

// Obtener detalles de las vacaciones
$sql = "SELECT v.*, e.nickname, e.cargo, d.nombre_departamento 
        FROM VACACIONES v 
        JOIN EMPLEADOS e ON v.id_empleado = e.id_empleado 
        JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento 
        WHERE v.id_vacaciones = ? AND v.id_empleado = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_vacaciones, $id_empleado);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: mis_vacaciones.php");
    exit();
}

$vacaciones = $result->fetch_assoc();
$stmt->close();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detalles de Solicitud de Vacaciones</h1>
        <a href="mis_vacaciones.php" class="btn btn-ghost">
            Volver a Mis Vacaciones
        </a>
    </div>

    <!-- Detalles de vacaciones -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6">
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
                    <span class="label-text font-bold">Estado</span>
                </label>
                <div class="p-2">
                    <span class="badge <?php 
                        echo isset($vacaciones['estado_aprobacion']) ? 
                            ($vacaciones['estado_aprobacion'] == 'aprobada' ? 'badge-success' : 
                            ($vacaciones['estado_aprobacion'] == 'rechazada' ? 'badge-error' : 'badge-warning')) 
                            : 'badge-warning'; 
                    ?>">
                        <?php 
                        echo isset($vacaciones['estado_aprobacion']) ? 
                            ($vacaciones['estado_aprobacion'] == 'aprobada' ? 'Aprobada' : 
                            ($vacaciones['estado_aprobacion'] == 'rechazada' ? 'Rechazada' : 'Pendiente')) 
                            : 'Pendiente'; 
                        ?>
                    </span>
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

            <?php if (isset($vacaciones['comentario_rrhh']) && !empty($vacaciones['comentario_rrhh'])): ?>
            <div class="form-control md:col-span-2">
                <label class="label">
                    <span class="label-text font-bold">Comentarios de RRHH</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo nl2br(htmlspecialchars($vacaciones['comentario_rrhh'])); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($vacaciones['estado_aprobacion']) && $vacaciones['estado_aprobacion'] == 'aprobada'): ?>
            <div class="form-control md:col-span-2">
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Tu solicitud de vacaciones ha sido aprobada.</span>
                </div>
            </div>
            <?php elseif (isset($vacaciones['estado_aprobacion']) && $vacaciones['estado_aprobacion'] == 'rechazada'): ?>
            <div class="form-control md:col-span-2">
                <div class="alert alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Tu solicitud de vacaciones ha sido rechazada.</span>
                </div>
            </div>
            <?php else: ?>
            <div class="form-control md:col-span-2">
                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Tu solicitud de vacaciones está pendiente de revisión por RRHH.</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?> 