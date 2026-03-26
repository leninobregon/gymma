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
$simbolo = $config['moneda_simbolo'] ?? '<?php echo $simbolo; ?>';
$reporteObj = new Reporte($db);

$inventario = $reporteObj->getInventarioCompleto();
$bajoStock = $reporteObj->getInventarioBajoStock(10);

$totalItems = 0;
$totalValor = 0;
foreach ($inventario as $item) {
    $totalItems += $item['cantidad'];
    $totalValor += ($item['cantidad'] * $item['precio']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario - GYM MA</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        body { background: var(--bg-body); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px; color: var(--text-main); }
        .header { background: var(--bg-card); padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .card-stat { background: var(--bg-card); padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #3498db; border: 1px solid var(--border-color); color: var(--text-main); }
        .card-stat h2 { margin: 0; color: var(--text-main); font-size: 1.8rem; }
        .card-stat small { color: var(--text-muted); font-weight: bold; }
        .alerta-stock { background: var(--bg-card); border-left: 4px solid #e74c3c !important; }
        .tabla-container { background: var(--bg-card); padding: 20px; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); border: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: collapse; background: var(--bg-card); color: var(--text-main); }
        th { background: var(--header-bg); padding: 12px; text-align: left; color: white; font-size: 0.85rem; }
        td { padding: 12px; border-bottom: 1px solid var(--border-color); }
        .badge-stock { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .stock-alto { background: #eafaf1; color: #27ae60; }
        .stock-bajo { background: #fef5e7; color: #f39c12; }
        .stock-critico { background: #fdecea; color: #e74c3c; }
        .btn-accion { padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold; background: var(--header-bg); color: white; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <div class="header">
        <h2><i class="fas fa-boxes"></i> Reporte de Inventario</h2>
        <div style="display:flex; gap:10px;">
            <a href="../../controllers/ExportController.php?type=inventario" class="btn-accion" style="background:#27ae60; color:white;"><i class="fas fa-file-excel"></i> EXCEL</a>
            <a href="../dashboard.php" class="btn-volver gris">← Dashboard</a>
        </div>
    </div>

    <div class="grid-stats">
        <div class="card-stat">
            <small>TOTAL PRODUCTOS</small>
            <h2><?= count($inventario) ?></h2>
        </div>
        <div class="card-stat">
            <small>UNIDADES EN STOCK</small>
            <h2><?= number_format($totalItems) ?></h2>
        </div>
        <div class="card-stat">
            <small>VALOR TOTAL (<?php echo $simbolo; ?>)</small>
            <h2><?php echo $simbolo; ?> <?= number_format($totalValor, 2) ?></h2>
        </div>
        <div class="card-stat alerta-stock">
            <small>BAJO STOCK (≤10)</small>
            <h2 style="color:#e74c3c;"><?= count($bajoStock) ?></h2>
        </div>
    </div>

    <?php if(count($bajoStock) > 0): ?>
    <div class="card-stat alerta-stock" style="margin-bottom: 25px;">
        <h4 style="margin:0 0 15px 0; color:#e74c3c;">⚠️ Productos con Stock Bajo</h4>
        <table>
            <tr><th>Producto</th><th>Stock</th><th>Precio</th></tr>
            <?php foreach($bajoStock as $p): ?>
            <tr>
                <td><?= $p['descripcion'] ?></td>
                <td><span class="badge-stock stock-critico"><?= $p['cantidad'] ?></span></td>
                <td><?php echo $simbolo; ?> <?= number_format($p['precio'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <div class="tabla-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>PRODUCTO</th>
                    <th>PRECIO (<?php echo $simbolo; ?>)</th>
                    <th>STOCK</th>
                    <th>VALOR (<?php echo $simbolo; ?>)</th>
                    <th>ESTADO</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($inventario as $item): 
                    $claseStock = $item['cantidad'] <= 5 ? 'stock-critico' : ($item['cantidad'] <= 10 ? 'stock-bajo' : 'stock-alto');
                    $textoStock = $item['cantidad'] <= 5 ? 'Crítico' : ($item['cantidad'] <= 10 ? 'Bajo' : 'OK');
                ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><strong><?= $item['descripcion'] ?></strong></td>
                    <td><?php echo $simbolo; ?> <?= number_format($item['precio'], 2) ?></td>
                    <td><?= $item['cantidad'] ?></td>
                    <td><?php echo $simbolo; ?> <?= number_format($item['cantidad'] * $item['precio'], 2) ?></td>
                    <td><span class="badge-stock <?= $claseStock ?>"><?= $textoStock ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
