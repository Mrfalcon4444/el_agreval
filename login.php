<?php
$pageTitle = "Iniciar Sesión - El Agreval";
include 'includes/header.php';
?>

<div class="flex justify-center items-center min-h-screen bg-base-200">
  <div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body flex flex-col items-center">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img src="imagenes/logo.png" alt="Logo de El Agreval" class="w-32 h-auto">
      </div>

      <h2 class="card-title text-2xl font-bold text-center w-full mb-6">Iniciar Sesión</h2>
      
      <?php if(isset($_GET['error'])): ?>
      <div class="alert alert-error mb-4 text-center">
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
      
      <form action="auth/procesar_login.php" method="POST" class="space-y-6 w-full">
        <div class="form-control w-full flex items-center">
          <label class="label">
            <span class="label-text text-center">Correo Electrónico</span>
          </label>
          <label class="input validator w-full">
            <svg class="h-[1em] opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2.5" fill="none" stroke="currentColor">
                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
              </g>
            </svg>
            <input type="email" name="correo" placeholder="correo@ejemplo.com" required class="grow text-center"/>
          </label>
        </div>

        <div class="form-control w-full flex items-center">
          <label class="label">
            <span class="label-text text-center">Contraseña</span>
          </label>
          <input type="password" name="password" placeholder="********" required class="input input-bordered w-full text-center"/>
        </div>

        <div class="form-control w-full flex items-center">
          <button type="submit" class="btn btn-primary w-full">Iniciar Sesión</button>
        </div>
      </form>
    </div>
  </div>
</div>