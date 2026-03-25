<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once "../config/Database.php";
require_once "../config/AppConfig.php";
require_once "../classes/Usuario.php";

$db = (new Database())->getConnection();
$config = (new AppConfig($db))->obtenerConfig();
$userObj = new Usuario($db);
$user = $userObj->obtenerPorId($_SESSION['user_id']);

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

$message = "";
$error = "";

if (isset($_POST['activar_2fa'])) {
    $pin = trim($_POST['pin']);
    
    if (strlen($pin) >= 4 && strlen($pin) <= 6) {
        $stmt = $db->prepare("UPDATE usuarios SET two_factor_enabled = 1, two_factor_pin = ? WHERE id = ?");
        $stmt->execute([$pin, $_SESSION['user_id']]);
        $user = $userObj->obtenerPorId($_SESSION['user_id']);
        $message = "✅ 2FA ACTIVADO con PIN: $pin";
    } else {
        $error = "⚠️ El PIN debe tener entre 4 y 6 dígitos";
    }
}

if (isset($_POST['desactivar_2fa'])) {
    $stmt = $db->prepare("UPDATE usuarios SET two_factor_enabled = 0, two_factor_pin = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $userObj->obtenerPorId($_SESSION['user_id']);
    $message = "❌ 2FA DESACTIVADO";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA - <?php echo $config['nombre_gym']; ?></title>
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
            padding: 25px 20px;
            text-align: center;
        }
        .header h1 { font-size: 1.3rem; font-weight: 700; margin: 0; }
        
        .body { padding: 30px; }
        
        .status {
            text-align: center;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .status-on { background: #d4edda; color: #155724; }
        .status-off { background: #f8d7da; color: #721c24; }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: <?php echo $text; ?>;
            font-size: 0.85rem;
        }
        .input-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 5px;
            background: transparent;
            color: <?php echo $text; ?>;
        }
        .input-group input:focus {
            border-color: <?php echo $border; ?>;
            outline: none;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        
        .info {
            background: rgba(0,0,0,0.05);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: <?php echo $text; ?>;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🔐 Autenticación de Dos Factores (PIN)</h1>
    </div>
    
    <div class="body">
        <div class="status <?php echo $user['two_factor_enabled'] ? 'status-on' : 'status-off'; ?>">
            <?php echo $user['two_factor_enabled'] ? '🔒 ACTIVADO' : '🔓 DESACTIVADO'; ?>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if(!$user['two_factor_enabled']): ?>
            <div class="info">
                <strong>¿Cómo funciona?</strong><br>
                Después de iniciar sesión, se te pediré un PIN de 4-6 dígitos que tú defines.
            </div>
            <form method="POST">
                <div class="input-group">
                    <label>🔑 Define tu PIN (4-6 dígitos)</label>
                    <input type="password" name="pin" maxlength="6" placeholder="1234" required>
                </div>
                <button type="submit" name="activar_2fa" class="btn btn-success">
                    ✅ ACTIVAR 2FA
                </button>
            </form>
        <?php else: ?>
            <div class="info">
                <strong>Tu PIN está activo.</strong><br>
                Después de iniciar sesión, ingresa tu PIN para acceder al sistema.
            </div>
            <form method="POST">
                <button type="submit" name="desactivar_2fa" class="btn btn-danger" onclick="return confirm('¿Desactivar 2FA?')">
                    ❌ DESACTIVAR 2FA
                </button>
            </form>
        <?php endif; ?>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
        
        <a href="dashboard.php" class="btn btn-secondary" style="display: block; text-align: center; text-decoration: none;">
            ⬅️ Volver
        </a>
    </div>
</div>

</body>
</html>