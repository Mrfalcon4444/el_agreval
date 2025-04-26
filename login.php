<?php
$pageTitle = "Iniciar Sesión - El Agreval";
include 'includes/header.php';
?>

<div class="flex justify-center items-center min-h-screen bg-base-200">
  <div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img src="imagenes/logo.png" alt="Logo de El Agreval" class="w-32 h-auto">
      </div>

      <h2 class="text-2xl font-bold text-center w-full mb-6">Iniciar Sesión</h2>

      <!-- Mensaje de error -->
      <?php if(isset($_GET['error'])): ?>
      <div class="alert alert-error mb-4">
        <?php 
        $error = $_GET['error'];
        if($error == 'credenciales') {
          echo "Correo electrónico o contraseña incorrectos.";
        } elseif($error == 'inactivo') {
          echo "Su cuenta está desactivada. Contacte al administrador.";
        } else {
          echo "Error al iniciar sesión. Intente nuevamente.";
        }
        ?>
      </div>
      <?php endif; ?>

      <!-- Formulario -->
      <form action="auth/procesar_login.php" method="POST" class="space-y-6">
        <!-- Campo de correo -->
        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">Correo Electrónico</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
              </svg>
            </span>
            <input type="email" name="correo" placeholder="correo@ejemplo.com" required class="input input-bordered w-full pl-10" />
          </div>
        </div>

        <!-- Campo de contraseña -->
        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">Contraseña</span>
          </label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"></path>
                <circle cx="16.5" cy="7.5" r=".5" fill="currentColor"></circle>
              </svg>
            </span>
            <input type="password" name="contraseña" id="password" required placeholder="Contraseña" class="input input-bordered w-full pl-10 pr-10" />
            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-.274.857-.68 1.662-1.194 2.382" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Recordarme y enlace -->
        <div class="flex justify-between items-center">
          <label class="label cursor-pointer">
            <input type="checkbox" name="remember" class="checkbox checkbox-primary mr-2" />
            <span class="label-text">Recordarme</span>
          </label>
          <a href="recuperar_password.php" class="text-sm text-primary hover:underline">¿Olvidaste tu contraseña?</a>
        </div>

        <!-- Botón de inicio de sesión -->
        <div class="form-control mt-6">
          <button type="submit" class="btn btn-primary w-full">Iniciar Sesión</button>
        </div>
      </form>
    </div>
  </div>
</div>

      <div class="text-center mt-6 text-sm">
        <p>Si no tienes una cuenta, contacta al administrador del sistema.</p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');

  togglePassword.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.querySelector('svg').classList.toggle('text-gray-400');
    this.querySelector('svg').classList.toggle('text-primary');
  });
});
</script>
      
      

<?php
include 'includes/footer.php';
?>