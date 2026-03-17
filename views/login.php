<?php
session_start();
require_once "../config/Database.php";
require_once "../config/AppConfig.php";

// Inicializar conexión y configuración
$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

// Si ya inició sesión, lo mandamos al dashboard
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #555;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-login:hover {
            filter: brightness(1.1);
        }

        .alert-error {
            background: #fff5f5;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 20px;
            border-left: 5px solid #c53030;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <?php 
        $rutaLogo = "../public/img/" . $config['logo_ruta'];
        if (file_exists($rutaLogo) && !empty($config['logo_ruta'])): ?>
            <img src="<?php echo $rutaLogo; ?>" alt="Logo">
        <?php else: ?>
            <div style="font-size: 3rem; margin-bottom: 10px;">🏋️‍♂️</div>
        <?php endif; ?>
        
        <h2><?php echo $config['nombre_gym']; ?></h2>
        <p style="color: #7f8c8d; font-size: 0.9rem; margin-top: 5px;">Sistema de Gestión</p>
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
    
    <div style="text-align: center; margin-top: 25px;">
        <small style="color: #bdc3c7;">&copy; <?php echo date('Y'); ?> Versión 2.0</small>
    </div>
</div>

</body>
</html>