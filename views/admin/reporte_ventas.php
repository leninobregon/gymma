<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php"; // Cargamos la configuración para la tasa

$db = (new Database())->getConnection();
$configObj = new AppConfig($db);
$config = $configObj->obtenerConfig();

// Tasa de cambio oficial del sistema
$tasa = $config['tasa_cambio'] ?? 36.65;
$tema = $_SESSION['tema'] ?? 'default';

// Filtros de fecha
$fecha_inicio = $_GET['desde'] ?? date('Y-m-01');
$fecha_fin = $_GET['hasta'] ?? date('Y-m-t');

$query = "SELECT v.*, s.nombre, s.apellido, u.usuario as cajero 
          FROM ventas v 
          LEFT JOIN socios s ON v.id_socio = s.id 
          LEFT JOIN usuarios u ON v.id_usuario = u.id
          WHERE DATE(v.fecha_venta) BETWEEN ? AND ?
          ORDER BY v.id DESC";

$stmt = $db->prepare($query);
$stmt->execute([$fecha_inicio, $fecha_fin]);
$reporte = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totales
$total_ingresos = 0;
$total_anulado = 0;

foreach($reporte as $r) {
    $estado_actual = $r['estado'] ?? 'COMPLETADO'; 
    if($estado_actual == 'COMPLETADO') {
        $total_ingresos += $r['monto_total'];
    } else {
        $total_anulado += $r['monto_total'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Financiero - <?= $config['nombre_gym'] ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        :root { --primary: #2c3e50; --success: #27ae60; --danger: #e74c3c; }
        .stats-report { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card-resumen { padding: 20px; border-radius: 12px; color: white; text-align: left; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; }
        .card-resumen small { font-weight: bold; opacity: 0.8; text-transform: uppercase; font-size: 0.7rem; }
        .card-resumen h3 { margin: 5px 0; font-size: 1.6rem; }
        .usd-val { font-size: 0.9rem; background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; display: inline-block; }
        
        .tabla-reporte { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        .tabla-reporte th { background: #f8f9fa; color: #7f8c8d; padding: 15px; text-align: left; font-size: 0.75rem; border-bottom: 2px solid #eee; }
        .tabla-reporte td { padding: 15px; border-bottom: 1px solid #eee; color: #333; font-size: 0.9rem; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        
        @media print { .btn-accion, form, header { display: none; } .dashboard-wrapper { margin: 0; padding: 0; } }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-file-invoice-dollar"></i> Reporte Consolidado</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span style="background:#2c3e50; color:white; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:bold;">
                Tasa: C$ <?= number_format($tasa, 2) ?>
            </span>
            <a href="../dashboard.php" class="btn-volver gris">← Volver</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <div class="stat-card" style="margin-bottom: 25px; padding: 20px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div><label style="display:block; font-size:12px; color:gray;">Desde:</label>
                <input type="date" name="desde" value="<?=$fecha_inicio?>" style="padding:8px; border:1px solid #ddd; border-radius:6px;"></div>
                
                <div><label style="display:block; font-size:12px; color:gray;">Hasta:</label>
                <input type="date" name="hasta" value="<?=$fecha_fin?>" style="padding:8px; border:1px solid #ddd; border-radius:6px;"></div>
                
                <button type="submit" class="btn-accion" style="background:var(--primary);"><i class="fas fa-filter"></i> Filtrar Datos</button>
                <button type="button" onclick="window.print()" class="btn-accion" style="background:#34495e;">🖨️ Imprimir Reporte</button>
            </form>
        </div>

        <div class="stats-report">
            <div class="card-resumen" style="background:var(--success);">
                <small>Ingresos Netos (C$)</small>
                <h3>C$ <?= number_format($total_ingresos, 2) ?></h3>
                <span class="usd-val">Equiv. $ <?= number_format($total_ingresos / $tasa, 2) ?> USD</span>
            </div>
            
            <div class="card-resumen" style="background:var(--danger);">
                <small>Total Anulaciones</small>
                <h3>C$ <?= number_format($total_anulado, 2) ?></h3>
                <span class="usd-val">$ <?= number_format($total_anulado / $tasa, 2) ?> USD</span>
            </div>

            <div class="card-resumen" style="background:var(--primary);">
                <small>Volumen de Ventas</small>
                <h3><?= count($reporte) ?></h3>
                <span class="usd-val">Transacciones procesadas</span>
            </div>
        </div>

        <div class="stat-card" style="background:white; border-radius:12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow:hidden;">
            <table class="tabla-reporte">
                <thead>
                    <tr>
                        <th>FECHA / HORA</th>
                        <th>SOCIO / CLIENTE</th>
                        <th>CONCEPTO</th>
                        <th style="text-align:right;">MONTO (C$)</th>
                        <th style="text-align:right;">REF (USD)</th>
                        <th style="text-align:center;">ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reporte as $reg): 
                        $est = $reg['estado'] ?? 'COMPLETADO';
                    ?>
                    <tr style="<?= ($est == 'ANULADO') ? 'background:#fff5f5;' : '' ?>">
                        <td style="color:#666; font-size:0.8rem;"><?= date('d/m/Y H:i', strtotime($reg['fecha_venta'])) ?></td>
                        <td>
                            <strong><?= $reg['nombre'] ? strtoupper($reg['nombre'].' '.$reg['apellido']) : 'CLIENTE GENERAL' ?></strong>
                            <br><small style="color:#999;">Cajero: <?= $reg['cajero'] ?></small>
                        </td>
                        <td><?= $reg['concepto'] ?></td>
                        <td style="text-align:right; font-weight:bold;">C$ <?= number_format($reg['monto_total'], 2) ?></td>
                        <td style="text-align:right; color:var(--success); font-weight:bold;">
                            $ <?= number_format($reg['monto_total'] / $tasa, 2) ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if($est == 'ANULADO'): ?>
                                <span class="badge" style="background:#ffdada; color:#c0392b;">Anulado</span>
                            <?php else: ?>
                                <span class="badge" style="background:#e3fcef; color:#155724;">Completado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($reporte)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:gray;">No se encontraron registros en este rango de fechas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>