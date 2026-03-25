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
$config = (new AppConfig($db))->obtenerConfig();

$tasa_cambio = $config['tasa_cambio'] ?? 36.65;
$fechaI = $_GET['desde'] ?? null;
$fechaF = $_GET['hasta'] ?? null;
$tema = $_SESSION['tema'] ?? 'default';

$cierres = $reporteObj->getHistorialCajas($fechaI, $fechaF);

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
    <title>Historial de Cajas - Arqueos</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .filtro-container { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: flex-end; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .card-stat { background: white; padding: 20px; border-radius: 12px; text-align: center; border-top: 5px solid #3498db; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .monto-ref { font-size: 0.85rem; color: #27ae60; font-weight: bold; display: block; margin-top: 5px; }
        .dif-negativa { color: #e74c3c; font-weight: bold; background: #fdf2f2; }
        .dif-positiva { color: #2ecc71; font-weight: bold; background: #f2fdf5; }
        .badge-abierta { background: #f1c40f; color: #000; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; }
        
        /* CAMBIO SOLICITADO: Estilo resaltado para la tasa */
        .tasa-destacada { 
            font-size: 13px; 
            font-weight: bold; 
            background: #2d3436; 
            color: #f1c40f; /* Color Amarillo/Dorado para resaltar */
            padding: 6px 14px; 
            border-radius: 20px;
            border: 1px solid #f1c40f;
        }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2>📂 Historial de Cajas</h2></div>
        <div style="display:flex; align-items:center; gap:15px;">
            <span class="tasa-destacada">TASA REF: C$ <?= $tasa_cambio ?></span>
            <a href="../dashboard.php" class="btn-volver gris">← Volver</a>
        </div>
    </header>

    <div class="dashboard-wrapper">
        <form method="GET" class="filtro-container">
            <div>
                <label style="display:block; font-size:12px; font-weight:bold;">DESDE:</label>
                <input type="date" name="desde" value="<?= $fechaI ?>" style="padding:8px; border:1px solid #ddd; border-radius:6px;">
            </div>
            <div>
                <label style="display:block; font-size:12px; font-weight:bold;">HASTA:</label>
                <input type="date" name="hasta" value="<?= $fechaF ?>" style="padding:8px; border:1px solid #ddd; border-radius:6px;">
            </div>
            <button type="submit" class="btn-accion">🔍 FILTRAR</button>

            <a href="../../controllers/ExportController.php?type=cajas&desde=<?= $fechaI ?>&hasta=<?= $fechaF ?>" 
               class="btn-accion" style="background:#27ae60; text-decoration:none;">📗 EXPORTAR EXCEL</a>
        </form>

        <div class="grid-stats">
            <div class="card-stat">
                <h4 style="margin:0; color:#7f8c8d;">SISTEMA (ESPERADO)</h4>
                <h2 style="margin:5px 0;">C$ <?= number_format($totalEsperadoG, 2) ?></h2>
                <span class="monto-ref">$ <?= number_format($totalEsperadoG / $tasa_cambio, 2) ?> USD</span>
            </div>
            <div class="card-stat" style="border-top-color: #2ecc71;">
                <h4 style="margin:0; color:#7f8c8d;">ENTREGADO (REAL)</h4>
                <h2 style="margin:5px 0;">C$ <?= number_format($totalRealG, 2) ?></h2>
                <span class="monto-ref">$ <?= number_format($totalRealG / $tasa_cambio, 2) ?> USD</span>
            </div>
        </div>

        <div style="background:white; padding:20px; border-radius:12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:#f8f9fa; border-bottom:2px solid #eee;">
                        <th style="padding:15px; text-align:left;">CAJERO / USUARIO</th>
                        <th style="text-align:left;">TIEMPOS (INICIO - FIN)</th>
                        <th style="text-align:right;">ESPERADO (C$)</th>
                        <th style="text-align:right;">REAL (C$)</th>
                        <th style="text-align:right;">DIFERENCIA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cierres as $c): 
                        $dif = $c['monto_cierre'] - $c['monto_esperado'];
                        $isAbierta = ($c['estado'] == 'ABIERTA');
                    ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:12px;">
                            <strong><?= strtoupper($c['nombre_usuario']) ?></strong><br>
                            <small style="color:#95a5a6;">Caja #<?= $c['id'] ?></small>
                        </td>
                        <td style="font-size:11px; line-height: 1.4;">
                            <span style="color:#27ae60;">🛫 <?= date('d/m/y H:i', strtotime($c['fecha_apertura'])) ?></span><br>
                            <span style="color:#c0392b;">
                                <?= !$isAbierta ? '🛬 '.date('d/m/y H:i', strtotime($c['fecha_cierre'])) : '<span class="badge-abierta">EN CURSO</span>' ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <strong>C$ <?= number_format($c['monto_esperado'], 2) ?></strong><br>
                            <small style="color:#7f8c8d;">$ <?= number_format($c['monto_esperado'] / $tasa_cambio, 2) ?></small>
                        </td>
                        <td style="text-align:right;">
                            <strong>C$ <?= number_format($c['monto_cierre'], 2) ?></strong><br>
                            <small style="color:#7f8c8d;">$ <?= number_format($c['monto_cierre'] / $tasa_cambio, 2) ?></small>
                        </td>
                        <td style="text-align:right; vertical-align: middle;" class="<?= ($dif < 0) ? 'dif-negativa':'dif-positiva' ?>">
                            <?php if($isAbierta): ?>
                                <span style="color:#95a5a6;">---</span>
                            <?php else: ?>
                                <?= ($dif >= 0 ? '+' : '') . 'C$ ' . number_format($dif, 2) ?><br>
                                <small>(<?= number_format($dif / $tasa_cambio, 2) ?> USD)</small>
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