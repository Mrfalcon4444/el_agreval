<?php
// Archivo de prueba para simple_correo.php

// Variables necesarias
$asunto = "Prueba de envío de correo";
$contenido = "
<html>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
        <h2 style='color: #2c3e50; text-align: center;'>Prueba de Correo</h2>
        <p>Este es un mensaje de prueba para verificar que el sistema de correo electrónico esté funcionando correctamente.</p>
        <p>Si está recibiendo este correo, significa que la configuración SMTP está correcta.</p>
    </div>
</body>
</html>";

// ID del empleado a quien enviar el correo (debe existir en la base de datos)
$id_empleado = $_GET['id_empleado'] ?? 1; // Valor predeterminado 1, o usar el proporcionado en la URL

// Incluir el archivo simple_correo.php
include_once 'simple_correo.php';
?> 