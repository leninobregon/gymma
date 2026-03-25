<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

if (!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = $config['tema'] ?? 'default';
}

$tema_class = ($_SESSION['tema'] !== 'default') ? 'tema-' . $_SESSION['tema'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $config['nombre_gym'] ?? 'GYM'; ?></title>
    <link rel="stylesheet" href="../../public/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-wrapper { padding: 20px; max-width: 1200px; margin: 0 auto; }
        body { margin: 0; font-family: 'Segoe UI', sans-serif; }
        header { background: var(--header-bg, #2c3e50); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .btn-accion { padding: 8px 15px; border-radius: 5px; color: white; text-decoration: none; font-weight: bold; display: inline-block; }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin-bottom: 15px; }
        .stat-card { padding: 15px; border-radius: 10px; color: white; text-decoration: none; display: block; }
        .card, .stat-card, .actions-box, .chart-box, .tabla-container { background: var(--bg-card, white); color: var(--text-main, #333); padding: 15px; border-radius: 10px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: var(--bg-card, white); }
        th { background: var(--header-bg, #2c3e50); color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid var(--border-color, #ddd); }
    </style>
</head>
<body class="<?php echo $tema_class; ?>">
<header>
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="../../public/img/<?php echo $config['logo_ruta'] ?? 'logo_principal.png'; ?>" width="35" style="border-radius:50%;">
        <h2 style="margin:0; font-size:1rem;"><?php echo $config['nombre_gym']; ?></h2>
    </div>
    <div style="display:flex; align-items:center; gap:15px;">
        <a href="../configurar_2fa.php" class="btn-accion" style="background:#6c757d;" title="Configurar 2FA">
            <i class="fas fa-shield-halved"></i>
        </a>
        <span style="font-size:0.8rem;"><i class="fas fa-user"></i> <?php echo strtoupper($_SESSION['usuario'] ?? ''); ?></span>
        <a href="../../controllers/AuthController.php?logout=1" class="btn-logout">Salir</a>
    </div>
</header>
<div class="dashboard-wrapper">