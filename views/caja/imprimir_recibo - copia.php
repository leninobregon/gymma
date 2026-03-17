<?php
require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

$id_venta = $_GET['id'] ?? 0;

// Obtener datos reales de la venta
$stmt = $db->prepare("SELECT v.*, s.nombre, s.apellido FROM ventas v LEFT JOIN socios s ON v.id_socio = s.id WHERE v.id = ?");
$stmt->execute([$id_venta]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) exit("Venta no encontrada");
?>
<style>
    @media print { .no-print { display: none; } }
    body { font-family: 'Courier New', monospace; width: 280px; margin: 0; padding: 10px; }
    .linea { border-top: 1px dashed #000; margin: 10px 0; }
</style>

<div id="ticket">
    <center>
        <strong style="font-size: 18px;"><?= strtoupper($config['nombre_gym']) ?></strong><br>
        <?= $config['direccion_gym'] ?><br>
        Tel: <?= $config['telefono_gym'] ?><br>
        <div class="linea"></div>
        RECIBO #<?= $id_venta ?><br>
        Fecha: <?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?><br>
        Cajero: ID-<?= $venta['id_usuario'] ?><br>
        <div class="linea"></div>
    </center>

    <table style="width: 100%;">
        <tr>
            <td colspan="2"><?= $venta['concepto'] ?></td>
        </tr>
        <tr>
            <td><strong>TOTAL:</strong></td>
            <td align="right"><strong>C$ <?= number_format($venta['monto_total'], 2) ?></strong></td>
        </tr>
    </table>

    <div class="linea"></div>
    Cliente: <?= $venta['nombre'] ? ($venta['nombre'].' '.$venta['apellido']) : 'Público General' ?><br>
    Pago: <?= $venta['metodo_pago'] ?><br>
    
    <center>
        <br>¡Gracias por su entrenamiento!<br>
        <strong>GYM PRO SYSTEM</strong>
    </center>
</div>

<div class="no-print" style="margin-top:20px;">
    <button onclick="window.print()">Imprimir Ahora</button>
    <button onclick="window.close()">Cerrar</button>
</div>

<script>window.onload = function() { window.print(); }</script>