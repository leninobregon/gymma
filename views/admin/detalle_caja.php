<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../dashboard.php"); exit();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';

$id_caja = $_GET['id'] ?? null;
if (!$id_caja) { header("Location: historial_cajas.php"); exit(); }

// Datos de la Caja (Incluimos la tasa de apertura capturada)
$stmtCaja = $db->prepare("SELECT c.*, u.nombre FROM cajas c JOIN usuarios u ON c.id_usuario = u.id WHERE c.id = ?");
$stmtCaja->execute([$id_caja]);
$caja = $stmtCaja->fetch(PDO::FETCH_ASSOC);

// Ventas del turno (Usamos la tasa_cambio_momento que añadimos a la DB)
$stmtVentas = $db->prepare("SELECT * FROM ventas WHERE id_caja = ? ORDER BY fecha_venta DESC");
$stmtVentas->execute([$id_caja]);
$ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

// Calculamos totales bimoneda del turno
$totalVentasCords = 0;
$totalVentasUsd = 0;

foreach($ventas as $v) {
    if($v['estado'] !== 'ANULADO') {
        $totalVentasCords += $v['monto_total'];
        // Usamos la tasa guardada en la venta para la auditoría exacta
        $totalVentasUsd += ($v['monto_total'] / $v['tasa_cambio_momento']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría Turno #<?= $id_caja ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        .grid-detalle { display: grid; grid-template-columns: 350px 1fr; gap: 25px; }
        .card-info { background: #fff; padding: 25px; border-radius: 15px; border-top: 6px solid #3498db; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .monto-principal { font-size: 24px; color: #2c3e50; font-weight: bold; margin-bottom: 0; }
        .monto-secundario { font-size: 16px; color: #27ae60; font-weight: bold; margin-top: 0; }
        .tabla-ventas { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        .tabla-ventas th { background: #f8f9fa; color: #7f8c8d; padding: 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .ref-usd { color: #27ae60; font-size: 12px; display: block; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <header>
        <div class="logo"><h2><i class="fas fa-search-dollar"></i> Auditoría: <?= strtoupper($caja['nombre']); ?> (Caja #<?= $id_caja ?>)</h2></div>
        <a href="historial_cajas.php" class="btn-volver gris">← Volver</a>
    </header>

    <div class="dashboard-wrapper">
        <div class="grid-detalle">
            <div class="card-info">
                <h3 style="margin-top:0;">Resumen del Turno</h3>
                <p><b>Estado:</b> <?= ($caja['estado'] == 'ABIERTA') ? '🟢 ABIERTA' : '🔴 CERRADA' ?></p>
                <p><b>Tasa Aplicada:</b> C$ <?= number_format($caja['tasa_apertura'], 2) ?></p>
                <hr>
                
                <p style="margin-bottom:5px;"><b>Ventas Netas (Turno):</b></p>
                <p class="monto-principal">C$ <?= number_format($totalVentasCords, 2) ?></p>
                <p class="monto-secundario">$ <?= number_format($totalVentasUsd, 2) ?> USD</p>
                
                <hr>
                <p><b>Saldo Final en Caja:</b></p>
                <h2 style="color:#2ecc71; margin:5px 0;">C$ <?= number_format($caja['monto_esperado'], 2) ?></h2>
                <span class="ref-usd">Equiv. $ <?= number_format($caja['monto_esperado'] / $caja['tasa_apertura'], 2) ?> USD</span>

                <?php if($caja['nota']): ?>
                    <div style="background:#fff9c4; padding:15px; border-radius:8px; margin-top:20px; border:1px solid #fbc02d;">
                        <b>Nota del Cajero:</b><br><small><?= $caja['nota'] ?></small>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h3 style="margin-top:0;">Cronología de Transacciones</h3>
                <table class="tabla-ventas">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Concepto</th>
                            <th style="text-align:right;">Monto (C$)</th>
                            <th style="text-align:right;">Ref (USD)</th>
                            <th style="text-align:center;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ventas as $v): 
                            $esAnulado = ($v['estado'] == 'ANULADO');
                        ?>
                        <tr style="<?= $esAnulado ? 'opacity:0.5; background:#fdf2f2;' : '' ?>">
                            <td><?= date('h:i A', strtotime($v['fecha_venta'])) ?></td>
                            <td>
                                <strong><?= $v['concepto'] ?></strong><br>
                                <small style="color:#bdc3c7;">Tasa: C$ <?= $v['tasa_cambio_momento'] ?></small>
                            </td>
                            <td style="text-align:right; font-weight:bold;">
                                <?= $esAnulado ? '<strike>' : '' ?>
                                C$ <?= number_format($v['monto_total'], 2) ?>
                                <?= $esAnulado ? '</strike>' : '' ?>
                            </td>
                            <td style="text-align:right;">
                                <span class="ref-usd">$ <?= number_format($v['monto_total'] / $v['tasa_cambio_momento'], 2) ?></span>
                            </td>
                            <td style="text-align:center;">
                                <b style="color:<?= $esAnulado ? '#e74c3c' : '#2ecc71' ?>"><?= $v['estado'] ?></b>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>