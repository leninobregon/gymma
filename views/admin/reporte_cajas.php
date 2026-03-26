<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}
$tema = $_SESSION['tema'] ?? 'default';

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
require_once "../../classes/Reporte.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$simbolo = $config['moneda_simbolo'] ?? 'C$';
$reporteObj = new Reporte($db);

$fechaI = $_GET['desde'] ?? null;
$fechaF = $_GET['hasta'] ?? null;

$cajas = $reporteObj->getHistorialCajas($fechaI, $fechaF);

$totalApertura = 0;
$totalCierre = 0;
$totalEsperado = 0;
$cajasAbiertas = 0;
$cajasCerradas = 0;

foreach ($cajas as $c) {
    $totalApertura += $c['monto_apertura'];
    $totalCierre += $c['monto_cierre'];
    $totalEsperado += $c['monto_esperado'];
    if ($c['estado'] === 'ABIERTA') $cajasAbiertas++;
    else $cajasCerradas++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cajas - GYM MA</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        body { background: var(--bg-body); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px; color: var(--text-main); }
        .header { background: var(--bg-card); padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); }
        .filtro-container { background: var(--bg-card); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; border: 1px solid var(--border-color); }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .card-stat { background: var(--bg-card); padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color); color: var(--text-main); }
        .card-stat h2 { margin: 5px 0 0 0; font-size: 1.5rem; color: var(--text-main); }
        .card-stat small { color: var(--text-muted); font-weight: bold; font-size: 0.75rem; }
        .tabla-container { background: var(--bg-card); padding: 20px; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: collapse; background: var(--bg-card); color: var(--text-main); }
        th { background: var(--header-bg); padding: 12px; text-align: left; font-size: 0.8rem; color: white; }
        td { padding: 12px; border-bottom: 1px solid var(--border-color); }
        .badge { padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: bold; }
        .badge-abierta { background: #eafaf1; color: #27ae60; }
        .badge-cerrada { background: #f4f6f7; color: #7f8c8d; }
        .diferencia { font-weight: bold; }
        .diff-positiva { color: #27ae60; }
        .diff-negativa { color: #e74c3c; }
        .btn-accion { padding: 10px 15px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold; background: var(--header-bg); color: white; }
        input, select { background: var(--input-bg); color: var(--input-text); border: 1px solid var(--input-border); }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <div class="header">
        <h2><i class="fas fa-money-check-alt"></i> Reporte de Cajas</h2>
        <div style="display:flex; gap:10px;">
            <a href="../../controllers/ExportController.php?type=cajas&desde=<?= $fechaI ?>&hasta=<?= $fechaF ?>" class="btn-accion" style="background:#27ae60; color:white;">📗 EXCEL</a>
            <a href="../dashboard.php" class="btn-volver gris">← Dashboard</a>
        </div>
    </div>

    <form method="GET" class="filtro-container">
        <div>
            <label style="font-size:11px; font-weight:bold; color:#7f8c8d;">DESDE</label>
            <input type="date" name="desde" value="<?= $fechaI ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
        </div>
        <div>
            <label style="font-size:11px; font-weight:bold; color:#7f8c8d;">HASTA</label>
            <input type="date" name="hasta" value="<?= $fechaF ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
        </div>
        <button type="submit" class="btn-accion" style="background:#3498db; color:white; border:none;"><i class="fas fa-search"></i> FILTRAR</button>
        <a href="reporte_cajas.php" class="btn-accion" style="background:#95a5a6; color:white; text-decoration:none;">LIMPIAR</a>
    </form>

    <div class="grid-stats">
        <div class="card-stat" style="border-left: 4px solid #3498db;">
            <small>TOTAL CAJAS</small>
            <h2><?= count($cajas) ?></h2>
        </div>
        <div class="card-stat" style="border-left: 4px solid #27ae60;">
            <small>ABIERTAS</small>
            <h2 style="color:#27ae60;"><?= $cajasAbiertas ?></h2>
        </div>
        <div class="card-stat" style="border-left: 4px solid #7f8c8d;">
            <small>CERRADAS</small>
            <h2 style="color:#7f8c8d;"><?= $cajasCerradas ?></h2>
        </div>
        <div class="card-stat" style="border-left: 4px solid #f39c12;">
            <small>MONTO APERTURA (<?php echo $simbolo; ?>)</small>
            <h2><?php echo $simbolo; ?> <?= number_format($totalApertura, 2) ?></h2>
        </div>
    </div>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>USUARIO</th>
                    <th>APERTURA</th>
                    <th>CIERRE</th>
                    <th>APERTURA (<?php echo $simbolo; ?>)</th>
                    <th>ESPERADO (<?php echo $simbolo; ?>)</th>
                    <th>CIERRE (<?php echo $simbolo; ?>)</th>
                    <th>DIFERENCIA</th>
                    <th>ESTADO</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($cajas) == 0): ?>
                <tr><td colspan="9" style="text-align:center; padding:30px; color:#999;">No hay registros</td></tr>
                <?php else: ?>
                <?php foreach($cajas as $c): 
                    $diferencia = $c['monto_cierre'] - $c['monto_esperado'];
                    $claseDiff = $diferencia >= 0 ? 'diff-positiva' : 'diff-negativa';
                ?>
                <tr>
                    <td><strong>#<?= $c['id'] ?></strong></td>
                    <td><?= $c['nombre_usuario'] ?></td>
                    <td><?= date('d/m H:i', strtotime($c['fecha_apertura'])) ?></td>
                    <td><?= $c['fecha_cierre'] ? date('d/m H:i', strtotime($c['fecha_cierre'])) : '-' ?></td>
                    <td><?php echo $simbolo; ?> <?= number_format($c['monto_apertura'], 2) ?></td>
                    <td><?php echo $simbolo; ?> <?= number_format($c['monto_esperado'], 2) ?></td>
                    <td><?php echo $simbolo; ?> <?= number_format($c['monto_cierre'], 2) ?></td>
                    <td class="diferencia <?= $claseDiff ?>">
                        <?= ($diferencia >= 0 ? '+' : '') . number_format($diferencia, 2) ?>
                    </td>
                    <td>
                        <span class="badge <?= $c['estado'] === 'ABIERTA' ? 'badge-abierta' : 'badge-cerrada' ?>">
                            <?= $c['estado'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
