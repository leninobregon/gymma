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
$agrupados = $reporte->getSociosPorVencerAgrupados();
$sociosPorVencer = $reporte->getSociosPorVencer(30);
$vencidos = $reporte->getSociosVencidos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Socios por Vencer - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #27ae60; --danger: #e74c3c; --warning: #f39c12; --text-main: #333; --text-muted: #666; --bg-card: #fff; --border-color: #ddd; --header-bg: #2c3e50; }
        body.tema-oscuro { --primary: #2ecc71; --danger: #e74c3c; --warning: #f39c12; --text-main: #e0e0e0; --text-muted: #aaa; --bg-card: #1e1e1e; --border-color: #333; --header-bg: #1a1a2e; }
        body.tema-darkblue { --primary: #2ecc71; --danger: #e74c3c; --warning: #f39c12; --text-main: #e0e0e0; --text-muted: #94a3b8; --bg-card: #1b263b; --border-color: #334155; --header-bg: #0d1b2a; }
        .alerta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .alerta-card { padding: 20px; border-radius: 10px; text-align: center; border: 2px solid; background: var(--bg-card); }
        .alerta-card .cantidad { font-size: 2.5rem; font-weight: bold; }
        .urgente { background: rgba(231, 76, 60, 0.1); border-color: var(--danger); }
        .urgente .cantidad { color: var(--danger); }
        .proximos { background: rgba(243, 156, 18, 0.1); border-color: var(--warning); }
        .proximos .cantidad { color: var(--warning); }
        .vencidos { background: rgba(192, 57, 43, 0.1); border-color: var(--danger); }
        .vencidos .cantidad { color: var(--danger); }
        .tabla-socios { width: 100%; border-collapse: collapse; color: var(--text-main); }
        .tabla-socios th { background: var(--header-bg); color: white; padding: 12px; text-align: left; }
        .tabla-socios td { padding: 10px; border-bottom: 1px solid var(--border-color); }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2>⏰ Socios por Vencer</h2></div>
        <div style="display:flex; gap:10px;">
            <a href="reportes.php" class="btn-volver gris">← Reportes</a>
            <a href="../dashboard.php" class="btn-volver">← Dashboard</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <div class="alerta-grid">
            <?php 
            $totalVencidos = 0;
            $totalUrgente = 0;
            $totalProximos = 0;
            foreach($agrupados as $a) {
                if(strpos($a['grupo'], 'VENCIDOS') !== false) $totalVencidos = $a['cantidad'];
                if(strpos($a['grupo'], 'URGENTE') !== false) $totalUrgente = $a['cantidad'];
                if(strpos($a['grupo'], 'POR VENCER') !== false) $totalProximos = $a['cantidad'];
            }
            ?>
            <div class="alerta-card vencidos">
                <div class="cantidad"><?php echo $totalVencidos; ?></div>
                <div>⚠️ VENCIDOS</div>
            </div>
            <div class="alerta-card urgente">
                <div class="cantidad"><?php echo $totalUrgente; ?></div>
                <div>🔥 URGENTE (1-3 días)</div>
            </div>
            <div class="alerta-card proximos">
                <div class="cantidad"><?php echo $totalProximos; ?></div>
                <div>⏳ POR VENCER (4-7 días)</div>
            </div>
        </div>

        <?php if(count($vencidos) > 0): ?>
        <div class="stat-card">
            <h3 style="color:#e74c3c;">⚠️ Socios Vencidos (<?php echo count($vencidos); ?>)</h3>
            <table class="tabla-socios">
                <thead>
                    <tr><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Plan</th><th>Vencimiento</th></tr>
                </thead>
                <tbody>
                    <?php foreach($vencidos as $s): ?>
                    <tr>
                        <td><?php echo $s['nombre'].' '.$s['apellido']; ?></td>
                        <td><?php echo $s['cedula']; ?></td>
                        <td><?php echo $s['telefono']; ?></td>
                        <td><?php echo $s['nombre_plan'] ?? 'N/A'; ?></td>
                        <td style="color:var(--danger);"><?php echo date('d/m/Y', strtotime($s['fecha_vencimiento'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if(count($sociosPorVencer) > 0): ?>
        <div class="stat-card">
            <h3 style="color:var(--warning);">⏳ Próximos 30 días (<?php echo count($sociosPorVencer); ?>)</h3>
            <table class="tabla-socios">
                <thead>
                    <tr><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Plan</th><th>Vence</th><th>Días</th></tr>
                </thead>
                <tbody>
                    <?php foreach($sociosPorVencer as $s): 
                        $dias = floor((strtotime($s['fecha_vencimiento']) - time()) / 86400);
                    ?>
                    <tr>
                        <td><?php echo $s['nombre'].' '.$s['apellido']; ?></td>
                        <td><?php echo $s['cedula']; ?></td>
                        <td><?php echo $s['telefono']; ?></td>
                        <td><?php echo $s['nombre_plan'] ?? 'N/A'; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($s['fecha_vencimiento'])); ?></td>
                        <td style="color:<?php echo $dias <= 3 ? 'var(--danger)' : 'var(--warning)'; ?>"><?php echo $dias; ?> días</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div style="text-align:center; margin-top:20px;">
            <a href="../../controllers/ExportController.php?type=socios&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>" class="btn-volver verde" target="_blank">📥 Exportar Excel</a>
        </div>
    </div>
</body>
</html>