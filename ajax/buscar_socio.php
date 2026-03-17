<?php
require_once "../config/Database.php";
$db = (new Database())->getConnection();

$busqueda = $_GET['consulta'] ?? ''; // Cambiamos el nombre del parámetro

if (empty($busqueda)) {
    echo json_encode(['status' => 'no_encontrado']);
    exit;
}

// Búsqueda por Cédula O Nombre O Apellido usando LIKE
$query = "SELECT s.*, p.nombre_plan, p.duracion_dias 
          FROM socios s 
          LEFT JOIN planes p ON s.id_plan = p.id 
          WHERE s.cedula LIKE ? 
             OR s.nombre LIKE ? 
             OR s.apellido LIKE ? 
          LIMIT 1"; // Traemos el primer resultado más cercano

$termino = "%$busqueda%";
$stmt = $db->prepare($query);
$stmt->execute([$termino, $termino, $termino]);
$socio = $stmt->fetch(PDO::FETCH_ASSOC);

if ($socio) {
    $status = 'vencido';
    $fecha_vence = 'Sin plan';
    $mensaje = "Socio Vencido / Inactivo";

    if (!empty($socio['fecha_ingreso']) && !empty($socio['duracion_dias']) && $socio['fecha_ingreso'] !== '0000-00-00') {
        $fecha_pago = new DateTime($socio['fecha_ingreso']);
        $vence = clone $fecha_pago;
        $vence->modify("+" . $socio['duracion_dias'] . " days");
        $hoy = new DateTime();
        $fecha_vence = $vence->format('d/m/Y');

        if ($hoy < $vence) {
            $diff = $hoy->diff($vence);
            $status = 'vigente';
            $mensaje = "Socio Activo. Vence en {$diff->days} días ($fecha_vence)";
        }
    }

    echo json_encode([
        'status' => $status, 
        'id' => $socio['id'], 
        'nombre' => $socio['nombre'] . " " . $socio['apellido'], 
        'telefono' => $socio['telefono'], 
        'plan' => $socio['nombre_plan'] ?? 'Ninguno',
        'vencimiento' => $fecha_vence, 
        'mensaje' => $mensaje
    ]);
} else {
    echo json_encode(['status' => 'no_encontrado']);
}