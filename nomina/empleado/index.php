<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Mis Nóminas - El Agreval";

// Incluir el encabezado común
include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Obtener ID del empleado actual
$id_empleado = $_SESSION['id_empleado'];

// Parámetros de filtrado y paginación
$fecha_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$fecha_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Construir consulta base
$sql_base = "SELECT n.id_nomina, n.fecha_pago, n.salario_bruto, n.salario_neto, n.pdf_ruta 
             FROM NOMINAS n
             WHERE n.id_empleado = ?";

$params = [$id_empleado];
$param_types = "i";

// Añadir filtros si están definidos
if (!empty($fecha_desde)) {
    $sql_base .= " AND n.fecha_pago >= ?";
    $params[] = $fecha_desde;
    $param_types .= "s";
}

if (!empty($fecha_hasta)) {
    $sql_base .= " AND n.fecha_pago <= ?";
    $params[] = $fecha_hasta;
    $param_types .= "s";
}

// Consulta para contar total de registros
$sql_count = $sql_base;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param($param_types, ...$params);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$total_registros = $result_count->num_rows;
$total_paginas = ceil($total_registros / $registros_por_pagina);
$stmt_count->close();

// Consulta para obtener los registros paginados
$sql_paginado = $sql_base . " ORDER BY n.fecha_pago DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $registros_por_pagina;
$param_types .= "ii";

$stmt = $conn->prepare($sql_paginado);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Obtener datos del empleado
$sql_empleado = "SELECT e.nickname, e.cargo, e.salario_base, d.nombre_departamento 
                FROM EMPLEADOS e
                LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
                WHERE e.id_empleado = ?";
$stmt_empleado = $conn->prepare($sql_empleado);
$stmt_empleado->bind_param("i", $id_empleado);
$stmt_empleado->execute();
$result_empleado = $stmt_empleado->get_result();
$empleado = $result_empleado->fetch_assoc();
$stmt_empleado->close();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Mis Nóminas</h1>
        <a href="../../empleado/dashboard.php" class="btn btn-sm">Volver al Dashboard</a>
    </div>
    
    <!-- Información del empleado -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Mi Información Laboral</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><span class="font-bold">Nombre:</span> <?php echo htmlspecialchars($empleado['nickname']); ?></p>
                <p><span class="font-bold">Cargo:</span> <?php echo htmlspecialchars($empleado['cargo']); ?></p>
                <p><span class="font-bold">Departamento:</span> <?php echo htmlspecialchars($empleado['nombre_departamento'] ?? 'No asignado'); ?></p>
            </div>
            <div>
                <p><span class="font-bold">Salario Base:</span> $<?php echo number_format($empleado['salario_base'], 2); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <form method="get" action="" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Fecha Desde</span>
                </label>
                <input type="date" name="desde" class="input input-bordered" value="<?php echo $fecha_desde; ?>">
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Fecha Hasta</span>
                </label>
                <input type="date" name="hasta" class="input input-bordered" value="<?php echo $fecha_hasta; ?>">
            </div>
            
            <div class="form-control flex items-end">
                <button type="submit" class="btn btn-primary">
                    Filtrar
                </button>
                <a href="index.php" class="btn btn-ghost mt-2">Limpiar filtros</a>
            </div>
        </form>
    </div>
    
    <!-- Tabla de nóminas -->
    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha de Pago</th>
                    <th>Salario Bruto</th>
                    <th>Salario Neto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($nomina = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $nomina['id_nomina']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($nomina['fecha_pago'])); ?></td>
                            <td>$<?php echo number_format($nomina['salario_bruto'], 2); ?></td>
                            <td>$<?php echo number_format($nomina['salario_neto'], 2); ?></td>
                            <td class="flex space-x-2">
                                <a href="ver_nomina.php?id=<?php echo $nomina['id_nomina']; ?>" class="btn btn-sm btn-info">
                                    Ver Detalles
                                </a>
                                <?php if ($nomina['pdf_ruta']): ?>
                                <a href="<?php echo '../../' . $nomina['pdf_ruta']; ?>" target="_blank" class="btn btn-sm btn-outline">
                                    Ver PDF
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">No se encontraron registros de nómina</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if ($total_paginas > 1): ?>
    <div class="flex justify-center mt-6">
        <div class="btn-group">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&desde=<?php echo $fecha_desde; ?>&hasta=<?php echo $fecha_hasta; ?>" class="btn">«</a>
            <?php else: ?>
                <button class="btn btn-disabled">«</button>
            <?php endif; ?>
            
            <?php
            // Mostrar máximo 5 páginas
            $inicio = max(1, $pagina_actual - 2);
            $fin = min($total_paginas, $pagina_actual + 2);
            
            for ($i = $inicio; $i <= $fin; $i++):
            ?>
                <a href="?pagina=<?php echo $i; ?>&desde=<?php echo $fecha_desde; ?>&hasta=<?php echo $fecha_hasta; ?>" 
                   class="btn <?php echo ($i == $pagina_actual) ? 'btn-active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&desde=<?php echo $fecha_desde; ?>&hasta=<?php echo $fecha_hasta; ?>" class="btn">»</a>
            <?php else: ?>
                <button class="btn btn-disabled">»</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Resumen -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php
        // Calcular total de nóminas recibidas
        $sql_total = "SELECT COUNT(*) as total, SUM(salario_neto) as suma_neto 
                     FROM NOMINAS 
                     WHERE id_empleado = ?";
        
        // Aplicar los mismos filtros
        $params_total = [$id_empleado];
        $param_types_total = "i";
        
        if (!empty($fecha_desde)) {
            $sql_total .= " AND fecha_pago >= ?";
            $params_total[] = $fecha_desde;
            $param_types_total .= "s";
        }
        
        if (!empty($fecha_hasta)) {
            $sql_total .= " AND fecha_pago <= ?";
            $params_total[] = $fecha_hasta;
            $param_types_total .= "s";
        }
        
        $stmt_total = $conn->prepare($sql_total);
        $stmt_total->bind_param($param_types_total, ...$params_total);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $total_info = $result_total->fetch_assoc();
        $stmt_total->close();
        
        $total_nominas = $total_info['total'];
        $suma_neto = $total_info['suma_neto'] ?? 0;
        ?>
        
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total Nóminas Recibidas</div>
                <div class="stat-value"><?php echo $total_nominas; ?></div>
                <div class="stat-desc">Historial completo</div>
            </div>
        </div>
        
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total Neto Recibido</div>
                <div class="stat-value">$<?php echo number_format($suma_neto, 2); ?></div>
                <div class="stat-desc">Suma de pagos netos</div>
            </div>
        </div>
    </div>
</div>

<?php
// Cerrar conexión
$stmt->close();
$conn->close();

// Incluir el pie de página común
include '../../includes/footer.php';
?> 