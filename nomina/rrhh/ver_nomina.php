<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
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

// Verificar que se proporcionó un ID de nómina
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="alert alert-error">ID de nómina no válido</div>';
    echo '<a href="historial_nominas.php" class="btn btn-primary mt-4">Volver al historial</a>';
    echo '</div>';
    include '../../includes/footer.php';
    exit();
}

$id_nomina = intval($_GET['id']);

// Obtener los datos de la nómina
$sql = "SELECT n.*, e.nickname, e.cargo, d.nombre_departamento 
        FROM NOMINAS n 
        JOIN EMPLEADOS e ON n.id_empleado = e.id_empleado
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
        WHERE n.id_nomina = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_nomina);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="alert alert-error">Nómina no encontrada</div>';
    echo '<a href="historial_nominas.php" class="btn btn-primary mt-4">Volver al historial</a>';
    echo '</div>';
    $stmt->close();
    $conn->close();
    include '../../includes/footer.php';
    exit();
}

$nomina = $result->fetch_assoc();
$stmt->close();

// Si se solicita regenerar el PDF
if (isset($_GET['regenerar_pdf']) && $_GET['regenerar_pdf'] == 1) {
    $pdf_path = generar_pdf($id_nomina, $conn);
    
    // Actualizar la ruta del PDF en la nómina
    $sql_update_pdf = "UPDATE NOMINAS SET pdf_ruta = ? WHERE id_nomina = ?";
    $stmt_update_pdf = $conn->prepare($sql_update_pdf);
    $stmt_update_pdf->bind_param("si", $pdf_path, $id_nomina);
    $stmt_update_pdf->execute();
    $stmt_update_pdf->close();
    
    $nomina['pdf_ruta'] = $pdf_path;
    
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="alert alert-success mb-6">PDF regenerado correctamente</div>';
}

