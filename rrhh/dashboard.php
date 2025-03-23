<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['cargo'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Panel de Administración - Gestión de Nómina";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$total_query = "SELECT COUNT(*) as total FROM NOMINA";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_nominas = $total_row['total'];
$total_paginas = ceil($total_nominas / $registros_por_pagina);

$sql = "SELECT n.id_nomina, e.nickname AS empleado, e.cargo, n.salario, n.descuentos, 
               n.neto_a_pagar, n.fecha_pago
        FROM NOMINA n
        JOIN EMPLEADOS e ON n.id_empleado = e.id_empleado
        ORDER BY n.fecha_pago DESC
        LIMIT $offset, $registros_por_pagina";

$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado del Dashboard -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Panel de Administración - Gestión de Nómina</h1>
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
    
    <!-- Botón para agregar nueva nómina -->
    <div class="mb-6">
        <a href="crear_nomina.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nueva Nómina
        </a>
    </div>
    
    <!-- Tabla de nómina -->
    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg">
        <table class="table w-full">
            <!-- Encabezado de la tabla -->
            <thead>
                <tr>
                    <th>ID Nómina</th>
                    <th>Empleado</th>
                    <th>Cargo</th>
                    <th>Salario</th>
                    <th>Descuentos</th>
                    <th>Neto a Pagar</th>
                    <th>Fecha de Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    $fila_alterna = true;
                    while($row = $result->fetch_assoc()) {
                        $clase_fila = $fila_alterna ? 'bg-base-200' : '';
                ?>
                <tr class="<?php echo $clase_fila; ?>">
                    <th><?php echo $row['id_nomina']; ?></th>
                    <td><?php echo htmlspecialchars($row['empleado']); ?></td>
                    <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                    <td><?php echo number_format($row['salario'], 2); ?></td>
                    <td><?php echo number_format($row['descuentos'], 2); ?></td>
                    <td><?php echo number_format($row['neto_a_pagar'], 2); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['fecha_pago'])); ?></td>
                    <td class="flex space-x-2">
                        <a href="modificar_nomina.php?id=<?php echo $row['id_nomina']; ?>" class="btn btn-ghost btn-sm">
                            Editar
                        </a>
                        <a href="eliminar_nomina.php?id=<?php echo $row['id_nomina']; ?>" class="btn btn-ghost btn-sm text-error"
                           onclick="return confirm('¿Estás seguro de eliminar este registro de nómina?');">
                            Eliminar
                        </a>
                    </td>
                </tr>
                <?php 
                        $fila_alterna = !$fila_alterna;
                    }
                } else {
                ?>
                <tr>
                    <td colspan="8" class="text-center py-4">No se encontraron registros de nómina</td>
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
            $inicio = max(1, $pagina_actual - 2);
            $fin = min($total_paginas, $pagina_actual + 2);
            
            if ($inicio > 1) {
                echo '<a href="?pagina=1" class="btn">1</a>';
                if ($inicio > 2) {
                    echo '<button class="btn btn-disabled">...</button>';
                }
            }
            
            for ($i = $inicio; $i <= $fin; $i++) {
                if ($i == $pagina_actual) {
                    echo '<button class="btn btn-active">' . $i . '</button>';
                } else {
                    echo '<a href="?pagina=' . $i . '" class="btn">' . $i . '</a>';
                }
            }
            
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
$conn->close();
include '../includes/footer.php';
?>
