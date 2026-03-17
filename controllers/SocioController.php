<?php
session_start();
require_once "../config/Database.php";
require_once "../classes/Socio.php";

$db = (new Database())->getConnection();
$socio = new Socio($db);

// --- 1. ACCIÓN: GUARDAR NUEVO SOCIO ---
if (isset($_POST['btn_guardar'])) {
    $foto = "default.png";
    
    // Gestión de la imagen de perfil
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = "SOCIO_" . time() . "." . $extension;
        
        $ruta_destino = "../public/uploads/" . $foto;
        
        // Crear carpeta de subidas si no existe
        if (!is_dir("../public/uploads/")) {
            mkdir("../public/uploads/", 0777, true);
        }
        
        move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino);
    }

    // Se envía todo el $_POST; la clase Socio procesará el teléfono y la emergencia
    if ($socio->registrar($_POST, $foto)) {
        header("Location: ../views/caja/registro_socios.php?msj=guardado");
    } else {
        header("Location: ../views/caja/registro_socios.php?error=1");
    }
    exit();
}

// --- 2. ACCIÓN: EDITAR SOCIO EXISTENTE ---
if (isset($_POST['btn_editar'])) {
    // La actualización incluye los campos corregidos de la DB
    if ($socio->actualizar($_POST)) {
        header("Location: ../views/caja/registro_socios.php?msj=editado");
    } else {
        header("Location: ../views/caja/registro_socios.php?error=editado");
    }
    exit();
}

// --- 3. ACCIÓN: ELIMINAR SOCIO (ACCESO PARA ADMIN Y CAJA) ---
if (isset($_GET['delete'])) {
    // Se permite el borrado si hay una sesión activa, sin importar el rol
    if (isset($_SESSION['rol'])) {
        $id = $_GET['delete'];
        
        if ($socio->eliminar($id)) {
            header("Location: ../views/caja/registro_socios.php?msj=eliminado");
        } else {
            header("Location: ../views/caja/registro_socios.php?error=borrado");
        }
    } else {
        header("Location: ../../login.php");
    }
    exit();
}

// Redirección por defecto si no hay acción
header("Location: ../views/caja/registro_socios.php");
exit();