<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = "Gestion el Agreval";

include 'includes/header.php';
?>



<?php

require_once 'config/config.php';

// Intentar conectar
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar conexión
if ($conn->connect_error) {
    echo "ERROR: " . $conn->connect_error;
} else {
    echo "CONEXIÓN EXITOSA";
    $conn->close();
}
?>



<?php

include 'includes/footer.php';
?>