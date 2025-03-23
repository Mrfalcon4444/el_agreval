<?php
// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivo de configuración
require_once 'config.php';

echo "<h1>Cargando datos de ejemplo para escuela privada</h1>";

// Crear conexión a la base de datos
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset
$conn->set_charset("utf8");

// ============================
// AGREGAR DEPARTAMENTOS NUEVOS
// ============================
echo "<h2>Agregando departamentos escolares...</h2>";

$departamentos = [
    ['Dirección General', 'directivo'],
    ['Dirección Académica', 'directivo'],
    ['Recursos Humanos', 'administrativo'],
    ['Secretaría Académica', 'administrativo'],
    ['Contabilidad', 'administrativo'],
    ['Prefectura', 'administrativo'],
    ['Maestros Primaria', 'docente'],
    ['Maestros Secundaria', 'docente'],
    ['Maestros Preparatoria', 'docente'],
    ['Orientación Educativa', 'apoyo'],
    ['Psicopedagogía', 'apoyo'],
    ['Sistemas', 'apoyo'],
    ['Mantenimiento', 'apoyo'],
    ['Cafetería', 'servicios'],
    ['Seguridad', 'servicios']
];

$departamentos_agregados = 0;

foreach ($departamentos as $departamento) {
    $nombre = $departamento[0];
    $tipo = $departamento[1];
    
    // Verificar si ya existe
    $check = $conn->prepare("SELECT id_departamento FROM DEPARTAMENTO WHERE nombre_departamento = ?");
    $check->bind_param("s", $nombre);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows == 0) {
        // No existe, agregar
        $stmt = $conn->prepare("INSERT INTO DEPARTAMENTO (nombre_departamento, tipo) VALUES (?, ?)");
        $stmt->bind_param("ss", $nombre, $tipo);
        
        if ($stmt->execute()) {
            echo "✓ Departamento agregado: $nombre<br>";
            $departamentos_agregados++;
        } else {
            echo "✗ Error al agregar departamento $nombre: " . $stmt->error . "<br>";
        }
        
        $stmt->close();
    } else {
        echo "→ Departamento $nombre ya existe<br>";
    }
    
    $check->close();
}

echo "<p>Total departamentos agregados: $departamentos_agregados</p>";

// ============================
// OBTENER TODOS LOS DEPARTAMENTOS
// ============================
$deptos = [];
$result = $conn->query("SELECT id_departamento, nombre_departamento FROM DEPARTAMENTO");
while ($row = $result->fetch_assoc()) {
    $deptos[$row['nombre_departamento']] = $row['id_departamento'];
}

// ============================
// AGREGAR EMPLEADOS DE EJEMPLO
// ============================
echo "<h2>Agregando personal escolar...</h2>";

// Función para generar correo electrónico basado en nombre
function generarCorreo($nombre) {
    // Separar en nombre y apellidos
    $partes = explode(' ', $nombre);
    $nombre_pila = strtolower($partes[0]);
    
    // Usa el primer apellido o el segundo nombre si no hay más partes
    $apellido = isset($partes[1]) ? strtolower($partes[1]) : '';
    
    // Crear correo
    if (!empty($apellido)) {
        return $nombre_pila . '.' . $apellido . '@elagreval.com';
    } else {
        return $nombre_pila . '@elagreval.com';
    }
}

