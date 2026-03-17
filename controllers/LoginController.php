<?php
session_start();
require_once "../config/Database.php";
require_once "../classes/Usuario.php";

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}


if (isset($_POST['btn_login'])) {
    $db = (new Database())->getConnection();
    $userObj = new Usuario($db);

    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    $auth = $userObj->login($usuario, $password);

    if ($auth) {
        // Guardar datos en sesión para usar en todo el sistema
        $_SESSION['user_id'] = $auth['id'];
        $_SESSION['nombre']  = $auth['nombre'];
        $_SESSION['usuario'] = $auth['usuario'];
        $_SESSION['rol']     = $auth['rol'];

        header("Location: ../views/dashboard.php");
    } else {
        // Si falla, regresa con error
        header("Location: ../index.php?error=1");
    }
    exit();
}

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}