<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';

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
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 350px; text-align: center; }
        input { width: 100%; padding: 15px; font-size: 1.5rem; text-align: center; border-radius: 8px; margin: 20px 0; font-weight: bold; }
        .btn { background: var(--primary); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body class="<?php echo ($tema !== 'default') ? 'tema-' . $tema : ''; ?>">
    <div class="card">
        <h2><i class="fas fa-door-open"></i> APERTURA</h2>
        <p>Monto inicial en caja</p>
        <form action="../../controllers/CajaController.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="number" name="monto_apertura" step="0.01" value="0.00" required autofocus onfocus="this.select()">
            <button type="submit" name="btn_abrir_caja" class="btn">INICIAR TURNO</button>
        </form>
        <br><a href="../dashboard.php" class="btn-volver gris">← Dashboard</a>
    </div>
</body>
</html>