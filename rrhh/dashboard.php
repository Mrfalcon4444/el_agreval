<?php
// Iniciar sesión
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Panel de RRHH - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$total_query = "SELECT COUNT(*) as total FROM EMPLEADOS";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_empleados = $total_row['total'];
$total_paginas = ceil($total_empleados / $registros_por_pagina);

$sql = "SELECT e.id_empleado, e.cargo, e.correo, e.nickname, e.estado_activo, 
               e.telefono_personal, e.fecha_ingreso_escuela, d.nombre_departamento, e.rol,
               n.salario_bruto
        FROM EMPLEADOS e
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
        LEFT JOIN NOMINAS n ON e.id_empleado = n.id_empleado
        ORDER BY e.id_empleado
        LIMIT $offset, $registros_por_pagina";

$result = $conn->query($sql);

// Obtener estadísticas de vacaciones pendientes
$vacaciones_query = "SELECT COUNT(*) as pendientes FROM VACACIONES WHERE estado = 0";
$vacaciones_result = $conn->query($vacaciones_query);
$vacaciones_pendientes = $vacaciones_result->fetch_assoc()['pendientes'];

// Obtener estadísticas de nómina
$nomina_query = "SELECT COUNT(*) as total FROM NOMINAS WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$nomina_result = $conn->query($nomina_query);
$nominas_recientes = $nomina_result->fetch_assoc()['total'];

