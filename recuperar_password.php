<?php
$pageTitle = "Recuperar Contraseña - El Agreval";
include 'includes/header.php';

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
?>

<div class="flex justify-center items-center min-h-screen bg-base-200">
  <div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img src="imagenes/logo.png" alt="Logo de El Agreval" class="w-32 h-auto">
      </div>

      <h2 class="text-2xl font-bold text-center w-full mb-6 flex justify-center">Recuperar Contraseña</h2>
      
      <?php if (isset($_GET['mensaje'])): ?>
      <div class="alert <?php echo ($_GET['tipo'] == 'error') ? 'alert-error' : 'alert-success'; ?> mb-4">
        <?php echo htmlspecialchars($_GET['mensaje']); ?>
      </div>
      <?php endif; ?>
      
      <p class="text-center mb-4">Ingresa tu correo electrónico para recibir un enlace de recuperación.</p>
      
      <form action="procesar_recuperacion.php" method="POST" class="space-y-6 flex flex-col items-center" id="recuperacion-form">
        <div class="form-control w-full max-w-xs">
          <label class="label flex justify-center">
            <span class="label-text">Correo Electrónico</span>
          </label>
          <label class="input validator">
            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
              </g>
            </svg>
            <input type="email" name="correo" placeholder="correo@ejemplo.com" required class="grow text-center"/>
          </label>
          <div class="validator-hint hidden text-error text-sm mt-1 text-center">Ingrese un correo electrónico válido</div>
        </div>
        
        <div class="form-control mt-6 w-full max-w-xs">
          <button type="submit" class="btn btn-primary mx-auto w-full" id="submit-btn">Enviar Enlace de Recuperación</button>
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
  const form = document.getElementById('recuperacion-form');
  const submitBtn = document.getElementById('submit-btn');
  const emailInput = form.querySelector('input[name="correo"]');
  const emailHint = form.querySelector('.validator-hint');
  
  // Validación del correo electrónico
  emailInput.addEventListener('blur', function() {
    if (!this.validity.valid) {
      emailHint.classList.remove('hidden');
    } else {
      emailHint.classList.add('hidden');
    }
  });
  
  emailInput.addEventListener('focus', function() {
    emailHint.classList.add('hidden');
  });
  
  // Prevenir múltiples envíos del formulario
  form.addEventListener('submit', function(e) {
    submitBtn.disabled = true;
    submitBtn.textContent = "Enviando...";
  });
});
</script>

<?php
include 'includes/footer.php';
?>