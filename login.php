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

      <h2 class="card-title text-2xl font-bold text-center w-full mb-6">Iniciar Sesión</h2>
      
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
      
      <form action="auth/procesar_login.php" method="POST" class="space-y-6">
        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">Correo Electrónico</span>
          </label>
          <label class="input validator">
            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
              </g>
            </svg>
            <input type="email" name="correo" placeholder="correo@ejemplo.com" required class="grow"/>
          </label>
          <div class="validator-hint hidden text-error text-sm mt-1">Ingrese un correo electrónico válido</div>
        </div>
        
        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">Contraseña </span>
            <br>
          </label>
          <label class="input validator">
            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"></path>
                <circle cx="16.5" cy="7.5" r=".5" fill="currentColor"></circle>
              </g>
            </svg>
            <input type="password" name="contraseña" required placeholder="Contraseña" />
          </label>
        </div>
        
        <div class="flex justify-between items-center">
          <label class="label cursor-pointer">
            <input type="checkbox" name="remember" class="checkbox checkbox-primary mr-2" />
            <span class="label-text">Recordarme</span>
          </label>
          <a href="recuperar_password.php" class="text-sm text-primary hover:underline">¿Olvidaste tu contraseña?</a>
        </div>
        
        <div class="form-control mt-6">
          <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
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
  const validators = document.querySelectorAll('.validator input');
  
  validators.forEach(input => {
    input.addEventListener('blur', function() {
      const hint = this.closest('.form-control').querySelector('.validator-hint');
      if (!hint) return;
      
      if (!this.validity.valid) {
        hint.classList.remove('hidden');
      } else {
        hint.classList.add('hidden');
      }
    });
    
    input.addEventListener('focus', function() {
      const hint = this.closest('.form-control').querySelector('.validator-hint');
      if (!hint) return;
      hint.classList.add('hidden');
    });
  });
});
</script>

<?php
include 'includes/footer.php';
?>