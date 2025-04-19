<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['cargo'] != 'Administrador') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Crear Nuevo Empleado - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$deptos_sql = "SELECT id_departamento, nombre_departamento FROM DEPARTAMENTO ORDER BY nombre_departamento";
$deptos_result = $conn->query($deptos_sql);

// Procesar el formulario si se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y sanitizar datos del formulario
    $cargo = filter_var($_POST['cargo'], FILTER_SANITIZE_STRING);
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
    $contraseña = $_POST['contraseña'];
    $nickname = filter_var($_POST['nickname'], FILTER_SANITIZE_STRING);
    
    // Hashear la contraseña
    $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);
    
    // Validar que el correo no exista ya
    $check_email = $conn->prepare("SELECT id_empleado FROM EMPLEADOS WHERE correo = ?");
    $check_email->bind_param("s", $correo);
    $check_email->execute();
    $check_email->store_result();
    
    if ($check_email->num_rows > 0) {
        $error = "Ya existe un empleado con ese correo electrónico.";
    } else {
        // Preparar la consulta SQL para insertar
        $stmt = $conn->prepare("INSERT INTO EMPLEADOS (cargo, rol, fecha_nacimiento, fecha_ingreso_escuela, rfc, 
                                estado_activo, nss, domicilio, telefono_personal, curp, id_departamento, 
                                correo, contraseña, nickname) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Comprobar si la preparación fue exitosa
        if ($stmt) {
            // Vincular parámetros
            $stmt->bind_param("ssssissssisss", 
                            $cargo, 
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
                            $nickname);
            
            // Ejecutar la consulta
            if ($stmt->execute()) {
                header("Location: dashboard.php?mensaje=Empleado creado exitosamente&tipo=success");
                exit();
            } else {
                $error = "Error al crear el empleado: " . $stmt->error;
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
        <h1 class="text-2xl font-bold">Crear Nuevo Empleado</h1>
        <a href="dashboard.php" class="btn btn-ghost">
            Volver al Dashboard
        </a>
    </div>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-error mb-6">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <!-- Formulario de creación de empleado -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información básica -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Nombre/Nickname</span>
                    </label>
                    <input type="text" name="nickname" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Cargo</span>
                    </label>
                    <input type="text" name="cargo" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Correo Electrónico</span>
                    </label>
                    <input type="email" name="correo" class="input input-bordered" required>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Contraseña</span>
                    </label>
                    <input type="password" name="contraseña" class="input input-bordered" required 
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                           title="Debe contener al menos 8 caracteres, incluyendo una mayúscula, una minúscula y un número">
                    <label class="label">
                        <span class="label-text-alt">Mínimo 8 caracteres, una mayúscula, una minúscula y un número</span>
                    </label>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Departamento</span>
                    </label>
                    <select name="id_departamento" class="select select-bordered" required>
                        <option value="" disabled selected>Seleccione un departamento</option>
                        <?php while($depto = $deptos_result->fetch_assoc()): ?>
                        <option value="<?php echo $depto['id_departamento']; ?>">
                            <?php echo htmlspecialchars($depto['nombre_departamento']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Teléfono Personal</span>
                    </label>
                    <input type="tel" name="telefono" class="input input-bordered">
                </div>
                
                <!-- Información adicional -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Fecha de Nacimiento</span>
                    </label>
                    <input type="date" name="fecha_nacimiento" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Fecha de Ingreso</span>
                    </label>
                    <input type="date" name="fecha_ingreso" class="input input-bordered">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">RFC</span>
                    </label>
                    <input type="text" name="rfc" class="input input-bordered" maxlength="13">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">NSS</span>
                    </label>
                    <input type="text" name="nss" class="input input-bordered" maxlength="20">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">CURP</span>
                    </label>
                    <input type="text" name="curp" class="input input-bordered" maxlength="18">
                </div>
                
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Estado</span>
                    </label>
                    <label class="cursor-pointer label justify-start">
                        <input type="checkbox" name="estado_activo" class="checkbox checkbox-primary mr-2" checked>
                        <span class="label-text">Activo</span>
                    </label>
                </div>
            </div>
            
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Domicilio</span>
                </label>
                <textarea name="domicilio" class="textarea textarea-bordered h-24"></textarea>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="dashboard.php" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn btn-primary">Crear Empleado</button>
            </div>
        </form>
    </div>
</div>

<?php
// Cerrar conexión
$conn->close();

// Incluir el pie de página común
include '../includes/footer.php';
?>