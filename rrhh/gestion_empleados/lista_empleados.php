<?php
// Iniciar sesión
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    die("Acceso denegado");
}


require_once '../config/config.php';

$pageTitle = "Editar Empleados - El Agreval";

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
               e.telefono_personal, e.fecha_ingreso_escuela, d.nombre_departamento, e.rol
        FROM EMPLEADOS e
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
        ORDER BY e.id_empleado
        LIMIT $offset, $registros_por_pagina";

$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado del Dashboard -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Panel de Administración - Gestión de Empleados</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm">Bienvenido, <?php echo htmlspecialchars($_SESSION['nickname']); ?></span>
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
                        <a href="gestion_empleado/editar_empleado.php?id=<?php echo $row['id_empleado']; ?>" class="btn btn-ghost btn-sm">
                            Editar
                        </a>
                        <?php if ($row['estado_activo']): ?>
                        <a href="cambiar_estado.php?id=<?php echo $row['id_empleado']; ?>&accion=baja" class="btn btn-ghost btn-sm text-error" 
                           onclick="return confirm('¿Estás seguro de dar de baja a este empleado?');">
                            Dar Baja
                        </a>
                        <?php else: ?>
                        <a href="cambiar_estado.php?id=<?php echo $row['id_empleado']; ?>&accion=alta" class="btn btn-ghost btn-sm text-success">
                            Activar
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                        $fila_alterna = !$fila_alterna;
                    }
                } else {
                ?>
                <tr>
                    <td colspan="9" class="text-center py-4">No se encontraron empleados</td>
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