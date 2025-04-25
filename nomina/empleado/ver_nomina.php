<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Detalles de Nómina - El Agreval";

// Incluir el encabezado común
include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Obtener ID del empleado actual
$id_empleado = $_SESSION['id_empleado'];

// Verificar que se proporcionó un ID de nómina
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="alert alert-error">ID de nómina no válido</div>';
    echo '<a href="index.php" class="btn btn-primary mt-4">Volver a mis nóminas</a>';
    echo '</div>';
    include '../../includes/footer.php';
    exit();
}

$id_nomina = intval($_GET['id']);

// Obtener los datos de la nómina asegurándose de que pertenezca al empleado
$sql = "SELECT n.*, e.nickname, e.cargo, d.nombre_departamento 
        FROM NOMINAS n 
        JOIN EMPLEADOS e ON n.id_empleado = e.id_empleado
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
        WHERE n.id_nomina = ? AND n.id_empleado = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_nomina, $id_empleado);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="alert alert-error">Nómina no encontrada o no tienes permisos para verla</div>';
    echo '<a href="index.php" class="btn btn-primary mt-4">Volver a mis nóminas</a>';
    echo '</div>';
    $stmt->close();
    $conn->close();
    include '../../includes/footer.php';
    exit();
}

$nomina = $result->fetch_assoc();
$stmt->close();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detalles de mi Nómina</h1>
        <div class="flex space-x-2">
            <a href="index.php" class="btn btn-sm">Volver</a>
            <?php if (!empty($nomina['pdf_ruta'])): ?>
                <a href="../../<?php echo $nomina['pdf_ruta']; ?>" target="_blank" class="btn btn-sm btn-primary">Ver PDF</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Información del empleado -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Información General</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p><span class="font-bold">Nombre:</span> <?php echo htmlspecialchars($nomina['nickname']); ?></p>
                <p><span class="font-bold">Cargo:</span> <?php echo htmlspecialchars($nomina['cargo']); ?></p>
                <p><span class="font-bold">Departamento:</span> <?php echo htmlspecialchars($nomina['nombre_departamento'] ?? 'No asignado'); ?></p>
            </div>
            <div>
                <p><span class="font-bold">Fecha de Pago:</span> <?php echo date('d/m/Y', strtotime($nomina['fecha_pago'])); ?></p>
                <p><span class="font-bold">Periodo:</span> <?php echo date('d/m/Y', strtotime($nomina['fecha_inicio_trabajo'])); ?> al <?php echo date('d/m/Y', strtotime($nomina['fecha_final_trabajo'])); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Detalles de la nómina -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Percepciones -->
        <div class="bg-base-100 shadow-xl rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Percepciones</h2>
            <table class="table w-full">
                <tbody>
                    <tr>
                        <td>Días Trabajados</td>
                        <td><?php echo $nomina['dias_trabajados']; ?> días</td>
                    </tr>
                    <tr>
                        <td>Salario por Días Trabajados</td>
                        <td>$<?php echo number_format(($nomina['salario_bruto'] - $nomina['monto_horas_extra']), 2); ?></td>
                    </tr>
                    <tr>
                        <td>Horas Extra</td>
                        <td><?php echo $nomina['horas_extra']; ?> hrs x $<?php echo number_format($nomina['tarifa_hora_extra'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Monto por Horas Extra</td>
                        <td>$<?php echo number_format($nomina['monto_horas_extra'], 2); ?></td>
                    </tr>
                    <tr class="font-bold">
                        <td>Total Percepciones</td>
                        <td>$<?php echo number_format($nomina['salario_bruto'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Deducciones -->
        <div class="bg-base-100 shadow-xl rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Deducciones</h2>
            <table class="table w-full">
                <tbody>
                    <tr>
                        <td>ISR (Impuesto Sobre la Renta)</td>
                        <td>$<?php echo number_format($nomina['isr'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>IMSS (Seguro Social)</td>
                        <td>$<?php echo number_format($nomina['imss'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>AFORE (Pensión)</td>
                        <td>$<?php echo number_format($nomina['afore'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>INFONAVIT</td>
                        <td>$<?php echo number_format($nomina['infonavit'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Otras Deducciones</td>
                        <td>$<?php echo number_format($nomina['otras_deducciones'], 2); ?></td>
                    </tr>
                    <tr class="font-bold">
                        <td>Total Deducciones</td>
                        <td>$<?php echo number_format(($nomina['isr'] + $nomina['imss'] + $nomina['afore'] + $nomina['infonavit'] + $nomina['otras_deducciones']), 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Resumen -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Resumen</h2>
        <div class="stats shadow w-full">
            <div class="stat">
                <div class="stat-title">Salario Bruto</div>
                <div class="stat-value text-primary">$<?php echo number_format($nomina['salario_bruto'], 2); ?></div>
            </div>
            
            <div class="stat">
                <div class="stat-title">Total Deducciones</div>
                <div class="stat-value text-secondary">$<?php echo number_format(($nomina['isr'] + $nomina['imss'] + $nomina['afore'] + $nomina['infonavit'] + $nomina['otras_deducciones']), 2); ?></div>
            </div>
            
            <div class="stat">
                <div class="stat-title">Salario Neto</div>
                <div class="stat-value text-accent">$<?php echo number_format($nomina['salario_neto'], 2); ?></div>
                <div class="stat-desc">Monto recibido</div>
            </div>
        </div>
    </div>
    
    <!-- Nota -->
    <div class="alert alert-info">
        <div class="flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#2196f3" class="w-6 h-6 mx-2">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <label>Si tienes alguna duda sobre tu nómina, por favor contacta al departamento de Recursos Humanos.</label>
        </div>
    </div>
</div>

<?php
// Cerrar conexión
$conn->close();

// Incluir el pie de página común
include '../../includes/footer.php';
?> 