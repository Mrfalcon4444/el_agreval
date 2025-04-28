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
               DATE(e.fecha_ingreso_escuela) as fecha_ingreso, d.nombre_departamento, e.foto_de_perfil 
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

// Contar vacaciones pendientes
$sql_vac_pendientes = "SELECT COUNT(*) as total FROM VACACIONES WHERE id_empleado = ? AND estado_aprobacion = 'pendiente'";
$stmt_vac_pendientes = $conn->prepare($sql_vac_pendientes);
$stmt_vac_pendientes->bind_param("i", $id_empleado);
$stmt_vac_pendientes->execute();
$result_vac_pendientes = $stmt_vac_pendientes->get_result();
$vac_pendientes = $result_vac_pendientes->fetch_assoc()['total'];

// Contar vacaciones activas
$sql_vac_activas = "SELECT COUNT(*) as total 
                   FROM VACACIONES 
                   WHERE id_empleado = ? 
                   AND estado = 1 
                   AND estado_aprobacion = 'aprobada'
                   AND fecha_finalizacion >= CURDATE()";

$stmt_vac_activas = $conn->prepare($sql_vac_activas);
$stmt_vac_activas->bind_param("i", $id_empleado);
$stmt_vac_activas->execute();
$result_vac_activas = $stmt_vac_activas->get_result();
$vacaciones_activas = $result_vac_activas->fetch_assoc();
$stmt_vac_activas->close();

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
    
    <!-- Información de la foto de perfil -->
    <div class="flex items-center space-x-4 mb-6">
        <!-- Foto de perfil circular -->
        <div class="avatar">
            <div class="w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                <img src="<?php echo !empty($empleado['foto_perfil']) ? htmlspecialchars($empleado['foto_perfil']) : '../images/perfil/default.jpg'; ?>" 
                    alt="Foto de perfil"
                    onerror="this.src='../images/perfil/default.jpg'">
            </div>
        </div>
        
        <!-- Formulario para actualizar -->
        <form action="actualizar_foto.php" method="post" enctype="multipart/form-data" class="mt-2">
            <label class="btn btn-sm btn-outline cursor-pointer">
                <i class="fas fa-camera mr-2"></i>
                Cambiar foto
                <input type="file" name="foto" class="hidden" accept="image/*" onchange="previewImage(this)">
            </label>
            <button type="submit" class="btn btn-sm btn-primary hidden" id="btn-submit">Guardar</button>
        </form>
    </div>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.avatar img').src = e.target.result;
                document.getElementById('btn-submit').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
    
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
    
    <!-- Menú rápido de vacaciones -->
    <h2 class="text-xl font-semibold mb-4">Gestión de Vacaciones</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Solicitar Vacaciones</h2>
                <p>Registra una nueva solicitud de vacaciones</p>
                <div class="card-actions justify-end">
                    <a href="solicitar_vacaciones.php" class="btn btn-primary">Solicitar</a>
                </div>
            </div>
        </div>
        
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Mis Vacaciones</h2>
                <p>Revisa todas tus solicitudes de vacaciones</p>
                <div class="card-actions justify-end">
                    <a href="mis_vacaciones.php" class="btn btn-primary">Ver todas</a>
                </div>
            </div>
        </div>
        
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Estado de Solicitudes</h2>
                <div class="flex justify-around my-2">
                    <div class="text-center">
                        <div class="badge badge-warning badge-lg"><?php echo $vac_pendientes; ?></div>
                        <p class="text-sm mt-1">Pendientes</p>
                    </div>
                    <div class="text-center">
                        <div class="badge badge-success badge-lg"><?php echo $vacaciones_activas['total']; ?></div>
                        <p class="text-sm mt-1">Aprobadas Activas</p>
                    </div>
                </div>
                <div class="card-actions justify-end">
                    <a href="mis_vacaciones.php" class="btn btn-ghost btn-sm">Ver detalles</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Menú de nómina -->
    <h2 class="text-xl font-semibold mb-4">Nómina</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Mis Nóminas</h2>
                <p>Consulta tus recibos de nómina y visualiza tu historial de pagos</p>
                <div class="card-actions justify-end">
                    <a href="../nomina/empleado/index.php" class="btn btn-primary">Ver nóminas</a>
                </div>
            </div>
        </div>
        
        <?php
        // Obtener la última nómina del empleado
        $sql_ultima_nomina = "SELECT fecha_pago, salario_neto, pdf_ruta FROM NOMINAS 
                             WHERE id_empleado = ? 
                             ORDER BY fecha_pago DESC 
                             LIMIT 1";
        $stmt_ultima_nomina = $conn->prepare($sql_ultima_nomina);
        $stmt_ultima_nomina->bind_param("i", $id_empleado);
        $stmt_ultima_nomina->execute();
        $result_ultima_nomina = $stmt_ultima_nomina->get_result();
        $ultima_nomina = $result_ultima_nomina->fetch_assoc();
        $stmt_ultima_nomina->close();
        ?>
        
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Último Pago</h2>
                <?php if ($ultima_nomina): ?>
                <div class="my-2">
                    <p><span class="font-bold">Fecha:</span> <?php echo date('d/m/Y', strtotime($ultima_nomina['fecha_pago'])); ?></p>
                    <p><span class="font-bold">Monto Neto:</span> $<?php echo number_format($ultima_nomina['salario_neto'], 2); ?></p>
                </div>
                <div class="card-actions justify-end">
                    <?php if ($ultima_nomina['pdf_ruta']): ?>
                    <a href="<?php echo '../' . $ultima_nomina['pdf_ruta']; ?>" target="_blank" class="btn btn-outline btn-sm">Ver recibo</a>
                    <?php endif; ?>
                    <a href="../nomina/empleado/index.php" class="btn btn-ghost btn-sm">Ver historial</a>
                </div>
                <?php else: ?>
                <p>No hay información de pagos disponible</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Cerrar conexión
$stmt->close();
$stmt_pendientes->close();
$stmt_activas->close();
$stmt_vac_pendientes->close();
$stmt_vac_activas->close();
$stmt_lista_activas->close();
$conn->close();

// Incluir el pie de página común
include '../includes/footer.php';
?>