<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Sistema de Nómina - El Agreval";

// Incluir el encabezado común
include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Contar nóminas procesadas en el mes actual
$inicio_mes = date('Y-m-01');
$fin_mes = date('Y-m-t');
$sql_nominas_mes = "SELECT COUNT(*) as total FROM NOMINAS WHERE fecha_pago BETWEEN ? AND ?";
$stmt = $conn->prepare($sql_nominas_mes);
$stmt->bind_param("ss", $inicio_mes, $fin_mes);
$stmt->execute();
$result = $stmt->get_result();
$nominas_mes = $result->fetch_assoc()['total'];
$stmt->close();

// Obtener información de las últimas nóminas procesadas
$sql_ultimas = "SELECT n.id_nomina, n.fecha_pago, n.salario_bruto, n.salario_neto, 
                      e.nickname, e.id_empleado, n.pdf_ruta 
               FROM NOMINAS n
               JOIN EMPLEADOS e ON n.id_empleado = e.id_empleado
               ORDER BY n.fecha_pago DESC
               LIMIT 10";
$result_ultimas = $conn->query($sql_ultimas);

// Contar empleados activos con salario base asignado
$sql_empleados = "SELECT COUNT(*) as total FROM EMPLEADOS 
                  WHERE estado_activo = 1 AND rol = 'Empleado' AND salario_base > 0";
$result_empleados = $conn->query($sql_empleados);
$empleados_con_salario = $result_empleados->fetch_assoc()['total'];

// Contar empleados activos sin salario base asignado
$sql_sin_salario = "SELECT COUNT(*) as total FROM EMPLEADOS 
                    WHERE estado_activo = 1 AND rol = 'Empleado' AND (salario_base IS NULL OR salario_base = 0)";
$result_sin_salario = $conn->query($sql_sin_salario);
$empleados_sin_salario = $result_sin_salario->fetch_assoc()['total'];
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Sistema de Gestión de Nómina</h1>
        <div class="flex space-x-2">
            <a href="../../rrhh/dashboard.php" class="btn btn-sm">Volver al Dashboard</a>
        </div>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php 
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'info';
        $clase = ($tipo == 'error') ? 'alert-error' : 'alert-success';
        ?>
        <div class="alert <?php echo $clase; ?> mb-6">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Tarjetas de resumen -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Nóminas procesadas este mes</div>
                <div class="stat-value"><?php echo $nominas_mes; ?></div>
                <div class="stat-desc"><?php echo date('F Y'); ?></div>
            </div>
        </div>
        
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-title">Empleados con salario asignado</div>
                <div class="stat-value"><?php echo $empleados_con_salario; ?></div>
                <div class="stat-desc">De <?php echo $empleados_con_salario + $empleados_sin_salario; ?> empleados activos</div>
            </div>
        </div>
        
        <div class="stats shadow">
            <div class="stat bg-warning text-warning-content">
                <div class="stat-title">Empleados sin salario</div>
                <div class="stat-value"><?php echo $empleados_sin_salario; ?></div>
                <div class="stat-desc">Requieren asignación de salario</div>
            </div>
        </div>
    </div>
    
    <!-- Botones de acción -->
    <div class="flex flex-wrap gap-4 mb-8">
        <a href="gestionar_salarios.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Gestionar Salarios Base
        </a>
        
        <a href="generar_nomina.php" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Generar Nueva Nómina
        </a>
        
        <a href="historial_nominas.php" class="btn btn-accent">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Ver Historial de Nóminas
        </a>
    </div>
    
    <!-- Últimas nóminas procesadas -->
    <h2 class="text-xl font-semibold mb-4">Últimas Nóminas Procesadas</h2>
    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg mb-8">
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
                <?php if ($result_ultimas->num_rows > 0): ?>
                    <?php while ($nomina = $result_ultimas->fetch_assoc()): ?>
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
                        <td colspan="6" class="text-center py-4">No hay nóminas procesadas recientemente</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Información adicional -->
    <div class="alert alert-info">
        <div class="flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#2196f3" class="w-6 h-6 mx-2">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <label>Para empezar, asegúrese de que todos los empleados tengan asignado un salario base.</label>
        </div>
    </div>
</div>

<?php
// Cerrar conexión
$conn->close();

// Incluir el pie de página común
include '../../includes/footer.php';
?> 