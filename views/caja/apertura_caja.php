<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit(); }

require_once "../../config/Database.php";
$db = (new Database())->getConnection();

// Verificar si ya existe una caja abierta para evitar el bucle
$stmt = $db->prepare("SELECT id FROM cajas WHERE estado = 'ABIERTA' AND id_usuario = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$cajaExistente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cajaExistente) {
    $_SESSION['id_caja'] = $cajaExistente['id'];
    header("Location: punto_venta.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Apertura de Caja - GYM MA</title>
    <style>
        body { background: #1a1a1a; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; color: white; }
        .card { background: #2d3436; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); width: 350px; text-align: center; }
        input { width: 100%; padding: 15px; font-size: 1.5rem; text-align: center; border: none; border-radius: 8px; margin: 20px 0; background: #eee; color: #000; font-weight: bold; }
        .btn { background: #27ae60; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; box-shadow: 0 4px 0 #1e8449; }
        .btn:active { transform: translateY(3px); box-shadow: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>💰 APERTURA</h2>
        <p>Monto inicial en caja</p>
        <form action="../../controllers/CajaController.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="number" name="monto_apertura" step="0.01" value="0.00" required autofocus onfocus="this.select()">
            <button type="submit" name="btn_abrir_caja" class="btn">INICIAR TURNO</button>
        </form>
        <br><a href="../dashboard.php" style="color: #aaa; text-decoration: none; font-size: 0.8rem;">Regresar</a>
    </div>
</body>
</html>