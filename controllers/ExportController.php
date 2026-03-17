<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') { exit("Acceso denegado"); }

require_once "../config/Database.php";
require_once "../classes/Reporte.php";

$db = (new Database())->getConnection();
$reporteObj = new Reporte($db);

$type = $_GET['type'] ?? 'ventas';
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_" . ucfirst($type) . "_" . date('d-m-Y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
echo "\xEF\xBB\xBF"; 

if ($type === 'cajas') {
    $datos = $reporteObj->getHistorialCajas($desde, $hasta);
    ?>
    <table border="1">
        <tr style="background:#2c3e50; color:white;">
            <th>CAJERO</th><th>ESTADO</th><th>APERTURA</th><th>CIERRE</th>
            <th>M. ESPERADO</th><th>M. REAL</th><th>DIFERENCIA</th>
        </tr>
        <?php foreach ($datos as $c): 
            $dif = $c['monto_cierre'] - $c['monto_esperado']; ?>
            <tr>
                <td><?= strtoupper($c['nombre_usuario']) ?></td>
                <td><?= $c['estado'] ?></td>
                <td><?= $c['fecha_apertura'] ?></td>
                <td><?= $c['fecha_cierre'] ?? '---' ?></td>
                <td><?= number_format($c['monto_esperado'], 2) ?></td>
                <td><?= number_format($c['monto_cierre'], 2) ?></td>
                <td><?= ($c['estado'] == 'ABIERTA') ? '---' : number_format($dif, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
} else {
    $datos = $reporteObj->getVentas($desde, $hasta);
    ?>
    <table border="1">
        <tr style="background:#2c3e50; color:white;">
            <th>FECHA</th><th>CONCEPTO</th><th>SOCIO</th><th>MONTO</th><th>ESTADO</th><th>CAJERO</th>
        </tr>
        <?php foreach ($datos as $v): ?>
            <tr>
                <td><?= date('d/m/Y H:i', strtotime($v['fecha_venta'])) ?></td>
                <td><?= $v['concepto'] ?></td>
                <td><?= $v['nombre'] ? $v['nombre']." ".$v['apellido'] : 'General' ?></td>
                <td><?= number_format($v['monto_total'], 2) ?></td>
                <td><?= $v['estado'] ?></td>
                <td><?= strtoupper($v['user_id']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}