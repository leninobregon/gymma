<?php
class AppConfig {
    private $conn;
    private $table_name = "configuracion";
    private $config_cache = null;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene la configuración y la guarda en caché para evitar múltiples
     * consultas a la base de datos en una misma ejecución.
     */
    public function obtenerConfig() {
        if ($this->config_cache !== null) {
            return $this->config_cache;
        }

        $query = "SELECT * FROM " . $this->table_name . " WHERE id = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $this->config_cache = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->config_cache;
    }

    /**
     * Método Auxiliar: Convierte montos dinámicamente
     * @param float $monto Monto base en Córdobas
     * @param string $hacia 'USD' o 'COR'
     * @return float
     */
    public function convertir($monto, $hacia = 'USD') {
        $config = $this->obtenerConfig();
        $tasa = $config['tasa_cambio'] ?? 36.65;

        if ($hacia === 'USD') {
            return $monto / $tasa;
        } else {
            return $monto * $tasa;
        }
    }
}