<?php
// Iniciar sesión
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] != 'Empleado') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';

$pageTitle = "Solicitar Vacaciones - El Agreval";

include '../includes/header.php';

$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Obtener datos del empleado actual
$id_empleado = $_SESSION['id_empleado'];

// Verificar si hay un mensaje de error
$error = isset($_GET['error']) ? $_GET['error'] : null;

?>

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Solicitar Vacaciones</h1>
        <a href="dashboard.php" class="btn btn-ghost">
            Volver al Dashboard
        </a>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-error mb-6">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Formulario de solicitud -->
    <div class="bg-base-100 shadow-xl rounded-lg p-6">
        <form method="POST" action="procesar_vacaciones.php" class="space-y-6">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Fecha de Inicio</span>
                </label>
                <input type="date" name="fecha_inicio" class="input input-bordered w-full" required>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Fecha de Finalización</span>
                </label>
                <input type="date" name="fecha_finalizacion" class="input input-bordered w-full" required>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Comentarios (opcional)</span>
                </label>
                <textarea name="comentarios" class="textarea textarea-bordered h-24" placeholder="Comentarios adicionales sobre su solicitud de vacaciones"></textarea>
            </div>

            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary">Solicitar Vacaciones</button>
            </div>
        </form>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?> 