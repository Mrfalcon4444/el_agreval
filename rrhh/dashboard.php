<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['cargo'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Gestión de Nómina - El Agreval";

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

$sql = "SELECT * FROM NOMINA ORDER BY fecha_pago DESC LIMIT $offset, $registros_por_pagina";
$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestión de Nómina</h1>
        <div class="flex items-center space-x-4">
            <span class="text-sm">Bienvenido, <?php echo htmlspecialchars($_SESSION['nickname']); ?></span>
            <a href="../logout.php" class="btn btn-sm">Cerrar Sesión</a>
        </div>
    </div>

    <div class="mb-6">
        <a href="crear_nomina.php" class="btn btn-primary">Nueva Nómina</a>
    </div>

    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>ID Nómina</th>
                    <th>Fecha Pago</th>
                    <th>Salario Bruto</th>
                    <th>Impuesto</th>
                    <th>Salario Neto</th>
                    <th>Inicio Trabajo</th>
                    <th>Fin Trabajo</th>
                    <th>ID Empleado</th>
                    <th>Estado</th>
                    <th>Historial</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $estado_texto = $row['estado_activo'] ? 'Activo' : 'Inactivo';
                        $estado_clase = $row['estado_activo'] ? 'text-success' : 'text-error';
                ?>
                <tr>
                    <td><?php echo $row['id_nomina']; ?></td>
                    <td><?php echo htmlspecialchars($row['fecha_pago']); ?></td>
                    <td><?php echo number_format($row['salario_bruto'], 2); ?></td>
                    <td><?php echo number_format($row['impuesto'], 2); ?></td>
                    <td><?php echo number_format($row['salario_neto'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha_inicio_trabajo']); ?></td>
                    <td><?php echo htmlspecialchars($row['fecha_final_trabajo']); ?></td>
                    <td><?php echo htmlspecialchars($row['id_empleado']); ?></td>
                    <td class="<?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></td>
                    <td><?php echo htmlspecialchars($row['historial_nomina']); ?></td>
                    <td class="flex space-x-2">
                        <a href="modificar_nomina.php?id=<?php echo $row['id_nomina']; ?>" class="btn btn-ghost btn-sm">Editar</a>
                        <a href="eliminar_nomina.php?id=<?php echo $row['id_nomina']; ?>" class="btn btn-ghost btn-sm text-error" onclick="return confirm('¿Estás seguro de eliminar esta nómina?');">Eliminar</a>
                    </td>
                </tr>
                <?php } } else { ?>
                <tr>
                    <td colspan="11" class="text-center py-4">No se encontraron registros de nómina</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
