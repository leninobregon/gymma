<?php
session_start();

require_once "../config/Database.php";
require_once "../classes/Inventario.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../views/login.php?error=no_autorizado");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$inventario = new Inventario($db);

// AGREGAR
if (isset($_POST['agregar'])) {
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 0);

    if (!empty($descripcion) && $precio > 0 && $cantidad >= 0) {
        if ($inventario->crear($descripcion, $precio, $cantidad)) {
            header("Location: ../views/admin/gestion_inventario.php?msj=agregado");
            exit();
        }
    }
    header("Location: ../views/admin/gestion_inventario.php?error=1");
    exit();
}

// ACTUALIZAR
if (isset($_POST['btn_actualizar_prod'])) {
    $id = intval($_POST['id'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $cantidad = intval($_POST['cantidad'] ?? 0);

    if ($id > 0 && !empty($descripcion) && $precio > 0 && $cantidad >= 0) {
        if ($inventario->actualizar($id, $descripcion, $precio, $cantidad)) {
            header("Location: ../views/admin/gestion_inventario.php?msj=actualizado");
            exit();
        }
    }
    header("Location: ../views/admin/gestion_inventario.php?error=1");
    exit();
}

// ELIMINAR
if (isset($_GET['eliminar_id'])) {
    $id = intval($_GET['eliminar_id']);
    
    if ($id > 0 && $inventario->eliminar($id)) {
        header("Location: ../views/admin/gestion_inventario.php?msj=eliminado");
        exit();
    }
    header("Location: ../views/admin/gestion_inventario.php?error=1");
    exit();
}

header("Location: ../views/admin/gestion_inventario.php");