<?php
session_start();
require_once "../config/Database.php";
require_once "../classes/Plan.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') { 
    header("Location: ../views/admin/gestion_planes.php"); 
    exit();
}

$db = (new Database())->getConnection();
$planObj = new Plan($db);

// AGREGAR
if (isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre_plan'] ?? '');
    $duracion = intval($_POST['duracion_dias'] ?? 0);
    $precio = floatval($_POST['precio'] ?? 0);
    
    if (!empty($nombre) && $duracion > 0 && $precio > 0) {
        if ($planObj->crear($nombre, $duracion, $precio)) {
            header("Location: ../views/admin/gestion_planes.php?msj=ok");
            exit();
        }
    }
    header("Location: ../views/admin/gestion_planes.php?msj=error");
    exit();
}

// ACTUALIZAR
if (isset($_POST['btn_actualizar_plan'])) {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre_plan'] ?? '');
    $duracion = intval($_POST['duracion_dias'] ?? 0);
    $precio = floatval($_POST['precio'] ?? 0);
    
    if ($id > 0 && !empty($nombre) && $duracion > 0 && $precio > 0) {
        if ($planObj->actualizar($id, $nombre, $duracion, $precio)) {
            header("Location: ../views/admin/gestion_planes.php?msj=editado");
            exit();
        }
    }
    header("Location: ../views/admin/gestion_planes.php?msj=error");
    exit();
}

// ELIMINAR
if (isset($_GET['eliminar_id'])) {
    $id = intval($_GET['eliminar_id']);
    if ($id > 0 && $planObj->eliminar($id)) {
        header("Location: ../views/admin/gestion_planes.php?msj=eliminado");
        exit();
    }
}

header("Location: ../views/admin/gestion_planes.php");