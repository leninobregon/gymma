<?php
class Venta {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function realizarVenta($id_usuario, $id_socio, $id_producto, $cantidad_vendida, $precio_unitario) {
        try {
            // Iniciar transacción para que si algo falla, no se descuente stock por error
            $this->conn->beginTransaction();

            $total = $cantidad_vendida * $precio_unitario;

            // 1. Insertar en la tabla Ventas
            $queryVenta = "INSERT INTO ventas (id_usuario, monto_total) VALUES (:user, :total)";
            $stmtV = $this->conn->prepare($queryVenta);
            $stmtV->execute([':user' => $id_usuario, ':total' => $total]);
            
            // 2. Descontar del Inventario
            $queryStock = "UPDATE inventario SET cantidad = cantidad - :cant WHERE id = :prod AND cantidad >= :cant";
            $stmtS = $this->conn->prepare($queryStock);
            $stmtS->execute([':cant' => $cantidad_vendida, ':prod' => $id_producto]);

            if ($stmtS->rowCount() == 0) {
                throw new Exception("Stock insuficiente.");
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }
}