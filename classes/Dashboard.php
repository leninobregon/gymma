<?php
class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getResumenVentasHoy() {
        $fechaHoy = date('Y-m-d');
        $query = "SELECT SUM(monto_total) as total FROM ventas WHERE DATE(fecha_venta) = :hoy";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':hoy' => $fechaHoy]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'] ?? 0;
    }

    public function getTotalSociosActivos() {
        $query = "SELECT COUNT(*) as total FROM socios WHERE estado = 'ACTIVO'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'];
    }

    public function getAlertasInventario() {
        $query = "SELECT COUNT(*) as total FROM inventario WHERE cantidad <= 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'];
    }
}