<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit(); }
require_once "../config/Database.php";

$db = (new Database())->getConnection();

// --- CONFIGURACIÓN DE TASA Y MONEDA ---
$tasa_dolar = 36.65; 
$moneda_sistema = $_SESSION['moneda'] ?? 'COR';

// --- LÓGICA DE PROCESAR PAGO ---
if (isset($_POST['procesar_pago'])) {
    if (!isset($_SESSION['id_caja'])) {
        header("Location: ../views/caja/apertura_caja.php?err=caja_no_encontrada");
        exit();
    }

    $id_socio = !empty($_POST['id_socio']) ? $_POST['id_socio'] : null;
    $tipo = $_POST['tipo_cobro']; 
    $id_usuario = $_SESSION['user_id'] ?? 1;
    $id_caja = $_SESSION['id_caja'];
    $metodo = $_POST['metodo_pago'] ?? 'EFECTIVO';

    try {
        $db->beginTransaction();
        $id_item_ref = null;
        $cant = 1;
        $monto_final_cords = 0; // Variable para normalizar a Córdobas

        if ($tipo === 'PLAN') {
            $id_p = $_POST['id_plan'];
            $stmtPlan = $db->prepare("SELECT nombre_plan, precio FROM planes WHERE id = ?");
            $stmtPlan->execute([$id_p]);
            $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
            
            // El precio en la DB de planes debería estar en C$
            // Si el sistema está en USD, asumimos que el precio que vino del form necesita ser C$
            $monto_final_cords = $plan['precio']; 
            $concepto = "Plan: " . $plan['nombre_plan'];
            $id_item_ref = $id_p;

            if ($id_socio) {
                // Se suma 30 días al socio
                $sqlSocio = "UPDATE socios SET 
                             id_plan = ?, 
                             fecha_ingreso = CURDATE(), 
                             fecha_vencimiento = DATE_ADD(CURDATE(), INTERVAL 30 DAY), 
                             estado = 'ACTIVO' 
                             WHERE id = ?";
                $db->prepare($sqlSocio)->execute([$id_p, $id_socio]);
            }
        } else {
            // Lógica para PRODUCTO
            $id_pr = $_POST['id_producto'];
            $cant = $_POST['cantidad'];
            $stmtProd = $db->prepare("SELECT descripcion, precio, cantidad FROM inventario WHERE id = ?");
            $stmtProd->execute([$id_pr]);
            $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);

            if ($prod['cantidad'] < $cant) throw new Exception("Stock insuficiente.");

            // Calculamos el monto total en Córdobas (precio base * cantidad)
            $monto_final_cords = $prod['precio'] * $cant;
            $concepto = "Venta Art: " . $prod['descripcion'] . " (x$cant)";
            $id_item_ref = $id_pr;

            // Restar del inventario
            $db->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE id = ?")->execute([$cant, $id_pr]);
        }

        // --- INSERTAR VENTA (SIEMPRE EN C$) ---
        $sqlIns = "INSERT INTO ventas 
                  (id_usuario, id_caja, id_socio, monto_total, concepto, tipo_item, id_item_referencia, cantidad_item, metodo_pago, estado, fecha_venta) 
                  VALUES (?,?,?,?,?,?,?,?,?,'COMPLETADO', NOW())";
        
        $stmtIns = $db->prepare($sqlIns);
        // Aquí pasamos $monto_final_cords que garantiza la moneda base
        $stmtIns->execute([$id_usuario, $id_caja, $id_socio, $monto_final_cords, $concepto, $tipo, $id_item_ref, $cant, $metodo]);
        
        $db->commit();
        header("Location: ../views/caja/punto_venta.php?res=success");
        exit();

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        header("Location: ../views/caja/punto_venta.php?res=error&msg=" . urlencode($e->getMessage()));
        exit();
    }
}

// --- LÓGICA DE ANULACIÓN (SÓLO ADMIN) ---
if (isset($_GET['action']) && $_GET['action'] === 'anular') {
    if ($_SESSION['rol'] !== 'ADMIN') {
        header("Location: ../views/admin/reportes.php?err=permiso_denegado");
        exit();
    }

    $id_venta = $_GET['id'] ?? '';
    try {
        $db->beginTransaction();
        $stmtV = $db->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmtV->execute([$id_venta]);
        $v = $stmtV->fetch(PDO::FETCH_ASSOC);

        if ($v && $v['estado'] !== 'ANULADO') {
            // 1. Devolver stock si era producto
            if ($v['tipo_item'] === 'PRODUCTO') {
                $db->prepare("UPDATE inventario SET cantidad = cantidad + ? WHERE id = ?")
                   ->execute([$v['cantidad_item'], $v['id_item_referencia']]);
            }
            
            // 2. Revocar membresía si era un plan
            if ($v['tipo_item'] === 'PLAN' && !empty($v['id_socio'])) {
                $db->prepare("UPDATE socios SET estado = 'INACTIVO', fecha_vencimiento = SUBDATE(fecha_vencimiento, INTERVAL 30 DAY) WHERE id = ?")
                   ->execute([$v['id_socio']]);
            }

            // 3. Cambiar estado de la venta
            $db->prepare("UPDATE ventas SET estado = 'ANULADO' WHERE id = ?")->execute([$id_venta]);
            
            $db->commit();
            header("Location: ../views/admin/reportes.php?msj=anulado_ok");
            exit();
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        header("Location: ../views/admin/reportes.php?err=" . urlencode($e->getMessage()));
        exit();
    }
}