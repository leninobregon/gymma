<?php
session_start();
require_once "../config/Database.php";
require_once "../classes/Usuario.php";

if (isset($_POST['btn_login'])) {
    $db = (new Database())->getConnection();
    $userObj = new Usuario($db);

    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $auth = $userObj->login($usuario, $password);

    if ($auth) {
        if (!empty($auth['two_factor_enabled']) && !empty($auth['two_factor_secret'])) {
            $_SESSION['user_id'] = $auth['id'];
            $_SESSION['nombre'] = $auth['nombre'];
            $_SESSION['usuario'] = $auth['usuario'];
            $_SESSION['rol'] = $auth['rol'];
            $_SESSION['pending_2fa'] = true;
            header("Location: ../views/verificar_2fa.php");
        } else {
            $_SESSION['user_id'] = $auth['id'];
            $_SESSION['nombre']  = $auth['nombre'];
            $_SESSION['usuario'] = $auth['usuario'];
            $_SESSION['rol']     = $auth['rol'];
            header("Location: ../views/dashboard.php");
        }
    } else {
        header("Location: ../views/login.php?error=1");
    }
    exit();
}

if (isset($_POST['btn_verificar_2fa'])) {
    require_once "../classes/TOTP.php";
    
    $codigo = trim($_POST['codigo_2fa']);
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT two_factor_secret FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && !empty($user['two_factor_secret'])) {
        $totp = new TOTP($user['two_factor_secret']);
        if ($totp->verifyCode($codigo)) {
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$user_id]);
            $auth = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $_SESSION['user_id'] = $auth['id'];
            $_SESSION['nombre'] = $auth['nombre'];
            $_SESSION['usuario'] = $auth['usuario'];
            $_SESSION['rol'] = $auth['rol'];
            $_SESSION['pending_2fa'] = false;
            
            header("Location: ../views/dashboard.php");
            exit();
        }
    }
    header("Location: ../views/verificar_2fa.php?error=1");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../views/login.php");
    exit();
}