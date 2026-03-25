<?php
session_start();
require_once "../config/Database.php";
require_once "../config/AppConfig.php";
require_once "../classes/Usuario.php";

if (isset($_POST['btn_login'])) {
    $database = new Database();
    $db = $database->getConnection();
    $userObj = new Usuario($db);

    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $auth = $userObj->login($usuario, $password);

    if ($auth) {
        $_SESSION['user_id'] = $auth['id'];
        $_SESSION['nombre']  = $auth['nombre'];
        $_SESSION['usuario'] = $auth['usuario'];
        $_SESSION['rol']     = $auth['rol'];

        $config = (new AppConfig($database->getConnection()))->obtenerConfig();
        $_SESSION['tema'] = $config['tema'] ?? 'default';

        // --- REVISA ESTA RUTA ---
        // Si el archivo está en /views/caja/registro_socios.php
        // Desde /controllers/AuthController.php debes subir un nivel y entrar a views
        header("Location: ../views/dashboard.php");
        exit();
    } else {
        header("Location: ../views/login.php?error=1");
        exit();
    }
}