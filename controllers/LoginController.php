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
        $_SESSION['user_id'] = $auth['id'];
        $_SESSION['nombre']  = $auth['nombre'];
        $_SESSION['usuario'] = $auth['usuario'];
        $_SESSION['rol']     = $auth['rol'];

        header("Location: ../views/dashboard.php");
    } else {
        header("Location: ../views/login.php?error=1");
    }
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../views/login.php");
    exit();
}