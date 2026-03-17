<?php
session_start();
require_once "../config/Database.php";
require_once "../classes/Plan.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') { exit("No autorizado"); }

$db = (new Database())->getConnection();
$planObj = new Plan($db);

// AGREGAR
if (isset($_POST['agregar'])) {
    if ($planObj->crear($_POST['nombre_plan'], $_POST['duracion_dias'], $_POST['precio'])) {
        header("Location: ../views/admin/gestion_planes.php?msj=ok");
    }
}

// ACTUALIZAR
if (isset($_POST['btn_actualizar_plan'])) {
    if ($planObj->actualizar($_POST['id'], $_POST['nombre_plan'], $_POST['duracion_dias'], $_POST['precio'])) {
        header("Location: ../views/admin/gestion_planes.php?msj=editado");
    }
}

// ELIMINAR
if (isset($_GET['eliminar_id'])) {
    if ($planObj->eliminar($_GET['eliminar_id'])) {
        header("Location: ../views/admin/gestion_planes.php?msj=eliminado");
    }
}