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
$simbolo = $config['moneda_simbolo'] ?? '<?php echo $simbolo; ?>';
$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';
$reporte = new Reporte($db);

$clientes = $reporte->getClientesFrecuentes(15);
$metricas = $reporte->getMetricasAvanzadas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes Frecuentes - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #27ae60; --danger: #e74c3c; --info: #3498db; --warning: #f39c12; --purple: #9b59b6; --text-main: #333; --text-muted: #666; --bg-card: #fff; --border-color: #ddd; --header-bg: #2c3e50; }
        body.tema-oscuro { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --warning: #f39c12; --purple: #9b59b6; --text-main: #e0e0e0; --text-muted: #aaa; --bg-card: #1e1e1e; --border-color: #333; --header-bg: #1a1a2e; }
        body.tema-darkblue { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --warning: #f39c12; --purple: #9b59b6; --text-main: #e0e0e0; --text-muted: #94a3b8; --bg-card: #1b263b; --border-color: #334155; --header-bg: #0d1b2a; }
        .metricas-dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .metrica-box { padding: 20px; border-radius: 10px; text-align: center; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); }
        .metrica-box h4 { margin: 0 0 8px 0; font-size: 0.75rem; color: var(--text-muted); }
        .metrica-box .valor { font-size: 1.8rem; font-weight: bold; }
        .cliente-card { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid var(--border-color); gap: 15px; color: var(--text-main); }
        .cliente-card:hover { background: rgba(0,0,0,0.03); }
        body.tema-oscuro .cliente-card:hover { background: rgba(255,255,255,0.03); }
        .cliente-pos { width: 40px; height: 40px; border-radius: 50%; background: var(--header-bg); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .cliente-info { flex: 1; }
        .cliente-info h4 { margin: 0; color: var(--text-main); }
        .cliente-info span { font-size: 0.85rem; color: var(--text-muted); }
        .cliente-stats { display: flex; gap: 25px; text-align: center; }
        .cliente-stats strong { display: block; font-size: 1.1rem; }
        .cliente-stats span { font-size: 0.75rem; color: var(--text-muted); }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2>⭐ Clientes Frecuentes</h2></div>
        <div style="display:flex; gap:10px;">
            <a href="reportes.php" class="btn-volver gris">← Reportes</a>
            <a href="../dashboard.php" class="btn-volver">← Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <div class="metricas-dashboard">
            <div class="metrica-box" style="border-color: var(--info);">
                <h4>TICKET PROMEDIO</h4>
                <div class="valor"><?php echo $simbolo; ?> <?php echo number_format($metricas['ticket_promedio'], 2); ?></div>
            </div>
            <div class="metrica-box" style="border-color: var(--primary);">
                <h4>VENTAS HOY</h4>
                <div class="valor"><?php echo $metricas['ventas_hoy']; ?></div>
            </div>
            <div class="metrica-box" style="border-color: var(--purple);">
                <h4>SOCIOS NUEVOS (MES)</h4>
                <div class="valor"><?php echo $metricas['socios_nuevos_mes']; ?></div>
            </div>
            <div class="metrica-box" style="border-color: var(--warning);">
                <h4>RENOVACIONES (MES)</h4>
                <div class="valor"><?php echo $metricas['renovaciones_mes']; ?></div>
            </div>
            <div class="metrica-box" style="border-color: var(--danger);">
                <h4>PRODUCTOS HOY</h4>
                <div class="valor"><?php echo $metricas['productos_vendidos_hoy']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <h3>🏆 Top Clientes por Compras</h3>
            <?php $pos = 1; foreach($clientes as $c): ?>
            <div class="cliente-card">
                <div class="cliente-pos"><?php echo $pos++; ?></div>
                <div class="cliente-info">
                    <h4><?php echo strtoupper($c['nombre'].' '.$c['apellido']); ?></h4>
                    <span>📞 <?php echo $c['telefono'] ?: 'Sin teléfono'; ?></span>
                </div>
                <div class="cliente-stats">
                    <div>
                        <strong style="color:var(--primary);"><?php echo $c['num_compras']; ?></strong>
                        <span>Compras</span>
                    </div>
                    <div>
                        <strong style="color:var(--info);"><?php echo $simbolo; ?> <?php echo number_format($c['total_gastado'], 2); ?></strong>
                        <span>Total Gastado</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center; margin-top:20px;">
            <a href="../../controllers/ExportController.php?type=clientes" class="btn-volver verde" target="_blank">📥 Exportar Excel</a>
        </div>
    </div>
</body>
</html>