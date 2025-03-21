<?php
// verifica si esta corriendo localente o el hostinger
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    // local
    $db_host = 'mysql-xxx.hostinger.com'; 
    $db_user = 'u390193918_root'; 
    $db_password = 'Supernova#2025'; 
    $db_name = 'u390193918_Agreval';
} else {
    // hositinger
    $db_host = 'localhost'; 
    $db_user = 'u390193918_root';
    $db_password = 'Supernova#2025';
    $db_name = 'u390193918_Agreval';
}

// Conectar a la base de datos
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Vericiar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
