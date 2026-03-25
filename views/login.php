<?php
session_start();
require_once "../config/Database.php";
require_once "../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

$tema = $config['tema'] ?? 'default';
$_SESSION['tema'] = $tema;

$bg_color = '#f4f4f4';
$card_bg = '#ffffff';
$text_color = '#333333';
$label_color = '#555555';
$input_bg = '#ffffff';
$input_text = '#333333';
$input_border = '#dddddd';
$border_top = '#27ae60';

if ($tema === 'oscuro') {
    $bg_color = '#121212';
    $card_bg = '#1e1e1e';
    $text_color = '#e0e0e0';
    $label_color = '#aaaaaa';
    $input_bg = '#2a2a2a';
    $input_text = '#e0e0e0';
    $input_border = '#444444';
} elseif ($tema === 'darkblue') {
    $bg_color = '#0d1b2a';
    $card_bg = '#1b263b';
    $text_color = '#e0e0e0';
    $label_color = '#94a3b8';
    $input_bg = '#0d1b2a';
    $input_text = '#e0e0e0';
    $input_border = '#334155';
    $border_top = '#3498db';
}

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | <?php echo $config['nombre_gym']; ?></title>
    <link rel="stylesheet" href="../public/css/estilos.css">
    <style>
        body {
            background-color: var(--bg-color); /* Color de fondo del dashboard */
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            border-top: 5px solid var(--primary); /* Línea superior como en el dashboard */
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header img {
            max-width: 120px;
            margin-bottom: 15px;
        }

        .login-header h2 {
            color: var(--secondary);
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: <?php echo $label_color; ?>; font-size: 0.85rem; text-transform: uppercase; }
        .form-control { width: 100%; padding: 12px; border: 1px solid <?php echo $input_border; ?>; border-radius: 8px; box-sizing: border-box; font-size: 1rem; background: <?php echo $input_bg; ?>; color: <?php echo $input_text; ?>; }
        .form-control:focus { border-color: <?php echo $border_top; ?>; outline: none; }
        .btn-login { width: 100%; padding: 14px; background: <?php echo $border_top; ?>; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-login:hover { filter: brightness(1.1); }
        .alert-error { background: #fdecea; color: #e74c3c; padding: 12px; border-radius: 8px; text-align: center; font-size: 0.9rem; margin-bottom: 20px; border-left: 4px solid #e74c3c; }
    </style>
</head>
<body style="background-color: <?php echo $bg_color; ?>; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; color: <?php echo $text_color; ?>;">

<div style="background: <?php echo $card_bg; ?>; width: 100%; max-width: 400px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border-top: 5px solid <?php echo $border_top; ?>;">
    <div class="login-header">
        <?php 
        $rutaLogo = "../public/img/" . $config['logo_ruta'];
        if (file_exists($rutaLogo) && !empty($config['logo_ruta'])): ?>
            <img src="<?php echo $rutaLogo; ?>" alt="Logo">
        <?php else: ?>
            <div style="font-size: 3rem; margin-bottom: 10px;">🏋️‍♂️</div>
        <?php endif; ?>
        
        <h2 style="text-align:center; margin:0 0 10px 0; font-size: 1.4rem; font-weight: 800; text-transform: uppercase; color: <?php echo $text_color; ?>;"><?php echo $config['nombre_gym']; ?></h2>
        <p style="color: <?php echo $label_color; ?>; font-size: 0.9rem; margin-top: 5px;">Sistema de Gestión</p>
    </div>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert-error">
            <strong>Acceso Denegado:</strong> Usuario o contraseña incorrectos.
        </div>
    <?php endif; ?>

    <form action="../controllers/AuthController.php" method="POST">
        <div class="form-group">
            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Nombre de usuario" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" name="btn_login" class="btn-login">
            ENTRAR AL PANEL
        </button>
    </form>
    
    <div style="text-align: center; margin-top: 25px; color: <?php echo $label_color; ?>;">
        <small>&copy; <?php echo date('Y'); ?> Versión 2.0</small>
    </div>
</div>

</body>
</html>