// Lista de empleados de ejemplo
$empleados = [
    // Directivos y administración
    [
        'nickname' => 'Roberto Fernández',
        'cargo' => 'Director General',
        'departamento' => 'Dirección General',
        'fecha_nacimiento' => '1970-05-15',
        'fecha_ingreso' => '2015-01-10',
        'rfc' => 'FERG700515ABC',
        'curp' => 'FERG700515HDFRBO09',
        'nss' => '12345678901',
        'telefono' => '4771234567',
        'domicilio' => 'Calle Principal 123, Centro'
    ],
    [
        'nickname' => 'María López',
        'cargo' => 'Directora Académica',
        'departamento' => 'Dirección Académica',
        'fecha_nacimiento' => '1975-07-22',
        'fecha_ingreso' => '2017-03-01',
        'rfc' => 'LOMA750722XYZ',
        'curp' => 'LOMA750722MDFPZR02',
        'nss' => '23456789012',
        'telefono' => '4772345678',
        'domicilio' => 'Av. Reforma 456, Jardines'
    ],
    [
        'nickname' => 'Patricia Ramírez',
        'cargo' => 'Jefa de Recursos Humanos',
        'departamento' => 'Recursos Humanos',
        'fecha_nacimiento' => '1980-11-30',
        'fecha_ingreso' => '2018-05-15',
        'rfc' => 'RAMP801130DEF',
        'curp' => 'RAMP801130MDFRTR01',
        'nss' => '34567890123',
        'telefono' => '4773456789',
        'domicilio' => 'Blvd. Torres 789, Las Flores'
    ],
    [
        'nickname' => 'Ana García',
        'cargo' => 'Secretaria Académica',
        'departamento' => 'Secretaría Académica',
        'fecha_nacimiento' => '1985-03-17',
        'fecha_ingreso' => '2019-01-20',
        'rfc' => 'GARA850317GHI',
        'curp' => 'GARA850317MDFRCN06',
        'nss' => '45678901234',
        'telefono' => '4774567890',
        'domicilio' => 'Paseo del Río 234, Arboledas'
    ],
    [
        'nickname' => 'Pedro Martínez',
        'cargo' => 'Contador',
        'departamento' => 'Contabilidad',
        'fecha_nacimiento' => '1982-09-05',
        'fecha_ingreso' => '2019-06-10',
        'rfc' => 'MARP820905JKL',
        'curp' => 'MARP820905HDFRTD07',
        'nss' => '56789012345',
        'telefono' => '4775678901',
        'domicilio' => 'Calle Pinos 567, Bosques'
    ],
    [
        'nickname' => 'Carlos Torres',
        'cargo' => 'Prefecto General',
        'departamento' => 'Prefectura',
        'fecha_nacimiento' => '1978-02-12',
        'fecha_ingreso' => '2018-01-15',
        'rfc' => 'TORC780212MNO',
        'curp' => 'TORC780212HDFRRL03',
        'nss' => '67890123456',
        'telefono' => '4776789012',
        'domicilio' => 'Av. Los Lagos 890, Del Valle'
    ],
    
    // Maestros de Primaria
    [
        'nickname' => 'Luisa Sánchez',
        'cargo' => 'Maestra de 1° Primaria',
        'departamento' => 'Maestros Primaria',
        'fecha_nacimiento' => '1988-06-28',
        'fecha_ingreso' => '2020-08-01',
        'rfc' => 'SANL880628PQR',
        'curp' => 'SANL880628MDFNCS08',
        'nss' => '78901234567',
        'telefono' => '4777890123',
        'domicilio' => 'Calle Educación 123, Magisterial'
    ],
    [
        'nickname' => 'Alejandro Díaz',
        'cargo' => 'Maestro de 2° Primaria',
        'departamento' => 'Maestros Primaria',
        'fecha_nacimiento' => '1990-09-18',
        'fecha_ingreso' => '2020-08-01',
        'rfc' => 'DIAA900918ABC',
        'curp' => 'DIAA900918HDFLZL03',
        'nss' => '78901234568',
        'telefono' => '4777890124',
        'domicilio' => 'Calle Progreso 234, Educación'
    ],
    [
        'nickname' => 'Isabel Gutiérrez',
        'cargo' => 'Maestra de 3° Primaria',
        'departamento' => 'Maestros Primaria',
        'fecha_nacimiento' => '1987-11-12',
        'fecha_ingreso' => '2019-08-01',
        'rfc' => 'GUTI871112DEF',
        'curp' => 'GUTI871112MDFTZS05',
        'nss' => '78901234569',
        'telefono' => '4777890125',
        'domicilio' => 'Av. Conocimiento 345, Saber'
    ],
    [
        'nickname' => 'Fernando Vega',
        'cargo' => 'Maestro de 4° Primaria',
        'departamento' => 'Maestros Primaria',
        'fecha_nacimiento' => '1985-07-25',
        'fecha_ingreso' => '2018-08-01',
        'rfc' => 'VEGF850725GHI',
        'curp' => 'VEGF850725HDFRFR01',
        'nss' => '78901234570',
        'telefono' => '4777890126',
        'domicilio' => 'Calle Aprendizaje 456, Escolar'
    ],
    [
        'nickname' => 'Carmen Morales',
        'cargo' => 'Maestra de 5° Primaria',
        'departamento' => 'Maestros Primaria',
        'fecha_nacimiento' => '1989-03-08',
        'fecha_ingreso' => '2020-08-01',
        'rfc' => 'MORC890308JKL',
        'curp' => 'MORC890308MDFRRR09',
        'nss' => '78901234571',
        'telefono' => '4777890127',
        'domicilio' => 'Blvd. Sabiduría 567, Academia'
    ],
    [
        'nickname' => 'Ricardo Flores',
        'cargo' => 'Maestro de 6° Primaria',
        'departamento' => 'Maestros Primaria',
        'fecha_nacimiento' => '1986-12-19',
        'fecha_ingreso' => '2019-08-01',
        'rfc' => 'FLOR861219MNO',
        'curp' => 'FLOR861219HDFCRC04',
        'nss' => '78901234572',
        'telefono' => '4777890128',
        'domicilio' => 'Av. Ciencia 678, Didáctica'
    ],
    
    // Maestros de Secundaria
    [
        'nickname' => 'Javier Hernández',
        'cargo' => 'Maestro de Matemáticas',
        'departamento' => 'Maestros Secundaria',
        'fecha_nacimiento' => '1983-12-09',
        'fecha_ingreso' => '2019-03-15',
        'rfc' => 'HERJ831209STU',
        'curp' => 'HERJ831209HDFRNV04',
        'nss' => '89012345678',
        'telefono' => '4778901234',
        'domicilio' => 'Blvd. Educativo 456, Moderna'
    ],
    [
        'nickname' => 'Laura Mendoza',
        'cargo' => 'Maestra de Español',
        'departamento' => 'Maestros Secundaria',
        'fecha_nacimiento' => '1984-05-22',
        'fecha_ingreso' => '2019-08-15',
        'rfc' => 'MENL840522PQR',
        'curp' => 'MENL840522MDFNDR03',
        'nss' => '89012345679',
        'telefono' => '4778901235',
        'domicilio' => 'Calle Literatura 234, Letras'
    ],
    [
        'nickname' => 'Antonio Jiménez',
        'cargo' => 'Maestro de Ciencias',
        'departamento' => 'Maestros Secundaria',
        'fecha_nacimiento' => '1982-02-15',
        'fecha_ingreso' => '2018-08-15',
        'rfc' => 'JIMA820215STU',
        'curp' => 'JIMA820215HDFRNT08',
        'nss' => '89012345680',
        'telefono' => '4778901236',
        'domicilio' => 'Av. Cientifica 345, Laboratorio'
    ],
    [
        'nickname' => 'Verónica Ortiz',
        'cargo' => 'Maestra de Historia',
        'departamento' => 'Maestros Secundaria',
        'fecha_nacimiento' => '1986-09-12',
        'fecha_ingreso' => '2020-08-15',
        'rfc' => 'ORTV860912VWX',
        'curp' => 'ORTV860912MDFRR09',
        'nss' => '89012345681',
        'telefono' => '4778901237',
        'domicilio' => 'Calle Memoria 456, Pasado'
    ],
    [
        'nickname' => 'Óscar Vargas',
        'cargo' => 'Maestro de Educación Física',
        'departamento' => 'Maestros Secundaria',
        'fecha_nacimiento' => '1988-11-28',
        'fecha_ingreso' => '2020-08-15',
        'rfc' => 'VARO881128YZA',
        'curp' => 'VARO881128HDFRRK07',
        'nss' => '89012345682',
        'telefono' => '4778901238',
        'domicilio' => 'Av. Deportes 567, Olímpica'
    ],
    
    // Maestros de Preparatoria
    [
        'nickname' => 'Miguel González',
        'cargo' => 'Maestro de Química',
        'departamento' => 'Maestros Preparatoria',
        'fecha_nacimiento' => '1979-08-21',
        'fecha_ingreso' => '2018-09-01',
        'rfc' => 'GOGM790821VWX',
        'curp' => 'GOGM790821HDFNNV05',
        'nss' => '90123456789',
        'telefono' => '4779012345',
        'domicilio' => 'Av. Central 789, Bachillerato'
    ],
    [
        'nickname' => 'Silvia Rodríguez',
        'cargo' => 'Maestra de Biología',
        'departamento' => 'Maestros Preparatoria',
        'fecha_nacimiento' => '1980-04-15',
        'fecha_ingreso' => '2018-09-01',
        'rfc' => 'RODS800415ABC',
        'curp' => 'RODS800415MDFDLV02',
        'nss' => '90123456790',
        'telefono' => '4779012346',
        'domicilio' => 'Calle Ciencias 234, Vida'
    ],
    [
        'nickname' => 'Héctor Navarro',
        'cargo' => 'Maestro de Física',
        'departamento' => 'Maestros Preparatoria',
        'fecha_nacimiento' => '1978-06-29',
        'fecha_ingreso' => '2017-09-01',
        'rfc' => 'NAVH780629DEF',
        'curp' => 'NAVH780629HDFVRC06',
        'nss' => '90123456791',
        'telefono' => '4779012347',
        'domicilio' => 'Av. Newton 345, Einstein'
    ],
    [
        'nickname' => 'Gabriela Torres',
        'cargo' => 'Maestra de Literatura',
        'departamento' => 'Maestros Preparatoria',
        'fecha_nacimiento' => '1982-10-05',
        'fecha_ingreso' => '2018-09-01',
        'rfc' => 'TOTG821005GHI',
        'curp' => 'TOTG821005MDFRR08',
        'nss' => '90123456792',
        'telefono' => '4779012348',
        'domicilio' => 'Calle Letras 456, Poesía'
    ],
    [
        'nickname' => 'Roberto Cruz',
        'cargo' => 'Maestro de Matemáticas Avanzadas',
        'departamento' => 'Maestros Preparatoria',
        'fecha_nacimiento' => '1981-12-12',
        'fecha_ingreso' => '2018-09-01',
        'rfc' => 'CRUR811212JKL',
        'curp' => 'CRUR811212HDFRRB09',
        'nss' => '90123456793',
        'telefono' => '4779012349',
        'domicilio' => 'Blvd. Álgebra 567, Cálculo'
    ],
    
    // Personal de Apoyo
    [
        'nickname' => 'Daniela Flores',
        'cargo' => 'Orientadora Educativa',
        'departamento' => 'Orientación Educativa',
        'fecha_nacimiento' => '1986-04-18',
        'fecha_ingreso' => '2020-05-01',
        'rfc' => 'FLOD860418YZA',
        'curp' => 'FLOD860418MDFLDN09',
        'nss' => '01234567890',
        'telefono' => '4770123456',
        'domicilio' => 'Callejón Educativo 234, Panorama'
    ],
    [
        'nickname' => 'Sofía Pérez',
        'cargo' => 'Psicopedagoga',
        'departamento' => 'Psicopedagogía',
        'fecha_nacimiento' => '1987-09-15',
        'fecha_ingreso' => '2019-08-10',
        'rfc' => 'PERS870915ASD',
        'curp' => 'PERS870915MDFRZF07',
        'nss' => '12233445566',
        'telefono' => '4771122334',
        'domicilio' => 'Calle Psicología 123, Desarrollo'
    ],
    [
        'nickname' => 'Eduardo Castro',
        'cargo' => 'Encargado de Sistemas',
        'departamento' => 'Sistemas',
        'fecha_nacimiento' => '1984-05-25',
        'fecha_ingreso' => '2019-01-15',
        'rfc' => 'CAGE840525QWE',
        'curp' => 'CAGE840525HDFSRD03',
        'nss' => '98877665544',
        'telefono' => '4779988776',
        'domicilio' => 'Av. Tecnológica 456, Digital'
    ],
    
    // Personal de Servicios
    [
        'nickname' => 'José Mendoza',
        'cargo' => 'Jefe de Mantenimiento',
        'departamento' => 'Mantenimiento',
        'fecha_nacimiento' => '1975-11-12',
        'fecha_ingreso' => '2017-06-01',
        'rfc' => 'MEJA751112ZXC',
        'curp' => 'MEJA751112HDFNNS09',
        'nss' => '65544332211',
        'telefono' => '4776655443',
        'domicilio' => 'Calle Servicios 789, Laboral'
    ],
    [
        'nickname' => 'Martha Torres',
        'cargo' => 'Encargada de Cafetería',
        'departamento' => 'Cafetería',
        'fecha_nacimiento' => '1980-07-30',
        'fecha_ingreso' => '2018-08-15',
        'rfc' => 'TOMA800730FGH',
        'curp' => 'TOMA800730MDFRRT08',
        'nss' => '11223344556',
        'telefono' => '4771212123',
        'domicilio' => 'Av. Alimentos 123, Gastronómica'
    ],
    [
        'nickname' => 'Raúl Gutiérrez',
        'cargo' => 'Jefe de Seguridad',
        'departamento' => 'Seguridad',
        'fecha_nacimiento' => '1973-03-05',
        'fecha_ingreso' => '2017-05-20',
        'rfc' => 'GURA730305RTY',
        'curp' => 'GURA730305HDFTTL02',
        'nss' => '99887766554',
        'telefono' => '4779876543',
        'domicilio' => 'Calle Vigilancia 456, Protección'
    ]
];

