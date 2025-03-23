<?php
// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivo de configuración
require_once 'config/config.php';

echo "<h1>Corrigiendo acentos en correos electrónicos</h1>";

// Crear conexión a la base de datos
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8");

// Función para eliminar acentos
function eliminarAcentos($texto) {
    $no_permitidas = array("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","Ñ","ü","Ü");
    $permitidas = array("a","e","i","o","u","A","E","I","O","U","n","N","u","U");
    return str_replace($no_permitidas, $permitidas, $texto);
}

// Obtener todos los empleados
$query = "SELECT id_empleado, nickname, correo FROM EMPLEADOS";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<h2>Actualizando correos electrónicos...</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Correo Antiguo</th><th>Correo Nuevo</th><th>Estado</th></tr>";
    
    $actualizados = 0;
    $errores = 0;
    
    while ($row = $result->fetch_assoc()) {
        $id_empleado = $row['id_empleado'];
        $nickname = $row['nickname'];
        $correo_antiguo = $row['correo'];
        
        // Generar nuevo correo sin acentos
        $partes = explode('@', $correo_antiguo);
        if (count($partes) == 2) {
            $usuario = $partes[0];
            $dominio = $partes[1];
            
            $usuario_sin_acentos = eliminarAcentos($usuario);
            $correo_nuevo = $usuario_sin_acentos . '@' . $dominio;
            
            // Verificar si el correo tiene acentos
            if ($correo_nuevo != $correo_antiguo) {
                // Actualizar correo
                $update = $conn->prepare("UPDATE EMPLEADOS SET correo = ? WHERE id_empleado = ?");
                $update->bind_param("si", $correo_nuevo, $id_empleado);
                
                if ($update->execute()) {
                    echo "<tr>";
                    echo "<td>{$id_empleado}</td>";
                    echo "<td>{$nickname}</td>";
                    echo "<td>{$correo_antiguo}</td>";
                    echo "<td>{$correo_nuevo}</td>";
                    echo "<td style='color:green;'>Actualizado</td>";
                    echo "</tr>";
                    $actualizados++;
                } else {
                    echo "<tr>";
                    echo "<td>{$id_empleado}</td>";
                    echo "<td>{$nickname}</td>";
                    echo "<td>{$correo_antiguo}</td>";
                    echo "<td>{$correo_nuevo}</td>";
                    echo "<td style='color:red;'>Error: " . $update->error . "</td>";
                    echo "</tr>";
                    $errores++;
                }
                
                $update->close();
            } else {
                echo "<tr>";
                echo "<td>{$id_empleado}</td>";
                echo "<td>{$nickname}</td>";
                echo "<td>{$correo_antiguo}</td>";
                echo "<td><i>Sin cambios</i></td>";
                echo "<td style='color:blue;'>Correcto</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr>";
            echo "<td>{$id_empleado}</td>";
            echo "<td>{$nickname}</td>";
            echo "<td>{$correo_antiguo}</td>";
            echo "<td><i>Formato inválido</i></td>";
            echo "<td style='color:red;'>Error: Formato de correo inválido</td>";
            echo "</tr>";
            $errores++;
        }
    }
    
    echo "</table>";
    echo "<p>Total correos actualizados: {$actualizados}</p>";
    echo "<p>Total errores: {$errores}</p>";
    
    // Ahora vamos a actualizar las contraseñas para los nombres con acentos
    echo "<h2>Regenerando contraseñas para nombres con acentos...</h2>";
    echo "<p>Las contraseñas se resetearán siguiendo el patrón 'nombre123' sin acentos.</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Nombre (Primer palabra)</th><th>Nueva contraseña</th><th>Estado</th></tr>";
    
    $result = $conn->query("SELECT id_empleado, nickname FROM EMPLEADOS");
    
    $actualizados = 0;
    $errores = 0;
    
    while ($row = $result->fetch_assoc()) {
        $id_empleado = $row['id_empleado'];
        $nickname = $row['nickname'];
        
        // Obtener el primer nombre
        $nombres = explode(' ', $nickname);
        $primer_nombre = $nombres[0];
        
        // Verificar si tiene acentos
        $primer_nombre_sin_acentos = eliminarAcentos($primer_nombre);
        $tiene_acentos = ($primer_nombre != $primer_nombre_sin_acentos);
        
        if ($tiene_acentos) {
            // Generar nueva contraseña
            $nueva_password = $primer_nombre_sin_acentos . '123';
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            
            // Actualizar contraseña
            $update = $conn->prepare("UPDATE EMPLEADOS SET contraseña = ? WHERE id_empleado = ?");
            $update->bind_param("si", $password_hash, $id_empleado);
            
            if ($update->execute()) {
                echo "<tr>";
                echo "<td>{$id_empleado}</td>";
                echo "<td>{$nickname}</td>";
                echo "<td>{$primer_nombre} → {$primer_nombre_sin_acentos}</td>";
                echo "<td>{$nueva_password}</td>";
                echo "<td style='color:green;'>Actualizado</td>";
                echo "</tr>";
                $actualizados++;
            } else {
                echo "<tr>";
                echo "<td>{$id_empleado}</td>";
                echo "<td>{$nickname}</td>";
                echo "<td>{$primer_nombre}</td>";
                echo "<td>{$nueva_password}</td>";
                echo "<td style='color:red;'>Error: " . $update->error . "</td>";
                echo "</tr>";
                $errores++;
            }
            
            $update->close();
        }
    }
    
    echo "</table>";
    echo "<p>Total contraseñas actualizadas: {$actualizados}</p>";
    echo "<p>Total errores: {$errores}</p>";
    
} else {
    echo "<p>No se encontraron empleados en la base de datos.</p>";
}

// Cerrar conexión
$conn->close();

echo "<p><strong>Nota importante:</strong> Por motivos de seguridad, deberías eliminar este archivo después de usarlo.</p>";
?>