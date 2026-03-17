<?php
session_start();
// SEGURIDAD: Solo el ADMIN puede respaldar la base de datos
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    die("Acceso denegado. Solo administradores.");
}

// Configuración de la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "gym_db";

// Nombre del archivo de respaldo con fecha y hora
$fecha = date("Y-m-d_H-i-s");
$nombre_archivo = "respaldo_gym_" . $fecha . ".sql";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener todas las tablas
    $tablas = [];
    $query = $pdo->query("SHOW TABLES");
    while ($row = $query->fetch(PDO::FETCH_NUM)) {
        $tablas[] = $row[0];
    }

    $contenido = "-- RESPALDO GYM MA DB \n-- Generado: " . date("Y-m-d H:i:s") . "\n\n";

    foreach ($tablas as $tabla) {
        // Estructura de la tabla (CREATE TABLE)
        $res = $pdo->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_ASSOC);
        $contenido .= "\n\n" . $res['Create Table'] . ";\n\n";

        // Datos de la tabla (INSERT INTO)
        $datos = $pdo->query("SELECT * FROM `$tabla`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($datos as $fila) {
            $columnas = array_keys($fila);
            $valores = array_values($fila);
            $valores_limpios = array_map(function($v) use ($pdo) {
                return $v === null ? 'NULL' : $pdo->quote($v);
            }, $valores);
            
            $contenido .= "INSERT INTO `$tabla` (`" . implode("`, `", $columnas) . "`) VALUES (" . implode(", ", $valores_limpios) . ");\n";
        }
    }

    // Forzar la descarga del archivo generado
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $nombre_archivo);
    echo $contenido;
    exit();

} catch (PDOException $e) {
    die("❌ Error al crear el respaldo: " . $e->getMessage());
}
?>