// Obtener estadísticas de incapacidades
$incapacidades_query = "SELECT COUNT(*) as total FROM INCAPACIDADES WHERE estado = 1";
$incapacidades_result = $conn->query($incapacidades_query);
$incapacidades_activas = $incapacidades_result->fetch_assoc()['total'];
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado del Dashboard -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Panel de RRHH - Gestión de Personal</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm">Bienvenido, <?php echo htmlspecialchars($_SESSION['nickname']); ?></span>
            <a href="../logout.php" class="btn btn-sm">Cerrar Sesión</a>
        </div>
    </div>
    
    <!-- Tarjetas de resumen -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total Empleados -->
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total Empleados</div>
                <div class="stat-value"><?php echo $total_empleados; ?></div>
                <div class="stat-desc">Activos e inactivos</div>
            </div>
        </div>
        
        <!-- Solicitudes de Vacaciones -->
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Solicitudes de Vacaciones</div>
                <div class="stat-value"><?php echo $vacaciones_pendientes; ?></div>
                <div class="stat-desc">Pendientes de revisión</div>
            </div>
        </div>
        
        <!-- Nóminas Recientes -->
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Nóminas Procesadas</div>
                <div class="stat-value"><?php echo $nominas_recientes; ?></div>
                <div class="stat-desc">Últimos 30 días</div>
            </div>
        </div>

        <!-- Incapacidades Activas -->
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Incapacidades Activas</div>
                <div class="stat-value"><?php echo $incapacidades_activas; ?></div>
                <div class="stat-desc">En proceso</div>
            </div>
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
    
    <!-- Botones de acción -->
    <div class="flex flex-wrap gap-4 mb-6">
        <a href="crear_empleado.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nuevo Empleado
        </a>
        <a href="vacaciones/index.php" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"/>
            </svg>
            Gestionar Vacaciones
        </a>
        <a href="nomina/index.php" class="btn btn-accent">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
            </svg>
            Gestionar Nómina
        </a>
        <a href="incapacidades/index.php" class="btn btn-info">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            Gestionar Incapacidades
        </a>
    </div>
    
    <!-- Tabla de empleados -->
    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg">
        <table class="table w-full">
            <!-- Encabezado de la tabla -->
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Rol</th>
                    <th>Departamento</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    $fila_alterna = true;
                    while($row = $result->fetch_assoc()) {
                        $clase_fila = $fila_alterna ? 'bg-base-200' : '';
                        $estado_texto = $row['estado_activo'] ? 'Activo' : 'Inactivo';
                        $estado_clase = $row['estado_activo'] ? 'text-success' : 'text-error';
                ?>
                <tr class="<?php echo $clase_fila; ?>">
                    <th><?php echo $row['id_empleado']; ?></th>
                    <td><?php echo htmlspecialchars($row['nickname']); ?></td>
                    <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                    <td><?php echo htmlspecialchars($row['rol'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_departamento'] ?? 'Sin departamento'); ?></td>
                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                    <td><?php echo htmlspecialchars($row['telefono_personal'] ?? 'No disponible'); ?></td>
                    <td><?php echo $row['fecha_ingreso_escuela'] ? date('d/m/Y', strtotime($row['fecha_ingreso_escuela'])) : 'No disponible'; ?></td>
                    <td class="<?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></td>
                    <td class="flex space-x-2">
                        <div class="dropdown dropdown-end">
                            <label tabindex="0" class="btn btn-sm m-1">Acciones</label>
                            <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                                <li><a href="modificar_empleado.php?id=<?php echo $row['id_empleado']; ?>">Editar Información</a></li>
                                <li><a href="nomina/gestionar.php?id=<?php echo $row['id_empleado']; ?>">Gestionar Nómina</a></li>
                                <li><a href="vacaciones/solicitudes.php?id=<?php echo $row['id_empleado']; ?>">Ver Vacaciones</a></li>
                                <li><a href="incapacidades/gestionar.php?id=<?php echo $row['id_empleado']; ?>">Gestionar Incapacidades</a></li>
                                <?php if ($row['estado_activo']): ?>
                                <li><a href="cambiar_estado.php?id=<?php echo $row['id_empleado']; ?>&accion=baja" 
                                      class="text-error"
                                      onclick="return confirm('¿Estás seguro de dar de baja a este empleado?');">Dar Baja</a></li>
                                <?php else: ?>
                                <li><a href="cambiar_estado.php?id=<?php echo $row['id_empleado']; ?>&accion=alta" 
                                      class="text-success">Activar</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </td>
                </tr>
                <?php 
                        $fila_alterna = !$fila_alterna;
                    }
                } else {
                ?>
                <tr>
                    <td colspan="10" class="text-center py-4">No se encontraron empleados</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
    <div class="flex justify-center mt-6">
        <div class="btn-group">
            <?php if ($pagina_actual > 1): ?>
            <a href="?pagina=<?php echo ($pagina_actual - 1); ?>" class="btn">«</a>
            <?php else: ?>
            <button class="btn btn-disabled">«</button>
            <?php endif; ?>
            
            <?php
            // Determinar qué páginas mostrar
            $inicio = max(1, $pagina_actual - 2);
            $fin = min($total_paginas, $pagina_actual + 2);
            
            // Mostrar primera página si no está en el rango
            if ($inicio > 1) {
                echo '<a href="?pagina=1" class="btn">1</a>';
                if ($inicio > 2) {
                    echo '<button class="btn btn-disabled">...</button>';
                }
            }
            
            // Mostrar páginas del rango
            for ($i = $inicio; $i <= $fin; $i++) {
                if ($i == $pagina_actual) {
                    echo '<button class="btn btn-active">' . $i . '</button>';
                } else {
                    echo '<a href="?pagina=' . $i . '" class="btn">' . $i . '</a>';
                }
            }
            
            // Mostrar última página si no está en el rango
            if ($fin < $total_paginas) {
                if ($fin < $total_paginas - 1) {
                    echo '<button class="btn btn-disabled">...</button>';
                }
                echo '<a href="?pagina=' . $total_paginas . '" class="btn">' . $total_paginas . '</a>';
            }
            ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
            <a href="?pagina=<?php echo ($pagina_actual + 1); ?>" class="btn">»</a>
            <?php else: ?>
            <button class="btn btn-disabled">»</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Cerrar conexión
$conn->close();

// Incluir el pie de página común
include '../includes/footer.php';
?>