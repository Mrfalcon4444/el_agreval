<?php
// Iniciar sesión (por si se necesita verificar si el usuario ya está logueado)
session_start();

// Verificar si el usuario ya está logueado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Si el usuario ya está logueado, redirigirlo según su cargo
    if ($_SESSION['cargo'] == 'Administrador') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['cargo'] == 'Administrador de nomina') {
        header("Location: rrhh/dashboard.php");
    }else {
        header("Location: dashboard.php");
    }
    exit();
} else {
    // Si el usuario no está logueado, redirigirlo a la página de login
    header("Location: login.php");
    exit();
}
?>