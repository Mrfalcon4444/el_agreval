<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

// Establecer explícitamente la zona horaria a Ciudad de México (o la que corresponda a tu ubicación)
date_default_timezone_set('America/Mexico_City');

$pageTitle = "Gestión de Vacaciones - El Agreval";

include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Actualizar automáticamente las vacaciones finalizadas
$update_sql = "UPDATE VACACIONES SET estado = 0 
              WHERE estado = 1 
              AND estado_aprobacion = 'aprobada' 
              AND fecha_finalizacion < CURDATE()";
$update_result = $conn->query($update_sql);
$vacaciones_finalizadas = $conn->affected_rows;

// Obtener el filtro de estado si existe
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

// Construir la consulta SQL con el filtro
$sql = "SELECT v.*, e.nickname, e.cargo, d.nombre_departamento 
        FROM VACACIONES v
        JOIN EMPLEADOS e ON v.id_empleado = e.id_empleado
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento";

if ($filtro_estado != 'todos') {
    if ($filtro_estado == 'aprobada') {
        $sql .= " WHERE v.estado_aprobacion = ? AND v.fecha_finalizacion >= CURDATE()";
    } else {
        $sql .= " WHERE v.estado_aprobacion = ?";
    }
}

$sql .= " ORDER BY v.fecha_solicitud DESC";

$stmt = $conn->prepare($sql);
if ($filtro_estado != 'todos') {
    $stmt->bind_param("s", $filtro_estado);
}
$stmt->execute();
$result = $stmt->get_result();

// Fecha actual para comparaciones (medianoche de hoy)
$fecha_actual = new DateTime(date('Y-m-d'));
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestión de Vacaciones</h1>
        <a href="../../rrhh/dashboard.php" class="btn btn-ghost">
            Volver al Dashboard
        </a>
    </div>

    <!-- Mensajes de notificación -->
    <?php if (isset($_GET['mensaje'])): ?>
        <?php 
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'info';
        $clase = ($tipo == 'error') ? 'alert-error' : 'alert-success';
        ?>
        <div class="alert <?php echo $clase; ?> mb-6">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($vacaciones_finalizadas > 0): ?>
        <div class="alert alert-info mb-6">
            <span>Se han finalizado automáticamente <?php echo $vacaciones_finalizadas; ?> período(s) de vacaciones que ya concluyeron.</span>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="flex space-x-4 mb-6">
        <a href="?estado=todos" class="btn <?php echo $filtro_estado == 'todos' ? 'btn-primary' : 'btn-ghost'; ?>">
            Todas
        </a>
        <a href="?estado=pendiente" class="btn <?php echo $filtro_estado == 'pendiente' ? 'btn-primary' : 'btn-ghost'; ?>">
            Pendientes
        </a>
        <a href="?estado=aprobada" class="btn <?php echo $filtro_estado == 'aprobada' ? 'btn-primary' : 'btn-ghost'; ?>">
            Aprobadas
        </a>
        <a href="?estado=rechazada" class="btn <?php echo $filtro_estado == 'rechazada' ? 'btn-primary' : 'btn-ghost'; ?>">
            Rechazadas
        </a>
    </div>

    <!-- Tabla de vacaciones -->
    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Departamento</th>
                    <th>Fecha Solicitud</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Días</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php 
                        // Convertir fechas a objetos DateTime para comparaciones más precisas
                        $fecha_inicio = new DateTime($row['fecha_inicio']);
                        $fecha_fin = new DateTime($row['fecha_finalizacion']);
                        
                        // Determinar el estado temporal
                        $estado_temporal = '';
                        $clase_temporal = '';
                        
                        if ($fecha_actual > $fecha_fin) {
                            $estado_temporal = 'Finalizado';
                            $clase_temporal = 'badge-ghost';
                        } elseif ($fecha_actual >= $fecha_inicio && $fecha_actual <= $fecha_fin) {
                            $estado_temporal = 'En curso';
                            $clase_temporal = 'badge-info';
                        } else {
                            $estado_temporal = 'Próximo';
                            $clase_temporal = 'badge-secondary';
                        }
                        ?>
                        <tr>
                            <th><?php echo $row['id_vacaciones']; ?></th>
                            <td><?php echo htmlspecialchars($row['nickname']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_departamento'] ?? 'Sin departamento'); ?></td>
                            <td><?php echo isset($row['fecha_solicitud']) ? date('d/m/Y H:i', strtotime($row['fecha_solicitud'])) : 'N/A'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_inicio'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_finalizacion'])); ?></td>
                            <td><?php echo $row['dias_totales']; ?></td>
                            <td>
                                <span class="badge <?php 
                                    echo isset($row['estado_aprobacion']) ? 
                                        ($row['estado_aprobacion'] == 'aprobada' ? 'badge-success' : 
                                        ($row['estado_aprobacion'] == 'rechazada' ? 'badge-error' : 'badge-warning')) 
                                        : 'badge-warning'; 
                                ?>">
                                    <?php 
                                    echo isset($row['estado_aprobacion']) ? 
                                        ($row['estado_aprobacion'] == 'aprobada' ? 'Aprobada' : 
                                        ($row['estado_aprobacion'] == 'rechazada' ? 'Rechazada' : 'Pendiente')) 
                                        : 'Pendiente'; 
                                    ?>
                                </span>
                                <?php if (isset($row['estado_aprobacion']) && $row['estado_aprobacion'] == 'aprobada'): ?>
                                    <span class="badge ml-1 <?php echo $clase_temporal; ?>">
                                        <?php echo $estado_temporal; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex space-x-2">
                                    <a href="ver_vacaciones.php?id=<?php echo $row['id_vacaciones']; ?>" class="btn btn-sm btn-info">Ver</a>
                                    
                                    <?php if (isset($row['estado_aprobacion']) && $row['estado_aprobacion'] == 'pendiente'): ?>
                                        <a href="aprobar_vacaciones.php?id=<?php echo $row['id_vacaciones']; ?>" class="btn btn-sm btn-success">
                                            Aprobar
                                        </a>
                                        <a href="rechazar_vacaciones.php?id=<?php echo $row['id_vacaciones']; ?>" class="btn btn-sm btn-error">
                                            Rechazar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4">No hay solicitudes de vacaciones registradas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include '../../includes/footer.php';
?> 