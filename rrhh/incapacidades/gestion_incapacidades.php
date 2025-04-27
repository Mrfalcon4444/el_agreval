<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    die("Acceso denegado");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Gestión de Incapacidades - El Agreval";

include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Obtener el filtro de estado si existe
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';

// Construir la consulta SQL con el filtro
$sql = "SELECT i.*, e.nickname, e.cargo, d.nombre_departamento 
        FROM INCAPACIDADES i
        JOIN EMPLEADOS e ON i.id_empleado = e.id_empleado
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento";

if ($filtro_estado != 'todos') {
    if ($filtro_estado == 'aprobada') {
        $sql .= " WHERE i.estado_aprobacion = ? AND i.fecha_finalizacion >= CURDATE()";
    } else {
        $sql .= " WHERE i.estado_aprobacion = ?";
    }
}

$sql .= " ORDER BY i.fecha_solicitud DESC";

$stmt = $conn->prepare($sql);
if ($filtro_estado != 'todos') {
    $stmt->bind_param("s", $filtro_estado);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestión de Incapacidades</h1>
        <div class="flex space-x-4">
            <a href="../../rrhh/dashboard.php" class="btn btn-ghost">
                Volver al Dashboard
            </a>
        </div>
    </div>

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

    <!-- Tabla de incapacidades -->
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
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <th><?php echo $row['id_incapacidad']; ?></th>
                            <td><?php echo htmlspecialchars($row['nickname']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_departamento'] ?? 'Sin departamento'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_solicitud'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_inicio'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_finalizacion'])); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                            <td>
                                <span class="badge <?php 
                                    echo $row['estado_aprobacion'] == 'aprobada' ? 'badge-success' : 
                                        ($row['estado_aprobacion'] == 'rechazada' ? 'badge-error' : 'badge-warning'); 
                                ?>">
                                    <?php 
                                    echo $row['estado_aprobacion'] == 'aprobada' ? 'Aprobada' : 
                                        ($row['estado_aprobacion'] == 'rechazada' ? 'Rechazada' : 'Pendiente'); 
                                    ?>
                                </span>
                            </td>
                            <td class="flex space-x-2">
                                <a href="ver_incapacidad.php?id=<?php echo $row['id_incapacidad']; ?>" class="btn btn-ghost btn-sm">
                                    Ver
                                </a>
                                <?php if ($row['estado_aprobacion'] == 'pendiente'): ?>
                                    <a href="aprobar_incapacidad.php?id=<?php echo $row['id_incapacidad']; ?>" class="btn btn-success btn-sm">
                                        Aprobar
                                    </a>
                                    <a href="rechazar_incapacidad.php?id=<?php echo $row['id_incapacidad']; ?>" class="btn btn-error btn-sm">
                                        Rechazar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center py-4">No se encontraron incapacidades registradas</td>
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