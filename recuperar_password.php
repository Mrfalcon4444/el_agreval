<!-- filepath: c:\Users\juana\Desktop\semestre 7\Inge software\el_agreval\recuperar_password.php -->
<?php
$pageTitle = "Recuperar Contraseña - El Agreval";
include 'includes/header.php';
?>

<div class="flex justify-center items-center min-h-screen bg-base-200">
  <div class="card w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
      <h2 class="text-2xl font-bold text-center mb-6">Recuperar Contraseña</h2>
      <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success mb-4">
          <?php echo htmlspecialchars($_GET['mensaje']); ?>
        </div>
      <?php endif; ?>
      <form action="auth/procesar_recuperar_password.php" method="POST" class="space-y-6">
        <div class="form-control w-full max-w-xs">
          <label class="label">
            <span class="label-text">Correo Electrónico</span>
          </label>
          <input type="email" name="correo" placeholder="correo@ejemplo.com" required class="input input-bordered" />
        </div>
        <div class="form-control mt-6">
          <button type="submit" class="btn btn-primary w-full">Enviar Enlace de Recuperación</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
include 'includes/footer.php';
?>