// Función para generar el PDF de la nómina
function generar_pdf($id_nomina, $conn) {
    // Usar la biblioteca mPDF para generar el PDF
    $sql = "SELECT n.*, e.nickname, e.cargo, d.nombre_departamento 
            FROM NOMINAS n 
            JOIN EMPLEADOS e ON n.id_empleado = e.id_empleado
            LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
            WHERE n.id_nomina = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_nomina);
    $stmt->execute();
    $result = $stmt->get_result();
    $nomina = $result->fetch_assoc();
    $stmt->close();
    
    // Crear directorio si no existe
    $pdf_dir = "uploads/nominas/pdf";
    if (!file_exists("../../$pdf_dir")) {
        mkdir("../../$pdf_dir", 0777, true);
    }
    
    // Nombre del archivo
    $filename = "nomina_" . $nomina['id_empleado'] . "_" . date('Ymd_His') . ".pdf";
    $pdf_path = "$pdf_dir/$filename";
    
    // Contenido del PDF
    $html = '
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                color: #333;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .title {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .subtitle {
                font-size: 18px;
                margin-bottom: 5px;
            }
            .info-block {
                margin-bottom: 20px;
            }
            .info-row {
                display: flex;
                margin-bottom: 5px;
            }
            .label {
                font-weight: bold;
                width: 200px;
            }
            .value {
                flex: 1;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid #ccc;
            }
            th, td {
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .total-row {
                font-weight: bold;
                background-color: #f9f9f9;
            }
            .footer {
                margin-top: 40px;
                text-align: center;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">RECIBO DE NÓMINA</div>
            <div class="subtitle">El Agreval</div>
            <div>RFC: EAGR123456789</div>
        </div>
        
        <div class="info-block">
            <div class="info-row">
                <div class="label">Nombre del Empleado:</div>
                <div class="value">' . htmlspecialchars($nomina['nickname']) . '</div>
            </div>
            <div class="info-row">
                <div class="label">Cargo:</div>
                <div class="value">' . htmlspecialchars($nomina['cargo']) . '</div>
            </div>
            <div class="info-row">
                <div class="label">Departamento:</div>
                <div class="value">' . htmlspecialchars($nomina['nombre_departamento']) . '</div>
            </div>
            <div class="info-row">
                <div class="label">Periodo de Pago:</div>
                <div class="value">' . date('d/m/Y', strtotime($nomina['fecha_inicio_trabajo'])) . ' al ' . date('d/m/Y', strtotime($nomina['fecha_final_trabajo'])) . '</div>
            </div>
            <div class="info-row">
                <div class="label">Fecha de Pago:</div>
                <div class="value">' . date('d/m/Y', strtotime($nomina['fecha_pago'])) . '</div>
            </div>
        </div>
        
        <table>
            <tr>
                <th colspan="2">Percepciones</th>
            </tr>
            <tr>
                <td>Días Trabajados</td>
                <td>' . $nomina['dias_trabajados'] . ' días</td>
            </tr>
            <tr>
                <td>Salario por Días Trabajados</td>
                <td>$' . number_format(($nomina['salario_bruto'] - $nomina['monto_horas_extra']), 2) . '</td>
            </tr>
            <tr>
                <td>Horas Extra</td>
                <td>' . $nomina['horas_extra'] . ' hrs x $' . number_format($nomina['tarifa_hora_extra'], 2) . ' = $' . number_format($nomina['monto_horas_extra'], 2) . '</td>
            </tr>
            <tr class="total-row">
                <td>Total Percepciones</td>
                <td>$' . number_format($nomina['salario_bruto'], 2) . '</td>
            </tr>
        </table>
        
        <table>
            <tr>
                <th colspan="2">Deducciones</th>
            </tr>
            <tr>
                <td>ISR</td>
                <td>$' . number_format($nomina['isr'], 2) . '</td>
            </tr>
            <tr>
                <td>IMSS</td>
                <td>$' . number_format($nomina['imss'], 2) . '</td>
            </tr>
            <tr>
                <td>AFORE</td>
                <td>$' . number_format($nomina['afore'], 2) . '</td>
            </tr>
            <tr>
                <td>INFONAVIT</td>
                <td>$' . number_format($nomina['infonavit'], 2) . '</td>
            </tr>
            <tr>
                <td>Otras Deducciones</td>
                <td>$' . number_format($nomina['otras_deducciones'], 2) . '</td>
            </tr>
            <tr class="total-row">
                <td>Total Deducciones</td>
                <td>$' . number_format(($nomina['isr'] + $nomina['imss'] + $nomina['afore'] + $nomina['infonavit'] + $nomina['otras_deducciones']), 2) . '</td>
            </tr>
        </table>
        
        <table>
            <tr>
                <th>Neto a Pagar</th>
                <th>$' . number_format($nomina['salario_neto'], 2) . '</th>
            </tr>
        </table>
        
        <div class="footer">
            <p>Este documento es una representación impresa de un Comprobante Fiscal Digital (CFDI)</p>
            <p>Fecha de emisión: ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </body>
    </html>';
    
    // Crear el archivo PDF
    require_once('../../vendor/autoload.php');
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9
    ]);
    
    $mpdf->WriteHTML($html);
    $mpdf->Output("../../$pdf_path", 'F');
    
    return $pdf_path;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detalles de Nómina</h1>
        <div class="flex space-x-2">
            <a href="historial_nominas.php" class="btn btn-sm">Volver</a>
            <?php if (!empty($nomina['pdf_ruta'])): ?>
                <a href="../../<?php echo $nomina['pdf_ruta']; ?>" target="_blank" class="btn btn-sm btn-primary">Ver PDF</a>
            <?php endif; ?>
            <a href="?id=<?php echo $id_nomina; ?>&regenerar_pdf=1" class="btn btn-sm btn-secondary">Regenerar PDF</a>
        </div>
    </div>
    
    <!-- Información del empleado -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Información del Empleado</h2>
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
                <div class="stat-desc">Monto a pagar</div>
            </div>
        </div>
    </div>
</div>

<?php
// Cerrar conexión
$conn->close();

// Incluir el pie de página común
include '../../includes/footer.php';
?> 