<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    header("Location: ../../login.php");
    exit();
}

require_once '../../config/config.php';

$pageTitle = "Detalles de Incapacidad - El Agreval";

include '../../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestion_incapacidades.php");
    exit();
}

$id_incapacidad = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Obtener detalles de la incapacidad
$sql = "SELECT i.*, e.nickname, e.cargo, d.nombre_departamento 
        FROM INCAPACIDADES i 
        JOIN EMPLEADOS e ON i.id_empleado = e.id_empleado 
        JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento 
        WHERE i.id_incapacidad = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_incapacidad);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: gestion_incapacidades.php");
    exit();
}

$incapacidad = $result->fetch_assoc();
$stmt->close();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detalles de Incapacidad</h1>
        <div class="flex space-x-4">
            <a href="gestion_incapacidades.php" class="btn btn-ghost">
                Volver a Incapacidades
            </a>
        </div>
    </div>

    <!-- Detalles de la incapacidad -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Empleado</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo htmlspecialchars($incapacidad['nickname']); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Departamento</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo htmlspecialchars($incapacidad['nombre_departamento']); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Cargo</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo htmlspecialchars($incapacidad['cargo']); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Tipo de Incapacidad</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo htmlspecialchars($incapacidad['tipo']); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Fecha de Inicio</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo date('d/m/Y', strtotime($incapacidad['fecha_inicio'])); ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Fecha de Finalización</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo date('d/m/Y', strtotime($incapacidad['fecha_finalizacion'])); ?>
                </div>
            </div>

            <div class="form-control md:col-span-2">
                <label class="label">
                    <span class="label-text font-bold">Documento Justificativo</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo nl2br(htmlspecialchars($incapacidad['documento_justificativo'])); ?>
                </div>
            </div>

            <div class="form-control md:col-span-2">
                <label class="label">
                    <span class="label-text font-bold">Documento Médico</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php if (!empty($incapacidad['documento_justificativo'])): ?>
                        <?php
                        $ext = strtolower(pathinfo($incapacidad['documento_justificativo'], PATHINFO_EXTENSION));
                        $doc_path = dirname(dirname(dirname(__FILE__))) . '/' . $incapacidad['documento_justificativo'];
                        $web_path = '../../' . $incapacidad['documento_justificativo'];
                        
                        if (file_exists($doc_path)):
                            if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                <img src="<?php echo htmlspecialchars($web_path); ?>" 
                                     alt="Documento médico" 
                                     class="max-w-full h-auto rounded-lg shadow-lg">
                            <?php elseif ($ext == 'pdf'): ?>
                                <div class="flex items-center space-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <a href="<?php echo htmlspecialchars($web_path); ?>" 
                                       target="_blank" 
                                       class="link link-primary">
                                        Ver documento PDF
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-red-500">El archivo no se encuentra en el servidor</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-500">No se ha subido ningún documento</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Estado</span>
                </label>
                <div class="p-2">
                    <span class="badge <?php echo $incapacidad['estado'] == 1 ? 'badge-success' : 'badge-error'; ?>">
                        <?php echo $incapacidad['estado'] == 1 ? 'Activa' : 'Finalizada'; ?>
                    </span>
                </div>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-bold">Estado de Aprobación</span>
                </label>
                <div class="p-2">
                    <span class="badge <?php 
                        echo $incapacidad['estado_aprobacion'] == 'aprobada' ? 'badge-success' : 
                            ($incapacidad['estado_aprobacion'] == 'rechazada' ? 'badge-error' : 'badge-warning'); 
                    ?>">
                        <?php 
                        echo $incapacidad['estado_aprobacion'] == 'aprobada' ? 'Aprobada' : 
                            ($incapacidad['estado_aprobacion'] == 'rechazada' ? 'Rechazada' : 'Pendiente'); 
                        ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($incapacidad['comentario_rrhh'])): ?>
            <div class="form-control md:col-span-2">
                <label class="label">
                    <span class="label-text font-bold">Comentarios de RRHH</span>
                </label>
                <div class="p-2 bg-base-200 rounded-lg">
                    <?php echo nl2br(htmlspecialchars($incapacidad['comentario_rrhh'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($incapacidad['estado_aprobacion'] == 'pendiente'): ?>
        <div class="flex justify-end space-x-4 mt-6">
            <a href="aprobar_incapacidad.php?id=<?php echo $id_incapacidad; ?>" class="btn btn-success">
                Aprobar Incapacidad
            </a>
            <a href="rechazar_incapacidad.php?id=<?php echo $id_incapacidad; ?>" class="btn btn-error">
                Rechazar Incapacidad
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
include '../../includes/footer.php';
?> 