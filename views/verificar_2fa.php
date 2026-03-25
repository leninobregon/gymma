<?php
session_start();
require_once "../config/Database.php";
require_once "../config/AppConfig.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();

$tema = $config['tema'] ?? 'default';

if ($tema === 'oscuro') {
    $bg = 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)';
    $card = '#1e1e1e';
    $text = '#e0e0e0';
    $border = '#e94560';
} elseif ($tema === 'darkblue') {
    $bg = 'linear-gradient(135deg, #0d1b2a 0%, #1b263b 100%)';
    $card = '#1b263b';
    $text = '#e0e0e0';
    $border = '#3498db';
} else {
    $bg = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    $card = '#ffffff';
    $text = '#333333';
    $border = '#667eea';
}

if (!isset($_SESSION['pending_2fa'])) {
    header("Location: login.php");
    exit();
}

$error = isset($_GET['error']) ? "PIN incorrecto. Intenta de nuevo." : "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar PIN - <?php echo $config['nombre_gym']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: <?php echo $bg; ?>;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .container {
            background: <?php echo $card; ?>;
            width: 100%;
            max-width: 400px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: <?php echo $border; ?>;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 { font-size: 1.5rem; font-weight: 700; }
        .header p { font-size: 0.9rem; opacity: 0.9; margin-top: 5px; }
        
        .body { padding: 30px; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: <?php echo $text; ?>;
            font-size: 0.85rem;
            text-align: center;
        }
        .input-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 8px;
            background: transparent;
            color: <?php echo $text; ?>;
        }
        .input-group input:focus {
            border-color: <?php echo $border; ?>;
            outline: none;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: <?php echo $border; ?>;
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .alert-error {
            background: #fee;
            color: #c00;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c00;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🔐 Verificar PIN</h1>
        <p>Ingresa tu PIN de seguridad</p>
    </div>
    
    <div class="body">
        <?php if($error): ?>
            <div class="alert-error">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../controllers/AuthController.php">
            <div class="input-group">
                <label>🔑 PIN (4-6 dígitos)</label>
                <input type="password" name="codigo_2fa" placeholder="••••" maxlength="6" required autofocus>
            </div>

            <button type="submit" name="btn_verificar_2fa" class="btn btn-primary">
                ✅ VERIFICAR
            </button>
            
            <a href="../controllers/AuthController.php?logout=1" class="btn btn-cancel">
                ⬅️ CANCELAR
            </a>
        </form>
    </div>
</div>

</body>
</html>