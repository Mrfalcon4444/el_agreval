<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Gestión de Salarios - El Agreval";

// Incluir el encabezado común
include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Procesar actualización de salarios si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_salarios'])) {
    foreach ($_POST['salario'] as $id_empleado => $salario) {
        $id_empleado = intval($id_empleado);
        $salario = floatval($salario);
        
        $update_sql = "UPDATE EMPLEADOS SET salario_base = ? WHERE id_empleado = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("di", $salario, $id_empleado);
        $stmt->execute();
        $stmt->close();
    }
    
    $mensaje = "Salarios actualizados correctamente";
    $tipo = "success";
}

// Obtener la lista de empleados activos con rol 'Empleado'
$sql = "SELECT e.id_empleado, e.nickname, e.cargo, e.salario_base, d.nombre_departamento
        FROM EMPLEADOS e
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
        WHERE e.estado_activo = 1 AND e.rol = 'Empleado'
        ORDER BY d.nombre_departamento, e.nickname";

$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestión de Salarios Base</h1>
        <a href="index.php" class="btn btn-sm">Volver al panel</a>
    </div>
    
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo; ?> mb-6">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <form method="post" action="">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Cargo</th>
                            <th>Departamento</th>
                            <th>Salario Base Actual (MXN)</th>
                            <th>Nuevo Salario Base (MXN)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($empleado = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $empleado['id_empleado']; ?></td>
                                    <td><?php echo htmlspecialchars($empleado['nickname']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['cargo']); ?></td>
                                    <td><?php echo htmlspecialchars($empleado['nombre_departamento'] ?? 'No asignado'); ?></td>
                                    <td><?php echo number_format($empleado['salario_base'], 2); ?></td>
                                    <td>
                                        <input type="number" name="salario[<?php echo $empleado['id_empleado']; ?>]" 
                                               value="<?php echo $empleado['salario_base']; ?>"
                                               step="0.01" min="0" class="input input-bordered w-full max-w-xs">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No se encontraron empleados activos</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="submit" name="actualizar_salarios" class="btn btn-primary">
                    Actualizar Salarios
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Cerrar conexión
$conn->close();

// Incluir el pie de página común
include '../../includes/footer.php';
?> 