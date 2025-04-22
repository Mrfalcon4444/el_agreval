<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Mis Incapacidades - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Obtener el ID del empleado logueado
$id_empleado = $_SESSION['id_empleado'];

// Obtener las incapacidades del empleado
$sql = "SELECT i.*, DATE(i.fecha_solicitud) AS fecha_solicitud_formateada 
        FROM INCAPACIDADES i 
        WHERE i.id_empleado = ? 
        ORDER BY i.fecha_solicitud DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Mis Incapacidades</h1>
        <div class="flex space-x-4">
            <a href="dashboard.php" class="btn btn-ghost">
                Volver al Dashboard
            </a>
            <a href="solicitar_incapacidad.php" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Solicitar Nueva Incapacidad
            </a>
        </div>
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
                    <th>Fecha Solicitud</th>
                    <th>Tipo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <th><?php echo $row['id_incapacidad']; ?></th>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_solicitud_formateada'])); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_inicio'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_finalizacion'])); ?></td>
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
                            <td>
                                <a href="ver_incapacidad.php?id=<?php echo $row['id_incapacidad']; ?>" class="btn btn-ghost btn-sm">
                                    Ver Detalles
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">No tienes incapacidades registradas</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include '../includes/footer.php';
?> 