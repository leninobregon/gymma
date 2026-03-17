<?php
session_start();
require_once "../config/Database.php";
require_once "../classes/Usuario.php";

// Seguridad: Solo el ADMIN puede ejecutar estas acciones
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../views/login.php");
    exit();
}

$db = (new Database())->getConnection();
$userObj = new Usuario($db);

// --- ACCIÓN: REGISTRAR ---
if (isset($_POST['btn_registrar_user'])) {
    if ($userObj->registrar($_POST)) {
        header("Location: ../views/admin/gestion_usuarios.php?res=registrado");
    } else {
        header("Location: ../views/admin/gestion_usuarios.php?res=error");
    }
    exit();
}

// --- ACCIÓN: ACTUALIZAR ---
if (isset($_POST['btn_actualizar_user'])) {
    if ($userObj->actualizar($_POST)) {
        header("Location: ../views/admin/gestion_usuarios.php?res=actualizado");
    } else {
        header("Location: ../views/admin/gestion_usuarios.php?res=error");
    }
    exit();
}

// --- ACCIÓN: ELIMINAR ---
if (isset($_GET['eliminar'])) {
    $id_a_eliminar = $_GET['eliminar'];
    $mi_id = $_SESSION['user_id'] ?? 0; // Usamos el ID de sesión para proteger el propio usuario

    // 1. No permitir que se borre a sí mismo
    if (intval($id_a_eliminar) === intval($mi_id)) {
        header("Location: ../views/admin/gestion_usuarios.php?res=autoerror");
        exit();
    }

    // 2. Ejecutar eliminación
    if ($userObj->eliminar($id_a_eliminar)) {
        header("Location: ../views/admin/gestion_usuarios.php?res=eliminado");
    } else {
        header("Location: ../views/admin/gestion_usuarios.php?res=error");
    }
    exit();
}

// Si llega aquí sin entrar en ningún IF, redirigir por defecto
header("Location: ../views/admin/gestion_usuarios.php");
exit();