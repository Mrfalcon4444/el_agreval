<?php
session_start();

// Verificar que el usuario esté autenticado y tenga permisos
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'rrhh')) {
    die("Acceso denegado");
}

include 'config.php'; // Archivo con la conexión a la base de datos

// Función para calcular el salario neto
function calcular_nomina($salario_base, $bonos, $deducciones) {
    return ($salario_base + $bonos) - $deducciones;
}

// Obtener empleados activos
$sql = "SELECT id, nombre, salario_base, bonos, deducciones FROM empleados WHERE activo = 1";
$result = $conn->query($sql);

$nominas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $salario_neto = calcular_nomina($row['salario_base'], $row['bonos'], $row['deducciones']);
        $nominas[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'salario_neto' => $salario_neto
        ];
    }
}

// Generar archivo de nómina en CSV
if (isset($_POST['generar_nomina'])) {
    $filename = "nomina_" . date('Y-m-d') . ".csv";
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");

    $output = fopen("php://output", "w");
    fputcsv($output, ['ID', 'Nombre', 'Salario Neto']);

    foreach ($nominas as $nomina) {
        fputcsv($output, $nomina);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Nómina</title>
</head>
<body>
    <h2>Gestión de Nómina</h2>
    <form method="post">
        <button type="submit" name="generar_nomina">Generar Nómina</button>
    </form>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Salario Neto</th>
        </tr>
        <?php foreach ($nominas as $nomina): ?>
            <tr>
                <td><?php echo $nomina['id']; ?></td>
                <td><?php echo $nomina['nombre']; ?></td>
                <td><?php echo number_format($nomina['salario_neto'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
