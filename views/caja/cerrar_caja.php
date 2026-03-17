<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['id_caja'])) { header("Location: punto_venta.php"); exit(); }

require_once "../../config/Database.php";
$db = (new Database())->getConnection();

$id_caja = $_SESSION['id_caja'];

// Obtener datos de apertura y sumar ventas
$stmt = $db->prepare("SELECT * FROM cajas WHERE id = ?"); $stmt->execute([$id_caja]);
$caja = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtV = $db->prepare("SELECT SUM(monto_total) as total FROM ventas WHERE id_caja = ? AND estado != 'ANULADO'");
$stmtV->execute([$id_caja]);
$totalVentas = $stmtV->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$esperado = $caja['monto_apertura'] + $totalVentas;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja - GYM MA</title>
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 400px; border-top: 8px solid #d63031; }
        .fila { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: bold; }
        input { width: 100%; padding: 12px; border: 2px solid #000; border-radius: 8px; margin-top: 5px; box-sizing: border-box; font-size: 1.2rem; text-align: center; }
        .btn-cerrar { background: #d63031; color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="text-align:center;">🔒 CERRAR TURNO</h2>
        <div class="fila"><span>Apertura:</span> <span>C$ <?=number_format($caja['monto_apertura'], 2)?></span></div>
        <div class="fila"><span>Ventas:</span> <span>C$ <?=number_format($totalVentas, 2)?></span></div>
        <hr>
        <div class="fila" style="color: #27ae60; font-size: 1.2rem;"><span>DEBE HABER:</span> <span>C$ <?=number_format($esperado, 2)?></span></div>

        <form action="../../controllers/CajaController.php" method="POST">
            <input type="hidden" name="id_caja" value="<?=$id_caja?>">
            <label style="display:block; margin-top:20px;"><b>EFECTIVO EN GAVETA:</b></label>
            <input type="number" name="monto_cierre" step="0.01" required placeholder="0.00" autofocus>
            <textarea name="nota" style="width:100%; margin-top:10px;" placeholder="Notas..."></textarea>
            <button type="submit" name="btn_cerrar_caja" class="btn-cerrar" onclick="return confirm('¿Finalizar turno?')">CERRAR CAJA AHORA</button>
        </form>
    </div>
</body>
</html>