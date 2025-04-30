<?php
// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

// Cargar PHPMailer
require_once '../../mail/smtp_config.php';

// Cargar PHPMailer desde la carpeta correcta
$phpmailerPath = dirname(dirname(dirname(__FILE__))) . '/mail/phpmailer/';
if (file_exists($phpmailerPath.'PHPMailer.php')) {
    require_once $phpmailerPath.'PHPMailer.php';
    require_once $phpmailerPath.'SMTP.php';
    require_once $phpmailerPath.'Exception.php';
} else {
    error_log("PHPMailer no encontrado en: " . $phpmailerPath);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si el archivo autoload.php existe
if (!file_exists('../../vendor/autoload.php')) {
    die("Error: No se encontró el archivo vendor/autoload.php. Por favor, ejecute 'composer require mpdf/mpdf' en el directorio raíz del proyecto.");
}

// Incluir la biblioteca FPDF
require_once '../../vendor/autoload.php';

$pageTitle = "Generar Nómina - El Agreval";

// Incluir el encabezado común
include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Variables para el formulario
$id_empleado = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mensaje = '';
$tipo = '';

// Obtener empleados activos con salario base asignado
$sql_empleados = "SELECT e.id_empleado, e.nickname, e.cargo, e.salario_base, d.nombre_departamento
                 FROM EMPLEADOS e
                 LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
                 WHERE e.estado_activo = 1 AND e.rol = 'Empleado' AND e.salario_base > 0
                 ORDER BY e.nickname";
$result_empleados = $conn->query($sql_empleados);

// Si se ha seleccionado un empleado, obtener sus datos
if ($id_empleado > 0) {
    $sql_empleado = "SELECT e.id_empleado, e.nickname, e.cargo, e.salario_base, d.nombre_departamento
                     FROM EMPLEADOS e
                     LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
                     WHERE e.id_empleado = ?";
    $stmt_empleado = $conn->prepare($sql_empleado);
    $stmt_empleado->bind_param("i", $id_empleado);
    $stmt_empleado->execute();
    $result_empleado = $stmt_empleado->get_result();
    $empleado = $result_empleado->fetch_assoc();
    $stmt_empleado->close();
}

// Función para calcular ISR (simulado para este ejemplo)
function calcular_isr($salario_bruto) {
    // Cálculo simplificado del ISR en México
    if ($salario_bruto <= 7735.00) {
        return $salario_bruto * 0.0192; // 1.92%
    } elseif ($salario_bruto <= 65651.07) {
        return $salario_bruto * 0.0640; // 6.40%
    } elseif ($salario_bruto <= 115375.90) {
        return $salario_bruto * 0.1088; // 10.88%
    } elseif ($salario_bruto <= 134119.41) {
        return $salario_bruto * 0.1600; // 16.00%
    } elseif ($salario_bruto <= 160577.65) {
        return $salario_bruto * 0.1792; // 17.92%
    } elseif ($salario_bruto <= 323862.00) {
        return $salario_bruto * 0.2136; // 21.36%
    } elseif ($salario_bruto <= 510451.00) {
        return $salario_bruto * 0.2352; // 23.52%
    } elseif ($salario_bruto <= 974535.03) {
        return $salario_bruto * 0.3000; // 30.00%
    } else {
        return $salario_bruto * 0.3400; // 34.00%
    }
}

// Función para calcular IMSS (simulado para este ejemplo)
function calcular_imss($salario_bruto) {
    // Cálculo simplificado de la cuota IMSS
    return $salario_bruto * 0.0340; // 3.40%
}

// Función para calcular AFORE (simulado para este ejemplo)
function calcular_afore($salario_bruto) {
    // Cálculo simplificado de la aportación al AFORE
    return $salario_bruto * 0.0165; // 1.65%
}

// Función para calcular INFONAVIT (simulado para este ejemplo)
function calcular_infonavit($salario_bruto) {
    // Cálculo simplificado de la aportación al INFONAVIT
    return $salario_bruto * 0.0500; // 5.00%
}

// Procesar el formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_nomina'])) {
    // Validar y obtener los datos del formulario
    $id_empleado = intval($_POST['id_empleado']);
    $dias_trabajados = intval($_POST['dias_trabajados']);
    $horas_extra = intval($_POST['horas_extra']);
    $tarifa_hora_extra = floatval($_POST['tarifa_hora_extra']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $fecha_pago = $_POST['fecha_pago'];
    $otras_deducciones = floatval($_POST['otras_deducciones']);
    
    // Obtener el salario base del empleado
    $sql_salario = "SELECT salario_base FROM EMPLEADOS WHERE id_empleado = ?";
    $stmt_salario = $conn->prepare($sql_salario);
    $stmt_salario->bind_param("i", $id_empleado);
    $stmt_salario->execute();
    $result_salario = $stmt_salario->get_result();
    $salario_base = $result_salario->fetch_assoc()['salario_base'];
    $stmt_salario->close();
    
    // Calcular salario diario
    $salario_diario = $salario_base / 30; // Suponiendo 30 días al mes
    
    // Calcular monto por días trabajados
    $salario_por_dias = $salario_diario * $dias_trabajados;
    
    // Calcular monto por horas extra
    $monto_horas_extra = $horas_extra * $tarifa_hora_extra;
    
    // Calcular salario bruto
    $salario_bruto = $salario_por_dias + $monto_horas_extra;
    
    // Calcular deducciones
    $isr = calcular_isr($salario_bruto);
    $imss = calcular_imss($salario_bruto);
    $afore = calcular_afore($salario_bruto);
    $infonavit = calcular_infonavit($salario_bruto);
    $total_deducciones = $isr + $imss + $afore + $infonavit + $otras_deducciones;
    
    // Calcular salario neto
    $salario_neto = $salario_bruto - $total_deducciones;
    
    // Usar consulta directa en lugar de bind_param debido a problemas persistentes
    $estado_activo = 1;
    $sql_insert = "INSERT INTO NOMINAS (id_empleado, fecha_pago, salario_bruto, impuesto, 
                                        isr, imss, afore, infonavit, otras_deducciones,
                                        salario_neto, fecha_inicio_trabajo, fecha_final_trabajo, 
                                        dias_trabajados, horas_extra, tarifa_hora_extra, 
                                        monto_horas_extra, estado_activo) 
                  VALUES ($id_empleado, '$fecha_pago', $salario_bruto, $total_deducciones,
                         $isr, $imss, $afore, $infonavit, $otras_deducciones,
                         $salario_neto, '$fecha_inicio', '$fecha_fin', 
                         $dias_trabajados, $horas_extra, $tarifa_hora_extra,
                         $monto_horas_extra, $estado_activo)";
    
    // Ejecutar la consulta directamente
    if ($conn->query($sql_insert)) {
        $id_nomina = $conn->insert_id;
        
        // Guardar en historial_nomina usando el mismo enfoque
        $sql_historial = "INSERT INTO HISTORIAL_NOMINA (id_nomina, id_empleado, periodo_inicio, periodo_fin,
                                                      salario_base, dias_trabajados, horas_extra, 
                                                      monto_horas_extra, salario_bruto, isr, imss, 
                                                      afore, infonavit, otras_deducciones, salario_neto)
                         VALUES ($id_nomina, $id_empleado, '$fecha_inicio', '$fecha_fin',
                                $salario_base, $dias_trabajados, $horas_extra, 
                                $monto_horas_extra, $salario_bruto, $isr, $imss, 
                                $afore, $infonavit, $otras_deducciones, $salario_neto)";
        
        if ($conn->query($sql_historial)) {
            // Generar el PDF
            $pdf_path = generar_pdf($id_nomina, $conn);
            
            // Actualizar la ruta del PDF en la nómina
            if ($pdf_path) {
                $pdf_path_escaped = $conn->real_escape_string($pdf_path);
                $sql_update_pdf = "UPDATE NOMINAS SET pdf_ruta = '$pdf_path_escaped' WHERE id_nomina = $id_nomina";
                $conn->query($sql_update_pdf);
            }
            
            // Enviar correo al empleado con la notificación de nómina
            if (enviar_notificacion_nomina($id_empleado, $empleado['nickname'], $empleado['correo'], $fecha_inicio, $fecha_fin, $fecha_pago, $salario_neto, $pdf_path)) {
                $mensaje = "Nómina generada correctamente. <a href='../../$pdf_path' target='_blank' class='underline'>Ver PDF</a>";
                $tipo = "success";
            } else {
                $mensaje = "Error al enviar la notificación de nómina al empleado: " . $conn->error;
                $tipo = "error";
            }
        } else {
            $mensaje = "Error al guardar el historial de nómina: " . $conn->error;
            $tipo = "error";
        }
    } else {
        $mensaje = "Error al generar la nómina: " . $conn->error;
        $tipo = "error";
    }
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
        'margin_footer' => 9,
        'tempDir' => '../../uploads/nominas/pdf/temp' // Directorio temporal personalizado
    ]);
    
    $mpdf->WriteHTML($html);
    try {
        $full_path = "../../" . $pdf_path;
        $mpdf->Output($full_path, \Mpdf\Output\Destination::FILE);
    } catch (Exception $e) {
        // Manejo de errores
        error_log("Error al generar PDF: " . $e->getMessage());
        return false;
    }
    
    return $pdf_path;
}

