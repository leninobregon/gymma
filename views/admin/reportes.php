<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

require_once "../../config/Database.php";
require_once "../../classes/Reporte.php";
require_once "../../config/AppConfig.php"; 

$db = (new Database())->getConnection();
$reporteObj = new Reporte($db);
$configObj = new AppConfig($db);
$config = $configObj->obtenerConfig();

// Tasa de cambio desde configuración
$tasa_cambio = $config['tasa_cambio'] ?? 36.65;
$fechaI = $_GET['desde'] ?? null;
$fechaF = $_GET['hasta'] ?? null;

$ventas = $reporteObj->getVentas($fechaI, $fechaF);
$resumenCajeros = $reporteObj->getResumenPorCajero($fechaI, $fechaF);

$totalRecaudado = 0;
$totalAnulado = 0;

foreach ($ventas as $v) {
    if ($v['estado'] !== 'ANULADO') { 
        $totalRecaudado += $v['monto_total']; 
    } else {
        $totalAnulado += $v['monto_total'];
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
        .filtro-container { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap; border-left: 5px solid #3498db; }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card-stat { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-bottom: 4px solid #3498db; position: relative; overflow: hidden; }
        .card-stat h2 { margin: 10px 0 5px 0; color: #2c3e50; font-size: 1.8rem; }
        .monto-usd { font-size: 0.9rem; color: #27ae60; font-weight: bold; background: #eafaf1; padding: 2px 8px; border-radius: 4px; }
        .venta-anulada { background-color: #fff5f5 !important; color: #a94442; text-decoration: line-through; opacity: 0.7; }
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .tasa-badge { background:#2c3e50; color:white; padding:6px 15px; border-radius:20px; font-size:13px; font-weight:bold; }
    </style>
</head>
<body>
    <header>
        <div class="logo"><h2>📊 Reporte Financiero</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-badge">Tasa: C$ <?= number_format($tasa_cambio, 2) ?></span>
            <a href="../dashboard.php" class="btn-accion" style="background:#7f8c8d; text-decoration:none;">← Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <form method="GET" class="filtro-container">
            <div style="flex:1;">
                <label style="display:block; font-size:11px; font-weight:bold; color:#7f8c8d;">RANGO DE FECHAS</label>
                <div style="display:flex; gap:10px;">
                    <input type="date" name="desde" value="<?= $fechaI ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px; flex:1;">
                    <input type="date" name="hasta" value="<?= $fechaF ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px; flex:1;">
                </div>
            </div>
            <button type="submit" class="btn-accion" style="height:42px;">🔍 FILTRAR</button>
            <a href="../../controllers/ExportController.php?type=ventas&desde=<?= $fechaI ?>&hasta=<?= $fechaF ?>" 
               class="btn-accion" style="background:#27ae60; text-decoration:none; height:42px; display:flex; align-items:center;">📗 EXCEL</a>
        </form>

        <div class="grid-stats">
            <div class="card-stat" style="border-bottom-color: #2ecc71;">
                <small style="color:#7f8c8d; font-weight:bold;">RECAUDACIÓN TOTAL (NETA)</small>
                <h2>C$ <?= number_format($totalRecaudado, 2) ?></h2>
                <span class="monto-usd">$ <?= number_format($totalRecaudado / $tasa_cambio, 2) ?> USD</span>
            </div>

            <?php foreach($resumenCajeros as $rc): ?>
                <div class="card-stat">
                    <small style="color:#7f8c8d; font-weight:bold;">CAJERO: <?= strtoupper($rc['nombre_persona']) ?></small>
                    <h2>C$ <?= number_format($rc['recaudado'], 2) ?></h2>
                    <span class="monto-usd">$ <?= number_format($rc['recaudado'] / $tasa_cambio, 2) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="card-stat" style="border-bottom-color: #e74c3c;">
                <small style="color:#7f8c8d; font-weight:bold;">TOTAL ANULACIONES</small>
                <h2 style="color:#e74c3c;">C$ <?= number_format($totalAnulado, 2) ?></h2>
                <span style="color:#c0392b; font-size:12px;">Pérdida/Ajuste en proceso</span>
            </div>
        </div>

        
        <div style="background:white; padding:20px; border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee; color:#7f8c8d;">
                        <th style="padding:15px; text-align:left;">FECHA / HORA</th>
                        <th style="text-align:left;">CONCEPTO</th>
                        <th style="text-align:left;">CLIENTE</th>
                        <th style="text-align:right;">MONTO (C$)</th>
                        <th style="text-align:right;">EQUIV (USD)</th>
                        <th style="text-align:center;">ESTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ventas)): ?>
                        <tr><td colspan="6" style="padding:30px; text-align:center; color:gray;">No hay registros para este periodo.</td></tr>
                    <?php endif; ?>

                    <?php foreach($ventas as $v): $esAnulado = ($v['estado'] === 'ANULADO'); ?>
                        <tr class="<?= $esAnulado ? 'venta-anulada' : '' ?>" style="border-bottom:1px solid #eee;">
                            <td style="padding:15px; font-size:0.85rem; color:#666;">
                                <?= date('d/m/y', strtotime($v['fecha_venta'])) ?> 
                                <span style="display:block; font-size:10px;"><?= date('h:i A', strtotime($v['fecha_venta'])) ?></span>
                            </td>
                            <td>
                                <strong style="color:#2c3e50;"><?= $v['concepto'] ?></strong>
                                <span style="display:block; font-size:10px; color:#3498db; font-weight:bold;">User ID: <?= $v['user_id'] ?></span>
                            </td>
                            <td><?= $v['nombre'] ? $v['nombre']." ".$v['apellido'] : '<span style="color:#bdc3c7;">Venta Directa</span>' ?></td>
                            <td style="text-align:right;"><strong><?= number_format($v['monto_total'], 2) ?></strong></td>
                            <td style="text-align:right; font-weight:bold; color:#27ae60;">$ <?= number_format($v['monto_total'] / $tasa_cambio, 2) ?></td>
                            <td style="text-align:center;">
                                <span class="status-pill" style="background:<?= $esAnulado ? '#fdecea':'#eafaf1' ?>; color:<?= $esAnulado ? '#e74c3c':'#2ecc71' ?>;">
                                    <?= $v['estado'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>