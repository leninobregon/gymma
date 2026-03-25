<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

require_once "../config/Database.php";
$db = (new Database())->getConnection();

// --- ACCIÓN: ABRIR CAJA ---
if (isset($_POST['btn_abrir_caja'])) {
    $id_usuario = $_POST['id_usuario'];
    $monto_apertura = $_POST['monto_apertura'];

    try {
        $sql = "INSERT INTO cajas (id_usuario, monto_apertura, monto_esperado, estado, fecha_apertura) 
                VALUES (?, ?, ?, 'ABIERTA', NOW())";
        
        $stmt = $db->prepare($sql);
        if ($stmt->execute([$id_usuario, $monto_apertura, $monto_apertura])) {
            
            // Guardamos el ID de la caja
            $nuevo_id_caja = $db->lastInsertId();
            $_SESSION['id_caja'] = $nuevo_id_caja;
            
            // Forzar guardado de sesión
            session_write_close(); 
            
            // Redirigir usando una ruta que evite el dashboard
            header("Location: ../views/caja/punto_venta.php");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../views/caja/apertura_caja.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

// --- ACCIÓN: CERRAR CAJA ---
if (isset($_POST['btn_cerrar_caja'])) {
    $id_caja = $_POST['id_caja'];
    $monto_cierre = $_POST['monto_cierre'];
    $nota = $_POST['nota'];

    try {
        // Calcular total recaudado en el turno
        $queryVentas = $db->prepare("SELECT SUM(monto_total) as total FROM ventas WHERE id_caja = ? AND estado != 'ANULADO'");
        $queryVentas->execute([$id_caja]);
        $totalVentas = $queryVentas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $queryCaja = $db->prepare("SELECT monto_apertura FROM cajas WHERE id = ?");
        $queryCaja->execute([$id_caja]);
        $monto_apertura = $queryCaja->fetchColumn();

        $monto_esperado = $monto_apertura + $totalVentas;

        $sqlCierre = "UPDATE cajas SET 
                        fecha_cierre = NOW(), 
                        monto_cierre = ?, 
                        monto_esperado = ?, 
                        estado = 'CERRADA', 
                        nota = ? 
                      WHERE id = ?";
        
        $stmtCierre = $db->prepare($sqlCierre);
        if ($stmtCierre->execute([$monto_cierre, $monto_esperado, $nota, $id_caja])) {
            unset($_SESSION['id_caja']);
            header("Location: ../views/dashboard.php?msj=caja_cerrada");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../views/caja/cerrar_caja.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}