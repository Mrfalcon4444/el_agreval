<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Panel de Empleado - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Obtener datos del empleado actual
$id_empleado = $_SESSION['id_empleado'];
$sql = "SELECT e.cargo, e.nickname, e.correo, e.telefono_personal, 
               DATE(e.fecha_ingreso_escuela) as fecha_ingreso, d.nombre_departamento 
        FROM EMPLEADOS e
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
        WHERE e.id_empleado = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();
$empleado = $result->fetch_assoc();

// Contar incapacidades pendientes
$sql_pendientes = "SELECT COUNT(*) as total FROM INCAPACIDADES WHERE id_empleado = ? AND estado_aprobacion = 'pendiente'";
$stmt_pendientes = $conn->prepare($sql_pendientes);
$stmt_pendientes->bind_param("i", $id_empleado);
$stmt_pendientes->execute();
$result_pendientes = $stmt_pendientes->get_result();
$pendientes = $result_pendientes->fetch_assoc()['total'];

// Contar incapacidades activas
$sql_activas = "SELECT COUNT(*) as total 
                FROM INCAPACIDADES 
                WHERE id_empleado = ? 
                AND estado = 1 
                AND estado_aprobacion = 'aprobada'
                AND fecha_finalizacion >= CURDATE()";

$stmt_activas = $conn->prepare($sql_activas);
$stmt_activas->bind_param("i", $id_empleado);
$stmt_activas->execute();
$result_activas = $stmt_activas->get_result();
$incapacidades_activas = $result_activas->fetch_assoc();
$stmt_activas->close();

// Obtener lista de incapacidades activas para mostrar
$sql_lista_activas = "SELECT i.*, d.nombre_departamento 
                      FROM INCAPACIDADES i
                      JOIN EMPLEADOS e ON i.id_empleado = e.id_empleado
                      LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
                      WHERE i.id_empleado = ? 
                      AND i.estado = 1 
                      AND i.estado_aprobacion = 'aprobada'
                      AND i.fecha_finalizacion >= CURDATE()
                      ORDER BY i.fecha_inicio DESC";

$stmt_lista_activas = $conn->prepare($sql_lista_activas);
$stmt_lista_activas->bind_param("i", $id_empleado);
$stmt_lista_activas->execute();
$result_lista_activas = $stmt_lista_activas->get_result();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado del Dashboard -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Panel de Empleado</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm">Bienvenido, <?php echo htmlspecialchars($empleado['nickname']); ?></span>
            <a href="../logout.php" class="btn btn-sm">Cerrar Sesión</a>
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
    
    <!-- Información del empleado -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Mi Información</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><span class="font-bold">Nombre:</span> <?php echo htmlspecialchars($empleado['nickname']); ?></p>
                <p><span class="font-bold">Cargo:</span> <?php echo htmlspecialchars($empleado['cargo']); ?></p>
                <p><span class="font-bold">Departamento:</span> <?php echo htmlspecialchars($empleado['nombre_departamento'] ?? 'No asignado'); ?></p>
            </div>
            <div>
                <p><span class="font-bold">Correo:</span> <?php echo htmlspecialchars($empleado['correo']); ?></p>
                <p><span class="font-bold">Teléfono:</span> <?php echo htmlspecialchars($empleado['telefono_personal'] ?? 'No disponible'); ?></p>
                <p><span class="font-bold">Fecha de Ingreso:</span> <?php echo $empleado['fecha_ingreso'] ? date('d/m/Y', strtotime($empleado['fecha_ingreso'])) : 'No disponible'; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Menú rápido de incapacidades -->
    <h2 class="text-xl font-semibold mb-4">Gestión de Incapacidades</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Solicitar Incapacidad</h2>
                <p>Registra una nueva solicitud de incapacidad</p>
                <div class="card-actions justify-end">
                    <a href="solicitar_incapacidad.php" class="btn btn-primary">Solicitar</a>
                </div>
            </div>
        </div>
        
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Mis Incapacidades</h2>
                <p>Revisa todas tus solicitudes de incapacidad</p>
                <div class="card-actions justify-end">
                    <a href="mis_incapacidades.php" class="btn btn-primary">Ver todas</a>
                </div>
            </div>
        </div>
        
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Estado de Solicitudes</h2>
                <div class="flex justify-around my-2">
                    <div class="text-center">
                        <div class="badge badge-warning badge-lg"><?php echo $pendientes; ?></div>
                        <p class="text-sm mt-1">Pendientes</p>
                    </div>
                    <div class="text-center">
                        <div class="badge badge-success badge-lg"><?php echo $incapacidades_activas['total']; ?></div>
                        <p class="text-sm mt-1">Aprobadas Activas</p>
                    </div>
                </div>
                <div class="card-actions justify-end">
                    <a href="mis_incapacidades.php" class="btn btn-ghost btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Cerrar conexión
$stmt->close();
$stmt_pendientes->close();
$stmt_activas->close();
$stmt_lista_activas->close();
$conn->close();

// Incluir el pie de página común
include '../includes/footer.php';
?>