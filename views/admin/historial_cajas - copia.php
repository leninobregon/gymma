<?php
session_start();
// SEGURIDAD: Solo ADMIN puede ver esto
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

require_once "../../config/Database.php";
$db = (new Database())->getConnection();

$fechaI = $_GET['desde'] ?? null;
$fechaF = $_GET['hasta'] ?? null;

// Consulta de todas las cajas con el nombre del usuario
$sql = "SELECT c.*, u.nombre as nombre_usuario, u.rol as user_rol 
        FROM cajas c 
        JOIN usuarios u ON c.id_usuario = u.id ";

if ($fechaI && $fechaF) {
    $sql .= " WHERE DATE(c.fecha_apertura) BETWEEN '$fechaI' AND '$fechaF' ";
}
$sql .= " ORDER BY c.id DESC";
$stmt = $db->query($sql);
$cierres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totales para las tarjetas superiores
$totalEsperadoG = 0; $totalRealG = 0;
foreach ($cierres as $ci) {
    if ($ci['estado'] === 'CERRADA') {
        $totalEsperadoG += $ci['monto_esperado'];
        $totalRealG += $ci['monto_cierre'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Cajas - Admin</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .filtro-container { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap; }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card-stat { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-top: 5px solid #3498db; }
        .badge-rol { font-size: 10px; padding: 2px 6px; border-radius: 4px; color: white; font-weight: bold; margin-left: 5px; }
        .dif-positiva { color: #2ecc71; font-weight: bold; }
        .dif-negativa { color: #e74c3c; font-weight: bold; }
        .status-badge { padding: 4px 8px; border-radius: 20px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <div class="logo"><h2>📂 Historial de Cierres de Caja</h2></div>
        <a href="../dashboard.php" class="btn-accion" style="background:#7f8c8d;">← Volver</a>
    </header>

    <div class="dashboard-wrapper">
        <form method="GET" class="filtro-container">
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size:12px; font-weight:bold;">FECHA INICIO:</label>
                <input type="date" name="desde" value="<?php echo $fechaI; ?>" style="padding:8px; border:1px solid #ddd; border-radius:6px;">
            </div>
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size:12px; font-weight:bold;">FECHA FIN:</label>
                <input type="date" name="hasta" value="<?php echo $fechaF; ?>" style="padding:8px; border:1px solid #ddd; border-radius:6px;">
            </div>
            <button type="submit" class="btn-accion">🔍 FILTRAR CAJAS</button>
            <?php if($fechaI): ?><a href="historial_cajas.php" class="btn-accion" style="background:#e74c3c;">RESTRABLECER</a><?php endif; ?>
        </form>

        <div class="grid-stats">
            <div class="card-stat">
                <h4 style="margin:0; color:#7f8c8d;">SISTEMA (ESPERADO)</h4>
                <h2 style="margin:10px 0; color:#2c3e50;">C$ <?php echo number_format($totalEsperadoG, 2); ?></h2>
                <small>Suma de ventas + apertura</small>
            </div>
            <div class="card-stat" style="border-top-color: #2ecc71;">
                <h4 style="margin:0; color:#7f8c8d;">MONTO REAL (ENTREGADO)</h4>
                <h2 style="margin:10px 0; color:#2c3e50;">C$ <?php echo number_format($totalRealG, 2); ?></h2>
                <small>Total físico en efectivo</small>
            </div>
            <?php $difTotal = $totalRealG - $totalEsperadoG; ?>
            <div class="card-stat" style="border-top-color: <?php echo ($difTotal < 0) ? '#e74c3c' : '#f39c12'; ?>;">
                <h4 style="margin:0; color:#7f8c8d;">DIFERENCIA GLOBAL</h4>
                <h2 style="margin:10px 0; color:<?php echo ($difTotal < 0) ? '#e74c3c' : '#2ecc71'; ?>;">C$ <?php echo number_format($difTotal, 2); ?></h2>
                <small><?php echo ($difTotal < 0) ? 'Faltante de dinero' : 'Sobrante/Cuadrado'; ?></small>
            </div>
        </div>

        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow-x: auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                        <th style="padding:15px; text-align:left;">CAJERO</th>
                        <th style="text-align:left;">APERTURA / CIERRE</th>
                        <th style="text-align:right;">ESPERADO</th>
                        <th style="text-align:right;">CIERRE REAL</th>
                        <th style="text-align:right;">DIFERENCIA</th>
                        <th style="text-align:center;">DETALLE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($cierres) > 0): ?>
                        <?php foreach($cierres as $c): 
                            $dif = $c['monto_cierre'] - $c['monto_esperado'];
                            $isAbierta = ($c['estado'] == 'ABIERTA');
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding:12px;">
                                <strong><?php echo strtoupper($c['nombre_usuario']); ?></strong>
                                <span class="badge-rol" style="background:<?php echo ($c['user_rol'] == 'ADMIN') ? '#e67e22':'#3498db'; ?>;"><?php echo $c['user_rol']; ?></span>
                            </td>
                            <td style="font-size:11px; color:#555;">
                                🛫 <?php echo date('d/m/y H:i', strtotime($c['fecha_apertura'])); ?><br>
                                🛬 <?php echo !$isAbierta ? date('d/m/y H:i', strtotime($c['fecha_cierre'])) : '---'; ?>
                            </td>
                            <td style="text-align:right;">C$ <?php echo number_format($c['monto_esperado'], 2); ?></td>
                            <td style="text-align:right;"><strong>C$ <?php echo number_format($c['monto_cierre'], 2); ?></strong></td>
                            <td style="text-align:right;" class="<?php echo ($dif < 0) ? 'dif-negativa':'dif-positiva'; ?>">
                                <?php echo $isAbierta ? '<span class="status-badge" style="background:#fff3cd; color:#856404;">ABIERTA</span>' : 'C$ '.number_format($dif, 2); ?>
                            </td>
                            <td style="text-align:center;">
                                <a href="detalle_caja.php?id=<?php echo $c['id']; ?>" style="text-decoration:none; font-size:1.3rem;" title="Ver Ventas">👁️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="padding:30px; text-align:center;">No hay registros de caja.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>