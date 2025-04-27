<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'RRHH administrador') {
    die("Acceso denegado");
}

$pageTitle = "Panel de Administración - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$total_query = "SELECT COUNT(*) as total FROM EMPLEADOS";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_empleados = $total_row['total'];
$total_paginas = ceil($total_empleados / $registros_por_pagina);

$sql = "SELECT e.id_empleado, e.cargo, e.correo, e.nickname, e.estado_activo, 
               e.telefono_personal, e.fecha_ingreso_escuela, d.nombre_departamento, e.rol
        FROM EMPLEADOS e
        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
        ORDER BY e.id_empleado
        LIMIT $offset, $registros_por_pagina";

$result = $conn->query($sql);
?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado del Dashboard -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Panel de Administración - Gestión de Empleados</h1>
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
    
    <!-- Botón para crear nuevo empleado -->
    <div class="mb-6">
        <a href="crear_empleado.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nuevo Empleado
        </a>
    </div>
    
    <!-- Tabla de empleados -->
    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-lg">
        <table class="table w-full">
            <!-- Encabezado de la tabla -->
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Cargo</th>
                    <th>Rol</th>
                    <th>Departamento</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    $fila_alterna = true;
                    while($row = $result->fetch_assoc()) {
                        $clase_fila = $fila_alterna ? 'bg-base-200' : '';
                        $estado_texto = $row['estado_activo'] ? 'Activo' : 'Inactivo';
                        $estado_clase = $row['estado_activo'] ? 'text-success' : 'text-error';
                ?>
                <tr class="<?php echo $clase_fila; ?>">
                    <th><?php echo $row['id_empleado']; ?></th>
                    <td><?php echo htmlspecialchars($row['nickname']); ?></td>
                    <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                    <td><?php echo htmlspecialchars($row['rol'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_departamento'] ?? 'Sin departamento'); ?></td>
                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                    <td><?php echo htmlspecialchars($row['telefono_personal'] ?? 'No disponible'); ?></td>
                    <td><?php echo $row['fecha_ingreso_escuela'] ? date('d/m/Y', strtotime($row['fecha_ingreso_escuela'])) : 'No disponible'; ?></td>
                    <td class="<?php echo $estado_clase; ?>"><?php echo $estado_texto; ?></td>
                    <td class="flex space-x-2">
                        <a href="modificar_empleado.php?id=<?php echo $row['id_empleado']; ?>" class="btn btn-ghost btn-sm">
                            Editar
                        </a>
                        <?php if ($row['estado_activo']): ?>
                        <a href="cambiar_estado.php?id=<?php echo $row['id_empleado']; ?>&accion=baja" class="btn btn-ghost btn-sm text-error" 
                           onclick="return confirm('¿Estás seguro de dar de baja a este empleado?');">
                            Dar Baja
                        </a>
                        <?php else: ?>
                        <a href="cambiar_estado.php?id=<?php echo $row['id_empleado']; ?>&accion=alta" class="btn btn-ghost btn-sm text-success">
                            Activar
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                        $fila_alterna = !$fila_alterna;
                    }
                } else {
                ?>
                <tr>
                    <td colspan="9" class="text-center py-4">No se encontraron empleados</td>
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
            // Determinar qué páginas mostrar
            $inicio = max(1, $pagina_actual - 2);
            $fin = min($total_paginas, $pagina_actual + 2);
            
            // Mostrar primera página si no está en el rango
            if ($inicio > 1) {
                echo '<a href="?pagina=1" class="btn">1</a>';
                if ($inicio > 2) {
                    echo '<button class="btn btn-disabled">...</button>';
                }
            }
            
            // Mostrar páginas del rango
            for ($i = $inicio; $i <= $fin; $i++) {
                if ($i == $pagina_actual) {
                    echo '<button class="btn btn-active">' . $i . '</button>';
                } else {
                    echo '<a href="?pagina=' . $i . '" class="btn">' . $i . '</a>';
                }
            }
            
            // Mostrar última página si no está en el rango
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
// 
// Incluir archivo de configuración
require_once '../config/config.php';

$pageTitle = "Modificar Empleado - El Agreval";

include '../includes/header.php';

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
        <a href="dashboard.php" class="btn btn-ghost">
            Volver al Dashboard
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
include '../includes/footer.php';
?>