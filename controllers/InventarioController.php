<?php
/**
 * CONTROLADOR DE INVENTARIO COMPLETO
 * Maneja: Agregar, Editar y Eliminar productos.
 */
session_start();

// 1. INCLUIR DEPENDENCIAS
require_once "../config/Database.php";
require_once "../classes/Inventario.php";

// 2. SEGURIDAD: Solo ADMIN puede operar aquí
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    header("Location: ../views/login.php?error=no_autorizado");
    exit();
}

// 3. INICIALIZAR CONEXIÓN
$database = new Database();
$db = $database->getConnection();
$inventario = new Inventario($db);

// --- ACCIÓN: AGREGAR PRODUCTO ---
if (isset($_POST['agregar'])) {
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];

    if (!empty($descripcion)) {
        if ($inventario->crear($descripcion, $precio, $cantidad)) {
            header("Location: ../views/admin/gestion_inventario.php?msj=agregado");
        } else {
            header("Location: ../views/admin/gestion_inventario.php?error=1");
        }
    }
    exit();
}

// --- ACCIÓN: ACTUALIZAR PRODUCTO (EDITAR) ---
if (isset($_POST['btn_actualizar_prod'])) {
    $id = $_POST['id'];
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];

    // Ejecutamos la actualización directamente
    $query = "UPDATE inventario SET descripcion = ?, precio = ?, cantidad = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$descripcion, $precio, $cantidad, $id])) {
        header("Location: ../views/admin/gestion_inventario.php?msj=actualizado");
    } else {
        header("Location: ../views/admin/gestion_inventario.php?error=1");
    }
    exit();
}

// --- ACCIÓN: ELIMINAR PRODUCTO ---
if (isset($_GET['eliminar_id'])) {
    $id = $_GET['eliminar_id'];

    $query = "DELETE FROM inventario WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$id])) {
        header("Location: ../views/admin/gestion_inventario.php?msj=eliminado");
    } else {
        header("Location: ../views/admin/gestion_inventario.php?error=1");
    }
    exit();
}

// Si alguien accede al archivo sin una acción válida, lo regresamos
header("Location: ../views/admin/gestion_inventario.php");
exit();
?>