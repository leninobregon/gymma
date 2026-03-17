<?php
session_start();
require_once "../config/Database.php";

$db = (new Database())->getConnection();

if (isset($_POST['procesar_pago'])) {
    $id_socio = !empty($_POST['id_socio']) ? $_POST['id_socio'] : null;
    $tipo = $_POST['tipo_cobro']; // 'PLAN' o 'PRODUCTO'
    $id_usuario = $_SESSION['user_id'] ?? 1;
    $metodo = $_POST['metodo_pago'];
    $imprimir = $_POST['opcion_ticket']; // 'si' o 'no'

    try {
        $db->beginTransaction();

        if ($tipo === 'PLAN') {
            $id_p = $_POST['id_plan'];
            $stmtPlan = $db->prepare("SELECT nombre_plan, precio FROM planes WHERE id = ?");
            $stmtPlan->execute([$id_p]);
            $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
            
            $monto = $plan['precio'];
            $concepto = "Plan: " . $plan['nombre_plan'];

            if ($id_socio) {
                $db->prepare("UPDATE socios SET id_plan = ?, fecha_ingreso = CURDATE(), estado = 'ACTIVO' WHERE id = ?")
                   ->execute([$id_p, $id_socio]);
            }
        } else {
            $id_pr = $_POST['id_producto'];
            $cant = (int)$_POST['cantidad'];

            $stmtProd = $db->prepare("SELECT descripcion, precio, cantidad FROM inventario WHERE id = ?");
            $stmtProd->execute([$id_pr]);
            $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);

            if ($prod['cantidad'] < $cant) {
                throw new Exception("Stock insuficiente para " . $prod['descripcion']);
            }

            $monto = $prod['precio'] * $cant;
            $concepto = "Venta Art: " . $prod['descripcion'] . " (x$cant)";

            // Descontar inventario
            $db->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE id = ?")
               ->execute([$cant, $id_pr]);
        }

        // Registrar Venta
        $stmtIns = $db->prepare("INSERT INTO ventas (id_usuario, id_socio, monto_total, concepto, tipo_item, metodo_pago, estado, fecha_venta) VALUES (?,?,?,?,?,?,'COMPLETADO', NOW())");
        $stmtIns->execute([$id_usuario, $id_socio, $monto, $concepto, $tipo, $metodo]);
        $id_venta = $db->lastInsertId();

        $db->commit();

        if ($imprimir === 'si') {
            echo "<script>
                var win = window.open('../views/caja/imprimir_recibo.php?id=$id_venta', '_blank');
                if(win){
                    win.focus();
                } else {
                    alert('Por favor permite las ventanas emergentes para imprimir');
                }
                window.location.href = '../views/caja/punto_venta.php?res=success';
            </script>";
        } else {
            header("Location: ../views/caja/punto_venta.php?res=success");
        }
    } catch (Exception $e) {
        $db->rollBack();
        header("Location: ../views/caja/punto_venta.php?error=" . urlencode($e->getMessage()));
    }
    exit();
}