$empleados_agregados = 0;

foreach ($empleados as $empleado) {
    // Generar correo basado en nombre
    $correo = generarCorreo($empleado['nickname']);
    
    // Generar contraseña basada en nombre
    $nombre_primero = explode(' ', $empleado['nickname'])[0];
    $contraseña = $nombre_primero . '123';
    
    // Verificar si el correo ya existe
    $check = $conn->prepare("SELECT id_empleado FROM EMPLEADOS WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows == 0) {
        // No existe, podemos agregarlos
        
        // Hashear la contraseña
        $password_hash = password_hash($contraseña, PASSWORD_DEFAULT);
        
        // Obtener el ID del departamento
        $id_departamento = $deptos[$empleado['departamento']] ?? null;
        
        if ($id_departamento === null) {
            echo "✗ Error: No se encontró el departamento '{$empleado['departamento']}' para {$empleado['nickname']}<br>";
            continue;
        }
        
        // Preparar la consulta
        $stmt = $conn->prepare("INSERT INTO EMPLEADOS 
                               (nickname, cargo, correo, contraseña, id_departamento, 
                                fecha_nacimiento, fecha_ingreso_escuela, rfc, curp, 
                                nss, telefono_personal, domicilio, estado_activo) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        
        $stmt->bind_param("ssssssssssss", 
                $empleado['nickname'], 
                $empleado['cargo'], 
                $correo,
                $password_hash,
                $id_departamento,
                $empleado['fecha_nacimiento'],
                $empleado['fecha_ingreso'],
                $empleado['rfc'],
                $empleado['curp'],
                $empleado['nss'],
                $empleado['telefono'],
                $empleado['domicilio']);
        
        if ($stmt->execute()) {
            echo "✓ Empleado agregado: {$empleado['nickname']} ({$correo})<br>";
            $empleados_agregados++;
        } else {
            echo "✗ Error al agregar empleado {$empleado['nickname']}: " . $stmt->error . "<br>";
        }
        
        $stmt->close();
    } else {
        echo "→ Empleado con correo {$correo} ya existe<br>";
    }
    
    $check->close();
}

echo "<p>Total empleados agregados: $empleados_agregados</p>";

// ============================
// RESUMEN
// ============================
echo "<h2>Resumen final</h2>";
echo "<p>Departamentos agregados: $departamentos_agregados</p>";
echo "<p>Empleados agregados: $empleados_agregados</p>";

// Mostrar todos los departamentos
echo "<h3>Todos los departamentos:</h3>";
$result = $conn->query("SELECT id_departamento, nombre_departamento, tipo FROM DEPARTAMENTO ORDER BY nombre_departamento");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id_departamento']}</td>";
    echo "<td>{$row['nombre_departamento']}</td>";
    echo "<td>{$row['tipo']}</td>";
    echo "</tr>";
}
echo "</table>";

// Mostrar todos los empleados
echo "<h3>Todos los empleados:</h3>";
$result = $conn->query("SELECT e.id_empleado, e.nickname, e.cargo, e.correo, d.nombre_departamento, e.estado_activo 
                        FROM EMPLEADOS e 
                        LEFT JOIN DEPARTAMENTO d ON e.id_departamento = d.id_departamento
                        ORDER BY d.nombre_departamento, e.nickname");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Cargo</th><th>Correo</th><th>Departamento</th><th>Estado</th></tr>";
while ($row = $result->fetch_assoc()) {
    $estado = $row['estado_activo'] ? 'Activo' : 'Inactivo';
    echo "<tr>";
    echo "<td>{$row['id_empleado']}</td>";
    echo "<td>{$row['nickname']}</td>";
    echo "<td>{$row['cargo']}</td>";
    echo "<td>{$row['correo']}</td>";
    echo "<td>{$row['nombre_departamento']}</td>";
    echo "<td>{$estado}</td>";
    echo "</tr>";
}
echo "</table>";

// Cerrar conexión
$conn->close();

echo "<p><strong>Nota importante:</strong> Por motivos de seguridad, deberías eliminar este archivo después de usarlo.</p>";
?>