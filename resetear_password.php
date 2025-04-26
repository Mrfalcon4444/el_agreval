<?php
$pageTitle = "Restablecer Contraseña - El Agreval";
include 'includes/header.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// Verificar si ya existe una sesión iniciada
session_start();
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Si el usuario ya está logueado, redirigirlo según su cargo
    if ($_SESSION['rol'] == 'Administrador') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['rol'] == 'RRHH administrador') {
        header("Location: rrhh/dashboard.php");
    } elseif ($_SESSION['rol'] == 'Empleado') {
        header("Location: empleado/dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

// Verificar que se han proporcionado los parámetros necesarios
if (!isset($_GET['token']) || !isset($_GET['id']) || empty($_GET['token']) || !is_numeric($_GET['id'])) {
    header("Location: recuperar_password.php?mensaje=Enlace de recuperación inválido.&tipo=error");
    exit();
}

$token = $_GET['token'];
$id_empleado = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

// Conectar a la base de datos
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    header("Location: recuperar_password.php?mensaje=Error en el servidor. Intente más tarde.&tipo=error");
    exit();
}

$conn->set_charset("utf8");

// Buscar el token en la base de datos
$sql = "SELECT token_hash as token, expires_at as fecha_expiracion FROM password_resets WHERE id_empleado = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_empleado);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si el token existe y no ha expirado
$token_valido = false;
$mensaje_error = null;

if ($result->num_rows === 0) {
    $mensaje_error = "El enlace de recuperación es inválido o ha expirado.";
} else {
    $row = $result->fetch_assoc();
    $fecha_expiracion = new DateTime($row['fecha_expiracion']);
    $ahora = new DateTime();
    
    // Verificar si el token ha expirado
    if ($ahora > $fecha_expiracion) {
        $mensaje_error = "El enlace de recuperación ha expirado. Solicita uno nuevo.";
    } 
    // Verificar si el token coincide
    elseif (!password_verify($token, $row['token'])) {
        $mensaje_error = "El enlace de recuperación es inválido.";
    } else {
        $token_valido = true;
    }
}

$stmt->close();
$conn->close();

// Si el token no es válido, redireccionar con error
if (!$token_valido) {
    header("Location: recuperar_password.php?mensaje=" . urlencode($mensaje_error) . "&tipo=error");
    exit();
}
?>

<div class="flex justify-center items-center min-h-screen bg-base-200">
  <div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img src="imagenes/logo.png" alt="Logo de El Agreval" class="w-32 h-auto">
      </div>

      <h2 class="text-2xl font-bold text-center w-full mb-6 flex justify-center">Restablecer Contraseña</h2>
      
      <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-error mb-4">
        <?php echo htmlspecialchars($_GET['error']); ?>
      </div>
      <?php endif; ?>
      
      <form action="procesar_reseat.php" method="POST" class="space-y-6 flex flex-col items-center" id="reset-form">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="hidden" name="id_empleado" value="<?php echo htmlspecialchars($id_empleado); ?>">
        
        <div class="form-control w-full max-w-xs">
          <label class="label flex justify-center">
            <span class="label-text">Nueva Contraseña</span>
          </label>
          <label class="input validator">
            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"></path>
                <circle cx="16.5" cy="7.5" r=".5" fill="currentColor"></circle>
              </g>
            </svg>
            <input type="password" name="nueva_contraseña" id="nueva_contraseña" required placeholder="Nueva contraseña" class="grow text-center" 
                 minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
          </label>
          <div class="text-sm mt-1 text-center">La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una minúscula y un número.</div>
        </div>
        
        <div class="form-control w-full max-w-xs">
          <label class="label flex justify-center">
            <span class="label-text">Confirmar Contraseña</span>
          </label>
          <label class="input validator">
            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"></path>
                <circle cx="16.5" cy="7.5" r=".5" fill="currentColor"></circle>
              </g>
            </svg>
            <input type="password" name="confirmar_contraseña" id="confirmar_contraseña" required placeholder="Confirmar contraseña" class="grow text-center" />
          </label>
          <div id="password-match-error" class="hidden text-error text-sm mt-1 text-center">Las contraseñas no coinciden</div>
        </div>
        
        <div class="w-full max-w-xs mt-4">
          <div class="progress-container mb-2">
            <div class="progress-bar bg-gray-200 rounded-full h-2.5">
              <div id="password-strength" class="h-2.5 rounded-full" style="width: 0%"></div>
            </div>
          </div>
          <p id="password-strength-text" class="text-xs text-center">Fortaleza de la contraseña</p>
        </div>
        
        <div class="form-control mt-6 w-full max-w-xs">
          <button type="submit" class="btn btn-primary mx-auto w-full" id="submit-btn">Cambiar Contraseña</button>
        </div>
      </form>
      
      <div class="text-center mt-6 text-sm">
        <p><a href="login.php" class="text-primary hover:underline">Volver al inicio de sesión</a></p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('reset-form');
  const passwordInput = document.getElementById('nueva_contraseña');
  const confirmInput = document.getElementById('confirmar_contraseña');
  const submitBtn = document.getElementById('submit-btn');
  const passwordMatchError = document.getElementById('password-match-error');
  const passwordStrength = document.getElementById('password-strength');
  const passwordStrengthText = document.getElementById('password-strength-text');
  
  // Evaluar fuerza de la contraseña
  passwordInput.addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    
    // Longitud mínima
    if (password.length >= 8) strength += 1;
    
    // Contiene letras minúsculas
    if (/[a-z]/.test(password)) strength += 1;
    
    // Contiene letras mayúsculas
    if (/[A-Z]/.test(password)) strength += 1;
    
    // Contiene números
    if (/\d/.test(password)) strength += 1;
    
    // Contiene caracteres especiales
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Actualizar la barra de progreso
    const percent = (strength / 5) * 100;
    passwordStrength.style.width = percent + '%';
    
    // Establecer color según la fuerza
    if (strength <= 2) {
      passwordStrength.className = 'h-2.5 rounded-full bg-red-500';
      passwordStrengthText.textContent = 'Débil';
    } else if (strength <= 3) {
      passwordStrength.className = 'h-2.5 rounded-full bg-yellow-500';
      passwordStrengthText.textContent = 'Media';
    } else {
      passwordStrength.className = 'h-2.5 rounded-full bg-green-500';
      passwordStrengthText.textContent = 'Fuerte';
    }
  });
  
  // Verificar que las contraseñas coincidan
  function checkPasswordsMatch() {
    if (passwordInput.value !== confirmInput.value) {
      passwordMatchError.classList.remove('hidden');
      return false;
    } else {
      passwordMatchError.classList.add('hidden');
      return true;
    }
  }
  
  confirmInput.addEventListener('input', checkPasswordsMatch);
  
  // Validación al enviar el formulario
  form.addEventListener('submit', function(e) {
    // Verificar que las contraseñas coincidan
    if (!checkPasswordsMatch()) {
      e.preventDefault();
      return;
    }
    
    // Verificar que la contraseña cumpla con el patrón requerido
    if (!passwordInput.validity.valid) {
      e.preventDefault();
      return;
    }
    
    // Prevenir múltiples envíos
    submitBtn.disabled = true;
    submitBtn.textContent = "Procesando...";
  });
});
</script>

<?php
include 'includes/footer.php';
?>
