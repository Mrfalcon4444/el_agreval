<?php
// Iniciar sesión (por si se necesita verificar si el usuario ya está logueado)
session_start();

// Verificar si el usuario ya está logueado
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
} else {
    // Si el usuario no está logueado, redirigirlo a la página de login
    header("Location: login.php");
    exit();
}
?>