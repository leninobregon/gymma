<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

$tema = $_SESSION['tema'] ?? 'default';

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
$buscar_id = $_GET['buscar_id'] ?? null;

$ventas = $reporteObj->getVentas($fechaI, $fechaF);
$resumenCajeros = $reporteObj->getResumenPorCajero($fechaI, $fechaF);

// Filtrar por ID si se buscó
if ($buscar_id) {
    $ventas = array_filter($ventas, function($v) use ($buscar_id) {
        return $v['id'] == $buscar_id;
    });
}

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
        :root { --primary: #27ae60; --danger: #e74c3c; --info: #3498db; --text-main: #333; --text-muted: #666; --bg-body: #f4f4f4; --bg-card: #fff; --border-color: #ddd; --header-bg: #2c3e50; --input-bg: #fff; --input-text: #333; --input-border: #ccc; }
        body.tema-oscuro { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --text-main: #e0e0e0; --text-muted: #aaa; --bg-body: #121212; --bg-card: #1e1e1e; --border-color: #333; --header-bg: #1a1a2e; --input-bg: #2a2a2a; --input-text: #e0e0e0; --input-border: #444; }
        body.tema-darkblue { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --text-main: #e0e0e0; --text-muted: #94a3b8; --bg-body: #0d1b2a; --bg-card: #1b263b; --border-color: #334155; --header-bg: #0d1b2a; --input-bg: #0d1b2a; --input-text: #e0e0e0; --input-border: #334155; }
        body { background-color: var(--bg-body); color: var(--text-main); }
        header { background: var(--header-bg); color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .filtro-container { background: var(--bg-card); padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap; border-left: 5px solid #3498db; border: 1px solid var(--border-color); color: var(--text-main); }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card-stat { background: var(--bg-card); padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-bottom: 4px solid #3498db; position: relative; overflow: hidden; border: 1px solid var(--border-color); color: var(--text-main); }
        .card-stat h2 { margin: 10px 0 5px 0; color: var(--text-main); font-size: 1.8rem; }
        .monto-usd { font-size: 0.9rem; color: var(--primary); font-weight: bold; background: rgba(39, 174, 96, 0.1); padding: 2px 8px; border-radius: 4px; }
        .venta-anulada { background-color: rgba(231, 76, 60, 0.1) !important; color: var(--danger); text-decoration: line-through; opacity: 0.7; }
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .tasa-badge { background:var(--header-bg); color:white; padding:6px 15px; border-radius:20px; font-size:13px; font-weight:bold; }
        table { background: var(--bg-card); color: var(--text-main); }
        th { background: var(--header-bg); color: white; }
        td { border-bottom: 1px solid var(--border-color); }
        input, select { background: var(--input-bg); color: var(--input-text); border: 1px solid var(--input-border); }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-chart-line"></i> Reporte Financiero</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-badge">Tasa: C$ <?= number_format($tasa_cambio, 2) ?></span>
            <a href="../dashboard.php" class="btn-volver gris"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <form method="GET" class="filtro-container">
            <div style="flex:1;">
                <label style="display:block; font-size:11px; font-weight:bold; color:var(--text-muted);">RANGO DE FECHAS</label>
                <div style="display:flex; gap:10px;">
                    <input type="date" name="desde" value="<?= $fechaI ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px; flex:1;">
                    <input type="date" name="hasta" value="<?= $fechaF ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px; flex:1;">
                </div>
            </div>
            <div style="width: 200px;">
                <label style="display:block; font-size:11px; font-weight:bold; color:var(--text-muted);">BUSCAR POR ID</label>
                <input type="number" name="buscar_id" value="<?= $_GET['buscar_id'] ?? '' ?>" placeholder="Ej: #55" style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
            </div>
            <button type="submit" class="btn-accion" style="height:42px;"><i class="fas fa-search"></i> FILTRAR</button>
            <?php if (isset($_GET['buscar_id']) && $_GET['buscar_id']): ?>
                <a href="reportes.php?desde=<?= $fechaI ?>&hasta=<?= $fechaF ?>" class="btn-volver gris"><i class="fas fa-times"></i> Limpiar</a>
            <?php endif; ?>
            <a href="../../controllers/ExportController.php?type=ventas&desde=<?= $fechaI ?>&hasta=<?= $fechaF ?>" 
               class="btn-accion" style="background:#27ae60; text-decoration:none; height:42px; display:flex; align-items:center;"><i class="fas fa-file-excel"></i> EXCEL</a>
            <a href="reporte_ingresos.php" class="btn-accion" style="background:#3498db; text-decoration:none; height:42px; display:flex; align-items:center;"><i class="fas fa-chart-pie"></i> INGRESOS/EGRESOS</a>
            <a href="reporte_cajeros.php" class="btn-accion" style="background:#9b59b6; text-decoration:none; height:42px; display:flex; align-items:center;"><i class="fas fa-user-tie"></i> CAJEROS</a>
            <a href="reporte_socios_vencer.php" class="btn-accion" style="background:#e67e22; text-decoration:none; height:42px; display:flex; align-items:center;"><i class="fas fa-user-clock"></i> SOCIOS POR VENCER</a>
            <a href="reporte_clientes_frecuentes.php" class="btn-accion" style="background:#e74c3c; text-decoration:none; height:42px; display:flex; align-items:center;"><i class="fas fa-star"></i> CLIENTES FRECUENTES</a>
            <a href="gestion_egresos.php" class="btn-accion" style="background:#e74c3c; text-decoration:none; height:42px; display:flex; align-items:center;"><i class="fas fa-file-invoice-dollar"></i> EGRESOS</a>
        </form>

        <div class="grid-stats">
            <div class="card-stat" style="border-bottom-color: var(--primary);">
                <small style="color:var(--text-muted); font-weight:bold;">RECAUDACIÓN TOTAL (NETA)</small>
                <h2>C$ <?= number_format($totalRecaudado, 2) ?></h2>
                <span class="monto-usd">$ <?= number_format($totalRecaudado / $tasa_cambio, 2) ?> USD</span>
            </div>

            <?php foreach($resumenCajeros as $rc): ?>
                <div class="card-stat">
                    <small style="color:var(--text-muted); font-weight:bold;">CAJERO: <?= strtoupper($rc['nombre_persona']) ?></small>
                    <h2>C$ <?= number_format($rc['recaudado'], 2) ?></h2>
                    <span class="monto-usd">$ <?= number_format($rc['recaudado'] / $tasa_cambio, 2) ?></span>
                </div>
            <?php endforeach; ?>

            <div class="card-stat" style="border-bottom-color: var(--danger);">
                <small style="color:var(--text-muted); font-weight:bold;">TOTAL ANULACIONES</small>
                <h2 style="color:var(--danger);">C$ <?= number_format($totalAnulado, 2) ?></h2>
                <span style="color:var(--danger); font-size:12px;">Pérdida/Ajuste en proceso</span>
            </div>
        </div>

        
        <div style="background:white; padding:20px; border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:var(--bg-card); border-bottom:2px solid var(--border-color); color:var(--text-muted);">
                        <th style="padding:15px; text-align:left;">ID</th>
                        <th style="padding:15px; text-align:left;">FECHA / HORA</th>
                        <th style="text-align:left;">CONCEPTO</th>
                        <th style="text-align:left;">CLIENTE</th>
                        <th style="text-align:right;">MONTO (C$)</th>
                        <th style="text-align:right;">EQUIV (USD)</th>
                        <th style="text-align:center;">ESTADO</th>
                        <th style="text-align:center;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($ventas)): ?>
                        <tr><td colspan="8" style="padding:30px; text-align:center; color:gray;">No hay registros para este periodo.</td></tr>
                    <?php endif; ?>

                    <?php foreach($ventas as $v): $esAnulado = ($v['estado'] === 'ANULADO'); ?>
                        <tr class="<?= $esAnulado ? 'venta-anulada' : '' ?>" style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:15px; font-weight:bold; color:var(--primary);">#<?= $v['id'] ?></td>
                            <td style="padding:15px; font-size:0.85rem; color:var(--text-muted);">
                                <?= date('d/m/y', strtotime($v['fecha_venta'])) ?> 
                                <span style="display:block; font-size:10px; color:var(--text-muted);"><?= date('h:i A', strtotime($v['fecha_venta'])) ?></span>
                            </td>
                            <td>
                                <strong style="color:var(--text-main);"><?= $v['concepto'] ?></strong>
                                <span style="display:block; font-size:10px; color:var(--primary); font-weight:bold;">User ID: <?= $v['user_id'] ?></span>
                            </td>
                            <td style="color:var(--text-main);"><?= $v['nombre'] ? $v['nombre']." ".$v['apellido'] : '<span style="color:var(--text-muted);">Venta Directa</span>' ?></td>
                            <td style="text-align:right; color:var(--text-main);"><strong><?= number_format($v['monto_total'], 2) ?></strong></td>
                            <td style="text-align:right; font-weight:bold; color:var(--primary);">$ <?= number_format($v['monto_total'] / $tasa_cambio, 2) ?></td>
                            <td style="text-align:center;">
                                <span class="status-pill" style="background:<?= $esAnulado ? 'rgba(231,76,60,0.2)':'rgba(39,174,96,0.2)' ?>; color:<?= $esAnulado ? 'var(--danger)' : 'var(--primary)' ?>;">
                                    <?= $v['estado'] ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <button onclick="window.open('../caja/imprimir_recibo.php?id=<?= $v['id'] ?>', 'Ticket', 'width=400,height=600')" 
                                        style="background:#3498db; color:white; border:none; padding:6px 10px; border-radius:4px; cursor:pointer; font-size:11px;">
                                    <i class="fas fa-print"></i> IMPRIMIR
                                </button>
                                <?php if (!$esAnulado): ?>
                                    <br><br>
                                    <a href="../../controllers/VentaController.php?action=anular&id=<?= $v['id'] ?>" 
                                       style="background:#e74c3c; color:white; padding:5px 10px; border-radius:4px; text-decoration:none; font-size:11px;"
                                       onclick="return confirm('¿ANULAR esta venta? Esto revertirá el stock y la membresía.')">
                                       <i class="fas fa-ban"></i> ANULAR
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>