<?php
require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";
$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

$id_venta = $_GET['id'] ?? 0;

// La tasa de cambio la tomamos de la configuración del sistema
$tasa_bcn = $config['tasa_cambio'] ?? 36.65; 

// Obtener datos de la venta, el socio y el nombre del cajero
$stmt = $db->prepare("
    SELECT v.*, s.nombre as s_nom, s.apellido as s_ape, u.nombre as cajero_nom 
    FROM ventas v 
    LEFT JOIN socios s ON v.id_socio = s.id 
    LEFT JOIN usuarios u ON v.id_usuario = u.id 
    WHERE v.id = ?
");
$stmt->execute([$id_venta]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) exit("Venta no encontrada");

// Calculamos el monto en dólares para mostrarlo como referencia
$total_usd = $venta['monto_total'] / $tasa_bcn;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?= $id_venta ?></title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 280px; 
            margin: 0 auto; 
            padding: 5px; 
            color: #000;
            font-size: 12px;
            line-height: 1.2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .linea { border-top: 1px dashed #000; margin: 6px 0; }
        .header-gym { font-size: 15px; font-weight: bold; }
        .detalle-tabla { width: 100%; border-collapse: collapse; margin: 5px 0; }
        
        @media print { 
            .no-print { display: none; } 
            body { width: 100%; padding: 0; margin: 0; }
        }
        .btn { padding: 8px 15px; cursor: pointer; border-radius: 4px; border: none; font-weight: bold; margin: 5px; }
    </style>
</head>
<body>

<div id="ticket">
    <div class="text-center">
        <span class="header-gym"><?= strtoupper($config['nombre_gym']) ?></span><br>
        <span style="font-size: 10px;">
            <?= $config['direccion_gym'] ?><br>
            TEL: <?= $config['telefono_gym'] ?>
        </span>
        <div class="linea"></div>
        
        <span class="bold">TICKET DE VENTA #<?= str_pad($id_venta, 6, "0", STR_PAD_LEFT) ?></span><br>
        FECHA: <?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?><br>
        
        CAJERO: <?= strtoupper($venta['cajero_nom']) ?><br>
        T. CAMBIO: C$ <?= number_format($tasa_bcn, 2) ?><br>
        
        <div class="linea"></div>
    </div>

    <table class="detalle-tabla">
        <thead>
            <tr>
                <td class="bold">DESCRIPCIÓN</td>
                <td class="text-right bold">TOTAL</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 4px 0;"><?= $venta['concepto'] ?></td>
                <td class="text-right">C$ <?= number_format($venta['monto_total'], 2) ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr><td colspan="2"><div class="linea"></div></td></tr>
            <tr style="font-size: 14px;">
                <td class="bold">TOTAL NETO:</td>
                <td class="text-right bold">C$ <?= number_format($venta['monto_total'], 2) ?></td>
            </tr>
            <tr style="font-size: 12px; color: #333;">
                <td class="bold italic">EQUIV. USD:</td>
                <td class="text-right bold">$ <?= number_format($total_usd, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="linea"></div>
    
    <div style="font-size: 10px;">
        <strong>CLIENTE:</strong> <?= $venta['s_nom'] ? (strtoupper($venta['s_nom'].' '.$venta['s_ape'])) : 'PÚBLICO GENERAL' ?><br>
        <strong>PAGO:</strong> <?= strtoupper($venta['metodo_pago']) ?><br>
        <strong>ESTADO:</strong> <?= strtoupper($venta['estado']) ?>
    </div>
    
    <div class="text-center">
        <br>
        ¡GRACIAS POR ENTRENAR CON NOSOTROS!<br>
        <strong><?= strtoupper($config['nombre_gym']) ?></strong>
    </div>
</div>

<div class="no-print" style="margin-top:20px; text-align:center;">
    <button class="btn" style="background: #2ecc71; color: white;" onclick="window.print()">🖨️ IMPRIMIR</button>
    <button class="btn" style="background: #e74c3c; color: white;" onclick="window.close()">❌ CERRAR</button>
</div>

<script>
    // El ticket se dispara automáticamente al cargar
    window.onload = function() {
        setTimeout(function() { window.print(); }, 500);
    };
</script>

</body>
</html>