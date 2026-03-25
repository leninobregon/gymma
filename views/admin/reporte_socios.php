<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}
$tema = $_SESSION['tema'] ?? 'default';

require_once "../../config/Database.php";
require_once "../../classes/Reporte.php";

$db = (new Database())->getConnection();
$reporteObj = new Reporte($db);

$activos = $reporteObj->getSociosEstado('ACTIVO');
$porVencer = $reporteObj->getSociosPorVencer(7);
$vencidos = $reporteObj->getSociosVencidos();
$inactivos = $reporteObj->getSociosEstado('INACTIVO');

function diasRestantes($fecha) {
    return (strtotime($fecha) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Socios - GYM MA</title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        body { background: var(--bg-body); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px; color: var(--text-main); }
        .header { background: var(--bg-card); padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); }
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .card-stat { background: var(--bg-card); padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid #3498db; border: 1px solid var(--border-color); color: var(--text-main); }
        .card-stat h2 { margin: 0; font-size: 1.8rem; color: var(--text-main); }
        .card-estado { border-left-color: #27ae60; }
        .card-vencer { border-left-color: #f39c12; }
        .card-vencido { border-left-color: #e74c3c; }
        .card-inactivo { border-left-color: #95a5a6; }
        .seccion { background: var(--bg-card); padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid var(--border-color); color: var(--text-main); }
        .seccion h3 { margin: 0 0 15px 0; color: var(--text-main); }
        table { width: 100%; border-collapse: collapse; background: var(--bg-card); color: var(--text-main); }
        th { background: var(--header-bg); padding: 10px; text-align: left; font-size: 0.8rem; color: white; }
        td { padding: 10px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        .badge { padding: 3px 10px; border-radius: 15px; font-size: 11px; font-weight: bold; }
        .badge-activo { background: #eafaf1; color: #27ae60; }
        .badge-vencer { background: #fef5e7; color: #f39c12; }
        .badge-vencido { background: #fdecea; color: #e74c3c; }
        .badge-inactivo { background: #f4f6f7; color: #7f8c8d; }
        .btn-accion { padding: 8px 15px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold; background: var(--header-bg); color: white; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <div class="header">
        <h2><i class="fas fa-users"></i> Reporte de Socios</h2>
        <div style="display:flex; gap:10px;">
            <a href="../../controllers/ExportController.php?type=socios" class="btn-accion" style="background:#27ae60; color:white;"><i class="fas fa-file-excel"></i> EXCEL</a>
            <a href="../dashboard.php" class="btn-volver gris">← Dashboard</a>
        </div>
    </div>

    <div class="grid-stats">
        <div class="card-stat card-estado">
            <small>ACTIVOS</small>
            <h2 style="color:#27ae60;"><?= count($activos) ?></h2>
        </div>
        <div class="card-stat card-vencer">
            <small>POR VENCER (7 días)</small>
            <h2 style="color:#f39c12;"><?= count($porVencer) ?></h2>
        </div>
        <div class="card-stat card-vencido">
            <small>VENCIDOS</small>
            <h2 style="color:#e74c3c;"><?= count($vencidos) ?></h2>
        </div>
        <div class="card-stat card-inactivo">
            <small>INACTIVOS</small>
            <h2 style="color:#7f8c8d;"><?= count($inactivos) ?></h2>
        </div>
    </div>

    <?php if(count($porVencer) > 0): ?>
    <div class="seccion" style="border-left: 4px solid #f39c12;">
        <h3>⚠️ Por Vencer (próximos 7 días)</h3>
        <table>
            <tr><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Plan</th><th>Vence</th><th>Días</th></tr>
            <?php foreach($porVencer as $s): $dias = diasRestantes($s['fecha_vencimiento']); ?>
            <tr>
                <td><?= $s['nombre'] . ' ' . $s['apellido'] ?></td>
                <td><?= $s['cedula'] ?></td>
                <td><?= $s['telefono'] ?></td>
                <td><?= $s['nombre_plan'] ?? 'N/A' ?></td>
                <td><?= date('d/m/Y', strtotime($s['fecha_vencimiento'])) ?></td>
                <td><span class="badge badge-vencer"><?= $dias ?> días</span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <?php if(count($vencidos) > 0): ?>
    <div class="seccion" style="border-left: 4px solid #e74c3c;">
        <h3>❌ Vencidos</h3>
        <table>
            <tr><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Plan</th><th>Venció</th><th>Días</th></tr>
            <?php foreach($vencidos as $s): $dias = abs(diasRestantes($s['fecha_vencimiento'])); ?>
            <tr>
                <td><?= $s['nombre'] . ' ' . $s['apellido'] ?></td>
                <td><?= $s['cedula'] ?></td>
                <td><?= $s['telefono'] ?></td>
                <td><?= $s['nombre_plan'] ?? 'N/A' ?></td>
                <td><?= date('d/m/Y', strtotime($s['fecha_vencimiento'])) ?></td>
                <td><span class="badge badge-vencido"><?= $dias ?> días</span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>

    <div class="seccion">
        <h3>✅ Socios Activos</h3>
        <table>
            <tr><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Plan</th><th>Ingreso</th><th>Vence</th></tr>
            <?php if(count($activos) == 0): ?>
            <tr><td colspan="6" style="text-align:center; color:#999;">No hay socios activos</td></tr>
            <?php else: ?>
            <?php foreach($activos as $s): ?>
            <tr>
                <td><?= $s['nombre'] . ' ' . $s['apellido'] ?></td>
                <td><?= $s['cedula'] ?></td>
                <td><?= $s['telefono'] ?></td>
                <td><?= $s['nombre_plan'] ?? 'N/A' ?></td>
                <td><?= date('d/m/Y', strtotime($s['fecha_ingreso'])) ?></td>
                <td><?= $s['fecha_vencimiento'] ? date('d/m/Y', strtotime($s['fecha_vencimiento'])) : 'N/A' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <?php if(count($inactivos) > 0): ?>
    <div class="seccion">
        <h3>⚫ Socios Inactivos</h3>
        <table>
            <tr><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Último Plan</th></tr>
            <?php foreach($inactivos as $s): ?>
            <tr>
                <td><?= $s['nombre'] . ' ' . $s['apellido'] ?></td>
                <td><?= $s['cedula'] ?></td>
                <td><?= $s['telefono'] ?></td>
                <td><?= $s['nombre_plan'] ?? 'N/A' ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
</body>
</html>
