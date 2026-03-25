<?php
session_start();
require_once "../config/Database.php";
require_once "../config/AppConfig.php";
require_once "../classes/Usuario.php";
require_once "../classes/TOTP.php";

$database = new Database();
$db = $database->getConnection();

try {
    $stmt = $db->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('two_factor_pin', $columns)) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN two_factor_pin VARCHAR(10) DEFAULT NULL");
    }
    if (!in_array('two_factor_enabled', $columns)) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0");
    }
} catch (PDOException $e) {}

if (isset($_POST['btn_login'])) {
    $database = new Database();
    $db = $database->getConnection();
    $userObj = new Usuario($db);

    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $auth = $userObj->login($usuario, $password);

    if ($auth) {
        $dbCheck = $database->getConnection();
        
        try {
            $stmtCol = $dbCheck->query("SHOW COLUMNS FROM usuarios LIKE 'two_factor_enabled'");
            $has2FA = $stmtCol->rowCount() > 0;
            
            if ($has2FA) {
                $stmt2FA = $dbCheck->prepare("SELECT two_factor_enabled, two_factor_pin FROM usuarios WHERE id = ?");
                $stmt2FA->execute([$auth['id']]);
                $user2FA = $stmt2FA->fetch(PDO::FETCH_ASSOC);
                
                $auth['two_factor_enabled'] = $user2FA['two_factor_enabled'] ?? 0;
                $auth['two_factor_pin'] = $user2FA['two_factor_pin'] ?? '';
            } else {
                $auth['two_factor_enabled'] = 0;
                $auth['two_factor_pin'] = '';
            }
        } catch (Exception $e) {
            $auth['two_factor_enabled'] = 0;
            $auth['two_factor_pin'] = '';
        }
        
        if (!empty($auth['two_factor_enabled']) && !empty($auth['two_factor_pin'])) {
            $_SESSION['user_id'] = $auth['id'];
            $_SESSION['nombre'] = $auth['nombre'];
            $_SESSION['usuario'] = $auth['usuario'];
            $_SESSION['rol'] = $auth['rol'];
            $_SESSION['pending_2fa'] = true;
            
            $config = (new AppConfig($database->getConnection()))->obtenerConfig();
            $_SESSION['tema'] = $config['tema'] ?? 'default';
            
            header("Location: ../views/verificar_2fa.php");
        } else {
            $_SESSION['user_id'] = $auth['id'];
            $_SESSION['nombre']  = $auth['nombre'];
            $_SESSION['usuario'] = $auth['usuario'];
            $_SESSION['rol']     = $auth['rol'];
            
            $config = (new AppConfig($database->getConnection()))->obtenerConfig();
            $_SESSION['tema'] = $config['tema'] ?? 'default';
            
            header("Location: ../views/dashboard.php");
        }
        exit();
    } else {
        header("Location: ../views/login.php?error=1");
        exit();
    }
}

if (isset($_POST['btn_verificar_2fa'])) {
    $pin = trim($_POST['codigo_2fa']);
    $user_id = $_SESSION['user_id'] ?? 0;
    
    if (empty($pin)) {
        header("Location: ../views/verificar_2fa.php?error=1");
        exit();
    }
    
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT two_factor_pin FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && !empty($user['two_factor_pin']) && $user['two_factor_pin'] === $pin) {
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id]);
        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $_SESSION['user_id'] = $auth['id'];
        $_SESSION['nombre'] = $auth['nombre'];
        $_SESSION['usuario'] = $auth['usuario'];
        $_SESSION['rol'] = $auth['rol'];
        $_SESSION['pending_2fa'] = false;
        
        $config = (new AppConfig($db))->obtenerConfig();
        $_SESSION['tema'] = $config['tema'] ?? 'default';
        
        header("Location: ../views/dashboard.php");
        exit();
    }
    
    header("Location: ../views/verificar_2fa.php?error=1");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../views/login.php");
    exit();
}