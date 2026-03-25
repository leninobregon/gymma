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
} elseif ($type === 'inventario') {
    $datos = $reporteObj->getInventarioCompleto();
    ?>
    <table border="1">
        <tr style="background:#2c3e50; color:white;">
            <th>ID</th><th>PRODUCTO</th><th>PRECIO</th><th>STOCK</th><th>VALOR TOTAL</th><th>ESTADO</th>
        </tr>
        <?php foreach ($datos as $p): 
            $estado = $p['cantidad'] <= 5 ? 'CRITICO' : ($p['cantidad'] <= 10 ? 'BAJO' : 'OK'); ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['descripcion'] ?></td>
                <td><?= number_format($p['precio'], 2) ?></td>
                <td><?= $p['cantidad'] ?></td>
                <td><?= number_format($p['cantidad'] * $p['precio'], 2) ?></td>
                <td><?= $estado ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
} elseif ($type === 'socios') {
    $activos = $reporteObj->getSociosEstado('ACTIVO');
    $inactivos = $reporteObj->getSociosEstado('INACTIVO');
    $vencidos = $reporteObj->getSociosVencidos();
    ?>
    <table border="1">
        <tr style="background:#27ae60; color:white;"><th colspan="6">SOCIOS ACTIVOS</th></tr>
        <tr style="background:#2c3e50; color:white;">
            <th>NOMBRE</th><th>CEDULA</th><th>TELEFONO</th><th>PLAN</th><th>INGRESO</th><th>VENCIMIENTO</th>
        </tr>
        <?php foreach ($activos as $s): ?>
            <tr>
                <td><?= $s['nombre'].' '.$s['apellido'] ?></td>
                <td><?= $s['cedula'] ?></td>
                <td><?= $s['telefono'] ?></td>
                <td><?= $s['nombre_plan'] ?? 'N/A' ?></td>
                <td><?= $s['fecha_ingreso'] ?></td>
                <td><?= $s['fecha_vencimiento'] ?? 'N/A' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <table border="1">
        <tr style="background:#e74c3c; color:white;"><th colspan="6">SOCIOS VENCIDOS</th></tr>
        <tr style="background:#2c3e50; color:white;">
            <th>NOMBRE</th><th>CEDULA</th><th>TELEFONO</th><th>PLAN</th><th>INGRESO</th><th>VENCIMIENTO</th>
        </tr>
        <?php foreach ($vencidos as $s): ?>
            <tr>
                <td><?= $s['nombre'].' '.$s['apellido'] ?></td>
                <td><?= $s['cedula'] ?></td>
                <td><?= $s['telefono'] ?></td>
                <td><?= $s['nombre_plan'] ?? 'N/A' ?></td>
                <td><?= $s['fecha_ingreso'] ?></td>
                <td><?= $s['fecha_vencimiento'] ?? 'N/A' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <table border="1">
        <tr style="background:#7f8c8d; color:white;"><th colspan="6">SOCIOS INACTIVOS</th></tr>
        <tr style="background:#2c3e50; color:white;">
            <th>NOMBRE</th><th>CEDULA</th><th>TELEFONO</th><th>PLAN</th><th>INGRESO</th><th>VENCIMIENTO</th>
        </tr>
        <?php foreach ($inactivos as $s): ?>
            <tr>
                <td><?= $s['nombre'].' '.$s['apellido'] ?></td>
                <td><?= $s['cedula'] ?></td>
                <td><?= $s['telefono'] ?></td>
                <td><?= $s['nombre_plan'] ?? 'N/A' ?></td>
                <td><?= $s['fecha_ingreso'] ?></td>
                <td><?= $s['fecha_vencimiento'] ?? 'N/A' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
} elseif ($type === 'ingresos') {
    $datos = $reporteObj->getIngresosEgresos($desde, $hasta);
    $categoria = $reporteObj->getIngresosPorCategoria($desde, $hasta);
    ?>
    <table border="1">
        <tr style="background:#2c3e50; color:white;"><th colspan="2">RESUMEN PERIODO: <?= $desde ?> AL <?= $hasta ?></th></tr>
        <tr><td><strong>INGRESOS TOTALES</strong></td><td><?= number_format($datos['ingresos'], 2) ?></td></tr>
        <tr><td><strong>EGRESOS TOTALES</strong></td><td><?= number_format($datos['egresos'], 2) ?></td></tr>
        <tr><td><strong>BALANCE</strong></td><td><?= number_format($datos['balance'], 2) ?></td></tr>
    </table>
    <br>
    <table border="1">
        <tr style="background:#27ae60; color:white;"><th colspan="2">POR MÉTODO DE PAGO</th></tr>
        <tr style="background:#2c3e50; color:white;"><th>MÉTODO</th><th>TOTAL</th></tr>
        <?php foreach ($datos['por_metodo'] as $m): ?>
            <tr><td><?= $m['metodo_pago'] ?></td><td><?= number_format($m['total'], 2) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <br>
    <table border="1">
        <tr style="background:#3498db; color:white;"><th colspan="2">POR CATEGORÍA</th></tr>
        <tr style="background:#2c3e50; color:white;"><th>CATEGORÍA</th><th>TOTAL</th></tr>
        <?php foreach ($categoria as $c): ?>
            <tr><td><?= $c['categoria'] ?></td><td><?= number_format($c['total'], 2) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php
} elseif ($type === 'cajeros') {
    $datos = $reporteObj->getRendimientoCajero($desde, $hasta);
    ?>
    <table border="1">
        <tr style="background:#2c3e50; color:white;">
            <th>EMPLEADO</th><th>ROL</th><th>VENTAS</th><th>RECAUDADO</th><th>ANULADO</th>
        </tr>
        <?php foreach ($datos as $c): ?>
        <tr>
            <td><?= strtoupper($c['usuario']) ?></td>
            <td><?= $c['rol'] ?></td>
            <td><?= $c['total_ventas'] ?></td>
            <td><?= number_format($c['total_recaudado'], 2) ?></td>
            <td><?= number_format($c['total_anulado'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php
} elseif ($type === 'clientes') {
    $datos = $reporteObj->getClientesFrecuentes(20);
    ?>
    <table border="1">
        <tr style="background:#2c3e50; color:white;">
            <th>#</th><th>CLIENTE</th><th>TELÉFONO</th><th>COMPRAS</th><th>TOTAL GASTADO</th>
        </tr>
        <?php $i=1; foreach ($datos as $c): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= $c['nombre'].' '.$c['apellido'] ?></td>
            <td><?= $c['telefono'] ?></td>
            <td><?= $c['num_compras'] ?></td>
            <td><?= number_format($c['total_gastado'], 2) ?></td>
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