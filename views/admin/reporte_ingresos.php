<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php");
    exit();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Reporte.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$simbolo = $config['moneda_simbolo'] ?? '<?php echo $simbolo; ?>';
$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';

$reporte = new Reporte($db);

$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? date('Y-m-d');

$datos = $reporte->getIngresosEgresos($desde, $hasta);
$categoria = $reporte->getIngresosPorCategoria($desde, $hasta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ingresos/Egresos - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #27ae60; --danger: #e74c3c; --info: #3498db; --text-main: #333; --text-muted: #666; --bg-card: #fff; --border-color: #ddd; }
        body.tema-oscuro { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --text-main: #e0e0e0; --text-muted: #aaa; --bg-card: #1e1e1e; --border-color: #333; }
        body.tema-darkblue { --primary: #2ecc71; --danger: #e74c3c; --info: #3498db; --text-main: #e0e0e0; --text-muted: #94a3b8; --bg-card: #1b263b; --border-color: #334155; }
        .resumen-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .resumen-card { padding: 25px; border-radius: 12px; text-align: center; border: 2px solid var(--border-color); background: var(--bg-card); }
        .resumen-card h3 { margin: 0 0 10px 0; font-size: 0.9rem; text-transform: uppercase; color: var(--text-muted); }
        .resumen-card .monto { font-size: 2rem; font-weight: bold; }
        .card-ingresos { background: rgba(39, 174, 96, 0.1); border-color: var(--primary); }
        .card-egresos { background: rgba(231, 76, 60, 0.1); border-color: var(--danger); }
        .card-balance { background: rgba(52, 152, 219, 0.1); border-color: var(--info); }
        .filtros { display: flex; gap: 15px; align-items: center; flex-wrap: wrap; color: var(--text-main); }
        .filtros input, .filtros button { padding: 10px; border-radius: 5px; }
        .btn-export { background: var(--primary); color: white; border: none; cursor: pointer; font-weight: 600; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-chart-pie"></i> Ingresos y Egresos</h2></div>
        <div style="display:flex; gap:10px;">
            <a href="reportes.php" class="btn-volver gris">← Reportes</a>
            <a href="../dashboard.php" class="btn-volver">← Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <div class="filtros">
            <form method="GET" style="display:flex; gap:10px; align-items:center;">
                <label>Desde: <input type="date" name="desde" value="<?php echo $desde; ?>"></label>
                <label>Hasta: <input type="date" name="hasta" value="<?php echo $hasta; ?>"></label>
                <button type="submit" class="btn-volver">Filtrar</button>
                <a href="reporte_ingresos.php" class="btn-volver gris">Limpiar</a>
            </form>
            <a href="../../controllers/ExportController.php?type=ingresos&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>" class="btn-volver verde" target="_blank">📥 Exportar Excel</a>
        </div>

        <div class="resumen-grid">
            <div class="resumen-card card-ingresos">
                <h3>💰 Ingresos</h3>
                <div class="monto" style="color:var(--primary);"><?php echo $simbolo; ?> <?php echo number_format($datos['ingresos'], 2); ?></div>
            </div>
            <div class="resumen-card card-egresos">
                <h3>📤 Egresos</h3>
                <div class="monto" style="color:var(--danger);"><?php echo $simbolo; ?> <?php echo number_format($datos['egresos'], 2); ?></div>
            </div>
            <div class="resumen-card card-balance">
                <h3>📊 Balance</h3>
                <div class="monto" style="color:<?php echo $datos['balance'] >= 0 ? 'var(--primary)' : 'var(--danger)'; ?>">
                    <?php echo $simbolo; ?> <?php echo number_format($datos['balance'], 2); ?>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="stat-card">
                <h3>📈 Ingresos por Método de Pago</h3>
                <canvas id="chartMetodos"></canvas>
                <table style="margin-top:15px;">
                    <tr><th>Método</th><th>Total</th></tr>
                    <?php foreach($datos['por_metodo'] as $m): ?>
                    <tr><td><?php echo $m['metodo_pago']; ?></td><td><?php echo $simbolo; ?> <?php echo number_format($m['total'], 2); ?></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="stat-card">
                <h3>🏷️ Ingresos por Categoría</h3>
                <canvas id="chartCategoria"></canvas>
            </div>
        </div>
    </div>

    <script>
        const metodosCtx = document.getElementById('chartMetodos').getContext('2d');
        new Chart(metodosCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($datos['por_metodo'], 'metodo_pago')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($datos['por_metodo'], 'total')); ?>,
                    backgroundColor: ['#27ae60', '#3498db', '#f39c12', '#9b59b6']
                }]
            }
        });

        const catCtx = document.getElementById('chartCategoria').getContext('2d');
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($categoria, 'categoria')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($categoria, 'total')); ?>,
                    backgroundColor: ['#27ae60', '#e67e22', '#3498db']
                }]
            }
        });
    </script>
</body>
</html>