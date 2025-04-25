<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Historial de Nóminas - El Agreval";

// Incluir el encabezado común
include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Parámetros de filtrado y paginación
$filtro_empleado = isset($_GET['empleado']) ? intval($_GET['empleado']) : 0;
$fecha_desde = isset($_GET['desde']) ? $_GET['desde'] : '';
$fecha_hasta = isset($_GET['hasta']) ? $_GET['hasta'] : '';
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$registros_por_pagina = 15;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Construir consulta base
$sql_base = "SELECT n.id_nomina, n.fecha_pago, n.salario_bruto, n.salario_neto, 
                    e.nickname, e.id_empleado, n.pdf_ruta 
             FROM NOMINAS n
             JOIN EMPLEADOS e ON n.id_empleado = e.id_empleado
             WHERE 1=1";

$params = [];
$param_types = "";

// Añadir filtros si están definidos
if ($filtro_empleado > 0) {
    $sql_base .= " AND e.id_empleado = ?";
    $params[] = $filtro_empleado;
    $param_types .= "i";
}

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
if (!empty($params)) {
    $stmt_count->bind_param($param_types, ...$params);
}
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

// Obtener la lista de empleados para el filtro
$sql_empleados = "SELECT id_empleado, nickname FROM EMPLEADOS WHERE estado_activo = 1 AND rol = 'Empleado' ORDER BY nickname";
$result_empleados = $conn->query($sql_empleados);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Historial de Nóminas</h1>
        <a href="index.php" class="btn btn-sm">Volver</a>
    </div>
    
    <!-- Filtros -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <form method="get" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Empleado</span>
                </label>
                <select name="empleado" class="select select-bordered w-full">
                    <option value="0">Todos los empleados</option>
                    <?php while ($empleado = $result_empleados->fetch_assoc()): ?>
                        <option value="<?php echo $empleado['id_empleado']; ?>"
                                <?php echo ($filtro_empleado == $empleado['id_empleado']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($empleado['nickname']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Desde</span>
                </label>
                <input type="date" name="desde" class="input input-bordered" value="<?php echo $fecha_desde; ?>">
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Hasta</span>
                </label>
                <input type="date" name="hasta" class="input input-bordered" value="<?php echo $fecha_hasta; ?>">
            </div>
            
            <div class="form-control flex items-end">
                <button type="submit" class="btn btn-primary">
                    Filtrar
                </button>
                <a href="historial_nominas.php" class="btn btn-ghost mt-2">Limpiar filtros</a>
            </div>
        </form>
    </div>
    
    <!-- Tabla de nóminas -->
    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
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
                            <td><?php echo htmlspecialchars($nomina['nickname']); ?></td>
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
                        <td colspan="6" class="text-center py-4">No se encontraron registros de nómina</td>
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
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>&empleado=<?php echo $filtro_empleado; ?>&desde=<?php echo $fecha_desde; ?>&hasta=<?php echo $fecha_hasta; ?>" class="btn">«</a>
            <?php else: ?>
                <button class="btn btn-disabled">«</button>
            <?php endif; ?>
            
            <?php
            // Mostrar máximo 5 páginas
            $inicio = max(1, $pagina_actual - 2);
            $fin = min($total_paginas, $pagina_actual + 2);
            
            for ($i = $inicio; $i <= $fin; $i++):
            ?>
                <a href="?pagina=<?php echo $i; ?>&empleado=<?php echo $filtro_empleado; ?>&desde=<?php echo $fecha_desde; ?>&hasta=<?php echo $fecha_hasta; ?>" 
                   class="btn <?php echo ($i == $pagina_actual) ? 'btn-active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>&empleado=<?php echo $filtro_empleado; ?>&desde=<?php echo $fecha_desde; ?>&hasta=<?php echo $fecha_hasta; ?>" class="btn">»</a>
            <?php else: ?>
                <button class="btn btn-disabled">»</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Estadísticas -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php
        // Calcular total de salarios brutos
        $sql_total_bruto = "SELECT SUM(salario_bruto) as total FROM NOMINAS WHERE 1=1";
        
        // Aplicar los mismos filtros
        $params_total = [];
        $param_types_total = "";
        
        if ($filtro_empleado > 0) {
            $sql_total_bruto .= " AND id_empleado = ?";
            $params_total[] = $filtro_empleado;
            $param_types_total .= "i";
        }
        
        if (!empty($fecha_desde)) {
            $sql_total_bruto .= " AND fecha_pago >= ?";
            $params_total[] = $fecha_desde;
            $param_types_total .= "s";
        }
        
        if (!empty($fecha_hasta)) {
            $sql_total_bruto .= " AND fecha_pago <= ?";
            $params_total[] = $fecha_hasta;
            $param_types_total .= "s";
        }
        
        $stmt_total = $conn->prepare($sql_total_bruto);
        if (!empty($params_total)) {
            $stmt_total->bind_param($param_types_total, ...$params_total);
        }
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $total_bruto = $result_total->fetch_assoc()['total'] ?? 0;
        $stmt_total->close();
        
        // Calcular total de salarios netos
        $sql_total_neto = "SELECT SUM(salario_neto) as total FROM NOMINAS WHERE 1=1";
        
        // Aplicar los mismos filtros
        if ($filtro_empleado > 0) {
            $sql_total_neto .= " AND id_empleado = ?";
        }
        
        if (!empty($fecha_desde)) {
            $sql_total_neto .= " AND fecha_pago >= ?";
        }
        
        if (!empty($fecha_hasta)) {
            $sql_total_neto .= " AND fecha_pago <= ?";
        }
        
        $stmt_total_neto = $conn->prepare($sql_total_neto);
        if (!empty($params_total)) {
            $stmt_total_neto->bind_param($param_types_total, ...$params_total);
        }
        $stmt_total_neto->execute();
        $result_total_neto = $stmt_total_neto->get_result();
        $total_neto = $result_total_neto->fetch_assoc()['total'] ?? 0;
        $stmt_total_neto->close();
        
        // Calcular total de deducciones
        $total_deducciones = $total_bruto - $total_neto;
        ?>
        
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total Salarios Brutos</div>
                <div class="stat-value">$<?php echo number_format($total_bruto, 2); ?></div>
                <div class="stat-desc">Suma de los salarios brutos</div>
            </div>
        </div>
        
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total Salarios Netos</div>
                <div class="stat-value">$<?php echo number_format($total_neto, 2); ?></div>
                <div class="stat-desc">Suma de los salarios netos</div>
            </div>
        </div>
        
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Total Deducciones</div>
                <div class="stat-value">$<?php echo number_format($total_deducciones, 2); ?></div>
                <div class="stat-desc">ISR, IMSS, AFORE, INFONAVIT y otras</div>
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