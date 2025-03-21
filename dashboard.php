<?php

session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/config.php';

$pageTitle = "Bienvenido - El Agreval";

include 'includes/header.php';

$nombre_empleado = htmlspecialchars($_SESSION['nickname']);
?>

<div class="hero min-h-screen bg-base-200">
  <div class="hero-content text-center">
    <div class="max-w-md">
      <h1 class="text-5xl font-bold">Hola</h1>
      <p class="py-6 text-3xl"><?php echo $nombre_empleado; ?></p>
      <a href="logout.php" class="btn btn-ghost">Cerrar Sesión</a>
    </div>
  </div>
</div>

<?php
include 'includes/footer.php';
?>