// Función para enviar correo al empleado con la notificación de nómina
function enviar_notificacion_nomina($id_empleado, $nombre_empleado, $correo_empleado, $fecha_inicio, $fecha_fin, $fecha_pago, $salario_neto, $pdf_path) {
    global $smtp_config;
    
    // Asunto y contenido del correo
    $asunto = 'Notificación de nueva nómina generada';
    
    // Formatear las fechas para mejorar la legibilidad
    $fecha_inicio_formateada = date('d/m/Y', strtotime($fecha_inicio));
    $fecha_fin_formateada = date('d/m/Y', strtotime($fecha_fin));
    $fecha_pago_formateada = date('d/m/Y', strtotime($fecha_pago));
    
    // Crear el contenido del correo
    $contenido = "
    <html>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-bottom: 20px;'>
            <h2 style='color: #2c3e50; text-align: center;'>Nueva Nómina Generada</h2>
            <p>Hola <strong>$nombre_empleado</strong>,</p>
            <p>Te informamos que se ha generado tu nómina correspondiente al periodo del <strong>$fecha_inicio_formateada</strong> al <strong>$fecha_fin_formateada</strong>.</p>
            
            <div style='background-color: #f0f8ff; border-left: 4px solid #3498db; padding: 10px; margin: 15px 0;'>
                <p style='margin: 0;'><strong>Información de tu nómina:</strong></p>
                <ul style='padding-left: 20px;'>
                    <li>Periodo: $fecha_inicio_formateada al $fecha_fin_formateada</li>
                    <li>Fecha de pago: $fecha_pago_formateada</li>
                    <li>Salario neto: $" . number_format($salario_neto, 2) . "</li>
                </ul>
            </div>
            
            <p>Puedes consultar tu nómina completa y el detalle de los conceptos en <a href='https://elagreval.icu' style='color: #3498db; text-decoration: none; font-weight: bold;'>elagreval.icu</a> iniciando sesión con tus credenciales.</p>
            
            <p>Si tienes alguna duda sobre tu nómina, por favor contacta al departamento de Recursos Humanos.</p>
            
            <p style='margin-top: 20px;'>Saludos,<br>El equipo de Recursos Humanos<br>El Agreval</p>
        </div>
        <p style='font-size: 12px; color: #999; text-align: center;'>Este es un correo automático, por favor no responder a esta dirección.</p>
    </body>
    </html>";

    // Enviar el correo electrónico
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = $smtp_config['host'];
        $mail->SMTPAuth = $smtp_config['auth'];
        $mail->Username = $smtp_config['username'];
        $mail->Password = $smtp_config['password'];
        $mail->SMTPSecure = $smtp_config['secure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $smtp_config['port'];
        $mail->CharSet = 'UTF-8';
        
        // Remitente
        $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
        
        // Desactivar depuración SMTP
        $mail->SMTPDebug = 0;
        
        // Destinatario
        $mail->addAddress($correo_empleado, $nombre_empleado);
        
        // Adjuntar PDF de la nómina si existe
        if ($pdf_path && file_exists("../../$pdf_path")) {
            $mail->addAttachment("../../$pdf_path", "Nomina_" . date('Ymd') . ".pdf");
        }
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $contenido;
        $mail->AltBody = strip_tags($contenido);
        
        // Enviar
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar el correo de notificación de nómina: {$mail->ErrorInfo}");
        return false;
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Generar Nueva Nómina</h1>
        <a href="index.php" class="btn btn-sm">Volver</a>
    </div>
    
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo; ?> mb-6">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-base-100 shadow-xl rounded-lg p-6 mb-8">
        <form method="post" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Seleccionar Empleado</span>
                    </label>
                    <select name="id_empleado" class="select select-bordered w-full" required>
                        <option value="">Seleccione un empleado</option>
                        <?php while ($empleado_opt = $result_empleados->fetch_assoc()): ?>
                            <option value="<?php echo $empleado_opt['id_empleado']; ?>"
                                <?php echo ($id_empleado == $empleado_opt['id_empleado']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($empleado_opt['nickname']); ?> - 
                                <?php echo htmlspecialchars($empleado_opt['cargo']); ?> - 
                                Salario: $<?php echo number_format($empleado_opt['salario_base'], 2); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Días Trabajados</span>
                    </label>
                    <input type="number" name="dias_trabajados" class="input input-bordered" min="1" max="31" value="15" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Horas Extra</span>
                    </label>
                    <input type="number" name="horas_extra" class="input input-bordered" min="0" value="0" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Tarifa por Hora Extra (MXN)</span>
                    </label>
                    <input type="number" name="tarifa_hora_extra" class="input input-bordered" min="0" step="0.01" value="100.00" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Fecha Inicio Periodo</span>
                    </label>
                    <input type="date" name="fecha_inicio" class="input input-bordered" value="<?php echo date('Y-m-01'); ?>" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Fecha Fin Periodo</span>
                    </label>
                    <input type="date" name="fecha_fin" class="input input-bordered" value="<?php echo date('Y-m-15'); ?>" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Fecha de Pago</span>
                    </label>
                    <input type="date" name="fecha_pago" class="input input-bordered" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Otras Deducciones (MXN)</span>
                    </label>
                    <input type="number" name="otras_deducciones" class="input input-bordered" min="0" step="0.01" value="0.00" required>
                </div>
            </div>
            
            <div class="alert alert-info mt-6">
                <div class="flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#2196f3" class="w-6 h-6 mx-2">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <label>Se calcularán automáticamente las deducciones de acuerdo a la legislación mexicana: ISR, IMSS, AFORE e INFONAVIT.</label>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="submit" name="generar_nomina" class="btn btn-primary">
                    Generar Nómina
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