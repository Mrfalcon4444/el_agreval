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

      <h2 class="text-2xl font-bold text-center w-full mb-6 flex justify-center">Iniciar Sesión</h2>
      
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
      
      <form action="auth/procesar_login.php" method="POST" class="space-y-6 flex flex-col items-center">
        <!-- Campo de correo -->
        <div class="form-control w-full max-w-xs">
          <label class="label flex justify-center">
            <span class="label-text">Correo Electrónico</span>
          </label>
          <label class="input input-bordered flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 opacity-70">
              <rect width="20" height="16" x="2" y="4" rx="2"></rect>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
            </svg>
            <input type="email" name="correo" placeholder="correo@ejemplo.com" required class="grow" />
          </label>
        </div>
        
        <!-- Campo de contraseña -->
        <div class="form-control w-full max-w-xs">
          <label class="label flex justify-center">
            <span class="label-text">Contraseña</span>
          </label>
          <label class="input input-bordered flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 opacity-70">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <input type="password" name="contraseña" id="password" required placeholder="Contraseña" class="grow" />
            <button type="button" id="togglePassword" class="btn btn-ghost btn-xs">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </label>
        </div>
        
        <!-- Recordarme y enlace -->
        <div class="flex justify-between items-center w-full max-w-xs">
          <label class="label cursor-pointer">
            <input type="checkbox" name="remember" class="checkbox checkbox-primary mr-2" />
            <span class="label-text">Recordarme</span>
          </label>
          <a href="recuperar_password.php" class="text-sm text-primary hover:underline">¿Olvidaste tu contraseña?</a>
        </div>
        
        <!-- Botón de inicio de sesión -->
        <div class="form-control mt-6 w-full max-w-xs">
          <button type="submit" class="btn btn-primary mx-auto w-full">Iniciar Sesión</button>
        </div>
      </form>
      
      <div class="text-center mt-6 text-sm">
        <p>Si no tienes una cuenta, contacta al administrador del sistema.</p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Toggle para mostrar/ocultar contraseña
  const togglePassword = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const eyeIcon = document.getElementById('eyeIcon');

  togglePassword.addEventListener('click', function() {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    // Cambiar el ícono del ojo
    if (type === 'text') {
      eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
      eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
  });
});
</script>

<?php
include 'includes/footer.php';
?>