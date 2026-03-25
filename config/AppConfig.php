<?php
class AppConfig {
    private $conn;
    private $table_name = "configuracion";
    private $config_cache = null;

    public function __construct($db) {
        $this->conn = $db;
        $this->asegurarColumnaTema();
    }

    private function asegurarColumnaTema() {
        try {
            $this->conn->exec("ALTER TABLE configuracion ADD COLUMN tema VARCHAR(20) DEFAULT 'default'");
        } catch (PDOException $e) {}
    }

    public function obtenerConfig() {
        if ($this->config_cache !== null) {
            return $this->config_cache;
        }

        $query = "SELECT * FROM " . $this->table_name . " WHERE id = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $this->config_cache = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!isset($this->config_cache['tema']) || empty($this->config_cache['tema'])) {
            $this->config_cache['tema'] = 'default';
        }
        
        return $this->config_cache;
    }

    public function convertir($monto, $hacia = 'USD') {
        $config = $this->obtenerConfig();
        $tasa = $config['tipo_cambio_bcn'] ?? 36.65;

        if ($hacia === 'USD') {
            return $monto / $tasa;
        } else {
            return $monto * $tasa;
        }
    }
}