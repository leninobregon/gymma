<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once "../config/Database.php";
require_once "../config/AppConfig.php";
require_once "../classes/Dashboard.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$dash = new Dashboard($db);

// 1. VERIFICACIÓN DE CAJA ACTIVA (Para el banner de aviso)
$stmtCaja = $db->prepare("SELECT id FROM cajas WHERE estado = 'ABIERTA' AND id_usuario = ? LIMIT 1");
$stmtCaja->execute([$_SESSION['user_id']]);
$cajaActiva = $stmtCaja->fetch(PDO::FETCH_ASSOC);

// 2. CARGA DE ESTADÍSTICAS
$ventasHoy = $dash->getResumenVentasHoy();
$socios = $dash->getTotalSociosActivos();
$alertas = $dash->getAlertasInventario();

// 3. DATOS PARA LA GRÁFICA (7 DÍAS)
$q = $db->query("SELECT DATE(fecha_venta) as f, SUM(monto_total) as t FROM ventas 
                 WHERE estado != 'ANULADO' AND fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(fecha_venta) ORDER BY f ASC");
$graf = $q->fetchAll(PDO::FETCH_ASSOC);

$fechasJs = []; $totalesJs = [];
foreach($graf as $d) { 
    $fechasJs[] = date('d/m', strtotime($d['f'])); 
    $totalesJs[] = (float)$d['t']; 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../public/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 15px; }
        .stat-card { padding: 12px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-decoration: none; display: block; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-3px); opacity: 0.9; }
        .stat-card h4 { margin: 0; font-size: 0.7rem; text-transform: uppercase; opacity: 0.8; }
        .stat-card p { font-size: 1.2rem !important; margin: 5px 0 !important; font-weight: bold; }
        .bg-ventas { background: #27ae60; } .bg-socios { background: #2980b9; } .bg-alertas { background: #e67e22; }
        
        /* Estilo para el aviso de caja abierta */
        .aviso-caja { background: #fff3cd; color: #856404; padding: 15px; border-radius: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .btn-volver-caja { background: #856404; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 0.8rem; }

        .chart-box, .actions-box { background: white; padding: 15px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 15px; }
        header { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: #2c3e50; color: white; }
        .btn-accion { transition: 0.2s; padding: 8px 12px; border-radius: 5px; color: white; text-decoration: none; font-size: 0.9rem; background: #34495e; }
    </style>
</head>
<body>
    <header>
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="../public/img/<?php echo $config['logo_ruta']; ?>" width="35" height="35" style="border-radius: 50%;">
            <h2 style="margin:0; font-size: 1.1rem;"><?php echo $config['nombre_gym']; ?></h2> 
        </div>
        <div style="font-size: 0.8rem; display: flex; align-items: center; gap: 12px;">
            <span>TC: <strong><?php echo number_format($config['tipo_cambio_bcn'], 2); ?></strong></span>
            <span>👤 <?php echo strtoupper($_SESSION['usuario']); ?></span>
            <a href="logout.php" style="background:#e74c3c; padding: 4px 10px; border-radius: 5px; color: white; text-decoration: none;">Salir</a>
        </div>
    </header>

    <div class="dashboard-wrapper" style="padding: 15px; max-width: 1200px; margin: auto;">
        
        <?php if ($cajaActiva): ?>
            <div class="aviso-caja">
                <div>
                    <strong>⚠️ Turno en curso:</strong> Tienes la Caja #<?php echo $cajaActiva['id']; ?> abierta.
                </div>
                <a href="caja/punto_venta.php" class="btn-volver-caja">VOLVER A VENTAS 🛒</a>
            </div>
        <?php endif; ?>

        <div class="stats-container">
            <a href="admin/reportes.php" class="stat-card bg-ventas">
                <h4>Ventas Hoy</h4>
                <p><?php echo $config['moneda_simbolo']; ?> <?php echo number_format($ventasHoy, 2); ?></p>
            </a>
            <a href="caja/registro_socios.php" class="stat-card bg-socios">
                <h4>Socios Activos</h4>
                <p><?php echo $socios; ?></p>
            </a>
            <a href="admin/gestion_inventario.php" class="stat-card bg-alertas">
                <h4>Stock Crítico</h4>
                <p><?php echo $alertas; ?></p>
            </a>
        </div>

        <div class="chart-box">
            <h4 style="margin:0 0 10px 0; font-size: 0.85rem; color: #7f8c8d;">📈 RENDIMIENTO SEMANAL</h4>
            <canvas id="grafVentas" style="max-height: 160px; width: 100%;"></canvas>
        </div>

        <div class="actions-box">
            <h4 style="margin:0 0 12px 0; font-size: 0.85rem; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px;">ACCIONES RÁPIDAS</h4>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <a href="caja/punto_venta.php" class="btn-accion">🛒 Punto de Venta</a>
                <a href="caja/registro_socios.php" class="btn-accion">👤 Socios</a>
                <?php if ($_SESSION['rol'] === 'ADMIN'): ?>
                    <a href="admin/gestion_usuarios.php" class="btn-accion" style="background: #d35400;">👥 Personal</a>
                    <a href="admin/gestion_inventario.php" class="btn-accion" style="background: #8e44ad;">📦 Stock</a>
                    <a href="admin/gestion_planes.php" class="btn-accion" style="background: #8e44ad;">💳 Planes</a>
                    <a href="admin/historial_cajas.php" class="btn-accion" style="background: #f39c12;">💰 Cajas</a>
                    <a href="admin/reportes.php" class="btn-accion" style="background: #34495e;">📊 Reportes</a>
                    <a href="admin/configuracion.php" class="btn-accion" style="background: #7f8c8d;">⚙️ Ajustes</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        new Chart(document.getElementById('grafVentas').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($fechasJs); ?>,
                datasets: [{
                    label: 'Ingresos',
                    data: <?php echo json_encode($totalesJs); ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
            }
        });
    </script>
</body>
</html>