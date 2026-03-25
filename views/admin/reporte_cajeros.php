<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Reporte.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';
$reporte = new Reporte($db);

$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$cajeros = $reporte->getRendimientoCajero($desde, $hasta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rendimiento Cajeros - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #27ae60; --danger: #e74c3c; --info: #3498db; --warning: #f39c12; --text-main: #333; --text-muted: #666; --bg-card: #fff; --border-color: #ddd; }
        body.tema-oscuro { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --warning: #f39c12; --text-main: #e0e0e0; --text-muted: #aaa; --bg-card: #1e1e1e; --border-color: #333; }
        body.tema-darkblue { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --warning: #f39c12; --text-main: #e0e0e0; --text-muted: #94a3b8; --bg-card: #1b263b; --border-color: #334155; }
        .metricas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .metrica-card { padding: 20px; border-radius: 10px; text-align: center; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); }
        .metrica-card h4 { margin: 0 0 10px 0; font-size: 0.8rem; color: var(--text-muted); }
        .metrica-card .valor { font-size: 1.8rem; font-weight: bold; }
        .cajero-row { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid var(--border-color); color: var(--text-main); }
        .cajero-row:nth-child(odd) { background: rgba(0,0,0,0.02); }
        body.tema-oscuro .cajero-row:nth-child(odd) { background: rgba(255,255,255,0.02); }
        .cajero-info { flex: 2; }
        .cajero-info h4 { margin: 0; color: var(--text-main); }
        .cajero-info span { font-size: 0.8rem; color: var(--text-muted); }
        .cajero-metricas { display: flex; gap: 30px; flex: 3; justify-content: space-around; text-align: center; }
        .cajero-metricas div strong { display: block; font-size: 1.2rem; }
        .cajero-metricas div span { font-size: 0.75rem; color: var(--text-muted); }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-user-tie"></i> Rendimiento por Cajero</h2></div>
        <div style="display:flex; gap:10px;">
            <a href="reportes.php" class="btn-volver gris">← Reportes</a>
            <a href="../dashboard.php" class="btn-volver">← Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <form method="GET" class="filtro-container">
            <div style="display:flex; gap:10px; align-items:center;">
                <label>Desde: <input type="date" name="desde" value="<?php echo $desde; ?>"></label>
                <label>Hasta: <input type="date" name="hasta" value="<?php echo $hasta; ?>"></label>
                <button type="submit" class="btn-volver">Filtrar</button>
                <a href="../../controllers/ExportController.php?type=cajeros&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>" class="btn-volver verde" target="_blank">📥 Exportar Excel</a>
            </div>
        </form>

        <?php 
        $totalRecaudado = array_sum(array_column($cajeros, 'total_recaudado'));
        $totalVentas = array_sum(array_column($cajeros, 'total_ventas'));
        ?>
        <div class="metricas-grid">
            <div class="metrica-card" style="border-color: var(--primary);">
                <h4>TOTAL RECAUDADO</h4>
                <div class="valor">C$ <?php echo number_format($totalRecaudado, 2); ?></div>
            </div>
            <div class="metrica-card" style="border-color: var(--info);">
                <h4>TOTAL VENTAS</h4>
                <div class="valor"><?php echo $totalVentas; ?></div>
            </div>
            <div class="metrica-card" style="border-color: var(--warning);">
                <h4>PROMEDIO/VENTA</h4>
                <div class="valor">C$ <?php echo $totalVentas > 0 ? number_format($totalRecaudado / $totalVentas, 2) : '0.00'; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <h3>📊 Detalle por Empleado</h3>
            <div style="max-height: 400px; overflow-y: auto;">
                <?php foreach($cajeros as $c): ?>
                <div class="cajero-row">
                    <div class="cajero-info">
                        <h4><?php echo strtoupper($c['usuario']); ?></h4>
                        <span><?php echo $c['rol']; ?></span>
                    </div>
                    <div class="cajero-metricas">
                        <div>
                            <strong><?php echo $c['total_ventas']; ?></strong>
                            <span>Ventas</span>
                        </div>
                        <div>
                            <strong style="color:var(--primary);">C$ <?php echo number_format($c['total_recaudado'], 2); ?></strong>
                            <span>Recaudado</span>
                        </div>
                        <div>
                            <strong style="color:var(--danger);">C$ <?php echo number_format($c['total_anulado'], 2); ?></strong>
                            <span>Anulado</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>