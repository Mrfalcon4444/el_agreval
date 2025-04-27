<?php
// Iniciar sesión
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario está logueado y es RRHH
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    die("Acceso denegado");
}

// Incluir archivo de configuración
require_once '../../config/config.php';

$pageTitle = "Modificar Empleado - El Agreval";

include '../../includes/header.php';

// Verificar que se recibió un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php?mensaje=ID de empleado inválido&tipo=error");
    exit();
}

$id_empleado = intval($_GET['id']);

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$deptos_sql = "SELECT id_departamento, nombre_departamento FROM DEPARTAMENTO ORDER BY nombre_departamento";
$deptos_result = $conn->query($deptos_sql);

$stmt = $conn->prepare("SELECT * FROM EMPLEADOS WHERE id_empleado = ?");
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: dashboard.php?mensaje=Empleado no encontrado&tipo=error");
    exit();
}

$empleado = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y sanitizar datos del formulario
    $cargo = filter_var($_POST['cargo'], FILTER_SANITIZE_STRING);
    $rol = filter_var($_POST['rol'], FILTER_SANITIZE_STRING);
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ? $_POST['fecha_nacimiento'] : NULL;
    $fecha_ingreso = $_POST['fecha_ingreso'] ? $_POST['fecha_ingreso'] : NULL;
    $rfc = filter_var($_POST['rfc'], FILTER_SANITIZE_STRING);
    $estado_activo = isset($_POST['estado_activo']) ? 1 : 0;
    $nss = filter_var($_POST['nss'], FILTER_SANITIZE_STRING);
    $domicilio = filter_var($_POST['domicilio'], FILTER_SANITIZE_STRING);
    $telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_STRING);
    $curp = filter_var($_POST['curp'], FILTER_SANITIZE_STRING);
    $id_departamento = filter_var($_POST['id_departamento'], FILTER_SANITIZE_NUMBER_INT);
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $nickname = filter_var($_POST['nickname'], FILTER_SANITIZE_STRING);
    $nueva_contraseña = $_POST['nueva_contraseña'];
    
    // Verificar si el correo ya existe para otro empleado
    $check_email = $conn->prepare("SELECT id_empleado FROM EMPLEADOS WHERE correo = ? AND id_empleado != ?");
    $check_email->bind_param("si", $correo, $id_empleado);
    $check_email->execute();
    $check_email->store_result();
    
    if ($check_email->num_rows > 0) {
        $error = "Ya existe otro empleado con ese correo electrónico.";
    } else {
        // Preparar la consulta SQL para actualizar
        if (!empty($nueva_contraseña)) {
            // Si se proporcionó una nueva contraseña, actualizarla también
            $contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("UPDATE EMPLEADOS SET 
                                cargo = ?,rol = ?, fecha_nacimiento = ?, fecha_ingreso_escuela = ?, rfc = ?, 
                                estado_activo = ?, nss = ?, domicilio = ?, telefono_personal = ?, 
                                curp = ?, id_departamento = ?, correo = ?, contraseña = ?, nickname = ? 
                                WHERE id_empleado = ?");
            
            $stmt->bind_param("sssssissssisssi", 
                           $cargo, 
                           $rol,
                           $fecha_nacimiento, 
                           $fecha_ingreso, 
                           $rfc, 
                           $estado_activo, 
                           $nss, 
                           $domicilio, 
                           $telefono, 
                           $curp, 
                           $id_departamento, 
                           $correo, 
                           $contraseña_hash, 
                           $nickname, 
                           $id_empleado);
        } else {
            // Si no hay nueva contraseña, actualizar sin cambiar la contraseña
            $stmt = $conn->prepare("UPDATE EMPLEADOS SET 
                                cargo = ?, rol = ?, fecha_nacimiento = ?, fecha_ingreso_escuela = ?, rfc = ?, 
                                estado_activo = ?, nss = ?, domicilio = ?, telefono_personal = ?, 
                                curp = ?, id_departamento = ?, correo = ?, nickname = ? 
                                WHERE id_empleado = ?");
            
            $stmt->bind_param("sssssissssissi", 
                           $cargo, 
                           $rol,
                           $fecha_nacimiento, 
                           $fecha_ingreso, 
                           $rfc, 
                           $estado_activo, 
                           $nss, 
                           $domicilio, 
                           $telefono, 
                           $curp, 
                           $id_departamento, 
                           $correo, 
                           $nickname, 
                           $id_empleado);
        }
        
        // Comprobar si la preparación fue exitosa
        if ($stmt) {
            // Ejecutar la consulta
            if ($stmt->execute()) {
                // Redirigir al dashboard con mensaje de éxito
                header("Location: dashboard.php?mensaje=Empleado actualizado exitosamente&tipo=success");
                exit();
            } else {
                $error = "Error al actualizar el empleado: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $error = "Error en la preparación de la consulta: " . $conn->error;
        }
    }
    
    $check_email->close();
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Modificar Empleado</h1>
        <a href="gestion_empleados/lista_empleados.php" class="btn btn-ghost">
            Volver a la lista de empleados
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-error mb-6">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <!-- Formulario de modificación de empleado -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información básica -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">ID de Empleado</span>
                    </label>
                    <input type="text" value="<?php echo $empleado['id_empleado']; ?>" class="input input-bordered" disabled>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nombre/Nickname</span>
                    </label>
                    <input type="text" name="nickname" value="<?php echo htmlspecialchars($empleado['nickname'] ?? ''); ?>" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Cargo</span>
                    </label>
                    <input type="text" name="cargo" value="<?php echo htmlspecialchars($empleado['cargo'] ?? ''); ?>" class="input input-bordered" required>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Rol</span>
                    </label>
                    <select name="rol" class="select select-bordered" required>
                        <option value="" disabled selected>Seleccione un rol</option>
                        <option value="Empleado">Empleado</option>
                        <option value="RRHH administrador">RRHH administrador</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Correo Electrónico</span>
                    </label>
                    <input type="email" name="correo" value="<?php echo htmlspecialchars($empleado['correo'] ?? ''); ?>" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nueva Contraseña</span>
                    </label>
                    <input type="password" name="nueva_contraseña" class="input input-bordered" 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                           title="Debe contener al menos 8 caracteres, incluyendo una mayúscula, una minúscula y un número">
                    <label class="label">
                        <span class="label-text-alt">Dejar vacío para mantener la contraseña actual</span>
                    </label>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Departamento</span>
                    </label>
                    <select name="id_departamento" class="select select-bordered" required>
                        <option value="" disabled>Seleccione un departamento</option>
                        <?php 
                        // Reiniciar el puntero del resultado
                        $deptos_result->data_seek(0);
                        while($depto = $deptos_result->fetch_assoc()): 
                            $selected = ($depto['id_departamento'] == $empleado['id_departamento']) ? 'selected' : '';
                        ?>
                        <option value="<?php echo $depto['id_departamento']; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($depto['nombre_departamento']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Teléfono Personal</span>
                    </label>
                    <input type="tel" name="telefono" value="<?php echo htmlspecialchars($empleado['telefono_personal'] ?? ''); ?>" class="input input-bordered">
                </div>
                
                <!-- Información adicional -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Fecha de Nacimiento</span>
                    </label>
                    <input type="date" name="fecha_nacimiento" value="<?php echo $empleado['fecha_nacimiento'] ?? ''; ?>" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Fecha de Ingreso</span>
                    </label>
                    <input type="date" name="fecha_ingreso" value="<?php echo $empleado['fecha_ingreso_escuela'] ?? ''; ?>" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">RFC</span>
                    </label>
                    <input type="text" name="rfc" value="<?php echo htmlspecialchars($empleado['rfc'] ?? ''); ?>" class="input input-bordered" maxlength="13">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">NSS</span>
                    </label>
                    <input type="text" name="nss" value="<?php echo htmlspecialchars($empleado['nss'] ?? ''); ?>" class="input input-bordered" maxlength="20">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">CURP</span>
                    </label>
                    <input type="text" name="curp" value="<?php echo htmlspecialchars($empleado['curp'] ?? ''); ?>" class="input input-bordered" maxlength="18">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Estado</span>
                    </label>
                    <label class="cursor-pointer label justify-start">
                        <input type="checkbox" name="estado_activo" class="checkbox checkbox-primary mr-2" <?php echo ($empleado['estado_activo'] == 1) ? 'checked' : ''; ?>>
                        <span class="label-text">Activo</span>
                    </label>
                </div>
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Domicilio</span>
                </label>
                <textarea name="domicilio" class="textarea textarea-bordered h-24"><?php echo htmlspecialchars($empleado['domicilio'] ?? ''); ?></textarea>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="dashboard.php" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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