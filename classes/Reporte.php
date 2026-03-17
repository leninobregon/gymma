<?php
class Reporte {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getVentas($desde = null, $hasta = null) {
        $sql = "SELECT v.*, s.nombre, s.apellido, u.usuario as user_id, u.rol as user_rol 
                FROM ventas v 
                LEFT JOIN socios s ON v.id_socio = s.id 
                LEFT JOIN usuarios u ON v.id_usuario = u.id";
        
        $params = [];
        if ($desde && $hasta) {
            $sql .= " WHERE DATE(v.fecha_venta) BETWEEN ? AND ?";
            $params = [$desde, $hasta];
        }

        $sql .= " ORDER BY v.fecha_venta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ESTE ES EL MÉTODO NUEVO QUE DEBES AGREGAR ---
    public function getHistorialCajas($desde = null, $hasta = null) {
        $sql = "SELECT c.*, u.usuario as nombre_usuario, u.rol as user_rol 
                FROM cajas c 
                JOIN usuarios u ON c.id_usuario = u.id";
        
        $params = [];
        if ($desde && $hasta) {
            $sql .= " WHERE DATE(c.fecha_apertura) BETWEEN ? AND ?";
            $params = [$desde, $hasta];
        }

        $sql .= " ORDER BY c.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumenPorCajero($desde = null, $hasta = null) {
        $sql = "SELECT u.usuario as nombre_persona, SUM(v.monto_total) as recaudado 
                FROM ventas v 
                JOIN usuarios u ON v.id_usuario = u.id 
                WHERE v.estado != 'ANULADO'";
        
        $params = [];
        if ($desde && $hasta) {
            $sql .= " AND DATE(v.fecha_venta) BETWEEN ? AND ?";
            $params = [$desde, $hasta];
        }

        $sql .= " GROUP BY u.id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIngresosPorMetodo($desde, $hasta) {
        $sql = "SELECT metodo_pago, SUM(monto_total) as total 
                FROM ventas 
                WHERE estado != 'ANULADO' AND DATE(fecha_venta) BETWEEN ? AND ?
                GROUP BY metodo_pago";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}