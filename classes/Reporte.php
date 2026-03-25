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

    public function getInventarioCompleto() {
        $sql = "SELECT * FROM inventario ORDER BY cantidad ASC, descripcion ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventarioBajoStock($limite = 5) {
        $sql = "SELECT * FROM inventario WHERE cantidad <= " . intval($limite) . " ORDER BY cantidad ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSociosEstado($estado = 'ACTIVO') {
        $sql = "SELECT s.*, p.nombre_plan 
                FROM socios s 
                LEFT JOIN planes p ON s.id_plan = p.id 
                WHERE s.estado = ? 
                ORDER BY s.fecha_vencimiento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSociosPorVencer($dias = 7) {
        $sql = "SELECT s.*, p.nombre_plan 
                FROM socios s 
                LEFT JOIN planes p ON s.id_plan = p.id 
                WHERE s.estado = 'ACTIVO' 
                AND s.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY s.fecha_vencimiento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSociosVencidos() {
        $sql = "SELECT s.*, p.nombre_plan 
                FROM socios s 
                LEFT JOIN planes p ON s.id_plan = p.id 
                WHERE s.estado = 'ACTIVO' 
                AND s.fecha_vencimiento < CURDATE()
                ORDER BY s.fecha_vencimiento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticasDashboard() {
        $stats = [];

        $stats['total_socios'] = $this->db->query("SELECT COUNT(*) as c FROM socios")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
        $stats['socios_activos'] = $this->db->query("SELECT COUNT(*) as c FROM socios WHERE estado = 'ACTIVO'")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
        $stats['socios_vencidos'] = $this->db->query("SELECT COUNT(*) as c FROM socios WHERE estado = 'ACTIVO' AND fecha_vencimiento < CURDATE()")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;

        $stats['ventas_hoy'] = $this->db->query("SELECT COALESCE(SUM(monto_total), 0) as t FROM ventas WHERE DATE(fecha_venta) = CURDATE() AND estado != 'ANULADO'")->fetch(PDO::FETCH_ASSOC)['t'] ?? 0;
        $stats['ventas_semana'] = $this->db->query("SELECT COALESCE(SUM(monto_total), 0) as t FROM ventas WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estado != 'ANULADO'")->fetch(PDO::FETCH_ASSOC)['t'] ?? 0;
        $stats['ventas_mes'] = $this->db->query("SELECT COALESCE(SUM(monto_total), 0) as t FROM ventas WHERE MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE()) AND estado != 'ANULADO'")->fetch(PDO::FETCH_ASSOC)['t'] ?? 0;

        $stats['productos_stock'] = $this->db->query("SELECT COUNT(*) as c FROM inventario WHERE cantidad > 0")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;
        $stats['productos_bajo_stock'] = $this->db->query("SELECT COUNT(*) as c FROM inventario WHERE cantidad <= 5")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;

        $stats['caja_abierta'] = $this->db->query("SELECT COUNT(*) as c FROM cajas WHERE estado = 'ABIERTA'")->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;

        return $stats;
    }

    public function getIngresosEgresos($desde, $hasta) {
        $ingresos = $this->db->prepare("
            SELECT SUM(monto_total) as total 
            FROM ventas 
            WHERE estado != 'ANULADO' AND DATE(fecha_venta) BETWEEN ? AND ?
        ");
        $ingresos->execute([$desde, $hasta]);
        $totalIngresos = $ingresos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $egresos = $this->db->prepare("
            SELECT SUM(monto_salida) as total 
            FROM caja_egresos 
            WHERE DATE(fecha_egreso) BETWEEN ? AND ?
        ");
        $egresos->execute([$desde, $hasta]);
        $totalEgresos = $egresos->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $porMetodo = $this->db->prepare("
            SELECT metodo_pago, SUM(monto_total) as total 
            FROM ventas 
            WHERE estado != 'ANULADO' AND DATE(fecha_venta) BETWEEN ? AND ?
            GROUP BY metodo_pago
        ");
        $porMetodo->execute([$desde, $hasta]);
        $metodos = $porMetodo->fetchAll(PDO::FETCH_ASSOC);

        return [
            'ingresos' => $totalIngresos,
            'egresos' => $totalEgresos,
            'balance' => $totalIngresos - $totalEgresos,
            'por_metodo' => $metodos
        ];
    }

    public function getIngresosPorCategoria($desde, $hasta) {
        $sql = "SELECT 
                    CASE 
                        WHEN tipo_item = 'MEMBRESIA' THEN 'Membresías'
                        WHEN tipo_item = 'PRODUCTO' THEN 'Productos'
                        ELSE 'Otros'
                    END as categoria,
                    SUM(monto_total) as total
                FROM ventas 
                WHERE estado != 'ANULADO' AND DATE(fecha_venta) BETWEEN ? AND ?
                GROUP BY categoria";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentasPorCategoria() {
        $sql = "SELECT 
                    CASE 
                        WHEN tipo_item = 'MEMBRESIA' THEN 'Membresías'
                        WHEN tipo_item = 'PRODUCTO' THEN 'Productos'
                        ELSE 'Otros'
                    END as categoria,
                    COUNT(*) as cantidad,
                    SUM(monto_total) as total
                FROM ventas 
                WHERE estado != 'ANULADO'
                GROUP BY categoria";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductosMasVendidos($limite = 5) {
        $sql = "SELECT concepto, SUM(cantidad_item) as cantidad_vendida, SUM(monto_total) as total_ventas
                FROM ventas 
                WHERE tipo_item = 'PRODUCTO' AND estado != 'ANULADO'
                GROUP BY concepto 
                ORDER BY cantidad_vendida DESC 
                LIMIT " . intval($limite);
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRendimientoCajero($desde = null, $hasta = null) {
        $sql = "SELECT 
                    u.id,
                    u.usuario,
                    u.rol,
                    COUNT(v.id) as total_ventas,
                    COALESCE(SUM(v.monto_total), 0) as total_recaudado,
                    COALESCE(SUM(CASE WHEN v.estado = 'ANULADO' THEN v.monto_total ELSE 0 END), 0) as total_anulado
                FROM usuarios u
                LEFT JOIN ventas v ON u.id = v.id_usuario";
        
        $params = [];
        if ($desde && $hasta) {
            $sql .= " AND DATE(v.fecha_venta) BETWEEN ? AND ?";
            $params = [$desde, $hasta];
        }
        
        $sql .= " WHERE u.rol IN ('ADMIN', 'CAJA') GROUP BY u.id ORDER BY total_recaudado DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientesFrecuentes($limite = 10) {
        $sql = "SELECT 
                    s.id,
                    s.nombre,
                    s.apellido,
                    s.telefono,
                    COUNT(v.id) as num_compras,
                    SUM(v.monto_total) as total_gastado
                FROM socios s
                JOIN ventas v ON s.id = v.id_socio
                WHERE v.estado != 'ANULADO'
                GROUP BY s.id
                ORDER BY num_compras DESC
                LIMIT " . intval($limite);
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSociosPorVencerAgrupados() {
        $sql = "SELECT 
                    CASE 
                        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 0 THEN 'VENCIDOS'
                        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 3 THEN 'URGENTE (1-3 días)'
                        WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 7 THEN 'POR VENCER (4-7 días)'
                        ELSE 'PROXIMOS (8-30 días)'
                    END as grupo,
                    COUNT(*) as cantidad
                FROM socios 
                WHERE estado = 'ACTIVO' AND fecha_vencimiento IS NOT NULL
                GROUP BY grupo
                ORDER BY FIELD(grupo, 'VENCIDOS', 'URGENTE (1-3 días)', 'POR VENCER (4-7 días)', 'PROXIMOS (8-30 días)')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMetricasAvanzadas() {
        $metricas = [];
        
        $metricas['ticket_promedio'] = $this->db->query("
            SELECT COALESCE(AVG(monto_total), 0) as promedio 
            FROM ventas 
            WHERE estado != 'ANULADO'
        ")->fetch(PDO::FETCH_ASSOC)['promedio'] ?? 0;

        $metricas['ventas_hoy'] = $this->db->query("
            SELECT COUNT(*) as total 
            FROM ventas 
            WHERE DATE(fecha_venta) = CURDATE() AND estado != 'ANULADO'
        ")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $metricas['socios_nuevos_mes'] = $this->db->query("
            SELECT COUNT(*) as total 
            FROM socios 
            WHERE MONTH(fecha_ingreso) = MONTH(CURDATE()) AND YEAR(fecha_ingreso) = YEAR(CURDATE())
        ")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $metricas['renovaciones_mes'] = $this->db->query("
            SELECT COUNT(*) as total 
            FROM socios 
            WHERE fecha_renovacion IS NOT NULL 
            AND MONTH(fecha_renovacion) = MONTH(CURDATE()) 
            AND YEAR(fecha_renovacion) = YEAR(CURDATE())
        ")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $metricas['productos_vendidos_hoy'] = $this->db->query("
            SELECT COALESCE(SUM(cantidad_item), 0) as total 
            FROM ventas 
            WHERE tipo_item = 'PRODUCTO' AND DATE(fecha_venta) = CURDATE() AND estado != 'ANULADO'
        ")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        return $metricas;
    }

    public function getNotificacionesStock() {
        return $this->db->query("
            SELECT id, descripcion, cantidad 
            FROM inventario 
            WHERE cantidad <= 5 
            ORDER BY cantidad ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNotificacionesSocios() {
        return $this->db->query("
            SELECT nombre, apellido, telefono, fecha_vencimiento
            FROM socios 
            WHERE estado = 'ACTIVO' 
            AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
            ORDER BY fecha_vencimiento ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
}