<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/Database.php";
require_once "../../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';

$bg_color = '#f4f4f4';
$text_color = '#333333';
$card_bg = '#ffffff';
$border_color = '#dddddd';
$header_bg = '#2c3e50';

if ($tema === 'oscuro') {
    $bg_color = '#121212';
    $text_color = '#e0e0e0';
    $card_bg = '#1e1e1e';
    $border_color = '#333333';
    $header_bg = '#1a1a2e';
} elseif ($tema === 'darkblue') {
    $bg_color = '#0d1b2a';
    $text_color = '#e0e0e0';
    $card_bg = '#1b263b';
    $border_color = '#334155';
    $header_bg = '#0d1b2a';
}
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
        * { box-sizing: border-box; }
        body { 
            background-color: <?php echo $bg_color; ?>; 
            color: <?php echo $text_color; ?>;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        header { 
            background: <?php echo $header_bg; ?>; 
            color: white; 
            padding: 15px 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .dashboard-wrapper { 
            padding: 20px; 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .card, .stat-card, .actions-box, .chart-box, .tabla-container, .seccion, .filtro-container, .grid-stats, .card-stat, .login-card { 
            background: <?php echo $card_bg; ?>; 
            color: <?php echo $text_color; ?>;
            border: 1px solid <?php echo $border_color; ?>;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: <?php echo $card_bg; ?>; 
            color: <?php echo $text_color; ?>;
        }
        th { 
            background: <?php echo $header_bg; ?>; 
            color: white; 
            padding: 12px; 
            text-align: left; 
        }
        td { 
            padding: 12px; 
            border-bottom: 1px solid <?php echo $border_color; ?>; 
        }
        input, select, textarea { 
            background: <?php echo $card_bg; ?>; 
            color: <?php echo $text_color; ?>;
            border: 1px solid <?php echo $border_color; ?>;
            padding: 10px;
            border-radius: 6px;
        }
        label { 
            color: <?php echo $text_color; ?>;
            font-weight: bold;
        }
        h1, h2, h3, h4 { color: <?php echo $text_color; ?>; }
        a { color: <?php echo ($tema !== 'default') ? '#3498db' : '#2980b9'; ?>; }
        .btn-accion {
            background: <?php echo $header_bg; ?>;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
        .btn-accion:hover { opacity: 0.9; }
    </style>
</head>
<body>
<header>
    <div style="display:flex; align-items:center; gap:10px;">
        <img src="../../public/img/<?php echo $config['logo_ruta'] ?? 'logo_principal.png'; ?>" width="35" style="border-radius:50%;">
        <h2 style="margin:0; font-size:1rem;"><?php echo $config['nombre_gym']; ?></h2>
    </div>
    <div style="display:flex; align-items:center; gap:15px; font-size:0.8rem;">
        <span><i class="fas fa-user"></i> <?php echo strtoupper($_SESSION['usuario'] ?? ''); ?></span>
        <a href="../../controllers/AuthController.php?logout=1" class="btn-logout">Salir</a>
    </div>
</header>
<div class="dashboard-wrapper">
