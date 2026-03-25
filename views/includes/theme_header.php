<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

$tema = $_SESSION['tema'] ?? $config['tema'] ?? 'default';

$bg_body = '#f4f4f4';
$bg_card = '#ffffff';
$text_main = '#333333';
$text_muted = '#666666';
$border_color = '#dddddd';
$header_bg = '#2c3e50';
$input_bg = '#ffffff';
$input_text = '#333333';
$input_border = '#cccccc';
$link_color = '#2980b9';

if ($tema === 'oscuro') {
    $bg_body = '#121212';
    $bg_card = '#1e1e1e';
    $text_main = '#e0e0e0';
    $text_muted = '#aaaaaa';
    $border_color = '#333333';
    $header_bg = '#1a1a2e';
    $input_bg = '#2a2a2a';
    $input_text = '#e0e0e0';
    $input_border = '#444444';
    $link_color = '#3498db';
} elseif ($tema === 'darkblue') {
    $bg_body = '#0d1b2a';
    $bg_card = '#1b263b';
    $text_main = '#e0e0e0';
    $text_muted = '#94a3b8';
    $border_color = '#334155';
    $header_bg = '#0d1b2a';
    $input_bg = '#0d1b2a';
    $input_text = '#e0e0e0';
    $input_border = '#334155';
    $link_color = '#3498db';
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
            background-color: <?php echo $bg_body; ?>; 
            color: <?php echo $text_main; ?>;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            min-height: 100vh;
        }
        header { 
            background: <?php echo $header_bg; ?>; 
            color: white; 
            padding: 15px 25px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .header-title { display: flex; align-items: center; gap: 12px; }
        .header-title img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.3); }
        .header-title h2 { margin: 0; font-size: 1.1rem; font-weight: 600; }
        .header-user { display: flex; align-items: center; gap: 15px; font-size: 0.85rem; }
        
        .btn-menu {
            display: inline-block;
            padding: 10px 18px;
            background: <?php echo $header_bg; ?>;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        .btn-menu:hover { 
            filter: brightness(1.15); 
            transform: translateY(-1px);
        }
        .btn-menu.verde { background: #27ae60; }
        .btn-menu.rojo { background: #e74c3c; }
        .btn-menu.naranja { background: #e67e22; }
        .btn-menu.morado { background: #8e44ad; }
        .btn-menu.azul { background: #3498db; }
        .btn-menu.amarillo { background: #f39c12; }
        .btn-menu.gris { background: #7f8c8d; }
        
        .btn-volver {
            display: inline-block;
            padding: 8px 15px;
            background: <?php echo $header_bg; ?>;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .dashboard-wrapper { 
            padding: 25px; 
            max-width: 1400px; 
            margin: 0 auto; 
        }
        
        .card, .stat-card, .actions-box, .chart-box, .tabla-container, 
        .seccion, .filtro-container, .grid-stats, .card-stat, .login-card,
        .stat-card, .recaudado-header, .calculadora-box {
            background: <?php echo $bg_card; ?>; 
            color: <?php echo $text_main; ?>;
            border: 1px solid <?php echo $border_color; ?>;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: <?php echo $bg_card; ?>; 
            color: <?php echo $text_main; ?>;
            border-radius: 8px;
            overflow: hidden;
        }
        th { 
            background: <?php echo $header_bg; ?>; 
            color: white; 
            padding: 14px 12px; 
            text-align: left; 
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        td { 
            padding: 12px; 
            border-bottom: 1px solid <?php echo $border_color; ?>; 
            font-size: 0.9rem;
        }
        tr:hover { background: rgba(0,0,0,0.03); }
        
        input, select, textarea { 
            background: <?php echo $input_bg; ?>; 
            color: <?php echo $input_text; ?>;
            border: 1px solid <?php echo $input_border; ?>;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.95rem;
            width: 100%;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: <?php echo $link_color; ?>;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        label { 
            color: <?php echo $text_muted; ?>;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 6px;
            display: block;
        }
        
        h1, h2, h3, h4 { 
            color: <?php echo $text_main; ?>;
            margin-top: 0;
        }
        
        a { 
            color: <?php echo $link_color; ?>;
            text-decoration: none;
        }
        a:hover { text-decoration: underline; }
        
        .stats-container { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 15px; 
            margin-bottom: 20px; 
        }
        
        .stat-card { 
            padding: 18px; 
            border-radius: 10px; 
            color: white; 
            transition: transform 0.2s ease;
            text-decoration: none;
            display: block;
        }
        .stat-card:hover { 
            transform: translateY(-3px); 
            opacity: 0.95;
        }
        
        .grid-stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 25px; 
        }
        
        .filtro-container { 
            display: flex; 
            align-items: flex-end; 
            gap: 15px; 
            flex-wrap: wrap;
            padding: 20px;
        }
        
        .form-group { margin-bottom: 15px; }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-activo { background: #eafaf1; color: #27ae60; }
        .badge-vencer { background: #fef5e7; color: #f39c12; }
        .badge-vencido { background: #fdecea; color: #e74c3c; }
        .badge-inactivo { background: #f4f6f7; color: #7f8c8d; }
        .badge-abierta { background: #eafaf1; color: #27ae60; }
        .badge-cerrada { background: #f4f6f7; color: #7f8c8d; }
        
        body.tema-oscuro .badge-activo,
        body.tema-darkblue .badge-activo { background: rgba(39, 174, 96, 0.2); color: #2ecc71; }
        body.tema-oscuro .badge-vencer,
        body.tema-darkblue .badge-vencer { background: rgba(243, 156, 18, 0.2); color: #f39c12; }
        body.tema-oscuro .badge-vencido,
        body.tema-darkblue .badge-vencido { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        body.tema-oscuro .badge-inactivo,
        body.tema-darkblue .badge-inactivo { background: rgba(127, 140, 141, 0.2); color: #95a5a6; }
        
        .acciones-cell { display: flex; gap: 5px; justify-content: center; }
        .btn-small {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            text-decoration: none;
            font-weight: 600;
        }
        
        .alert-success { 
            background: #eafaf1; 
            color: #27ae60; 
            padding: 15px; 
            border-radius: 8px; 
            border-left: 4px solid #27ae60;
            margin-bottom: 20px;
        }
        .alert-error { 
            background: #fdecea; 
            color: #e74c3c; 
            padding: 15px; 
            border-radius: 8px; 
            border-left: 4px solid #e74c3c;
            margin-bottom: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: <?php echo $text_main; ?>;
            margin: 0;
        }
    </style>
</head>
<body>
<header>
    <div class="header-title">
        <img src="../../public/img/<?php echo $config['logo_ruta'] ?? 'logo_principal.png'; ?>" alt="Logo">
        <h2 class="gym-nombre"><?php echo $config['nombre_gym']; ?></h2>
    </div>
    <div class="header-user">
        <span><i class="fas fa-user"></i> <?php echo strtoupper($_SESSION['usuario'] ?? ''); ?></span>
        <a href="../../controllers/AuthController.php?logout=1" class="btn-menu rojo">Cerrar Sesión</a>
    </div>
</header>
<div class="dashboard-wrapper">
