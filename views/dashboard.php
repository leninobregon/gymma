<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once "../config/Database.php";
require_once "../config/AppConfig.php";
require_once "../classes/Dashboard.php";
require_once "../classes/Reporte.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$dash = new Dashboard($db);
$reporte = new Reporte($db);

$tema = $_SESSION['tema'] ?? 'default';

$bg_body = '#f8f9fa';
$bg_card = '#ffffff';
$text_main = '#2d3436';
$text_muted = '#636e72';
$border_color = '#dfe6e9';
$header_bg = '#2d3436';
$primary = '#00b894';
$danger = '#d63031';
$warning = '#fdcb6e';
$info = '#0984e3';

if ($tema === 'oscuro') {
    $bg_body = '#1a1a2e';
    $bg_card = '#16213e';
    $text_main = '#f1f2f6';
    $text_muted = '#a4b0be';
    $border_color = '#2d3436';
    $header_bg = '#0f0f23';
    $primary = '#00cec9';
    $danger = '#ff7675';
    $warning = '#ffeaa7';
    $info = '#74b9ff';
} elseif ($tema === 'darkblue') {
    $bg_body = '#0a1628';
    $bg_card = '#1a2d4a';
    $text_main = '#ecf0f1';
    $text_muted = '#95a5a6';
    $border_color = '#2c3e50';
    $header_bg = '#0a1628';
    $primary = '#00d2d3';
    $danger = '#ff6b6b';
    $warning = '#feca57';
    $info = '#54a0ff';
}

$stmtCaja = $db->prepare("SELECT id FROM cajas WHERE estado = 'ABIERTA' AND id_usuario = ? LIMIT 1");
$stmtCaja->execute([$_SESSION['user_id']]);
$cajaActiva = $stmtCaja->fetch(PDO::FETCH_ASSOC);

