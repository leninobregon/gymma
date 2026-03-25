<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') { exit("Acceso denegado"); }

require_once "../config/Database.php";

$db = (new Database())->getConnection();

try {
    $db->exec("ALTER TABLE configuracion ADD COLUMN IF NOT EXISTS tema VARCHAR(20) DEFAULT 'default'");
} catch (PDOException $e) {
    try {
        $db->exec("ALTER TABLE configuracion ADD COLUMN tema VARCHAR(20) DEFAULT 'default'");
    } catch (PDOException $e2) {}
}

if (isset($_POST['btn_save_config'])) {
    $db = (new Database())->getConnection();

    $nombre_gym     = $_POST['nombre_gym'];
    $telefono_gym   = $_POST['telefono_gym'];
    $direccion_gym  = $_POST['direccion_gym'];
    $moneda_iso     = $_POST['moneda_iso'];
    $moneda_nombre  = $_POST['moneda_nombre'];
    $moneda_simbolo = $_POST['moneda_simbolo'];
    $tc_bcn         = $_POST['tipo_cambio_bcn'];
    $tema           = $_POST['tema'] ?? 'default';

    // Manejo del Logo
    $query_logo = "";
    $params_extra = [];
    
    if (isset($_FILES['logo_gym']) && $_FILES['logo_gym']['error'] === 0) {
        $ext = pathinfo($_FILES['logo_gym']['name'], PATHINFO_EXTENSION);
        $nombre_foto = "logo_principal." . $ext;
        $ruta_destino = "../public/img/" . $nombre_foto;

        if (move_uploaded_file($_FILES['logo_gym']['tmp_name'], $ruta_destino)) {
            $query_logo = ", logo_ruta = ?";
            $params_extra[] = $nombre_foto;
        }
    }

    $sql = "UPDATE configuracion SET 
            nombre_gym = ?, 
            telefono_gym = ?, 
            direccion_gym = ?, 
            moneda_iso = ?, 
            moneda_nombre = ?, 
            moneda_simbolo = ?, 
            tipo_cambio_bcn = ?,
            tema = ?,
            ultima_actualizacion = NOW()
            $query_logo
            WHERE id = 1";

    $stmt = $db->prepare($sql);
    $params = [$nombre_gym, $telefono_gym, $direccion_gym, $moneda_iso, $moneda_nombre, $moneda_simbolo, $tc_bcn, $tema];
    
    if(!empty($params_extra)) { $params = array_merge($params, $params_extra); }

    if ($stmt->execute($params)) {
        $_SESSION['tema'] = $tema;
        session_write_close();
        header("Location: ../views/admin/configuracion.php?msj=ok");
    } else {
        header("Location: ../views/admin/configuracion.php?msj=error");
    }
}