$stats = $reporte->getEstadisticasDashboard();
$productosTop = $reporte->getProductosMasVendidos(3);
$sociosPorVencer = $reporte->getSociosPorVencer(7);
$ventasCategoria = $reporte->getVentasPorCategoria();
$metricas = $reporte->getMetricasAvanzadas();

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: <?php echo $primary; ?>;
            --secondary: <?php echo $header_bg; ?>;
            --danger: <?php echo $danger; ?>;
            --warning: <?php echo $warning; ?>;
            --info: <?php echo $info; ?>;
            --text-main: <?php echo $text_main; ?>;
            --text-muted: <?php echo $text_muted; ?>;
            --bg-body: <?php echo $bg_body; ?>;
            --bg-card: <?php echo $bg_card; ?>;
            --border-color: <?php echo $border_color; ?>;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: var(--bg-body);
            color: var(--text-main);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
        }
        
        /* Header */
        .main-header {
            background: linear-gradient(135deg, var(--secondary) 0%, #1a252f 100%);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-section img {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .logo-section h1 {
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .btn-logout {
            background: var(--danger);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        
        .btn-logout:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* Contenedor principal */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
        }
        
        /* Aviso caja */
        .aviso-caja {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            color: #2d3436;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(253, 203, 110, 0.3);
        }
        
        body.tema-oscuro .aviso-caja,
        body.tema-darkblue .aviso-caja {
            background: linear-gradient(135deg, #3d3115 0%, #4a4a2a 100%);
            color: #f1f2f6;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid var(--border-color);
            transition: 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .stat-card h4 {
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
        }
        
        .stat-card .icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2.5rem;
            opacity: 0.15;
        }
        
        .stat-ventas { border-left-color: var(--primary); }
        .stat-ventas::before { background: var(--primary); }
        
        .stat-socios { border-left-color: var(--info); }
        .stat-socios::before { background: var(--info); }
        
        .stat-stock { border-left-color: var(--warning); }
        .stat-stock::before { background: var(--warning); }
        
        .stat-vencidos { border-left-color: var(--danger); }
        .stat-vencidos::before { background: var(--danger); }
        
        .stat-cajas { border-left-color: #9b59b6; }
        .stat-cajas::before { background: #9b59b6; }
        
        /* Métricas adicionales */
        .metricas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .metrica-card {
            background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-body) 100%);
            padding: 18px;
            border-radius: 14px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .metrica-card h4 {
            color: var(--text-muted);
            font-size: 0.7rem;
            margin-bottom: 8px;
        }
        
        .metrica-card .value {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        /* Acciones Rápidas */
        .actions-section {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }
        
        .actions-section h3 {
            color: var(--text-main);
            margin-bottom: 15px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .actions-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .btn-accion {
            padding: 12px 20px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        
        .btn-accion:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .chart-card {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .chart-card h3 {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Notificaciones */
        .notif-card {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.1) 0%, rgba(231, 76, 60, 0.05) 100%);
            border-left: 4px solid var(--danger);
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .notif-card h4 {
            color: var(--danger);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notif-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .notif-tag {
            background: var(--bg-card);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .dashboard-container {
                padding: 15px;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .actions-grid {
                justify-content: center;
            }
            
            .btn-accion {
                flex: 1;
                justify-content: center;
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo-section">
            <img src="../public/img/<?php echo $config['logo_ruta']; ?>" alt="Logo">
            <h1><?php echo $config['nombre_gym']; ?></h1>
        </div>
        <div class="user-section">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['usuario'], 0, 1)); ?></div>
                <span><?php echo strtoupper($_SESSION['usuario']); ?></span>
            </div>
            <a href="configurar_2fa.php" class="btn-logout" style="background: #6c757d;" title="Configurar 2FA">
                <i class="fas fa-shield-halved"></i>
            </a>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </header>

    <div class="dashboard-container">
        <?php if ($cajaActiva): ?>
        <div class="aviso-caja">
            <div>
                <strong><i class="fas fa-exclamation-triangle"></i> Turno en curso:</strong> 
                Caja #<?php echo $cajaActiva['id']; ?> abierta
            </div>
            <a href="caja/punto_venta.php" class="btn-volver" style="background: #2d3436;"><i class="fas fa-cash-register"></i> Ir a Ventas</a>
        </div>
        <?php endif; ?>

        <!-- Stats Principales -->
        <div class="stats-grid">
            <a href="admin/reportes.php" class="stat-card stat-ventas">
                <h4><i class="fas fa-dollar-sign"></i> Ventas Hoy</h4>
                <div class="value"><?php echo $config['moneda_simbolo']; ?> <?php echo number_format($stats['ventas_hoy'], 2); ?></div>
                <i class="fas fa-chart-line icon"></i>
            </a>
            <a href="admin/reporte_socios.php" class="stat-card stat-socios">
                <h4><i class="fas fa-users"></i> Socios Activos</h4>
                <div class="value"><?php echo $stats['socios_activos']; ?></div>
                <i class="fas fa-user-friends icon"></i>
            </a>
            <a href="admin/gestion_inventario.php" class="stat-card stat-stock">
                <h4><i class="fas fa-box"></i> Stock Crítico</h4>
                <div class="value"><?php echo $stats['productos_bajo_stock']; ?></div>
                <i class="fas fa-exclamation-circle icon"></i>
            </a>
            <a href="admin/reporte_socios.php" class="stat-card stat-vencidos">
                <h4><i class="fas fa-user-clock"></i> Socios Vencidos</h4>
                <div class="value"><?php echo $stats['socios_vencidos']; ?></div>
                <i class="fas fa-calendar-times icon"></i>
            </a>
            <a href="admin/reporte_cajas.php" class="stat-card stat-cajas">
                <h4><i class="fas fa-cash-register"></i> Cajas Abiertas</h4>
                <div class="value"><?php echo $stats['caja_abierta']; ?></div>
                <i class="fas fa-door-open icon"></i>
            </a>
        </div>

        <!-- Métricas -->
        <div class="metricas-grid">
            <div class="metrica-card">
                <h4><i class="fas fa-receipt"></i> Ticket Promedio</h4>
                <div class="value"><?php echo $config['moneda_simbolo']; ?> <?php echo number_format($metricas['ticket_promedio'], 2); ?></div>
            </div>
            <div class="metrica-card">
                <h4><i class="fas fa-user-plus"></i> Nuevos (Mes)</h4>
                <div class="value"><?php echo $metricas['socios_nuevos_mes']; ?></div>
            </div>
            <div class="metrica-card">
                <h4><i class="fas fa-redo"></i> Renovaciones</h4>
                <div class="value"><?php echo $metricas['renovaciones_mes']; ?></div>
            </div>
            <div class="metrica-card">
                <h4><i class="fas fa-shopping-bag"></i> Productos Hoy</h4>
                <div class="value"><?php echo $metricas['productos_vendidos_hoy']; ?></div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="actions-section">
            <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
            <div class="actions-grid">
                <a href="caja/punto_venta.php" class="btn-accion" style="background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);">
                    <i class="fas fa-cash-register"></i> Punto de Venta
                </a>
                <a href="caja/registro_socios.php" class="btn-accion" style="background: linear-gradient(135deg, #0984e3 0%, #74b9ff 100%);">
                    <i class="fas fa-id-card-alt"></i> Socios
                </a>
                <?php if ($_SESSION['rol'] === 'ADMIN'): ?>
                <a href="admin/gestion_usuarios.php" class="btn-accion" style="background: linear-gradient(135deg, #e17055 0%, #fab1a0 100%);">
                    <i class="fas fa-user-shield"></i> Personal
                </a>
                <a href="admin/gestion_inventario.php" class="btn-accion" style="background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);">
                    <i class="fas fa-warehouse"></i> Inventario
                </a>
                <a href="admin/gestion_planes.php" class="btn-accion" style="background: linear-gradient(135deg, #00b894 0%, #55efc4 100%);">
                    <i class="fas fa-tags"></i> Planes
                </a>
                <a href="admin/reportes.php" class="btn-accion" style="background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);">
                    <i class="fas fa-file-invoice-dollar"></i> Reportes | Ventas
                </a>
                <a href="admin/reporte_socios.php" class="btn-accion" style="background: linear-gradient(135deg, #0984e3 0%, #81ecec 100%);">
                    <i class="fas fa-users"></i> Reporte Socios
                </a>
                <a href="admin/reporte_cajas.php" class="btn-accion" style="background: linear-gradient(135deg, #fdcb6e 0%, #ffeaa7 100%); color: #2d3436;">
                    <i class="fas fa-money-check-alt"></i> Reporte Cajas
                </a>
                <a href="admin/configuracion.php" class="btn-accion" style="background: linear-gradient(135deg, #636e72 0%, #b2bec3 100%);">
                    <i class="fas fa-sliders-h"></i> Ajustes
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-chart-area"></i> Rendimiento Semanal</h3>
                <canvas id="grafVentas" style="max-height: 200px;"></canvas>
            </div>
            <?php if(count($ventasCategoria) > 0): ?>
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Ventas por Categoría</h3>
                <div style="display: flex; align-items: center; gap: 20px;">
                    <canvas id="grafCategoria" style="max-height: 180px; max-width: 180px;"></canvas>
                    <div style="flex: 1;">
                        <?php foreach($ventasCategoria as $c): ?>
                        <div style="margin: 8px 0; padding: 8px; background: var(--bg-body); border-radius: 8px; display: flex; justify-content: space-between;">
                            <span><?php echo $c['categoria']; ?></span>
                            <strong><?php echo $c['cantidad']; ?> - C$ <?php echo number_format($c['total'], 2); ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Notificaciones -->
        <?php 
        $notifStock = $reporte->getNotificacionesStock();
        $notifSocios = $reporte->getNotificacionesSocios();
        if(count($notifStock) > 0 || count($notifSocios) > 0): 
        ?>
        <div class="notif-card">
            <h4><i class="fas fa-bell"></i> Notificaciones</h4>
            <div class="notif-tags">
                <?php foreach($notifStock as $s): ?>
                    <span class="notif-tag"><i class="fas fa-box"></i> <?php echo $s['descripcion']; ?> (<?php echo $s['cantidad']; ?>)</span>
                <?php endforeach; ?>
                <?php foreach($notifSocios as $soc): ?>
                    <span class="notif-tag"><i class="fas fa-user-clock"></i> <?php echo $soc['nombre'].' '.$soc['apellido']; ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        new Chart(document.getElementById('grafVentas').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($fechasJs); ?>,
                datasets: [{
                    label: 'Ingresos',
                    data: <?php echo json_encode($totalesJs); ?>,
                    backgroundColor: 'rgba(0, 184, 148, 0.8)',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        <?php if(count($ventasCategoria) > 0): ?>
        new Chart(document.getElementById('grafCategoria').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($ventasCategoria, 'categoria')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($ventasCategoria, 'total')); ?>,
                    backgroundColor: ['#00b894', '#0984e3', '#fdcb6e', '#6c5ce7']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>